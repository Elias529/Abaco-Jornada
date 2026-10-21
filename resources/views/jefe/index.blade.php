@extends('layouts.app', ['titulo' => 'Estado'])

@section('contenido')
    <h1>Estado del equipo</h1>
    <p class="fecha">{{ $fecha }}</p>
    <p class="secundario">Ves el horario y el estado de cada persona. Para abrir su registro hace falta un motivo. Nada de lo que ves se puede cambiar desde aquí; para dar de alta o cambiar horarios, usa Equipo.</p>

    @if ($conCierresAntiguos > 0)
        <p class="franja" role="status">
            <span class="franja-texto">
                <strong>{{ $conCierresAntiguos === 1 ? '1 persona tiene' : $conCierresAntiguos.' personas tienen' }} cierres sin resolver de hace más de {{ $diasHastaAvisar }} días.</strong>
                <span>Abre su registro indicando el motivo de consulta.</span>
            </span>
        </p>
    @endif

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
                        @if ($fila['persona']->esJefa())
                            · jefa
                        @elseif ($fila['persona']->esTecnico())
                            · técnico
                        @endif
                    </span>
                    @if ($fila['antiguos'] > 0)
                        <strong>
                            {{ $fila['antiguos'] === 1 ? '1 cierre sin resolver' : $fila['antiguos'].' cierres sin resolver' }}
                            de hace más de {{ $diasHastaAvisar }} días
                        </strong>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
@endsection
