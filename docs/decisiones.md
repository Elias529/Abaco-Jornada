# Decisiones

Cada fila enlaza la regla o la tarea que la aplica. El estado «vigente» es lo que el código hace hoy. «Pendiente de aplicar» es un acuerdo de Pablo del 9 de octubre de 2026 que el código todavía no cumple.

## 2026-10-09 · Fichaje con conexión

- Decisión: el proyecto es una aplicación web. Cada fichaje requiere conexión y la confirmación del servidor.
- Motivo: la cola del navegador puede guardar el contenido de un formulario, reenviarlo con otra sesión y perder pendientes.
- Alternativa descartada: guardar la acción en el navegador y enviarla sola al recuperar la red.
- Estado: vigente. `public/js/reloj.js` ya no guarda ni reenvía formularios.
- Aplica en: [reglas-jornada.md](reglas-jornada.md). Prueba `test_la_incidencia_de_conexion_declara_horas_sin_fichar_en_directo`.

## 2026-10-09 · Una caída de internet es una incidencia

- Decisión: mostrar «No guardado: sin conexión». Al volver, la persona puede comunicar la incidencia y declarar las horas. Se conservan motivo, autor y momento de recepción. No es un fichaje en directo ni un olvido automático.
- Motivo: la falta de conexión suele ser ajena a la persona, y no debe ocultarse ni contarse como si hubiera fichado a la hora.
- Alternativa descartada: reenviar el formulario tal cual, o abrir un aviso de olvido solo porque no hubo red.
- Estado: vigente. El texto es «No guardado: sin conexión». Al volver la red, «Comunicar incidencia» guarda las horas declaradas, el motivo, la persona y la hora de recepción. No abre un fichaje en directo ni un aviso de olvido.
- Aplica en: [reglas-jornada.md](reglas-jornada.md). Prueba `test_la_incidencia_de_conexion_declara_horas_sin_fichar_en_directo`.

## 2026-10-07 · El calendario es el del centro de Madrid

- Decisión: todas las personas, también quien teletrabaja desde otro municipio, siguen los festivos del centro de Madrid.
- Motivo: el centro de trabajo está en el municipio de Madrid. No se aplica el calendario de la comunidad a cada centro, ni el del municipio de la ficha.
- Alternativa descartada: un calendario por municipio de residencia.
- Estado: vigente en `JornadaService::festivoDe`. Pablo dejó pendiente confirmar el centro y quién valida y actualiza el calendario. Ver [reuniones.md](reuniones.md).
- Aplica en: prueba `test_quien_esta_en_otra_comunidad_sigue_el_calendario_de_madrid`.

## 2026-10-07 · No se borra una hora al corregirla

- Decisión: la corrección añade la hora nueva y conserva la anterior, quién la cambió y cuándo. Un informe ya emitido no se reescribe solo.
- Motivo: el registro tiene que poder contrastarse. La retención de contraste es el artículo 34.9 del Estatuto de los Trabajadores, cuatro años. Ábaco lo confirma.
- Alternativa descartada: sustituir la hora y olvidar la anterior.
- Estado: vigente en `correcciones`. La salida guarda además quién la declaró y cuándo, en `cerrado_por` y `cerrado_at`. Una corrección posterior no borra esa declaración.
- Aplica en: prueba `test_cambiar_una_hora_conserva_la_anterior`.

## 2026-10-07 · La baja no borra la jornada

- Decisión: desactivar a una persona pone `active` en falso y no borra sus jornadas. Las claves foráneas usan `restrictOnDelete`.
- Motivo: el historial no puede desaparecer al cerrar el acceso.
- Alternativa descartada: borrar en cascada los fichajes de esa persona.
- Estado: vigente.
- Aplica en: prueba `test_la_baja_conserva_la_jornada`.

## 2026-10-07 · La dirección no ficha

- Decisión: el papel `jefe` consulta. No entra en las rutas que escriben fichajes, horarios o accesos. Abrir el registro de otra persona exige un motivo y deja constancia.
- Motivo: la alta dirección queda fuera del registro de jornada y la consulta tiene que ser trazable.
- Alternativa descartada: dar a la dirección las mismas acciones que a la jefa.
- Estado: sustituida por la decisión del 2026-10-14.

## 2026-10-14 · Desaparece la dirección y aparece el administrador técnico

- Decisión: el papel `jefe` desaparece. La jefa (papel `jefa`, antes `responsable`; la migración `pasar_el_papel_responsable_a_jefa` renombra las cuentas) hace también sus funciones (estado del equipo, registro con resumen mensual, copia CSV y consultas) y sigue gestionando el equipo. Nace el papel `tecnico`, que ficha como cualquier persona y gestiona actualizaciones y la base de datos activa. La migración `pasar_la_direccion_a_administracion_tecnica` convierte las cuentas `jefe` en `tecnico`.
- Motivo: la jefa ya conoce al equipo y la consulta con motivo sigue siendo trazable. Hace falta alguien que mantenga el sistema sin ver datos de las personas.
- Alternativa descartada: que el técnico fuera también jefe, porque vería los registros.
- Límite del técnico: copia y actualización solo con SQLite en archivo. Antes de actualizar se hace una copia y, si no se puede, no se toca nada. El panel enseña nombres de tabla y número de filas, nunca el contenido.
- Estado: vigente.
- Aplica en: `tests/Feature/JefeTest.php`, `tests/Feature/TecnicoTest.php` y `tests/Feature/SistemaEnArchivoTest.php`.

## 2026-10-15 · «Comunicar incidencia» también llega al técnico

- Decisión: al guardar una incidencia de conexión en Hoy, se abre además un ticket para el administrador técnico con el motivo y las horas declaradas. Él lo ve en Tickets, recibe el correo y puede contestar. La incidencia sigue guardándose en el registro de la persona como hasta ahora.
- Motivo: el botón guardaba la incidencia en el registro pero no avisaba a nadie, y el técnico no podía ayudar.
- Alternativa descartada: sustituir el formulario por un ticket. Perderíamos las horas declaradas en el registro y la constancia de quién las declara y cuándo las recibe el servidor.
- Estado: vigente.
- Aplica en: `tests/Feature/TicketTest.php`.

## 2026-10-15 · Los avisos ya no tienen margen de 8 ni reinicio a los 28 días

- Decisión: se quita el umbral de 8 avisos y la ventana de 28 días. Cada cierre sin completar se avisa desde el primero y no caduca por el tiempo: deja de contar solo cuando la persona lo explica. El histórico no se borra y se conserva cuatro años, como el resto del registro.
- Motivo: con la ventana se podía llegar a 7, esperar a que caducaran y volver a empezar sin que el sistema lo notara. Además, el 8 se podía leer como una tolerancia.
- Alternativa descartada: quitar solo la ventana y dejar el umbral de 8. Quien llegara a 8 se quedaría ahí para siempre y el margen seguiría existiendo.
- Aviso a la jefa: el criterio para que la jefa actúe es la antigüedad y no un número de avisos. En Estado marca a quien tiene cierres sin resolver de hace más de 7 días, con un resumen arriba, y lo repite en la ficha de la persona. Es solo consulta: no crea avisos ni manda correos. Hay que confirmar los 7 días con el cliente y con Pablo.
- Estado: vigente. Sustituye al margen de 8 avisos de `docs/reglas-jornada.md`.
- Aplica en: `tests/Feature/JornadaTest.php` y `tests/Feature/JefeTest.php`.

## 2026-10-14 · Los fallos de la aplicación se cuentan en un ticket

- Decisión: cualquier persona, jefa incluida, abre un ticket desde "Ayuda" en el menú. Es una conversación con el técnico. Los estados son abierto, en curso y resuelto. El técnico recibe un correo al abrirse y quien lo abrió recibe otro al resolverse. Si quien lo abrió escribe en uno resuelto, se reabre.
- Motivo: la persona que tiene el problema no tiene otra vía que avisar de palabra, y el técnico no queda con rastro de lo que pasó.
- Límite: el ticket solo lo ven quien lo abrió y el técnico. Ni la jefa ve los de otras personas. El formulario pide no escribir datos de salud ni de clientes. Un correo que falla se anota en el registro y no impide guardar el ticket.
- Estado: vigente.
- Aplica en: `tests/Feature/TicketTest.php`.

## 2026-10-07 · El exceso no se autoriza en la aplicación

- Decisión: salir a las 18:35 guarda las 18:35. No se pide autorización ni se etiqueta como hora extra.
- Motivo: Ábaco ve el total y decide. La aplicación no convierte el exceso en una categoría laboral.
- Alternativa descartada: un módulo de horas extra con aprobación.
- Estado: vigente en el cálculo. La frase «No es una hora extra» está pendiente de revisión, porque mostrar el exceso no clasifica el tiempo.
- Aplica en: prueba `test_seguir_guarda_la_salida_real_sin_pedir_autorizacion`.
