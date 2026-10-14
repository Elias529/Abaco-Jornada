@extends('layouts.app', ['titulo' => 'Sistema'])

@php
    $kb = fn (?int $bytes) => $bytes === null ? 'sin archivo' : number_format($bytes / 1024, 1, ',', '.').' KB';
    $filas = fn (int $n) => $n.' '.($n === 1 ? 'fila' : 'filas');
@endphp

@section('contenido')
    <h1>Sistema</h1>
    <p class="secundario">Aquí ves el estado de la base de datos activa y la actualizas. No ves los registros de nadie.</p>

    <section class="resumen resumen-fila" aria-label="Resumen">
        <div>
            <span>Base de datos</span>
            <strong>{{ $base['responde'] ? 'Responde' : 'No responde' }}</strong>
        </div>
        <div>
            <span>Integridad</span>
            <strong>
                @if ($base['integridad'] === null)
                    Sin comprobar
                @else
                    {{ $base['integridad']['correcta'] ? 'Correcta' : 'Con fallos' }}
                @endif
            </strong>
        </div>
        <div>
            <span>Cambios pendientes</span>
            <strong>{{ count($base['pendientes']) }}</strong>
        </div>
    </section>

    <article class="tarjeta">
        <h2>Base de datos activa</h2>
        <p>{{ strtoupper($base['driver']) }} · {{ $base['nombre'] }} · {{ $kb($base['bytes']) }}</p>
        <p class="secundario">Responde en {{ $base['milisegundos'] }} ms. {{ $base['aplicadas'] }} cambios aplicados.</p>
        @if ($base['error'])
            <p class="franja" role="alert">{{ $base['error'] }}</p>
        @endif
        @if ($base['integridad'])
            <p class="secundario">{{ $base['integridad']['texto'] }}</p>
        @endif
        <ul class="lista">
            @foreach ($base['tablas'] as $tabla)
                <li class="lista-tecnica">
                    <span>{{ $tabla['nombre'] }}</span>
                    <strong>{{ $filas($tabla['filas']) }}</strong>
                </li>
            @endforeach
        </ul>
    </article>

    <article class="tarjeta">
        <h2>Actualizaciones</h2>
        <p class="secundario">Laravel {{ $entorno['laravel'] }} · PHP {{ $entorno['php'] }} · entorno {{ $entorno['entorno'] }} · correo {{ $entorno['correo'] }}@if ($entorno['depuracion']) · depuración activa @endif</p>
        @if ($base['pendientes'] === [])
            <p>Todo está al día.</p>
        @else
            <p>Hay {{ count($base['pendientes']) }} cambios sin aplicar:</p>
            <ul class="lista">
                @foreach ($base['pendientes'] as $pendiente)
                    <li class="lista-tecnica"><span>{{ $pendiente }}</span></li>
                @endforeach
            </ul>
        @endif
        <form method="post" action="{{ route('tecnico.actualizar') }}" class="formulario">
            @csrf
            <label class="casilla" for="confirmo">
                <input id="confirmo" name="confirmo" type="checkbox" value="1">
                <span>Quiero actualizar. Antes se hace una copia.</span>
            </label>
            <button class="boton boton-principal" type="submit">Actualizar</button>
        </form>
    </article>

    <article class="tarjeta">
        <h2>Copias</h2>
        <form method="post" action="{{ route('tecnico.copia') }}">
            @csrf
            <button class="boton boton-secundario" type="submit">Hacer una copia ahora</button>
        </form>
        @if ($copias === [])
            <p class="secundario">Todavía no hay copias.</p>
        @else
            <ul class="lista">
                @foreach ($copias as $copia)
                    <li class="lista-tecnica">
                        <span>{{ $copia['nombre'] }}</span>
                        <strong>{{ $copia['fecha'] }} · {{ $kb($copia['bytes']) }}</strong>
                    </li>
                @endforeach
            </ul>
        @endif
    </article>

    <article class="tarjeta">
        <h2>Lo que se ha hecho</h2>
        @if ($acciones->isEmpty())
            <p class="secundario">Todavía no se ha hecho nada.</p>
        @else
            <ul class="lista">
                @foreach ($acciones as $accion)
                    <li class="lista-tecnica">
                        <span>{{ $accion->created_at->format('Y-m-d H:i') }} · {{ $accion->autor->name }} · {{ $accion->accion === 'copia' ? 'Copia' : 'Actualización' }}</span>
                        <span class="secundario">{{ $accion->detalle }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </article>
@endsection
