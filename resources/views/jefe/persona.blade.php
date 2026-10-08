@extends('layouts.app', ['titulo' => $persona->name])

@section('contenido')
    <p><a class="enlace" href="{{ route('jefe.index') }}">Volver al equipo</a></p>
    <h1>{{ $persona->name }}</h1>
    <p>{{ $persona->email }}</p>
    <p class="secundario">Centro en {{ $persona->municipality }}. El registro cuenta desde {{ $persona->starts_on ? \App\Support\Tiempo::fecha($persona->starts_on) : 'sin fecha' }}.</p>

    <div class="estado estado-consulta" aria-live="polite">
        <img class="icono-estado icono-pequeno" src="{{ asset('marca/iconos/'.$estado['icono']) }}" alt="">
        <div>
            <p class="etiqueta-estado">Hoy</p>
            <p class="estado-texto estado-texto-corto">{{ $estado['texto'] }}</p>
        </div>
    </div>

    <p>
        <a class="boton boton-principal" href="{{ route('jefe.registro', $persona) }}">Ver registro</a>
    </p>
    <p class="secundario">Para abrir el registro hace falta un motivo. La consulta queda escrita.</p>

    <h2>Horario</h2>
    <p class="secundario">Los cambios de horario los hace el responsable. Cada horario nuevo vale desde su fecha y los días anteriores conservan el suyo.</p>
    <ul class="lista">
        @forelse ($horarios as $horario)
            <li>
                <span>Desde {{ \App\Support\Tiempo::fecha($horario->effective_from) }}</span>
                <strong>{{ $horario->texto() }}</strong>
            </li>
        @empty
            <li>Sin horario.</li>
        @endforelse
    </ul>

    <h2>Ausencias</h2>
    <ul class="lista">
        @forelse ($ausencias as $ausencia)
            <li>
                <span>{{ \App\Support\Tiempo::fecha($ausencia->absence_date) }}</span>
                <strong>{{ $ausencia->tipoTexto() }}</strong>
            </li>
        @empty
            <li>Sin ausencias previstas.</li>
        @endforelse
    </ul>

    @if ($persona->active === false)
        <p class="secundario">Acceso cerrado. El registro se conserva.</p>
    @endif

    @if ($consultas->isNotEmpty())
        <h2>Consultas de su registro</h2>
        <ul class="lista">
            @foreach ($consultas as $visita)
                <li>
                    <span>{{ $visita->viewer->name }}@if ($visita->reason). {{ $visita->reason }}@endif</span>
                    <span class="secundario">{{ \App\Support\Tiempo::fecha($visita->created_at) }} {{ $visita->created_at->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
