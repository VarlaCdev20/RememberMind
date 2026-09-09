# Base de datos AS-IS

## Método y hechos

Conexión PDO directa, sin boot de Laravel, a la configuración local. BEGIN READ ONLY y ROLLBACK; consultas de information_schema, pg_constraint, pg_indexes, migrations y nombres de roles, más comprobaciones agregadas sin valores personales. PostgreSQL 18.4; 61 tablas public, incluidas técnicas. El catálogo completo de columnas, FK, UNIQUE, CHECK e índices aparece al final. Esta auditoría no cambió migraciones ni hizo migrate:fresh.

Las 60 migraciones registradas coinciden nominalmente con los 60 archivos locales. Eso no prueba que nunca se editaran migraciones ya aplicadas ni equivalencia completa con un esquema creado desde cero. El inventario del servidor es la evidencia del AS-IS efectivo; reproducir desde cero en una BD de pruebas y comparar es una puerta pendiente.

## Integridad y normalización

Hay PK en las tablas inspeccionadas y numerosas FK: el sistema no carece de integridad referencial. La debilidad está en invariantes que FK independientes no expresan, índices de negocio faltantes y columnas de compatibilidad. No confundir número de tablas con 3FN.

* 1FN: textos narrativos como observaciones son válidos. Listas de diagnósticos/alergias y respuestas serializadas sin esquema no deben sustituir hechos estructurados consultables. El JSON de un instrumento sí puede ser un documento de respuestas versionado y validado.
* 2FN: las entidades usan PK de una columna; en pivotes verificar que los atributos dependan del vínculo completo, no de la persona. Un teléfono del familiar no pertenece al vínculo con cada residente.
* 3FN: cod_cama determina habitación; copiar ambos además de asignaciones introduce dependencia y discrepancia. Copiar identidad desde usuario/familiar/preadmisión sin política de snapshot crea varias fuentes activas. puntaje_total y puntaje, o estado_eval y estado, son duplicación semántica directa.
* Clínica: booleanos de diagnósticos en ficha_medica_adulto no representan inicio, remisión, certeza o desconocimiento. No migrar false automáticamente a diagnóstico ausente confirmado.
* Temporalidad: fecha_ing en residente no representa reingresos. Cama actual necesita intervalo. SoftDeletes/updated_at/activity_log no reconstruyen por sí solos una historia clínica confiable.

## Cuatro comprobaciones de datos

| Comprobación | Resultado observado | Interpretación |
|---|---:|---|
| Administraciones cuyo cod_am difiere del de su medicación | 0 | No se detectó el conflicto en las filas actuales; el modelo permite que ocurra |
| Camas con más de una asignación estado ACTIVO | 1 grupo | Requiere conciliación; no se corrigió ni se expusieron residentes |
| Evaluaciones con puntaje distinto de puntaje_total, incluyendo NULL | 0 | Compatibilidad actual consistente en ese par |
| Evaluaciones con estado distinto de estado_eval, incluyendo NULL | 0 | No garantiza semántica correcta de alerta/estado |

No se infieren porcentajes sin denominadores ni se declara limpieza total de datos. Antes de importar: huérfanos, claves duplicadas, fechas inválidas, identidad probable duplicada, prescripciones activas incoherentes y discrepancias de todos los pares deben entrar en un informe de conciliación privado.

## Mapa exhaustivo de tablas actuales a destino

C = conservar concepto/datos con adaptación; R = reemplazar estructura sin perder historia; T = conservar técnico. No significa que pueda ejecutarse un rename sin adaptar consumidores.

| Tablas actuales | Decisión | Destino / transformación |
|---|---|---|
| users | C | users para acceso; identidad a persons/person_identifiers, organización a staff_assignments |
| familiares, familiar_adulto | R | persons + resident_contacts; usuario opcional, consentimiento explícito |
| voluntarios | R | persons; usuario opcional y vínculos de voluntariado |
| adulto_mayor | R | residents + persons + admissions + consents; no duplicar alergias/cama |
| preadmisiones | R | admission_cases con persona candidata y snapshot documental original |
| estado_adulto, historial_estado_adulto | R/C | enum de estancia y resident_status_changes, conservar códigos legacy |
| habitaciones, camas | C | rooms, beds con FK room_id y estado operativo |
| asignacion_adulto_mayor | R | bed_assignments por estancia e intervalo; conciliar ACTIVO duplicado |
| areas_institucionales | C | institutions/areas; separar asignación profesional de cuenta |
| horarios_personal_admin, horarios_personal_salud | R | staff_schedules sin tablas por profesión |
| turnos_institucionales, turnos_enfermeria | R | shifts para definición y staff_schedules para ocurrencias |
| asignaciones_plazas_enfermeria | R | staff_assignments para puesto/área y horarios para vigencia |
| asignaciones_turno_adulto | R | care_tasks/asignación de atención con responsable; conservar vínculo histórico al turno en daily_observations/handovers |
| ficha_medica_adulto | R | clinical_notes + diagnoses + allergies, mantener documento original y procedencia |
| notas_evolucion_medica | C | clinical_notes con tipo, autor, fecha del hecho y correcciones |
| medicacion_adulto | R | prescriptions + prescription_items, documento de receta enlazado |
| administracion_medicacion | R | medication_administrations, pertenencia por ítem y dosis única |
| signos_vitales_adulto | C | vital_signs con unidades y fechas; separar lectura de peso de diagnóstico |
| valoracion_funcional_adulto, valoracion_enfermeria_admision | R | assessments vinculadas a versión de instrumento; datos no de escala a observaciones/nota |
| areas_geriatricas | R | Código de dominio en instrumento/enum; conservar catálogo si institución realmente lo administra |
| instrumentos_geriatricos | C | assessment_instruments + instrument_versions |
| evaluaciones_geriatricas | R | assessments, resolver aliases y distinguir riesgo de estado |
| planes_cuidado | C | care_plans versionados |
| tareas_plan_cuidado | C | care_tasks + care_task_executions; separar definición/resultado |
| seguimientos_diarios | C | daily_observations por hecho/turno, sin borrar registros anteriores |
| pases_turno | C | handovers con autor/emisor/receptor y contenido finalizado |
| alertas_adulto, acciones_alerta | C | alerts + alert_actions con estado y responsable |
| obs_adulto | R | daily_observations o clinical_notes según tipo; no duplicar texto en ambos |
| atenciones_adulto, tipo_atenciones_adulto | R | clinical_notes, therapy_sessions o social_followups según naturaleza; conservar tipo/origen |
| actividades_adulto, tipo_actividades_adulto | R | activities + activity_participations; no agrupar sesiones solo por nombre |
| asignacion_voluntarios, asistencia_voluntarios, disponibilidad_voluntarios | R | volunteer_assignments + activity_participations + staff_schedules por persona voluntaria; distinguir horario/disponibilidad mediante tipo |
| documentos_adulto_mayor, documentos_usuarios, documentos_preadmision, tipos_documentos_usuario | R | documents + document_versions; tipo enumerado configurable después; sin ruta pública |
| roles, permissions, model_has_roles, model_has_permissions, role_has_permissions | T | Spatie, adaptar model_type/id al migrar usuario y grants efectivos |
| activity_log | T | Bitácora minimizada, no historia clínica fuente |
| sessions, password_reset_tokens, personal_access_tokens | T | Conservar contrato de autenticación; revisar tipo de usuario y campos sensibles |
| cache, cache_locks, jobs, job_batches, failed_jobs, migrations | T | Infraestructura Laravel; no mezclar con tablas de dominio |

## Campos que hoy pueden sobrescribir historia

AdultoMayor::fillable incluye alergias, estado, fecha_ing, consentimiento, responsable y cama. FichaMedicaAdultoModal::guardar actualiza ficha existente (línea 160); SaludFichaPanel hace lo mismo (251). EvaluacionGeriatrica tiene mutadores que sincronizan resultados y estados (booted y setters), sin versión de instrumento inmutable. MedicacionAdulto permite modificar dosis/frecuencia/estado; un registro final debe conservar los términos de la orden que se administró. TareaPlanCuidado mezcla tarea y resultado; separar ejecuciones evita perder cada cumplimiento. Los formularios de contactos/usuarios actualizan identidad; registrar correcciones, pero no replicar toda identidad como historia clínica.

Para cada columna del catálogo: clasificar identidad corregible, hecho histórico, estado derivado, dato técnico o compatibilidad. Las columnas clínicas/temporales anteriores requieren reglas explícitas; no hacer una copia ciega de cada update a una tabla genérica y llamarla longitudinalidad.
