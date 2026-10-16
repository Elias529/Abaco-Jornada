@extends('layouts.app', ['ancha' => true, 'titulo' => 'Alta'])

@section('contenido')
    <a class="volver" href="{{ route('equipo.index') }}">Volver al equipo</a>
    <h1>Dar de alta</h1>
    <p class="secundario">Antes de crear el acceso, elige el tipo de jornada, cuánto tiempo estará y mira el calendario. Los festivos no esperan fichaje.</p>

    <fieldset class="tipos-jornada">
        <legend>Tipo de jornada</legend>
        <div class="tipos-jornada-lista">
            @foreach ($tipos as $opcion)
                <a
                    class="tipo-jornada @if ($tipo === $opcion) tipo-jornada-activa @endif"
                    href="{{ route('equipo.create', array_merge($consulta, ['anio' => $anio, 'tipo' => $opcion->value])) }}"
                    @if ($tipo === $opcion) aria-current="true" @endif
                >
                    <strong>{{ $opcion->etiqueta() }}</strong>
                    <span>{{ $opcion->descripcion() }}</span>
                    <span class="secundario">{{ $opcion->textoHorario() }}</span>
                </a>
            @endforeach
        </div>
    </fieldset>

    <form method="get" action="{{ route('equipo.create') }}#alta-calendario" class="alta-estancia">
        @if ($tipo)
            <input type="hidden" name="tipo" value="{{ $tipo->value }}">
        @endif
        @if ($duracion)
            <input type="hidden" name="duracion" value="{{ $duracion->value }}">
        @endif

        <fieldset class="tipos-jornada">
            <legend>Tiempo en la empresa</legend>
            <div class="alta-estancia-inicio">
                <label for="starts_on">El registro cuenta desde</label>
                <input id="starts_on" name="starts_on" type="date" value="{{ $startsOn }}" required onchange="this.form.requestSubmit()">
                <button class="boton boton-secundario" type="submit">Ver en el calendario</button>
            </div>
            <div class="tipos-jornada-lista">
                @foreach ($duraciones as $opcion)
                    <button
                        class="tipo-jornada @if ($duracion === $opcion) tipo-jornada-activa @endif"
                        type="submit"
                        name="duracion"
                        value="{{ $opcion->value }}"
                    >
                        <strong>{{ $opcion->etiqueta() }}</strong>
                        @if ($opcion === \App\DuracionEstancia::Indefinido)
                            <span class="secundario">Desde el {{ $startsOn }}, sin último día</span>
                        @elseif ($opcion === \App\DuracionEstancia::HastaElDia)
                            <span class="secundario">Indica el último día abajo</span>
                        @else
                            <span class="secundario">Hasta el {{ $opcion->fechaFin(\Illuminate\Support\Carbon::parse($startsOn), null) }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
            @if ($duracion === \App\DuracionEstancia::HastaElDia)
                <label for="ends_on">Último día</label>
                <div class="alta-estancia-inicio">
                    <input id="ends_on" name="ends_on" type="date" value="{{ $endsOn }}" min="{{ $startsOn }}" onchange="this.form.requestSubmit()">
                    <button class="boton boton-secundario" type="submit">Ver en el calendario</button>
                </div>
            @endif
        </fieldset>
    </form>

    @unless ($tipo)
        <p class="franja" role="status">Elige un tipo de jornada para continuar el alta.</p>
    @endunless
    @if ($tipo && $duracion === \App\DuracionEstancia::HastaElDia && ! $periodoListo)
        <p class="franja" role="status">Elige el último día para verlo en el calendario.</p>
    @elseif ($tipo && ! $periodoListo)
        <p class="franja" role="status">Elige cuánto tiempo estará para verlo en el calendario.</p>
    @endif

    <section id="alta-calendario" class="alta-calendario" aria-labelledby="alta-calendario-titulo">
        <h2 id="alta-calendario-titulo">
            @if ($periodoListo && $finPeriodo)
                Calendario del periodo
            @else
                Calendario {{ $anio }}
            @endif
        </h2>
        @if ($navegaAnios)
            <p class="anios">
                @if ($puedeRetroceder)
                    <a href="{{ route('equipo.create', array_merge($consulta, ['anio' => $anio - 1])) }}">Año anterior</a>
                @endif
                @if ($puedeAvanzar)
                    <a href="{{ route('equipo.create', array_merge($consulta, ['anio' => $anioSiguiente])) }}">Año siguiente</a>
                @endif
            </p>
        @endif
        @if ($sinCalendario)
            <p class="franja" role="status">El calendario no está cargado. No inventamos festivos. Puedes elegir el tipo de jornada igual.</p>
        @else
            <p class="secundario">Festivos públicos del centro {{ $centro }}. En rojo, los de todo el país. En verde, los de tu comunidad. Si cae en domingo, pasa al lunes.</p>
        @endif

        <ul class="leyenda">
            <li><span class="muestra muestra-estado"></span> Festivo Nacional</li>
            <li><span class="muestra muestra-comunidad"></span> Festivo Autonómico</li>
            <li><span class="muestra muestra-hoy"></span> Hoy</li>
            @if ($periodoListo)
                @php
                    $leyendaLaborable = 'Laborable';
                    if ($tipo) {
                        $leyendaLaborable .= ' · '.$tipo->etiqueta();
                    }
                    if ($duracion) {
                        $leyendaLaborable .= ' · '.$duracion->etiqueta();
                    }
                    $claseLaborable = 'muestra muestra-laborable';
                    if ($tipo) {
                        $claseLaborable .= ' muestra-laborable-'.$tipo->value;
                    }
                @endphp
                <li><span class="{{ $claseLaborable }}"></span> {{ $leyendaLaborable }}</li>
                <li><span class="muestra muestra-fuera"></span> Fuera del tiempo en la empresa</li>
            @endif
        </ul>

        <div class="anio">
            @foreach ($meses as $mes)
                <section class="mes-caja">
                    <h2>{{ $mes['nombre'] }}@if ($muestraAnioMes) {{ $mes['anio'] }}@endif</h2>
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
                                    } elseif ($periodoListo && $dia['laborable'] && $dia['en_periodo']) {
                                        $clases .= ' casilla-laborable '.($tipo ? 'casilla-laborable-'.$tipo->value : 'casilla-en-periodo');
                                    } elseif ($periodoListo && $dia['laborable']) {
                                        $clases .= ' casilla-fuera';
                                    }
                                    $titulo = collect([
                                        $festivo ? $festivo->name.'. '.$festivo->etiqueta().'.' : null,
                                        $dia['hoy'] ? 'Hoy.' : null,
                                        $tipo && $dia['laborable'] && $dia['en_periodo'] ? $tipo->etiqueta().'.' : null,
                                        $periodoListo && $dia['laborable'] && ! $dia['en_periodo'] ? 'Fuera del tiempo en la empresa.' : null,
                                    ])->filter()->implode(' ');
                                @endphp
                                <span class="{{ $clases }}" @if ($titulo !== '') title="{{ $titulo }}" @endif>
                                    <strong>{{ $dia['numero'] }}</strong>
                                    @if ($festivo)
                                        <small>{{ $festivo->name }}</small>
                                    @endif
                                </span>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </section>

    @if ($tipo && $periodoListo)
        <form method="post" action="{{ route('equipo.store') }}" class="formulario">
            @csrf
            <input type="hidden" name="tipo" value="{{ $tipo->value }}">
            <input type="hidden" name="starts_on" value="{{ $startsOn }}">
            <input type="hidden" name="duracion" value="{{ $duracion->value }}">
            @if ($duracion === \App\DuracionEstancia::HastaElDia)
                <input type="hidden" name="ends_on" value="{{ $endsOn }}">
            @endif

            <p class="secundario">
                Horario de {{ $tipo->etiqueta() }}: {{ $tipo->textoHorario() }}.
                @if ($finPeriodo)
                    En la empresa desde el {{ $startsOn }} hasta el {{ $finPeriodo }}.
                @else
                    En la empresa desde el {{ $startsOn }}, sin fecha de fin.
                @endif
                Los festivos no esperan fichaje.
            </p>

            <label for="name">Nombre</label>
            <input id="name" name="name" value="{{ old('name') }}" required maxlength="120">

            <label for="email">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>

            <label for="password">Contraseña inicial</label>
            <input id="password" name="password" type="text" required minlength="8" autocomplete="new-password">

            <button class="boton boton-principal" type="submit">Guardar alta</button>
        </form>
    @endif
@endsection
