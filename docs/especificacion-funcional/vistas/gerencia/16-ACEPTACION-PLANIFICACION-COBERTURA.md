---
title: "Gerencia: aceptación de Planificación y cobertura"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: ACCEPTANCE_SPECIFICATION_WITH_STATIC_TEST_LOCALIZATION
runtime_verified: false
---

# Aceptación de Planificación y cobertura

## 1. Contratos y pruebas localizadas

Fuentes: [Planificación](14-PLANIFICACION-DEL-EQUIPO.md), [Cobertura](15-COBERTURA-DEL-EQUIPO.md) y [propuesta visual](17-DISENO-PLANIFICACION-COBERTURA.md). **Los 28 escenarios PC01–PC28 están ESPECIFICADOS, NO EJECUTADOS EN ESTA TAREA.** Las imágenes no son capturas de la aplicación ni prueba de aceptación.

Pruebas existentes localizadas, no reejecutadas:

- **PT1:** [PersonalInstitucionalHorariosTest](../../../../tests/Feature/PersonalInstitucionalHorariosTest.php) y [PersonalInstitucionalAsignacionesTest](../../../../tests/Feature/PersonalInstitucionalAsignacionesTest.php): horarios y asignaciones; ampliar regresiones H02/H03, no conservar errores para pasar tests.
- **PT2:** [AsignacionesIntegrityTest](../../../../tests/Feature/AsignacionesIntegrityTest.php) y [AsignacionesJornadaPlanificadaTest](../../../../tests/Feature/AsignacionesJornadaPlanificadaTest.php): relaciones, planificación de jornada y límites del proceso; no equivalen a recurrencia maestra íntegra.
- **PT3:** [MiTurnoFueraDeJornadaTest](../../../../tests/Feature/MiTurnoFueraDeJornadaTest.php): consulta temporal de Enfermería, distinta de autorización gerencial para modificar.
- **PT4:** [RolesVistasPermisosMatrixTest](../../../../tests/Feature/RolesVistasPermisosMatrixTest.php): entradas por rol; ampliar revocación y payloads.

## 2. Matriz de aceptación

| Caso | Requisito/entrada | Resultado verificable requerido | Prueba base / trabajo pendiente |
|---|---|---|---|
| PC01 | 21A, calendario | Semana inicial, mes/lista alternativos y navegación de fechas sin mutación | Nuevo frontend/integración |
| PC02 | PL01/PL02, filtros | Área/profesión/persona filtran registros reales autorizados; limpiar conserva universo correcto | PT1/PT4, ampliar |
| PC03 | PL03–PL05 | Cada bloque abre persona/área/jornada/turno correctos; sin datos de otros targets | PT1/PT2, ampliar lectura |
| PC04 | Preparar cambio | Captura sin guardar no crea filas, no abre jornadas ni emite avisos | Nuevo escenario de efectos |
| PC05 | Revisar/confirmar | Confirmación conserva cambios concretos, actor y destinatarios pertinentes; no confunde guardar con publicar una entidad inexistente | Nuevo escenario de integración |
| PC06 | Permisos | Actor nulo/inactivo, permiso revocado, target cambiado y modo Ver como rol no guardan | PT4; ampliar Action/HTTP/Livewire |
| PC07 | Relaciones | Cuenta/personal/área/turno/jornada no aptos se rechazan según contrato; no tomar primera FK disponible | PT1/PT2, ampliar |
| PC08 | Vigencia | Fecha final capturada y ocurrencias coinciden con SQL; no anunciar recurrencia indefinida o ignorar la fecha final | Nuevo caso tras resolver representación |
| PC09 | Medianoche | Inicio/fin tienen fechas correctas y bloque marca +1 día cuando proceda | PT1/PT3; regresión H03 |
| PC10 | Solapamiento | Guardias de días adyacentes y cruces reales se rechazan en backend, no solo visualmente | PT1; PostgreSQL desechable |
| PC11 | Límites | No inventar descansos máximos/mínimos o guardia de 24/48 horas desde horas iguales | Nuevo negativo del contrato |
| PC12 | Concurrencia | Dos guardados/reintentos no crean intervalos incompatibles o duplicados del mismo cambio | PostgreSQL aislado pendiente |
| PC13 | Atomicidad | Fallo parcial no deja medias asignaciones ni auditoría de éxito falsa; conserva anteriores | PT1/PT2, inyección de fallo |
| PC14 | 23A, avisos | Avisos internos solo después de cambio confirmado, para Administración/afectados, sin residentes ajenos | Nuevo escenario de destinatarios |
| PC15 | Reintento de aviso | Fallo de notificación no simula rollback del cambio; seguimiento y reintento sin duplicados | Nuevo caso de efectos posteriores |
| PC16 | Cancelar | Cancelar/cerrar con captura pendiente confirma salida; no guarda ni avisa | Frontend/integración pendiente |
| PC17 | Catálogo turno | Cambiar horario de turno compartido no reescribe silenciosamente ventanas históricas; se acredita representación o limitación | Caso de consumidores históricos pendiente |
| PC18 | Finalizar | Fecha de fin informada se conserva y aplica como contrato; no cambia todo inmediatamente mientras anuncia futuro | PT1; crear regresión de finalizar |
| PC19 | Planilla calculada | Cada celda distingue algoritmo/propuesta/registro; abrir consulta no crea plaza o jornada para aparentar confirmación | Nuevo caso del generador/consumidor |
| PC20 | 22A, navegación | Cobertura abre la consulta tabular correcta, no el catálogo de turnos renombrado | PT4; nueva integración de ruta |
| PC21 | CO01–CO06 | Tabla y drawer tienen misma fecha/área/turno, asignaciones y personas reales; no asistencia inferida | Nuevo adaptador/consulta |
| PC22 | Totales | Personas distintas y asignaciones son conteos diferentes; multiarea no infla total institucional | Nuevo caso de deduplicación |
| PC23 | Ausencia | Cero filas en filtro muestra sin registros; fallo, restricción y datos incompletos no se convierten en cero normal | Nuevo escenario de estados |
| PC24 | Ventana de cobertura | Turno de ayer todavía vigente y jornada futura/cerrada se distinguen según ventana y contrato | PT2/PT3; ampliar consulta |
| PC25 | Alcance | Abrir ficha o cambiar ID reautoriza fuente; lectura de cobertura no concede expedientes/edición | PT4; payload y permisos revocados |
| PC26 | Gráfico | Barras y tabla alternativa usan mismo dataset con unidad/fecha; no déficit/porcentaje de seguridad inventado | Frontend/dataset pendiente |
| PC27 | Historia/estado | Baja laboral y área inactiva no borran pasado; consulta no pretende snapshot exacto con datos sobrescritos | PT1/PT2; ampliación relacional |
| PC28 | Accesibilidad/build | Calendario/lista, tabla/drawer, foco, teclado, errores, carga, responsive cinco tamaños y build | QA visual/frontend pendientes al implementar |

## 3. Ejecución posterior

Entorno sintético desechable, SQLite para lógica portátil y PostgreSQL para bloqueo/carreras. Leer tests/AGENTS.md antes de ejecutar. No migrar/resetear bases institucionales. Comandos dirigidos candidatos, **no ejecutados aquí**:

```bash
php artisan test --filter=PersonalInstitucionalHorariosTest
php artisan test --filter=PersonalInstitucionalAsignacionesTest
php artisan test --filter=AsignacionesJornadaPlanificadaTest
php artisan test --filter=MiTurnoFueraDeJornadaTest
```

Las pruebas nuevas necesitan implementación de los consumidores reales; una suite anterior no acredita un aviso o ruta aún inexistentes. Registrar comandos, entorno, pruebas/aserciones, resultados y límites. QA visual en 1440/1280/1024/768/390 al conectar la UI; revisar ilustraciones estáticas no lo sustituye.

**Estado:** aceptación especificada y fuentes localizadas. No hay 28 pruebas de aplicación aprobadas por esta entrega documental.
