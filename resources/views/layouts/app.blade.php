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
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4.5l2.5 1.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Hoy
                </a>
                <a href="{{ route('registro') }}" @if (request()->routeIs('registro', 'registro.show', 'registro.dia')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7h10M8 12h10M8 17h7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Mi registro
                </a>
                <a href="{{ route('calendario') }}" @if (request()->routeIs('calendario')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M4 10h16M8 3.5v3M16 3.5v3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Calendario
                </a>
                @unless (auth()->user()->esJefa())
                    <a href="{{ route('jornada.incidencia.crear') }}" @if (request()->routeIs('jornada.incidencia.crear')) aria-current="page" @endif @if (($incidenciasNuevas ?? 0) > 0) aria-label="Incidencia, {{ $incidenciasNuevas === 1 ? '1 nueva' : $incidenciasNuevas.' nuevas' }}" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4" width="12" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9 9h6M9 13h6M9 17h3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        Incidencia
                        @if (($incidenciasNuevas ?? 0) > 0)
                            <span class="aviso-ticket" aria-hidden="true">{{ $incidenciasNuevas }}</span>
                        @endif
                    </a>
                @endunless
                @if (auth()->user()->esJefa())
                    <p class="lateral-kicker">Equipo</p>
                    <a href="{{ route('equipo.index') }}" @if (request()->routeIs('equipo.*')) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="2.2" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="16" cy="9.2" r="1.7" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M4.2 18.2c.7-2.5 2.5-3.7 4.8-3.7s4.1 1.2 4.8 3.7M14.2 14.7c1.3-.3 2.6 0 3.5.9.9.8 1.4 1.9 1.6 3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        Personas
                    </a>
                    <a href="{{ route('jefe.index') }}" @if (request()->routeIs('jefe.index', 'jefe.persona', 'jefe.registro', 'jefe.dia')) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h3.5l2.2-6 3.6 10 2.2-4H20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Estado del equipo
                    </a>
                    <a href="{{ route('incidencias.index') }}" @if (request()->routeIs('incidencias.*')) aria-current="page" @endif @if (($incidenciasNuevas ?? 0) > 0) aria-label="Incidencias, {{ $incidenciasNuevas === 1 ? '1 nueva' : $incidenciasNuevas.' nuevas' }}" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4" width="12" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9 9h6M9 13h6M9 17h3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        Incidencias
                        @if (($incidenciasNuevas ?? 0) > 0)
                            <span class="aviso-ticket" aria-hidden="true">{{ $incidenciasNuevas }}</span>
                        @endif
                    </a>
                    <a href="{{ route('jefe.consultas') }}" @if (request()->routeIs('jefe.consultas')) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6.5h14a1.5 1.5 0 0 1 1.5 1.5v7A1.5 1.5 0 0 1 19 16.5H9.5L5 20v-3.5H5A1.5 1.5 0 0 1 3.5 15V8A1.5 1.5 0 0 1 5 6.5z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                        Consultas
                    </a>
                @endif
                @if (auth()->user()->esTecnico())
                    <a href="{{ route('tecnico.index') }}" @if (request()->routeIs('tecnico.index', 'tecnico.copia', 'tecnico.actualizar')) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 3.8v2.1M12 18.1v2.1M3.8 12h2.1M18.1 12h2.1M6.2 6.2l1.5 1.5M16.3 16.3l1.5 1.5M17.8 6.2l-1.5 1.5M7.7 16.3l-1.5 1.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        Sistema
                    </a>
                    <a href="{{ route('tecnico.tickets') }}" @if (request()->routeIs('tecnico.tickets*', 'tickets.show')) aria-current="page" @endif @if (($ticketsNuevos ?? 0) > 0) aria-label="Tickets, {{ $ticketsNuevos === 1 ? '1 nuevo' : $ticketsNuevos.' nuevos' }}" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7.5h14v3a2 2 0 0 0 0 4v3H5v-3a2 2 0 0 0 0-4v-3z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 8.5v8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="1.6 2.2"/></svg>
                        Tickets
                        @if (($ticketsNuevos ?? 0) > 0)
                            <span class="aviso-ticket" aria-hidden="true">{{ $ticketsNuevos }}</span>
                        @endif
                    </a>
                @endif
                @unless (auth()->user()->esTecnico())
                    <div class="menu-pie">
                        <a href="{{ route('tickets.index', ['desde' => request()->routeIs('tickets.*') ? null : '/'.ltrim(request()->path(), '/')]) }}" @if (request()->routeIs('tickets.*')) aria-current="page" @endif>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 4v2M12 18v2M4 12h2M18 12h2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            Ayuda
                        </a>
                    </div>
                @endunless
            </nav>
            <div class="lateral-cuenta">
                @if (auth()->user()->esJefa() && request()->routeIs('jefe.*'))
                    <span class="solo-lectura">Solo lectura</span>
                @endif
                <span class="lateral-inicial" aria-hidden="true">{{ $iniciales }}</span>
                <span class="lateral-quien">
                    <span class="persona">{{ auth()->user()->name }}</span>
                    <span class="lateral-papel">{{ $papel }}</span>
                </span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="lateral-salir" type="submit" aria-label="Salir">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 7V5a2 2 0 0 1 2-2h7v18h-7a2 2 0 0 1-2-2v-2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 12h10M11 8l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7.5h14M5 12h14M5 16.5h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
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
                <button type="button" class="tema" data-tema-toggle aria-label="Modo oscuro">
                    <svg class="tema-luna" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path fill="currentColor" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
                    </svg>
                    <svg class="tema-sol" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M12 2.6v2.2M12 19.2v2.2M2.6 12h2.2M19.2 12h2.2M5.2 5.2l1.6 1.6M17.2 17.2l1.6 1.6M18.8 5.2l-1.6 1.6M6.8 17.2 5.2 18.8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
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
