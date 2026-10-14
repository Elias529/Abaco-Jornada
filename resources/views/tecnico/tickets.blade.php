@extends('layouts.app', ['titulo' => 'Tickets'])

@section('contenido')
    <p><a class="enlace" href="{{ route('tecnico.index') }}">Volver a Sistema</a></p>
    <h1>Tickets</h1>
    <p class="secundario">Los problemas que cuenta el equipo. Hay {{ $pendientes }} sin resolver.</p>

    <nav class="paginas" aria-label="Filtrar tickets">
        <a href="{{ route('tecnico.tickets') }}" @if ($filtro === 'pendientes') aria-current="page" @endif>Sin resolver</a>
        <a href="{{ route('tecnico.tickets', ['estado' => 'resuelto']) }}" @if ($filtro === 'resuelto') aria-current="page" @endif>Resueltos</a>
        <a href="{{ route('tecnico.tickets', ['estado' => 'todos']) }}" @if ($filtro === 'todos') aria-current="page" @endif>Todos</a>
    </nav>

    @if ($tickets->isEmpty())
        <p>No hay tickets aquí.</p>
    @else
        <ul class="lista">
            @foreach ($tickets as $ticket)
                <li>
                    <a href="{{ route('tickets.show', $ticket) }}">
                        <span>{{ $ticket->asunto }}</span>
                        <strong>{{ $ticket->etiquetaDeEstado() }}</strong>
                    </a>
                    <span class="secundario">
                        {{ $ticket->autor->name }}
                        · {{ \App\Support\Tiempo::fecha($ticket->created_at) }} {{ $ticket->created_at->format('H:i') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
