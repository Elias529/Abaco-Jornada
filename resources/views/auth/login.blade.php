@extends('layouts.app', ['acceso' => true, 'titulo' => 'Entrar'])

@section('contenido')
    <div class="marca-acceso">
        <img class="isotipo isotipo-acceso" src="{{ asset('marca/logos/abacoqd_isotipo.svg') }}" alt="">
        <h1>Ábaco Jornada</h1>
    </div>
    <p class="secundario">Entra con el correo que te dio tu jefa.</p>
    <button type="button" class="tema" data-tema-toggle>Modo oscuro</button>

    <form method="post" action="{{ route('login') }}" class="formulario">
        @csrf
        <label for="email">Correo</label>
        <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required>

        <label for="password">Contraseña</label>
        <div class="campo-clave">
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button type="button" class="ver-clave" data-ver-clave aria-controls="password" aria-pressed="false" aria-label="Ver contraseña">
                <svg class="ojo ojo-abierto" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="ojo ojo-cerrado" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                    <path d="M3 3l18 18"/>
                    <path d="M10.6 6.1A10.7 10.7 0 0 1 12 6c6.5 0 10 6 10 6a18.5 18.5 0 0 1-4.1 4.6"/>
                    <path d="M6.7 6.7C3.6 8.6 2 12 2 12s3.5 7 10 7a9.8 9.8 0 0 0 4.2-.9"/>
                    <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                </svg>
            </button>
        </div>

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
    <script>
        (function () {
            var boton = document.querySelector('[data-ver-clave]');
            var campo = document.getElementById('password');
            if (!boton || !campo) {
                return;
            }
            boton.addEventListener('click', function () {
                var visible = campo.type === 'password';
                campo.type = visible ? 'text' : 'password';
                boton.querySelector('.ojo-abierto').toggleAttribute('hidden', visible);
                boton.querySelector('.ojo-cerrado').toggleAttribute('hidden', !visible);
                boton.setAttribute('aria-pressed', visible ? 'true' : 'false');
                boton.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Ver contraseña');
            });
        })();
    </script>
@endsection
