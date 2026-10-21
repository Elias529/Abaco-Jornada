@extends('layouts.app', ['titulo' => 'Ayuda'])

@section('contenido')
    <h1>Ayuda</h1>
    <p class="secundario">¿Algo no funciona? Abre un ticket y el técnico lo verá. Solo tú y el técnico veis tus tickets.</p>

    <section class="bandeja-incidencias">
        <p class="incidencia-texto">Cuenta qué ha pasado. El técnico lo recibe con tu nombre y puede contestarte aquí.</p>
        <a class="boton-hoy" href="{{ route('tickets.create', ['desde' => $desde]) }}">Abrir un ticket</a>
    </section>

    <h2>Mis tickets</h2>
    @if ($tickets->isEmpty())
        <p class="incidencia-vacia">Todavía no has abierto ningún ticket.</p>
    @else
        <ul class="lista lista-incidencias">
            @foreach ($tickets as $ticket)
                <li class="incidencia-fila">
                    <a href="{{ route('tickets.show', $ticket) }}">
                        <span>{{ $ticket->asunto }}</span>
                        <x-chip :tipo="$ticket->estado">{{ $ticket->etiquetaDeEstado() }}</x-chip>
                    </a>
                    <p class="incidencia-texto">{{ \Illuminate\Support\Str::limit($ticket->descripcion, 110) }}</p>
                    <span class="secundario">{{ \App\Support\Tiempo::fecha($ticket->created_at) }} {{ $ticket->created_at->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
