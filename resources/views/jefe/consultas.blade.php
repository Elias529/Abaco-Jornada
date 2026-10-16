@extends('layouts.app', ['titulo' => 'Consultas'])

@section('contenido')
    @php
        $pendientes = $solicitudes->where('estado', \App\Models\SolicitudVacaciones::PENDIENTE);
        $revisadas = $solicitudes->where('estado', '!=', \App\Models\SolicitudVacaciones::PENDIENTE);
    @endphp

    <h1>Consultas</h1>

    <nav class="tickets-filtros" aria-label="Apartados">
        <a class="tickets-filtro" href="{{ route('jefe.consultas') }}" @if ($vista !== 'vacaciones') aria-current="page" @endif>Abrir consulta</a>
        <a class="tickets-filtro" href="{{ route('jefe.consultas', ['vista' => 'vacaciones']) }}" @if ($vista === 'vacaciones') aria-current="page" @endif @if ($pendientes->count() > 0) aria-label="Solicitud de vacaciones, {{ $pendientes->count() === 1 ? '1 nueva' : $pendientes->count().' nuevas' }}" @endif>
            Solicitud de vacaciones
            @if ($pendientes->count() > 0)
                <span class="aviso-ticket" aria-hidden="true">{{ $pendientes->count() }}</span>
            @endif
        </a>
    </nav>

    @if ($vista === 'vacaciones')
    <section class="bloque-consultas">
        <h2>Por decidir</h2>
        <p class="secundario">El equipo las pide desde su calendario.</p>

        @if ($pendientes->isEmpty())
            <p class="incidencia-vacia">No hay solicitudes de vacaciones.</p>
        @else
            <ul class="lista lista-consultas">
                @foreach ($pendientes as $solicitud)
                    <li class="solicitud-pendiente">
                        <div class="consulta-cuerpo">
                            <div class="consulta-cabeza">
                                <span>{{ $solicitud->autor->name }}</span>
                                <strong class="pastilla pastilla-pendiente">Pendiente</strong>
                            </div>
                            <p class="consulta-motivo">{{ $solicitud->periodoTexto() }}</p>
                            @if ($solicitud->nota)
                                <p class="consulta-nota">{{ $solicitud->nota }}</p>
                            @endif
                            <span class="secundario">Pedida el {{ \App\Support\Tiempo::fecha($solicitud->created_at) }} {{ $solicitud->created_at->format('H:i') }}</span>
                        </div>
                        <div class="solicitud-acciones">
                            <form method="post" action="{{ route('jefe.vacaciones.decidir', $solicitud) }}">
                                @csrf
                                <input type="hidden" name="decision" value="aprobada">
                                <button class="boton boton-principal" type="submit">Aprobar</button>
                            </form>
                            <form method="post" action="{{ route('jefe.vacaciones.decidir', $solicitud) }}">
                                @csrf
                                <input type="hidden" name="decision" value="rechazada">
                                <button class="boton boton-secundario" type="submit">Rechazar</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($revisadas->isNotEmpty())
            <h3>Ya revisadas</h3>
            <ul class="lista lista-consultas">
                @foreach ($revisadas as $solicitud)
                    <li>
                        <div class="consulta-cabeza">
                            <span>{{ $solicitud->autor->name }}</span>
                            <strong class="pastilla pastilla-{{ $solicitud->estado }}">{{ $solicitud->estadoTexto() }}</strong>
                        </div>
                        <p class="consulta-motivo">{{ $solicitud->periodoTexto() }}</p>
                        @if ($solicitud->nota)
                            <p class="consulta-nota">{{ $solicitud->nota }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
    @else
    <section class="bloque-consultas">
        <h2>Registros consultados</h2>
        <p class="secundario">Quién ha abierto el registro de otra persona, cuándo y por qué. Esta lista no se puede cambiar. Se muestran las últimas 100.</p>

        @if ($consultas->isEmpty())
            <p class="incidencia-vacia">Todavía no hay consultas.</p>
        @else
            <ul class="lista lista-consultas">
                @foreach ($consultas as $consulta)
                    <li>
                        <span>{{ $consulta->viewer->name }} consultó el registro de {{ $consulta->subject->name }}</span>
                        @if ($consulta->reason)
                            <span>{{ $consulta->reason }}</span>
                        @endif
                        <span class="secundario">{{ \App\Support\Tiempo::fecha($consulta->created_at) }} {{ $consulta->created_at->format('H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
    @endif
@endsection
