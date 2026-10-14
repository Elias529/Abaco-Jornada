@extends('layouts.app', ['titulo' => 'Ayuda'])

@section('contenido')
    <h1>Ayuda</h1>
    <p class="secundario">¿Algo no funciona? Abre un ticket y el técnico lo verá. Solo tú y el técnico veis tus tickets.</p>

    <p>
        <a class="boton boton-principal" href="{{ route('tickets.create', ['desde' => $desde]) }}">Abrir un ticket</a>
    </p>

    <h2>Mis tickets</h2>
    @if ($tickets->isEmpty())
        <p>Todavía no has abierto ningún ticket.</p>
    @else
        <ul class="lista">
            @foreach ($tickets as $ticket)
                <li>
                    <a href="{{ route('tickets.show', $ticket) }}">
                        <span>{{ $ticket->asunto }}</span>
                        <strong>{{ $ticket->etiquetaDeEstado() }}</strong>
                    </a>
                    <span class="secundario">{{ \App\Support\Tiempo::fecha($ticket->created_at) }} {{ $ticket->created_at->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
