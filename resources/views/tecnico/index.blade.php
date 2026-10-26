@extends('layouts.app', ['titulo' => 'Sistema'])

@php
    $kb = fn (?int $bytes) => $bytes === null ? 'sin archivo' : number_format($bytes / 1024, 1, ',', '.').' KB';
    $filas = fn (int $n) => $n.' '.($n === 1 ? 'fila' : 'filas');
    $integridadCorrecta = $base['integridad'] !== null && $base['integridad']['correcta'];
    $ultimaCopia = $copias[0]['fecha'] ?? 'Todavía no hay copias';
@endphp

@section('contenido')
    <div class="sistema-pagina">
        <header class="sistema-cabeza">
            <div>
                <p class="espacio-sobre">Administración técnica</p>
                <h1>Sistema</h1>
                <p class="secundario">Base de datos, copias y actualizaciones.</p>
            </div>
            <form method="post" action="{{ route('tecnico.exportar') }}" data-exportar-bbdd>
                @csrf
                <x-boton tipo="principal" tipoHtml="submit" class="sistema-copia" title="Crear una copia y elegir dónde guardarla">
                    <x-icono nombre="archive" />
                    Hacer una copia ahora
                </x-boton>
            </form>
        </header>

        <section class="sistema-resumen" aria-label="Estado del sistema">
            <article class="sistema-resumen-card">
                <div class="sistema-resumen-titulo">
                    <x-icono nombre="database" />
                    <span>Base de datos</span>
                </div>
                <strong>{{ $base['responde'] ? 'Responde' : 'No responde' }}</strong>
                <small>{{ $base['milisegundos'] }} ms · {{ $base['responde'] ? 'conexión correcta' : 'sin conexión' }}</small>
                <span class="sistema-estado {{ $base['responde'] ? 'sistema-estado-ok' : 'sistema-estado-error' }}">
                    <x-icono :nombre="$base['responde'] ? 'circle-check' : 'triangle-alert'" />
                    {{ $base['responde'] ? 'Correcto' : 'Revisar' }}
                </span>
            </article>

            <article class="sistema-resumen-card">
                <div class="sistema-resumen-titulo">
                    <x-icono nombre="shield-check" />
                    <span>Integridad</span>
                </div>
                <strong>{{ $base['integridad'] === null ? 'Sin comprobar' : ($integridadCorrecta ? 'Correcta' : 'Con fallos') }}</strong>
                <small>{{ $base['integridad']['texto'] ?? 'No se ha podido comprobar todavía' }}</small>
                <span class="sistema-estado {{ $integridadCorrecta ? 'sistema-estado-ok' : 'sistema-estado-aviso' }}">
                    <x-icono :nombre="$integridadCorrecta ? 'circle-check' : 'triangle-alert'" />
                    {{ $integridadCorrecta ? 'Correcto' : 'Revisar' }}
                </span>
            </article>

            <article class="sistema-resumen-card">
                <div class="sistema-resumen-titulo">
                    <x-icono nombre="archive" />
                    <span>Copias</span>
                </div>
                <strong>{{ count($copias) }} {{ count($copias) === 1 ? 'copia' : 'copias' }}</strong>
                <small>Última: {{ $ultimaCopia }}</small>
                <span class="sistema-estado sistema-estado-ok">
                    <x-icono nombre="circle-check" />
                    Correcto
                </span>
            </article>

            <article class="sistema-resumen-card">
                <div class="sistema-resumen-titulo">
                    <x-icono nombre="refresh" />
                    <span>Actualizaciones</span>
                </div>
                <strong>{{ $base['pendientes'] === [] ? 'Al día' : count($base['pendientes']).' pendientes' }}</strong>
                <small>{{ $base['aplicadas'] }} cambios aplicados</small>
                <span class="sistema-estado {{ $base['pendientes'] === [] ? 'sistema-estado-ok' : 'sistema-estado-aviso' }}">
                    <x-icono :nombre="$base['pendientes'] === [] ? 'circle-check' : 'triangle-alert'" />
                    {{ $base['pendientes'] === [] ? 'Correcto' : 'Pendiente' }}
                </span>
            </article>
        </section>

        <div class="sistema-cuerpo">
            <section class="sistema-actividad" aria-labelledby="sistema-actividad-titulo">
                <h2 id="sistema-actividad-titulo">Lo que se ha hecho</h2>
                @if ($acciones->isEmpty())
                    <p class="secundario sistema-vacio">Todavía no se ha hecho nada.</p>
                @else
                    <div class="sistema-tabla" role="table">
                        <div class="sistema-tabla-cabeza" role="row">
                            <span>Momento</span>
                            <span>Acción</span>
                            <span>Persona</span>
                            <span>Resultado</span>
                        </div>
                        @foreach ($acciones as $accion)
                            <div class="sistema-tabla-fila" role="row">
                                <span>{{ $accion->created_at->locale('es')->isoFormat('D MMM, HH:mm') }}</span>
                                <span>{{ $accion->accion === 'copia' ? 'Copia' : 'Actualización' }}</span>
                                <span>{{ $accion->autor->name }}</span>
                                <span>{{ $accion->detalle }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <aside class="sistema-lateral">
                <article class="sistema-panel">
                    <h2>Actualización de la base de datos</h2>
                    <p class="secundario">Antes de aplicar cambios se hace una copia de seguridad.</p>
                    @if ($base['pendientes'] !== [])
                        <p>Hay {{ count($base['pendientes']) }} cambios sin aplicar.</p>
                    @else
                        <p>Todo está al día.</p>
                    @endif
                    <form method="post" action="{{ route('tecnico.actualizar') }}" class="sistema-actualizar" data-actualizar-form>
                        @csrf
                        <input type="hidden" name="confirmo" value="1">
                        <x-boton tipo="secundario" tipoHtml="button" data-actualizar-abre>
                            <x-icono nombre="refresh" />
                            Actualizar
                        </x-boton>
                    </form>
                </article>

                <article class="sistema-panel">
                    <h2>Base de datos activa</h2>
                    <dl class="sistema-datos">
                        <div><dt>Motor</dt><dd>{{ strtoupper($base['driver']) }}</dd></div>
                        <div><dt>Tablas</dt><dd>{{ count($base['tablas']) }}</dd></div>
                        <div><dt>Integridad</dt><dd>{{ $base['integridad'] === null ? 'Sin comprobar' : ($integridadCorrecta ? 'Correcta' : 'Con fallos') }}</dd></div>
                        <div><dt>Tamaño</dt><dd>{{ $kb($base['bytes']) }}</dd></div>
                    </dl>
                    <span class="sr">
                        @foreach ($base['tablas'] as $tabla)
                            {{ $tabla['nombre'] }}: {{ $filas($tabla['filas']) }}.
                        @endforeach
                    </span>
                    @if ($base['error'])
                        <p class="franja" role="alert">{{ $base['error'] }}</p>
                    @endif
                </article>
            </aside>
        </div>
    </div>

    <dialog class="sistema-dialogo" data-actualizar-dialogo aria-labelledby="sistema-dialogo-titulo">
        <div class="sistema-dialogo-cabeza">
            <h2 id="sistema-dialogo-titulo">Actualizar la base de datos</h2>
            <button class="sistema-dialogo-cerrar" type="button" data-actualizar-cerrar aria-label="Cerrar">
                <x-icono nombre="x" />
            </button>
        </div>
        <p>Antes de aplicar los cambios se realiza una copia. Esta acción de muestra no modifica la base de datos real.</p>
        <label class="sistema-confirmacion">
            <input type="checkbox" data-actualizar-confirmo>
            <span>Quiero actualizar. Antes se hace una copia.</span>
        </label>
        <div class="sistema-dialogo-acciones">
            <button class="boton boton-secundario" type="button" data-actualizar-cancelar>Cancelar</button>
            <button class="boton boton-principal" type="button" data-actualizar-confirmar disabled>Actualizar</button>
        </div>
    </dialog>

    <script>
        (function () {
            var formulario = document.querySelector('[data-actualizar-form]');
            var dialogo = document.querySelector('[data-actualizar-dialogo]');
            var confirmacion = document.querySelector('[data-actualizar-confirmo]');
            var confirmar = document.querySelector('[data-actualizar-confirmar]');

            if (!formulario || !dialogo || !confirmacion || !confirmar) {
                return;
            }

            function cerrarDialogo() {
                confirmacion.checked = false;
                confirmar.disabled = true;
                dialogo.close();
            }

            document.querySelector('[data-actualizar-abre]').addEventListener('click', function () {
                confirmacion.checked = false;
                confirmar.disabled = true;
                dialogo.showModal();
            });
            confirmacion.addEventListener('change', function () {
                confirmar.disabled = !confirmacion.checked;
            });
            document.querySelector('[data-actualizar-cerrar]').addEventListener('click', function () {
                cerrarDialogo();
            });
            document.querySelector('[data-actualizar-cancelar]').addEventListener('click', function () {
                cerrarDialogo();
            });
            confirmar.addEventListener('click', function () {
                formulario.submit();
            });
            dialogo.addEventListener('click', function (evento) {
                if (evento.target === dialogo) {
                    cerrarDialogo();
                }
            });
        })();

        (function () {
            var formulario = document.querySelector('[data-exportar-bbdd]');
            if (!formulario || typeof window.showSaveFilePicker !== 'function') {
                return;
            }

            formulario.addEventListener('submit', async function (evento) {
                evento.preventDefault();

                try {
                    var archivo = await window.showSaveFilePicker({
                        suggestedName: 'jornada-copia.sqlite',
                        types: [{
                            description: 'Copia de base de datos SQLite',
                            accept: { 'application/x-sqlite3': ['.sqlite'] },
                        }],
                    });
                    var respuesta = await fetch(formulario.action, {
                        method: 'POST',
                        body: new FormData(formulario),
                        headers: { Accept: 'application/x-sqlite3' },
                    });
                    var tipo = respuesta.headers.get('content-type') || '';

                    if (!respuesta.ok || !tipo.includes('application/x-sqlite3')) {
                        window.location.href = respuesta.url;
                        return;
                    }

                    var contenido = await respuesta.blob();
                    var escritor = await archivo.createWritable();
                    await escritor.write(contenido);
                    await escritor.close();
                    window.location.reload();
                } catch (error) {
                    if (error && error.name === 'AbortError') {
                        return;
                    }

                    formulario.submit();
                }
            });
        })();
    </script>
@endsection
