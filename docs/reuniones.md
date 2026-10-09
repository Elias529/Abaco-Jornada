# Reuniones

Los acuerdos de producto anteriores a esta nota están resumidos en [decisiones.md](decisiones.md). Aquí queda la revisión escrita del 9 de octubre de 2026.

## 2026-10-09 · Revisión técnica

- Tema: conservar lo que funciona, corregir los riesgos y dejar las decisiones por escrito.
- Autor de la revisión: Pablo.
- Responsable de aplicarla: Svetlana.
- Decisiones:
  - El fichaje requiere conexión y confirmación del servidor.
  - Una caída de internet es una incidencia. El texto será «No guardado: sin conexión». Al volver se declaran las horas con motivo, autor y momento. No es un fichaje en directo ni un olvido automático.
- Motivo: la cola sin conexión puede guardar contraseñas de un formulario, reenviar una acción con otra sesión y perder pendientes. Las horas solapadas y las correcciones concurrentes pueden sumar tiempo que no se trabajó.
- Estado: los dos acuerdos de conexión ya están en el código. El resto del orden de trabajo sigue en [plan.md](plan.md).

## Pendiente

| Asunto | Responsable | Estado |
| --- | --- | --- |
| Confirmar el centro de trabajo y quién valida y actualiza el calendario. La aplicación usa el municipio de Madrid para toda la plantilla | Por confirmar con Pablo | Pendiente |
| Comprobar que los márgenes de avisos y las correcciones no permiten ocultar un incumplimiento | Svetlana | Pendiente. Ver las tareas «Revisar» de [plan.md](plan.md) |
| Probar desconexión, cambio de cuenta, sesión caducada, restauración de una copia y dos ediciones a la vez en el entorno elegido | Svetlana | Pendiente |
