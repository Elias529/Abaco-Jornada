# Reglas de jornada

Zona horaria: `Europe/Madrid`. Las horas de fichar, pausar, volver y salir son las del servidor en el momento de la petición. La entrada usa la hora del servidor si no se envía otra.

Cada persona tiene una sede municipal (`centro`, por ejemplo `ES-MD` o `ES-CT`). El festivo se busca en esa sede: los de todo el país y los de esa comunidad. Al dar de alta se elige la sede y el calendario del alta cambia con ella. Ya no se imponen siempre los autonómicos de Madrid.

## Horario

Cada persona tiene un horario con vigencia (`effective_from`). Para un día se usa el de vigencia más reciente que no sea posterior a ese día.

El horario de demostración es de 09:00 a 14:00 y de 15:00 a 18:00, desde el 7 de enero de 2026.

Si se pulsa Empezar jornada 10 minutos o más antes de la hora de inicio, Hoy pregunta «¿Empiezas ya?». Sí guarda la hora real. Todavía no no ficha. No hay correo.

Si se guardan dos horarios con la misma vigencia, la aplicación no tiene una regla explícita de desempate. Queda abierto en [plan.md](plan.md).

Un cambio de horario pone `cuenta = false` en todos los avisos anteriores de esa persona, no solo en los del periodo afectado.

## Pausa

Pausa cierra el tramo de trabajo abierto y abre un tramo de pausa. Volver cierra la pausa y abre otro tramo de trabajo. Los minutos de pausa no entran en Hoy ni en Semana.

La comida de 14:00 a 15:00 es la referencia del horario. No se pausa sola. Si la persona se olvida, el tiempo sigue contando como trabajo hasta que pulse Pausa o corrija la hora.

## Olvidos y cierre

Si queda un tramo abierto de un día anterior, lo primero que se ve es ese día. Hay que indicar la hora de salida. No se puede empezar el día nuevo hasta cerrar el anterior. Un tramo sin salida de un día pasado no usa la hora actual: no se sabe cuándo terminó, así que no se inventa.

Si el equipo sigue encendido pasada la hora de fin de tarde, y la persona no ha dicho que sigue, el tiempo visible se corta en esa hora. Terminar jornada no se ofrece hasta pulsar Sigo. Seguir después de las 18:00 guarda la salida real. No se convierte en hora extra ni pide autorización. El texto actual dice «No es una hora extra»; Pablo pide revisar esa frase porque mostrar un exceso no clasifica el tiempo.

La persona puede corregir una hora suya durante 7 días. Después lo hace la jefa. La hora anterior, la nueva, quién y cuándo quedan en `correcciones`. Esta corrección no vuelve a leer el tramo dentro de la operación ni impide que se cruce con otro tramo. Es un defecto abierto.

## Festivos

Si el día es festivo en la sede de la persona, antes de empezar se pregunta si hay trabajo real. Si la respuesta es no, no se ficha. Si es sí, se puede empezar y el día sigue siendo festivo en el calendario.

Si no hay ningún festivo cargado para el año en curso, no se inventan. Quien mantiene el calendario ve el aviso. Los días ya trabajados se quedan como se registraron. Hoy «año completo» significa que existe al menos un festivo de ese año. Pablo pide revisar si esa definición basta.

## Avisos

Se cuenta un aviso por persona, tipo y día. Varios mensajes del mismo cierre son un solo aviso.

No hay umbral ni ventana de tiempo. Cada aviso con `cuenta` verdadera y sin respuesta se cuenta desde el primero. Dentro del año en curso no caduca: deja de contar solo cuando la persona lo explica. En Hoy y en el registro salen por antigüedad, el más antiguo primero, y solo los de este año. El aviso no cambia el registro por sí solo. Un día sin cerrar nunca es un registro correcto, haya los avisos que haya. El histórico se conserva cuatro años; un aviso de un año anterior no sale en esa lista, pero un tramo que siga abierto sigue impidiendo fichar.

La jefa se entera por la antigüedad y no por el número: en Estado se marca a quien tiene avisos sin explicar de hace más de 7 días (`DIAS_HASTA_AVISAR_A_LA_JEFA`), con un resumen arriba y la misma señal en la ficha de la persona. Es solo consulta y no crea avisos. Los 7 días están pendientes de confirmar con el cliente y con Pablo.

Un fallo común de una fecha marca los avisos de ese día como exclusión común y no cuentan para cada persona.

Explicar un aviso escribe `responded_at`. Con eso deja de contar. Pablo pide separar explicación, corrección de horas y resolución: explicar no debe completar una salida ni borrar un olvido confirmado. Eso no está separado todavía.

Si pasan 20 minutos desde la hora de inicio y no hay fichaje, un comando puede avisar por correo una sola vez. Si el envío falla, se reintenta en la siguiente pasada.

## Conexión

El fichaje necesita conexión y la confirmación del servidor. Si el navegador está sin red, el envío no sale y no se guarda nada en el ordenador. El texto es «No guardado: sin conexión».

Al recuperar la conexión, la persona puede comunicar la incidencia. No hace falta escribir horas: el texto queda en Incidencias para la jefa y, además, llega al administrador técnico como un ticket («Incidencia de conexión») para que pueda contestarlo en Ayuda. Si el ticket o el correo fallan, el texto de la jefa se guarda igualmente. No abre un fichaje ni un aviso de olvido.

El motivo tiene entre 10 y 500 caracteres. Una incidencia nueva está Pendiente. Un mensaje o «En curso» la pasa a En curso. Al resolverla, el chat se cierra y sale del listado principal; se consulta en Archivo.

## Papeles

- Trabajadora: ficha y ve su registro.
- Jefa (papel `jefa`): además gestiona el equipo, los horarios, las ausencias, la baja y el calendario. También hace las funciones de dirección: estado del equipo, registro con resumen mensual, copia CSV y lista de consultas. Cada consulta de un registro ajeno exige motivo y queda escrita.
- Administrador técnico: ficha como cualquier persona del equipo. Además gestiona las actualizaciones y comprueba la base de datos activa (estado, integridad, copias). No ve los registros de otras personas. Cada copia o actualización queda escrita en `acciones_tecnicas`.
