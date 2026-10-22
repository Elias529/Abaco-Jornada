@extends('layouts.app', ['titulo' => 'Consultas'])

@section('contenido')
    <div class="consultas-pagina">
        <header class="consultas-cabeza">
            <div>
                <p class="espacio-sobre">Equipo</p>
                <h1>Consultas a registros</h1>
                <span class="sr">Registros consultados</span>
                <p class="secundario">Últimas 100 consultas. Se conserva quién entró, cuándo y por qué.</p>
            </div>
            <span class="equipo-solo-lectura">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12s3-5 8-5 8 5 8 5-3 5-8 5-8-5-8-5z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2" fill="currentColor"/></svg>
                Solo lectura
            </span>
        </header>

        <section class="consultas-tabla" aria-label="Registro de consultas">
            @if ($consultas->isEmpty())
                <p class="registro-vacio">Todavía no hay consultas.</p>
            @else
                <div class="consultas-tabla-cabeza">
                    <span>Quién consultó</span>
                    <span>Registro consultado</span>
                    <span>Motivo</span>
                    <span>Fecha y hora</span>
                </div>
                @foreach ($consultas as $consulta)
                    <div class="consultas-fila">
                        <span>{{ $consulta->viewer->name }}</span>
                        <span>{{ $consulta->subject->name }}</span>
                        <span>{{ $consulta->reason ?: '—' }}</span>
                        <span>{{ $consulta->created_at->locale('es')->isoFormat('D MMM YYYY') }} · {{ $consulta->created_at->format('H:i') }}</span>
                    </div>
                    <span class="sr">{{ $consulta->viewer->name }} consultó el registro de {{ $consulta->subject->name }}</span>
                @endforeach
            @endif
            <div class="consultas-pie">
                <span>{{ $consultas->count() }} {{ $consultas->count() === 1 ? 'consulta' : 'consultas' }} de muestra</span>
                <span>Registro de acceso</span>
            </div>
        </section>
    </div>
@endsection
