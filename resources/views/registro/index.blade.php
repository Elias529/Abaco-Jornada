@extends('layouts.app', ['ancha' => true, 'titulo' => 'Registro'])

@section('contenido')
    @php
        $lista = $consulta ? route('equipo.registro', $persona) : route('registro');
        $copia = $consulta ? route('equipo.csv', array_merge(['user' => $persona], request()->query())) : route('registro.csv', request()->query());
    @endphp

    <div class="registro">
        <header class="registro-cabeza">
            <div>
                <p class="espacio-sobre">Mi jornada</p>
                <h1>{{ $consulta ? 'Registro de '.$persona->name : 'Mi registro' }}</h1>
                <p class="secundario">{{ $consulta ? 'Revisa los días de '.$persona->name.' y abre uno para consultar sus tramos.' : 'Revisa los días y abre uno para consultar sus tramos.' }}</p>
            </div>
            <a class="registro-csv" href="{{ $copia }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v10M8 10l4 4 4-4M5 19h14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Descargar CSV
            </a>
        </header>

        <div class="registro-barra">
            <div class="registro-tabs" role="tablist">
                @foreach (['semana' => 'Esta semana', 'mes' => 'Este mes', 'anterior' => 'Mes anterior'] as $clave => $etiqueta)
                    <a href="{{ $lista }}?periodo={{ $clave }}" @if ($periodo === $clave) aria-current="page" @endif>{{ $etiqueta }}</a>
                @endforeach
            </div>
            <p class="registro-rango">{{ $rango }}</p>
        </div>

        <details class="registro-filtrar" @if ($buscado && $periodo !== 'semana' && $periodo !== 'mes' && $periodo !== 'anterior') open @endif>
            <summary>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h5M15 6h5M4 12h7M17 12h3M4 18h2M10 18h10" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="6" r="2.1" fill="currentColor"/><circle cx="14.5" cy="12" r="2.1" fill="currentColor"/><circle cx="7.5" cy="18" r="2.1" fill="currentColor"/></svg>
                Filtrar
            </summary>
            <form method="get" action="{{ $lista }}" class="registro-filtros">
                <input type="hidden" name="periodo" value="todo">
                <div>
                    <label for="desde">Desde</label>
                    <input id="desde" name="desde" type="date" value="{{ $periodo === 'todo' ? $desde : '' }}">
                </div>
                <div>
                    <label for="hasta">Hasta</label>
                    <input id="hasta" name="hasta" type="date" value="{{ $periodo === 'todo' ? $hasta : '' }}">
                </div>
                <div>
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="cerrada" @selected($estado === 'cerrada')>Cerrada</option>
                        <option value="pendiente" @selected($estado === 'pendiente')>Abierta</option>
                        <option value="tardia" @selected($estado === 'tardia')>Cierre al día siguiente</option>
                    </select>
                </div>
                <button class="boton boton-secundario" type="submit">Ver</button>
            </form>
        </details>

        @if ($propio && $pendientes !== [])
            <section class="registro-pendientes" id="pendientes">
                <h2>Pendiente de explicar</h2>
                <p class="secundario">Si un día no tiene fichaje, no sale en la lista. Explícalo aquí.</p>
                @foreach ($pendientes as $pendiente)
                    <form class="registro-pendiente" method="post" action="{{ route('registro.explicar') }}">
                        @csrf
                        <input type="hidden" name="work_date" value="{{ $pendiente['fecha'] }}">
                        <p>El {{ $pendiente['dia'] }} {{ $pendiente['texto'] }}.</p>
                        <label for="explicacion-{{ $pendiente['fecha'] }}">Qué pasó</label>
                        <textarea id="explicacion-{{ $pendiente['fecha'] }}" name="body" rows="2" maxlength="500" required>{{ old('work_date') === $pendiente['fecha'] ? old('body') : '' }}</textarea>
                        <button class="boton boton-secundario" type="submit">Explicar</button>
                    </form>
                @endforeach
            </section>
        @endif

        <section class="registro-card">
            @if ($jornadas->isEmpty())
                <p class="registro-vacio">No hay jornadas en este periodo.</p>
            @else
                <div class="registro-tabla" role="table">
                    <div class="registro-cabecera" role="row">
                        <span role="columnheader">Fecha</span>
                        <span role="columnheader">Tramos de trabajo</span>
                        <span role="columnheader">Jornada</span>
                        <span role="columnheader">Total</span>
                        <span role="columnheader">Estado</span>
                        <span role="columnheader"></span>
                    </div>
                    @foreach ($jornadas as $jornada)
                        @php($fila = $tabla->presentacion($persona, $jornada))
                        <a class="registro-fila" role="row" href="{{ route('registro.show', $jornada) }}">
                            <span role="cell">{{ $fila['fecha'] }}</span>
                            <span role="cell" class="registro-tramos">{{ $fila['tramos'] }}</span>
                            <span role="cell" class="registro-barras" aria-hidden="true">
                                @foreach ($fila['barras'] as $barra)
                                    <i style="left: {{ $barra['left'] }}%; width: {{ $barra['width'] }}%;"></i>
                                @endforeach
                            </span>
                            <span role="cell" class="registro-total">{{ $fila['total'] }}</span>
                            <span role="cell" class="registro-estados">
                                <span class="registro-pill registro-pill-{{ $fila['clase'] }}">{{ $fila['estado'] }}</span>
                                @if ($fila['incidencia'])
                                    <span class="registro-pill registro-pill-incidencia">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4" width="12" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9 9h6M9 13h6M9 17h3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                        Incidencia
                                    </span>
                                @endif
                            </span>
                            <span role="cell" class="registro-flecha" aria-hidden="true">›</span>
                        </a>
                    @endforeach
                </div>
            @endif
            <div class="registro-pie">
                <span>{{ $jornadas->total() === 1 ? '1 jornada' : $jornadas->total().' jornadas' }}</span>
                <span>Las correcciones conservan el historial</span>
            </div>
        </section>

        @if ($jornadas->hasPages())
            <p class="paginas">
                @if ($jornadas->previousPageUrl())
                    <a href="{{ $jornadas->previousPageUrl() }}">Días más recientes</a>
                @endif
                <span>Página {{ $jornadas->currentPage() }} de {{ $jornadas->lastPage() }}</span>
                @if ($jornadas->nextPageUrl())
                    <a href="{{ $jornadas->nextPageUrl() }}">Días anteriores</a>
                @endif
            </p>
        @endif

        @if ($explicaciones->isNotEmpty())
            <h2>Explicaciones</h2>
            <ul class="lista lista-incidencias">
                @foreach ($explicaciones as $explicacion)
                    <li class="incidencia-fila">
                        <span>{{ $explicacion->body }}</span>
                        <span class="secundario">{{ \App\Support\Tiempo::fecha($explicacion->created_at) }}@if ($explicacion->work_date) · {{ \App\Support\Tiempo::fecha($explicacion->work_date) }}@endif</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($consulta && $consultas->isNotEmpty())
            <h2>Consultas</h2>
            <ul class="lista lista-incidencias">
                @foreach ($consultas as $visita)
                    <li class="incidencia-fila">
                        <span>{{ $visita->viewer->name }}@if ($visita->reason). {{ $visita->reason }}@endif</span>
                        <span class="secundario">{{ \App\Support\Tiempo::fecha($visita->created_at) }} {{ $visita->created_at->format('H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
