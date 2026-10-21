@extends('layouts.app', ['titulo' => 'Incidencias'])

@section('contenido')
    <h1>Incidencias</h1>
    <p class="secundario">Las que envía el equipo y las que envías tú a una persona. Pulsa una para ver el detalle.</p>
    <p><button class="boton boton-principal" type="button" data-dialogo-abre="comunicar">Comunicar incidencia</button></p>

    <dialog class="historial-ventana dialogo-comunicar" data-dialogo="comunicar" @if ($errors->has('destinatario') || $errors->has('motivo')) data-abierto @endif>
        <div class="historial-ventana-caja">
            <header class="comunicar-panel-cabeza">
                <h2>Comunicar incidencia</h2>
                <button class="historial-cerrar" type="button" data-dialogo-cerrar aria-label="Cerrar">✕</button>
            </header>
            <form method="post" action="{{ route('jornada.incidencia') }}" class="formulario comunicar-formulario">
                @csrf
                <label for="incidencia-destinatario">Para</label>
                <select id="incidencia-destinatario" name="destinatario" required>
                    <option value="">Elige una persona</option>
                    @foreach ($equipo as $persona)
                        <option value="{{ $persona->id }}" @selected((string) old('destinatario') === (string) $persona->id)>{{ $persona->name }}</option>
                    @endforeach
                </select>
                <label for="incidencia-motivo">Qué pasó</label>
                <textarea id="incidencia-motivo" name="motivo" required placeholder="0 a 500 caracteres" data-max="500" data-vacio="Escribe el motivo de la incidencia." data-largo="El motivo puede tener hasta 500 caracteres.">{{ old('motivo') }}</textarea>
                <button class="boton boton-principal" type="submit">Enviar incidencia</button>
            </form>
        </div>
    </dialog>

    <section class="bandeja-incidencias">
        <form method="get" class="filtros-incidencia" action="{{ route('incidencias.index') }}">
            <div class="campo-incidencia">
                <label for="persona">Persona</label>
                <select id="persona" name="persona">
                    <option value="">Todas</option>
                    @foreach ($personas as $persona)
                        <option value="{{ $persona->id }}" @selected($personaId === $persona->id)>{{ $persona->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo-incidencia">
                <label for="desde">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ $desde }}">
            </div>
            <div class="campo-incidencia">
                <label for="hasta">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ $hasta }}">
            </div>
            <button class="boton boton-secundario" type="submit">Ver</button>
        </form>
        <a class="boton-hoy @if ($desde === $hoy && $hasta === $hoy && $personaId === null) boton-hoy-activo @endif" href="{{ route('incidencias.index', ['desde' => $hoy, 'hasta' => $hoy]) }}">Hoy</a>
    </section>

    @if ($incidencias->isEmpty())
        <p class="incidencia-vacia">No hay incidencias.</p>
    @else
        <ul class="lista incidencias-lista">
            @foreach ($incidencias as $incidencia)
                <li>
                    <a href="{{ route('incidencias.show', $incidencia) }}">
                        <span class="incidencia-cabeza">
                            <span class="incidencia-quien">
                                <span class="incidencia-inicial" aria-hidden="true">{{ mb_substr($incidencia->autor->name, 0, 1) }}</span>
                                <span>
                                    {{ $incidencia->autor->name }}
                                    @if ($incidencia->destinatario)
                                        · Para {{ $incidencia->destinatario->name }}
                                    @endif
                                </span>
                            </span>
                            <x-chip :tipo="$incidencia->estado">{{ $incidencia->estadoTexto() }}</x-chip>
                        </span>
                        <span class="incidencia-texto">{{ \Illuminate\Support\Str::limit($incidencia->cuerpo, 110) }}</span>
                        <span class="secundario">{{ \App\Support\Tiempo::fecha($incidencia->created_at) }} {{ $incidencia->created_at->format('H:i') }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
