# Mapa de migración AS-IS → TO-BE

Este mapa cubre **las 61 tablas y todas sus columnas físicas** inventariadas en 02. Diseño, no ejecución. Nombres destino se definen en 03. Dentro de una celda se abrevia el prefijo de tabla para sus campos siguientes. Una fila que enumera varias columnas describe una transformación conjunta (no copia posicional ciega).

## Contrato de importación

- Extraer snapshot consistente a entorno aislado autorizado. Guardar conteos, hashes, versión de código y un batch_key. No importar sobre la base activa durante desarrollo.
- Cada PK origen → migration_links(source_table,source_key,target_table,target_key,source_hash,disposition). Los códigos visibles se preservan también como legacy_code; residents.public_code conserva cod_am. IDs nuevos generados, todas las FK remapeadas. Nunca reutilizar MAX.
- Conservar copia original cifrada por fila cuando exista transformación, conflicto o eliminación de columna. Clave protegida y acceso exclusivo de conciliación; migration_links no es un depósito consultable por pantallas de negocio. No volcar contraseñas/secretos a logs: hashes y 2FA siguen exclusivamente en almacén protegido de cuentas; payload de procedencia los excluye.
- Nombre o teléfono iguales no bastan para fusionar personas. Preferir cod_usu explícito; luego documento verificado país/tipo/número/complemento. Enlaces divergentes → cuarentena, revisión humana y resolución auditable. Fechas/hora faltantes se preservan como precisión desconocida en procedencia; no pueden habilitar automatismos.
- Campos eliminados del modelo operativo **no se pierden**: espejos coincidentes se colapsan; valores divergentes se conservan y bloquean conciliación. Un estado desconocido nunca se convierte silenciosamente a ACTIVO.
- Conservación de created_at/updated_at no demuestra instante del evento. Si el destino exige instante y solo existe timestamp técnico, importarlo con marca inferred_timestamp en procedencia y revisión; la UI identifica registro histórico de precisión limitada.
- Estados: normalizar acentos/mayúsculas únicamente con diccionario explícito. Los catálogos completos de valores reales y sus conteos se perfilarán en el ensayo aprobado; esta tarea no leyó registros personales ni declara resueltos datos ambiguos. D02/D03 deben cerrarse antes del importador final.

## Mapa por tabla y columna

### acciones_alerta → alert_actions

Clasificación: **MODIFICAR**. Acciones de atención de alertas.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_accion_alerta | migration_links.source_key → target_key; alert_actions.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_alerta, accion, responsable_id, fecha_accion, estado, observacion | alert_actions.alert_id,description,recorded_by,occurred_at,result,notes | Acción histórica no equivale estado global alerta |
| created_at | alert_actions.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | alert_actions.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### actividades_adulto → activities + activity_participations

Clasificación: **DIVIDIR**. Programación y asistencia individual en la misma fila.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_act_adul | migration_links.source_key → target_key; activities.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, observacion, estado | activity_participations.resident_id,notes,participation_status,attendance_status; activities.status | REALIZADA evidencia asistencia solo según semántica actual; CANCELADA preserva motivo |
| cod_tipo_act, fecha, hora_inicio, hora_fin, registrado_por | activities.activity_type_id,starts_at,ends_at,recorded_by | Un evento por fila inicial; consolidación colectiva solo revisión posterior |
| created_at | activities.created_at, activity_participations.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | activities.updated_at, activity_participations.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### activity_log → activity_log + encounters + clinical_notes + vital_signs + admission_decisions

Clasificación: **DIVIDIR**. Auditoría transversal y hechos clínicos guardados solo en properties.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, log_name, description, subject_type, subject_id, causer_type, causer_id, event, batch_uuid, properties, created_at, updated_at | activity_log (contrato de 03) | Conservar eventos y properties saneadas, remapear sujeto/autor con morph map; mantener referencia legacy si tipo retirado, sin destruir evidencia. |

### administracion_medicacion → medication_administrations

Clasificación: **MODIFICAR**. Dosis registrada u omitida.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_admin_med | migration_links.source_key → target_key; medication_administrations.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_med_adulto, fecha, hora_programada, hora_real, administrado, motivo_omision, efecto_observado, observacion, registrado_por | medication_administrations.prescription_item_id,scheduled_at,administered_at,outcome,omission_reason,observed_effect,notes,recorded_by | Clave de importación estable; administrado=false exige motivo o incidencia de calidad |
| cod_am | migration_links.encrypted_source_payload | Comprobar que corresponde a persona de prescripción; discrepancia bloquea |
| created_at | medication_administrations.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | medication_administrations.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### adulto_mayor → persons + person_identifiers + residents + person_contacts + consents + admissions + bed_assignments + allergies + resident_status_changes

Clasificación: **DIVIDIR**. Identidad, expediente, ingreso, cama y consentimiento juntos.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_am | migration_links.source_key → target_key; persons.legacy_code; residents.public_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombres, ap_paterno, ap_materno, fecha_nac, genero, estado_civil, telefono, celular, telefono_fijo, departamento_residencia, ciudad_municipio, zona, calle | persons.given_names,paternal_name,maternal_name,birth_date,sex,civil_status,phone,mobile,department,city,zone,street | Conservar celular y fijo; si telefono difiere de fijo retener ambos en procedencia y conciliar antes de elegir phone |
| ci, complemento_ci, expedicion_ci | person_identifiers.number,complement,issued_at | País/tipo no probados: pendiente validación, no adivinar |
| tiene_celular, sabe_usar_whatsapp | residents.mobile_capabilities | JSON validado {has_mobile,uses_whatsapp} |
| fecha_ing, hora_ing, tipo_ing, permanencia, motivo_ingreso, procedencia_ingreso, cod_pre_origen | admissions.admitted_at,admission_type,permanence,origin,admission_case_id; admission_cases.reason | Crear estancia heredada; motivo sin caso queda en procedencia y nota de ingreso encounters/clinical_notes |
| nivel_educat, grupo_sanguineo, factor_rh, seguro_salud, observaciones, archivado_en, motivo_archivado | residents.education_level,blood_group,rh_factor,health_insurance,notes,archived_at,archive_reason | Conservar separación estado expediente/estancia |
| alergias | allergies.substance,verification_status | Texto heredado no confirmado; conciliar con ficha sin eliminar discrepancias |
| contacto_emergencia_nombre, contacto_emergencia_parentesco, contacto_emergencia_celular, contacto_emergencia_direccion, responsable_principal, autorizado_informacion_medica | persons; person_contacts.relationship,is_responsible,is_emergency,can_receive_clinical_info | Vincular contacto solo con evidencia; autorización queda pendiente sin consentimiento validado |
| consentimiento_datos | consents.decision,scope,verification_status | Importar declaración no verificada, sin inventar firmante ni fecha de firma |
| cod_est_adul | resident_status_changes.next_status,dimension | Separar catálogo mixto por dimensión según tabla de equivalencias aprobada |
| foto | persons.photo_document_id; documents; document_versions | Archivo privado |
| cod_habitacion, cod_cama | bed_assignments.bed_id | Conciliar con asignaciones: no sobrescribir conflicto; habitación se deriva de cama |
| created_at | persons.created_at, person_identifiers.created_at, residents.created_at, person_contacts.created_at, consents.created_at, admissions.created_at, bed_assignments.created_at, allergies.created_at, resident_status_changes.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | persons.updated_at, person_identifiers.updated_at, residents.updated_at, person_contacts.updated_at, consents.updated_at, admissions.updated_at, bed_assignments.updated_at, allergies.updated_at, resident_status_changes.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### alertas_adulto → alerts + alert_actions

Clasificación: **DIVIDIR**. Riesgos, atención y cierre actuales.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_alerta | migration_links.source_key → target_key; alerts.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, cod_turno, origen, tipo_alerta, nivel, motivo, responsable_id, estado | alerts.person_id,shift_id,origin,type,severity,reason,assigned_to,status | Estados normalizados; no inferir motor experto desde origen libre |
| accion_tomada, fecha_atencion, atendido_por, fecha_cierre, cerrado_por, observacion_cierre | alert_actions.description,occurred_at,recorded_by,action_type,notes | Crear eventos atendida/cerrada; evitar duplicar acciones_alerta idénticas solo tras conciliación |
| created_at | alerts.created_at, alert_actions.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | alerts.updated_at, alert_actions.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### areas_geriatricas → assessment_domains

Clasificación: **CONSERVAR**. Dimensiones de VGI.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_area | migration_links.source_key → target_key; assessment_domains.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombre, descripcion, estado | assessment_domains.name,description,active | Catálogo independiente de áreas laborales |
| created_at | assessment_domains.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | assessment_domains.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### areas_institucionales → areas + staff_assignments

Clasificación: **MODIFICAR**. Unidades con responsable y presentación.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_area | migration_links.source_key → target_key; areas.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombre, slug, tipo_area, descripcion, color, icono, estado, orden, observaciones, imagen_area, deleted_at | areas.name,slug,kind,description,display,active,archived_at | Display conserva tokens/icono/orden y referencia visual; observaciones en description diferenciadas |
| responsable_id | staff_assignments.person_id,is_area_lead | Inicio desconocido queda inferido y documentado, no inventar antigüedad |
| roles_sugeridos, modulos_relacionados | migration_links.encrypted_source_payload | Desaparecen acoplamientos operativos; navegación deriva permisos, sugerencias revisadas en configuración |
| creado_por | areas.recorded_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |
| actualizado_por | areas.updated_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |
| created_at | areas.created_at, staff_assignments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | areas.updated_at, staff_assignments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### asignacion_adulto_mayor → bed_assignments

Clasificación: **MODIFICAR**. Ubicación del adulto y registro de asignación.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_asig_adulto | migration_links.source_key → target_key; bed_assignments.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, cod_cama, fecha_asignacion, hora_asignacion, estado, observaciones | bed_assignments.admission_id,bed_id,starts_at,status,reason | Fecha fin solo siguiente asignación/alta documentada; conflictos quedan en cuarentena |
| cod_habitacion | migration_links.encrypted_source_payload | Desaparece redundancia; comprobar cama pertenece a habitación |
| registrado_por | bed_assignments.recorded_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |
| created_at | bed_assignments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | bed_assignments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### asignacion_voluntarios → volunteer_assignments

Clasificación: **MODIFICAR**. Programa colaboración con residente.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_asig_vol | migration_links.source_key → target_key; volunteer_assignments.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_vol, cod_am, fecha_asig, fecha_fin, estado, obser | volunteer_assignments.volunteer_profile_id,resident_id,starts_at,ends_at,status,notes | Hora desconocida marcada en procedencia; no asumir actividad colectiva |
| created_at | volunteer_assignments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | volunteer_assignments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### asignaciones_plazas_enfermeria → staffing_slots + staffing_slot_assignments

Clasificación: **DIVIDIR**. Asigna personas a plazas rotativas.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id | migration_links.source_key → target_key; staffing_slots.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| plaza | staffing_slots.code | Normalizar sin unir plazas distintas |
| cod_usu, tipo, fecha, motivo | staffing_slot_assignments.staff_assignment_id,kind,starts_on,reason | Fin deducible solo con siguiente reemplazo inequívoco |
| created_at | staffing_slots.created_at, staffing_slot_assignments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | staffing_slots.updated_at, staffing_slot_assignments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### asignaciones_turno_adulto → care_assignments

Clasificación: **MODIFICAR**. Responsable de residente por turno.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_asig_turno | migration_links.source_key → target_key; care_assignments.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, cod_turno, cod_usu_enfermero, fecha_inicio, fecha_fin, nivel_supervision, estado, motivo_asignacion, asignado_por | care_assignments.admission_id,roster_assignment_id,staff_assignment_id,starts_at,ends_at,supervision_level,status,reason,recorded_by | Turno de catálogo se combina con fecha para roster; si no es inequívoco roster nulo y origen conservado |
| cod_habitacion, cod_cama | migration_links.encrypted_source_payload | Ubicación histórica de asignación se conserva como evidencia; no crea ocupación contradictoria |
| created_at | care_assignments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | care_assignments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### asistencia_voluntarios → volunteer_attendances

Clasificación: **MODIFICAR**. Entradas/salidas y ausencias.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_asis_vol | migration_links.source_key → target_key; volunteer_attendances.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_vol, cod_am, fecha, hora_entrada, hora_salida, actividad_realizada, novedades_observaciones, estado, observaciones | volunteer_attendances.volunteer_profile_id,resident_id,occurred_on,arrived_at,departed_at,work_description,notes,outcome | FK asignación solo coincidencia inequívoca; ausencia sin hora no se rellena; duplicados se revisan |
| created_at | volunteer_attendances.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | volunteer_attendances.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### atenciones_adulto → encounters

Clasificación: **MODIFICAR**. Atención tipificada por residente.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_aten_adul | migration_links.source_key → target_key; encounters.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, cod_tipo_aten, fecha, hora, observacion, estado, registrado_por | encounters.person_id,encounter_type_id,occurred_at,notes,status,recorded_by | No inferir profesional desde tipo de atención |
| created_at | encounters.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | encounters.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### cache → cache

Clasificación: **CONSERVAR**. Caché.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| key, value, expiration | cache (contrato de 03) | Descartar valores de caché derivados y regenerar. |

### cache_locks → cache_locks

Clasificación: **CONSERVAR**. Locks de caché.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| key, owner, expiration | cache_locks (contrato de 03) | No trasladar locks activos; recrear vacía tras detener trabajadores. |

### camas → beds

Clasificación: **MODIFICAR**. Inventario físico de camas.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_cama | migration_links.source_key → target_key; beds.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_habitacion, codigo, estado, observaciones, deleted_at, observacion, numero | beds.room_id,code,operational_status,notes,number,archived_at | Ocupada sale del estado físico; conflicto notas preservado |
| created_at | beds.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | beds.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### disponibilidad_voluntarios → volunteer_availabilities

Clasificación: **MODIFICAR**. Horario semanal de colaboración.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_hor_vol | migration_links.source_key → target_key; volunteer_availabilities.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_vol, dia_semana, hora_inicio, hora_fin, estado, obser, observaciones | volunteer_availabilities.volunteer_profile_id,weekday,starts_at,ends_at,status,notes | ISO día; observaciones distintas se conservan rotuladas; vigencia importada no inventa antigüedad |
| created_at | volunteer_availabilities.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | volunteer_availabilities.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### documentos_adulto_mayor → documents + document_versions + document_events

Clasificación: **FUSIONAR**. Archivos del expediente y recetas.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_doc_am | migration_links.source_key → target_key; documents.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, nombre, tipo_documento, estado, observaciones, modulo_ref | documents.person_id,title,document_type_id,status,notes,module_origin | Tipos libres se catalogan conservando texto |
| ruta_archivo, fecha_subida | document_versions.storage_path,uploaded_at | Archivo comprobado hash, faltante bloquea integridad documental pero conserva referencia |
| created_at | documents.created_at, document_versions.created_at, document_events.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | documents.updated_at, document_versions.updated_at, document_events.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### documentos_preadmision → documents + document_versions + document_events + document_types

Clasificación: **FUSIONAR**. Checklist, archivos y generación inicial.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_doc_pre | migration_links.source_key → target_key; documents.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_pre, tipo_documento, nombre_documento, estado, observaciones, fecha_limite_entrega | documents.admission_case_id,document_type_id,title,status,notes,due_at | Mismo documento puede enlazar persona del caso tras aprobación |
| archivo_path, nombre_original, es_generado_sistema, fecha_generacion | document_versions.storage_path,original_name,generated,uploaded_at | Si sin archivo queda requisito documents sin versión; generación no prueba entrega |
| es_institucional, obligatorio, grupo_documento, bloquea_avance, permite_48h | document_types.contexts,required,blocks_progress,grace_hours | Crear variante de tipo si requisitos del caso difieren; guardar snapshot original en procedencia |
| created_at | documents.created_at, document_versions.created_at, document_events.created_at, document_types.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | documents.updated_at, document_versions.updated_at, document_events.updated_at, document_types.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### documentos_usuarios → documents + document_versions + document_events

Clasificación: **FUSIONAR**. Expediente documental del usuario.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_doc_usu | migration_links.source_key → target_key; documents.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_usu, cod_tipo_doc, observaciones, estado, deleted_at, tipo_documento, nombre_documento | documents.person_id,document_type_id,title,notes,status,archived_at | Tipo por FK tiene precedencia solo si texto compatible; discrepancia revisada |
| archivo_path, nombre_original, fecha_subida, fecha_vencimiento, archivo, mime_type, extension, tamanio, fecha_emision, subido_por, reemplaza_a | document_versions.storage_path,original_name,uploaded_at,expires_on,mime_type,extension,byte_size,issued_on,recorded_by,supersedes_id | Comparar rutas y checksum; reemplaza_a define cadena solo si acíclica y mismo titular; copiar privados sin sobrescribir |
| validado_por, fecha_validacion, motivo_observacion | document_events.recorded_by,occurred_at,reason,event_type | Evento validación/observación; fecha desconocida se marca, no se firma retroactivamente |
| created_at | documents.created_at, document_versions.created_at, document_events.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | documents.updated_at, document_versions.updated_at, document_events.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| creado_por | documents.recorded_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |
| actualizado_por | documents.updated_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |

### estado_adulto → resident_status_changes + admissions + admission_cases

Clasificación: **REEMPLAZAR**. Catálogo que mezcla flujo y condición del adulto.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_est_adul | migration_links.source_key; diccionario de equivalencias a enums por dimensión | Retirar PK del catálogo operativo; conservar código/etiqueta como equivalencia. No crear transiciones ni estancias desde una fila de catálogo: los hechos provienen de residentes, casos e historial. target_key puede ser nulo para esta disposición. |
| estado | migration_links.encrypted_source_payload; enums por dimensión | No nueva tabla de estados mezclados; conservar equivalencias de todos los códigos |

### evaluaciones_geriatricas → assessments

Clasificación: **MODIFICAR**. Resultado y formulario de VGI.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_eval_ger | migration_links.source_key → target_key; assessments.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, cod_instrumento, fecha_eval, observaciones, hora_eval, datos_formulario | assessments.person_id,instrument_version_id,evaluated_at,answers,notes | Parsear JSON válido; texto no parseable queda notas y procedencia |
| puntaje, resultado_cualitativo, puntaje_total, categoria_resultado, nivel_alerta, nivel_riesgo | assessments.score,interpretation,risk_level | Si espejo coincide, colapsar; si difiere, bloquear revisión clínica; riesgo no sobreescribe interpretación |
| evaluador_id, registrado_por | assessments.recorded_by,evaluator_id | Si distintos preservar ambas atribuciones y resolver; no usar rol como acreditación |
| estado, deleted_at, estado_eval, motivo_anulacion, anulado_por, anulado_en | assessments.status,voided_at,void_reason,voided_by | Separar estado de documento y riesgo; anulador desde anulado_por, nunca evaluador_id |
| created_at | assessments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | assessments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### failed_jobs → failed_jobs

Clasificación: **CONSERVAR**. Fallos de trabajos.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, uuid, connection, queue, payload, exception, failed_at | failed_jobs (contrato de 03) | Archivar para análisis restringido; no reintentar jobs antiguos sin adaptación. |

### familiar_adulto → person_contacts

Clasificación: **MODIFICAR**. Vínculo con responsabilidad específica.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id | migration_links.source_key → target_key; person_contacts.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_fam, cod_am, parentesco_vinculo, es_responsable, estado, observaciones, deleted_at | person_contacts.contact_person_id,person_id,relationship,is_responsible,ends_at,notes | Pivot es fuente contextual preferente; deleted_at/estado finalizan vínculo, no borran persona |
| created_at | person_contacts.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | person_contacts.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### familiares → persons + person_identifiers + person_contacts

Clasificación: **FUSIONAR**. Identidad del contacto con cuenta opcional.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_fam | migration_links.source_key → target_key; persons.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombres, ap_paterno, ap_materno, telefono, celular, correo, direccion, zona | persons.given_names,paternal_name,maternal_name,phone,mobile,email,address,zone | Unificar con usuario solo por enlace cod_usu y contraste de identificadores |
| ci | person_identifiers.number | Mantener fuente y verificar |
| parentesco_vinculo, es_responsable, estado, observaciones | person_contacts.relationship,is_responsible,notes; persons.active | Parentesco global solo respaldo si vínculo no tiene valor; si varios residentes no propagar automáticamente |
| cod_usu | users.person_id | Resolver cuenta ya existente, no crear duplicada |
| created_at | persons.created_at, person_identifiers.created_at, person_contacts.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | persons.updated_at, person_identifiers.updated_at, person_contacts.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### ficha_medica_adulto → encounters + clinical_notes + diagnoses + allergies

Clasificación: **DIVIDIR**. Antecedentes y lista fija de enfermedades.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_ficha_medica | migration_links.source_key → target_key; encounters.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, registrado_por | encounters.person_id,recorded_by | Atención inicial importada con fecha fuente marcada inferida si solo created_at |
| hipertension, diabetes, problemas_cardiacos, acv, parkinson, epilepsia, alzheimer_diagnosticado, depresion, ansiedad, problemas_sueno, problemas_visuales, problemas_auditivos, dolor_cronico | diagnoses.description,certainty,clinical_status | True → condición referida con etiqueta exacta; false → formulario original, no negación confirmada |
| alergias | allergies.substance,verification_status | Conciliar fuente con adulto; mantener discrepancias |
| restricciones_alimentarias, hospitalizaciones, cirugias, observacion_medica | clinical_notes.narrative | Secciones nombradas conservadas; restricción clínica no se confunde con alergia |
| estado, deleted_at | clinical_notes.status,voided_at; diagnoses.status; allergies.status | Archivado histórico no equivale a enfermedad resuelta; marcar procedencia |
| created_at | encounters.created_at, clinical_notes.created_at, diagnoses.created_at, allergies.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | encounters.updated_at, clinical_notes.updated_at, diagnoses.updated_at, allergies.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### habitaciones → rooms

Clasificación: **MODIFICAR**. Espacios, capacidad y ubicación.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_habitacion | migration_links.source_key → target_key; rooms.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| codigo, nombre, tipo_habitacion, capacidad, estado, observaciones, deleted_at, ubicacion, observacion, piso, descripcion | rooms.code,name,room_type,licensed_capacity,active,notes,location,floor,description,archived_at | Si observacion/observaciones difieren conservar ambos rotulados; capacidad autorizada no se recalcula como camas |
| created_at | rooms.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | rooms.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### historial_estado_adulto → resident_status_changes

Clasificación: **MODIFICAR**. Transiciones con motivo y soporte.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_hist_estado | migration_links.source_key → target_key; resident_status_changes.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, estado_anterior, estado_nuevo, fecha_cambio, motivo, documento_respaldo, cambiado_por, observacion | resident_status_changes.resident_id,previous_status,next_status,occurred_at,reason,evidence_version_id,recorded_by | Conciliar dimensión por estado; observación se agrega a reason rotulada; documento enlaza versión |
| created_at | resident_status_changes.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | resident_status_changes.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### horarios_personal_admin → staff_schedules + staff_assignments

Clasificación: **FUSIONAR**. Horarios de personal administrativo.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_hor_per_admin | migration_links.source_key → target_key; staff_schedules.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| dia_semana, hora_inicio, hora_fin, turno, estado, observaciones, cod_usu | staff_schedules.weekday,start_time,end_time,shift_id,valid_until,notes,staff_assignment_id | Deserializar observaciones {tipo_asignacion,observaciones}; horario vigente por fechas explícitas inferidas y marcadas; turno reconciliado por horas. |
| created_at | staff_schedules.created_at, staff_assignments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | staff_schedules.updated_at, staff_assignments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| cod_per_adm | migration_links.encrypted_source_payload | Desaparece alias sin modelo; cod_usu es referencia real, discordancia se revisa. |

### horarios_personal_salud → staff_schedules + staff_assignments

Clasificación: **FUSIONAR**. Horarios de personal de salud.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_hor_per_sal | migration_links.source_key → target_key; staff_schedules.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| dia_semana, hora_inicio, hora_fin, turno, estado, observaciones, cod_usu | staff_schedules.weekday,start_time,end_time,shift_id,valid_until,notes,staff_assignment_id | Deserializar observaciones {tipo_asignacion,observaciones}; horario vigente por fechas explícitas inferidas y marcadas; turno reconciliado por horas. |
| created_at | staff_schedules.created_at, staff_assignments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | staff_schedules.updated_at, staff_assignments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| cod_per_sal | migration_links.encrypted_source_payload | Desaparece alias sin modelo; cod_usu es referencia real, discordancia se revisa. |

### instrumentos_geriatricos → assessment_instruments + instrument_versions

Clasificación: **DIVIDIR**. Catálogo, límites y cortes en registro editable.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_instrumento | migration_links.source_key → target_key; assessment_instruments.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_area, nombre, siglas, estado | assessment_instruments.assessment_domain_id,name,acronym,active | Mapear dimensiones |
| tipo_resultado, puntaje_maximo, punto_corte_normal, punto_corte_riesgo, descripcion | instrument_versions.result_type,max_score,interpretation_rules,source_reference | Versión legacy: cortes observados no aseguran los usados históricamente; no recalificar retrospectivamente |
| created_at | assessment_instruments.created_at, instrument_versions.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | assessment_instruments.updated_at, instrument_versions.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### job_batches → job_batches

Clasificación: **CONSERVAR**. Lotes de trabajos.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at | job_batches (contrato de 03) | Archivar metadata y enlazar lotes; no reanudar contadores como trabajos ejecutables. |

### jobs → jobs

Clasificación: **CONSERVAR**. Cola pendiente.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, queue, payload, attempts, reserved_at, available_at, created_at | jobs (contrato de 03) | Drenar/pausar colas; no reproducir payload serializado de modelos viejos. Reencolar solo trabajos pendientes conciliados, sin duplicar correos. |

### medicacion_adulto → prescriptions + prescription_items

Clasificación: **DIVIDIR**. Orden y pauta individual con prescriptor textual.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_med_adulto | migration_links.source_key → target_key; prescriptions.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, medico_indica, documento_receta, registrado_por | prescriptions.person_id,external_prescriber,evidence_version_id,recorded_by | No inferir credencial del texto; una cabecera por fila inicialmente |
| nombre_medicamento, dosis, frecuencia, via_administracion, hora_programada, fecha_inicio, fecha_fin, estado, observacion, deleted_at | prescription_items.medication_name,dose_text,frequency_text,route,schedule,starts_at,ends_at,clinical_status; prescriptions.notes,status,voided_at | Parsear pauta solo si inequívoca; mantener texto; no habilitar generación de dosis hasta conciliación |
| created_at | prescriptions.created_at, prescription_items.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | prescriptions.updated_at, prescription_items.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### migrations → migrations

Clasificación: **CONSERVAR**. Control del esquema.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, migration, batch | migrations (contrato de 03) | Conservar contrato, pero inicializar con migraciones nuevas realmente aplicadas. Lista anterior en manifiesto de migración, nunca simular batches. |

### model_has_permissions → model_has_permissions

Clasificación: **CONSERVAR**. Permisos directos.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| permission_id, model_type, model_id | model_has_permissions (contrato de 03) | Remapear IDs y revisar cada concesión directa; no usar ausencia de permiso directo como denegación. |

### model_has_roles → model_has_roles

Clasificación: **CONSERVAR**. Roles asignados.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| role_id, model_type, model_id | model_has_roles (contrato de 03) | Remapear model_id/model_type y role_id; comprobar asignaciones efectivas contra matriz firmada. |

### notas_evolucion_medica → encounters + clinical_notes + vital_signs

Clasificación: **DIVIDIR**. SOAP/interconsulta y signos copiados.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_nota | migration_links.source_key → target_key; encounters.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, fecha, hora, registrado_por | encounters.person_id,occurred_at,recorded_by | Una atención importada por nota salvo enlace probado |
| tipo_nota, subjetivo, objetivo, valoracion, plan, observaciones | clinical_notes.kind,subjective,objective,assessment,plan,narrative | Preservar SOAP e interconsulta; referencia a encuentro |
| pa_sistolica, pa_diastolica, fc, fr, temperatura, saturacion, glucosa, peso | vital_signs.systolic,diastolic,heart_rate,respiratory_rate,temperature_c,oxygen_percent,glucose_mg_dl,weight_kg; clinical_notes.vital_sign_id | No deduplicar mediciones distintas solo por hora; coincidencias exactas revisadas |
| estado, motivo_anulacion, anulado_por, anulado_en, deleted_at | clinical_notes.status,void_reason,voided_by,voided_at | Preservar anulado aunque soft delete no se aplique en modelo |
| created_at | encounters.created_at, clinical_notes.created_at, vital_signs.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | encounters.updated_at, clinical_notes.updated_at, vital_signs.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### obs_adulto → daily_observations

Clasificación: **FUSIONAR**. Observación de seguimiento con riesgo.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_obs_adul | migration_links.source_key → target_key; daily_observations.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, cod_est_adul, fecha, estado, deleted_at | daily_observations.person_id,observed_at,status,voided_at; migration_links.encrypted_source_payload | Estado adulto es contexto histórico en payload, no estado de observación |
| creado_por, observacion, categoria, nivel_riesgo, tipo_obs, descripcion, registrado_por, nivel_importancia | daily_observations.recorded_by,narrative,category,kind,importance,structured_data | Colapsar espejos iguales; conflicto preservado y revisado |
| created_at | daily_observations.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | daily_observations.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### pases_turno → handovers

Clasificación: **MODIFICAR**. Resumen y recepción por turno.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_pase | migration_links.source_key → target_key; handovers.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, turno_saliente_id, turno_entrante_id, enfermero_saliente_id, enfermero_entrante_id, fecha, estado_general_cierre, resumen_turno, recomendacion_siguiente_turno, requiere_vigilancia_especial, motivo_vigilancia, estado, fecha_recibido | handovers.admission_id,outgoing_shift_id,incoming_shift_id,outgoing_staff_id,incoming_staff_id,occurred_at,general_status,summary,recommendations,special_watch,watch_reason,status,received_at | Jornadas se resuelven por fecha+persona+turno; receptor sin evidencia no se fabrica |
| tareas_realizadas_json, tareas_pendientes_json, alertas_activas_json | handovers.snapshot | Preservar snapshot fechado, traducir referencias con mapa cuando existan, no ejecutar tareas a partir del JSON |
| created_at | handovers.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | handovers.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### password_reset_tokens → password_reset_tokens

Clasificación: **CONSERVAR**. Recuperación de contraseña.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| email, token, created_at | password_reset_tokens (contrato de 03) | No migrar tokens válidos: invalidar y permitir nueva solicitud. email conserva contrato Fortify. |

### permissions → permissions

Clasificación: **CONSERVAR**. Capacidades Spatie.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, name, guard_name, created_at, updated_at | permissions (contrato de 03) | Crear catálogo canónico y aliases temporales de 07; permisos antiguos sin destino quedan pendientes, no se conceden implícitamente. |

### personal_access_tokens → personal_access_tokens

Clasificación: **CONSERVAR**. Tokens Sanctum.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at | personal_access_tokens (contrato de 03) | Revocar tokens vigentes al corte; conservar registro de revocación y migrar identidad del titular solo si necesaria para auditoría. |

### planes_cuidado → care_plans + care_plan_versions

Clasificación: **DIVIDIR**. Plan activo, nivel y número de versión.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_plan | migration_links.source_key → target_key; care_plans.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, tipo_plan, estado | care_plans.resident_id,kind,status | Agrupar versiones solo con evidencia; cada fila raíz separada si linaje desconocido |
| version, nivel_cuidado, origen, resumen, fecha_inicio, fecha_fin, creado_por, validado_por, deleted_at | care_plan_versions.version,care_level,origin,summary,starts_at,ends_at,recorded_by,validated_by,voided_at | Si no fecha firma dejar validated_at nulo con marca de legado |
| created_at | care_plans.created_at, care_plan_versions.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | care_plans.updated_at, care_plan_versions.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### preadmisiones → persons + person_identifiers + person_contacts + admission_cases + admission_decisions

Clasificación: **DIVIDIR**. Solicitud, familiar, asignación y aprobación.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_pre | migration_links.source_key → target_key; persons.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| estado, fecha_solicitud, fecha_asignacion, motivo_ingreso, procedencia_ingreso, tipo_ingreso, permanencia, prioridad, descripcion_caso, enfermero_asignado, observaciones | admission_cases.status,requested_at,assigned_at,reason,origin,admission_type,permanence,priority,description,assigned_staff_id; migration_links.encrypted_source_payload | Observaciones forman parte de description con separador de procedencia; enfermero → vínculo laboral revisado |
| nombres, ap_paterno, ap_materno, fecha_nac, genero, estado_civil, telefono, celular, departamento_residencia, ciudad_municipio, zona, calle, direccion_referencia | persons.given_names,paternal_name,maternal_name,birth_date,sex,civil_status,phone,mobile,department,city,zone,street,address_reference | Identidad única con snapshot original protegido; rechazado también conserva persona |
| ci, expedicion_ci | person_identifiers.number,issued_at | No crear identidad adicional si cod_am_generado coherente |
| familiar_nombres, familiar_ap_paterno, familiar_ap_materno, familiar_ci, familiar_parentesco, familiar_celular, familiar_correo, familiar_direccion, cod_fam_generado | persons; person_identifiers; person_contacts; admission_cases.contact_person_id | Usar enlace explícito, comparar datos, no inferir responsable legal |
| documentos_iniciales_completos, documentos_institucionales_generados | migration_links.encrypted_source_payload | Desaparecen booleanos operativos; recalcular checklist desde documents/versions |
| creado_por | persons.recorded_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |
| created_at | persons.created_at, person_identifiers.created_at, person_contacts.created_at, admission_cases.created_at, admission_decisions.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | persons.updated_at, person_identifiers.updated_at, person_contacts.updated_at, admission_cases.updated_at, admission_decisions.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| motivo_rechazo, observacion_rechazo, fecha_rechazo, rechazado_por, fecha_aprobacion, aprobado_por | admission_decisions.reason,notes,decided_at,recorded_by,decision | Crear decisiones separadas si ambas existen; no perder actor |
| cod_am_generado | admissions.admission_case_id; residents.person_id | Conciliar contra cod_pre_origen; una estancia por caso; discrepancia bloquea |

### role_has_permissions → role_has_permissions

Clasificación: **CONSERVAR**. Permisos del rol.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| permission_id, role_id | role_has_permissions (contrato de 03) | Recrear según matriz aprobada, comparar diferencias y registrar aprobación. |

### roles → roles

Clasificación: **CONSERVAR**. Roles Spatie.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, name, guard_name, created_at, updated_at | roles (contrato de 03) | Mapear nombres a plantillas aprobadas de 07; no trasladar privilegios excesivos por defecto. |

### seguimientos_diarios → daily_observations + incidents

Clasificación: **FUSIONAR**. Observación estructurada del turno.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_seg_diario | migration_links.source_key → target_key; daily_observations.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, cod_turno, cod_plan, registrado_por, fecha, hora_inicio, hora_fin, requiere_medico, observacion | daily_observations.person_id,shift_id,care_plan_version_id,recorded_by,observed_at,ended_at,narrative,requires_clinician | kind=turno, preservar unicidad vigente por persona/turno/día |
| estado_general, alimentacion, porcentaje_alimentacion, hidratacion, movilidad, intento_caminar_solo, higiene, sueno, orientacion, repite_preguntas, confusion_observable, conducta, participacion, incidente | daily_observations.structured_data; incidents | JSON con mismas claves; incidente solo crea hecho si detalle suficiente validado, si no incidencia de calidad |
| created_at | daily_observations.created_at, incidents.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | daily_observations.updated_at, incidents.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### sessions → sessions

Clasificación: **CONSERVAR**. Sesiones web.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| id, user_id, ip_address, user_agent, payload, last_activity | sessions (contrato de 03) | No transferir payload de sesión al corte: cerrar sesiones. Recrear esquema y remapear user_id solo para archivo de auditoría autorizado. |

### signos_vitales_adulto → vital_signs

Clasificación: **MODIFICAR**. Serie temporal de signos.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_signo | migration_links.source_key → target_key; vital_signs.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, fecha, hora, presion_sistolica, presion_diastolica, frecuencia_cardiaca, frecuencia_respiratoria, temperatura, saturacion, glucosa, peso, talla, dolor, observacion | vital_signs.person_id,measured_at,systolic,diastolic,heart_rate,respiratory_rate,temperature_c,oxygen_percent,glucose_mg_dl,weight_kg,height_cm,pain_score,notes | Combinar fecha/hora en zona centro; unidades se verifican |
| presion_arterial, imc | migration_links.encrypted_source_payload | Presión textual solo fallback parseable y coherente; IMC se recalcula y diferencia se reporta |
| registrado_por | vital_signs.recorded_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |
| estado, motivo_anulacion, anulado_por, fecha_anulacion | vital_signs.status,void_reason,voided_by,voided_at | No ocultar anulación en trazabilidad |
| created_at | vital_signs.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | vital_signs.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### tareas_plan_cuidado → care_tasks + care_task_executions

Clasificación: **DIVIDIR**. Definición, programación y ejecución de tarea.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_tarea | migration_links.source_key → target_key; care_tasks.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_plan, cod_turno, responsable_id, area, titulo, descripcion, frecuencia, fecha_programada, hora_programada, prioridad, estado, observacion, transferida_a_turno_id, registrado_por | care_tasks.care_plan_version_id,shift_id,assigned_staff_id,area_id,title,description,frequency,scheduled_at,priority,status,transferred_to_shift_id,recorded_by,notes | Resolver área textual sin inventar; programación y asignación separadas |
| cod_am | migration_links.encrypted_source_payload | Comprobar residente contra plan; eliminar FK redundante |
| fecha_realizada, resultado, motivo_omision | care_task_executions.occurred_at,result,omission_reason,outcome | Crear ejecución solo si evidencia de realizada/omitida; programada no equivale ejecutada |
| created_at | care_tasks.created_at, care_task_executions.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | care_tasks.updated_at, care_task_executions.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### tipo_actividades_adulto → activity_types

Clasificación: **CONSERVAR**. Catálogo usado por actividad y reportes.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_tipo_act | migration_links.source_key → target_key; activity_types.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombre, descripcion, estado | activity_types.name,description,active | Conservar catálogo |
| created_at | activity_types.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | activity_types.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### tipo_atenciones_adulto → encounter_types

Clasificación: **CONSERVAR**. Catálogo de atenciones.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_tipo_aten | migration_links.source_key → target_key; encounter_types.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombre, descripcion, estado | encounter_types.name,description,active | Conservar catálogo |
| created_at | encounter_types.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | encounter_types.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### tipos_documentos_usuario → document_types

Clasificación: **MODIFICAR**. Requisitos documentales por roles.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_tipo_doc | migration_links.source_key → target_key; document_types.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombre, descripcion, aplica_roles, obligatorio, requiere_vencimiento, requiere_validacion, estado, orden, deleted_at | document_types.name,description,role_requirements,required,requires_expiry,requires_validation,active,display_order,archived_at | Traducir roles mediante matriz aprobada; conservar requisitos originales |
| created_at | document_types.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | document_types.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### turnos_enfermeria → shifts

Clasificación: **FUSIONAR**. Catálogo horario asistencial.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_turno | migration_links.source_key → target_key; shifts.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombre, hora_inicio, hora_fin, orden, estado, observacion | shifts.name,starts_at,ends_at,display,active | Orden y observación en display; conservar códigos en mapa |
| created_at | shifts.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | shifts.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### turnos_institucionales → shifts

Clasificación: **FUSIONAR**. Catálogo de horario institucional.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_turno | migration_links.source_key → target_key; shifts.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombre, hora_inicio, hora_fin, estado, descripcion, color, observaciones, deleted_at | shifts.name,starts_at,ends_at,active,display,archived_at | Unir catálogo por centro+horas+cruce medianoche revisado, no por nombre |
| creado_por | shifts.recorded_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |
| actualizado_por | shifts.updated_by | Remapear User; conservar autor en derivados del mismo hecho. Nulo histórico no se sustituye por profesional inventado. |
| created_at | shifts.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | shifts.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### users → persons + person_identifiers + users + professionals + staff_assignments + person_contacts

Clasificación: **DIVIDIR**. Cuenta, identidad y adaptación laboral virtual.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_usu | migration_links.source_key → target_key; persons.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombres, ap_paterno, ap_materno, telefono, pais_telefono, codigo_telefono, genero, fecha_nacimiento, observaciones, direccion, zona, ciudad, calle, nro_domicilio | persons.given_names,paternal_name,maternal_name,birth_date,sex,phone,country,address,zone,city,street,house_number; migration_links.encrypted_source_payload | Separar identidad de acceso; prefijo telefónico se integra a phone; observaciones libres se conservan sin inferir formación |
| pais_documento, tipo_documento, numero_documento, expedido | person_identifiers.country,type,number,issued_at | Identidad verificada antes de unificar |
| correo, email_verified_at, password, estado, acceso_sistema, ultimo_acceso, debe_cambiar_password, password_changed_at, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at, remember_token | users.email,email_verified_at,password,last_login_at,must_change_password,password_changed_at,two_factor_secret,two_factor_recovery_codes,two_factor_confirmed_at,remember_token,access_enabled,status | Mantener hash; convertir SI/NO; invalidar sesiones/tokens en corte; conservar cifrado 2FA solo con clave compatible o recuperación controlada |
| foto_de_perfil | persons.photo_document_id; documents; document_versions | Copiar binario a almacenamiento privado y enlazar |
| contacto_emergencia, parentesco_emergencia, celular_emergencia, ap_paterno_emergencia, ap_materno_emergencia | persons; person_contacts | Crear contacto identificado con fuente, sin cuenta obligatoria |
| tipo_vinculacion, cod_area | staff_assignments.linkage_type,area_id | Fecha laboral desconocida no se presume created_at salvo marca estimada; cargo/profesión requieren validación |
| current_team_id | migration_links.encrypted_source_payload | Desaparece del modelo operativo: no existen teams; cualquier valor no nulo se revisa antes del corte |
| created_at | persons.created_at, person_identifiers.created_at, users.created_at, professionals.created_at, staff_assignments.created_at, person_contacts.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | persons.updated_at, person_identifiers.updated_at, users.updated_at, professionals.updated_at, staff_assignments.updated_at, person_contacts.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### valoracion_enfermeria_admision → assessments + vital_signs

Clasificación: **REEMPLAZAR**. Valoración inicial asociada a caso o adulto.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_val_enf | migration_links.source_key → target_key; assessments.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, fecha_valoracion, hora_valoracion, estado, registrado_por, cod_pre | assessments.person_id,admission_case_id,evaluated_at,recorded_by,status | Sujeto puede ser persona aún no residente; ambos enlaces deben concordar |
| estado_general, nivel_conciencia, orientacion, comunicacion, hay_dolor, intensidad_dolor, ubicacion_dolor, movilidad, apoyo_movilidad, riesgo_caida, piel_estado, hay_heridas, ubicacion_heridas, higiene_ingreso, continencia_basica, alimentacion_aparente, observacion, recomendacion_enfermeria | assessments.answers,notes,risk_level | Esquema legacy ingreso; extraer JSON y Persona/Tiempo/Espacio sin defaults falsos; conservar texto original |
| signos_vitales_iniciales | vital_signs; assessments.vital_sign_id | JSON válido → medición con unidades; texto ambiguo permanece respuesta legacy y revisión |
| created_at | assessments.created_at, vital_signs.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | assessments.updated_at, vital_signs.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

### valoracion_funcional_adulto → assessments

Clasificación: **FUSIONAR**. Dependencia, ayudas, riesgo y Barthel.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_val_func | migration_links.source_key → target_key; assessments.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| cod_am, fecha_valoracion, nivel_dependencia, observacion, registrado_por, riesgo_caida, indice_barthel | assessments.person_id,evaluated_at,recorded_by,score,interpretation,risk_level,notes | Instrumento legacy funcional; si Barthel existe preservar total, no inventar respuestas |
| come_solo, se_bana_solo, se_viste_solo, va_bano_solo, camina_solo, usa_baston, usa_andador, usa_silla_ruedas, baja_vision, baja_audicion, dificultad_hablar, molestia_luz, molestia_ruido, se_asusta_facil, necesita_supervision | assessments.answers | Cada clave conserva valor y tipo bajo versión legacy-funcional |
| created_at | assessments.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | assessments.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| estado, motivo_anulacion, anulado_por, fecha_anulacion | assessments.status,void_reason,voided_by,voided_at | Historial conservado |

### voluntarios → persons + person_identifiers + volunteer_profiles

Clasificación: **DIVIDIR**. Identidad y vinculación voluntaria.

| Columnas origen | Columnas / estructura destino | Transformación y columnas que desaparecen |
|---|---|---|
| cod_vol | migration_links.source_key → target_key; persons.legacy_code | PK nueva independiente; conservar código origen. Para tablas divididas se generan varias correspondencias. |
| nombres, ap_paterno, ap_materno, celular, correo, fecha_nac | persons.given_names,paternal_name,maternal_name,mobile,email,birth_date | Conciliar cod_usu y no agrupar por nombre |
| ci | person_identifiers.number | Verificación manual de colisiones |
| profesion_ocupacion, estado, observaciones, fecha_ing, disponibilidad_inicial, area_apoyo_preferente, archivado_en | volunteer_profiles.occupation,active,notes,joined_on,initial_availability,preferred_area_id,archived_at | Área textual solo se enlaza si catálogo inequívoco; ocupación no acredita |
| cod_usu | users.person_id | Enlace a persona sin cambiar permisos por perfil |
| created_at | persons.created_at, person_identifiers.created_at, volunteer_profiles.created_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |
| updated_at | persons.updated_at, person_identifiers.updated_at, volunteer_profiles.updated_at | Preservar timestamp de fuente en filas derivadas con marca de importación; no usar como firma clínica. |

## Datos nuevos sin tabla fuente

| Destino | Construcción inicial |
|---|---|
| institutions | Centro real validado, zona horaria y datos institucionales confirmados; no inferir multisedes |
| professions, specialties, positions, professionals, professional_qualifications | Catálogos y acreditaciones revisados por administración; roles antiguos solo generan candidatos, nunca habilitación automática |
| staff_assignments, staffing_slots | Derivar enlaces conocidos, completar cargo/área/vigencia con evidencia; identificar plazas del generador sin guardarlas como camas |
| roster_assignments | Publicar calendario validado a partir de horarios/plazas; no convertir proyección virtual pasada en asistencia cumplida |
| consents, person_contacts | Evidencia y declaraciones de origen; acceso clínico familiar pendiente hasta validación del vínculo y consentimiento |
| admission_decisions, admissions | Extraer decisiones/fecha ingreso conocidas; no reconstruir reingresos no documentados; cerrar conflictos antes de ocupación operativa |
| clinical_notes, diagnoses, allergies | Extraer hechos referidos con fuente; no confirmar diagnósticos por booleanos/defaults; conservar negaciones/desconocidos |
| instrument_versions | Versión legacy para cada catálogo y formularios funcional/ingreso; versión prospectiva validada independiente |
| care_plan_versions, care_task_executions | Separar definición/resultado que ya existe; no inventar ejecuciones recurrentes |
| visits, social_followups | Sin datos actuales verificables: empezar vacías y completar caso de uso real |
| incidents | Solo desde detalle inequívoco de seguimiento y revisión, en otro caso comenzar vacío |
| document_events | Inferir únicamente validación/anulación/entrega respaldada por datos; PDF generado no acredita recepción ni firma |
| expert_rule_sets, expert_rule_versions, expert_runs, expert_reviews | Nuevas, sin inventar histórico experto; reglas actuales pueden ser insumo sujeto a aprobación clínica |
| report_exports | Nuevas exportaciones, sin fingir auditoría retroactiva |
| migration_links | Crear durante cada ensayo/importación, con hash y conciliación por fuente; manifiesto protegido |

## Orden y puntos de corte

1. Catálogos, persona/identificadores/cuentas y correspondencias. Separar resolución de identidad del otorgamiento de permisos.
2. Institución, área, cargo, acreditaciones, vínculos/horarios/plazas. Las FK circulares de autor/documentos se cargan en segunda pasada permitida por nulabilidad, no eliminando restricciones finales.
3. Residentes, casos, contactos, estancias e intervalos. Resolver primero cama duplicada, fecha fin desconocida y origen de caso divergente.
4. Documentos/versions y comprobación de binarios; completar referencias de foto, consentimiento y acreditación.
5. Atención, signos, notas, condiciones/alergias; VGI con versión legacy; prescripción/línea/administración en ese orden.
6. Plan raíz/versiones/tareas/resultados, asignación asistencial/seguimiento/pase, actividad/participación y voluntariado, alertas/acciones.
7. Auditoría y referencias documentales pendientes; rebuild de consultas/caché; colas drenadas; sesiones/tokens invalidados en corte.
8. Conciliación por tabla y módulo: cada fila fuente corresponde a destinos o a incidencia explícita. Suma de conciliadas + cuarentena = total origen; cuarentena clínica/ocupación/cuentas críticas debe ser cero antes de habilitar esos flujos.

## Criterios de aceptación de datos

Cero FK huérfanas; cero ocupaciones solapadas; cero administración de persona distinta; cero historiales eliminados; igual número de hechos fuente o explicación de consolidación firmada; 100% de archivos contabilizados (hash coincidente o incidencia resuelta); hashes de contraseñas conservados sin exposición; permisos efectivos revisados; conteos y totales de reportes comparados sobre mismo corte. Idempotencia: segundo pase no crea filas, dosis, correos ni ocupaciones extra. Ensayar rollback sobre copia restaurada y documentar duración medida, no estimada.

Eliminar/fusionar aquí significa retirar la tabla antigua del esquema operativo después del período de comparación y retención aprobado. No ejecutar DROP ni modificar la base actual como parte de esta entrega.

## Extracción específica desde activity_log.properties

| Origen | Destino | Regla |
|---|---|---|
| log_name=Medico, subject_type/subject_id, causer_type/causer_id, created_at | encounters.person_id/recorded_by/occurred_at + migration_links | Resolver sujeto AdultoMayor y autor real; no convertir todo evento de auditoría en atención |
| properties.condicion_medica, estado_cognitivo, nivel_dependencia, observacion del modal médico | clinical_notes.narrative/assessment | Conservar etiquetas y valores como valoración referida sin firma retroactiva |
| properties.signos_vitales.PA/FC/FR/Temp/SatO2 | vital_signs + clinical_notes.vital_sign_id | Parseo validado/unidades verificadas; preservar texto inválido, no inventar medición |
| properties.decision, motivo_decision, seguimiento_requerido, cuidado_especial_requerido, motivo_derivacion, institucion_derivada, recomendacion_final | admission_decisions.decision/reason/notes | Enlazar caso solo inequívoco; si no existe, registrar incidencia y no inventar admisión |
| description/event/batch_uuid/properties restantes | activity_log sin pérdida + procedencia protegida | No duplicar clínicos existentes; payload original inmutable y acceso restringido |

Los campos de formulario que nunca se persistieron no son migrables: declarar ausencia, sin rellenarlos con defaults de la UI. Esta excepción forma parte del mapa obligatorio y no autoriza extracción automática de cualquier texto como diagnóstico.

## Equivalencias de estados declarados en seeders/código

| Estado AS-IS | Destino propuesto / regla |
|---|---|
| ACTIVO, INACTIVO, ARCHIVADO, RESTAURADO | Estado de expediente/residents.archived_at y resident_status_changes dimensión expediente; no cerrar estancia solo por INACTIVO |
| SEGUIMIENTO_ESPECIAL, EN_SEGUIMIENTO_ACTIVO, OBSERVADO | Cambio de condición/seguimiento; no diagnóstico. Confirmar semántica de OBSERVADO por fuente |
| RETIRADO, TRASLADADO, FALLECIDO, EGRESADO | Alta de estancia con motivo y momento verificable; fallecimiento no inferir solo de archivado |
| PREADMISION, PENDIENTE_VALORACION_ENFERMERIA, VALORACION_ENFERMERIA_COMPLETADA, PENDIENTE_VALORACION_MEDICA, VALORACION_MEDICA_COMPLETADA | admission_cases ASIGNADO/EN_VALORACION/PENDIENTE_DECISION + evaluaciones registradas; estado no crea evidencia clínica ausente |
| DECISION_ADMISION, PENDIENTE_ASIGNACION | admission_cases PENDIENTE_DECISION o APROBADO según decisión comprobada; no ocupación hasta asignación válida |
| ADMITIDO, ASIGNADO | admissions ABIERTA y bed_assignments solo con datos efectivos; no duplicar estancia |
| NO_ADMITIDO, DERIVADO | admission_decisions RECHAZADO/DERIVADO y caso finalizado, no borrar persona |

Los códigos EST_001..EST_020 se resuelven a su etiqueta real antes de transformar; nombres escritos donde se esperaba código son incidencias. Perfilado futuro debe identificar estados adicionales no declarados y detenerlos para revisión.
