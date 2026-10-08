<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? 'Jornada' }} · Ábaco</title>
    <link rel="icon" href="{{ asset('marca/logos/abacoqd_isotipo.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/paleta.css') }}?v={{ filemtime(public_path('css/paleta.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/jornada.css') }}?v={{ filemtime(public_path('css/jornada.css')) }}">
</head>
<body @if ($pendiente ?? false) data-pendiente="1" @endif>
    <a class="saltar" href="#contenido">Saltar al contenido</a>
    <div class="ventana {{ $ancha ?? false ? 'ventana-ancha' : '' }} {{ ($compacta ?? false) ? 'ventana-fichar' : '' }}">
        @unless ($compacta ?? false)
            <header class="cabecera">
                <img class="isotipo" src="{{ asset('marca/logos/abacoqd_isotipo.svg') }}" alt="Ábaco">
                <div>
                    <p class="nombre">Jornada</p>
                    @auth
                        <p class="persona">{{ auth()->user()->name }}</p>
                    @endauth
                </div>
            </header>

            @auth
                <nav class="menu" aria-label="Secciones">
                    <a href="{{ route('jornada') }}">Hoy</a>
                    <a href="{{ route('registro') }}">Mi registro</a>
                    @if (auth()->user()->esResponsable())
                        <a href="{{ route('equipo.index') }}">Equipo</a>
                        <a href="{{ route('calendario') }}">Calendario</a>
                    @endif
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Salir</button>
                    </form>
                </nav>
            @endauth
        @endunless

        <p class="sin-conexion" data-sin-conexion hidden>Sin conexión. Lo guardamos y lo enviamos al volver.</p>

        @unless ($compacta ?? false)
            @if (session('ok'))
                <p class="guardado" role="status">{{ session('ok') }}</p>
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

        <p class="nota-datos">Guardamos tu nombre, tu horario y las horas de la jornada. No guardamos ubicación.</p>
    </div>
    <script src="{{ asset('js/reloj.js') }}?v={{ filemtime(public_path('js/reloj.js')) }}"></script>
</body>
</html>
