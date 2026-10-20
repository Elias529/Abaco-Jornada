<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'Jornada' }} · Ábaco</title>
    <script>
        (function () {
            var guardado = null;
            try { guardado = localStorage.getItem('abaco-tema'); } catch (e) {}
            var oscuro = guardado === 'oscuro' || (guardado !== 'claro' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-tema', oscuro ? 'oscuro' : 'claro');
        })();
    </script>
    <link rel="icon" href="{{ asset('marca/logos/abacoqd_isotipo.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/paleta.css') }}?v={{ filemtime(public_path('css/paleta.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/jornada.css') }}?v={{ filemtime(public_path('css/jornada.css')) }}">
</head>
<body @if ($pendiente ?? false) data-pendiente="1" @endif>
    <a class="saltar" href="#contenido">Saltar al contenido</a>

    @auth
        <header class="barra">
            <form class="marca" method="get" action="{{ route('jornada') }}">
                <button class="marca-logo" type="submit">
                    <img class="isotipo" src="{{ asset('marca/logos/abacoqd_isotipo.svg') }}" alt="">
                    <span class="marca-nombre">Jornada</span>
                </button>
            </form>
            <nav class="menu" aria-label="Secciones">
                <a href="{{ route('jornada') }}" @if (request()->routeIs('jornada')) aria-current="page" @endif>Hoy</a>
                <a href="{{ route('registro') }}" @if (request()->routeIs('registro', 'registro.show')) aria-current="page" @endif>Mi registro</a>
                <a href="{{ route('calendario') }}" aria-label="Calendario" @if (request()->routeIs('calendario')) aria-current="page" @endif>
                    <svg class="icono-calendario" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <rect x="3.2" y="4.8" width="17.6" height="15.4" rx="2.2" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M3.2 9.4h17.6M8 3.2v3.4M16 3.2v3.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </a>
                @if (auth()->user()->esJefa())
                    <a href="{{ route('equipo.index') }}" @if (request()->routeIs('equipo.*')) aria-current="page" @endif>Equipo</a>
                    <a href="{{ route('jefe.index') }}" @if (request()->routeIs('jefe.index', 'jefe.persona', 'jefe.registro', 'jefe.dia')) aria-current="page" @endif>Estado</a>
                    <a href="{{ route('jefe.consultas') }}" @if (request()->routeIs('jefe.consultas')) aria-current="page" @endif>Consultas</a>
                    <a href="{{ route('incidencias.index') }}" @if (request()->routeIs('incidencias.*')) aria-current="page" @endif @if (($incidenciasNuevas ?? 0) > 0) aria-label="Incidencias, {{ $incidenciasNuevas === 1 ? '1 nueva' : $incidenciasNuevas.' nuevas' }}" @endif>
                        Incidencias
                        @if (($incidenciasNuevas ?? 0) > 0)
                            <span class="aviso-ticket" aria-hidden="true">{{ $incidenciasNuevas }}</span>
                        @endif
                    </a>
                @endif
                @if (auth()->user()->esTecnico())
                    <a href="{{ route('tecnico.index') }}" @if (request()->routeIs('tecnico.index', 'tecnico.copia', 'tecnico.actualizar')) aria-current="page" @endif>Sistema</a>
                    <a href="{{ route('tecnico.tickets') }}" @if (request()->routeIs('tecnico.tickets*', 'tickets.show')) aria-current="page" @endif @if (($ticketsNuevos ?? 0) > 0) aria-label="Tickets, {{ $ticketsNuevos === 1 ? '1 nuevo' : $ticketsNuevos.' nuevos' }}" @endif>
                        Tickets
                        @if (($ticketsNuevos ?? 0) > 0)
                            <span class="aviso-ticket" aria-hidden="true">{{ $ticketsNuevos }}</span>
                        @endif
                    </a>
                    <a href="{{ route('jornada.incidencia.crear') }}" @if (request()->routeIs('jornada.incidencia.crear')) aria-current="page" @endif>Comunicar incidencia</a>
                @else
                    <a href="{{ route('tickets.index', ['desde' => request()->routeIs('tickets.*') ? null : '/'.ltrim(request()->path(), '/')]) }}" @if (request()->routeIs('tickets.*')) aria-current="page" @endif>Ayuda</a>
                    @unless (auth()->user()->esJefa())
                        <a href="{{ route('jornada.incidencia.crear') }}" @if (request()->routeIs('jornada.incidencia.crear')) aria-current="page" @endif>Comunicar incidencia</a>
                    @endunless
                @endif
            </nav>
            <div class="barra-cuenta">
                @if (auth()->user()->esJefa() && request()->routeIs('jefe.*'))
                    <span class="solo-lectura">Solo lectura</span>
                @endif
                <span class="persona">{{ auth()->user()->name }}</span>
                <button type="button" class="tema" data-tema-toggle aria-label="Modo oscuro">
                    <svg class="tema-luna" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="currentColor" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
                    </svg>
                    <svg class="tema-sol" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M12 2.6v2.2M12 19.2v2.2M2.6 12h2.2M19.2 12h2.2M5.2 5.2l1.6 1.6M17.2 17.2l1.6 1.6M18.8 5.2l-1.6 1.6M6.8 17.2 5.2 18.8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Salir</button>
                </form>
            </div>
        </header>
    @endauth

    <div class="pagina {{ ($acceso ?? false) ? 'pagina-acceso' : '' }} {{ ($compacta ?? false) ? 'pagina-fichar' : '' }}">
        <p class="sin-conexion" data-sin-conexion hidden>No guardado: sin conexión.</p>

        @unless ($compacta ?? false)
            @if (session('ok'))
                <p class="guardado boton" role="status">{{ session('ok') }}</p>
            @endif
            @if (session('aviso'))
                <p class="franja" role="status">{{ session('aviso') }}</p>
            @endif
            @if ($errors->any())
                <p class="franja" role="alert">{{ $errors->first() }}</p>
            @endif
        @endunless

        <main id="contenido">
            @yield('contenido')
        </main>

        @if (auth()->check() && auth()->user()->esJefa() && request()->routeIs('jefe.*'))
            <p class="nota-datos">Aquí solo consultas. Lo guardado no se cambia, y cada vez que abres el registro de una persona queda escrito quién lo hizo y por qué.</p>
        @endif
    </div>
    <script src="{{ asset('js/reloj.js') }}?v={{ filemtime(public_path('js/reloj.js')) }}"></script>
    <script>
        (function () {
            var raiz = document.documentElement;
            var boton = document.querySelector('[data-tema-toggle]');
            if (!boton) {
                return;
            }
            function pintar() {
                var oscuro = raiz.getAttribute('data-tema') === 'oscuro';
                boton.setAttribute('aria-label', oscuro ? 'Modo claro' : 'Modo oscuro');
                boton.setAttribute('aria-pressed', oscuro ? 'true' : 'false');
            }
            pintar();
            boton.addEventListener('click', function () {
                var oscuro = raiz.getAttribute('data-tema') !== 'oscuro';
                raiz.setAttribute('data-tema', oscuro ? 'oscuro' : 'claro');
                try { localStorage.setItem('abaco-tema', oscuro ? 'oscuro' : 'claro'); } catch (e) {}
                pintar();
            });
        })();
    </script>
</body>
</html>
