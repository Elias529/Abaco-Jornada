@extends('layouts.app', ['titulo' => 'Registro'])

@section('contenido')
    <p><a class="enlace" href="{{ route('jefe.persona', $persona) }}">Volver a {{ $persona->name }}</a></p>
    <h1>Registro de {{ $persona->name }}</h1>
    <p class="secundario">Solo lectura. Se conservan cuatro años, el plazo del artículo 34.9 del Estatuto de los Trabajadores. Ábaco lo confirma. Si una hora cambió, se ve la anterior, quién la cambió y cuándo.</p>
    <p>
        <a class="boton boton-secundario" href="{{ route('jefe.csv', $persona) }}">Descargar copia</a>
    </p>

    <form method="get" action="{{ route('jefe.registro', $persona) }}" class="formulario formulario-mes">
        <label for="mes">Mes</label>
        <div class="fila">
            <select id="mes" name="mes">
                @foreach ($meses as $valor => $texto)
                    <option value="{{ $valor }}" @selected($valor === $mes)>{{ $texto }}</option>
                @endforeach
            </select>
            <button class="boton boton-secundario" type="submit">Ver mes</button>
        </div>
    </form>

    <h2>{{ ucfirst($mesTexto) }}</h2>
    <section class="resumen resumen-fila" aria-label="Totales del mes">
        <div>
            <span>Registrado</span>
            <strong>{{ \App\Support\Tiempo::corto($resumen['registrados']) }}</strong>
        </div>
        <div>
            <span>Previsto hasta hoy</span>
            <strong>{{ \App\Support\Tiempo::corto($resumen['previstos']) }}</strong>
        </div>
        <div>
            <span>Por encima del horario</span>
            <strong>{{ \App\Support\Tiempo::corto($resumen['porEncima']) }}</strong>
        </div>
    </section>
    <p class="secundario">El tiempo por encima del horario se guarda tal como fue. No es una hora extra ni está autorizado: lo decide Ábaco.</p>

    @if ($semanas->isEmpty())
        <p>No hay jornadas en este mes.</p>
    @else
        @foreach ($semanas as $semana)
            <h3 class="semana">
                <span>Semana del {{ $semana['lunes']->locale('es')->isoFormat('D [de] MMMM') }} al {{ $semana['domingo']->locale('es')->isoFormat('D [de] MMMM') }}</span>
                <strong>{{ \App\Support\Tiempo::texto($semana['minutos']) }}</strong>
            </h3>
            <ul class="lista">
                @foreach ($semana['jornadas'] as $jornada)
                    @php
                        $conCambios = $jornada->tramos->contains(fn ($tramo) => $tramo->correcciones->isNotEmpty());
                    @endphp
                    <li>
                        <a href="{{ route('jefe.dia', $jornada) }}">
                            <span>{{ \App\Support\Tiempo::fecha($jornada->work_date) }}</span>
                            <strong>{{ \App\Support\Tiempo::texto($servicio->minutosRegistrados($persona, $jornada)) }}</strong>
                        </a>
                        <span class="secundario">
                            @if ($jornada->tramoAbierto())
                                Pendiente de cerrar
                            @elseif ($jornada->closed_late)
                                Cerrada al día siguiente
                            @else
                                Cerrada
                            @endif
                            @if ($conCambios)
                                · con cambios
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endforeach
    @endif

    @if ($explicaciones->isNotEmpty())
        <h2>Explicaciones</h2>
        <ul class="lista">
            @foreach ($explicaciones as $explicacion)
                <li>
                    <span>{{ $explicacion->body }}</span>
                    <span class="secundario">{{ \App\Support\Tiempo::fecha($explicacion->created_at) }}@if ($explicacion->work_date) · {{ \App\Support\Tiempo::fecha($explicacion->work_date) }}@endif</span>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($consultas->isNotEmpty())
        <h2>Consultas</h2>
        <ul class="lista">
            @foreach ($consultas as $visita)
                <li>
                    <span>{{ $visita->viewer->name }}@if ($visita->reason). {{ $visita->reason }}@endif</span>
                    <span class="secundario">{{ \App\Support\Tiempo::fecha($visita->created_at) }} {{ $visita->created_at->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
