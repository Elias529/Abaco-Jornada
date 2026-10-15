@extends('layouts.app', ['titulo' => 'Incidencias'])

@section('contenido')
    <h1>Incidencias</h1>
    <p class="secundario">Lo que el equipo envía desde Hoy. Pulsa una para ver el detalle.</p>

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
        <ul class="lista lista-incidencias">
            @foreach ($incidencias as $incidencia)
                <li class="incidencia-fila">
                    <a href="{{ route('incidencias.show', $incidencia) }}">
                        <span>{{ $incidencia->autor->name }}</span>
                        <strong class="incidencia-marca">Incidencia</strong>
                    </a>
                    <p class="incidencia-texto">{{ \Illuminate\Support\Str::limit($incidencia->cuerpo, 110) }}</p>
                    <span class="secundario">{{ \App\Support\Tiempo::fecha($incidencia->created_at) }} {{ $incidencia->created_at->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
