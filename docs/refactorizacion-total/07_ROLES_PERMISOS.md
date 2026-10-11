# Roles, permisos y autorización contextual

## Hallazgos actuales que condicionan el diseño

Las siete rutas usuarios ya separan usuarios.ver/crear/editar y conservan admin.usuarios.*: cambio previo que esta documentación no altera. Persisten: resource adultos protegido por adultos.ver; grupos médico/psicología sin permiso granular de ruta (algunos componentes restringen por rol en mount); Gate::before universal para SUPERADMINISTRADOR; roles como profesión/cargo; FormRequests authorize=true; concesión de rol sin límite delegable; variantes de nombres superadmin en SidebarService; documentos públicos; scope familiar/asistencial incompleto. No equivale a afirmar que toda ruta carezca de control: DocumentosUsuarioController y varios componentes sí usan checks, pero son heterogéneos.

## Modelo de decisión

Autorizado = cuenta activa y access_enabled + sesión válida + permiso explícito + Policy contextual + regla de negocio/estado válido. Evaluar en ruta para entrada y nuevamente en cada método Livewire mutador/Action; el permiso de render no autoriza una llamada manipulada. No confiar en IDs públicos del componente: recargar con scope y verificar parentesco entre recursos.

Policy contextual comprueba institución/área, asignación vigente, sujeto, estado/versionado, competencia profesional cuando aplica y consentimiento de divulgación. Un permiso global no sustituye estas condiciones. Consultas index/reportes aplican idéntico scope antes de agregación; show, PDF, descarga e impresión verifican el objeto. DocumentPolicy delega scope al titular persona/caso.

Spatie mantiene 5 tablas: los permisos de roles y directos son **aditivos**. No existe denegación por quitar una concesión directa si un rol todavía la da. Configurar plantillas pequeñas combinables; probar permisos efectivos con getAllPermissions. UI no permite “restar” permiso de un rol por usuario. No añadir mecanismo universal de deny.

No Gate::before que convierta administrador técnico en clínico. Facultades clínicas exigen acreditación y relación asistencial. Acceso excepcional de emergencia requiere decisión D04; no implementar bypass oculto ni otorgar SUPERADMINISTRADOR para resolver errores de permiso.

## Plantillas propuestas

Estas 16 plantillas de acceso son combinables y no son 16 módulos ni profesiones. Configuración propuesta sujeta a D04/D05; ninguna se concede automáticamente por migrar datos laborales. “Gestión” abajo se expande según el catálogo exacto por entidad; no concede eliminar histórico ni todos los permisos del sistema.

| Plantilla | Responsabilidad | Scope | Capacidades | Restricción |
|---|---|---|---|---|
| admin_identidad | usuarios y roles; sesiones/seguridad | Sin acceso clínico implícito | usuarios.ver/crear/editar/bloquear/restablecer_password/asignar_roles, roles.editar_permisos | No puede otorgar capacidad fuera de conjunto delegable ni cambiar su propia jerarquía |
| gestor_institucion | institución, áreas, cargos, personal, horarios/plazas | Centro autorizado | Catálogos Institución ver/crear/editar/activar; staff_assignments y schedules crear/cerrar; roster publicar | Acreditación validada por responsable designado, no por rol administrativo solo |
| gestor_admisiones | casos, documentación inicial, estancia y cama | Centro y casos asignados | admission_cases ver/crear/editar/asignar; admissions ingresar/dar_alta; bed_assignments trasladar | Decisión clínica requiere capacidad separada, evidencia firmada |
| clinico_consulta | historia, notas, signos, condiciones, interconsulta | Residentes bajo relación asistencial vigente | encounters ver/crear/derivar; clinical_notes ver/crear/firmar/corregir; diagnoses/allergies confirmar | Profesional acreditado; no prescribe sin prescriptor |
| prescriptor | prescripciones y conciliación | Relación asistencial + habilitación vigente | prescriptions ver/crear/firmar/suspender/finalizar; prescription_items ver/crear/corregir | No implica permiso de administrar dosis |
| administrador_medicacion | agenda y administración/omisión | Equipo/turno asignado | medication_administrations ver/registrar/corregir; prescription_items.ver | No modifica orden firmada |
| evaluador_vgi | evaluaciones | Competencia de instrumento y relación asistencial | assessments ver/crear/finalizar/corregir/anular; instrument_versions.ver | Publicación de reglas de instrumento es facultad separada |
| coordinador_cuidados | equipo, planes, tareas, pases | Área/centro y residentes a cargo | care_assignments crear/cerrar; care_plan_versions validar; care_tasks crear/transferir; handovers ver | Puede delegar tarea, no credenciales clínicas |
| operador_cuidados | seguimiento y tareas | Asignación/turno vigente | care_task_executions registrar; daily_observations crear; handovers emitir/recibir; alerts crear/atender | Lectura mínima necesaria del plan; sin cambios de prescripción |
| gestor_social | contactos, visitas, ficha social | Centro y residentes atendidos | person_contacts crear/editar/designar_responsable; visits crear/registrar_entrada/registrar_salida; social_followups firmar | No habilita portal clínico sin vínculo y consentimiento |
| gestor_actividades | eventos y voluntariado | Centro | activity_types/activities/volunteer_profiles gestión; asignaciones y asistencias registrar | Excepción de disponibilidad explícita; sin expediente clínico general |
| gestor_documental | checklist y validación | Titulares del módulo autorizado | documents ver/crear; document_versions crear/descargar; document_events validar | Permiso documental no amplía scope clínico; enviar necesita capacidad distinta |
| revisor_experto | versiones y resultados | Competencia clínica y ámbito de revisión | expert_rule_versions.aprobar; expert_reviews.revisar; expert_runs.ver | Aprobación de regla separada de autor por política de publicación |
| auditor | bitácora y reportes autorizados | Ámbito auditor aprobado y mínima información | auditoria.ver; report_exports.generar/descargar según reporte | Solo lectura; exportar no sigue de ver expediente |
| familiar | portal y documentos compartidos | Su persona vinculada por person_contacts vigente + consent válido | portal_familiar.ver; documents.ver contextual | Nunca adults/residents.ver global ni reportes clínicos institucionales |
| voluntario | su disponibilidad, asignaciones y asistencia | volunteer_profile de su person_id | volunteer_availabilities ver/crear/editar propias; volunteer_assignments.ver; volunteer_attendances.ver | No leer ni modificar otros voluntarios ni expediente médico |

## Catálogo canónico de permisos por entidad

`ver` cubre listar y detalle sujetos a Policy; `crear` permite iniciar borrador, no firmar ni validar automáticamente. `editar` solo metadata/borrador; `corregir` agrega versión histórica. No existe eliminar para hechos clínicos. `eliminar_borrador` exige cero efectos/referencias. Se usa el nombre de tabla como prefijo estable salvo users → usuarios, conservando el contrato existente.

| Entidad | Permisos base |
|---|---|
| persons | persons.ver, persons.crear, persons.editar, persons.activar, persons.desactivar, persons.anular, persons.eliminar_borrador |
| person_identifiers | person_identifiers.ver, person_identifiers.crear, person_identifiers.editar, person_identifiers.activar, person_identifiers.desactivar, person_identifiers.anular, person_identifiers.eliminar_borrador |
| users | usuarios.ver, usuarios.crear, usuarios.editar, usuarios.bloquear, usuarios.restablecer_password, usuarios.asignar_roles |
| institutions | institutions.ver, institutions.crear, institutions.editar, institutions.activar, institutions.desactivar, institutions.anular, institutions.eliminar_borrador |
| areas | areas.ver, areas.crear, areas.editar, areas.activar, areas.desactivar, areas.anular, areas.eliminar_borrador |
| positions | positions.ver, positions.crear, positions.editar, positions.activar, positions.desactivar, positions.anular, positions.eliminar_borrador |
| professions | professions.ver, professions.crear, professions.editar, professions.activar, professions.desactivar, professions.anular, professions.eliminar_borrador |
| specialties | specialties.ver, specialties.crear, specialties.editar, specialties.activar, specialties.desactivar, specialties.anular, specialties.eliminar_borrador |
| professionals | professionals.ver, professionals.crear, professionals.editar, professionals.activar, professionals.desactivar, professionals.anular, professionals.eliminar_borrador |
| professional_qualifications | professional_qualifications.ver, professional_qualifications.crear, professional_qualifications.editar, professional_qualifications.publicar, professional_qualifications.retirar, professional_qualifications.anular, professional_qualifications.eliminar_borrador |
| staff_assignments | staff_assignments.ver, staff_assignments.crear, staff_assignments.corregir, staff_assignments.cerrar, staff_assignments.anular, staff_assignments.eliminar_borrador |
| shifts | shifts.ver, shifts.crear, shifts.editar, shifts.activar, shifts.desactivar, shifts.anular, shifts.eliminar_borrador |
| staff_schedules | staff_schedules.ver, staff_schedules.crear, staff_schedules.corregir, staff_schedules.cerrar, staff_schedules.anular, staff_schedules.eliminar_borrador |
| staffing_slots | staffing_slots.ver, staffing_slots.crear, staffing_slots.editar, staffing_slots.activar, staffing_slots.desactivar, staffing_slots.anular, staffing_slots.eliminar_borrador |
| staffing_slot_assignments | staffing_slot_assignments.ver, staffing_slot_assignments.crear, staffing_slot_assignments.corregir, staffing_slot_assignments.cerrar, staffing_slot_assignments.anular, staffing_slot_assignments.eliminar_borrador |
| roster_assignments | roster_assignments.ver, roster_assignments.crear, roster_assignments.corregir, roster_assignments.cerrar, roster_assignments.anular, roster_assignments.eliminar_borrador |
| residents | residents.ver, residents.crear, residents.editar, residents.activar, residents.desactivar, residents.anular, residents.eliminar_borrador |
| resident_status_changes | resident_status_changes.ver, resident_status_changes.crear, resident_status_changes.corregir, resident_status_changes.anular |
| consents | consents.ver, consents.crear, consents.corregir, consents.anular |
| admission_cases | admission_cases.ver, admission_cases.crear, admission_cases.editar, admission_cases.cerrar, admission_cases.reabrir, admission_cases.anular, admission_cases.eliminar_borrador |
| admission_decisions | admission_decisions.ver, admission_decisions.crear, admission_decisions.corregir, admission_decisions.anular |
| admissions | admissions.ver, admissions.crear, admissions.editar, admissions.cerrar, admissions.reabrir, admissions.anular, admissions.eliminar_borrador |
| rooms | rooms.ver, rooms.crear, rooms.editar, rooms.activar, rooms.desactivar, rooms.anular, rooms.eliminar_borrador |
| beds | beds.ver, beds.crear, beds.editar, beds.activar, beds.desactivar, beds.anular, beds.eliminar_borrador |
| bed_assignments | bed_assignments.ver, bed_assignments.crear, bed_assignments.corregir, bed_assignments.cerrar, bed_assignments.anular, bed_assignments.eliminar_borrador |
| encounter_types | encounter_types.ver, encounter_types.crear, encounter_types.editar, encounter_types.activar, encounter_types.desactivar, encounter_types.anular, encounter_types.eliminar_borrador |
| encounters | encounters.ver, encounters.crear, encounters.corregir, encounters.anular |
| clinical_notes | clinical_notes.ver, clinical_notes.crear, clinical_notes.corregir, clinical_notes.anular |
| diagnoses | diagnoses.ver, diagnoses.crear, diagnoses.corregir, diagnoses.anular |
| allergies | allergies.ver, allergies.crear, allergies.corregir, allergies.anular |
| vital_signs | vital_signs.ver, vital_signs.crear, vital_signs.corregir, vital_signs.anular |
| prescriptions | prescriptions.ver, prescriptions.crear, prescriptions.corregir, prescriptions.anular |
| prescription_items | prescription_items.ver, prescription_items.crear, prescription_items.corregir, prescription_items.anular |
| medication_administrations | medication_administrations.ver, medication_administrations.crear, medication_administrations.corregir, medication_administrations.anular |
| assessment_domains | assessment_domains.ver, assessment_domains.crear, assessment_domains.editar, assessment_domains.activar, assessment_domains.desactivar, assessment_domains.anular, assessment_domains.eliminar_borrador |
| assessment_instruments | assessment_instruments.ver, assessment_instruments.crear, assessment_instruments.editar, assessment_instruments.activar, assessment_instruments.desactivar, assessment_instruments.anular, assessment_instruments.eliminar_borrador |
| instrument_versions | instrument_versions.ver, instrument_versions.crear, instrument_versions.editar, instrument_versions.publicar, instrument_versions.retirar, instrument_versions.anular, instrument_versions.eliminar_borrador |
| assessments | assessments.ver, assessments.crear, assessments.corregir, assessments.anular |
| care_assignments | care_assignments.ver, care_assignments.crear, care_assignments.corregir, care_assignments.cerrar, care_assignments.anular, care_assignments.eliminar_borrador |
| care_plans | care_plans.ver, care_plans.crear, care_plans.editar, care_plans.cerrar, care_plans.reabrir, care_plans.anular, care_plans.eliminar_borrador |
| care_plan_versions | care_plan_versions.ver, care_plan_versions.crear, care_plan_versions.editar, care_plan_versions.publicar, care_plan_versions.retirar, care_plan_versions.anular, care_plan_versions.eliminar_borrador |
| care_tasks | care_tasks.ver, care_tasks.crear, care_tasks.editar, care_tasks.cerrar, care_tasks.reabrir, care_tasks.anular, care_tasks.eliminar_borrador |
| care_task_executions | care_task_executions.ver, care_task_executions.crear, care_task_executions.corregir, care_task_executions.anular |
| daily_observations | daily_observations.ver, daily_observations.crear, daily_observations.corregir, daily_observations.anular |
| handovers | handovers.ver, handovers.crear, handovers.corregir, handovers.anular |
| person_contacts | person_contacts.ver, person_contacts.crear, person_contacts.corregir, person_contacts.cerrar, person_contacts.anular, person_contacts.eliminar_borrador |
| visits | visits.ver, visits.crear, visits.editar, visits.cerrar, visits.reabrir, visits.anular, visits.eliminar_borrador |
| social_followups | social_followups.ver, social_followups.crear, social_followups.corregir, social_followups.anular |
| activity_types | activity_types.ver, activity_types.crear, activity_types.editar, activity_types.activar, activity_types.desactivar, activity_types.anular, activity_types.eliminar_borrador |
| activities | activities.ver, activities.crear, activities.editar, activities.cerrar, activities.reabrir, activities.anular, activities.eliminar_borrador |
| activity_participations | activity_participations.ver, activity_participations.crear, activity_participations.corregir, activity_participations.anular |
| volunteer_profiles | volunteer_profiles.ver, volunteer_profiles.crear, volunteer_profiles.editar, volunteer_profiles.activar, volunteer_profiles.desactivar, volunteer_profiles.anular, volunteer_profiles.eliminar_borrador |
| volunteer_availabilities | volunteer_availabilities.ver, volunteer_availabilities.crear, volunteer_availabilities.corregir, volunteer_availabilities.cerrar, volunteer_availabilities.anular, volunteer_availabilities.eliminar_borrador |
| volunteer_assignments | volunteer_assignments.ver, volunteer_assignments.crear, volunteer_assignments.editar, volunteer_assignments.cerrar, volunteer_assignments.reabrir, volunteer_assignments.anular, volunteer_assignments.eliminar_borrador |
| volunteer_attendances | volunteer_attendances.ver, volunteer_attendances.crear, volunteer_attendances.corregir, volunteer_attendances.anular |
| incidents | incidents.ver, incidents.crear, incidents.corregir, incidents.anular |
| alerts | alerts.ver, alerts.crear, alerts.editar, alerts.cerrar, alerts.reabrir, alerts.anular, alerts.eliminar_borrador |
| alert_actions | alert_actions.ver, alert_actions.crear, alert_actions.corregir, alert_actions.anular |
| document_types | document_types.ver, document_types.crear, document_types.editar, document_types.activar, document_types.desactivar, document_types.anular, document_types.eliminar_borrador |
| documents | documents.ver, documents.crear, documents.editar, documents.cerrar, documents.reabrir, documents.anular, documents.eliminar_borrador |
| document_versions | document_versions.ver, document_versions.crear, document_versions.editar, document_versions.publicar, document_versions.retirar, document_versions.anular, document_versions.eliminar_borrador |
| document_events | document_events.ver, document_events.crear, document_events.corregir, document_events.anular |
| expert_rule_sets | expert_rule_sets.ver, expert_rule_sets.crear, expert_rule_sets.editar, expert_rule_sets.activar, expert_rule_sets.desactivar, expert_rule_sets.anular, expert_rule_sets.eliminar_borrador |
| expert_rule_versions | expert_rule_versions.ver, expert_rule_versions.crear, expert_rule_versions.editar, expert_rule_versions.publicar, expert_rule_versions.retirar, expert_rule_versions.anular, expert_rule_versions.eliminar_borrador |
| expert_runs | expert_runs.ver, expert_runs.crear, expert_runs.corregir, expert_runs.anular |
| expert_reviews | expert_reviews.ver, expert_reviews.crear, expert_reviews.corregir, expert_reviews.anular |
| report_exports | report_exports.ver, report_exports.crear, report_exports.editar, report_exports.cerrar, report_exports.reabrir, report_exports.anular, report_exports.eliminar_borrador |
| migration_links | migration_links.auditar |

Acciones de negocio adicionales (el permiso base crear/editar no las implica):

- Identidad y Seguridad: usuarios.asignar_roles,usuarios.bloquear,usuarios.restablecer_password,roles.editar_permisos,auditoria.ver.
- Institución y Personal: professional_qualifications.validar,roster_assignments.publicar,staffing_slot_assignments.reemplazar.
- Residentes: residents.archivar,residents.restaurar,consents.revocar,resident_status_changes.registrar.
- Admisiones y Ocupación: admission_cases.asignar,admission_decisions.decidir,admissions.ingresar,admissions.dar_alta,bed_assignments.trasladar.
- Clínica: clinical_notes.firmar,diagnoses.confirmar,allergies.confirmar,encounters.derivar.
- Medicación: prescriptions.firmar,prescriptions.suspender,prescriptions.finalizar,medication_administrations.registrar.
- Valoración Geriátrica Integral: instrument_versions.publicar,assessments.finalizar,assessments.corregir.
- Cuidados: care_plan_versions.validar,care_tasks.transferir,care_task_executions.registrar,handovers.emitir,handovers.recibir.
- Social y Familia: person_contacts.designar_responsable,visits.registrar_entrada,visits.registrar_salida,social_followups.firmar,portal_familiar.ver.
- Actividades y Voluntariado: activity_participations.registrar_asistencia,volunteer_assignments.exceptuar_disponibilidad,volunteer_attendances.registrar.
- Seguridad Asistencial: incidents.revisar,alerts.atender,alerts.cerrar,alerts.reabrir.
- Documentos: document_versions.descargar,document_versions.generar,document_versions.enviar,document_events.validar,document_events.firmar.
- Sistema Experto: expert_rule_versions.aprobar,expert_runs.ejecutar,expert_reviews.revisar.
- Reportes: report_exports.generar,report_exports.descargar,migration_links.auditar.

Spatie/UI de roles: roles.ver, roles.editar_permisos; pivotes solo mediante asignación/revocación auditada, sin CRUD público independiente. Contratos de sesiones/cache/jobs/migrations no reciben permisos de producto; herramientas operativas restringidas por entorno. Desactivar cuentas conserva usuarios.cambiar_estado como alias controlado durante transición, revisando sesión activa.

## Migración de roles actuales

| Actual | Candidatos de plantilla TO-BE | Validación antes de asignar |
|---|---|---|
| SUPERADMINISTRADOR | admin_identidad + gestor_institucion | No heredar bypass clínico; dirección/aprobaciones explícitas |
| ADMINISTRADOR | gestor_institucion + gestor_admisiones | Revisar social/documental/reportes según función real |
| MEDICO GENERAL/GERIATRA | clinico_consulta + prescriptor + evaluador_vgi | Licencia/competencia y población asistencial |
| ENFERMEROS | operador_cuidados + administrador_medicacion + evaluador_vgi | Alcance de valoración; coordinador solo designado; no prescripción automática |
| PSICOLOGO/A | clinico_consulta + evaluador_vgi | Instrumentos/competencia; no prescriptor por defecto |
| FISIOTERAPEUTA | clinico_consulta + evaluador_vgi | Plan funcional vía Cuidados según responsabilidad |
| NUTRICIONISTA | clinico_consulta + evaluador_vgi | Plan nutricional vía Cuidados; no prescripción farmacológica implícita |
| PEDAGOGO | gestor_actividades o evaluador_vgi limitado | No afirmar credencial clínica solo por rol |
| FAMILIAR | familiar | Vínculo, persona, consentimiento y alcance de documentos |
| VOLUNTARIO | voluntario | Perfil y acciones propias; sin permisos de gestión global |

Profesiones/especialidades/cargos se migran por evidencia distinta de esta matriz. Las asignaciones actuales de múltiples roles deben revisarse conjuntamente para evitar acumulación accidental.

## Compatibilidad de permisos antiguos

| Familia actual | Destino |
|---|---|
| usuarios.ver/crear/editar y nombres admin.usuarios.* | Conservar nombres actuales; no ampliar ver a store/update |
| usuarios.cambiar_estado, usuarios.acceso.* | Acciones de cuenta con revocación de sesión; aliases acotados hasta retirar rutas antiguas |
| adultos.* | residents.*, cada acción diferenciada; ver no concede crear/editar |
| familiares.* | person_contacts.* / portal_familiar.ver según actor |
| documentos_usuarios.*, documentos.* | documents.*, document_versions.*, document_events.* con scope titular |
| turnos.*, turnos_enfermeria.* | shifts.*, staff_schedules.*, roster_assignments.* según operación |
| valoracion_enfermeria.*, valoracion_funcional.*, evaluaciones.* | assessments.* + competencia de instrumento |
| ficha_medica.*, valoracion_medica.* | clinical_notes.*, diagnoses.*, allergies.*; decisión admisión separada |
| medicacion.* / administracion_medicacion.* | prescriptions/prescription_items frente a medication_administrations |
| plan_cuidado.*, tareas.*, seguimiento.*, pase_turno.* | care_plans/versions/tasks/executions, daily_observations, handovers |
| alertas.* | alerts.*, alert_actions.*, incidents.* (nuevo) |
| reportes.* / *.reportes.* | report_exports.generar/descargar + autorización del tipo y datos |

Antes del corte generar diferencias de catálogo completo: cada permiso utilizado por routes/Livewire/seeders debe tener destino o retiro explícito. Las familias de esta tabla son reglas de correspondencia, no una concesión wildcard en producción. Ningún permiso desconocido se migra a acceso amplio.

## Pruebas de seguridad obligatorias

Invitado; cuenta inactiva con sesión existente; acceso_sistema deshabilitado; usuario con único permiso; acción Livewire directa sin abrir modal; IDs de otro residente/centro; familiar ajeno y consentimiento revocado; clínico sin asignación; acreditación expirada; intento de asignarse superadmin; admin técnico accediendo clínica; sustitución de prescription_item; repetición de request_key; archivo privado directo; PDF/Excel/CSV del mismo scope; caché por identidad/scope; alias legacy con idéntica Policy. Conservar seis regresiones de UsuariosRoutesPermissionsTest y añadir cobertura de store/update/destroy según contrato real.

Secretos 2FA/password/tokens deben ocultarse en serialización y auditoría; invitaciones/reset usan token aleatorio y expiración, no contraseña de iniciales+documento ni correo temporal que permita acceso accidental. Revocar sesiones al bloquear cuenta. Consentimiento de datos y autorización de divulgación son conceptos separados.

