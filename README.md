# Ábaco Jornada

Aplicación web para el registro de jornada de Ábaco. La persona ficha la entrada, la pausa y la salida. La hora la confirma el servidor, en la zona `Europe/Madrid`.

La dirección consulta los registros y no ficha. La responsable gestiona el equipo, los horarios y el calendario del centro.

## Arrancar

Hace falta PHP 8.3 y Composer. Los pasos de instalación, copia y restauración están en [docs/instalacion-operacion.md](docs/instalacion-operacion.md).

```bash
php artisan serve
php artisan test
```

La aplicación queda en `http://127.0.0.1:8000/login`. Las cuentas de demostración salen en esa pantalla, en «Cuentas de prueba».

## Documentación

| Documento | Para qué sirve |
| --- | --- |
| [docs/alcance.md](docs/alcance.md) | Qué incluye la entrega y qué queda fuera |
| [docs/plan.md](docs/plan.md) | Tareas abiertas, prioridad y cuándo se dan por hechas |
| [docs/reglas-jornada.md](docs/reglas-jornada.md) | Horarios, pausas, olvidos, festivos y conexión |
| [docs/decisiones.md](docs/decisiones.md) | Qué se decidió, por qué y qué se descartó |
| [docs/reuniones.md](docs/reuniones.md) | Acuerdos, responsables y asuntos pendientes |
| [docs/instalacion-operacion.md](docs/instalacion-operacion.md) | Instalar, actualizar, copiar y restaurar |
| [docs/pruebas.md](docs/pruebas.md) | Casos probados y defectos aún abiertos |

La revisión de Pablo del 9 de octubre de 2026 pide corregir los puntos críticos de [docs/plan.md](docs/plan.md) antes de usar registros reales.
