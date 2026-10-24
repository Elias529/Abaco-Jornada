<header class="calendario-cabecera">
    <div>
        <p class="espacio-sobre">Mi jornada</p>
        <h1>Calendario</h1>
        <p class="secundario">{{ $centro->municipio() }} · {{ $anio }}</p>
    </div>

    <div class="calendario-controles">
        <nav class="calendario-anio" aria-label="Cambiar año">
            @if ($anioAnterior)
                <a href="{{ route('calendario', $anioAnterior) }}" data-calendario-ir aria-label="Año anterior">‹</a>
            @else
                <span aria-hidden="true">‹</span>
            @endif
            <strong>{{ $anio }}</strong>
            @if ($anioSiguiente)
                <a href="{{ route('calendario', $anioSiguiente) }}" data-calendario-ir aria-label="Año siguiente">›</a>
            @else
                <span aria-hidden="true">›</span>
            @endif
        </nav>

        <div class="calendario-vistas" role="group" aria-label="Vista del calendario">
            <span aria-current="page">Mes</span>
            <span>Año</span>
        </div>
    </div>
</header>

<div class="calendario-layout">
    <section class="calendario-panel" aria-label="Calendario mensual">
        @include('calendario.mes')
    </section>

    <aside class="calendario-aside">
        <section class="calendario-panel calendario-eventos" aria-labelledby="titulo-eventos-calendario">
            <h2 id="titulo-eventos-calendario">En {{ $mes['nombre'] }}</h2>

            @forelse ($eventos as $evento)
                <article class="calendario-evento calendario-evento-{{ $evento['clase'] }}">
                    <time datetime="{{ $evento['fecha'] }}">
                        <strong>{{ $evento['dia'] }}</strong>
                        <span>{{ $evento['diaSemana'] }}</span>
                    </time>
                    <div>
                        <strong>{{ $evento['titulo'] }}</strong>
                        <span>{{ $evento['subtitulo'] }}</span>
                    </div>
                </article>
            @empty
                <p class="secundario calendario-eventos-vacio">No hay eventos previstos este mes.</p>
            @endforelse
        </section>

        <section class="calendario-panel calendario-centro" aria-labelledby="titulo-centro-calendario">
            <h2 id="titulo-centro-calendario">Tu centro</h2>
            <dl>
                <div>
                    <dt>Ubicación</dt>
                    <dd>{{ $centro->municipio() }}</dd>
                </div>
                <div>
                    <dt>Comunidad</dt>
                    <dd>{{ $centro->comunidad() }}</dd>
                </div>
                <div>
                    <dt>Año</dt>
                    <dd>{{ $anio }}</dd>
                </div>
            </dl>
            <p class="secundario calendario-centro-nota">El horario y las ausencias previstas se muestran según el centro.</p>
        </section>

        <ul class="leyenda calendario-leyenda sr" aria-label="Leyenda del calendario">
            <li><span class="muestra muestra-estado"></span> Festivo Nacional</li>
            <li><span class="muestra muestra-comunidad"></span> Festivo Autonómico</li>
            <li><span class="muestra muestra-hoy"></span> Hoy</li>
            <li><span class="muestra muestra-vacaciones"></span> Vacaciones</li>
        </ul>
    </aside>
</div>
