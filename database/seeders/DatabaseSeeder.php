<?php

namespace Database\Seeders;

use App\Models\Ausencia;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Personas ficticias. Contraseña de todas: Jornada2026
     */
    public function run(): void
    {
        $marta = $this->persona('Marta Ruiz', 'marta.ruiz@abaco.test', 'responsable', 'Madrid');
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test', 'trabajadora', 'Madrid');
        $lucia = $this->persona('Lucía Vega', 'lucia.vega@abaco.test', 'trabajadora', 'Alcalá de Henares');

        foreach ([$marta, $ana, $lucia] as $persona) {
            Horario::create([
                'user_id' => $persona->id,
                'morning_start' => '09:00',
                'morning_end' => '14:00',
                'afternoon_start' => '15:00',
                'afternoon_end' => '18:00',
                'effective_from' => '2026-01-07',
            ]);
        }

        Ausencia::create([
            'user_id' => $ana->id,
            'absence_date' => '2026-10-15',
            'tipo' => 'vacaciones',
        ]);

        // La dirección no ficha: no lleva horario y su jornada no se registra.
        $this->persona('Carmen Ortega', 'carmen.ortega@abaco.test', 'jefe', 'Madrid');

        $this->call(FestivosSeeder::class);
    }

    private function persona(string $nombre, string $correo, string $papel, string $municipio): User
    {
        return User::create([
            'name' => $nombre,
            'email' => $correo,
            'password' => 'Jornada2026',
            'role' => $papel,
            'active' => true,
            'starts_on' => '2026-01-07',
            'municipality' => $municipio,
        ]);
    }
}
