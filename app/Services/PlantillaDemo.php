<?php

namespace App\Services;

use App\Models\Ausencia;
use App\Models\Horario;
use App\Models\User;
use App\Sede;
use App\TipoJornada;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class PlantillaDemo
{
    public const CLAVE = 'Jornada2026';

    public function __construct(private ?string $archivo = null)
    {
        $this->archivo ??= database_path('seeders/data/personas.json');
    }

    /**
     * Carga la plantilla compartida. Se puede repetir: no duplica correos
     * ni borra fichajes. La clave de las cuentas nuevas es Jornada2026.
     */
    public function aplicar(): void
    {
        foreach ($this->leer() as $fila) {
            $this->aplicarPersona($fila);
        }
    }

    public function registrarAlta(User $persona): void
    {
        $horario = $persona->horarios()->orderBy('effective_from')->first();
        $fila = [
            'name' => $persona->name,
            'email' => $persona->email,
            'role' => $persona->role,
            'municipality' => (string) $persona->municipality,
            'centro' => (string) $persona->centro,
            'starts_on' => $persona->starts_on?->toDateString(),
            'ends_on' => $persona->ends_on?->toDateString(),
            'tipo' => $horario?->tipo ?? TipoJornada::Partida->value,
            'morning_start' => $horario ? substr((string) $horario->morning_start, 0, 5) : '09:00',
            'morning_end' => $horario ? substr((string) $horario->morning_end, 0, 5) : '14:00',
            'afternoon_start' => $horario ? substr((string) $horario->afternoon_start, 0, 5) : '15:00',
            'afternoon_end' => $horario ? substr((string) $horario->afternoon_end, 0, 5) : '18:00',
            'effective_from' => $horario?->effective_from?->toDateString() ?? $persona->starts_on?->toDateString(),
            'ausencias' => [],
        ];

        $todas = $this->leer();
        foreach ($todas as $indice => $existente) {
            if (($existente['email'] ?? '') === $persona->email) {
                $todas[$indice] = array_merge($existente, $fila);

                $this->guardar($todas);

                return;
            }
        }

        $todas[] = $fila;
        $this->guardar($todas);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function leer(): array
    {
        if (! is_file($this->archivo)) {
            return [];
        }

        $datos = json_decode((string) File::get($this->archivo), true);
        if (! is_array($datos) || ! array_is_list($datos)) {
            return [];
        }

        return $datos;
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    private function aplicarPersona(array $fila): void
    {
        $correo = (string) ($fila['email'] ?? '');
        if ($correo === '') {
            return;
        }

        $sede = Sede::tryFrom((string) ($fila['centro'] ?? '')) ?? Sede::porDefecto();
        $persona = User::query()->firstOrCreate(
            ['email' => $correo],
            [
                'name' => (string) ($fila['name'] ?? $correo),
                'password' => self::CLAVE,
                'role' => (string) ($fila['role'] ?? 'trabajadora'),
                'active' => true,
                'starts_on' => $fila['starts_on'] ?? null,
                'ends_on' => $fila['ends_on'] ?? null,
                'municipality' => (string) ($fila['municipality'] ?? $sede->municipio()),
                'centro' => $sede->value,
            ],
        );

        $persona->fill([
            'name' => (string) ($fila['name'] ?? $persona->name),
            'role' => (string) ($fila['role'] ?? $persona->role),
            'starts_on' => $fila['starts_on'] ?? $persona->starts_on,
            'ends_on' => $fila['ends_on'] ?? $persona->ends_on,
            'municipality' => (string) ($fila['municipality'] ?? $sede->municipio()),
            'centro' => $sede->value,
        ])->save();

        $desde = Carbon::parse((string) ($fila['effective_from'] ?? $fila['starts_on'] ?? now()->toDateString()))->toDateString();
        if (! Horario::query()->where('user_id', $persona->id)->whereDate('effective_from', $desde)->exists()) {
            Horario::create([
                'user_id' => $persona->id,
                'tipo' => $fila['tipo'] ?? TipoJornada::Partida->value,
                'morning_start' => $fila['morning_start'] ?? '09:00',
                'morning_end' => $fila['morning_end'] ?? '14:00',
                'afternoon_start' => $fila['afternoon_start'] ?? '15:00',
                'afternoon_end' => $fila['afternoon_end'] ?? '18:00',
                'effective_from' => $desde,
            ]);
        }

        foreach ($fila['ausencias'] ?? [] as $ausencia) {
            if (! is_array($ausencia) || ! isset($ausencia['absence_date'])) {
                continue;
            }

            $fecha = Carbon::parse((string) $ausencia['absence_date'])->toDateString();
            if (Ausencia::query()->where('user_id', $persona->id)->whereDate('absence_date', $fecha)->exists()) {
                continue;
            }

            Ausencia::create([
                'user_id' => $persona->id,
                'absence_date' => $fecha,
                'tipo' => $ausencia['tipo'] ?? 'vacaciones',
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $personas
     */
    private function guardar(array $personas): void
    {
        File::ensureDirectoryExists(dirname($this->archivo));
        File::put(
            $this->archivo,
            json_encode($personas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n",
        );
    }
}
