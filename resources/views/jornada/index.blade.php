@extends('layouts.app', ['compacta' => true, 'titulo' => 'Hoy'])

@section('contenido')
    <span data-clock data-server-ms="{{ $serverMs }}" hidden></span>
    <header class="espacio-saludo">
        <div>
            <p class="espacio-sobre">Tu espacio de trabajo</p>
            <h1>{{ $saludo }}, {{ $nombre }}</h1>
            <p class="espacio-fecha">{{ $fechaLarga }}</p>
        </div>
        <p class="chip-estado chip-{{ $pastilla['clase'] }}">{{ $pastilla['texto'] }}</p>
    </header>

    @if ($banner)
        @if (($banner['accion'] ?? null) === 'completar')
            <form method="post" action="{{ route('jornada.completar') }}">
                @csrf
                <x-franja>
                    {{ $banner['texto'] }}
                    <x-slot:primero>
                        <x-campo-hora id="hora-cierre" name="hora" :min="$banner['min']" :max="$banner['max']" />
                    </x-slot:primero>
                    <x-slot:segundo>
                        <button class="franja-accion" type="submit">Completar</button>
                    </x-slot:segundo>
                </x-franja>
            </form>
        @elseif (($banner['accion'] ?? null) === 'registro')
            <x-franja>
                {{ $banner['texto'] }}
                <x-slot:primero>
                    <a class="franja-accion" href="{{ $banner['jornada'] ? route('registro.show', $banner['jornada']) : route('registro.dia', $banner['fecha']) }}">Revisar</a>
                </x-slot:primero>
            </x-franja>
        @elseif (($banner['accion'] ?? null) === 'calendario')
            <x-franja>
                {{ $banner['texto'] }}
                <x-slot:primero>
                    <a class="franja-accion" href="{{ route('calendario') }}">Cargar calendario</a>
                </x-slot:primero>
            </x-franja>
        @elseif (($banner['accion'] ?? null) === 'cierre')
            <x-franja>
                {{ $banner['texto'] }}
                <x-slot:primero>
                    <form method="post" action="{{ route('jornada.cerrar') }}">
                        @csrf
                        <input type="hidden" name="hora" value="{{ $banner['hora'] }}">
                        <button class="franja-accion" type="submit">Terminé a las {{ $banner['hora'] }}</button>
                    </form>
                </x-slot:primero>
                <x-slot:segundo>
                    <form method="post" action="{{ route('jornada.seguir') }}">
                        @csrf
                        <button class="franja-accion" type="submit">Sigo</button>
                    </form>
                </x-slot:segundo>
            </x-franja>
        @elseif (($banner['accion'] ?? null) === 'festivo')
            <x-franja>
                {{ $banner['texto'] }}
                <x-slot:primero>
                    <form method="post" action="{{ route('jornada.festivo') }}">
                        @csrf
                        <button class="franja-accion" name="respuesta" value="si" type="submit">Sí, hay trabajo</button>
                    </form>
                </x-slot:primero>
                <x-slot:segundo>
                    <form method="post" action="{{ route('jornada.festivo') }}">
                        @csrf
                        <button class="franja-accion" name="respuesta" value="no" type="submit">No</button>
                    </form>
                </x-slot:segundo>
            </x-franja>
        @elseif (($banner['accion'] ?? null) === 'inicio')
            <x-franja>
                {{ $banner['texto'] }}
                <x-slot:primero>
                    <form method="post" action="{{ route('jornada.reunion') }}">
                        @csrf
                        <button class="franja-accion" type="submit">Ya estaba trabajando</button>
                    </form>
                </x-slot:primero>
            </x-franja>
        @endif
    @endif

    <div class="espacio">
        <div class="espacio-columna">
            <section class="tarjeta" aria-live="polite">
                @if (session('ok'))
                    <p class="chip-guardado" role="status">{{ session('ok') }}</p>
                @endif
                @if (session('aviso'))
                    <p class="franja franja-dentro" role="status">{{ session('aviso') }}</p>
                @endif
                @if ($errors->any())
                    <p class="franja franja-dentro" role="alert">{{ $errors->first() }}</p>
                @endif

                <div class="tarjeta-cabeza">
                    <h2>Jornada de hoy</h2>
                    <p>{{ $centroNombre }}@if ($tipoLargo) · {{ $tipoLargo }}@endif</p>
                </div>

                <div class="jornada-estado">
                    <span class="jornada-marca marca-{{ $estado }}" aria-hidden="true">
                        @if ($estado === 'pausa')
                            <svg viewBox="0 0 24 24"><path d="M8 5h3.2v14H8zM12.8 5H16v14h-3.2z" fill="currentColor"/></svg>
                        @else
                            <svg viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>
                        @endif
                    </span>
                    <div>
                        @if ($desdeTexto)
                            <p class="espacio-kicker">{{ $desdeTexto }}</p>
                        @endif
                        <p class="jornada-titulo">{{ $estadoTexto }}</p>
                    </div>
                </div>

                <div class="jornada-medida">
                    <p class="jornada-cifra">
                        <strong data-llevas data-corto="1" data-min="1" data-cerrados="{{ $minutosCerrados }}" @if ($abiertoMs) data-abierto-ms="{{ $abiertoMs }}" @endif>{{ $hoyLargo }}</strong>
                        <span>Tiempo trabajado hoy</span>
                    </p>
                    <div class="jornada-acciones">
                        @if ($puedeVolver)
                            <form method="post" action="{{ route('jornada.volver') }}">
                                @csrf
                                <button class="boton-hoy boton-hoy-principal" type="submit">Volver</button>
                            </form>
                            <form method="post" action="{{ route('jornada.salida') }}">
                                @csrf
                                <button class="boton-hoy boton-hoy-linea" type="submit">Terminar jornada</button>
                            </form>
                        @elseif ($puedeSalir)
                            <form method="post" action="{{ route('jornada.salida') }}">
                                @csrf
                                <button class="boton-hoy boton-hoy-principal" type="submit">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7l10 10M17 7 7 17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    Terminar jornada
                                </button>
                            </form>
                            @if ($puedePausar)
                                <form method="post" action="{{ route('jornada.pausa') }}">
                                    @csrf
                                    <button class="boton-hoy boton-hoy-linea" type="submit">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5h3.2v14H8zM12.8 5H16v14h-3.2z" fill="currentColor"/></svg>
                                        Pausa
                                    </button>
                                </form>
                            @endif
                        @elseif ($puedeEntrar)
                            <div class="jornada-empezar">
                                <p class="etiqueta-estado">Inicio de jornada</p>
                                <form method="post" action="{{ route('jornada.entrada') }}">
                                    @csrf
                                    <button class="boton-hoy boton-hoy-principal" type="submit">Empezar jornada</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="eje-leyenda">
                    <span>Registrado / previsto</span>
                    @if ($previstoTexto)
                        <span>{{ $previstoTexto }}</span>
                    @endif
                </div>
                <div class="eje" aria-hidden="true">
                    @foreach ($barra['previsto'] as $trozo)
                        <span class="eje-previsto" style="left: {{ $trozo['left'] }}%; width: {{ $trozo['width'] }}%;"></span>
                    @endforeach
                    @foreach ($barra['hecho'] as $trozo)
                        <span class="eje-hecho" style="left: {{ $trozo['left'] }}%; width: {{ $trozo['width'] }}%;" @if ($trozo['abierto']) data-eje-vivo data-eje-left="{{ $trozo['left'] }}" @endif></span>
                    @endforeach
                </div>
                <div class="eje-marcas" aria-hidden="true">
                    @foreach (['07:00', '09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00'] as $marca)
                        <span>{{ $marca }}</span>
                    @endforeach
                </div>

                @if ($salidaTexto || $porEncimaTexto || $notaDia)
                    <p class="secundario jornada-nota">{{ $salidaTexto }} {{ $porEncimaTexto }} {{ $notaDia }}</p>
                @endif

                @if ($puedeEntrar)
                    <div class="acciones-secundarias">
                        @include('jornada.fuera')
                    </div>
                    @unless ($esJefa)
                        <p class="secundario aviso-conexion" data-conexion-vuelta hidden>Ya hay conexión. Entra y declara las horas que no se guardaron.</p>
                    @endunless
                @elseif (($banner['accion'] ?? null) === 'cierre')
                    <div class="acciones-secundarias">
                        <button class="accion-secundaria" type="button" data-dialogo-abre="otra-hora">Terminé a otra hora</button>
                    </div>
                @elseif (($banner['accion'] ?? null) === 'completar')
                    <div class="acciones-secundarias">
                        <a class="accion-secundaria" href="{{ route('registro', ['estado' => 'pendiente']) }}">Ver en Mi registro</a>
                    </div>
                @endif

                @if ($puedeBorrarPrueba ?? false)
                    <form method="post" action="{{ route('jornada.borrar-prueba') }}">
                        @csrf
                        <button class="enlace-prueba" type="submit">Borrar fichaje de hoy</button>
                    </form>
                @endif
            </section>

            <section class="tarjeta">
                <div class="tarjeta-cabeza">
                    <h2>Tu horario</h2>
                    <a class="tarjeta-enlace" href="{{ route('calendario') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M4 10h16M8 3.5v3M16 3.5v3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        Calendario
                    </a>
                </div>
                @if ($tipoCorto)
                    <dl class="horario-filas">
                        <div>
                            <dt>Tipo de jornada</dt>
                            <dd>{{ $tipoCorto }}</dd>
                        </div>
                        <div>
                            <dt>Mañana</dt>
                            <dd>{{ $mananaTexto }}</dd>
                        </div>
                        @if ($tardeTexto)
                            <div>
                                <dt>Tarde</dt>
                                <dd>{{ $tardeTexto }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="secundario">Todavía no tienes un horario asignado.</p>
                @endif
            </section>
        </div>

        <div class="espacio-columna">
            <section class="tarjeta">
                <div class="tarjeta-cabeza">
                    <h2>Esta semana</h2>
                    <a class="tarjeta-enlace" href="{{ route('registro') }}">Ver registro</a>
                </div>
                <p class="semana-cifra" data-semana data-cerrados="{{ $semanaCerrados }}">{{ $semanaTexto }}</p>
                <p class="espacio-kicker">Horas registradas · {{ $semanaRango }}</p>
                <p class="secundario semana-nota">El total reúne las horas registradas de esta semana. Abre Mi registro para revisar cada día.</p>
            </section>

            <section class="tarjeta">
                <div class="tarjeta-cabeza">
                    <h2>Tramos de hoy</h2>
                    @if ($tramosAbiertos > 0)
                        <p class="chip-estado chip-en-curso">{{ $tramosAbiertos === 1 ? '1 abierto' : $tramosAbiertos.' abiertos' }}</p>
                    @endif
                </div>
                @if ($tramosHoy === [])
                    <p class="secundario">Todavía no hay tramos hoy.</p>
                @else
                    <ul class="tramos">
                        @foreach ($tramosHoy as $tramo)
                            <li>
                                <span class="jornada-marca marca-{{ $tramo['tipo'] === 'pausa' ? 'pausa' : 'trabajando' }}" aria-hidden="true">
                                    @if ($tramo['tipo'] === 'pausa')
                                        <svg viewBox="0 0 24 24"><path d="M8 5h3.2v14H8zM12.8 5H16v14h-3.2z" fill="currentColor"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>
                                    @endif
                                </span>
                                <span>
                                    <strong>{{ $tramo['titulo'] }}</strong>
                                    <small>{{ $tramo['detalle'] }}</small>
                                </span>
                                <span class="tramo-horas">{{ $tramo['horas'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="tarjeta">
                <h2>A un paso</h2>
                <ul class="pasos">
                    <li>
                        <a href="{{ route('registro') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7h8M8 12h8M8 17h5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><rect x="4" y="3.5" width="16" height="17" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
                            Revisar mis jornadas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('calendario') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M4 10h16M8 3.5v3M16 3.5v3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            Consultar festivos y ausencias
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tickets.index') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 11v5M12 8h.01" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            Necesito ayuda
                        </a>
                    </li>
                </ul>
            </section>
        </div>
    </div>

    @if ($puedeEntrar && $puedeFuera && ! in_array(data_get($banner, 'accion'), ['completar', 'cierre'], true))
        <dialog class="dialogo-hoy" data-dialogo="sin-portatil" aria-labelledby="titulo-sin-portatil">
            <div class="dialogo-cabecera">
                <h2 id="titulo-sin-portatil">Trabajé sin el portátil</h2>
                <form method="dialog">
                    <button class="dialogo-cerrar" type="submit">Cerrar</button>
                </form>
            </div>
            <form method="post" action="{{ route('jornada.fuera') }}" class="formulario">
                @csrf
                <p class="secundario">Elige la hora de inicio y de fin, de 00 a 23.</p>
                <div class="horas-par">
                    <div>
                        <label for="fuera-inicio">Inicio</label>
                        <x-campo-hora id="fuera-inicio" name="inicio" :max="now()->format('H:i')" />
                    </div>
                    <div>
                        <label for="fuera-fin">Fin</label>
                        <x-campo-hora id="fuera-fin" name="fin" :max="now()->format('H:i')" />
                    </div>
                </div>
                <button class="boton boton-principal" type="submit">Anotar fuera del equipo</button>
            </form>
        </dialog>
    @endif

    @if (($banner['accion'] ?? null) === 'cierre')
        <dialog class="dialogo-hoy" data-dialogo="otra-hora" aria-labelledby="titulo-otra-hora">
            <div class="dialogo-cabecera">
                <h2 id="titulo-otra-hora">Terminé a otra hora</h2>
                <form method="dialog">
                    <button class="dialogo-cerrar" type="submit">Cerrar</button>
                </form>
            </div>
            <form method="post" action="{{ route('jornada.cerrar') }}" class="formulario">
                @csrf
                <x-campo-hora id="hora-otra" name="hora" :max="now()->format('H:i')" />
                <button class="boton boton-principal" type="submit">Terminé a otra hora</button>
            </form>
        </dialog>
    @endif
@endsection
