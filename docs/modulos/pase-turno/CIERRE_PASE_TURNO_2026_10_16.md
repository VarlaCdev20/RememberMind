# Pase de turno — cierre funcional del 16–17/10/2026

## Alcance y fuentes

Solicitud del propietario: analizar y terminar el módulo de pases de turno. Se contrastaron el contrato del módulo, jornadas/asignaciones, autorización vigente y el baseline congelado. Se conserva la excepción de lectura histórica: emisor, receptor o asignación del residente a la jornada saliente/entrante exacta. No se amplía el acceso general de Enfermería a residentes de otros profesionales.

No se incorporan nuevas reglas clínicas, responsabilidades, permisos, tablas, columnas ni catálogos. Los estados observados BORRADOR/ENTREGADO/RECIBIDO/ANULADO y las entradas existentes GENERADO/EMITIDO/PENDIENTE se conservan; DEC-OPEN-002 continúa abierta.

## Implementado

- Resolución de la jornada propia por ventana real, incluida una noche iniciada el día anterior. No se elige una jornada global como respaldo.
- Relevo por inicio cronológico y área vinculada. Las asignaciones de residentes desempatan jornadas con el mismo inicio; no desplazan una jornada inmediata sin receptor por otra posterior.
- Si falta planificar el relevo, se conserva la lista de residentes propios, se explica la causa y permanece disponible el historial y la recepción autorizada. No se crean jornadas ni receptores artificiales.
- Borrador, entrega, emisión HTTP y generación existente utilizan la misma escritura transaccional. El bloqueo del residente precede al primer insert; se rechazan duplicados entre entradas distintas.
- Un profesional no puede sobrescribir ni entregar el borrador de otro tras una reasignación. Las mutaciones revalidan cuenta, personal, permiso y asignación/contexto en el backend.
- Bandeja de recepción coherente con las entradas existentes y con la asignación entrante exacta. La recepción persiste al profesional que realmente confirma, y guarda la designación anterior en Activitylog.
- Anulación lógica desde el detalle, con confirmación y motivo opcional según el contrato existente. Conserva contenido, autoría, receptor, fecha y observación de recepción. El detalle mantiene visible esa procedencia incluso después de anular.
- Hechos de medicación limitados a la ventana del turno. La agenda distingue el día consultado del reloj real, también al cruzar medianoche. La dosis administrada muestra su unidad de la prescripción.
- Últimos registros guardados separados de los hechos de la jornada, con sus fechas. Las lecturas de contexto no presentan registros anulados/rectificados como el último dato vigente.
- Texto libre de estado general, sin clasificación clínica inventada. Pendientes comunicados no se convierten automáticamente en acciones resueltas.
- Ventanas, campos, botones, secciones, estados y paginación compartidos. Captura antes del contexto en móvil, aviso de cambios sin guardar, errores junto al campo y retorno del foco al disparador disponible.
- Historial filtrable y paginado mediante Livewire; limpiar filtros restablece cadenas vacías. La vista usa tarjetas adaptables y no exige una tabla ancha en móvil.
- Códigos internos omitidos de texto visible; los pendientes históricos serializados en TEXT se muestran como texto legible, sin cambiar su almacenamiento.

## Integración y seguridad

Ruta existente: `/admin/enfermeria/pase-turno`, con `pases_turno.ver`. La entrada HTTP delega en el mismo servicio de aplicación. Se conserva la arquitectura híbrida de Cuidados, la autorización contextual existente y Activitylog dentro de la transacción. No se escriben ni se cierran fuentes clínicas al entregar/recibir.

La revisión independiente, de solo lectura, detectó dos P2 (salto de relevo y recepción oculta tras anulación). Ambos se corrigieron y ganaron regresiones; la revisión final no identificó otros hallazgos accionables en el alcance examinado.

## Pruebas ejecutadas

| Evidencia | Resultado |
|---|---|
| SQLite: `php artisan test --filter='PaseTurnoReconstruidoTest\|PaseTurnoIntegridadTest\|AgendaMedicacionTest' --compact` | **43 PASS / 202 aserciones** |
| PostgreSQL desechable: `scripts/probar-postgres.ps1`, mismas suites + `PaseTurnoConcurrenciaPostgresTest` | **45 PASS / 221 aserciones** |
| Integraciones cercanas: Pase, Integridad, TurnoCompletoEnfermero, SeguridadCriticaEnfermeria, MiTurnoFueraDeJornada | **68 PASS / 363 aserciones**, antes de los últimos ajustes de presentación/agenda y nuevas regresiones |
| Frontend: `node --test tests/Frontend/modal-layering.test.js tests/Frontend/clinical-form-feedback.test.js` | **15 PASS** |
| `npm run build` | **PASS**; advertencia existente de chunk JS mayor de 500 kB |
| Pint acotado y `git diff --check` del alcance | **PASS** |

Las carreras PostgreSQL comprueban dos primeros escritores por entradas diferentes y dos recepciones simultáneas: una sola fila/confirmación y su auditoría. Se prueba también rollback completo ante fallo de auditoría, autoría del borrador, reasignación legítima, recepción doble, HTTP repetido, nocturnidad, relevo inmediato sin receptor, permisos y paginación real.

Un primer lote PostgreSQL descubrió contaminación entre fixtures: la suite de concurrencia usa commits reales y debía restablecer `RefreshDatabaseState` para la siguiente suite. Se corrigió el aislamiento; el lote final completo pasó. No se debilitó ninguna regla de producción para obtener verde.

Evidencia local ignorada por Git: `storage/app/qa/pase-postgres.junit.xml`, `pase-postgres.log` y `pase-sqlite.junit.xml`. El runner utilizó su propio clúster, usuario, base y puerto; lo detuvo y limpió. No se ejecutaron resets contra la base operativa.

## Verificación de interfaz

Interacción real mediante navegador conectado contra una copia SQLite sintética aislada en loopback. Se completó guardar/reabrir borrador → confirmar entrega → cambiar a receptor → revisar/confirmar recepción → consultar historial/detalle. Se comprobó el aviso de descarte sin perder captura y el cierre con Escape. El foco se mantiene en el diálogo y vuelve al botón correspondiente al cerrar.

Se midieron **1440, 1280, 1024, 768 y 390 CSS px reales**, compensando la escala interna del navegador. Historial, captura y detalle sin desbordamiento horizontal; captura primero, contexto plegado en móvil; acciones principales y cierre con objetivos de al menos 44 × 44 px. Se corrigió una interferencia del puente de formularios heredados usando el ancho `xl` del modal compartido.

**Límites:** la captura de pantalla del navegador no estuvo disponible. Las comprobaciones se apoyan en accesibilidad, DOM, estilos calculados, dimensiones e interacción real; no equivalen a una aceptación visual por píxeles. El navegador informa movimiento reducido desactivado y no expone simulación en este entorno; se conservan los estilos compartidos correspondientes, sin afirmar ejecución real de ese modo. No se acredita certificación clínica ni legal.

## Documentación reservada y operación

- **Discrepancia preexistente del baseline:** la migración `2026_09_23_161500_add_recepcion_fields_to_pases_turno_table.php` y el modelo usan `fecha_hora_recepcion` y `observacion_recepcion`; el inventario congelado consultado no los detalla. No se encontró una decisión explícita que concilie ambos documentos. Se mantuvieron los campos existentes, sin migración ni modificación del baseline. Su conciliación formal requiere la decisión del propietario conforme a la gobernanza de BDD.
- En la sesión operativa observada el 16/10 faltaba planificar el siguiente relevo. El módulo ahora muestra esa causa; la planificación real corresponde a coordinación y no se reemplazó con datos de prueba.
- Se retiraron de PaseTurno los accesores V1 sin consumidores y la fecha de recepción ficticia basada en `now()`. La agenda compartida usa la relación canónica `residente`; nombres de API/DTO históricos compartidos se conservaron para no ampliar el cambio a otros módulos.
- **Commit:** no creado. El checkout contiene cientos de cambios anteriores, incluidos hunks previos en archivos del módulo y dependencias compartidas. Se preservó ese trabajo y no se mezcló en un commit automático. Sin push.
- **Limpieza QA:** el servidor HTTP sintético se detuvo. La revisión automática rechazó retirar su directorio temporal y los scripts auxiliares ignorados por Git; permanecen aislados, sin servidor activo y fuera del flujo operativo. El rechazo no afectó las correcciones ni las pruebas del módulo.

## Estado

Flujo implementado y verificado funcionalmente en datos sintéticos y PostgreSQL aislado. Permanecen explícitos los límites de captura visual/movimiento reducido, la conciliación documental reservada del baseline y la necesidad de planificación institucional real para habilitar una entrega.
