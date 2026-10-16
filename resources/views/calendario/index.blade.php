@extends('layouts.app', ['ancha' => true, 'titulo' => 'Calendario'])

@section('contenido')
    <h1>Calendario {{ $anio }}</h1>
    <p class="anios">
        @if ($anio > 2020)
            <a href="{{ route('calendario', ['anio' => $anio - 1]) }}">Año anterior</a>
        @endif
        @if ($puedeAvanzar)
            <a href="{{ route('calendario', ['anio' => $anioSiguiente]) }}">Año siguiente</a>
        @endif
    </p>
    @if ($sinCalendario)
        <p class="franja" role="status">El calendario de {{ $anio }} no está cargado. No inventamos festivos. Los días ya trabajados se quedan como se registraron.</p>
    @endif
    @if (! $sinCalendario)
        <p class="secundario">Festivos públicos del centro {{ $centro }}. En rojo, los de todo el país. En verde, los de tu comunidad. Si cae en domingo, pasa al lunes.</p>
    @endif

    <ul class="leyenda">
        <li><span class="muestra muestra-estado"></span> Festivo Nacional</li>
        <li><span class="muestra muestra-comunidad"></span> Festivo Autonómico</li>
        <li><span class="muestra muestra-hoy"></span> Hoy</li>
        <li><span class="muestra muestra-vacaciones"></span> Vacaciones</li>
        <li><span class="muestra muestra-permiso"></span> Permiso</li>
    </ul>

    <div class="anio">
        @foreach ($meses as $mes)
            <section class="mes-caja">
                <h2>{{ $mes['nombre'] }}</h2>
                <div class="rejilla-mes">
                    @foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $letra)
                        <span class="cabecera-dia">{{ $letra }}</span>
                    @endforeach
                    @foreach ($mes['dias'] as $dia)
                        @if ($dia === null)
                            <span class="casilla casilla-vacia"></span>
                        @else
                            @php
                                $festivo = $dia['festivo'];
                                $clases = 'casilla';
                                if ($dia['hoy']) {
                                    $clases .= ' casilla-hoy';
                                }
                                if ($festivo) {
                                    $clases .= ' casilla-'.$festivo->ambito;
                                } elseif ($dia['finde']) {
                                    $clases .= ' casilla-finde';
                                }
                                if ($dia['ausencia']) {
                                    $clases .= ' casilla-'.$dia['ausencia']->tipo;
                                }
                                $titulo = collect([
                                    $festivo ? $festivo->name.'. '.$festivo->etiqueta().'.' : null,
                                    $dia['ausencia'] ? $dia['ausencia']->tipoTexto().'.' : null,
                                    $dia['hoy'] ? 'Hoy.' : null,
                                    $dia['registro'] ? 'Abrir el registro.' : null,
                                ])->filter()->implode(' ');
                            @endphp
                            @if ($dia['registro'])
                                <a class="{{ $clases }}" href="{{ $dia['registro'] }}" @if ($titulo !== '') title="{{ $titulo }}" @endif>
                            @else
                                <span class="{{ $clases }}" @if ($titulo !== '') title="{{ $titulo }}" @endif>
                            @endif
                                <strong>{{ $dia['numero'] }}</strong>
                                @if ($dia['hoy'])
                                    <small>Hoy</small>
                                @endif
                                @if ($festivo)
                                    <small>{{ $festivo->name }}</small>
                                @endif
                                @if ($dia['ausencia'])
                                    <small>{{ $dia['ausencia']->tipoTexto() }}</small>
                                @endif
                                @if ($esJefa && $dia['faltan']->isNotEmpty())
                                    <small>{{ $dia['faltan']->count() }} {{ $dia['faltan']->count() === 1 ? 'falta' : 'faltan' }}</small>
                                @endif
                            @if ($dia['registro'])
                                </a>
                            @else
                                </span>
                            @endif
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    @unless ($esJefa)
        <section class="tarjeta solicitud-vacaciones">
            <h2>Solicitar vacaciones</h2>
            <p class="secundario">La jefa lo ve en Consultas y decide si lo aprueba.</p>
            <form method="post" action="{{ route('calendario.vacaciones') }}" class="formulario">
                @csrf
                <div class="rango-fechas" data-rango>
                    <label for="vacaciones-fechas">Fechas</label>
                    <button id="vacaciones-fechas" class="rango-abre" type="button" data-rango-abre aria-expanded="false">Elige el primer y el último día</button>
                    <input type="hidden" name="desde" value="{{ old('desde') }}">
                    <input type="hidden" name="hasta" value="{{ old('hasta') }}">
                    <p class="rango-aviso" data-rango-aviso hidden>Elige el primer día y después el último.</p>
                    <div class="rango-panel" data-rango-panel hidden>
                        <div class="rango-mes">
                            <button type="button" data-rango-prev aria-label="Mes anterior">‹</button>
                            <strong data-rango-titulo></strong>
                            <button type="button" data-rango-next aria-label="Mes siguiente">›</button>
                        </div>
                        <div class="rango-dias" data-rango-dias></div>
                    </div>
                </div>
                <label for="vacaciones-nota">Nota, si hace falta</label>
                <textarea id="vacaciones-nota" name="nota" maxlength="500">{{ old('nota') }}</textarea>
                <button class="boton boton-principal" type="submit">Solicitar vacaciones</button>
            </form>
            @if ($solicitudes->isNotEmpty())
                <ul class="lista">
                    @foreach ($solicitudes as $solicitud)
                        <li>
                            <span>{{ $solicitud->periodoTexto() }}</span>
                            <strong class="pastilla pastilla-{{ $solicitud->estado }}">{{ $solicitud->estadoTexto() }}</strong>
                            @if ($solicitud->nota)
                                <span class="secundario">{{ $solicitud->nota }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endunless
@endsection
