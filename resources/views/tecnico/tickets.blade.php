@extends('layouts.app', ['titulo' => 'Tickets'])

@section('contenido')
    <a class="volver" href="{{ route('tecnico.index') }}">Volver a Sistema</a>
    <h1>Tickets</h1>
    <p class="secundario">Los problemas que cuenta el equipo. {{ $pendientes === 1 ? 'Hay 1 sin resolver.' : 'Hay '.$pendientes.' sin resolver.' }}</p>

    <nav class="tickets-filtros" aria-label="Filtrar tickets">
        <a class="tickets-filtro" href="{{ route('tecnico.tickets') }}" @if ($filtro === 'pendientes') aria-current="page" @endif>Sin resolver</a>
        <a class="tickets-filtro" href="{{ route('tecnico.tickets', ['estado' => 'resuelto']) }}" @if ($filtro === 'resuelto') aria-current="page" @endif>Resueltos</a>
        <a class="tickets-filtro" href="{{ route('tecnico.tickets', ['estado' => 'todos']) }}" @if ($filtro === 'todos') aria-current="page" @endif>Todos</a>
    </nav>

    @if ($tickets->isEmpty())
        <p class="tickets-vacio">
            @if ($filtro === 'pendientes')
                No hay tickets sin resolver.
            @elseif ($filtro === 'resuelto')
                Todavía no hay tickets resueltos.
            @else
                Todavía no hay tickets.
            @endif
        </p>
    @else
        <ul class="lista lista-tickets">
            @foreach ($tickets as $ticket)
                <li>
                    <a href="{{ route('tickets.show', $ticket) }}">
                        <span>{{ $ticket->asunto }}</span>
                        <strong class="pastilla pastilla-{{ $ticket->estado }}">{{ $ticket->etiquetaDeEstado() }}</strong>
                    </a>
                    <p class="tickets-extracto">{{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', $ticket->descripcion), 110) }}</p>
                    <span class="secundario">
                        {{ $ticket->autor->name }}
                        · {{ \App\Support\Tiempo::fecha($ticket->created_at) }} {{ $ticket->created_at->format('H:i') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
