@extends('layouts.app', ['ancha' => true, 'titulo' => 'Equipo'])

@section('contenido')
    <h1>Equipo</h1>
    <p><a class="boton boton-principal" href="{{ route('equipo.create') }}">Dar de alta</a></p>
    <ul class="lista">
        @foreach ($personas as $persona)
            <li>
                <a href="{{ route('equipo.show', $persona) }}">
                    <span>{{ $persona->name }}</span>
                    <strong>{{ $persona->esResponsable() ? 'Responsable' : 'Trabajadora' }}</strong>
                </a>
                <span class="secundario">
                    {{ $persona->municipality }}
                    @if (! $persona->active)
                        · acceso cerrado
                    @endif
                </span>
            </li>
        @endforeach
    </ul>
@endsection
