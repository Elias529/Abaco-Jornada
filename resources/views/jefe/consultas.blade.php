@extends('layouts.app', ['titulo' => 'Consultas'])

@section('contenido')
    <h1>Consultas</h1>
    <p class="secundario">Quién ha abierto el registro de otra persona, cuándo y por qué. Esta lista no se puede cambiar. Se muestran las últimas 100.</p>

    @if ($consultas->isEmpty())
        <p>Todavía no hay consultas.</p>
    @else
        <ul class="lista">
            @foreach ($consultas as $consulta)
                <li>
                    <span>{{ $consulta->viewer->name }} consultó el registro de {{ $consulta->subject->name }}</span>
                    @if ($consulta->reason)
                        <span>{{ $consulta->reason }}</span>
                    @endif
                    <span class="secundario">{{ \App\Support\Tiempo::fecha($consulta->created_at) }} {{ $consulta->created_at->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
