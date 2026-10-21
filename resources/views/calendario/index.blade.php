@extends('layouts.app', ['ancha' => true, 'titulo' => 'Calendario'])

@section('contenido')
    <h1>Calendario</h1>

    <ul class="leyenda">
        <li><span class="muestra muestra-estado"></span> Festivo Nacional</li>
        <li><span class="muestra muestra-comunidad"></span> Festivo Autonómico</li>
        <li><span class="muestra muestra-hoy"></span> Hoy</li>
        <li><span class="muestra muestra-vacaciones"></span> Vacaciones <strong class="vacaciones-total">{{ $diasVacaciones }}</strong></li>
    </ul>

    <div data-calendario>
        @include('calendario.mes')
    </div>

    <div class="vacaciones-pedir">
        <button class="vacaciones-abre" type="button" data-vacaciones-pedir>Solicitar vacaciones</button>
        <p class="vacaciones-aviso" data-vacaciones-texto hidden>Elige un rango de fechas</p>
        <div class="vacaciones-acciones" data-vacaciones-acciones hidden>
            <button class="vacaciones-tick" type="button" data-vacaciones-aceptar aria-label="Aceptar">✓</button>
            <button class="vacaciones-equis" type="button" data-vacaciones-cancelar aria-label="Cancelar">✕</button>
        </div>
        <form method="post" action="{{ route('calendario.vacaciones') }}" data-vacaciones-form hidden>
            @csrf
            <input type="hidden" name="desde" data-vacaciones-desde>
            <input type="hidden" name="hasta" data-vacaciones-hasta>
        </form>
    </div>
@endsection
