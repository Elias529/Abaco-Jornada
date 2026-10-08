<?php

namespace Database\Seeders;

use App\Models\Festivo;
use Illuminate\Database\Seeder;

class FestivosSeeder extends Seeder
{
    /**
     * Calendario del municipio de Madrid para 2026, el del Ayuntamiento:
     * las fiestas que publica para la capital, San Isidro y La Almudena.
     * El centro está en Madrid y este calendario vale para toda la plantilla.
     */
    public function run(): void
    {
        $madrid = [
            '2026-01-01' => 'Año Nuevo',
            '2026-01-06' => 'Epifanía del Señor',
            '2026-04-02' => 'Jueves Santo',
            '2026-04-03' => 'Viernes Santo',
            '2026-05-01' => 'Fiesta del Trabajo',
            '2026-05-02' => 'Dos de Mayo',
            '2026-05-15' => 'San Isidro',
            '2026-08-15' => 'Asunción de la Virgen',
            '2026-10-12' => 'Fiesta Nacional de España',
            '2026-11-02' => 'Todos los Santos',
            '2026-11-09' => 'La Almudena',
            '2026-12-07' => 'Día de la Constitución',
            '2026-12-08' => 'Inmaculada Concepción',
            '2026-12-25' => 'Navidad',
        ];

        foreach ($madrid as $fecha => $nombre) {
            $this->guardar($fecha, 'Madrid', $nombre);
        }

        Festivo::query()->where('municipality', '!=', 'Madrid')->delete();
    }

    private function guardar(string $fecha, string $municipio, string $nombre): void
    {
        $enTodos = Festivo::query()
            ->whereDate('holiday_date', $fecha)
            ->where('municipality', '')
            ->first();

        $existente = Festivo::query()
            ->whereDate('holiday_date', $fecha)
            ->where('municipality', $municipio)
            ->first();

        if ($enTodos && $municipio !== '') {
            if ($existente) {
                $enTodos->delete();
            } else {
                $enTodos->update([
                    'municipality' => $municipio,
                    'name' => $nombre,
                ]);

                return;
            }
        }

        if ($existente) {
            $existente->update(['name' => $nombre]);

            return;
        }

        Festivo::create([
            'holiday_date' => $fecha,
            'municipality' => $municipio,
            'name' => $nombre,
        ]);
    }
}
