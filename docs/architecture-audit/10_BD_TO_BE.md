# Modelo de datos objetivo

## Tamaño y alcance

Propuesta completa: **51 tablas funcionales (incluye users) + 15 técnicas = 66**. No es una meta de reducción desde las 61 actuales: elimina duplicación pero incorpora estancias, versiones, ejecuciones y capacidades que hoy no existen. Para el piloto se difieren 9 tablas señaladas A: quedan **42 funcionales + 15 técnicas = 57**. Si Sanctum no tiene uso funcional al cerrar la transición se puede retirar personal_access_tokens, por decisión separada.

La cifra cercana a 70 se revisó críticamente: no se crean catálogos para sexo, cada estado, cada tipo de nota o cada profesión como tabla clínica; no se crean tablas por dashboard; no hay tablas del timeline ni indicadores duplicando hechos. Las tablas separadas tienen identidades, cardinalidades o ciclos de vida diferentes. Reducirlas a 50 exigiría recortar alcance o mezclar historia/operación, no solo normalizar mejor.

## Catálogo exacto: PK id bigint identity salvo pivotes con PK compuesta

En todas las filas clínicas: created_at/updated_at, author_id FK users, occurred_at para hecho, status y motivo/autor/fecha de corrección cuando aplique. Fechas de finalización son distintas de fechas del hecho. FK históricas RESTRICT por defecto; desactivar personas/usuarios en vez de borrarlos. El siguiente es diseño lógico implementable, no DDL aprobado.

| # | Tabla | Atributos y FK esenciales | Regla/índice principal |
|---|---|---|---|
| 1 | persons | nombres, apellidos, birth_date, datos contacto | Identidad humana; no correo como deduplicación universal |
| 2 | person_identifiers | person_id, país, tipo, número normalizado, complemento | UNIQUE país+tipo+número+complemento normalizado; permite persona sin documento |
| 3 | users | person_id, login_email, password, estado, 2FA | UNIQUE person_id y lower(login_email); compatibilidad correo durante transición |
| 4 | professionals | person_id, profession_id, habilitación | UNIQUE person_id; profesión principal inicial |
| 5 | professions | code, name | UNIQUE code |
| 6 | specialties | profession_id, code, name | UNIQUE code |
| 7 | professional_specialties | professional_id, specialty_id | PK compuesta; vigencia de habilitación si se requiere |
| 8 | staff_assignments | person_id, institution_id, area_id, position_id, start/end | Índice persona+vigencia; profesional opcional por relación de persona |
| 9 | positions | code, name | UNIQUE code; cargo no concede permiso |
| 10 | institutions | name, datos operativos | Una institución inicial, sin multi-tenant implícito |
| 11 | areas | institution_id, code, name | UNIQUE institution_id+code |
| 12 | rooms | institution_id, code, tipo | UNIQUE institution_id+code |
| 13 | beds | room_id, code, operational_status | UNIQUE room_id+code; ocupación derivada |
| 14 | shifts | institution_id, name, hora inicio/fin | Definition de turno; cruce de medianoche explícito |
| 15 | staff_schedules | person_id, shift_id nullable, starts_at, ends_at, kind | CHECK fin>inicio; índices persona+inicio; kind availability/duty distingue disponibilidad |
| 16 | residents | person_id, legacy_code | UNIQUE person_id y legacy_code |
| 17 | resident_contacts | resident_id, person_id, parentesco, responsable, vigencia | Índice residente+vigencia; un responsable principal activo si regla validada |
| 18 | consents | resident_id, contact_id nullable, scope, granted/revoked_at, documento | Cada autorización con otorgante, evidencia y vigencia; no booleano universal |
| 19 | admission_cases | person_id, requested_at, status, decisión/autor | Solicitud no duplica identidad actual; snapshot original explícito |
| 20 | admissions | resident_id, admission_case_id nullable, admitted_at, discharged_at | UNIQUE parcial estancia abierta por residente; reingresos múltiples |
| 21 | bed_assignments | admission_id, bed_id, starts_at, ends_at | UNIQUE parcial cama activa y estancia activa; intervalos históricos no solapados |
| 22 | resident_status_changes | admission_id, from_status, to_status, occurred_at, actor | append-only; índice admission_id+occurred_at |
| 23 | clinical_notes | resident_id, admission_id nullable, kind, contenido, supersedes_id | Corrección enlazada; índice residente+fecha |
| 24 | diagnoses | resident_id, código/texto, certeza, onset/resolved_at, supersedes_id | No false=ausencia; índice residente+estado |
| 25 | allergies | resident_id, sustancia, reacción, certeza, status, supersedes_id | Conservar desconocido/negado/confirmado según protocolo |
| 26 | vital_signs | resident_id, admission_id nullable, observed_at, medidas tipadas/unidades, supersedes_id | CHECK rangos físicos básicos validados; índice residente+observed_at |
| 27 | prescriptions | resident_id, prescriber_id, signed_at, status, supersedes_id | Solo actor habilitado; índice residente+estado |
| 28 | prescription_items | prescription_id, medicamento, dosis decimal, unidad, vía, pauta, inicio/fin | Dosis/unidad explícitas; UNIQUE id+prescription_id si FK compuesta necesaria |
| 29 | medication_administrations | prescription_item_id, scheduled_at, performed_at, outcome, reason, request_key | UNIQUE request_key y dosis programada; no repetir resident_id como segunda verdad |
| 30 | assessment_instruments | code, name, domain | UNIQUE code; catálogo sin resultados |
| 31 | instrument_versions | instrument_id, version, schema_json, scoring_version | UNIQUE instrument_id+version; inmutable tras uso |
| 32 | assessments | resident_id, instrument_version_id, assessed_at, answers_json, score, interpretation, risk, status | Índice residente+fecha; corrección por supersedes_id; JSON validado según versión |
| 33 | care_plans | resident_id, version, previous_id, goals, status, valid_from/to | Una versión activa por plan lógico, no necesariamente un solo plan interdisciplinario global |
| 34 | care_tasks | care_plan_id, resident_id, assigned_person_id, schedule_id nullable, action, due_at | Derivar residente del plan o FK compuesta para consistencia; tareas fuera de plan permitidas explícitamente |
| 35 | care_task_executions | task_id, performed_at, outcome, notes, request_key | UNIQUE request_key; ejecución separada de definición |
| 36 | daily_observations | resident_id, schedule_id nullable, observed_at, kind, contenido/medidas | Fecha/hora real; inmutabilidad al finalizar |
| 37 | handovers | resident_id, from_schedule_id, to_schedule_id nullable, sent/received_at, contenido | Recepción distinta de emisión; no sobrescribir pase enviado |
| 38 A | therapy_sessions | resident_id, care_plan_id, professional_id, starts_at, outcome | Sesión de rehabilitación con resultado trazable |
| 39 A | nutrition_plans | resident_id, care_plan_id, version, dieta, valid_from/to | Plan alimentario distinto de evaluación MNA/peso |
| 40 A | activities | institution_id, name, kind, starts_at, ends_at | Una actividad puede tener muchos participantes |
| 41 A | activity_participations | activity_id, person_id, role, attendance | UNIQUE activity_id+person_id+role |
| 42 A | volunteer_assignments | person_id, resident_id nullable, area_id nullable, start/end | Vínculo operativo, no cuenta de acceso; al menos un destino |
| 43 A | visits | resident_id, visitor_person_id, scheduled_at, attended_at, status | Agenda de visitas con permisos propios |
| 44 A | social_followups | resident_id, professional_id, occurred_at, notes, supersedes_id | Seguimiento social con confidencialidad específica |
| 45 | incidents | resident_id, occurred_at, kind, gravedad, relato | Caída es tipo de incidente; hechos y medidas inmediatas |
| 46 | alerts | resident_id, source_type/id, severity, status, assigned_to, detected_at | Deduplicación por fuente/regla; source validado por adaptador de dominio |
| 47 | alert_actions | alert_id, actor_id, action, occurred_at, motivo | append-only; transición de alerta y acción atómicas |
| 48 A | expert_runs | resident_id, snapshot_json, snapshot_hash, engine/rules_version, status, result_json | Clave idempotente; input y output preservados |
| 49 A | expert_reviews | expert_run_id, reviewer_id, decision, reason, reviewed_at | Revisión inmutable; varias revisiones explícitas |
| 50 | documents | person_id/resident_id/admission_case_id alternativos, type, classification, status | CHECK exactamente un propietario; FK explícitas, Policy del propietario |
| 51 | document_versions | document_id, version, private_path, hash, MIME, size, author_id | UNIQUE document_id+version; firma/recepción como metadatos trazables |

Tablas técnicas (15): roles, permissions, model_has_roles, model_has_permissions, role_has_permissions, activity_log, sessions, password_reset_tokens, personal_access_tokens, cache, cache_locks, jobs, job_batches, failed_jobs, migrations. Reports no agrega tablas al inicio. A significa ampliación/piloto diferido; funcionalidades existentes diferidas se mantienen temporalmente en legacy, no se borran para lograr el conteo. Durante convivencia el número físico será mayor que el TO-BE.

## Invariantes que deben quedar en PostgreSQL

UNIQUE parcial WHERE ends_at IS NULL en bed_assignments.bed_id y admission_id; CHECK ends_at>starts_at; UNIQUE parcial en admissions(resident_id) WHERE discharged_at IS NULL. Para intervalos históricos no basta el índice parcial: evaluar EXCLUDE sobre rango temporal, o validación con bloqueo por cama y prueba concurrente. La extensión necesaria para EXCLUDE se decidirá en el corte de camas. Las FK se indexan según joins/consultas; PostgreSQL no crea automáticamente todos los índices del lado referenciante. [Constraints de PostgreSQL](https://www.postgresql.org/docs/current/ddl-constraints.html).

Prescripción se bloquea al administrar para evitar carrera con suspensión; verificar vigencia respecto al momento clínico y al registro. Diferenciar dosis programada, PRN y corrección: no usar el timestamp de clic como única clave. PRN necesita request_key única, motivo y referencia a la orden; una anulación no borra la ejecución ni permite redosificar automáticamente.

JSONB se limita a respuestas/versiones de instrumento y snapshots externos. Diagnósticos, medicamentos y vínculos relevantes no se guardan como EAV genérico. Para documents el CHECK de un propietario evita polimorfismo sin FK. alerts.source_type/id es una excepción polimórfica documentada: referencias de procedencia validada por aplicación; la alerta sigue teniendo FK de residente y no pierde su snapshot si la fuente deja de ser visible.

## Personas: decisión y costo

Ventajas: identidad única entre empleado/familiar/residente, sin obligar a tener cuenta; menos correcciones repetidas y documentos asociados correctamente. Desventajas: importación y deduplicación complejas, permisos de datos personales más finos y joins nuevos. No deduplicar por nombre ni correo: documento normalizado, evidencia y revisión de coincidencias dudosas. Si no hay documento, generar persona provisional y no fusionar automáticamente.

Preservar tabla de correspondencias de importación (artefacto controlado o tabla temporal de migración, no tabla de producto permanente), claves legacy y hashes de filas fuente. Conservar snapshots de solicitudes históricas con etiqueta explícita; no deben sobrescribir identidad actual. La primera migración debe validar login con cod_usu antes de tocar PK de users. No cambiar PK, morph types, RBAC y frontend en el mismo corte.

## Historia longitudinal como requisito inicial

Cada hecho distingue ocurrido, registrado y finalizado; una corrección apunta al original y contiene motivo/autor. Estado actual es una consulta o proyección reconstruible. Consultar «hace seis meses» usa fecha del hecho y versión vigente; consultar «qué sabíamos entonces» usa también recorded_at. No se exige un motor bitemporal universal: conservar ambos tiempos y correcciones es suficiente para el alcance inicial. Nunca inventar fechas de hechos en importación: marcar unknown/estimated y conservar fuente.
