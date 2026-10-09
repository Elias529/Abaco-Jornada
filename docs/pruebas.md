# Pruebas

Las pruebas automáticas viven en `tests/Feature` y `tests/Unit`. Se lanzan con `php artisan test`. Usan una base en memoria y no tocan la base de desarrollo.

La revisión de Pablo del 9 de octubre de 2026 vio 18 pruebas y 92 aserciones. Desde entonces se añadieron la vista de dirección, el aviso por correo y los duplicados de ausencia y festivo. El resultado de la última ejecución en este repositorio se anota al final.

## Casos cubiertos

| Prueba | Qué demuestra |
| --- | --- |
| `test_entrada_pausa_salida_y_no_duplica` | Entrada, pausa, vuelta y salida. Una segunda entrada no abre otra jornada |
| `test_cierre_olvidado_permite_empezar_el_dia_nuevo` | El día anterior sin cerrar se completa y después se puede empezar |
| `test_una_persona_no_ve_el_registro_de_otra` | El registro ajeno no se abre sin permiso |
| `test_la_baja_conserva_la_jornada` | La baja cierra el acceso y deja la jornada |
| `test_quien_esta_en_otra_comunidad_sigue_el_calendario_de_madrid` | Otra ficha municipal ve el festivo del centro de Madrid |
| `test_la_reunion_conserva_la_hora_prevista` | La reunión guarda la hora de inicio del horario |
| `test_el_equipo_encendido_no_alarga_la_jornada` | Pasada la hora de salida, el equipo encendido no suma de más en el panel |
| `test_seguir_guarda_la_salida_real_sin_pedir_autorizacion` | Salir más tarde guarda esa hora, sin autorización |
| `test_el_festivo_del_centro_pregunta_si_hay_trabajo` | El festivo pregunta si hay trabajo real |
| `test_el_trabajo_fuera_del_equipo_usa_las_horas_indicadas` | El tramo fuera del equipo usa las horas indicadas |
| `test_por_debajo_de_ocho_avisos_el_registro_no_cambia` | Por debajo de 8 avisos el registro no cambia de estado |
| `test_el_fallo_comun_y_el_horario_no_cuentan` | Un fallo común o un cambio de horario deja el aviso fuera de la cuenta |
| `test_la_consulta_pide_motivo_y_queda_escrita` | Ver el registro de otra persona exige motivo y queda anotado |
| `test_la_incidencia_de_conexion_declara_horas_sin_fichar_en_directo` | Sin convertir la caída de red en un fichaje en directo ni en un olvido. Guarda motivo, autora y hora de recepción |
| `JefeTest` | La dirección consulta, no ficha y no cambia horas, horarios ni accesos |
| `AvisoPorCorreoTest` | El correo de olvido de inicio sale una vez, con las exclusiones previstas |
| `DuplicadosTest` | Una ausencia o un festivo repetido no se duplica |

## Defectos abiertos

La revisión del 9 de octubre de 2026 los reprodujo. Todavía no tienen prueba que los impida.

| Defecto | Qué ocurre | Estado |
| --- | --- | --- |
| Cola sin conexión | El formulario se guardaba en el navegador y se reenviaba al volver | Cerrado. Ya no hay cola. La declaración queda en `test_la_incidencia_de_conexion_declara_horas_sin_fichar_en_directo` |
| Tramos solapados | Una corrección no impide cruzar dos tramos. Una reunión puede repetir tiempo ya anotado | Abierto |
| Cifra distinta en el panel y en el registro | Con la jornada abierta pasada la hora de salida, Hoy puede cortar el tiempo y el registro o el CSV seguir sumando | Abierto |
| Dos correcciones seguidas | No se relee el tramo al guardar. El inicio puede quedar después del fin | Abierto |
| Borrado de demostración | Ana y Marta pueden borrar el fichaje de hoy sin mirar el entorno | Abierto. Hay una prueba que lo permite a propósito |
| Cambio de horario | Excluye todos los avisos de la persona, no solo los de las fechas afectadas | Abierto |
| Histórico | La pantalla de registro muestra como máximo 60 jornadas | Abierto |

## Aún sin probar en un entorno real

Desconexión de verdad, cambio de cuenta en el mismo navegador, sesión caducada, restaurar `database/database.sqlite` y dos ediciones a la vez. Las pruebas actuales no cubren ese recorrido.

## Última ejecución

El 9 de octubre de 2026, `php artisan test` pasó 42 pruebas y 228 aserciones. La revisión de Pablo de esa misma fecha se hizo sobre una versión anterior, con 18 pruebas y 92 aserciones. La cola sin conexión ya tiene prueba. Los demás defectos de la tabla anterior siguen abiertos.
