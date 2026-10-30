<a class="calendario-anual-enlace" href="{{ route('calendario', ['anio' => $anio, 'mes' => $mesAnual['numero']]) }}" data-calendario-ir>
    <section class="calendario-anual-mes" aria-labelledby="titulo-mes-{{ $mesAnual['nombre'] }}">
        <h2 id="titulo-mes-{{ $mesAnual['nombre'] }}">{{ ucfirst($mesAnual['nombre']) }}</h2>
        <div class="calendario-anual-rejilla">
        @foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $letra)
            <span class="calendario-anual-cabecera">{{ $letra }}</span>
        @endforeach

        @foreach ($mesAnual['dias'] as $dia)
            @if ($dia === null)
                <span class="casilla calendario-anual-dia calendario-anual-vacio"></span>
            @else
                @php
                    $festivo = $dia['festivo'];
                    $clases = 'casilla calendario-anual-dia';
                    if ($dia['hoy']) {
                        $clases .= ' calendario-anual-hoy';
                    }
                    if ($festivo) {
                        $clases .= ' calendario-anual-'.$festivo->ambito;
                    } elseif ($dia['finde']) {
                        $clases .= ' calendario-anual-finde';
                    }
                    if ($dia['ausencia'] && $dia['ausencia']->tipo === 'vacaciones' && $dia['cuenta']) {
                        $clases .= ' calendario-anual-vacaciones';
                    } elseif ($dia['ausencia']) {
                        $clases .= ' calendario-anual-'.$dia['ausencia']->tipo;
                    } elseif ($dia['pendiente']) {
                        $clases .= ' calendario-anual-pendiente';
                    }
                @endphp
                <span class="{{ $clases }}" data-fecha="{{ $dia['fecha'] }}" data-cuenta="{{ $dia['cuenta'] ? '1' : '0' }}">
                    {{ $dia['numero'] }}
                    @if ($dia['ausencia'] && $dia['ausencia']->tipo === 'vacaciones' && $dia['cuenta'])
                        <small class="calendario-anual-etiqueta calendario-anual-etiqueta-vacaciones">Vacaciones</small>
                    @elseif ($dia['pendiente'])
                        <small class="calendario-anual-etiqueta calendario-anual-etiqueta-pendiente">Vacaciones</small>
                    @endif
                </span>
            @endif
        @endforeach
        </div>
    </section>
</a>
