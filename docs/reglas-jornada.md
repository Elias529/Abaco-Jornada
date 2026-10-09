# Reglas de jornada

Zona horaria: `Europe/Madrid`. Las horas de fichar, pausar, volver y salir son las del servidor en el momento de la petición. La entrada usa la hora del servidor si no se envía otra.

El centro de trabajo es Madrid (`JornadaService::CENTRO`). El festivo se busca solo en ese municipio. El municipio de la ficha de la persona no cambia el calendario.

## Horario

Cada persona tiene un horario con vigencia (`effective_from`). Para un día se usa el de vigencia más reciente que no sea posterior a ese día.

El horario de demostración es de 09:00 a 14:00 y de 15:00 a 18:00, desde el 7 de enero de 2026.

Si se guardan dos horarios con la misma vigencia, la aplicación no tiene una regla explícita de desempate. Queda abierto en [plan.md](plan.md).

Un cambio de horario pone `cuenta = false` en todos los avisos anteriores de esa persona, no solo en los del periodo afectado.

## Pausa

Pausa cierra el tramo de trabajo abierto y abre un tramo de pausa. Volver cierra la pausa y abre otro tramo de trabajo. Los minutos de pausa no entran en Hoy ni en Semana.

La comida de 14:00 a 15:00 es la referencia del horario. No se pausa sola. Si la persona se olvida, el tiempo sigue contando como trabajo hasta que pulse Pausa o corrija la hora.

## Olvidos y cierre

Si queda un tramo abierto de un día anterior, lo primero que se ve es ese día. Hay que indicar la hora de salida. Después se puede empezar el día nuevo.

Si el equipo sigue encendido pasada la hora de fin de tarde, y la persona no ha dicho que sigue, el tiempo visible se corta en esa hora. Terminar jornada no se ofrece hasta pulsar Sigo. Seguir después de las 18:00 guarda la salida real. No se convierte en hora extra ni pide autorización. El texto actual dice «No es una hora extra»; Pablo pide revisar esa frase porque mostrar un exceso no clasifica el tiempo.

La persona puede corregir una hora suya durante 7 días. Después lo hace la responsable. La hora anterior, la nueva, quién y cuándo quedan en `correcciones`. Esta corrección no vuelve a leer el tramo dentro de la operación ni impide que se cruce con otro tramo. Es un defecto abierto.

## Festivos

Si el día es festivo en Madrid, antes de empezar se pregunta si hay trabajo real. Si la respuesta es no, no se ficha. Si es sí, se puede empezar y el día sigue siendo festivo en el calendario.

Si no hay ningún festivo cargado para el año en curso, no se inventan. Quien mantiene el calendario ve el aviso. Los días ya trabajados se quedan como se registraron. Hoy «año completo» significa que existe al menos un festivo de ese año. Pablo pide revisar si esa definición basta.

## Avisos

Se cuenta un aviso por persona, tipo y día. Varios mensajes del mismo cierre son un solo aviso.

El margen es 8 avisos con `cuenta` verdadera, sin respuesta, en los últimos 28 días. Por debajo, el registro no cambia. El texto es «Este mes tienes N cierres sin completar…». Estar por debajo de 8 no convierte un día sin cerrar en un registro correcto.

Un fallo común de una fecha marca los avisos de ese día como exclusión común y no cuentan para cada persona.

Explicar un aviso escribe `responded_at`. Con eso deja de contar. Pablo pide separar explicación, corrección de horas y resolución: explicar no debe completar una salida ni borrar un olvido confirmado. Eso no está separado todavía.

Si pasan 20 minutos desde la hora de inicio y no hay fichaje, un comando puede avisar por correo una sola vez. Si el envío falla, se reintenta en la siguiente pasada.

## Conexión

El fichaje necesita conexión y la confirmación del servidor. Si el navegador está sin red, el envío no sale y no se guarda nada en el ordenador. El texto es «No guardado: sin conexión».

Al recuperar la conexión, la persona puede comunicar la incidencia y declarar el inicio y el fin. El servidor guarda el motivo, la persona que lo declara y la hora de recepción (`anotado_at`). El tramo queda cerrado, con situación `incidencia`. No es un fichaje en directo y no abre un aviso de olvido. Esas horas sí cuentan en el total del día. Después se puede empezar la jornada si todavía no hay un tramo de trabajo normal.

## Papeles

- Trabajadora: ficha y ve su registro.
- Responsable: además gestiona el equipo, los horarios, las ausencias, la baja y el calendario.
- Dirección: consulta estado, horario y registro. Cada consulta de un registro ajeno exige motivo y queda escrita. No ficha, no corrige horas y no cambia horarios ni accesos.
