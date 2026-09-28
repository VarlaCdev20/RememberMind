# Plan de refactorización total (no ejecutado)

La entrega actual termina en diseño. Este plan requiere autorización posterior de implementación; no es autorización de commit, push, despliegue, nueva BD ni instalación. Objetivo final: los **14 módulos funcionales**, incluido cierre de pantallas incompletas actuales con el alcance descrito. Ninguna fase intermedia se presenta como producto terminado.

## Dependencias y secuencia

| Fase | Alcance y dependencias | Entregable comprobable | Criterio de salida |
|---|---|---|---|
| 0. Cerrar decisiones | D01–D15 con responsables; inventario de datos reales, acceso y conservación | ERD/catálogos/estados/roles firmados, dataset anonimizado autorizado y matriz de aceptación | Cero decisiones que bloqueen esquema/seguridad; presupuesto y ventana acordados |
| 1. Caracterización | Conservar cambios previos de usuarios; medir rutas, permisos, views, SQL y dependencias | Tests de comportamiento actual útil; lista de defectos que no se preservarán | Cada capacidad de 01 tiene dueño, prueba y destino; código actual reproducible |
| 2. Fundaciones de datos/seguridad | Persona/cuenta, claves, contratos técnicos, Policies, auditoría, almacenamiento privado base | Migraciones nuevas en entorno aislado y tablas puente/procedencia; catálogo permisos | Sin autoescalada; login/2FA/reset/sesiones y mismos nombres de rutas de usuarios; ninguna BD real usada por tests |
| 3. Institución y documentos | Catálogos reales, perfiles/acreditaciones, empleo, horarios/plazas; tipos/versiones/eventos de documentos | Formularios Blade/Livewire y planilla validada; checklist/versionado privado | Cobertura/descanso/solapes probados; persona sin cuenta; documento reemplazado conserva evidencia |
| 4. Residentes y admisiones | Casos/contactos/consentimientos, estancias/habitaciones/camas y decisiones | Flujo preadmisión→valoración→decisión→ingreso→traslado→alta→reingreso | Reintento no duplica estancia, dos solicitudes concurrentes no ocupan la misma cama; alta libera recursos |
| 5. Clínica y VGI | Atención/nota/diagnóstico/alergia/signos; instrumentos/versiones/valoraciones | Ficha integrada y formularios únicos, importación de log clínico legado | Anulación/corrección trazables, score reproducible, identidad/caso/estancia coherentes; cada instrumento acordado completo |
| 6. Medicación | Prescripción/líneas/pauta, dosis y conciliación; depende de Clínica/acreditaciones | Agenda por turno y registro de administración/omisión | Permisos prescribir/administrar separados; duplicado/id ajeno/orden suspendida rechazados; corrección no redosifica |
| 7. Cuidados y seguridad | Equipo asignado, plan/versiones/tareas/ejecuciones, seguimiento/pase; incidentes/alertas/acciones | Turno completo con continuidad asistencial | Plan histórico no cambia por nueva versión; recepción del pase comprobada; alerta cierra con evidencia |
| 8. Social y actividades | Red/portal, visitas/ficha social reales; eventos/participación; voluntariado completo | Portales propios y gestión administrativa sin alias vacíos | Familiar ajeno/consentimiento revocado rechazados; voluntario ve solo lo propio; asistencias independientes |
| 9. Sistema experto | Reglas/versiones/ejecuciones/revisión; requiere validación clínica y datos firmados | Motor determinista explicable y UI de revisión | Misma versión+input produce mismo resultado; faltantes explícitos; no diagnóstico/prescripción autónoma |
| 10. Reportes y UX completa | Queries autorizadas y exportación en todos los módulos; componentes compartidos | PDF/Excel/CSV y dashboards reales; sin rutas placeholder activas | Totales iguales con mismo corte/scope; sin fuga por export/caché; pruebas de todos los enlaces/pantallas |
| 11. Ensayos integrales | Snapshot aislado, ETL idempotente, archivos, roles, concilios y rollback | Manifiesto por tabla/archivo, diferencias explicadas, pruebas extremo a extremo | Cero FK huérfanas/concurrencia crítica/fugas; cuarentena crítica resuelta; tiempos medidos |
| 12. Corte autorizado | Ventana, freeze breve de escrituras, backup verificado, colas drenadas, importación final | Verificación y apertura gradual del producto | Dirección/operación clínica autorizan; restauración ensayada, monitoreo activo y datos consistentes |
| 13. Retiro de compatibilidad | Período de observación acordado, sin consumidores antiguos | Retiro de alias/código/tablas antiguas después de conservación autorizada | Cero usos legacy y aceptación de todos los módulos; sin pérdida de historia |

Fases pueden organizar trabajo interno, pero la dependencia de datos permanece. Documentos privados básicos se habilitan antes de importar clínica; validación de consentimiento antes de portal. Reportes se prueban por módulo desde su fase, aunque su consolidación sea fase10. No acumular la seguridad para el final.

## Pruebas de aceptación por módulo

| Módulo | Casos mínimos de regresión e integración |
|---|---|
| Identidad | Invitado, permiso único, cuenta desactivada con sesión, login correo, 2FA/reset, rol no delegable, técnico sin clínica |
| Institución | Persona multiperfil, especialidad incompatible, licencia expirada, horario nocturno y solapes, rotación/descanso/reemplazo, publicar dos veces |
| Residentes | Persona ya registrada, código visible conservado, archivo vs alta, consentimiento otorgado/revocado sin borrar evidencia |
| Admisiones | Documentación pendiente/plazo48h, enfermero disponible, doble aprobación, transición inválida, dos transacciones por cama, traslado rollback, reingreso |
| Clínica | Nota firmada/corregida, alergia desconocida, medición fuera de unidad/rango, ID de otra persona, autor preservado, extracción legada de log |
| Medicación | Orden firmada/suspendida, autorización separada, omisión obligatoria, request repetido, concurrencia, corrección misma dosis, pauta ambigua bloqueada |
| VGI | Cada instrumento publicado: respuestas válidas/incompletas/límites, score servidor, versión nueva sin alterar resultado viejo, preingreso sin residente |
| Cuidados | Plan/versión, tarea recurrente y omisión, asignación al turno correcto, transferencia, pase emitido/recibido, cambio concurrente |
| Social | Múltiples familiares con parentesco contextual, responsable/emergencia, portal propio, revocación, visita llegada/salida, seguimiento social corregido |
| Actividades | Evento colectivo y registros legados individuales, cupo, reprogramación/asistencia, disponibilidad voluntario, excepción autorizada, ausencia justificada sin horas |
| Seguridad asistencial | Incidente vs alerta, deduplicación, atender/cerrar/reabrir, actor/motivo, historial preservado |
| Documentos | MIME/tamaño autorizados, archivo privado, titular ajeno, reemplazo, hash, validación/entrega/firma separados, plazo/vencimiento, fallo storage/job |
| Experto | Reproducibilidad, reglas retiradas, inputs faltantes, explicación, revisión aceptar/rechazar, ninguna escritura clínica autónoma |
| Reportes | Mismo filtro/scope/corte en pantalla/PDF/Excel/CSV, campos sensibles excluidos, descarga reautorizada, datos anulados claramente tratados |

## Estrategia de pruebas de BD

Suite rápida aislada SQLite para casos compatibles y suite Integration PostgreSQL para FKs, indices parciales, triggers, SQL nativo, bloqueo concurrente y migración. Semillas de pruebas ficticias, sin ejecutar DatabaseSeeder de demo nominal en entorno real. Guardia de conexión antes de cualquier reset de BD de pruebas. No ejecutar migrate:fresh sobre entorno actual. Construir bases efímeras y destruirlas solo con autorización y verificación del destino durante implementación futura.

En esta tarea no se ejecuta php artisan test ni route:list como sustituto del diseño. El resultado 37 passed/8 skipped/0 failed corresponde al trabajo previo de rutas, no certifica este plan ni los módulos clínicos. No confundir skips de funciones Jetstream deshabilitadas con pruebas de funcionalidad clínica.

## Migración incremental y rollback

Migraciones aditivas y adaptadores temporales controlados: una sola vía de escritura por concepto en cada fase. No doble escritura improvisada en Livewire y controller. Si una fase todavía lee legado, indicar explícitamente cuál esquema es autoritativo; no habilitar UI nueva parcialmente consistente.

Durante ensayo comparar reportes y conteos en solo lectura. Antes de corte: backup restaurado de prueba, manifiesto de archivos, freeze de cambios estructurales y colas, snapshot final, importación y conciliación. Antes de apertura puede revertirse código y restaurar backup. Después de nuevas escrituras, no basta volver al código antiguo: reconciliar escrituras nuevas con plan probado o adoptar roll-forward. Esto debe aprobarse en D14 con umbrales concretos de parada.

Criterios de no apertura: discrepancia de paciente/dosis, camas solapadas, pérdida de documento/historial, permiso excesivo, clínica pendiente de validación, ETL no idempotente o recuperación no ensayada. No inventar estimación en semanas sin tamaño de datos, equipo y validación institucional. Medir rendimiento con volúmenes representativos antes de fijar SLOs (D15).

## Definición de terminado global

Los 14 módulos satisfacen 05/06; toda funcionalidad existente útil tiene prueba; todas las tablas de 04 están conciliadas; navegación no anuncia páginas vacías; formularios comparten reglas; PostgreSQL consistente y privado; roles efectivos aprobados; reportes comparados; usuarios operativos validan escenarios; soporte y restauración documentados. Commit/push/despliegue siguen fuera de esta tarea y de esta entrega documental.

