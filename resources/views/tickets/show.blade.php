@extends('layouts.app', ['titulo' => 'Ticket'])

@section('contenido')
    <p>
        <a class="enlace" href="{{ $esTecnico ? route('tecnico.tickets') : route('tickets.index') }}">
            {{ $esTecnico ? 'Volver a los tickets' : 'Volver a Ayuda' }}
        </a>
    </p>
    <h1>{{ $ticket->asunto }}</h1>
    <p class="secundario">
        {{ $ticket->etiquetaDeEstado() }}
        · Abierto por {{ $ticket->autor->name }}
        el {{ \App\Support\Tiempo::fecha($ticket->created_at) }} a las {{ $ticket->created_at->format('H:i') }}
        @if ($ticket->pagina)
            · desde {{ $ticket->pagina }}
        @endif
    </p>

    <article class="tarjeta">
        <p>{!! nl2br(e($ticket->descripcion)) !!}</p>
    </article>

    <h2>Conversación</h2>
    @if ($ticket->mensajes->isEmpty())
        <p class="secundario">Todavía nadie ha contestado.</p>
    @else
        <ul class="lista">
            @foreach ($ticket->mensajes as $mensaje)
                <li class="{{ $mensaje->autor->esTecnico() ? 'mensaje-tecnico' : '' }}">
                    <span>
                        <strong>{{ $mensaje->autor->name }}</strong>
                        @if ($mensaje->autor->esTecnico())
                            · técnico
                        @endif
                    </span>
                    <span>{!! nl2br(e($mensaje->cuerpo)) !!}</span>
                    <span class="secundario">{{ \App\Support\Tiempo::fecha($mensaje->created_at) }} {{ $mensaje->created_at->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <form method="post" action="{{ route('tickets.mensaje', $ticket) }}" class="formulario">
        @csrf
        <label for="cuerpo">{{ $ticket->estaResuelto() ? 'Si el problema sigue, escribe aquí. Se reabrirá.' : 'Escribe un mensaje' }}</label>
        <textarea id="cuerpo" name="cuerpo" maxlength="2000" required>{{ old('cuerpo') }}</textarea>
        <button class="boton boton-principal" type="submit">Enviar mensaje</button>
    </form>

    @if ($esTecnico)
        <h2>Estado</h2>
        <form method="post" action="{{ route('tecnico.tickets.estado', $ticket) }}" class="formulario">
            @csrf
            <label for="estado">Cambiar el estado</label>
            <select id="estado" name="estado" required>
                @foreach (\App\Models\Ticket::ESTADOS as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected($ticket->estado === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <button class="boton boton-secundario" type="submit">Guardar estado</button>
        </form>
    @endif
@endsection
