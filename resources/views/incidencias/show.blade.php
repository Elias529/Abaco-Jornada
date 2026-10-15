@extends('layouts.app', ['titulo' => 'Incidencia'])

@section('contenido')
    <p>
        <a class="enlace" href="{{ route('incidencias.index') }}">Volver a incidencias</a>
    </p>
    <h1>Incidencia</h1>
    <p class="secundario">
        Enviada por {{ $incidencia->autor->name }}
        el {{ \App\Support\Tiempo::fecha($incidencia->created_at) }} a las {{ $incidencia->created_at->format('H:i') }}
    </p>

    <article class="tarjeta tarjeta-incidencia">
        <p class="incidencia-marca">Incidencia</p>
        <p>{!! nl2br(e($incidencia->cuerpo)) !!}</p>
    </article>
@endsection
