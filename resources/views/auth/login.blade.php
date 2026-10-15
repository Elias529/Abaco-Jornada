@extends('layouts.app', ['acceso' => true, 'titulo' => 'Entrar'])

@section('contenido')
    <div class="marca-acceso">
        <img class="isotipo isotipo-acceso" src="{{ asset('marca/logos/abacoqd_isotipo.svg') }}" alt="">
        <h1>Ábaco Jornada</h1>
    </div>
    <p class="secundario">Entra con el correo que te dio tu jefa.</p>

    <form method="post" action="{{ route('login') }}" class="formulario">
        @csrf
        <label for="email">Correo</label>
        <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required>

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <button class="boton boton-principal" type="submit">Entrar</button>
    </form>

    @if (app()->environment('local', 'testing'))
        <details class="pruebas">
            <summary>Cuentas de prueba</summary>
            <p>Ana López · ana.lopez@abaco.test</p>
            <p>Marta Ruiz, jefa · marta.ruiz@abaco.test</p>
            <p>Carmen Ortega, administradora técnica · carmen.ortega@abaco.test</p>
            <p>Contraseña: Jornada2026</p>
        </details>
    @endif
@endsection
