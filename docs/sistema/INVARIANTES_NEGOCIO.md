---
title: "Invariantes de negocio"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [invariant_mapping]
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
runtime_evidence_status: PARTIAL
runtime_evidence_scope: FASE_3_TARGETED_TESTS_ISOLATED_POSTGRESQL
supersedes: []
related_docs: ["../TRAZABILIDAD.md", "../DEUDA_TECNICA.md"]
related_modules: [ingreso, alojamiento, jornadas, medicacion, signos, alertas, pase]
---

# Invariantes de negocio

Fuentes rectoras: [AGENTS.md](../../AGENTS.md), [baseline](../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [decisión V2.2](../../docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md) y [roles](../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Snapshot del commit base + árbol con cambios previos sin commit. El mapeo original de Fase 2 fue estático. Fase 3 ejecuta migraciones/seed, pruebas PostgreSQL aisladas y build; resultados y límites en TRAZABILIDAD. Inventario normativo 70+1=71; su comprobación instalada se registra separadamente, sin cambiar el esquema.

## Alcance de los estados

**APPROVED CONTRACT:** invariantes derivados de fuentes rectoras. **OBSERVED IMPLEMENTATION:** protección localizada. PROTECTED significa mecanismo estático y expectativa de test definidos para el alcance citado; nunca suite pasada ni garantía global. PARTIAL indica cobertura/capa/entrada incompleta. MISSING indica mecanismo no localizado; NOT_VERIFIED evidencia insuficiente. **TESTED EXPECTATION** no es ejecución.

## Invariantes identificados

| ID | Contrato / descripción | Implementación observada | Test definido | Estado / límites |
|---|---|---|---|---|
| INV-ADM-001 | Aprobar preadmisión no crea residente | [FormalizarAdmision / PreadmisionController](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php) | TEST-ADM-001 | PROTECTED — APPROVED CONTRACT AGENTS; separación observada |
| INV-ADM-002 | Formalizar ingreso es atómico | [FormalizarAdmision::ejecutar](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php) | TEST-ADM-001 | PARTIAL — Transaction/locks observados; Fase3AdmisionAtomicidadTest acredita 9 puntos de rollback en PostgreSQL; no certificación de toda operación |
| INV-ALO-001 | No reutilizar cama con ocupación activa | [OcupacionCama::saving + Action + hardening](../../app/Models/OcupacionCama.php) | TEST-ALO-001, TEST-ALO-002 | PARTIAL — Eloquent/índice definidos; Fase3ConcurrenciaCamaPostgresTest acredita carrera real de admisión, sin DDL nuevo |
| INV-ALO-002 | Residente no tiene dos ocupaciones activas | [OcupacionCama::saving + hardening](../../app/Models/OcupacionCama.php) | TEST-ALO-001 | PARTIAL — Negativo de ocupación activa ejecutado en Fase 3; carrera de admisión acredita una cama, no dos camas para un residente |
| INV-CON-001 | Consentimiento firmado por residente XOR contacto del mismo residente | [Consentimiento::saving](../../app/Models/Consentimiento.php) | Fase3AdmisionAtomicidadTest::test_consentimiento_no_acepta_contacto_relacional_de_otro_residente | PARTIAL — Negativo Model ejecutado en PostgreSQL; no demuestra todo SQL |
| INV-IDE-001 | Usuario y personal son entidades distintas; no fabricar personal | [ContextoClinicoService::personalActivo](../../app/Backend/Modulos/Clinica/Servicios/ContextoClinicoService.php) | TEST-IDE-001 | PROTECTED — APPROVED CONTRACT AGENTS/BDD; cuenta enlazada a profesional real |
| INV-CLI-001 | Autor clínico deriva del usuario; no atribuir a otro profesional | [RegistrarSignosVitalesAction / AutorizacionClinicaService](../../app/Backend/Modulos/Clinica/Acciones/RegistrarSignosVitalesAction.php) | TEST-CLI-001, TEST-MED-002 | PROTECTED — Protección de entradas mapeadas, no certificación de todo controlador legacy |
| INV-MED-001 | Administración pertenece a prescripción del mismo residente y horario propio | [AdministracionMedicacion::saving / RegistrarAdministracionMedicacionService](../../app/Models/AdministracionMedicacion.php) | TEST-MED-001 | PROTECTED — Model y Service rechazan relación cruzada; DB hardening definido |
| INV-MED-002 | Enfermería no prescribe ni modifica orden médica | [PrescripcionPolicy / MedicacionController](../../app/Policies/PrescripcionPolicy.php) | TEST-AUTH-001 | PROTECTED — APPROVED CONTRACT roles; negar create a Enfermería |
| INV-FAM-001 | Familiar solo accede al residente vinculado/autorizado | [ResidentePolicy::view](../../app/Policies/ResidentePolicy.php) | TEST-FAM-001 | PROTECTED — Scope de residente protegido en entrada mapeada; contenido separado TECH-001 |
| INV-CLI-002 | Evento clínico nuevo genera registro; no sobrescribir historia para ocultar error | [SignosVitalesService::rectificar / CuidadoController](../../app/Backend/Modulos/Clinica/Servicios/SignosVitalesService.php) | NOT_FOUND (universal, no existe garantía de todos los módulos) | PARTIAL — Corrección signos conserva anterior; no guardia de update universal |
| INV-ALT-001 | Alerta conserva origen y trayectoria de eventos | [AlertaController / AlertasService / Panel](../../app/Http/Controllers/Alertas/AlertaController.php) | TEST-ALT-001 | PARTIAL — Fase 3 concentra eventos iniciales/estados y acredita historia/rollback por entradas; no todos los orígenes concurrentes |
| INV-CLI-003 | Historia clínica no se borra físicamente de forma ordinaria | [ModeloOperativo::boot deleting](../../app/Models/ModeloOperativo.php) | TEST-PAS-003 | PARTIAL — Bloquea delete de instancia Eloquent; Query Builder/SQL directo fuera de esa guardia |
| INV-INS-001 | Respuesta pertenece a instrumento aplicado y opción a pregunta | [RespuestaInstrumento::saving / InstrumentoController](../../app/Models/RespuestaInstrumento.php) | TEST-INS-001 | PROTECTED — Validación estructural no valida licencias/método, DEC-OPEN-005 |
| INV-ACT-001 | No duplicar residente participante en actividad | [UNIQUE(cod_actividad,cod_residente)](../../database/migrations/2026_09_18_001066_create_participantes_actividad_table.php) | NOT_FOUND (negativo específico en mapeo) | PARTIAL — Índice definido, BDD instalada no comprobada |
| INV-EST-001 | Resultado respeta estudio/tipo/componente y no duplica componente | [EstudioClinicoController::resultados](../../app/Http/Controllers/Clinica/EstudioClinicoController.php) | TEST-EST-001 | PROTECTED — Valida relación, transaction, rechaza duplicado y UNIQUE declarado |
| INV-SV-001 | Preview de signos no persiste ni genera alerta | [SignosVitalesService::preEvaluar](../../app/Backend/Modulos/Clinica/Servicios/SignosVitalesService.php) | TEST-SV-001 | PROTECTED — No create en preEvaluar; test compara contadores |
| INV-SV-002 | Objetivo personalizado no rebaja crítico general | [EvaluadorSignosVitales / decisión V2.2](../../app/Backend/Modulos/Clinica/SignosVitales/EvaluadorSignosVitales.php) | TEST-SV-002 | PROTECTED — APPROVED CONTRACT V2.2 + merge protegido |
| INV-PAS-001 | Pase solo se entrega/recibe en contexto autorizado y no se reescribe tras recepción | [PaseTurnoService::procesarPase / confirmarRecepcion](../../app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php) | TEST-PAS-001, TEST-PAS-002 | PARTIAL — Locks/ciclo sí; Fase 3 revalida permiso/cuenta/ARJ exacta y origen de anulación; pruebas dirigidas del ciclo |
| INV-JOR-001 | Asignación requiere personal/jornada reales, no fallback | [AsignacionResidenteJornada / asignarJornada](../../app/Models/AsignacionResidenteJornada.php) | TEST-JOR-001 | PROTECTED — APPROVED CONTRACT integridad/contexto; distintas relaciones laborales/asistenciales |

Los NOT_FOUND describen cobertura concreta de esta revisión, no ausencia absoluta de tests en todo el repositorio. El negativo de consentimiento se añadió y ejecutó en Fase 3. [Trazabilidad](../../docs/TRAZABILIDAD.md) proporciona métodos exactos. Fuentes normativas no se sustituyen por tests, que pueden incorporar excepción temporal o expectativa antigua.

## Transaction boundaries

| Caso de uso | Transacción requerida / entidades | Implementación observada | Rollback / límites | Test |
|---|---|---|---|---|
| Formalizar admisión | Sí: residente, admisión, ocupación, vínculo, historial, consentimiento, seguro opcional | FormalizarAdmision::ejecutar transaction(...,3), locks preadmisión/cama | Rollback9puntos y carrera real PostgreSQL acreditados en Fase 3 (Action de admisión) | TEST-ADM-001 happy path |
| Prescripción programada | Sí: orden + horarios | [prescribir](../../app/Http/Controllers/Medicacion/MedicacionController.php) transaction | SQL conjunto; no demuestra administración real | [Tests definidos](../../tests/Feature/MedicacionV2Test.php) |
| Administración programada | Sí: registro coherente con orden/horario, duplicado | [registrarProgramada](../../app/Backend/Modulos/Medicacion/Servicios/RegistrarAdministracionMedicacionService.php) transaction + locks | Rollback SQL; concurrencia no ejecutada | TEST-MED-001/002 no prueban carreras |
| PRN | Una fila; no transaction propia observada | registrarPrn create; sin identidad de reintento aprobada | No declarar idempotencia por código aleatorio; DEC-OPEN-004 | Reintento según decisión NOT_FOUND |
| Confirmar signos críticos | Sí: signo + alerta + evento | [Action](../../app/Backend/Modulos/Clinica/Acciones/RegistrarSignosVitalesAction.php) + decisión dentro de transaction | Fallo alerta debe revertir signo | TEST-SV-004 definido |
| Rectificar signos | Sí: original RECTIFICADO + nueva medición | SignosVitalesService::rectificar transaction/lock | No reevalúa alerta en ese método; FK original/nuevo no añadida | Rollback específico NOT_FOUND |
| Mutar alerta + evento | Sí | AlertasService concentra Panel/HTTP, transaction/lock y evento con actor/estados/fecha | Fase 3 acredita creación/cambio y rollback; sin sobrescribir historia | Fase3AlertasTest; AlertasFlujoTest; TEST-ALT-001 |
| Asignar residente/jornada | Sí: revalidar jornada/duplicado | [asignarJornada](../../app/Http/Controllers/Cuidados/CuidadoController.php) transaction + lock | No fabrica jornada ni personal | TEST-JOR-001 solo no-fallback |
| Entregar/recibir pase | Sí para ciclo y doble confirmación | procesarPase/confirmarRecepcion/anular transaction; generar/HTTP crean una fila con contexto revalidado | Lock no demuestra unicidad física de todas las entradas; TECH-005/009 | TEST-PAS-001/002 secuenciales |
| Resultados de estudio | Sí: resultados + REALIZADO | [resultados](../../app/Http/Controllers/Clinica/EstudioClinicoController.php) transaction | Rechazo componente ajeno/duplicado | TEST-EST-001 |
| Aplicar instrumento | Sí: aplicación + respuestas | [aplicar](../../app/Http/Controllers/Instrumentos/InstrumentoController.php) transaction | Coherencia previa; no valida método/licencia | TEST-INS-001 Model; rollback HTTP NOT_FOUND |
| Incidente/lesión/alerta | Sí | [registrar](../../app/Backend/Modulos/Enfermeria/Servicios/IncidentesEnfermeriaService.php) transaction exterior | AlertasService crea evento inicial dentro de la transacción exterior; fallo integral de incidente no acreditado | [Tests definidos](../../tests/Feature/IncidentesEnfermeriaTest.php); rollback integral no acreditado |
| Archivo + metadata | Filesystem y SQL no comparten transaction | [storage luego create](../../app/Http/Controllers/Documentos/DocumentoController.php) | Limpieza compensatoria/huérfanos NOT VERIFIED | Test documento privado no prueba fallo combinado |

## Garantías físicas separadas

[Hardening](../../database/migrations/2026_09_24_000300_harden_v2_data_integrity.php) define índices/checks y mecanismos PostgreSQL/SQLite. [Resultado único](../../database/migrations/2026_09_18_001033_create_resultados_estudio_table.php) y [Respuesta única](../../database/migrations/2026_09_18_001060_create_respuestas_instrumento_table.php) declaran UNIQUE. Presencia estática no demuestra instalación, SQL directo ni carrera. ModeloOperativo veta delete de instancia; Query Builder/SQL directo fuera de esa guardia.

## Resumen

11 PROTECTED, 9 PARTIAL; 20 invariantes: clasificación por capa del mapeo original. Fase 3 acredita los diez IDs prioritarios que se detallan abajo. No es certificación global ni garantía universal de SQL directo. [Deuda](../../docs/DEUDA_TECNICA.md) separa correcciones futuras de este contrato documental.

## Evidencia runtime prioritaria de Fase 3

La matriz superior conserva clasificación por capa (PROTECTED/PARTIAL), no un sello global. Evidencia acotada en [TRAZABILIDAD](../TRAZABILIDAD.md):

| ID | Prueba ejecutada PostgreSQL | Alcance |
|---|---|---|
| INV-ADM-001 | BddOperativaV2Test::test_residente_solo_se_crea_al_formalizar_admision_y_aprobar_no_lo_crea y test_creacion_directa_de_residente_esta_bloqueada | Action/Eloquent; no prohibición universal SQL de crear residente |
| INV-ADM-002 | Fase3AdmisionAtomicidadTest::test_fallo_intermedio_revierte_toda_la_admision (9 puntos) | Todas tablas de operación y auditoría regresan a conteos iniciales |
| INV-ALO-001 | Fase3ConcurrenciaCamaPostgresTest::test_dos_procesos_solapados_solo_formalizan_una_admision_para_la_cama | Dos conexiones independientes solapadas y locks reales; 1 ganador/1 denegación |
| INV-ALO-002 | BddOperativaV2Test::test_no_se_reutiliza_cama_ni_residente_con_ocupacion_activa | Negativo Eloquent/Action; no carrera distinta por dos camas para un residente |
| INV-MED-001 | BddOperativaV2Test::test_administracion_debe_corresponder_a_prescripcion_del_mismo_residente | Relación cruzada rechazada; lectores D preservan escritor real |
| INV-MED-002 | Fase3SeguridadNucleoTest::test_enfermeria_no_crea_ni_edita_prescripciones_aunque_tenga_permiso | HTTP con grants técnicos no sustituye competencia |
| INV-CON-001 | Fase3AdmisionAtomicidadTest::test_consentimiento_no_acepta_contacto_relacional_de_otro_residente | Model; no nueva FK compuesta ni protección SQL directa acreditada |
| INV-FAM-001 | Fase3SeguridadNucleoTest, casos familiar propio/ajeno/contacto inactivo | Identidad/visitas propias; clínica/documentos no publicados denegados |
| INV-CLI-003 | Fase3AdmisionAtomicidadTest::test_borrado_ordinario_de_signo_preserva_historia | Delete de instancia conserva snapshot persistido; no universal Query Builder/SQL |
| INV-ALT-001 | Fase3AlertasTest (creación/cambio/eventos/rollback/inmutabilidad) | Entradas manuales/detector/HTTP; no certificación de concurrencia de todos orígenes |

Total prioritario: 10 IDs con evidencia runtime acotada. Los nueve solicitados más rollback de admisión. No se cambia el contrato de los restantes diez ni se presentan como fallidos por no verificarse en esta fase. Los resultados exactos/fallos del gate completo se conservan en TRAZABILIDAD.
