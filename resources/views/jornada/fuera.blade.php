@if ($puedeFuera && ! in_array(data_get($banner, 'accion'), ['completar', 'cierre'], true))
    <button class="accion-secundaria" type="button" data-dialogo-abre="sin-portatil">Trabajé sin el portátil</button>
@endif
