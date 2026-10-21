<?php

namespace Database\Seeders;

use App\Services\PlantillaDemo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Personas de la plantilla compartida. Contraseña de las cuentas nuevas: Jornada2026.
     * Se puede repetir: no duplica correos ni borra fichajes.
     */
    public function run(): void
    {
        app(PlantillaDemo::class)->aplicar();
    }
}
