@if ($puedeFuera && ! in_array(data_get($banner, 'accion'), ['completar', 'cierre'], true))
    <details class="boton-desplegable">
        <summary class="boton boton-principal">Trabajo fuera del equipo</summary>
        <form method="post" action="{{ route('jornada.fuera') }}" class="formulario">
            @csrf
            <p class="secundario">Elige la hora de inicio y de fin, de 00 a 23.</p>
            <div class="horas-par">
                <div>
                    <label for="fuera-inicio">Inicio</label>
                    <x-hora id="fuera-inicio" name="inicio" :max="now()->format('H:i')" />
                </div>
                <div>
                    <label for="fuera-fin">Fin</label>
                    <x-hora id="fuera-fin" name="fin" :max="now()->format('H:i')" />
                </div>
            </div>
            <button class="boton boton-secundario" type="submit">Anotar fuera del equipo</button>
        </form>
    </details>
@endif
