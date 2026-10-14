<?php

namespace Tests\Feature;

use App\Mail\TicketNuevo;
use App\Mail\TicketResuelto;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_cualquier_rol_puede_abrir_un_ticket_y_avisa_al_tecnico(): void
    {
        $tecnico = User::factory()->tecnico()->create();

        foreach ([User::factory()->create(), User::factory()->jefa()->create()] as $persona) {
            $this->actingAs($persona)->post(route('tickets.store'), [
                'asunto' => 'No me deja fichar',
                'descripcion' => 'Al pulsar Empezar no pasa nada.',
                'desde' => '/jornada',
            ])->assertRedirect();

            $ticket = Ticket::where('user_id', $persona->id)->firstOrFail();
            $this->assertSame(Ticket::ABIERTO, $ticket->estado);
            $this->assertSame('/jornada', $ticket->pagina);
        }

        Mail::assertSent(TicketNuevo::class, 2);
        Mail::assertSent(TicketNuevo::class, fn (TicketNuevo $correo) => $correo->hasTo($tecnico->email));
    }

    public function test_el_tecnico_que_abre_un_ticket_no_se_avisa_a_si_mismo(): void
    {
        $tecnico = User::factory()->tecnico()->create();

        $this->actingAs($tecnico)->post(route('tickets.store'), [
            'asunto' => 'Prueba',
            'descripcion' => 'Ticket de prueba del técnico.',
        ])->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_si_el_correo_falla_el_ticket_se_guarda(): void
    {
        User::factory()->tecnico()->create();
        $ana = User::factory()->create();
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('sin conexión'));

        $this->actingAs($ana)->post(route('tickets.store'), [
            'asunto' => 'Fallo',
            'descripcion' => 'Algo ha fallado en la pantalla.',
        ])->assertRedirect();

        $this->assertSame(1, Ticket::where('user_id', $ana->id)->count());
    }

    public function test_el_formulario_rechaza_datos_no_validos(): void
    {
        $ana = User::factory()->create();

        $this->actingAs($ana)->post(route('tickets.store'), [
            'asunto' => '',
            'descripcion' => 'corto',
        ])->assertSessionHasErrors(['asunto', 'descripcion']);

        $this->actingAs($ana)->post(route('tickets.store'), [
            'asunto' => "Dos\nlíneas",
            'descripcion' => 'Una descripción suficiente.',
        ])->assertSessionHasErrors('asunto');

        $this->assertSame(0, Ticket::count());
    }

    public function test_la_pagina_de_origen_solo_se_guarda_si_es_de_la_aplicacion(): void
    {
        $ana = User::factory()->create();

        foreach (['https://externa.example/x', '//externa.example/x'] as $externa) {
            $this->actingAs($ana)->post(route('tickets.store'), [
                'asunto' => 'Origen',
                'descripcion' => 'Probando la página de origen.',
                'desde' => $externa,
            ]);
        }

        $this->assertSame(2, Ticket::whereNull('pagina')->count());
    }

    public function test_solo_el_autor_y_el_tecnico_ven_un_ticket(): void
    {
        $ana = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $ana->id]);

        $this->actingAs($ana)->get(route('tickets.show', $ticket))->assertOk();
        $this->actingAs(User::factory()->tecnico()->create())->get(route('tickets.show', $ticket))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('tickets.show', $ticket))->assertNotFound();
        $this->actingAs(User::factory()->jefa()->create())->get(route('tickets.show', $ticket))->assertNotFound();
        $this->actingAs(User::factory()->create())
            ->post(route('tickets.mensaje', $ticket), ['cuerpo' => 'Hola'])->assertNotFound();
    }

    public function test_cada_persona_solo_ve_sus_tickets_en_ayuda(): void
    {
        $ana = User::factory()->create();
        Ticket::factory()->create(['user_id' => $ana->id, 'asunto' => 'Asunto de Ana']);
        Ticket::factory()->create(['asunto' => 'Asunto de otra persona']);

        $this->actingAs($ana)->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Asunto de Ana')
            ->assertDontSee('Asunto de otra persona');
    }

    public function test_la_conversacion_cambia_el_estado(): void
    {
        $ana = User::factory()->create();
        $tecnico = User::factory()->tecnico()->create();
        $ticket = Ticket::factory()->create(['user_id' => $ana->id]);

        $this->actingAs($tecnico)->post(route('tickets.mensaje', $ticket), ['cuerpo' => 'Lo miro ahora.']);
        $this->assertSame(Ticket::EN_CURSO, $ticket->fresh()->estado);

        $this->actingAs($ana)->post(route('tickets.mensaje', $ticket), ['cuerpo' => 'Gracias.']);
        $this->assertSame(Ticket::EN_CURSO, $ticket->fresh()->estado);
        $this->assertSame(2, $ticket->mensajes()->count());

        $this->actingAs($tecnico)->post(route('tecnico.tickets.estado', $ticket), ['estado' => Ticket::RESUELTO]);
        $this->assertTrue($ticket->fresh()->estaResuelto());
        $this->assertNotNull($ticket->fresh()->resuelto_at);

        $this->actingAs($ana)->post(route('tickets.mensaje', $ticket), ['cuerpo' => 'Sigue fallando.']);
        $this->assertSame(Ticket::ABIERTO, $ticket->fresh()->estado);
        $this->assertNull($ticket->fresh()->resuelto_at);
    }

    public function test_al_resolver_se_avisa_al_autor_una_sola_vez(): void
    {
        $ana = User::factory()->create();
        $tecnico = User::factory()->tecnico()->create();
        $ticket = Ticket::factory()->create(['user_id' => $ana->id]);

        $this->actingAs($tecnico)->post(route('tecnico.tickets.estado', $ticket), ['estado' => Ticket::RESUELTO]);
        $this->actingAs($tecnico)->post(route('tecnico.tickets.estado', $ticket), ['estado' => Ticket::RESUELTO]);

        Mail::assertSent(TicketResuelto::class, 1);
        Mail::assertSent(TicketResuelto::class, fn (TicketResuelto $correo) => $correo->hasTo($ana->email));
    }

    public function test_solo_el_tecnico_cambia_el_estado_y_ve_todos_los_tickets(): void
    {
        $ana = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $ana->id]);

        $this->actingAs($ana)->post(route('tecnico.tickets.estado', $ticket), ['estado' => Ticket::RESUELTO])
            ->assertRedirect(route('jornada'));
        $this->actingAs($ana)->get(route('tecnico.tickets'))->assertRedirect(route('jornada'));
        $this->actingAs(User::factory()->jefa()->create())->get(route('tecnico.tickets'))
            ->assertRedirect(route('jornada'));
        $this->assertSame(Ticket::ABIERTO, $ticket->fresh()->estado);

        $this->actingAs(User::factory()->tecnico()->create())
            ->get(route('tecnico.tickets'))
            ->assertOk()
            ->assertSee($ticket->asunto);
    }

    public function test_el_tecnico_filtra_los_tickets_por_estado(): void
    {
        $tecnico = User::factory()->tecnico()->create();
        Ticket::factory()->create(['asunto' => 'Pendiente uno']);
        Ticket::factory()->resuelto()->create(['asunto' => 'Cerrado uno']);

        $this->actingAs($tecnico)->get(route('tecnico.tickets'))
            ->assertSee('Pendiente uno')->assertDontSee('Cerrado uno');
        $this->actingAs($tecnico)->get(route('tecnico.tickets', ['estado' => 'resuelto']))
            ->assertSee('Cerrado uno')->assertDontSee('Pendiente uno');
        $this->actingAs($tecnico)->get(route('tecnico.tickets', ['estado' => 'todos']))
            ->assertSee('Pendiente uno')->assertSee('Cerrado uno');
    }

    public function test_un_estado_inexistente_se_rechaza(): void
    {
        $ticket = Ticket::factory()->create();

        $this->actingAs(User::factory()->tecnico()->create())
            ->post(route('tecnico.tickets.estado', $ticket), ['estado' => 'inventado'])
            ->assertSessionHasErrors('estado');
    }
}
