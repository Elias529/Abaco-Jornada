@extends('layouts.app', ['ancha' => true, 'titulo' => 'Registro'])

@section('contenido')
    <h1>{{ $consulta ? 'Registro de '.$persona->name : 'Mi registro' }}</h1>
    <p class="secundario">{{ $consulta ? 'Ves los registros de '.$persona->name.' y puedes sacar una copia.' : 'Ves los tuyos y puedes sacar una copia.' }} Se conservan cuatro años, el plazo del artículo 34.9 del Estatuto de los Trabajadores. Ábaco lo confirma. Una copia ya sacada no se reescribe sola.</p>

    @if ($jornadas->isEmpty())
        <p class="incidencia-vacia">{{ $buscado ? 'No hay jornadas con ese filtro.' : 'No hay jornadas.' }}</p>
    @else
    <div class="tabla-envoltorio">
        <table class="tabla-registro">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jornadas as $jornada)
                    @php($fila = $tabla->fila($persona, $jornada))
                    <tr>
                        <td data-etiqueta="Fecha"><a href="{{ route('registro.show', $jornada) }}">{{ $fila['Fecha'] }}</a></td>
                        <td data-etiqueta="Inicio">{{ $fila['Inicio'] }}</td>
                        <td data-etiqueta="Fin">{{ $fila['Fin'] }}</td>
                        <td data-etiqueta="Total">{{ $fila['Total'] }}</td>
                        <td data-etiqueta="Estado">
                            @if ($fila['Estado'] === 'Pendiente de cerrar')
                                <span class="pastilla pastilla-pendiente">{{ $fila['Estado'] }}</span>
                            @elseif ($fila['Estado'] === 'Cerrada al día siguiente')
                                <span class="pastilla pastilla-tardia">{{ $fila['Estado'] }}</span>
                            @else
                                <span class="pastilla">{{ $fila['Estado'] }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
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

    <form method="get" class="bandeja-incidencias" action="{{ $consulta ? route('equipo.registro', $persona) : route('registro') }}">
        <div class="filtros-incidencia">
            <div class="campo-incidencia">
                <label for="desde">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ $desde }}">
            </div>
            <div class="campo-incidencia">
                <label for="hasta">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ $hasta }}">
            </div>
            <div class="campo-incidencia campo-ancho">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="cerrada" @selected($estado === 'cerrada')>Cerrada</option>
                    <option value="pendiente" @selected($estado === 'pendiente')>Pendiente de cerrar</option>
                    <option value="tardia" @selected($estado === 'tardia')>Cerrada al día siguiente</option>
                </select>
            </div>
            <button class="boton boton-secundario" type="submit">Ver</button>
        </div>
        <button class="boton-hoy" type="submit" formaction="{{ $consulta ? route('equipo.csv', $persona) : route('registro.csv') }}">Descargar Excel</button>
    </form>
    <p class="secundario">Acota por rango o estado y pulsa Ver. Si no eliges nada, la lista y la descarga incluyen todos los días.</p>

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
@endsection
