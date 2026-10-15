@extends('layouts.app', ['titulo' => 'Tickets'])

@section('contenido')
    <p><a class="enlace" href="{{ route('tecnico.index') }}">Volver a Sistema</a></p>
    <h1>Tickets</h1>
    <p class="secundario">Los problemas que cuenta el equipo. Hay {{ $pendientes }} sin resolver.</p>

    <nav class="bandeja-incidencias filtros-ticket" aria-label="Filtrar tickets">
        <a class="{{ $filtro === 'pendientes' ? 'boton-hoy' : 'filtro-ticket' }}" href="{{ route('tecnico.tickets') }}" @if ($filtro === 'pendientes') aria-current="page" @endif>Sin resolver</a>
        <a class="{{ $filtro === 'resuelto' ? 'boton-hoy' : 'filtro-ticket' }}" href="{{ route('tecnico.tickets', ['estado' => 'resuelto']) }}" @if ($filtro === 'resuelto') aria-current="page" @endif>Resueltos</a>
        <a class="{{ $filtro === 'todos' ? 'boton-hoy' : 'filtro-ticket' }}" href="{{ route('tecnico.tickets', ['estado' => 'todos']) }}" @if ($filtro === 'todos') aria-current="page" @endif>Todos</a>
    </nav>

    @if ($tickets->isEmpty())
        <p class="incidencia-vacia">No hay tickets aquí.</p>
    @else
        <ul class="lista lista-incidencias">
            @foreach ($tickets as $ticket)
                <li class="incidencia-fila">
                    <a href="{{ route('tickets.show', $ticket) }}">
                        <span>{{ $ticket->asunto }}</span>
                        <strong class="pastilla pastilla-{{ $ticket->estado }}">{{ $ticket->etiquetaDeEstado() }}</strong>
                    </a>
                    <p class="incidencia-texto">{{ \Illuminate\Support\Str::limit($ticket->descripcion, 110) }}</p>
                    <span class="secundario">
                        {{ $ticket->autor->name }}
                        · {{ \App\Support\Tiempo::fecha($ticket->created_at) }} {{ $ticket->created_at->format('H:i') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
