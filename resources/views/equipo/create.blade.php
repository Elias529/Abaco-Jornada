@extends('layouts.app', ['ancha' => true, 'titulo' => 'Alta'])

@section('contenido')
    <p><a class="enlace" href="{{ route('equipo.index') }}">Volver al equipo</a></p>
    <h1>Dar de alta</h1>
    <p class="secundario">Nombre, acceso, centro y horario. Con eso ya entra y ve su horario. No hay más trabajo diario.</p>
    <form method="post" action="{{ route('equipo.store') }}" class="formulario">
        @csrf
        <label for="name">Nombre</label>
        <input id="name" name="name" value="{{ old('name') }}" required maxlength="120">

        <label for="email">Correo</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required>

        <label for="password">Contraseña inicial</label>
        <input id="password" name="password" type="text" required minlength="8" autocomplete="new-password">

        <label for="starts_on">El registro cuenta desde</label>
        <input id="starts_on" name="starts_on" type="date" value="{{ old('starts_on', now()->toDateString()) }}" required>

        <label for="municipality">Municipio del centro</label>
        <input id="municipality" name="municipality" value="{{ old('municipality') }}" required maxlength="120">
        <p class="secundario">Los festivos son los del centro de Madrid, también si la persona está en otra comunidad.</p>

        <label for="morning_start">Empieza</label>
        <x-hora id="morning_start" name="morning_start" :valor="old('morning_start', '09:00')" />
        <label for="morning_end">Fin de la mañana</label>
        <x-hora id="morning_end" name="morning_end" :valor="old('morning_end', '14:00')" />
        <label for="afternoon_start">Vuelta</label>
        <x-hora id="afternoon_start" name="afternoon_start" :valor="old('afternoon_start', '15:00')" />
        <label for="afternoon_end">Fin de la jornada</label>
        <x-hora id="afternoon_end" name="afternoon_end" :valor="old('afternoon_end', '18:00')" />

        <button class="boton boton-principal" type="submit">Guardar alta</button>
    </form>
@endsection
