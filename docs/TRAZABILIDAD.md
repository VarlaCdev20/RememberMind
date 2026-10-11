---
title: "Trazabilidad de Fases 2 a 4"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-07
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [static_traceability, automated_test_evidence]
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
runtime_evidence_status: PARTIAL
runtime_evidence_scope: FASE_3_AND_4_AUTOMATED_TESTS_ISOLATED_POSTGRESQL
automated_global_gate_status: PASS
supersedes: []
related_docs: ["sistema/INVARIANTES_NEGOCIO.md", "seguridad/MATRIZ_ROLES_PERMISOS.md", "DEUDA_TECNICA.md"]
related_modules: [ingreso, alojamiento, jornadas, medicacion, signos, alertas, pase]
---

# Trazabilidad de Fases 2 a 4

## Lote posterior — Dolor V2, 09/10/2026

[Decisión aprobada](base-de-datos/DECISION_DOLOR_V2.md) → migración de tres
columnas/self-FK → CuidadosEnfermeriaService → MisPacientes → captura/popup
Dolor → DolorMigrationTest/MisPacientesRedisenadaTest/dolor-registro.test.js.
Pruebas SQLite/PG, regresión Signos y revisión runtime sintética se registran
en [resultado Dolor V2](frontend/FORMULARIO_DOLOR_V2_RESULTADO.md). El gate
global de las fases históricas de este documento no acredita el árbol actual.
No hay staging, commit ni push; se preservan cambios previos ajenos.

Gate final de este lote: PHP935 PASS/15 omitidas/5 fallos previos,19.028
aserciones; JS104 PASS; build PASS; Dolor SQLite37/1.420 y PostgreSQL27/193,
regresión Signos35/184. PostgreSQL fresh/seed/rollback PASS, conteo71 operativas
sin cambios. QA visual parcial, sin certificar los estados no ejercitados.

Las matrices iniciales se mapearon estáticamente en Fase 2 sobre commit base y cambios previos. Las secciones Fases 3 y 4 registran comandos/DB/tests ejecutados y límites. verified_against_commit identifica la base, no un commit de correcciones; runtime_verified=false conserva ausencia de certificación integral clínica/operativa o de producción. Un gate automatizado global PASS no sustituye esa certificación. El inventario normativo sigue subordinado a sus fuentes BDD aprobadas.

## Alcance y clasificación de evidencia

Solo siete módulos núcleo. IDs locales permiten localizar requisito, regla, invariante, autorización, flujo y test; no son aprobación institucional nueva. **APPROVED CONTRACT** en [AGENTS](../AGENTS.md), [baseline](../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [V2.2](../docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md) y [roles](../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). **OBSERVED IMPLEMENTATION** indica código leído; **TESTED EXPECTATION** indica aserción definida en el mapeo Fase 2; solo la evidencia Fase 3 acredita ejecución de un método/gate concreto. PROPOSED/HISTORICAL/OPEN DECISION/TECHNICAL DEBT/CONFLICT/NOT VERIFIED conservan sentido de [gobernanza](../docs/GOBERNANZA_DOCUMENTAL.md).

## Requisitos, reglas y autorización por módulo

| REQ / requisito | RULE / regla | AUTH / frontera | Implementación principal | Contrato / invariantes / flujo / tests |
|---|---|---|---|---|
| REQ-ADM-001 — Revisar postulantes y formalizar admisión con cama sin convertir aprobación en creación de residente. | RULE-ADM-001 — Aprobar no crea residente; no CRUD directo de creación; admisión con cama y operación atómica. | AUTH-ADM-001 — Middleware HTTP preadmisiones.revisar/admisiones.formalizar; panel verifica roles y acceso de dashboard. NONE OBSERVED Policy de admisión. Action revalida cuenta, rol autorizado y permiso formalizar (corrección Fase 3). | [app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php](../app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php); [app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php](../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php) | [Ingreso institucional](../docs/modulos/ingreso-institucional/README.md); INV-ADM-001, INV-ADM-002, INV-CON-001; FLOW-ADM-001; TEST-ADM-001, TEST-PRE-001 |
| REQ-ALO-001 — Mantener identidad residencial y alojamiento disponible respetando ingreso formal. | RULE-ALO-001 — Una ocupación activa por cama y por residente; no creación directa de residente; no interpretar cama física como permiso de ingreso. | AUTH-ALO-001 — ResidentePolicy::view / viewAny; create false. Familiar vínculo activo/autoriza_informacion; contenido clínico no permitido automáticamente TECH-001. | [app/Frontend/Livewire/Admisiones/HabitacionesPanel.php](../app/Frontend/Livewire/Admisiones/HabitacionesPanel.php); [app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php](../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php) | [Residentes y alojamiento](../docs/modulos/residentes-alojamiento/README.md); INV-ALO-001, INV-ALO-002, INV-FAM-001; FLOW-ADM-001; TEST-ALO-001, TEST-ALO-002, TEST-FAM-001 |
| REQ-JOR-001 — Separar catálogo de turno, jornada real y asignaciones laborales/asistenciales. | RULE-JOR-001 — Usuario != personal; turno != jornada; asignación laboral != asistencial; no fallback de primera fila. | AUTH-JOR-001 — Panel autorizarAsignacion comprueba ACTIVO+turnos.asignar; asignarJornada HTTP roles SA/Admin+gestionar. Service de turno añade scope/competencia para cuidado. | [app/Frontend/Livewire/Administracion/Identidad/TurnosAsignacionesPanel.php](../app/Frontend/Livewire/Administracion/Identidad/TurnosAsignacionesPanel.php); [app/Backend/Modulos/Enfermeria/Servicios/TurnoEnfermeriaService.php](../app/Backend/Modulos/Enfermeria/Servicios/TurnoEnfermeriaService.php) | [Jornadas y asignaciones](../docs/modulos/jornadas-asignaciones/README.md); INV-IDE-001, INV-JOR-001; FLOW-JOR-001; TEST-JOR-001, TEST-IDE-001 |
| REQ-MED-001 — Separar orden médica, horarios y administración/omisión asistencial. | RULE-MED-001 — Administrar mismo residente/orden/horario; no atribuir a otro personal; no registrar dosis ficticia para satisfacer FK. | AUTH-MED-001 — PrescripcionPolicy + AutorizacionClinicaService para orden; HTTP administración solo Enfermería y Service contextual. AdministracionMedicacionPolicy existe pero no es llamado por ese HTTP y contempla Médico: no mezclar entradas. | [app/Http/Controllers/Medicacion/MedicacionController.php](../app/Http/Controllers/Medicacion/MedicacionController.php); [app/Backend/Modulos/Medicacion/Servicios/AgendaMedicacionService.php](../app/Backend/Modulos/Medicacion/Servicios/AgendaMedicacionService.php) | [Medicación](../docs/modulos/medicacion/README.md); INV-MED-001, INV-MED-002, INV-CLI-001; FLOW-MED-001; TEST-MED-001, TEST-MED-002, TEST-AUTH-001 |
| REQ-SV-001 — Registrar mediciones longitudinales y evaluación explicable sin persistencia durante preview. | RULE-SV-001 — Preview cero efectos; nuevo registro por evento; objetivo no rebaja crítico general; no persistir evaluación como diagnóstico global. | AUTH-SV-001 — Action comprueba autor/jornada/residente; TurnoEnfermeriaService contextual. Objetivos Action médico/permiso propios. NONE OBSERVED Policy específica de signos; no inventarla. | [app/Frontend/Livewire/Enfermeria/Cuidados/MisPacientes.php](../app/Frontend/Livewire/Enfermeria/Cuidados/MisPacientes.php); [app/Backend/Modulos/Clinica/Acciones/RegistrarSignosVitalesAction.php](../app/Backend/Modulos/Clinica/Acciones/RegistrarSignosVitalesAction.php) | [Signos vitales](../docs/modulos/signos-vitales/README.md); INV-SV-001, INV-SV-002, INV-CLI-001, INV-CLI-002; Secuencia signos en FLUJOS_GERIATRICOS; TEST-SV-001, TEST-SV-002, TEST-SV-003, TEST-SV-004, TEST-SV-005 |
| REQ-ALT-001 — Conservar condición/origen y trayectoria de atención, sin confundir alerta, evento, notificación y auditoría. | RULE-ALT-001 — Alerta no pierde eventos; evento clínico no equivale a dispatch UI, correo ni Activitylog. | AUTH-ALT-001 — Panel comprobarPermiso por acción o gestionar y scope según rol; HTTP Enfermería contexto o Admin permiso específico; Policy alerta NONE OBSERVED. | [app/Frontend/Livewire/Compartido/Alertas/AlertasPanel.php](../app/Frontend/Livewire/Compartido/Alertas/AlertasPanel.php); [app/Backend/Modulos/Alertas/Servicios/AlertasService.php](../app/Backend/Modulos/Alertas/Servicios/AlertasService.php) | [Alertas](../docs/modulos/alertas/README.md); INV-ALT-001, INV-SV-001; FLOW-ALT-001; TEST-ALT-001, TEST-SV-001, TEST-SV-004 |
| REQ-PAS-001 — Transferir información relevante del residente entre jornadas y profesionales autorizados. | RULE-PAS-001 — Conservar fuente/fecha/autor/contexto; no fabricar jornada entrante ni perder pendientes; no redefinir resultados clínicos por lector. | AUTH-PAS-001 — Service revalida cuenta, permiso, turno/residente/ARJ propios al procesar; recepción activa legítima y anulación por emisor autorizado (Fase 3). | [app/Frontend/Livewire/Enfermeria/Cuidados/PaseTurnoPanel.php](../app/Frontend/Livewire/Enfermeria/Cuidados/PaseTurnoPanel.php); [app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php](../app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php) | [Pase de turno y continuidad](../docs/modulos/pase-turno/README.md); INV-PAS-001, INV-CLI-003; FLOW-PAS-001; TEST-PAS-001, TEST-PAS-002, TEST-PAS-003 |

No se deriva competencia de UI ni de seed aislado. [Modelo](../docs/seguridad/MODELO_AUTORIZACION.md) y [matriz](../docs/seguridad/MATRIZ_ROLES_PERMISOS.md) señalan diferencias por entrada y guardias reales.

## Catálogo de tests definidos

| TEST | Archivo / método exacto | Alcance inspeccionado | Estado de evidencia |
|---|---|---|---|
| TEST-ADM-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_residente_solo_se_crea_al_formalizar_admision_y_aprobar_no_lo_crea` | Separación aprobación/admisión y relaciones creadas | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-ALO-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_no_se_reutiliza_cama_ni_residente_con_ocupacion_activa` | Rechazo secuencial Eloquent, no carrera | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-ALO-002 | [tests/Feature/IntegridadFisicaBddV2Test.php](../tests/Feature/IntegridadFisicaBddV2Test.php) :: `test_la_base_impide_dos_ocupaciones_activas_para_la_misma_cama` | Restricción SQL en configuración de test; no PostgreSQL ejecutado | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-IDE-001 | [tests/Feature/ContextoClinicoIntegrityTest.php](../tests/Feature/ContextoClinicoIntegrityTest.php) :: `test_no_atribuye_una_operacion_al_primer_personal_disponible` | No elegir personal arbitrario | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-CLI-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_registro_clinico_toma_el_personal_del_usuario_autenticado` | Autor derivado de sesión | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-MED-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_administracion_debe_corresponder_a_prescripcion_del_mismo_residente` | Model rechaza orden ajena | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-AUTH-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_roles_competencias_y_acceso_familiar_estan_separados` | Policies/roles y vínculo, no publicación de contenido | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-FAM-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_familiar_no_puede_acceder_por_idor_a_otro_expediente` | HTTP residente ajeno denegado | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-ALT-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_alerta_conserva_historial_de_eventos` | Trayectoria HTTP; no todas las entradas | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-INS-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_respuestas_solo_aceptan_preguntas_del_instrumento_aplicado` | Coherencia pregunta/aplicación en Model | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-EST-001 | [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php) :: `test_estudios_rechazan_componentes_ajenos_y_aceptan_los_propios` | HTTP componente y duplicado | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-PAS-001 | [tests/Feature/PaseTurnoReconstruidoTest.php](../tests/Feature/PaseTurnoReconstruidoTest.php) :: `test_ciclo_borrador_a_entregado_y_bloqueo_edicion` | Ciclo panel/Service | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-PAS-002 | [tests/Feature/PaseTurnoReconstruidoTest.php](../tests/Feature/PaseTurnoReconstruidoTest.php) :: `test_solo_receptor_valido_puede_recibir_pase` | Receptor propio/ajeno | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-PAS-003 | [tests/Feature/PaseTurnoReconstruidoTest.php](../tests/Feature/PaseTurnoReconstruidoTest.php) :: `test_no_borrado_fisico_anulacion_logica` | Conserva fila; no autorización negativa de anulación | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-JOR-001 | [tests/Feature/AsignacionesIntegrityTest.php](../tests/Feature/AsignacionesIntegrityTest.php) :: `test_resident_assignment_does_not_fabricate_a_workday_or_professional` | Sin jornada/personal ficticios | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-SV-001 | [tests/Feature/MisPacientesRedisenadaTest.php](../tests/Feature/MisPacientesRedisenadaTest.php) :: `test_formulario_muestra_preevaluacion_sin_persistirla` | Preview no altera contadores signos/alertas | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-SV-002 | [tests/Feature/ObjetivosSignosVitalesTest.php](../tests/Feature/ObjetivosSignosVitalesTest.php) :: `test_objetivo_no_oculta_critico_general` | Crítico general preservado | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-SV-003 | [tests/Feature/ObjetivosSignosVitalesTest.php](../tests/Feature/ObjetivosSignosVitalesTest.php) :: `test_enfermeria_y_superadmin_no_pueden_definir_objetivos` | Competencia médica para objetivos | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-SV-004 | [tests/Feature/MisPacientesRedisenadaTest.php](../tests/Feature/MisPacientesRedisenadaTest.php) :: `test_falla_al_crear_alerta_revierte_el_signo_critico` | Rollback por fallo inducido, definición no ejecución | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-MED-002 | [tests/Feature/ModalesMedicacionSeguridadClinicaTest.php](../tests/Feature/ModalesMedicacionSeguridadClinicaTest.php) :: `test_profesional_y_fecha_se_asignan_en_backend_sin_confiar_en_frontend` | Autor/fecha backend | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-PRE-001 | [tests/Feature/CasosPreadmisionTest.php](../tests/Feature/CasosPreadmisionTest.php) :: `test_usuario_que_solo_puede_ver_no_puede_registrar_preadmision` | Lectura no crea caso | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| TEST-SV-005 | [tests/Feature/SignosVitalesPanelClasificacionTest.php](../tests/Feature/SignosVitalesPanelClasificacionTest.php) :: `test_el_panel_cuenta_y_filtra_sin_datos_sin_confundirlos_con_normales` | Sin datos distinto de normal | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |

Método existente y aserción localizada no prueba cobertura completa del módulo. Test de Superadmin puede usar flag testing. Tests SQLite/RefreshDatabase no acreditan instalación/concurrencia PostgreSQL. Ninguno se ejecutó en esta fase.

## Matriz de cobertura estática

| Módulo | Invariante o regla | EXPECTED TEST | FOUND TEST | Estado / gap |
|---|---|---|---|---|
| Ingreso institucional | INV-ADM-001: Aprobar preadmisión no crea residente | Camino positivo y negativo conforme contrato | TEST-ADM-001 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Ingreso institucional | INV-ADM-002: Formalizar ingreso es atómico | Camino positivo y negativo conforme contrato | TEST-ADM-001 | STATICALLY_MAPPED; protección PARTIAL; RUNTIME_NOT_EXECUTED |
| Ingreso institucional | INV-CON-001: Consentimiento firmado por residente XOR contacto del mismo residente | Camino positivo y negativo conforme contrato | NOT_FOUND (negativo específico en mapeo) | NOT_FOUND; protección PARTIAL; RUNTIME_NOT_EXECUTED |
| Residentes y alojamiento | INV-ALO-001: No reutilizar cama con ocupación activa | Camino positivo y negativo conforme contrato | TEST-ALO-001, TEST-ALO-002 | STATICALLY_MAPPED; protección PARTIAL; RUNTIME_NOT_EXECUTED |
| Residentes y alojamiento | INV-ALO-002: Residente no tiene dos ocupaciones activas | Camino positivo y negativo conforme contrato | TEST-ALO-001 | STATICALLY_MAPPED; protección PARTIAL; RUNTIME_NOT_EXECUTED |
| Residentes y alojamiento | INV-FAM-001: Familiar solo accede al residente vinculado/autorizado | Camino positivo y negativo conforme contrato | TEST-FAM-001 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Jornadas y asignaciones | INV-IDE-001: Usuario y personal son entidades distintas; no fabricar personal | Camino positivo y negativo conforme contrato | TEST-IDE-001 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Jornadas y asignaciones | INV-JOR-001: Asignación requiere personal/jornada reales, no fallback | Camino positivo y negativo conforme contrato | TEST-JOR-001 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Medicación | INV-MED-001: Administración pertenece a prescripción del mismo residente y horario propio | Camino positivo y negativo conforme contrato | TEST-MED-001 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Medicación | INV-MED-002: Enfermería no prescribe ni modifica orden médica | Camino positivo y negativo conforme contrato | TEST-AUTH-001 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Medicación | INV-CLI-001: Autor clínico deriva del usuario; no atribuir a otro profesional | Camino positivo y negativo conforme contrato | TEST-CLI-001, TEST-MED-002 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Signos vitales | INV-SV-001: Preview de signos no persiste ni genera alerta | Camino positivo y negativo conforme contrato | TEST-SV-001 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Signos vitales | INV-SV-002: Objetivo personalizado no rebaja crítico general | Camino positivo y negativo conforme contrato | TEST-SV-002 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Signos vitales | INV-CLI-001: Autor clínico deriva del usuario; no atribuir a otro profesional | Camino positivo y negativo conforme contrato | TEST-CLI-001, TEST-MED-002 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Signos vitales | INV-CLI-002: Evento clínico nuevo genera registro; no sobrescribir historia para ocultar error | Camino positivo y negativo conforme contrato | NOT_FOUND (universal, no existe garantía de todos los módulos) | NOT_FOUND; protección PARTIAL; RUNTIME_NOT_EXECUTED |
| Alertas | INV-ALT-001: Alerta conserva origen y trayectoria de eventos | Camino positivo y negativo conforme contrato | TEST-ALT-001 | STATICALLY_MAPPED; protección PARTIAL; RUNTIME_NOT_EXECUTED |
| Alertas | INV-SV-001: Preview de signos no persiste ni genera alerta | Camino positivo y negativo conforme contrato | TEST-SV-001 | STATICALLY_MAPPED; protección PROTECTED; RUNTIME_NOT_EXECUTED |
| Pase de turno y continuidad | INV-PAS-001: Pase solo se entrega/recibe en contexto autorizado y no se reescribe tras recepción | Camino positivo y negativo conforme contrato | TEST-PAS-001, TEST-PAS-002 | STATICALLY_MAPPED; protección PARTIAL; RUNTIME_NOT_EXECUTED |
| Pase de turno y continuidad | INV-CLI-003: Historia clínica no se borra físicamente de forma ordinaria | Camino positivo y negativo conforme contrato | TEST-PAS-003 | STATICALLY_MAPPED; protección PARTIAL; RUNTIME_NOT_EXECUTED |
| Ingreso | INV-ADM-002 | Fallo tras escritura intermedia y dos formalizaciones concurrentes | NOT_FOUND en mapeo específico | TEST GAP HIGH; TECH-005 |
| Medicación | RULE-MED-001 | Dos administraciones concurrentes y reintento PRN con identidad aprobada | NOT_FOUND en mapeo específico | TEST GAP HIGH; TECH-005; DEC-OPEN-004 |
| Signos | RULE-SV-001: medición de signos persistida | Peso/dolor aislados no producen signo con siete variables null | NOT_FOUND en mapeo específico | IMPLEMENTATION/TEST GAP HIGH; TECH-011 |
| Signos | INV-SV-001 / transaction crítica | Preview sin efectos / fallo creación alerta revierte medición | TEST-SV-001 / TEST-SV-004 | DEFINED / STATICALLY_MAPPED / RUNTIME_NOT_EXECUTED |
| Alertas | INV-ALT-001 | Evento de creación y trayectoria equivalentes Panel/Service/HTTP | TEST-ALT-001 solo HTTP; AlertasFlujoTest espera cuatro eventos sin inicial | Cobertura PARTIAL; TECH-002; no ratifica omisión de evento |
| Pase | AUTH-PAS-001 | Permiso revocado/inactivo/jornada ajena/anulación no autorizada por todas las entradas | NOT_FOUND en mapeo específico; TEST-PAS-002 cubre receptor | AUTHORIZATION/TEST GAP HIGH; TECH-009 |
| Pase / medicación / alertas | Continuidad fiel a resultado/estado y pendientes derivados | Proyección incluye administración REGISTRADA con resultado, cuidado EJECUTADA, alerta RECONOCIDA/ASIGNADA | NOT_FOUND en mapeo específico | CONTINUITY/TEST GAP HIGH; TECH-008 |
| Alojamiento / familiar | INV-FAM-001 y contenido permitido | Vínculo no publica clínica completa en HTML/JSON/export/download | TEST-FAM-001 scope, contenido completo NOT_FOUND | AUTHORIZATION/TEST GAP HIGH; TECH-001; DEC-OPEN-006 |

NOT_FOUND acota búsqueda/inspección de esta fase; no afirmación de ausencia universal de tests. No se crean tests que repitan nombres de clase ni se debilitan aserciones para obtener verde.

## Decisiones y deuda enlazadas

| ID | Significado preservado | Módulos vinculados |
|---|---|---|
| DEC-OPEN-001 | Estados institucionales / valores de ingreso | Ingreso, alojamiento, contexto dependiente |
| DEC-OPEN-002 | Catálogos clínicos/operativos pendientes | Planes, alertas, consentimientos, heridas |
| DEC-OPEN-003 | Etiquetas clínicas globales | Signos, lectura/pase; no inferir Estable por ausencia |
| DEC-OPEN-004 | PRN/reintentos / identidad de eventos | Medicación y alertas cuando aplicable |
| DEC-OPEN-005 | Metodología/derechos/validación del sistema experto | Boundary futuro, instrumentos; no aprobación de umbrales nuevos |
| DEC-OPEN-006 | Publicación clínica familiar adicional | Residente, reportes/documentación |
| TECH-001 | Contenido clínico familiar en entradas genéricas | Alojamiento/residente |
| TECH-002 | Eventos de alerta por entrada | Alertas/continuidad |
| TECH-004 | Guardia temporal Superadmin por entorno | Autorización transversal |
| TECH-005 | Garantías físicas/concurrencia por capa | Ingreso, alojamiento, medicación, pase |
| TECH-006 | Evidencia normativa/documental divergente | Ingreso/estados y contadores antiguos |
| TECH-008 | Proyección de continuidad incompatible | Pase, medicación, cuidado, alertas |
| TECH-009 | Mutaciones/contexto no equivalentes por entrada | Ingreso, pase y fronteras alternativas |
| TECH-010 | Excepciones inesperadas expuestas en mensajes | Signos e ingreso heredado |
| TECH-011 | Mínimo validado con peso/dolor que no se persiste como signo | Signos, entrada expediente/rectificación |

[Registro DEC](../docs/DECISIONES_PENDIENTES.md) no recibe IDs nuevos; [registro TECH](../docs/DEUDA_TECNICA.md) amplía evidencia existente y crea solo cuatro problemas diferenciados. TECH-003/007 siguen vigentes fuera del desarrollo de los siete contratos; no se eliminan ni duplican.

## Fuentes investigadas y entregables

Inspección de contenido/segmentos pertinentes, no solo nombres: las clases enlazadas en contratos y arquitectura, rutas web/bootstrap/providers/config, lockfiles, seed de permisos, migraciones operativas/hardening/objetivos, Models de coherencia, blade/script de entradas y cuerpos/aserciones de tests mapeados. Búsqueda de nombres fue orientación, no prueba de ejecución. Fuentes rectoras y documentos de Fase 1 leídos primero; no se repite clasificación masiva.

- [Arquitectura](../docs/arquitectura/ARQUITECTURA_VIGENTE.md); [22 dominios](../docs/sistema/MAPA_DOMINIO_FUNCIONAL.md); [7 flujos y secuencias](../docs/sistema/FLUJOS_GERIATRICOS.md).
- [Estados](../docs/sistema/ESTADOS_Y_CICLOS_DE_VIDA.md); [Autorización](../docs/seguridad/MODELO_AUTORIZACION.md); [10 roles](../docs/seguridad/MATRIZ_ROLES_PERMISOS.md).
- [20 invariantes: 11 PROTECTED / 9 PARTIAL](../docs/sistema/INVARIANTES_NEGOCIO.md); [15 fuentes longitudinales](../docs/sistema/CONTINUIDAD_ASISTENCIAL.md).
- [Plantilla de 36 secciones](../docs/modulos/_PLANTILLA_CONTRATO_MODULO.md) y siete contratos enlazados en la matriz.

16 archivos nuevos; mapa existente consolidado. Integración mínima en portal, arquitectura/README como índice hacia descripción ampliada, estado, changelog y deuda. No se cambia decisiones, estructura BDD ni código funcional. Manifest de archivos nuevos corresponde a los 16 enlaces/documentos de entrega; modificaciones documentales listadas en CHANGELOG.

## Verificación de cierre Fase 2 — histórica

**Comprobaciones locales — PASS estático (2026-10-06):** 109 Markdown en docs; 815 enlaces locales fuera de bloques de código y dos anchors comprobados sin destinos ausentes. Los 16 documentos nuevos tienen metadatos exigidos, STATIC_REPOSITORY_REVIEW y runtime_verified=false; plantilla y siete contratos contienen las 36 secciones. Los 22 localizadores archivo/método TEST existen. Portal sin encabezados/párrafos largos duplicados; IDs DEC conservados 001..006. Conteos excluyen URLs externas y no certifican su disponibilidad.

**Preservación — PASS:** huellas de 1.144 rutas no Markdown (incluidas cinco ausencias previas) comparadas con snapshot anterior a Fase 2, sin cambios; HEAD permanece 8e9e20325519c5da5a6b25f6fb26568cad78efe1, índice Git sin staging. No commit/push. Ninguna suite runtime ni build ejecutados.

Caveman Cloud no tiene herramienta/CLI disponible en esta sesión; no se generan costes/trazas/Cave Score ficticios. Caveman-evidence-review se limita aquí al contraste local verificable, no reporte Cloud. El primer revisor local encontró imprecisiones de diagrama/transacción/localizador que se corrigieron y agotó cuota antes de cerrar.

**Caveman-review independiente final — PASS documental estático.** El revisor f2_final_review leyó los 16 entregables nuevos, mapa consolidado y siete documentos de integración; contrastó código sensible, versiones y aserciones de los 22 tests mapeados. Cuatro ajustes finales se corrigieron y revalidaron: receptor opcional por entrada de pase, normalización de fecha/hora por actor, validación laboral HTTP distinta del panel/contexto y mínimo de signos distinto de persistencia en expediente (TECH-011). Sin hallazgos documentales pendientes. No ejecutó aplicación/tests/migraciones/seed/build ni modificó archivos.

Fase 2 documental cerrada con PASS. Los gaps técnicos permanecen OPEN; producto y runtime no se certifican. Ningún PASS de esta sección significa que una suite funcional pasó.

## Fase 3 — evidencia runtime ejecutada y acotada

Base: `8e9e20325519c5da5a6b25f6fb26568cad78efe1`, rama `REINICIO`, árbol de trabajo sin commit con cambios previos preservados. Los metadatos estáticos y resultados de Fases 1/2 anteriores conservan su alcance histórico; esta sección registra ejecuciones nuevas y no certifica producción ni todos los módulos.

Entorno comprobado antes del reset: `APP_ENV=testing`, PHP 8.4.24, PostgreSQL 18.4 en `127.0.0.1:5432`, base exclusiva `remembermind_f3_test_ffe68375`, inicialmente cero tablas. Usuario exclusivo sin SUPERUSER/CREATEDB/CREATEROLE; conexión pública revocada. Configuración de caché, sesión y correo en array, cola sync, configuración cacheada aislada. `.env` no modificado; credenciales de prueba fuera del repositorio. SQLite `:memory:` se usa únicamente para regresiones rápidas; no acredita bloqueos PostgreSQL.

| Comando / alcance | Motor | Exit | Duración | Resultado |
|---|---|---:|---:|---|
| `php artisan about --only=environment` | PostgreSQL | 0 | 1,68 s | PASS; entorno testing |
| `php artisan migrate:status` antes de instalar | PostgreSQL | 1 | 0,52 s | Esperado: base vacía sin tabla de migraciones |
| `php artisan migrate:fresh --seed --force` | PostgreSQL | 0 | 3,87 s | PASS en base nueva desechable confirmada |
| `php vendor/phpunit/phpunit/phpunit --no-progress --colors=never` inicial | PostgreSQL | 2 | 784,06 s | FAIL previo: 613 tests, 11.765 aserciones, 14 fallos, 1 error, 13 omitidos |
| `npm run build` inicial | N/A | 0 | 7,18 s | PASS; aviso de tiempos de plugins Vite/Rolldown |
| PHPUnit `--filter 'Fase3SeguridadNucleoTest\|PaseTurnoReconstruidoTest\|BddOperativaV2Test\|RolesBaselineCongeladoTest\|AccesoClinicoTemporalSuperadminTest\|SeguridadCriticaEnfermeriaTest'` | PostgreSQL | 0 | 92,81 s PHPUnit | PASS: 68 tests, 447 aserciones |
| PHPUnit `--filter 'PaseTurnoReconstruidoTest\|Fase3SignosVitalesTest::test_excepcion_tecnica'` después de revisión A | SQLite | 0 | 2,69 s PHPUnit | PASS: 21 tests, 84 aserciones; no prueba concurrencia |
| PHPUnit casos nuevos A, negativos de turno/residente y error técnico | PostgreSQL | 1 | 23,70 s | 15 tests; 14 PASS, 1 fallo de fixture por nombre de operación incorrecto; corregido sin cambiar producto |
| PHPUnit `--filter 'Fase3SeguridadNucleoTest::test_administrador_no_crea'` | PostgreSQL | 0 | 3,40 s | PASS: endpoint `diagnostico`, denegación 403 y cero diagnósticos |

Regresiones A reproducidas antes de corregir: clínica serializada a Familiar vinculado, PDF no publicado accesible, contacto inactivo aceptado, flag clínico Superadmin válido en production, Action de admisión sin guardia, permiso de lectura del pase suficiente para crear, asignación de otra jornada, receptor inactivo y anulación ajena. La revisión independiente detectó además residente inactivo y turno no vigente; ambos casos dieron RED antes de corregir y GREEN después. No se acredita como regresión el primer fixture de lectura ajena que en realidad tenía asignación; se sustituyó por un residente realmente ajeno.

Casos de autorización adicionales ejecutados en PostgreSQL: médico prescribe y autor enviado se ignora; Enfermería con grants técnicos no crea ni edita prescripción; Administrador con grant no registra diagnóstico; Superadmin sin excepción no prescribe; Familiar propio recibe solo identidad permitida, ajeno denegado, rutas clínicas/globales/documentos no publicados bloqueados. Error técnico sintético HTTP: reportado internamente, sin SQLSTATE, ruta privada ni credencial sintética en mensaje público; sin signo persistido.

Baseline inicial: `ObjetivosSignosVitalesTest::test_base_rechaza_dos_objetivos_vigentes_para_misma_variable` es TEST INFRASTRUCTURE: tras violar UNIQUE consulta dentro de una transacción PostgreSQL abortada. Los otros fallos previos corresponden a expectativas de UI/layout/texto y dos rutas de reportes/legacy; se conservarán para comparar el cierre. No se rediseña UX ni se debilitan invariantes para obtener verde. Logs completos de ejecución conservados fuera del repositorio durante la tarea; los resultados relevantes se consolidan aquí.

### Lote B — TECH-011 / REQ-SV-001 / INV-CLI-002

Raíz: Request y validarYNormalizar contaban peso/dolor, aunque registrar persiste únicamente siete variables de signos. Corrección: mínimo sobre esas siete mediciones, conservando nullable y el par de PA. Implementación: StoreSignosVitalesRequest y SignosVitalesService. Pruebas: Fase3SignosVitalesTest (13 casos) y SignosVitalesPanelClasificacionTest (1 caso).

`B-red-corrected.log`: SQLite, exit 1, 13 tests / 35 assertions / 6 failures reales. `B-green-pg.log`: PostgreSQL, exit 0, 22.013 s, 14 tests / 59 assertions PASS. El gate ampliado `B-gate-pg.log` ejecutó 22 tests / 89 assertions con 1 error preexistente de infraestructura en ObjetivosSignosVitalesTest (SQLSTATE 25P02 tras UNIQUE intencional); su savepoint se corrigió en lote E, conservando la violación UNIQUE intencional. Peso/dolor aislados no generan registros; temperatura/glucemia/PA/varias mediciones sí. Falla de evento crítico revierte signo y alerta conservando historia. Commit futuro: pendiente.

Lotes A–E tienen evidencia dirigida; resultados de la suite global y revalidación Familiar se detallan en el cierre final. DEC-OPEN-001..006 permanecen OPEN. Sin cambios estructurales en BDD, commit ni push.

### Lote C — TECH-002 / RULE-ALT-001 / INV-ALT-001 / AUTH-ALT-001

Implementación: AlertasService concentra estados/eventos de Panel y HTTP; DeteccionAlertasService agrega evento inicial atómico con actor autenticado y scope. SaludAlertasPanel deja de persistir al leer; HistorialAlerta bloquea ID y reautoriza historial; HTTP index y pendientes limitan lectura Enfermería. Mantiene coordinación Admin según permisos y límites clínicos de Enfermería. No cambia catálogos, cierre directo crítico ni estructura.

`C-red-final.log`: SQLite exit 2, 7 tests / 13 aserciones, 2 errores de evento inexistente y 5 fallos reales. `C-green-sqlite.log`: exit 0, 17 tests / 86 aserciones. `C-green-pg.log`: exit 0, 38.614 s, 24 tests / 109 aserciones PASS; incluye rollback de creación/cambio, historia inmutable, HTTP scope y cierre crítico directo. Gate ampliado dejó visible el fallo de texto de timeline previo; fixture sin rol ahora debe denegarse al montar, se corrigió su expectativa de entrada sin debilitar autorización. Sin commit.

### Lote D — TECH-008 / FLOW-PAS-001 / INV-MED-001 / INV-ALT-001

Implementación: PaseTurnoService; reutiliza AgendaMedicacionService. Resultado y estado se leen por separado; omisión explícita permanece un hecho, mientras ausencia produce únicamente proyección de agenda. Incluye ambos estados reales de cuidado completado, pendientes previos y alertas reconocidas/asignadas. No inserta tareas ni copia el expediente.

`D-red-final.log`: SQLite exit 1, 2 tests / 2 aserciones / 2 fallos. `D-green-sqlite.log`: exit 0, 23 tests / 96 aserciones. `D-gate-pg.log`: exit 0, 13.310 s, 46 tests / 203 aserciones PASS. Métodos test_continuidad_* verifican lectura después del escritor real de medicación y ausencia de nuevas filas; gate conserva agenda/MiTurno/pase. Generar pase exige receptor activo y ARJ exacta entrante, manteniendo GENERADO. Sin cambios de estructura ni decisiones abiertas. Sin commit.

### Lote E — TECH-005 / RULE-ADM-001 / INV-ADM-002 / INV-ALO-001 / INV-CON-001 / INV-CLI-003

Fase3AdmisionAtomicidadTest inyecta fallo en nueve puntos (contacto, residente, admisión, vínculo, ocupación, historial, consentimiento, seguro y Activity) y compara todas sus tablas antes/después. Prueba contacto ajeno rechazado por Model y borrado ordinario de signo rechazado conservando snapshot persistido. No certifica SQL directo de consentimiento ni una prohibición universal de UPDATE/DELETE por Query Builder.

Fase3ConcurrenciaCamaPostgresTest usa DatabaseMigrations únicamente tras comprobar APP_ENV=testing, nombre exclusivo y rol dedicado. Dos procesos PHP independientes llegan a barrera TCP local; un tercer PDO retiene lock de cama hasta observar ambos PIDs PostgreSQL esperando locks. Libera la fila y comprueba una admisión, una denegación por cod_cama, una ocupación y ninguna operación parcial. No usa sleep ni simulación secuencial; sin DDL nuevo.

`E-gate-pg.log`: 18 tests / 164 aserciones, exit 1 por conteo fixture de auditoría anterior. `E-green-pg.log`: exit 1, 91.209 s, 44 tests / 409 aserciones; carrera PASS con PIDs 35272/12512, waiting_locks=2, RECHAZADA/ADMITIDA, 1 residente/1 ocupación. Único fallo: comparación del atributo decimal en memoria frente a hidratación PostgreSQL, no destrucción de historia. Se corrigió snapshot persistido antes de delete. `E-correcciones-pg.log`: exit 0, 19.580 s, 12 tests / 126 aserciones PASS, incluidos 9 fallos inducidos y prueba de nombre de medicamento. No se presenta el run de 44 como PASS global.

ObjetivosSignosVitalesTest conserva violación UNIQUE intencional dentro de transaction/savepoint para que PostgreSQL pueda consultar luego. No modifica índice/validación ni debilita expectativa. E acredita sus 8 casos, incluida protección del crítico general.

Revisión independiente D detectó medicamento Model impreso en Blade: D-label-red.log (SQLite exit 1) reprodujo el problema; proyección ahora string nombre_generico con eager load, tres aserciones aprobadas en E-correcciones-pg. Sin rediseño UI.

### Herramientas y límites Fase 3

Lint PHP de archivos tocados: 0 errores. Pint aplicado solo a los seis nuevos archivos PHP de pruebas, seguido de --test: exit 0; no formatea masivamente trabajo previo. Análisis estático adicional: no herramienta instalada aplicable localizada. Build final: npm run build, exit 0, 4.82 s; warning de tiempos de callbacks Vite/Rolldown conservado. npm test: exit 1, 10.96 s, timeout 6000 ms en sidebar-responsive.test.js:72; no archivos JS/CSS ni sidebar modificados por Fase 3, clasificado UNRELATED / TEST INFRASTRUCTURE sin afirmar baseline JS ejecutado. Suite PHP final ejecutada con FAIL global; detalle y separación del baseline en la sección siguiente.

Caveman Cloud: MCP/CLI disponibles no incluyen capacidades de Cloud; no hay traces/costs/ledger. caveman-evidence-review Cloud NOT VERIFIED, no sustituido por inspección local ni métricas fabricadas. Auditoría local de afirmaciones conserva comandos, exit codes, duraciones, pruebas y DB; revisión independiente read-only de código dio PASS tras corregir proyección D, con resultados finales y alcance documentados; el dictamen independiente de cierre se registra abajo.


### Inventario exclusivo de cambios Fase 3

Comparación SHA-256 con el snapshot anterior a Fase 3, excluyendo cambios previos e ignorados: 61 archivos (33 app, 1 Blade, 12 tests y 15 documentos existentes). Ningún documento nuevo. Seis archivos PHP nuevos de pruebas; los demás ya existían al inicio. Este inventario no atribuye a Fase 3 todas las diferencias contra HEAD.

- [app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php](../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php)
- [app/Backend/Modulos/Alertas/Servicios/AlertasService.php](../app/Backend/Modulos/Alertas/Servicios/AlertasService.php)
- [app/Backend/Modulos/Alertas/Servicios/DeteccionAlertasService.php](../app/Backend/Modulos/Alertas/Servicios/DeteccionAlertasService.php)
- [app/Backend/Modulos/Clinica/Servicios/AccesoClinicoTemporalService.php](../app/Backend/Modulos/Clinica/Servicios/AccesoClinicoTemporalService.php)
- [app/Backend/Modulos/Clinica/Servicios/SignosVitalesService.php](../app/Backend/Modulos/Clinica/Servicios/SignosVitalesService.php)
- [app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php](../app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php)
- [app/Frontend/Livewire/Admisiones/DecisionAdmisionModal.php](../app/Frontend/Livewire/Admisiones/DecisionAdmisionModal.php)
- [app/Frontend/Livewire/Admisiones/PreadmisionWizard.php](../app/Frontend/Livewire/Admisiones/PreadmisionWizard.php)
- [app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php](../app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php)
- [app/Frontend/Livewire/Compartido/Alertas/AlertasPanel.php](../app/Frontend/Livewire/Compartido/Alertas/AlertasPanel.php)
- [app/Frontend/Livewire/Compartido/Alertas/AlertasPendientesPanel.php](../app/Frontend/Livewire/Compartido/Alertas/AlertasPendientesPanel.php)
- [app/Frontend/Livewire/Compartido/Alertas/SaludAlertasPanel.php](../app/Frontend/Livewire/Compartido/Alertas/SaludAlertasPanel.php)
- [app/Frontend/Livewire/Compartido/Clinica/RegistroSignosVitalesModal.php](../app/Frontend/Livewire/Compartido/Clinica/RegistroSignosVitalesModal.php)
- [app/Frontend/Livewire/Compartido/Clinica/SaludSignosPanel.php](../app/Frontend/Livewire/Compartido/Clinica/SaludSignosPanel.php)
- [app/Frontend/Livewire/Compartido/Residentes/RedApoyoPanel.php](../app/Frontend/Livewire/Compartido/Residentes/RedApoyoPanel.php)
- [app/Frontend/Livewire/Enfermeria/Cuidados/PaseTurnoPanel.php](../app/Frontend/Livewire/Enfermeria/Cuidados/PaseTurnoPanel.php)
- [app/Frontend/Livewire/Enfermeria/Medicacion/SaludAdministracionMedicacionPanel.php](../app/Frontend/Livewire/Enfermeria/Medicacion/SaludAdministracionMedicacionPanel.php)
- [app/Frontend/Livewire/Features/Alertas/HistorialAlerta.php](../app/Frontend/Livewire/Features/Alertas/HistorialAlerta.php)
- [app/Http/Controllers/Alertas/AlertaController.php](../app/Http/Controllers/Alertas/AlertaController.php)
- [app/Http/Controllers/Clinica/AdultoMayorSignosVitalesController.php](../app/Http/Controllers/Clinica/AdultoMayorSignosVitalesController.php)
- [app/Http/Controllers/Cuidados/CuidadoController.php](../app/Http/Controllers/Cuidados/CuidadoController.php)
- [app/Http/Controllers/Documentos/AdultoMayorDocumentoController.php](../app/Http/Controllers/Documentos/AdultoMayorDocumentoController.php)
- [app/Http/Controllers/Documentos/DocumentoController.php](../app/Http/Controllers/Documentos/DocumentoController.php)
- [app/Http/Controllers/Identidad/InstitucionalController.php](../app/Http/Controllers/Identidad/InstitucionalController.php)
- [app/Http/Controllers/Medicacion/AdultoMayorAdministracionMedicacionController.php](../app/Http/Controllers/Medicacion/AdultoMayorAdministracionMedicacionController.php)
- [app/Http/Controllers/Medicacion/AdultoMayorMedicacionController.php](../app/Http/Controllers/Medicacion/AdultoMayorMedicacionController.php)
- [app/Http/Controllers/Reportes/ReporteV2Controller.php](../app/Http/Controllers/Reportes/ReporteV2Controller.php)
- [app/Http/Controllers/Residentes/AdultoMayorController.php](../app/Http/Controllers/Residentes/AdultoMayorController.php)
- [app/Http/Controllers/Residentes/RelacionResidenteController.php](../app/Http/Controllers/Residentes/RelacionResidenteController.php)
- [app/Http/Controllers/Residentes/ResidenteController.php](../app/Http/Controllers/Residentes/ResidenteController.php)
- [app/Http/Controllers/Residentes/ResumenFamiliaSocialController.php](../app/Http/Controllers/Residentes/ResumenFamiliaSocialController.php)
- [app/Http/Requests/Clinica/StoreSignosVitalesRequest.php](../app/Http/Requests/Clinica/StoreSignosVitalesRequest.php)
- [app/Policies/ResidentePolicy.php](../app/Policies/ResidentePolicy.php)
- [docs/DEUDA_TECNICA.md](../docs/DEUDA_TECNICA.md)
- [docs/ESTADO_ACTUAL.md](../docs/ESTADO_ACTUAL.md)
- [docs/ROADMAP.md](../docs/ROADMAP.md)
- [docs/TRAZABILIDAD.md](../docs/TRAZABILIDAD.md)
- [docs/modulos/alertas/README.md](../docs/modulos/alertas/README.md)
- [docs/modulos/ingreso-institucional/README.md](../docs/modulos/ingreso-institucional/README.md)
- [docs/modulos/jornadas-asignaciones/README.md](../docs/modulos/jornadas-asignaciones/README.md)
- [docs/modulos/medicacion/README.md](../docs/modulos/medicacion/README.md)
- [docs/modulos/pase-turno/README.md](../docs/modulos/pase-turno/README.md)
- [docs/modulos/residentes-alojamiento/README.md](../docs/modulos/residentes-alojamiento/README.md)
- [docs/modulos/signos-vitales/README.md](../docs/modulos/signos-vitales/README.md)
- [docs/seguridad/MATRIZ_ROLES_PERMISOS.md](../docs/seguridad/MATRIZ_ROLES_PERMISOS.md)
- [docs/seguridad/MODELO_AUTORIZACION.md](../docs/seguridad/MODELO_AUTORIZACION.md)
- [docs/sistema/CONTINUIDAD_ASISTENCIAL.md](../docs/sistema/CONTINUIDAD_ASISTENCIAL.md)
- [docs/sistema/INVARIANTES_NEGOCIO.md](../docs/sistema/INVARIANTES_NEGOCIO.md)
- [resources/views/pages/residentes/show.blade.php](../resources/views/pages/residentes/show.blade.php)
- [tests/Feature/AlertasFlujoTest.php](../tests/Feature/AlertasFlujoTest.php)
- [tests/Feature/AlertasHistorialFeatureTest.php](../tests/Feature/AlertasHistorialFeatureTest.php)
- [tests/Feature/BddOperativaV2Test.php](../tests/Feature/BddOperativaV2Test.php)
- [tests/Feature/Fase3AdmisionAtomicidadTest.php](../tests/Feature/Fase3AdmisionAtomicidadTest.php)
- [tests/Feature/Fase3AlertasTest.php](../tests/Feature/Fase3AlertasTest.php)
- [tests/Feature/Fase3ConcurrenciaCamaPostgresTest.php](../tests/Feature/Fase3ConcurrenciaCamaPostgresTest.php)
- [tests/Feature/Fase3SeguridadNucleoTest.php](../tests/Feature/Fase3SeguridadNucleoTest.php)
- [tests/Feature/Fase3SignosVitalesTest.php](../tests/Feature/Fase3SignosVitalesTest.php)
- [tests/Feature/ObjetivosSignosVitalesTest.php](../tests/Feature/ObjetivosSignosVitalesTest.php)
- [tests/Feature/PaseTurnoReconstruidoTest.php](../tests/Feature/PaseTurnoReconstruidoTest.php)
- [tests/Feature/VisibilidadNavegacionTest.php](../tests/Feature/VisibilidadNavegacionTest.php)
- [tests/Support/fase3_admision_worker.php](../tests/Support/fase3_admision_worker.php)


### Cierre runtime final Fase 3

| Comando / alcance | Exit | Duración | Resultado exacto |
|---|---:|---:|---|
| `php vendor/phpunit/phpunit/phpunit --no-progress --colors=never --log-junit <temporal>/final-suite.xml` | 1 | 1711,34 s reloj; 1709,55 s PHPUnit | FAIL global: 670 tests, 12.068 aserciones, 0 errores, 15 fallos, 13 omitidos |
| Cohorte dentro de la suite: cinco clases Fase3 y PaseTurnoReconstruidoTest | Incluida arriba | Incluida arriba | PASS acotado: 70 tests, 374 aserciones; ninguna omisión de esta cohorte |
| PHPUnit `--filter 'VisibilidadNavegacionTest|Fase3SeguridadNucleoTest'` después de alinear expectativa Familiar | 0 | 40,46 s reloj; 39,725 s PHPUnit | PASS: 22 tests, 91 aserciones |
| Helper temporal guardado que ejecuta `Artisan::call('migrate:fresh', ['--seed'=>true, '--force'=>true])` | 0 | 2,71 s | PASS; testing/pgsql/base y rol efectivos comprobados antes del reset; 71/71 tablas operativas instaladas, missing=[] |
| `npm run build` | 0 | 4,82 s | PASS; warning PLUGIN_TIMINGS de callbacks Vite/Rolldown |
| `npm test` | 1 | 10,96 s | FAIL: timeout Puppeteer 6000 ms en sidebar-responsive.test.js:72; UNRELATED / TEST INFRASTRUCTURE |

Comparación de identidades de test contra baseline: los catorce fallos iniciales permanecen; el error inicial de ObjetivosSignosVitalesTest desapareció tras savepoint. Un fallo adicional corresponde a **CONTRACT VIOLATION / TEST INFRASTRUCTURE**: VisibilidadNavegacionTest esperaba ficha heredada 200 y listado documental Familiar 200. El contrato vigente no publica clínica/documentos del residente al Familiar; la entrada heredada redirige a proyección segura. Se alineó únicamente esa prueba: redirect exacto, GET de identidad con 200 y nombre, negativos de clínica/otros contactos/JSON/PDF, documentos propios y ajenos 403. La clase completa y los doce casos de seguridad nuevos pasan en el run posterior 22/91. No se modificó código funcional después de la suite final; no se repitió toda la suite tras este ajuste exclusivo de test. No presentar como ejecución global de 14 fallos: la ejecución completa registrada tuvo 15.

Los trece omitidos son los mismos alcances Jetstream/Fortify inhabilitados del baseline: API (3), eliminación de cuenta (2), verificación de email (3), registro público (2) y 2FA (3). No se añadieron skips para lograr verde; la carrera PostgreSQL se ejecutó y pasó. Fuente: XML de PHPUnit y condiciones existentes de markTestSkipped.

#### Fallos preexistentes que permanecen

- UNRELATED (UI/texto/estilo): [Tests.Feature.AdministracionMedicacionEnfermeriaTest::test_vista_medicacion_contiene_cabecera_exacta_y_resumen_compacto](../tests/Feature/AdministracionMedicacionEnfermeriaTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.AlertasHistorialFeatureTest::test_usuario_con_permiso_ve_estado_actual_y_timeline_cronologico](../tests/Feature/AlertasHistorialFeatureTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.Architecture.FrontendArchitectureTest::test_graficas_del_sistema_comparten_tema_tokens_y_contenedor_canonico](../tests/Feature/Architecture/FrontendArchitectureTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.Architecture.FrontendArchitectureTest::test_layouts_y_overlays_comparten_profundidad_y_validacion_canonicas](../tests/Feature/Architecture/FrontendArchitectureTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.AreasAtencionSuperadminTest::test_superadmin_puede_acceder_al_centro_de_areas_de_atencion](../tests/Feature/AreasAtencionSuperadminTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.AuthenticationTest::test_login_screen_can_be_rendered](../tests/Feature/AuthenticationTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.FiltrosDisenoUnificadoTest::test_el_estilo_canonico_usa_tokens_semanticos_y_no_colores_fijos](../tests/Feature/FiltrosDisenoUnificadoTest.php).
- LEGACY / UNRELATED: [Tests.Feature.FrontendRestauradoV2Test::test_pantallas_admin_sin_parametros_no_fallan_por_dependencias_legacy](../tests/Feature/FrontendRestauradoV2Test.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.ModalesMedicacionSeguridadClinicaTest::test_modal_administrar_se_abre_centrado_con_datos_bloqueados_y_5_correctos](../tests/Feature/ModalesMedicacionSeguridadClinicaTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.ModalesMedicacionSeguridadClinicaTest::test_tabla_abre_drawer_y_drawer_abre_modales_emergentes](../tests/Feature/ModalesMedicacionSeguridadClinicaTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.OverlayComponentsTest::test_drawer_conserva_foco_y_cierre_explicito](../tests/Feature/OverlayComponentsTest.php).
- UNRELATED: [Tests.Feature.PantallasConectadasTest::test_paginas_conectadas_renderizan_con_un_residente](../tests/Feature/PantallasConectadasTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.RolePreviewTest::test_preview_cambia_sidebar_y_dashboard_y_muestra_banner_permanente](../tests/Feature/RolePreviewTest.php).
- UNRELATED (UI/texto/estilo): [Tests.Feature.SuperadminDashboardHubTest::test_dashboard_superadmin_muestra_supervision_global](../tests/Feature/SuperadminDashboardHubTest.php).

Los dos fallos de rutas/reportes ya existían antes de Fase 3; uno declara dependencias legacy y el otro HTTP 500 en reporte institucional. Los demás son expectativas de texto/layout/estilo. No se atribuyen a estos cambios, no se debilitan sus aserciones ni se amplía el alcance a rediseño. No es certificación de que todo el producto carezca de regresiones: la evidencia favorable se limita a los gates y casos ejecutados.

Logs y XML completos fuera del repositorio durante esta tarea: `C:/Users/CARLAE~1/AppData/Local/Temp/remembermind-f3-logs/` (baseline-suite.log, final-suite.log/xml, final-comparison.json, final-family-contract.log, final-migrate-seed.log, final-build.log, final-js.log, gates RED/GREEN y final-links.json). Las credenciales aisladas no forman parte de esta documentación.

Preservación: 160 rutas protegidas comparadas por SHA-256 contra snapshot inicial; 0 cambios Fase 3 en estructura/migraciones/factories BDD, docs/base-de-datos, DECISIONES_PENDIENTES o skills. HEAD base sin cambios, índice sin staging, sin commit/push. Sin documentos nuevos ni reglas clínicas/competencias/DEC nuevas. `.env` conservado.

Enlaces documentales finales: 109 Markdown, 898 enlaces locales, 0 rotos; dos anchors verificados. `git diff --check` acotado: exit 0, sin errores de whitespace.

Evidencia local: PASS para afirmaciones acotadas de implementación, comandos, tests y base instalada; FAIL del gate global conservado. Caveman Cloud NOT VERIFIED por capacidades ausentes. Caveman-review independiente final: PASS acotado de código, tests, documentación y evidencia local, sin hallazgos accionables pendientes. El revisor contrastó archivos y logs, no ejecutó pruebas ni operaciones BDD. Confirma cohorte 70/374, revalidación 22/91 y reset/seed 71/71; conserva FAIL global PHP/JS y Cloud NOT VERIFIED. Nunca sustituye suite funcional ni certifica producción.


## Fase 4 — precheck histórico bloqueado por entorno (2026-10-06)

La propietaria autorizó estabilización global, con baseline PHP completo obligatorio antes de editar código. En este precheck la fase quedó **INCOMPLETE / BLOCKED_RUNTIME_ENVIRONMENT**. Este apartado conserva evidencia histórica; el resultado posterior a «habilita php» se registra en la siguiente sección. Los resultados de Fase 3 no se reinterpretan ni se presentan como nueva ejecución.

Precheck: HEAD 8e9e20325519c5da5a6b25f6fb26568cad78efe1, rama REINICIO y cambios previos presentes. Snapshot de 1321 rutas, incluido hash de .env, y diff inicial guardados fuera del repo. Antes de registrar esta sección, 0 cambios Fase 4 en el repositorio. No staging, commit ni push.

Conexión exclusiva confirmada: APP_ENV=testing, pgsql, 127.0.0.1:5432, base remembermind_f3_test_ffe68375, rol remembermind_f3_test_ffe68375_runner, PostgreSQL 18.4. Introspección 71/71 tablas operativas presentes. No reset/seed ejecutados en Fase 4.

| Ejecución | Exit | Duración | Resultado |
|---|---:|---:|---|
| `php vendor/phpunit/phpunit/phpunit --testdox --colors=never --display-skipped --log-junit <temp>/baseline-php.xml` | 1 | 1,02 s | Arranque bloqueado; 0 tests ejecutados. Windows Control de aplicaciones bloquea php_mbstring.dll de PHP 8.4.24; PHPUnit exige mbstring nativo |
| `composer check-platform-reqs --no-interaction`, PHP alternativo ya instalado 8.5.9 | 1 | No medida | Alternativa descartada: phpoffice/phpspreadsheet instalado exige PHP >=7.4.0 y <8.5.0. No se alteran lockfiles ni se ignora el requisito |
| `npm test` | 1 | 13,37 s reloj; 10,735 s Node | 30 tests, 28 PASS, 2 FAIL, 0 skipped |
| `npm run build` | 0 | 4,52 s reloj; 3,68 s Vite | PASS; warning PLUGIN_TIMINGS conservado |
| `node --test --test-name-pattern='sidebar adaptativo' tests/Frontend/sidebar-responsive.test.js`, cinco repeticiones secuenciales tras build | 1 en las cinco | 8,71 / 8,63 / 8,67 / 8,63 / 8,63 s | Mismo timeout; no evidencia de flaky en estas cinco ejecuciones |

Inventario temporal: ENV-001 bloqueo Windows/mbstring; ENV-002 runtime alternativo incompatible; JS-001 shell-visual-consistency.test.js:85, icono sol visible en tema claro a 320px; JS-002 sidebar-responsive.test.js:72, timeout de condición left===0 al abrir drawer móvil. Los dos primeros tienen causa de entorno comprobada. Las causas de los fallos JS siguen UNKNOWN; timeout por sí solo no acredita timing issue ni autoriza aumentar timeout. No se corrige código antes de obtener el baseline PHP requerido.

Durante este precheck los catorce fallos PHP previos y trece omitidos de Fase 3 quedaron pendientes de reproducción Fase 4, no contabilizados como nuevos resultados. Ubuntu existente no tenía PHP; su candidato php-cli era 8.5, igualmente incompatible. Docker CLI existía, pero el daemon no estaba disponible. No se instaló PHP ni se desactivó política de seguridad en ese momento. Se solicitó autorización para instalación oficial de PHP 8.3/8.4 o ruta/restauración de runtime aprobado; la respuesta posterior «habilita php» permitió continuar. Matriz, snapshots y logs en C:/Users/CARLAE~1/AppData/Local/Temp/remembermind-f4-logs/.

En ese precheck TECH-005/009/010 conservaron PARTIAL; TECH-003/006/007 OPEN; DEC-OPEN-001..006 sin cambios. Todavía no se habían realizado reparaciones de producto Fase 4, nuevas pruebas, reglas, permisos, schema o dependencias. Cloud quedó NOT VERIFIED: MCP/CLI Caveman no disponibles. La revisión final del producto estaba pendiente y aún no se cumplía Definition of Done ni PASS global; la ejecución posterior se registra abajo.


Revisión independiente read-only del precheck Fase 4: **PASS acotado**, sin hallazgos accionables. Contrastó logs de PHP/JS/build/repeticiones, requisito del PhpSpreadsheet, guardia DB, alternativas de entorno y snapshot de 1321 rutas. Confirmó solo tres documentos modificados, sin código/BDD/contratos/skills/DEC/.env/staging/commit. La revisión de producto y el gate global Fase 4 aún estaban **NO EJECUTADOS**; ese dictamen no fue cierre de Fase 4. Inventario temporal de los trece skips históricos con motivo/justificación/temporalidad/dependencia/riesgo: historical-skips-inventory.json en logs F4; todavía no se había ejecutado PHP.

## Fase 4 — estabilización y regresión (2026-10-06)

La propietaria autorizó «habilita php». Winget verificó descarga oficial de PHP 8.4.25, pero Windows siguió bloqueando su ejecutable; no se alteró Control de aplicaciones. Se instaló PHP 8.3.33 ZTS x64 oficial en paralelo, se conservaron extensiones requeridas y se antepuso su carpeta al PATH de usuario. Las terminales ya abiertas deben reiniciarse. PHP nativo (mbstring, DOM, XMLWriter, PDO PostgreSQL/SQLite) y composer check-platform-reqs PASS. Ningún lockfile, .env ni dependencia del repositorio cambió. SHA-256 del paquete 8.3.33: b089e370ff99eb7038b0d22617dec2f3a1d0e93ca26b11fd218f2f5b60422271.

Los resultados del precheck anterior son históricos. Tras habilitar PHP se ejecutó toda la baseline antes de cambiar código: PHP 8.3.33, PHPUnit 12.5.36, PostgreSQL 18.4, APP_ENV=testing, 127.0.0.1:5432, base remembermind_f3_test_ffe68375 y rol exclusivo remembermind_f3_test_ffe68375_runner. La guardia compara current_database/current_user y el nombre desechable antes de cualquier reset. Datos sintéticos; credenciales fuera del repo.

### Baseline F4 y matriz de fallos

| Comando | Exit | Tiempo | Resultado |
|---|---:|---:|---|
| php vendor/phpunit/phpunit/phpunit --no-progress --colors=never --display-skipped --log-junit temporal/baseline-php-enabled.xml | 1 | 932,60 s reloj / 931,447 s XML | 670 tests, 12.082 aserciones, 14 fallos, 0 errores, 13 omitidos |
| npm test | 1 | 9,323 s reloj | 30 tests, 28 PASS, 2 FAIL, 0 omitidos |
| npm run build | 0 | 2,693 s reloj / 2,27 s Vite | PASS |

Los catorce fallos PHP coinciden con los preexistentes de la baseline, no son regresiones introducidas en Fase 3. Las matrices temporales php-failure-matrix-current.json y js-failure-matrix-current.json registran ID/suite/test/mensaje/módulo/contrato/causa/tipo/preexistencia/F3/acción. Se comprobó contrato y causa antes de reparar cada caso; no hubo ajuste masivo de catorce tests ni nuevos skips.

| ID | Caso / causa | Clasificación y corrección |
|---|---|---|
| PHP-001 | AdministracionMedicacionEnfermeriaTest, resumen esperaba alergia sin fila | TEST INFRASTRUCTURE: fixture con alergia sintética real y caso sin alergia que no inventa alerta |
| PHP-002 | AlertasHistorialFeatureTest, Por Atender frente al estado actual | OBSOLETE TEST EXPECTATION: verifica estado ABIERTA y texto Abierta existente; no define etiqueta clínica ni cambia DEC |
| PHP-003 | FrontendArchitectureTest, detalle readonly exigía modal | OBSOLETE TEST EXPECTATION: verifica drawer canónico existente; conserva profundidad/validación |
| PHP-004 | FrontendArchitectureTest, gráfico de alertas sin contenedor canónico | REAL PRODUCT BUG: conecta rm-chart-card en el contenedor real, sin nuevas series/reglas |
| PHP-005 | AreasAtencionSuperadminTest, uppercase CSS confundido con texto HTML | OBSOLETE TEST EXPECTATION: capitalización del contenido vigente |
| PHP-006 | AuthenticationTest, exigía bundle sin minificar | OBSOLETE TEST EXPECTATION: admite livewire.js/livewire.min.js y conserva rm_ui e id obligatorios |
| PHP-007 | FiltrosDisenoUnificadoTest, HEX locales | REAL PRODUCT BUG: 54 valores existentes centralizados en colors.css, sin cambiar valores ni selectores; test no debilitado |
| PHP-008 | FrontendRestauradoV2Test, reporte HTTP500 | REAL PRODUCT BUG: cierre correcto de x-ui.page-header; causa era parse Blade, no columnas BDD |
| PHP-009 | ModalesMedicacionSeguridadClinicaTest, copy/checklist anteriores; orden real ausente | OBSOLETE TEST EXPECTATION y REAL PRODUCT BUG relacionado: modal/drawer consumen dosisDetalle vigente; desaparecen ejemplo Omeprazol/Normon y variables ausentes. Assert del form comprueba residente/fármaco/dosis/vía/hora/autor/indicación reales, orden sin edición y apertura sin administración nueva |
| PHP-010 | ModalesMedicacionSeguridadClinicaTest, capitalización de subtítulo | OBSOLETE TEST EXPECTATION: se conserva la apertura/cierre modal/drawer |
| PHP-011 | OverlayComponentsTest, fixture no optaba por cierre explícito | TEST INFRASTRUCTURE: solicita dismissOnBackdrop=false; no cambia default global |
| PHP-012 | PantallasConectadasTest, mismo reporte HTTP500 | REAL PRODUCT BUG: mismo cierre Blade, sin alterar consulta clínica |
| PHP-013 | RolePreviewTest, misma variante del bundle Livewire | OBSOLETE TEST EXPECTATION: admite min/debug; mantiene preview readonly/identidad/permisos |
| PHP-014 | SuperadminDashboardHubTest, divisor anterior | OBSOLETE TEST EXPECTATION: verifica rm-dashboard-divider compartido, mantiene indicadores/perfil |
| JS-001 | Shell, sol visible en claro a320px | REAL PRODUCT BUG: regla genérica display de iconos ganaba por especificidad; tres reglas de visibilidad más específicas, sin modificar animación |
| JS-002 | Sidebar, timeout | TEST INFRASTRUCTURE: @js($activeNursingSection) quedaba literal e impedía iniciar Alpine; se resuelve en fixture y se rechaza Blade pendiente/pageerror. Expectativa del texto usa color canónico vigente. No se incrementa timeout ni cambia sidebar funcional |

### Verificaciones dirigidas y límites

- Reporte institucional: 1/1 objetivo; cohorte 10 tests / 21 aserciones PASS.
- Alergias/resumen: 2/14 objetivo; clase 6/43 PASS. Ausencia de dato no genera alerta ficticia.
- Historial de alertas: 1/8 objetivo; clase 5/16 PASS.
- Arquitectura overlays y gráficos: objetivos 1/12 y 1/31; clase 14/267 PASS.
- Áreas de atención: objetivo 1/7; clase 4/24 PASS.
- Autenticación: objetivo 1/6; clase 6/15 PASS. Preview: objetivo 1/10; clase 8/43 PASS.
- Medicación: dos objetivos 2/42; cohorte ModalesMedicacionSeguridadClinicaTest + AdministracionMedicacionEnfermeriaTest + AgendaMedicacionTest, 15/125 PASS. Sin cambios de reglas, competencias o prescripciones.
- Drawer: objetivo 1/5; clase 3/18 PASS. Dashboard superadmin: objetivo 1/17; clase 2/20 PASS.
- Filtros: objetivo 1/5; clase 2/6 PASS. Extracción inversa de variables conserva el texto original exactamente. Comprobación Chrome de estilos computados: 6 casos claro/dark-class/dark-attribute por base/selección, 279 elementos, 13 propiedades, PASS. Una colisión nueva de alias focus detectada durante esa comprobación se corrigió y repitió; no se afirma QA visual de todas las pantallas.
- Topbar: prueba Chrome del shell 3/3 PASS. Sidebar: cinco repeticiones secuenciales posteriores 5/5 PASS (3,06 / 3,06 / 3,04 / 3,05 / 3,07 s reloj); antes falló cinco veces igual. No se clasifica como flaky ni se añaden retries/sleeps.
- Exportaciones de áreas: RED de tres errores inesperados antes del parche; clase 18/60 PASS después, con 3 errores reportados/seguros, 3 descargas/auditorías positivas, 6 denegaciones por permiso/cuenta y área inexistente esperada. Permiso real areas.reportes. La prueba simula la frontera del exportador: no acredita formato de Excel ni entrega al navegador. Éxito auditado significa generación retornada.
- Integridad física: dos nuevos casos SQL directo 2/6 PASS; cohorte IntegridadFisicaBddV2Test + BddOperativaV2Test + OcupacionCamaIntegrityTest 26/241 PostgreSQL PASS; clase física 5/13 SQLite PASS. Segunda cama del mismo residente y administración cruzada son rechazadas por UNIQUE/FK existentes; no por Eloquent. Otras garantías de Model/Service no se atribuyen a SQL.
- Pint --test: las dos clases con pruebas nuevas pasan. El conjunto F4 retorna FAIL de formato en siete archivos; HEAD + before.diff reconstruidos fuera del repo muestran los mismos fixers preexistentes, sin nueva categoría incumplida. No se reformatean archivos ajenos por conveniencia. PHPStan/Larastan/ESLint no están configurados como herramientas propias del repo; no se instalan.

La suite final se reinició después de corregir la colisión CSS y del último cambio de fuente. Un intento interrumpido a43,42s no es resultado final ni baseline; final-php-result.json/xml corresponden solo al run completo posterior. Los comandos globales, cohortes, skips y preservación se consolidan a continuación.

### Gate global final F4

Los cuatro comandos requeridos pasan después del último cambio de fuente. Se conservan los resultados FAIL de Fase 3 y del precheck como historia, sin reinterpretarlos:

| Comando / alcance | Exit | Duración | Resultado |
|---|---:|---:|---|
| php vendor/phpunit/phpunit/phpunit --no-progress --colors=never --display-skipped --log-junit temporal/final-php.xml | 0 | 900,178 s reloj / 899,185 s XML | PASS: 686 tests, 12.219 aserciones, 0 fallos, 0 errores, 13 omitidos |
| npm test completo | 0 | 4,205 s Node | PASS: 30/30, 0 fallos, 0 omitidos |
| npm run build | 0 | 2,49 s Vite | PASS; no warning PLUGIN_TIMINGS en esta ejecución final |
| Artisan::call migrate:fresh --seed --force mediante helper temporal con guardia | 0 | 2,835 s reloj | PASS posterior a la suite final; conexión efectiva testing exclusiva; 71/71 tablas operativas instaladas, missing=[] |

El warning PLUGIN_TIMINGS sí apareció en el precheck histórico; no causó fallo y no se optimizó. Los 14 fallos PHP y 2 JS inventariados quedan resueltos; 0 UNKNOWN y 0 regresiones F3 identificadas. Se incorporan 16 casos PHP significativos (1 ausencia de alergia, 13 casos de exportación y 2 SQL directo). No se añadieron skips ni se aumentaron timeouts/retries para obtener verde.

**Gate global automatizado F4: PASS**, según los cuatro criterios solicitados. La ejecución automatizada no acredita operación clínica completa, todos los roles/pantallas, producción, datos reales, ni cierre total de TECH. Pint mantiene FAIL de siete archivos con fixers preexistentes; se informa separado y no se presenta como PASS de formato. TECH-005/009/010 permanecen PARTIAL; 003/006/007 OPEN; DEC-OPEN-001..006 OPEN sin modificación.

### Cohortes Fase 3 reejecutadas dentro de la suite global

Estas cifras se extraen de final-php.xml de F4; no son resultados reciclados de F3 ni seis comandos separados. Las cohortes se solapan y sus conteos no se suman como tests únicos. Todas tienen 0 fallos, 0 errores y 0 omitidos.

| Cohorte | Clases incluidas | Tests / aserciones | Resultado |
|---|---|---:|---|
| Autorización | Fase3SeguridadNucleoTest, PaseTurnoReconstruidoTest, BddOperativaV2Test, RolesBaselineCongeladoTest, AccesoClinicoTemporalSuperadminTest, SeguridadCriticaEnfermeriaTest | 77 / 485 | PASS |
| Signos | Fase3SignosVitalesTest, SignosVitalesPanelClasificacionTest | 14 / 59 | PASS |
| Alertas | Fase3AlertasTest, AlertasFlujoTest, AlertasHistorialFeatureTest | 25 / 117 | PASS |
| Continuidad | PaseTurnoReconstruidoTest, AgendaMedicacionTest, MiTurnoServiceTest | 46 / 206 | PASS |
| Admisión | Fase3AdmisionAtomicidadTest | 11 / 114 | PASS |
| Concurrencia | Fase3ConcurrenciaCamaPostgresTest | 1 / 29 | PASS |

La carrera PostgreSQL ejecutó dos procesos reales (PIDs 28844 y 15920), con 2 locks en espera; una solicitud ADMITIDA y otra RECHAZADA, 1 residente y 1 ocupación. Un test con dos procesos no equivale a dos tests. No demuestra toda concurrencia institucional.

### Inventario individual de los 13 omitidos finales

Los mismos casos aparecen en la baseline y la suite final; condiciones existentes inspeccionadas. En cada fila, JUSTIFICADO significa feature inhabilitada en la configuración actual, no aprobación institucional para habilitarla. TEMPORAL indica que no se encontró una fecha aprobada. RISK corresponde a la rama habilitada sin ejecución. El negativo de registro público deshabilitado sí se ejecuta; AGENTS prohíbe registro público. No se habilitaron API/eliminación/email/2FA para obtener verde.

| TEST / fuente | MOTIVO | JUSTIFICADO | TEMPORAL | DEPENDENCIA | RISK |
|---|---|---|---|---|---|
| [test_api_token_permissions_can_be_updated](../tests/Feature/ApiTokenPermissionsTest.php) | API support is not enabled. | Sí, configuración actual | Sin fecha aprobada | Jetstream API | Rama habilitada sin ejecutar |
| [test_api_tokens_can_be_created](../tests/Feature/CreateApiTokenTest.php) | API support is not enabled. | Sí, configuración actual | Sin fecha aprobada | Jetstream API | Rama habilitada sin ejecutar |
| [test_user_accounts_can_be_deleted](../tests/Feature/DeleteAccountTest.php) | Account deletion is not enabled. | Sí, configuración actual | Sin fecha aprobada | Jetstream accountDeletion | Rama habilitada sin ejecutar |
| [test_correct_password_must_be_provided_before_account_can_be_deleted](../tests/Feature/DeleteAccountTest.php) | Account deletion is not enabled. | Sí, configuración actual | Sin fecha aprobada | Jetstream accountDeletion | Rama habilitada sin ejecutar |
| [test_api_tokens_can_be_deleted](../tests/Feature/DeleteApiTokenTest.php) | API support is not enabled. | Sí, configuración actual | Sin fecha aprobada | Jetstream API | Rama habilitada sin ejecutar |
| [test_email_verification_screen_can_be_rendered](../tests/Feature/EmailVerificationTest.php) | Email verification not enabled. | Sí, configuración actual | Sin fecha aprobada | Fortify emailVerification | Rama habilitada sin ejecutar |
| [test_email_can_be_verified](../tests/Feature/EmailVerificationTest.php) | Email verification not enabled. | Sí, configuración actual | Sin fecha aprobada | Fortify emailVerification | Rama habilitada sin ejecutar |
| [test_email_can_not_verified_with_invalid_hash](../tests/Feature/EmailVerificationTest.php) | Email verification not enabled. | Sí, configuración actual | Sin fecha aprobada | Fortify emailVerification | Rama habilitada sin ejecutar |
| [test_registration_screen_can_be_rendered](../tests/Feature/RegistrationTest.php) | Registration support is not enabled. | Sí, configuración actual | Sin fecha aprobada | Fortify registration | Rama habilitada sin ejecutar |
| [test_new_users_can_register](../tests/Feature/RegistrationTest.php) | Registration support is not enabled. | Sí, configuración actual | Sin fecha aprobada | Fortify registration | Rama habilitada sin ejecutar |
| [test_two_factor_authentication_can_be_enabled](../tests/Feature/TwoFactorAuthenticationSettingsTest.php) | Two factor authentication is not enabled. | Sí, configuración actual | Sin fecha aprobada | Fortify twoFactorAuthentication | Rama 2FA sin ejecutar; no certificación de seguridad |
| [test_recovery_codes_can_be_regenerated](../tests/Feature/TwoFactorAuthenticationSettingsTest.php) | Two factor authentication is not enabled. | Sí, configuración actual | Sin fecha aprobada | Fortify twoFactorAuthentication | Rama 2FA sin ejecutar; no certificación de seguridad |
| [test_two_factor_authentication_can_be_disabled](../tests/Feature/TwoFactorAuthenticationSettingsTest.php) | Two factor authentication is not enabled. | Sí, configuración actual | Sin fecha aprobada | Fortify twoFactorAuthentication | Rama 2FA sin ejecutar; no certificación de seguridad |
### Inventario exclusivo de archivos F4 y preservación

24 archivos respecto al snapshot F4: 8 de producto, 12 de tests y 4 documentos existentes. El árbol incluye otros cambios previos intencionales, ajenos a esta lista:

- [docs/CHANGELOG.md](../docs/CHANGELOG.md)
- [docs/DEUDA_TECNICA.md](../docs/DEUDA_TECNICA.md)
- [docs/ESTADO_ACTUAL.md](../docs/ESTADO_ACTUAL.md)
- [docs/TRAZABILIDAD.md](../docs/TRAZABILIDAD.md)
- [tests/Feature/AreasInstitucionalesV2Test.php](../tests/Feature/AreasInstitucionalesV2Test.php)
- [app/Frontend/Livewire/Superadministrador/Identidad/AreasInstitucionalesPanel.php](../app/Frontend/Livewire/Superadministrador/Identidad/AreasInstitucionalesPanel.php)
- [resources/frontend/styles/design-system/components/filters.css](../resources/frontend/styles/design-system/components/filters.css)
- [resources/frontend/styles/design-system/components/topbar.css](../resources/frontend/styles/design-system/components/topbar.css)
- [resources/frontend/styles/design-system/tokens/colors.css](../resources/frontend/styles/design-system/tokens/colors.css)
- [resources/views/livewire/alertas/modales/drawer-graficos.blade.php](../resources/views/livewire/alertas/modales/drawer-graficos.blade.php)
- [resources/views/livewire/medicacion/partials/drawer-dosis.blade.php](../resources/views/livewire/medicacion/partials/drawer-dosis.blade.php)
- [resources/views/livewire/medicacion/partials/modal-administrar.blade.php](../resources/views/livewire/medicacion/partials/modal-administrar.blade.php)
- [resources/views/livewire/reportes/reportes-institucionales-panel.blade.php](../resources/views/livewire/reportes/reportes-institucionales-panel.blade.php)
- [tests/Feature/AdministracionMedicacionEnfermeriaTest.php](../tests/Feature/AdministracionMedicacionEnfermeriaTest.php)
- [tests/Feature/AlertasHistorialFeatureTest.php](../tests/Feature/AlertasHistorialFeatureTest.php)
- [tests/Feature/Architecture/FrontendArchitectureTest.php](../tests/Feature/Architecture/FrontendArchitectureTest.php)
- [tests/Feature/AreasAtencionSuperadminTest.php](../tests/Feature/AreasAtencionSuperadminTest.php)
- [tests/Feature/AuthenticationTest.php](../tests/Feature/AuthenticationTest.php)
- [tests/Feature/IntegridadFisicaBddV2Test.php](../tests/Feature/IntegridadFisicaBddV2Test.php)
- [tests/Feature/ModalesMedicacionSeguridadClinicaTest.php](../tests/Feature/ModalesMedicacionSeguridadClinicaTest.php)
- [tests/Feature/OverlayComponentsTest.php](../tests/Feature/OverlayComponentsTest.php)
- [tests/Feature/RolePreviewTest.php](../tests/Feature/RolePreviewTest.php)
- [tests/Feature/SuperadminDashboardHubTest.php](../tests/Feature/SuperadminDashboardHubTest.php)
- [tests/Frontend/sidebar-responsive.test.js](../tests/Frontend/sidebar-responsive.test.js)

Snapshot inicial: 1321 rutas con hashes y before.diff fuera del repositorio. Las cinco ausencias de Models/factory V1 ya estaban presentes antes de F4; no son eliminaciones de esta fase. Comparación final confirma .env idéntico, 0 cambios en migraciones/estructura BDD, docs/base-de-datos, DECISIONES_PENDIENTES, skills y cambios F3 ajenos; 0 archivos nuevos y 0 cambios de fuente posteriores al snapshot durante la suite. HEAD conserva 8e9e20325519c5da5a6b25f6fb26568cad78efe1, rama REINICIO; sin staging, commit ni push. No se crearon documentos nuevos en el repo. No hay nuevas reglas clínicas, permisos, sistema experto ni decisiones estructurales.

Evidencia íntegra temporal: C:/Users/CARLAE~1/AppData/Local/Temp/remembermind-f4-logs/. Archivos principales: baseline-php-enabled.log/xml/result.json; php-failure-matrix-current.json; js-failure-matrix-current.json; final-php.log/xml/result.json; final-comparison.json; final-js.log; final-build.log; final-migrate-seed.log/result.json; sidebar-fixed-repeat-results.json; skips-inventory-current.json; filters-computed-equivalence.json; pint-comparison.json; preservation-final.json; final-links.json; final-diff-check.log. Son artefactos locales de esta sesión, no una colección publicada ni garantia de conservación externa; credenciales fuera del repositorio y de la documentación.

Comprobación documental final: 109 Markdown, 955 enlaces locales, 0 rotos y 2 anchors verificados. git diff --check acotado a los 20 archivos de fuente/test F4: exit 0; los avisos CRLF/LF no son fallos de whitespace.

Revisión de evidencia local: PASS para las afirmaciones acotadas respaldadas por esos resultados; Caveman Cloud NOT VERIFIED por ausencia de MCP/CLI. Revisión independiente caveman-review final de código/tests/seguridad/integridad/BDD/documentación: **PASS acotado**, sin hallazgos accionables pendientes. Se corrigieron la referencia al permiso areas.reportes y campos faltantes de las matrices. El revisor contrastó JUnit 686/12.219, seis cohortes, 13 skips, JS 30/30, sidebar 5/5, build, reset/seed 71/71, preservación y límites de deuda/formato/exportación. Revisión exclusivamente read-only: no ejecutó suites/BDD ni modificó archivos. No sustituye suites ni certificación clínica/producción.


## Navegación y auditoría de Enfermería — 2026-10-07

Solicitud de la propietaria: auditar la continuidad y aplicar Mi turno → Mis residentes → Cuidados (Signos, Dolor, Cognición, Conducta, Sueño, Ingesta, Hidratación, Eliminación, Movilidad, Heridas) → Medicación → Alertas → Incidentes → Pase de turno. Autorización explícita posterior: exactamente seis permisos de lectura registros_conductuales/sueno/ingesta/hidratacion/eliminacion/movilidad.ver para ENFERMEROS, con alcance contextual. Sin nuevas escrituras concedidas, sin cambios del esquema congelado, sin reglas clínicas nuevas y sin commit.

Implementación: SidebarService + parámetros y selección activa del componente compartido; NavegacionCuidadosService como configuración de interfaz; MisPacientes conserva selección/registro rápido y conecta SeguimientoDiarioPanel/RegistrosEnfermeria. Sueño e hidratación muestran historial propio; Sueño individual es consulta sin permiso de crear. Heridas reutiliza el formulario de curaciones mediante un parcial y mantiene la autorización del cierre. Consulta del residente reautorizada en cada render; curación exige su permiso existente. Se eliminó el hook de compatibilidad updatedCodAm del componente tocado. PaseTurnoService añade lectura de ControlCognitivo vigente; consumidor muestra Cognición/Conducta/Movilidad y campos canónicos intensidad/cantidad_ml, sin unidades para ausencia. Badge cuenta los cinco estados activos ya utilizados por el flujo.

### Evidencia ejecutada, alcance acotado

- Baseline anterior al cambio: 53 tests / 291 aserciones PASS, 56.155 s.
- Integración PostgreSQL 18.4 desechable, PHP 8.3.33: 103 tests / 1.028 aserciones PASS, 156.297 s. Cohorte: SidebarEnfermeroTest, SidebarServiceTest, MisResidentesNavegacionTest, SeguimientoDiarioPersistenciaTest, PaseTurnoReconstruidoTest, EnfermeriaV2Test, EnfermeriaV2CorreccionCriticaTest, ModuloEnfermeriaIntegralTest, TurnoCompletoEnfermeroTest y RolesBaselineCongeladoTest.
- Tras ajustar la selección activa entre directorio y destino: SidebarEnfermeroTest + MisResidentesNavegacionTest, 21 tests / 117 aserciones PASS, 34.833 s. Esta repetición se solapa con la cohorte anterior; no sumar como casos únicos.
- Regresión probada: navegación no persiste mediciones; cuidado desconocido/revocado denegado; residente ajeno denegado al cambiar el componente; creación de curación sin permiso denegada; lectura de sueño no concede creación; pase lee control vigente y excluye control anulado/ajeno sin copiar registros; badge excluye cerrado/ajeno.
- Frontend final: npm test 30/30 PASS, 3.884 s. npm run build PASS, Vite 8.3.1.
- Chrome/Puppeteer: HTML real del componente Blade renderizado por Laravel con usuario sintético y seeder dentro de transacción revertida. Harness local de navegador con CSS compilado y Alpine/Livewire reales, no sesión de la aplicación completa. 1440/1280/1024/768/390 px PASS: diez destinos distintos, Heridas como único hijo activo, teclado Space, flyout de laptop, Escape/retorno de foco, reduced motion, targets de 44 px, sin scroll horizontal. Scroll vertical permite llegar a Alertas/Incidentes/Pase sin taparlos con el footer. Capturas de viewport (no full-page) inspeccionadas; las primeras capturas full-page de Chrome distorsionaban la geometría fija y se descartaron. No certifica todas las pantallas clínicas ni accesibilidad global.

Evidencia temporal fuera del repo: C:/Users/CARLAE~1/AppData/Local/Temp/remembermind-enfermeria-nav-20261007/. before.json/diff, baseline.log/xml, final-integration.log/xml, final-navigation.log/xml, final-frontend.log, build.log, sidebar-rendered.html, visual-results.json y sidebar-{ancho}.png/continuidad.png. Intentos intermedios fallidos se conservaron en targeted/integration/visual logs cuando corresponde; solo los resultados finales anteriores respaldan PASS. Credenciales no documentadas.

### Límites y auditoría

Los diez accesos reutilizan capacidades actuales; Cognición y Conducta se capturan en seguimiento diario completo, no en nuevas pantallas independientes. Sueño individual permanece de lectura con el grant aprobado. Los registros del pase son los últimos guardados y no se certifica que todos sean de la jornada. El listado global SaludSeguimientoListPanel continúa siendo un hallazgo preexistente de alcance no contextual: no está enlazado desde estos accesos y las seis lecturas no son condiciones de acceso de ese listado; requiere corrección separada. No se ratifica seguridad global por estos resultados. DEC-OPEN-001..006 y deuda técnica global conservan su estado. El seeder completo fue ejecutado solo en PostgreSQL desechable.

**Aplicación local autorizada:** verificada configuración APP_ENV=local, PostgreSQL remembermind_dev en 127.0.0.1. Se añadieron únicamente las seis lecturas existentes al rol ENFERMEROS mediante givePermissionTo en transacción: 66 → 72 grants. Ningún permiso retirado; pivot de todos los demás roles idéntico antes/después; comprobación de que ninguna adición excede la lista aprobada. Auditoría en Spatie Activitylog con fuente OWNER_CONVERSATION_2026_10_07, sin atribuir el acto técnico a un usuario clínico inventado. Cache Spatie invalidada. No se ejecutaron migraciones, reset ni seeder completo sobre esa base. Evidencia: local-permissions-result.json y helper externo apply-local-read-permissions.php.

El PASS global de Fase 4 más arriba corresponde a su snapshot anterior. Esta tarea acredita exclusivamente las cohortes y la navegación indicadas; no se repitieron las 686 pruebas globales ni un reset/seed final adicional para certificar toda la aplicación.

### Cierre y preservación

Revisión caveman-review del delta de esta tarea: sin hallazgos accionables pendientes dentro del alcance implementado; el listado global preexistente queda reportado, no certificado. Revisión realizada por el mismo agente en fase de lectura, no revisión independiente de otro agente. Autorizaciones revalidadas en acciones y render; grant selectivo comprobado; sin consultas de historia clínica para resolver un acceso sin residente/contexto autorizado.

Preservación: 1.321 rutas inventariadas antes de modificar; cambian solamente 18 rutas existentes del alcance y se añaden NavegacionCuidadosService.php y el parcial heridas-curaciones.blade.php. .env, HEAD 8e9e20325519c5da5a6b25f6fb26568cad78efe1, rama REINICIO e índice sin staging conservados. Cambios anteriores ajenos preservados. git diff --check del alcance PASS; 109 documentos / 957 enlaces locales / 0 rotos / 2 anchors válidos. Guard final del PostgreSQL desechable PASS y 71/71 tablas operativas instaladas, sin reset adicional. No documentos nuevos en el repositorio, commit ni push. PASS acotado de navegación e integración; límites anteriores continúan vigentes.
