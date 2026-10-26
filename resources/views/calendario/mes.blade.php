<section class="mes-caja mes-unico calendario-mes-card">
@if ($sinCalendario)
    <p class="franja" role="status">El calendario de {{ $anio }} no está cargado. No inventamos festivos. Los días ya trabajados se quedan como se registraron.</p>
@endif

<div class="mes-nav">
    @if ($mesAnterior)
        <a class="mes-flecha" href="{{ route('calendario', $mesAnterior) }}" data-calendario-ir aria-label="Mes anterior">‹</a>
    @else
        <span class="mes-flecha" aria-hidden="true"></span>
    @endif
    <h2>{{ $mes['nombre'] }} {{ $anio }}</h2>
    @if ($mesSiguiente)
        <a class="mes-flecha" href="{{ route('calendario', $mesSiguiente) }}" data-calendario-ir aria-label="Mes siguiente">›</a>
    @else
        <span class="mes-flecha" aria-hidden="true"></span>
    @endif
</div>

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
                    if ($dia['ausencia'] && $dia['ausencia']->tipo === 'vacaciones') {
                        if ($dia['cuenta']) {
                            $clases .= ' casilla-vacaciones';
                        }
                    } elseif ($dia['ausencia']) {
                        $clases .= ' casilla-'.$dia['ausencia']->tipo;
                    } elseif ($dia['pendiente']) {
                        $clases .= ' casilla-pendiente';
                    }
                    $titulo = collect([
                        $dia['hoy'] ? 'Hoy.' : null,
                        $dia['registro'] ? 'Abrir el registro.' : null,
                    ])->filter()->implode(' ');
                @endphp
                @if ($dia['registro'])
                    <a class="{{ $clases }}" href="{{ $dia['registro'] }}" data-fecha="{{ $dia['fecha'] }}" data-cuenta="{{ $dia['cuenta'] ? '1' : '0' }}" @if ($titulo !== '') title="{{ $titulo }}" @endif>
                @else
                    <span class="{{ $clases }}" data-fecha="{{ $dia['fecha'] }}" data-cuenta="{{ $dia['cuenta'] ? '1' : '0' }}" @if ($titulo !== '') title="{{ $titulo }}" @endif>
                @endif
                    <strong>{{ $dia['numero'] }}</strong>
                    @if ($festivo)
                        <small class="casilla-etiqueta casilla-etiqueta-festivo">{{ $festivo->name }}</small>
                    @elseif ($dia['ausencia'])
                        <small class="casilla-etiqueta casilla-etiqueta-{{ $dia['ausencia']->tipo }}">{{ $dia['ausencia']->tipoTexto() }}</small>
                    @elseif ($dia['pendiente'])
                        <small class="casilla-etiqueta casilla-etiqueta-vacaciones-pendiente">Vacaciones</small>
                    @endif
                    @if ($dia['hoy'])
                        <small>Hoy</small>
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
