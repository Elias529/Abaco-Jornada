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
    <style>@layer reset, tokens, base, componentes, pantallas;</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body @class(['es-jefa' => auth()->check() && auth()->user()->esJefa()]) @if ($pendiente ?? false) data-pendiente="1" @endif>
    <a class="saltar" href="#contenido">Saltar al contenido</a>

    @auth
        @php
            $partes = preg_split('/\s+/', trim(auth()->user()->name)) ?: [];
            $iniciales = mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1).mb_substr($partes[1] ?? '', 0, 1));
            $papel = auth()->user()->esJefa() ? 'Jefa' : (auth()->user()->esTecnico() ? 'Técnico' : 'Trabajadora');
        @endphp
        <header class="barra lateral">
            <a class="marca-logo" href="{{ route('jornada') }}">
                <img class="isotipo" src="{{ asset('marca/logos/abacoqd_isotipo.svg') }}" alt="">
                <span>
                    <span class="marca-nombre">Jornada</span>
                    <span class="marca-sub">ÁbacoQD</span>
                </span>
            </a>
            <nav class="menu" aria-label="Secciones">
                <p class="lateral-kicker">Mi jornada</p>
                <a href="{{ route('jornada') }}" @if (request()->routeIs('jornada')) aria-current="page" @endif>
                    <x-icono nombre="clock" />
                    Hoy
                </a>
                <a href="{{ route('registro') }}" @if (request()->routeIs('registro', 'registro.show', 'registro.dia')) aria-current="page" @endif>
                    <x-icono nombre="list" />
                    Mi registro
                </a>
                <a href="{{ route('calendario') }}" @if (request()->routeIs('calendario')) aria-current="page" @endif>
                    <x-icono nombre="calendar" />
                    Calendario
                </a>
                @unless (auth()->user()->esJefa())
                    <a href="{{ route('jornada.incidencia.crear') }}" @if (request()->routeIs('jornada.incidencia.crear')) aria-current="page" @endif @if (($incidenciasNuevas ?? 0) > 0) aria-label="Incidencia, {{ $incidenciasNuevas === 1 ? '1 nueva' : $incidenciasNuevas.' nuevas' }}" @endif>
                        <x-icono nombre="inbox" />
                        Incidencia
                        @if (($incidenciasNuevas ?? 0) > 0)
                            <span class="aviso-ticket" aria-hidden="true">{{ $incidenciasNuevas }}</span>
                        @endif
                    </a>
                @endunless
                @if (auth()->user()->esJefa())
                    <p class="lateral-kicker">Equipo</p>
                    <a href="{{ route('equipo.index') }}" @if (request()->routeIs('equipo.*')) aria-current="page" @endif>
                        <x-icono nombre="user-plus" />
                        Personas
                    </a>
                    <a href="{{ route('jefe.index') }}" @if (request()->routeIs('jefe.index', 'jefe.persona', 'jefe.registro', 'jefe.dia')) aria-current="page" @endif>
                        <x-icono nombre="users" />
                        Estado del equipo
                    </a>
                    <a href="{{ route('incidencias.index') }}" @if (request()->routeIs('incidencias.*')) aria-current="page" @endif @if (($incidenciasNuevas ?? 0) > 0) aria-label="Incidencias, {{ $incidenciasNuevas === 1 ? '1 nueva' : $incidenciasNuevas.' nuevas' }}" @endif>
                        <x-icono nombre="inbox" />
                        Incidencias
                        @if (($incidenciasNuevas ?? 0) > 0)
                            <span class="aviso-ticket" aria-hidden="true">{{ $incidenciasNuevas }}</span>
                        @endif
                    </a>
                    <a href="{{ route('jefe.consultas') }}" @if (request()->routeIs('jefe.consultas')) aria-current="page" @endif>
                        <x-icono nombre="eye" />
                        Consultas
                    </a>
                @endif
                @if (auth()->user()->esTecnico())
                    <a href="{{ route('tecnico.index') }}" @if (request()->routeIs('tecnico.index', 'tecnico.copia', 'tecnico.actualizar', 'tecnico.restaurar')) aria-current="page" @endif>
                        <x-icono nombre="sliders" />
                        Sistema
                    </a>
                    <a href="{{ route('tecnico.tickets') }}" @if (request()->routeIs('tecnico.tickets*', 'tickets.show')) aria-current="page" @endif @if (($ticketsNuevos ?? 0) > 0) aria-label="Tickets, {{ $ticketsNuevos === 1 ? '1 nuevo' : $ticketsNuevos.' nuevos' }}" @endif>
                        <x-icono nombre="ticket" />
                        Tickets
                        @if (($ticketsNuevos ?? 0) > 0)
                            <span class="aviso-ticket" aria-hidden="true">{{ $ticketsNuevos }}</span>
                        @endif
                    </a>
                @endif
                @unless (auth()->user()->esTecnico())
                    <div class="menu-pie">
                        <a href="{{ route('tickets.index', ['desde' => request()->routeIs('tickets.*') ? null : '/'.ltrim(request()->path(), '/')]) }}" @if (request()->routeIs('tickets.*')) aria-current="page" @endif>
                            <x-icono nombre="help" />
                            Ayuda
                        </a>
                    </div>
                @endunless
            </nav>
            <div class="lateral-cuenta">
                <span class="lateral-inicial" aria-hidden="true">{{ $iniciales }}</span>
                <span class="lateral-quien">
                    <span class="persona">{{ auth()->user()->name }}</span>
                    <span class="lateral-papel">{{ $papel }}</span>
                </span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="lateral-salir" type="submit" aria-label="Salir">
                        <x-icono nombre="logout" />
                    </button>
                </form>
            </div>
        </header>
    @endauth

    @auth
        @php
            $fechaCorta = now()->locale('es');
            $fechaBarra = ucfirst($fechaCorta->isoFormat('dddd')).', '.$fechaCorta->day.' '.rtrim($fechaCorta->isoFormat('MMM'), '.').' '.$fechaCorta->year;
        @endphp
        <div class="cuerpo">
            <header class="cabecera-app">
                <a class="cabecera-logo" href="{{ route('jornada') }}">
                    <img class="isotipo" src="{{ asset('marca/logos/abacoqd_isotipo.svg') }}" alt="">
                    <span>
                        <span class="marca-nombre">Jornada</span>
                        <span class="marca-sub">ÁbacoQD</span>
                    </span>
                </a>
                <button class="cabecera-plegar" type="button" data-plegar aria-label="Plegar navegación" title="Plegar navegación">
                    <x-icono nombre="list" />
                </button>
                <nav class="migas" aria-label="Ruta">
                    <a href="{{ route('jornada') }}">Ábaco Jornada</a>
                    @if (! empty($miga['url'] ?? null))
                        <span aria-hidden="true">›</span>
                        <a href="{{ $miga['url'] }}">{{ $miga['texto'] }}</a>
                    @endif
                    <span aria-hidden="true">›</span>
                    <span>{{ $titulo ?? 'Hoy' }}</span>
                </nav>
                <span class="cabecera-fecha">{{ $fechaBarra }}</span>
                <button type="button" class="tema" data-tema-toggle aria-label="Cambiar a modo oscuro">
                    <x-icono nombre="moon" class="tema-luna" />
                    <x-icono nombre="sun" class="tema-sol" />
                </button>
                <span class="cabecera-inicial" aria-hidden="true">{{ $iniciales }}</span>
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
    @auth
        </div>
    @endauth
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
                boton.setAttribute('aria-label', oscuro ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
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
        (function () {
            var plegar = document.querySelector('[data-plegar]');
            if (!plegar) {
                return;
            }
            function pintarNav(plegada) {
                document.body.classList.toggle('nav-plegada', plegada);
                plegar.setAttribute('aria-label', plegada ? 'Mostrar navegación' : 'Plegar navegación');
                plegar.setAttribute('title', plegada ? 'Mostrar navegación' : 'Plegar navegación');
                plegar.setAttribute('aria-pressed', plegada ? 'true' : 'false');
            }
            var guardada = false;
            try { guardada = localStorage.getItem('abaco-nav') === 'plegada'; } catch (e) {}
            pintarNav(guardada);
            plegar.addEventListener('click', function () {
                var plegada = !document.body.classList.contains('nav-plegada');
                pintarNav(plegada);
                try { localStorage.setItem('abaco-nav', plegada ? 'plegada' : 'abierta'); } catch (e) {}
            });
        })();
    </script>
</body>
</html>
