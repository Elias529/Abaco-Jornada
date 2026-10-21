<?php

namespace Tests\Feature;

use App\Models\Jornada;
use App\Models\User;
use App\Services\PlantillaDemo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlantillaDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_seeder_carga_la_misma_plantilla_y_se_puede_repetir(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, User::query()->where('email', 'marta.ruiz@abaco.test')->count());
        $this->assertSame(1, User::query()->where('email', 'ana.lopez@abaco.test')->count());
        $this->assertSame(1, User::query()->where('email', 'lucia.vega@abaco.test')->count());
        $this->assertSame(1, User::query()->where('email', 'carmen.ortega@abaco.test')->count());

        $lucia = User::query()->where('email', 'lucia.vega@abaco.test')->first();
        $this->assertSame('ES-CT', $lucia->centro);
        $this->assertSame('Barcelona', $lucia->municipality);
        $this->assertSame('trabajadora', $lucia->role);
    }

    public function test_el_seeder_no_borra_fichajes_ni_cambia_la_clave(): void
    {
        $this->seed();
        $ana = User::query()->where('email', 'ana.lopez@abaco.test')->first();
        $ana->update(['password' => 'ClaveQueYaTenia']);
        Jornada::create(['user_id' => $ana->id, 'work_date' => '2026-10-07']);

        $this->seed();

        $this->assertTrue($ana->fresh()->jornadas()->whereDate('work_date', '2026-10-07')->exists());
        $this->assertTrue(Hash::check('ClaveQueYaTenia', $ana->fresh()->password));
    }

    public function test_un_alta_entra_en_la_plantilla_y_aparece_al_sembrar_otra_base(): void
    {
        $archivo = storage_path('framework/testing/personas-alta.json');
        $origen = database_path('seeders/data/personas.json');
        File::ensureDirectoryExists(dirname($archivo));
        copy($origen, $archivo);
        $plantilla = new PlantillaDemo($archivo);

        $nuria = User::create([
            'name' => 'Nuria Vega',
            'email' => 'nuria.vega@abaco.test',
            'password' => 'Jornada2026',
            'role' => 'trabajadora',
            'active' => true,
            'starts_on' => '2026-10-13',
            'municipality' => 'Sevilla',
            'centro' => 'ES-AN',
        ]);
        $plantilla->registrarAlta($nuria);

        User::query()->delete();
        $plantilla->aplicar();

        $alta = User::query()->where('email', 'nuria.vega@abaco.test')->first();
        $this->assertNotNull($alta);
        $this->assertSame('ES-AN', $alta->centro);
        $this->assertSame('Sevilla', $alta->municipality);
        unlink($archivo);
    }
}
