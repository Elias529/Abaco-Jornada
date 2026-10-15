@extends('layouts.app', ['titulo' => 'Incidencia'])

@section('contenido')
    <a class="volver" href="{{ route('jornada') }}">Volver a hoy</a>

    <section class="panel pantalla-formulario">
        <h1>Incidencia</h1>
        <p class="secundario pantalla-texto">Cuenta qué ha pasado. La jefa lo verá en Incidencias y el técnico lo recibe en Ayuda. No hace falta escribir las horas.</p>

        <form method="post" action="{{ route('jornada.incidencia') }}" class="formulario">
            @csrf
            <label for="incidencia-motivo">Qué pasó</label>
            <textarea id="incidencia-motivo" name="motivo" maxlength="500" required>{{ old('motivo') }}</textarea>

            <button class="boton boton-principal" type="submit">Enviar Incidencia</button>
        </form>
    </section>
@endsection
