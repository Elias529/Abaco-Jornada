<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Correccion;
use App\Models\Horario;
use App\Models\Jornada;
use App\Models\Tramo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JefeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-07 10:45:00', 'Europe/Madrid'));
    }

    public function test_la_direccion_entra_en_su_panel_y_no_ficha(): void
    {
        $jefe = $this->jefe();

        $this->post(route('login'), ['email' => $jefe->email, 'password' => 'password'])
            ->assertRedirect(route('jefe.index'));

        $this->actingAs($jefe)->get(route('jornada'))->assertRedirect(route('jefe.index'));

        $this->actingAs($jefe)
            ->post(route('jornada.entrada'))
            ->assertRedirect(route('jefe.index'))
            ->assertSessionHas('aviso');

        $this->assertDatabaseCount('jornadas', 0);
    }

    public function test_el_panel_enseña_estado_y_horario_sin_crear_avisos(): void
    {
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $lucia = $this->persona('Lucía Vega', 'lucia.vega@abaco.test');
        $this->horario($ana);
        $this->horario($lucia);
        $jornada = Jornada::create(['user_id' => $lucia->id, 'work_date' => '2026-10-07']);
        Tramo::create([
            'jornada_id' => $jornada->id,
            'tipo' => Tramo::TRABAJO,
            'started_at' => '2026-10-07 09:00:00',
        ]);

        $respuesta = $this->actingAs($this->jefe())
            ->get(route('jefe.index'))
            ->assertOk()
            ->assertSee('Todavía no ha empezado')
            ->assertSee('Trabajando')
            ->assertSee('09:00–14:00 y 15:00–18:00');

        $this->assertCount(2, $respuesta->viewData('personas'));
        $this->assertDatabaseCount('avisos', 0);
        $this->assertDatabaseCount('jornadas', 1);
    }

    public function test_solo_la_direccion_entra_en_las_paginas_del_jefe(): void
    {
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $marta = $this->persona('Marta Ruiz', 'marta.ruiz@abaco.test', 'responsable');

        foreach ([$ana, $marta] as $persona) {
            $this->actingAs($persona)->get(route('jefe.index'))->assertRedirect(route('jornada'));
            $this->actingAs($persona)->get(route('jefe.registro', $ana))->assertRedirect(route('jornada'));
            $this->actingAs($persona)->get(route('jefe.consultas'))->assertRedirect(route('jornada'));
        }

        $this->assertDatabaseCount('consultas', 0);
    }

    public function test_la_direccion_no_puede_cambiar_horarios_horas_ni_accesos(): void
    {
        $jefe = $this->jefe();
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $this->horario($ana);
        $jornada = Jornada::create(['user_id' => $ana->id, 'work_date' => '2026-10-06']);
        $tramo = Tramo::create([
            'jornada_id' => $jornada->id,
            'tipo' => Tramo::TRABAJO,
            'started_at' => '2026-10-06 09:30:00',
            'ended_at' => '2026-10-06 18:00:00',
        ]);

        $cambios = [
            ['equipo.horario', [$ana], [
                'morning_start' => '08:00',
                'morning_end' => '14:00',
                'afternoon_start' => '15:00',
                'afternoon_end' => '17:00',
                'effective_from' => '2026-10-08',
            ]],
            ['equipo.ausencia', [$ana], ['absence_date' => '2026-10-20', 'tipo' => 'vacaciones']],
            ['equipo.baja', [$ana], []],
            ['tramos.corregir', [$tramo], ['campo' => 'started_at', 'hora' => '09:00', 'motivo' => 'hora']],
            ['calendario.fallo', [], ['falla_date' => '2026-10-07', 'note' => 'Caída']],
        ];

        foreach ($cambios as [$ruta, $parametros, $datos]) {
            $this->actingAs($jefe)
                ->post(route($ruta, $parametros), $datos)
                ->assertRedirect(route('jefe.index'));
        }

        $this->assertDatabaseCount('horarios', 1);
        $this->assertDatabaseCount('ausencias', 0);
        $this->assertDatabaseCount('correcciones', 0);
        $this->assertDatabaseCount('fallos_comunes', 0);
        $this->assertTrue($ana->fresh()->active);
        $this->assertSame('09:30', $tramo->fresh()->started_at->timezone('Europe/Madrid')->format('H:i'));
    }

    public function test_la_direccion_no_se_gestiona_desde_el_equipo(): void
    {
        $jefe = $this->jefe();
        $marta = $this->persona('Marta Ruiz', 'marta.ruiz@abaco.test', 'responsable');
        $this->persona('Ana López', 'ana.lopez@abaco.test');

        $respuesta = $this->actingAs($marta)->get(route('equipo.index'))->assertOk();
        $this->assertCount(2, $respuesta->viewData('personas'));

        $this->actingAs($marta)->get(route('equipo.show', $jefe))->assertNotFound();
        $this->actingAs($marta)->post(route('equipo.baja', $jefe))->assertNotFound();
        $this->assertTrue($jefe->fresh()->active);
    }

    public function test_el_registro_pide_motivo_y_la_consulta_queda_escrita(): void
    {
        $jefe = $this->jefe();
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        Jornada::create(['user_id' => $ana->id, 'work_date' => '2026-10-06']);

        $this->actingAs($jefe)
            ->get(route('jefe.registro', $ana))
            ->assertSee('hace falta un motivo')
            ->assertDontSee('Descargar copia');

        $this->actingAs($jefe)
            ->post(route('jefe.motivo', $ana), ['reason' => ''])
            ->assertSessionHasErrors('reason');
        $this->assertDatabaseCount('consultas', 0);

        $this->actingAs($jefe)
            ->post(route('jefe.motivo', $ana), ['reason' => 'Revisión del artículo 34.9'])
            ->assertRedirect(route('jefe.registro', $ana));

        $this->actingAs($jefe)
            ->get(route('jefe.registro', $ana))
            ->assertOk()
            ->assertSee('Descargar copia');

        $this->assertDatabaseHas('consultas', [
            'viewer_id' => $jefe->id,
            'subject_id' => $ana->id,
            'reason' => 'Revisión del artículo 34.9',
        ]);
    }

    public function test_el_motivo_de_una_persona_no_abre_el_registro_de_otra(): void
    {
        $jefe = $this->jefe();
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $lucia = $this->persona('Lucía Vega', 'lucia.vega@abaco.test');
        $jornadaDeLucia = Jornada::create(['user_id' => $lucia->id, 'work_date' => '2026-10-06']);

        $this->actingAs($jefe)->post(route('jefe.motivo', $ana), ['reason' => 'Revisión']);

        $this->actingAs($jefe)->get(route('jefe.registro', $lucia))->assertSee('hace falta un motivo');
        $this->actingAs($jefe)->get(route('jefe.dia', $jornadaDeLucia))->assertSee('hace falta un motivo');
        $this->actingAs($jefe)->get(route('jefe.csv', $lucia))->assertSee('hace falta un motivo');
    }

    public function test_el_dia_enseña_la_hora_anterior_y_no_ofrece_cambiarla(): void
    {
        $jefe = $this->jefe();
        $marta = $this->persona('Marta Ruiz', 'marta.ruiz@abaco.test', 'responsable');
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $jornada = Jornada::create(['user_id' => $ana->id, 'work_date' => '2026-10-06']);
        $tramo = Tramo::create([
            'jornada_id' => $jornada->id,
            'tipo' => Tramo::TRABAJO,
            'started_at' => '2026-10-06 09:00:00',
            'ended_at' => '2026-10-06 18:00:00',
        ]);
        Correccion::create([
            'tramo_id' => $tramo->id,
            'user_id' => $marta->id,
            'field' => 'started_at',
            'previous_value' => '2026-10-06 09:30:00',
            'new_value' => '2026-10-06 09:00:00',
            'reason' => 'hora',
        ]);

        $this->actingAs($jefe)->post(route('jefe.motivo', $ana), ['reason' => 'Reclamación']);

        $this->actingAs($jefe)
            ->get(route('jefe.dia', $jornada))
            ->assertOk()
            ->assertSee('Antes 09:30')
            ->assertSee('ahora 09:00')
            ->assertSee('Marta Ruiz')
            ->assertDontSee('Guardar corrección')
            ->assertDontSee(route('tramos.corregir', $tramo), false);

        $this->actingAs($jefe)
            ->get(route('jefe.registro', $ana))
            ->assertSee('con cambios');
    }

    public function test_el_mes_suma_lo_registrado_y_se_puede_elegir(): void
    {
        $jefe = $this->jefe();
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $this->horario($ana);
        $this->jornadaCerrada($ana, '2026-10-05', '09:00', '18:00');
        $this->jornadaCerrada($ana, '2026-10-06', '09:00', '17:00');

        $this->actingAs($jefe)->post(route('jefe.motivo', $ana), ['reason' => 'Revisión']);

        $respuesta = $this->actingAs($jefe)
            ->get(route('jefe.registro', $ana))
            ->assertSee('Octubre de 2026');

        $this->assertSame(
            ['registrados' => 1020, 'previstos' => 2400, 'porEncima' => 60, 'dias' => 2],
            $respuesta->viewData('resumen'),
        );

        $respuesta
            ->assertSee('Semana del 5 de octubre al 11 de octubre')
            ->assertSee('17 h 0 min');
        $this->assertCount(1, $respuesta->viewData('semanas'));

        $this->actingAs($jefe)
            ->get(route('jefe.registro', [$ana, 'mes' => '2026-09']))
            ->assertSee('Septiembre de 2026')
            ->assertSee('No hay jornadas en este mes.');

        foreach (['hola', '2020-01', '2027-01'] as $mesNoValido) {
            $this->actingAs($jefe)
                ->get(route('jefe.registro', [$ana, 'mes' => $mesNoValido]))
                ->assertSee('Octubre de 2026');
        }
    }

    public function test_un_dia_sin_cerrar_no_sigue_sumando_con_el_reloj(): void
    {
        $jefe = $this->jefe();
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $this->horario($ana);
        $jornada = Jornada::create(['user_id' => $ana->id, 'work_date' => '2026-10-06']);
        Tramo::create([
            'jornada_id' => $jornada->id,
            'tipo' => Tramo::TRABAJO,
            'started_at' => '2026-10-06 09:00:00',
        ]);

        $this->actingAs($jefe)->post(route('jefe.motivo', $ana), ['reason' => 'Revisión']);

        $respuesta = $this->actingAs($jefe)
            ->get(route('jefe.registro', $ana))
            ->assertSee('Pendiente de cerrar');

        $this->assertSame(0, $respuesta->viewData('resumen')['registrados']);

        $this->actingAs($jefe)
            ->get(route('jefe.index'))
            ->assertSee('Tiene un día anterior sin cerrar');
    }

    public function test_la_copia_csv_trae_el_historial_y_queda_tras_el_motivo(): void
    {
        $jefe = $this->jefe();
        $marta = $this->persona('Marta Ruiz', 'marta.ruiz@abaco.test', 'responsable');
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $jornada = Jornada::create(['user_id' => $ana->id, 'work_date' => '2026-10-06']);
        $tramo = Tramo::create([
            'jornada_id' => $jornada->id,
            'tipo' => Tramo::TRABAJO,
            'started_at' => '2026-10-06 09:00:00',
            'ended_at' => '2026-10-06 18:00:00',
        ]);
        Correccion::create([
            'tramo_id' => $tramo->id,
            'user_id' => $marta->id,
            'field' => 'started_at',
            'previous_value' => '2026-10-06 09:30:00',
            'new_value' => '2026-10-06 09:00:00',
            'reason' => 'hora',
        ]);

        $this->actingAs($jefe)->post(route('jefe.motivo', $ana), ['reason' => 'Inspección']);

        $copia = $this->actingAs($jefe)->get(route('jefe.csv', $ana))->assertOk()->streamedContent();

        $this->assertStringContainsString('Fecha;Inicio;Fin;"Inicio anterior"', $copia);
        $this->assertStringContainsString('2026-10-06;09:00;18:00;09:30', $copia);
        $this->assertStringContainsString('Marta Ruiz', $copia);
    }

    public function test_cada_persona_sigue_descargando_su_copia(): void
    {
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        $this->jornadaCerrada($ana, '2026-10-06', '09:00', '18:00');

        $copia = $this->actingAs($ana)->get(route('registro.csv'))->assertOk()->streamedContent();

        $this->assertStringContainsString('2026-10-06;09:00;18:00', $copia);
    }

    public function test_las_consultas_dicen_quien_miro_y_por_que(): void
    {
        $marta = $this->persona('Marta Ruiz', 'marta.ruiz@abaco.test', 'responsable');
        $ana = $this->persona('Ana López', 'ana.lopez@abaco.test');
        Consulta::create(['viewer_id' => $marta->id, 'subject_id' => $ana->id, 'reason' => 'Corregir un cierre']);

        $this->actingAs($this->jefe())
            ->get(route('jefe.consultas'))
            ->assertOk()
            ->assertSee('Marta Ruiz consultó el registro de Ana López')
            ->assertSee('Corregir un cierre');
    }

    private function jefe(): User
    {
        return User::factory()->jefe()->create([
            'name' => 'Carmen Ortega',
            'email' => 'carmen.ortega@abaco.test',
        ]);
    }

    private function persona(string $nombre, string $correo, string $papel = 'trabajadora'): User
    {
        return User::factory()->create([
            'name' => $nombre,
            'email' => $correo,
            'role' => $papel,
        ]);
    }

    private function horario(User $user): void
    {
        Horario::create([
            'user_id' => $user->id,
            'morning_start' => '09:00',
            'morning_end' => '14:00',
            'afternoon_start' => '15:00',
            'afternoon_end' => '18:00',
            'effective_from' => '2026-01-07',
        ]);
    }

    private function jornadaCerrada(User $user, string $dia, string $inicio, string $fin): void
    {
        $jornada = Jornada::create(['user_id' => $user->id, 'work_date' => $dia]);
        Tramo::create([
            'jornada_id' => $jornada->id,
            'tipo' => Tramo::TRABAJO,
            'started_at' => $dia.' '.$inicio.':00',
            'ended_at' => $dia.' '.$fin.':00',
        ]);
    }
}
