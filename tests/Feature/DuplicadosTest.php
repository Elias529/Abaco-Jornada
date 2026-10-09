<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_ausencia_repetida_se_avisa_y_no_se_duplica(): void
    {
        $responsable = User::factory()->create(['role' => 'responsable']);
        $ana = User::factory()->create();
        $datos = ['absence_date' => '2026-10-20', 'tipo' => 'permiso'];

        $this->actingAs($responsable)->post(route('equipo.ausencia', $ana), $datos)->assertSessionHas('ok');
        $this->actingAs($responsable)->post(route('equipo.ausencia', $ana), $datos)
            ->assertSessionHas('aviso', 'Ese día ya tiene una ausencia.');

        $this->assertDatabaseCount('ausencias', 1);
    }

    public function test_un_festivo_repetido_se_avisa_y_no_se_duplica(): void
    {
        $responsable = User::factory()->create(['role' => 'responsable']);
        $datos = ['holiday_date' => '2026-12-09', 'name' => 'Festivo de prueba'];

        $this->actingAs($responsable)->post(route('calendario.store'), $datos)->assertSessionHas('ok');
        $this->actingAs($responsable)->post(route('calendario.store'), $datos)
            ->assertSessionHas('aviso', 'Ese festivo ya está cargado.');

        $this->assertDatabaseCount('festivos', 1);
    }
}
