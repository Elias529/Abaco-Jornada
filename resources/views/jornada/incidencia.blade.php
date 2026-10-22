@extends('layouts.app', ['titulo' => 'Comunicar incidencia'])

@section('contenido')
    <div class="comunicar">
        <header class="comunicar-cabecera">
            <a class="volver" href="{{ route('jornada') }}">Volver a hoy</a>
            <h1>Comunicar incidencia</h1>
            <button class="historial-abre" type="button" data-historial-abre>Historial de incidencias</button>
        </header>

        <section class="comunicar-panel">
            <header class="comunicar-panel-cabeza">
                <h2>Abrir incidencia</h2>
            </header>
            <p class="comunicar-ayuda">Cuenta qué ha pasado. La jefa lo verá en Incidencias.</p>
            <form method="post" action="{{ route('jornada.incidencia') }}" class="formulario comunicar-formulario">
                @csrf
                <label for="incidencia-motivo">Qué pasó</label>
                <textarea id="incidencia-motivo" name="motivo" required minlength="{{ \App\Models\Incidencia::CUERPO_MINIMO }}" maxlength="{{ \App\Models\Incidencia::CUERPO_MAXIMO }}" placeholder="{{ \App\Models\Incidencia::CUERPO_MINIMO }} a {{ \App\Models\Incidencia::CUERPO_MAXIMO }} caracteres" data-min="{{ \App\Models\Incidencia::CUERPO_MINIMO }}" data-max="{{ \App\Models\Incidencia::CUERPO_MAXIMO }}" data-vacio="Escribe el motivo de la incidencia." data-corto="El motivo tiene que tener al menos {{ \App\Models\Incidencia::CUERPO_MINIMO }} caracteres." data-largo="El motivo puede tener hasta {{ \App\Models\Incidencia::CUERPO_MAXIMO }} caracteres.">{{ old('motivo') }}</textarea>
                <button class="boton boton-principal" type="submit">Enviar Incidencia</button>
            </form>
        </section>
    </div>

    <dialog class="historial-ventana" data-historial @if ($seleccion || request()->has('historial')) data-abierto @endif>
        <div class="historial-ventana-caja">
            <header class="comunicar-panel-cabeza">
                <h2>{{ $seleccion ? 'Chat' : 'Historial de incidencias' }}</h2>
                @if ($seleccion)
                    <a class="comunicar-nueva" href="{{ route('jornada.incidencia.crear', ['historial' => 1]) }}">Volver al historial</a>
                @endif
                <button class="historial-cerrar" type="button" data-historial-cerrar aria-label="Cerrar">✕</button>
            </header>

            @if ($seleccion)
                @include('incidencias.chat', ['incidencia' => $seleccion, 'sinCabecera' => true])
            @elseif ($incidencias->isEmpty())
                <p class="chat-vacio">Todavía no has enviado ninguna.</p>
            @else
                <ul class="comunicar-lista">
                    @foreach ($incidencias as $incidencia)
                        <li>
                            <a class="estado-{{ $incidencia->estadoLista() }}" href="{{ route('jornada.incidencia.crear', ['incidencia' => $incidencia->id]) }}">
                                <span class="incidencia-texto">
                                    @if ($incidencia->user_id !== auth()->id())
                                        De {{ $incidencia->autor->name }}:
                                    @elseif ($incidencia->destinatario)
                                        Para {{ $incidencia->destinatario->name }}:
                                    @endif
                                    {{ $incidencia->cuerpo }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </dialog>
@endsection
