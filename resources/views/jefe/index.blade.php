@extends('layouts.app', ['titulo' => 'Equipo'])

@section('contenido')
    <h1>Equipo</h1>
    <p class="fecha">{{ $fecha }}</p>
    <p class="secundario">Ves el horario y el estado de cada persona. Para abrir su registro hace falta un motivo. Nada de lo que ves se puede cambiar desde aquí.</p>

    <section class="resumen resumen-fila" aria-label="Ahora">
        <div>
            <span>Trabajando</span>
            <strong>{{ $cuentas['trabajando'] }}</strong>
        </div>
        <div>
            <span>En pausa</span>
            <strong>{{ $cuentas['pausa'] }}</strong>
        </div>
        <div>
            <span>Con algo pendiente</span>
            <strong>{{ $cuentas['pendiente'] }}</strong>
        </div>
    </section>

    @if ($personas->isEmpty())
        <p>Todavía no hay personas en el equipo.</p>
    @else
        <ul class="lista">
            @foreach ($personas as $fila)
                <li>
                    <a href="{{ route('jefe.persona', $fila['persona']) }}">
                        <span class="persona-estado">
                            <img class="icono-estado icono-pequeno" src="{{ asset('marca/iconos/'.$fila['estado']['icono']) }}" alt="">
                            <span>{{ $fila['persona']->name }}</span>
                        </span>
                        <strong>{{ $fila['estado']['texto'] }}</strong>
                    </a>
                    <span class="secundario">
                        {{ $fila['horario'] ? $fila['horario']->texto() : 'Sin horario asignado' }}
                        · {{ $fila['persona']->municipality }}
                        @if ($fila['persona']->esResponsable())
                            · responsable
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
