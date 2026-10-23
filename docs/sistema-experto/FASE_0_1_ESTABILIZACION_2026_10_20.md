# O.R.I.O.N. — Fase 0.1: estabilización

Fecha: 20/10/2026. Repositorio: `C:/Users/CARLAENCINAS/Desktop/RememberMind`. Rama: `feature/apoyo-decision-informe-neurocognitivo`.

**Estado: correcciones de código implementadas y verificadas; fase abierta por conservación histórica compuesta e integridad SQL pendientes de autorización estructural. No se avanzó a Fase 1.**

Se leyeron completos los tres archivos de auditoría solicitados. Se preservan sin sobrescritura. Las cifras siguientes proceden de pruebas ejecutadas, no de estimaciones. Los cambios previos del propietario se conservaron; la copia inicial de su diff y el inventario de estado están en el área de trabajo. No se hizo commit, push, despliegue ni cambio de estructura.

## Correcciones y causas raíz

| Hallazgo | Causa raíz | Corrección y comprobación |
|---|---|---|
| H01: diagnósticos accesibles por rol | La consulta lateral admitía el rol como sustituto de `diagnosticos.ver`. | Nueva consulta de continuidad revalida usuario/residente y Policy `consultarResultados`, exige permiso explícito antes del SQL. Negativo de revocación sin consulta a diagnósticos. |
| H02: cuidados sin permisos propios | La autorización de resultados expertos se reutilizaba para leer planes, intervenciones, programas y ejecuciones. | Se exigen los cuatro permisos de lectura antes de consultar cuidados, en panel y agenda. Capacidad denegada se presenta como falta de autorización. Pruebas de cada permiso ausente y revocación tras montaje. |
| H03: pendientes incorrectos | Cualquier ejecución de la intervención en el día/jornada podía resolver varios horarios; había tolerancia temporal, hora 08:00 implícita y filas PENDIENTE tratadas como realizadas. | Identidad por residente, intervención y fecha/hora programada exactas. Se respetan día, vigencia, turno y apertura/cierre del plan; no se inventa horario. Estado pendiente sigue pendiente; omisión y cancelación se distinguen. Regresión en panel, agenda y Mi Turno. |
| Modal de agenda: identificadores cruzados | El contexto autorizado del residente no restringía suficientemente los identificadores recibidos. | La intervención debe pertenecer a un plan del residente y la programación a esa intervención. Negativos de manipulación de ambos IDs. |
| H04: documento posterior al corte | Estado, residente y hash correctos no comprobaban fecha del documento frente al corte. | Comprobación estricta de fecha válida, presente y anterior/igual al corte en captura, construcción de memoria y replay. Pruebas positiva/negativa y tampering con huella recalculada. |
| H10: 85 errores PostgreSQL | `migrate:fresh` descartaba tablas y volvía a crear `vpo_inmutable`, pero la función anterior seguía en el esquema; `down()` no participa en fresh. | Restablecimiento exclusivo del esquema del runner desechable antes de fresh. Puente de eventos Symfony/Laravel habilitado explícitamente en tests. Se validan base aleatoria, propietario, runner sin privilegios, loopback, esquema public, PDO de escritura y ausencia de transacciones; se rechazan URLs/destinos divididos. Dos fresh consecutivos y guardas negativas aprobados. No se alteró la migración histórica. |
| H11: tres errores SQLite `red_v_rama` | Fixture genérica colocaba NULL en `fecha_hora_vinculacion`, permitido en una rama y prohibido para D-137. | Fecha explícita en la fixture D-137. Se conserva el trigger y la restricción real. |
| H12: fallo de arquitectura SQLite | El Blade de Psicología hacía una lectura Eloquent para decidir acceso. | La página prepara la capacidad; Blade presenta el booleano. `PresentationBoundaryTest` vuelve a aprobar. |
| Fixtures relacionadas de cuidados | Una usaba `fecha_inicio`, inexistente en el contrato; otra abría el plan después del horario que pretendía comprobar. | Se utiliza `fecha_hora_apertura` válida y anterior al instante ensayado. No se relajaron aserciones para ocultar un fallo. |

Se respetaron las Policies, los permisos y los roles existentes. No se añadieron permisos, concesiones por rol, reglas clínicas, diagnósticos automáticos ni reglas de inferencia nuevas. No se reconstruyó el motor.

## Resultados antes y después

| Motor y selección | Casos | Aprobados | Fallos | Errores | Omitidos | Aserciones |
|---|---:|---:|---:|---:|---:|---:|
| SQLite, auditoría original | 2144 | 2125 | 1 | 3 | 15 | 24704 |
| SQLite, cierre ampliado | 2236 | 2217 | 0 | 0 | 19 | 25324 |
| PostgreSQL, auditoría original | 132 | 47 | 0 | 85 | 0 | 282 |
| PostgreSQL, cierre ampliado | 191 | 191 | 0 | 0 | 0 | 1572 |

SQLite: 134 archivos únicos, los 124 originales y diez adicionales, repartidos sin casos repetidos en cuatro procesos con bases en memoria y almacenamiento aislado. Los motivos exactos de omisión y los casos PostgreSQL están en [la evidencia](C:/Users/CARLAENCINAS/Documents/Codex/2026-10-20/df/outputs/ORION-FASE-0-1-pruebas.json). PostgreSQL utiliza un clúster/base/runner propios con puerto aleatorio, detenido y eliminado al finalizar. Se ampliaron las selecciones; no se suman ejecuciones intermedias ni se afirma haber ejecutado toda la suite global de RememberMind.

Comparación por identidad de caso: los 2144 originales de SQLite siguen presentes (2129 aprobados y 15 omitidos por PostgreSQL); los 132 originales de PostgreSQL aprobaron. Los 19 casos omitidos en el cierre SQLite aprobaron en PostgreSQL, comprobados uno a uno.

Los casos automatizados cubren inferencia, grafo y reglas existentes, adaptadores y fuentes, captura Petersen, corte documental, replay, conservación disponible, revisiones, gobierno, permisos, navegación, arquitectura, agenda y concurrencia PostgreSQL. Los comandos, JUnit y detalle por caso están adjuntos. Las pruebas de corrupción generan deliberadamente excepciones registradas; se evaluó el estado JUnit de cada caso.

Controles adicionales: 22 archivos PHP sin errores de sintaxis; Pint `--test` aprobado en los 21 archivos tratados con ese formateador; ninguna migración alterada según SHA256; build Vite aprobado en directorio temporal (7,05 s); los dos ensayos de independencia del motor aprobados. `git diff --check` señala espacios finales en los dos Blade que ya tenían esos hunks en el diff inicial del propietario; no es un fallo PHPUnit y se conservaron esos cambios previos. No se ejecutó QA visual manual ni validación clínica de instrumentos; estas pruebas técnicas no equivalen a certificación clínica.

## Las 29 tablas y sus relaciones

Se consultaron exclusivamente catálogos PostgreSQL con transacción READ ONLY, sin filas clínicas: 120 tablas físicas, 104 de negocio (75 operativas y 29 expertas), más 16 técnicas. Las 29 expertas están presentes: 23 D-137, cinco de conservación reducida y una de revisión profesional. Modelo, tabla y PK coinciden en las 29; se revisaron relaciones explícitas con claves string. El [inventario verificado](C:/Users/CARLAENCINAS/Documents/Codex/2026-10-20/df/outputs/ORION-FASE-0-1-inventario-verificado.csv) detalla columnas, FK e índices por tabla. No se modificaron modelos ni migraciones. La metadata comparada con la auditoría no muestra cambios de columnas ni restricciones.

La referencia histórica a 69 operativas no se utilizó para autorizar cambios: se preservó todo el esquema operativo instalado, incluidas sus extensiones aprobadas. Una FK existente prueba referencia válida; no prueba por sí misma igualdad de evaluación/versión en todo el grafo.

## Pendientes que impiden cerrar la fase

| Hallazgo | Estado verificable y acción concreta |
|---|---|
| H05, conservación íntegra del compuesto instrumental/Petersen (P1 funcional) | El compuesto se prepara en memoria; el contrato conserva `persistido=false` y `release_retenido=false`. Las 29 tablas no representan íntegramente ambas ramas, configuración adquirida, membresías, orden, ausencias, fuentes mutables y ejecutor. Se documentó [propuesta estructural](FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md). Antes de cualquier migración: prototipo desechable guardar/recuperar/replay contra DTO canónico, diccionario exacto y autorización expresa del propietario. No se simula persistencia con JSON/EAV, campos ajenos o archivos clínicos. |
| H08, reconstrucción de un pasado mutable | El corte corregido excluye documentos futuros; no reconstruye contenido previo ya modificado sin instantánea. La ampliación debe conservar la proyección adquirida y sus colecciones cerradas. No se inventó ni reescribió historia anterior. |
| H07, integridad contextual e inmutabilidad SQL | Las guardas Eloquent y los controles SQL aprobados existentes siguen vigentes; no equivalen a prohibición universal de SQL directo ni a todas las igualdades de evaluación/versión. Constraints/triggers adicionales requieren autorización estructural separada. |
| H06, vinculación instrumental versionada incompleta | Sigue pendiente el cierre de la matriz instrumento/edición/método/original/perfil/interpretación con oráculos aprobados. No se inventó una configuración ni metodología clínica y no se inició Fase 1. |
| H09, cobertura/documentación | Esta entrega registra correcciones y evidencia. Persisten las pruebas de aceptación del futuro guardado compuesto, retención/restauración del ejecutor y QA visual/validación institucional aplicables. |

La propuesta distingue el candidato reducido existente de una alternativa con copias expertas tipadas. El primero incluye modificaciones operativas congeladas y no puede instalarse con esta autorización; la alternativa necesita su propio diccionario. Ninguna se ha implementado. La restricción de no alterar estructuras se ha respetado; por ello **no se garantiza todavía conservación histórica completa del compuesto Petersen**.

## Integridad histórica y permisos

No se ejecutaron escrituras, seeds ni migraciones en la base operativa. No se eliminaron ni sobrescribieron reglas, inferencias, revisiones o evaluaciones históricas. Los ensayos de escritura y destrucción de esquemas se limitaron a bases desechables y fixtures; incluyen rechazo de modificaciones/borrados donde el contrato actual los prohíbe y revisión/concurrencia. El historial existente no se recalculó ni se convirtió a un formato nuevo.

Se verificó la denegación por permiso y por alcance, incluida revocación, antes de consultar recursos clínicos. La consulta de resultados sigue usando su Policy actual. Estas garantías están acotadas al flujo corregido y a las protecciones actuales: no se extienden a la conservación compuesta todavía ausente ni a la inmutabilidad SQL universal pendiente en H07.

## Archivos intervenidos en esta fase

Rutas relativas al repositorio indicado arriba; varios archivos ya contenían cambios del propietario, preservados. `RedisenoEnfermeriaIntegralTest.php` ya existía sin seguimiento: se corrigió su fixture y se ordenaron imports. Los otros archivos previos ajenos al alcance no se atribuyen a esta entrega.

- [app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarContinuidadAsistencialExperta.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarContinuidadAsistencialExperta.php)
- [app/Modulos/ApoyoDecision/Aplicacion/Consultas/CapturasInstrumentalesResidente.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Aplicacion/Consultas/CapturasInstrumentalesResidente.php)
- [app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php)
- [app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto-residente.blade.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto-residente.blade.php)
- [app/Modulos/Cuidados/Aplicacion/Consultas/OcurrenciasCuidado.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Cuidados/Aplicacion/Consultas/OcurrenciasCuidado.php)
- [app/Modulos/Cuidados/Aplicacion/Consultas/MiTurnoService.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Cuidados/Aplicacion/Consultas/MiTurnoService.php)
- [app/Modulos/Cuidados/Presentacion/Livewire/Paginas/AgendaEnfermeria.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Cuidados/Presentacion/Livewire/Paginas/AgendaEnfermeria.php)
- [app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/agenda-enfermeria.blade.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/agenda-enfermeria.blade.php)
- [app/Modulos/Valoraciones/Presentacion/Livewire/Paginas/DashboardPsicologo.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Valoraciones/Presentacion/Livewire/Paginas/DashboardPsicologo.php)
- [app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/dashboard-psicologo.blade.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/dashboard-psicologo.blade.php)
- [motor-experto/src/motor_experto/dominio/instrumentos/CorteDocumentoInstrumental.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/motor-experto/src/motor_experto/dominio/instrumentos/CorteDocumentoInstrumental.php)
- [motor-experto/src/motor_experto/adaptadores/entrada/ConstructorMemoriaInstrumental.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/motor-experto/src/motor_experto/adaptadores/entrada/ConstructorMemoriaInstrumental.php)
- [motor-experto/src/motor_experto/soporte/conservacion/InstantaneaInstrumental.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/motor-experto/src/motor_experto/soporte/conservacion/InstantaneaInstrumental.php)
- [scripts/probar-postgres.ps1](C:/Users/CARLAENCINAS/Desktop/RememberMind/scripts/probar-postgres.ps1)
- [tests/Support/RestablecerEsquemaPostgres.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Support/RestablecerEsquemaPostgres.php)
- [tests/TestCase.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/TestCase.php)
- [tests/Feature/ResumenCognitivoResidenteTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/ResumenCognitivoResidenteTest.php)
- [tests/Feature/EstabilizacionOrionContinuidadTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/EstabilizacionOrionContinuidadTest.php)
- [tests/Feature/RedisenoEnfermeriaIntegralTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/RedisenoEnfermeriaIntegralTest.php)
- [tests/Feature/ConocimientoCognitivoInstitucionalTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/ConocimientoCognitivoInstitucionalTest.php)
- [tests/Feature/PreparacionPostgresRepetidaTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/PreparacionPostgresRepetidaTest.php)
- [tests/Feature/GuardasPreparacionBaseTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/GuardasPreparacionBaseTest.php)
- [tests/Unit/InstantaneaInstrumentalCompletaTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Unit/InstantaneaInstrumentalCompletaTest.php)
- [tests/Unit/CorteDocumentoInstrumentalTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Unit/CorteDocumentoInstrumentalTest.php)
- [tests/Unit/OcurrenciasCuidadoTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Unit/OcurrenciasCuidadoTest.php)
- [tests/Feature/MiTurnoAuditoriaCierreTest.php](C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/MiTurnoAuditoriaCierreTest.php)
- [docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md](C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md)
- [docs/sistema-experto/FASE_0_1_ESTABILIZACION_2026_10_20.md](C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_0_1_ESTABILIZACION_2026_10_20.md)

## Evidencia adjunta

- [Resultados y comandos ejecutados](C:/Users/CARLAENCINAS/Documents/Codex/2026-10-20/df/outputs/ORION-FASE-0-1-pruebas.txt).
- [Resultados por caso, omisiones y hashes JUnit](C:/Users/CARLAENCINAS/Documents/Codex/2026-10-20/df/outputs/ORION-FASE-0-1-pruebas.json).
- [Inventario físico de 29 tablas](C:/Users/CARLAENCINAS/Documents/Codex/2026-10-20/df/outputs/ORION-FASE-0-1-inventario-verificado.csv).
- [Comprobación de estructura](C:/Users/CARLAENCINAS/Documents/Codex/2026-10-20/df/outputs/ORION-FASE-0-1-integridad-esquema.json).
- [Dependencia estructural de conservación](FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md).
