@extends('layouts.app', ['titulo' => 'Consultas'])

@section('contenido')
    <div class="consultas-pagina">
        <header class="consultas-cabeza">
            <div>
                <p class="espacio-sobre">Equipo</p>
                <h1>Consultas a registros</h1>
                <span class="sr">Registros consultados</span>
                <p class="secundario">Últimas 100 consultas. Se conserva quién entró, cuándo y por qué.</p>
            </div>
            <span class="equipo-solo-lectura">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2" fill="currentColor"/></svg>
                Solo lectura
            </span>
        </header>

        <section class="consultas-tabla" aria-label="Registro de consultas">
            @if ($consultas->isEmpty())
                <p class="registro-vacio">Todavía no hay consultas.</p>
            @else
                <div class="consultas-tabla-cabeza">
                    <span>Quién consultó</span>
                    <span>Registro consultado</span>
                    <span>Motivo</span>
                    <span>Fecha y hora</span>
                </div>
                @foreach ($consultas as $consulta)
                    <div class="consultas-fila">
                        <span>{{ $consulta->viewer->name }}</span>
                        <span>{{ $consulta->subject->name }}</span>
                        <span>{{ $consulta->reason ?: '—' }}</span>
                        <span class="consultas-fecha">{{ $consulta->created_at->locale('es')->isoFormat('D MMM YYYY') }} · {{ $consulta->created_at->format('H:i') }}</span>
                    </div>
                    <span class="sr">{{ $consulta->viewer->name }} consultó el registro de {{ $consulta->subject->name }}</span>
                @endforeach
            @endif
            <div class="consultas-pie">
                <span>{{ $consultas->count() }} {{ $consultas->count() === 1 ? 'consulta' : 'consultas' }} de muestra</span>
                <span>Registro de acceso</span>
            </div>
        </section>

        <section class="consultas-seccion" aria-labelledby="titulo-solicitudes-vacaciones">
            <header class="consultas-seccion-cabeza">
                <div>
                    <h2 id="titulo-solicitudes-vacaciones">Solicitudes de vacaciones</h2>
                    <p class="secundario">Revisa y decide las solicitudes pendientes del equipo.</p>
                </div>
                @if ($solicitudesVacaciones->isNotEmpty())
                    <span class="pastilla pastilla-pendiente">{{ $solicitudesVacaciones->count() }}</span>
                @endif
            </header>

            <section class="consultas-tabla solicitudes-tabla" aria-label="Solicitudes de vacaciones pendientes">
                @if ($solicitudesVacaciones->isEmpty())
                    <p class="registro-vacio">No hay solicitudes pendientes.</p>
                @else
                    <div class="consultas-tabla-cabeza solicitudes-tabla-cabeza">
                        <span>Persona</span>
                        <span>Periodo</span>
                        <span>Acciones</span>
                    </div>
                    @foreach ($solicitudesVacaciones as $solicitud)
                        <div class="consultas-fila solicitud-fila">
                            <span>{{ $solicitud->autor->name }}</span>
                            <span class="consultas-fecha">{{ $solicitud->periodoTexto() }}</span>
                            <span class="solicitud-acciones">
                                <form method="post" action="{{ route('jefe.vacaciones.decidir', $solicitud) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="{{ \App\Models\SolicitudVacaciones::APROBADA }}">
                                    <x-boton
                                        tipo="principal"
                                        tipoHtml="submit"
                                        class="solicitud-accion solicitud-aprobar"
                                        aria-label="Aprobar solicitud de {{ $solicitud->autor->name }}"
                                        title="Aprobar"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="m5 12 4.5 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </x-boton>
                                </form>
                                <form method="post" action="{{ route('jefe.vacaciones.decidir', $solicitud) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="{{ \App\Models\SolicitudVacaciones::RECHAZADA }}">
                                    <x-boton
                                        tipo="secundario"
                                        tipoHtml="submit"
                                        class="solicitud-accion solicitud-rechazar"
                                        aria-label="Rechazar solicitud de {{ $solicitud->autor->name }}"
                                        title="Rechazar"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="m7 7 10 10M17 7 7 17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </x-boton>
                                </form>
                            </span>
                        </div>
                    @endforeach
                @endif
            </section>
        </section>
    </div>
@endsection
