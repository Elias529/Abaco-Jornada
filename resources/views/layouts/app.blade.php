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
                <a href="{{ route('calendario') }}" @if (request()->routeIs('calendario')) aria-current="page" @endif>Calendario</a>
                @if (auth()->user()->esJefa())
                    <a href="{{ route('equipo.index') }}" @if (request()->routeIs('equipo.*')) aria-current="page" @endif>Equipo</a>
                    <a href="{{ route('jefe.index') }}" @if (request()->routeIs('jefe.index', 'jefe.persona', 'jefe.registro', 'jefe.dia')) aria-current="page" @endif>Estado</a>
                    <a href="{{ route('jefe.consultas') }}" @if (request()->routeIs('jefe.consultas')) aria-current="page" @endif>Consultas</a>
                    <a href="{{ route('incidencias.index') }}" @if (request()->routeIs('incidencias.*')) aria-current="page" @endif>Incidencias</a>
                @endif
                @if (auth()->user()->esTecnico())
                    <a href="{{ route('tecnico.index') }}" @if (request()->routeIs('tecnico.index', 'tecnico.copia', 'tecnico.actualizar')) aria-current="page" @endif>Sistema</a>
                    <a href="{{ route('tecnico.tickets') }}" @if (request()->routeIs('tecnico.tickets*', 'tickets.show')) aria-current="page" @endif>Tickets</a>
                @else
                    <a href="{{ route('tickets.index', ['desde' => request()->routeIs('tickets.*') ? null : '/'.ltrim(request()->path(), '/')]) }}" @if (request()->routeIs('tickets.*')) aria-current="page" @endif>Ayuda</a>
                @endif
            </nav>
            <div class="barra-cuenta">
                @if (auth()->user()->esJefa() && request()->routeIs('jefe.*'))
                    <span class="solo-lectura">Solo lectura</span>
                @endif
                <span class="persona">{{ auth()->user()->name }}</span>
                <button type="button" class="tema" data-tema-toggle>Modo oscuro</button>
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
                boton.textContent = oscuro ? 'Modo claro' : 'Modo oscuro';
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
