<?php

namespace App\Console\Commands;

use App\Mail\OlvidoDeInicio;
use App\Models\User;
use App\Services\JornadaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

#[Signature('jornada:avisar-inicio')]
#[Description('Manda un correo a quien no ha empezado la jornada poco después de su hora de inicio')]
class AvisarOlvidoDeInicio extends Command
{
    public function handle(JornadaService $jornada): int
    {
        $ahora = now();
        $enviados = 0;
        $responsables = User::query()
            ->where('active', true)
            ->where('role', 'responsable')
            ->pluck('email', 'id');

        User::query()
            ->where('active', true)
            ->where('role', '!=', 'jefe')
            ->orderBy('id')
            ->each(function (User $persona) use ($jornada, $ahora, $responsables, &$enviados): void {
                $horaPrevista = $jornada->avisarDeInicio($persona, $ahora);

                if ($horaPrevista === null) {
                    return;
                }

                $copia = $responsables->except($persona->id)->values()->all();

                try {
                    Mail::to($persona->email)
                        ->cc($copia)
                        ->send(new OlvidoDeInicio($persona, $horaPrevista, $copia !== []));
                    $enviados++;
                } catch (Throwable $error) {
                    $jornada->liberarAvisoDeInicio($persona, $ahora);
                    $this->error('No se pudo avisar a '.$persona->email.': '.$error->getMessage());
                }
            });

        $this->info('Correos enviados: '.$enviados);

        return self::SUCCESS;
    }
}
