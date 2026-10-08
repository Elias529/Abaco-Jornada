@extends('layouts.app', ['compacta' => true])

@section('contenido')
    <header class="cabecera-fichar">
        <h1 class="nombre">Jornada · {{ $fecha }}</h1>
    </header>

    @if ($banner)
        <div class="franja" role="status">
            @if (($banner['accion'] ?? null) === 'completar')
                <form method="post" action="{{ route('jornada.completar') }}" class="franja-linea">
                    @csrf
                    <p>{{ $banner['texto'] }}</p>
                    <x-hora id="hora-cierre" name="hora" :min="$banner['min']" :max="$banner['max']" />
                    <button class="franja-accion" type="submit">Completar</button>
                </form>
            @else
                <p>{{ $banner['texto'] }}</p>
                @if (($banner['accion'] ?? null) === 'registro')
                    <a class="franja-accion" href="{{ route('registro') }}">Revisarlos</a>
                @elseif (($banner['accion'] ?? null) === 'calendario')
                    <a class="franja-accion" href="{{ route('calendario') }}">Cargar calendario</a>
                @elseif (($banner['accion'] ?? null) === 'cierre')
                    <div class="franja-acciones">
                        <form method="post" action="{{ route('jornada.cerrar') }}">
                            @csrf
                            <input type="hidden" name="hora" value="{{ $banner['hora'] }}">
                            <button class="boton boton-secundario" type="submit">Terminé a las {{ $banner['hora'] }}</button>
                        </form>
                        <form method="post" action="{{ route('jornada.cerrar') }}" class="fila">
                            @csrf
                            <x-hora id="hora-otra" name="hora" :max="now()->format('H:i')" />
                            <button class="boton boton-secundario" type="submit">Terminé a otra hora</button>
                        </form>
                        <form method="post" action="{{ route('jornada.seguir') }}">
                            @csrf
                            <button class="boton boton-secundario" type="submit">Sigo</button>
                        </form>
                    </div>
                @elseif (($banner['accion'] ?? null) === 'festivo')
                    <form method="post" action="{{ route('jornada.festivo') }}" class="fila">
                        @csrf
                        <button class="boton boton-secundario" name="respuesta" value="si" type="submit">Sí, hay trabajo</button>
                        <button class="boton boton-secundario" name="respuesta" value="no" type="submit">No</button>
                    </form>
                @endif
            @endif
        </div>
    @endif

    <div class="cuerpo-fichar">
        @if (session('ok'))
            <p class="guardado" role="status">{{ session('ok') }}</p>
        @endif
        @if (session('aviso'))
            <p class="franja franja-dentro" role="status">{{ session('aviso') }}</p>
        @endif
        @if ($errors->any())
            <p class="franja franja-dentro" role="alert">{{ $errors->first() }}</p>
        @endif

        <section class="estado" aria-live="polite">
            <p class="etiqueta-estado">Estado</p>
            <p class="estado-texto">{{ $estadoTexto }}</p>
        </section>

        @if ($salidaTexto || $porEncimaTexto || $notaDia)
            <p class="secundario">
                {{ $salidaTexto }}
                {{ $porEncimaTexto }}
                {{ $notaDia }}
            </p>
        @endif

        @if ($puedeVolver)
            <form method="post" action="{{ route('jornada.volver') }}" class="accion-principal">
                @csrf
                <button class="boton boton-principal" type="submit">Volver</button>
            </form>
        @elseif ($puedeSalir)
            <form method="post" action="{{ route('jornada.salida') }}" class="accion-principal">
                @csrf
                <button class="boton boton-principal" type="submit">Terminar jornada</button>
            </form>
        @elseif ($puedeEntrar)
            <form method="post" action="{{ route('jornada.entrada') }}" class="accion-principal formulario">
                @csrf
                <label for="hora-inicio">Inicio de jornada</label>
                <x-hora id="hora-inicio" name="hora" :valor="now()->format('H:i')" :max="now()->format('H:i')" />
                <button class="boton boton-principal" type="submit">Empezar jornada</button>
            </form>
        @endif

        <div class="rejilla">
            @if ($puedePausar)
                <form method="post" action="{{ route('jornada.pausa') }}" class="rejilla-pausa">
                    @csrf
                    <button class="boton boton-secundario" type="submit">Pausa</button>
                </form>
                <form method="post" action="{{ route('jornada.ya') }}" class="rejilla-ya">
                    @csrf
                    <x-hora id="hora-antes" name="hora" :max="now()->format('H:i')" />
                    <button class="boton boton-secundario" type="submit">Ya estaba trabajando</button>
                </form>
            @endif

            @if ($puedeVolver)
                <form method="post" action="{{ route('jornada.salida') }}">
                    @csrf
                    <button class="boton boton-secundario" type="submit">Terminar jornada</button>
                </form>
            @endif

            @if (($banner['accion'] ?? null) === 'inicio')
                <form method="post" action="{{ route('jornada.reunion') }}">
                    @csrf
                    <button class="boton boton-secundario" type="submit">Reunión, mantener las {{ $banner['hora'] }}</button>
                </form>
            @endif
        </div>

        <section class="resumen" aria-label="Resumen">
            <div>
                <span>Hoy</span>
                <strong data-llevas data-corto="1" data-cerrados="{{ $minutosCerrados }}" @if ($abiertoMs) data-abierto-ms="{{ $abiertoMs }}" @endif>{{ $hoyTexto }}</strong>
            </div>
            <div>
                <span>Semana</span>
                <strong>{{ $semanaTexto }}</strong>
            </div>
            <a href="{{ route('registro') }}">Mi registro</a>
        </section>

        <p class="secundario">{{ $horarioTexto }}</p>

        @if ($puedeFuera && ! in_array(data_get($banner, 'accion'), ['completar', 'cierre'], true))
            <form method="post" action="{{ route('jornada.fuera') }}" class="formulario">
                @csrf
                <p class="secundario">Trabajo fuera del equipo. Elige la hora de inicio y de fin, de 00 a 23.</p>
                <label for="fuera-inicio">Inicio</label>
                <x-hora id="fuera-inicio" name="inicio" :max="now()->format('H:i')" />
                <label for="fuera-fin">Fin</label>
                <x-hora id="fuera-fin" name="fin" :max="now()->format('H:i')" />
                <button class="boton boton-secundario" type="submit">Anotar fuera del equipo</button>
            </form>
        @endif

        @if ($puedeBorrarPrueba ?? false)
            <form method="post" action="{{ route('jornada.borrar-prueba') }}">
                @csrf
                <button class="enlace-prueba" type="submit">Borrar fichaje de hoy</button>
            </form>
        @endif

        <nav class="menu menu-pie" aria-label="Cuenta">
            @if ($esResponsable)
                <a href="{{ route('equipo.index') }}">Equipo</a>
                <a href="{{ route('calendario') }}">Calendario</a>
            @endif
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Salir</button>
            </form>
        </nav>
    </div>
@endsection
