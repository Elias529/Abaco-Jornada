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
        $marta = $this->persona('Marta Ruiz', 'marta.ruiz@abaco.test', 'jefa', 'Madrid');
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test', 'trabajadora', 'Madrid');
        $carmen = $this->persona('Carmen Ortega', 'carmen.ortega@abaco.test', 'tecnico', 'Madrid');

        foreach ([$marta, $ana, $carmen] as $persona) {
            Horario::create([
                'user_id' => $persona->id,
                'tipo' => 'partida',
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
            'centro' => 'ES-MD',
        ]);
    }
}
