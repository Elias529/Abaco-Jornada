<?php

namespace App\Services;

use App\Models\Jornada;
use App\Models\Tramo;
use App\Models\User;
use App\Support\Tiempo;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Copia del registro de una persona en CSV. La comparten quien consulta
 * su propio registro y quien lo consulta con motivo.
 */
class RegistroCsv
{
    /** @var list<string> */
    public const COLUMNAS = ['Fecha', 'Inicio', 'Fin', 'Total', 'Estado', 'Incidencia'];

    public function __construct(private JornadaService $jornada) {}

    /**
     * La misma fila que pinta la tabla de Mi registro.
     *
     * @return array{Fecha: string, Inicio: string, Fin: string, Total: string, Estado: string, Incidencia: string}
     */
    public function fila(User $persona, Jornada $jornada): array
    {
        $jornada->loadMissing('tramos');
        $primero = $jornada->tramos
            ->where('tipo', Tramo::TRABAJO)
            ->sortBy('started_at')
            ->first();
        $ultimo = $jornada->tramos
            ->filter(fn (Tramo $tramo) => $tramo->ended_at !== null)
            ->sortBy('ended_at')
            ->last();
        $incidencia = $jornada->tramos->contains(fn (Tramo $tramo) => $tramo->situacion === 'incidencia');

        return [
            'Fecha' => Tiempo::fecha($jornada->work_date),
            'Inicio' => $primero?->started_at ? Tiempo::hora($primero->started_at) : '',
            'Fin' => $jornada->estaCerrada() && $ultimo?->ended_at ? Tiempo::hora($ultimo->ended_at) : '',
            'Total' => Tiempo::texto($this->jornada->minutosRegistrados($persona, $jornada)),
            'Estado' => $this->estado($jornada),
            'Incidencia' => $incidencia ? 'Sí' : 'No',
        ];
    }

    public function descargar(User $persona, ?string $desde = null, ?string $hasta = null): StreamedResponse
    {
        $desde = is_string($desde) && $desde !== '' ? $desde : null;
        $hasta = is_string($hasta) && $hasta !== '' ? $hasta : null;
        $jornadas = Jornada::query()
            ->with('tramos')
            ->where('user_id', $persona->id)
            ->when($desde, fn ($q) => $q->whereDate('work_date', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('work_date', '<=', $hasta))
            ->orderByDesc('work_date')
            ->get();

        return $this->archivo($persona, $jornadas);
    }

    public function descargarVacio(User $persona): StreamedResponse
    {
        return $this->archivo($persona, collect());
    }

    private function archivo(User $persona, iterable $jornadas): StreamedResponse
    {
        $nombre = 'registro-'.$persona->id.'.csv';

        return response()->streamDownload(function () use ($jornadas, $persona) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, self::COLUMNAS, ';');

            foreach ($jornadas as $jornada) {
                fputcsv($salida, array_values($this->fila($persona, $jornada)), ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function estado(Jornada $jornada): string
    {
        if ($jornada->tramoAbierto()) {
            return 'Pendiente de cerrar';
        }

        if ($jornada->closed_late) {
            return 'Cerrada al día siguiente';
        }

        return 'Cerrada';
    }
}
