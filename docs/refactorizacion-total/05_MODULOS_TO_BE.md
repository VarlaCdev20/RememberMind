# Módulos TO-BE

**14 módulos** con dueño único de cada tabla, monolito Laravel. No módulos por profesión: fisioterapia/nutrición/psicología comparten Clínica/VGI/Cuidados según caso de uso; no se eliminan sus capacidades. Actividades y Voluntariado permanecen juntos por residentes, programación, colaboración y reportes ya compartidos, pero conservan asistencias distintas. Documentos y Sistema Experto son módulos explícitos, no cajones de servicios.

Cada fila de entidad define modelo y tabla; las FK exactas, nulabilidad y cardinalidad están en 03. El contrato de siete operaciones por entidad está en 06 y sus permisos generados en 07. Pantallas indicadas son Blade con componentes Livewire, nunca Vue/Inertia. Las dependencias citadas significan lectura/servicio de caso de uso; solo el propietario escribe sus invariantes.

## 1. Identidad y Seguridad

**Justificación desde código:** UsuariosPanel/UsuarioFormModal/UsuarioController implementan vías paralelas; Jetstream cubre autenticación. RolesPermisosPanel edita asignaciones, no profesión.

**Casos de uso:** Alta de persona sin acceso; vincular cuenta; activar/bloquear sesión; recuperar contraseña; configurar 2FA; asignar permisos delegables; consultar auditoría.

**Pantallas necesarias:** PersonasIndex, PersonaForm, UsuarioIndex, UsuarioForm, AccesoPanel, RolesPermisosPanel, AuditoriaIndex. Ruta de vistas: resources/views/modules/identidad/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Institución provee contexto laboral para autorización; Social provee vínculo y consentimiento. Ninguno altera credenciales directamente.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: usuarios.asignar_roles,usuarios.bloquear,usuarios.restablecer_password,roles.editar_permisos,auditoria.ver. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| Person: Identidad personal única, tenga o no cuenta | persons | photo_document_id → documents | Perfil M de 06; No fusionar automáticamente por nombre; normalización conserva grafía original |
| PersonIdentifier: Identificación oficial por país y tipo | person_identifiers | person_id → persons | Perfil M de 06; U(country,type,number,complement normalizado) solo identificadores verificados activos; vacíos no son números |
| User: Cuenta de acceso independiente del empleo | users | person_id → persons | Perfil A de 06; U(person_id), U(lower(email)); correo de acceso separado del contacto; Fortify configurable |

Además administra contratos roles, permissions, model_has_roles, model_has_permissions, role_has_permissions, activity_log y credenciales/sesiones/tokens. Caché, jobs, batches, failed_jobs y migrations son infraestructura compartida, no otro módulo de producto.

## 2. Institución y Personal

**Justificación desde código:** PersonalInstitucionalForm deriva cargo y especialidad de rol; horarios guardan metadata JSON; GeneradorPlanillaEnfermeriaService genera cobertura/rotaciones.

**Casos de uso:** Mantener centro, áreas y cargos; acreditar profesional; asignar empleo; versionar horario; simular/publicar planilla; asignar/reemplazar plaza; detectar cobertura/descanso/solapamiento.

**Pantallas necesarias:** AreasIndex, ProfesionalesIndex, AcreditacionForm, PersonalIndex, VinculoForm, HorariosPanel, PlanillaPanel, PlazasPanel, TurnosIndex. Ruta de vistas: resources/views/modules/institucion/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Identidad aporta personas; Documentos acredita; Cuidados consume turnos publicados. Institución es dueña de staff_assignments, no Identidad.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: professional_qualifications.validar,roster_assignments.publicar,staffing_slot_assignments.reemplazar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| Institution: Centro y zona horaria operativa | institutions | Catálogo raíz | Perfil M de 06; U(code); inicialmente un centro, sin prometer multitenencia completa |
| Area: Unidad organizativa real | areas | institution_id → institutions; parent_id → areas | Perfil M de 06; U(institution_id,slug); sin roles que impliquen pertenencia |
| Position: Cargo laboral independiente del permiso | positions | institution_id → institutions | Perfil M de 06; U(institution_id,name) |
| Profession: Profesión acreditable | professions | Catálogo raíz | Perfil M de 06; U(name) |
| Specialty: Especialidad de una profesión | specialties | profession_id → professions | Perfil M de 06; U(profession_id,name) |
| Professional: Perfil profesional de una persona | professionals | person_id → persons | Perfil M de 06; U(person_id); no exige usuario ni empleo activo |
| ProfessionalQualification: Acreditación profesional con vigencia | professional_qualifications | professional_id → professionals; profession_id → professions; specialty_id → specialties; evidence_version_id → document_versions | Perfil V de 06; Especialidad debe corresponder a profesión; renovaciones son filas nuevas; no inferir titulación desde rol |
| StaffAssignment: Vínculo laboral y ubicación temporal | staff_assignments | person_id → persons; area_id → areas; position_id → positions | Perfil T de 06; Una asignación principal por persona e institución en cada instante |
| Shift: Catálogo único de turnos | shifts | institution_id → institutions | Perfil M de 06; U(institution_id,name); madrugada/noche explícitas |
| StaffSchedule: Versión de horario semanal de un vínculo | staff_schedules | staff_assignment_id → staff_assignments; shift_id → shifts | Perfil T de 06; Día ISO1..7; periodos no solapados por persona; cambios cierran vigencia anterior |
| StaffingSlot: Plaza rotativa de planilla | staffing_slots | area_id → areas | Perfil M de 06; U(area_id,code); plaza no es cama ni cuenta |
| StaffingSlotAssignment: Titularidad y reemplazos de plaza | staffing_slot_assignments | staffing_slot_id → staffing_slots; staff_assignment_id → staff_assignments | Perfil T de 06; Evitar dos titulares simultáneos; conservar reemplazos |
| RosterAssignment: Turno fechado publicado, descanso o apoyo | roster_assignments | staff_assignment_id → staff_assignments; staffing_slot_id → staffing_slots; shift_id → shifts | Perfil T de 06; U(generation_key) si existe; no guardar simulaciones como trabajo realizado |

## 3. Residentes

**Justificación desde código:** AdultoMayorController y modales combinan expediente, salud, familia y ubicación; no convertir todo en un único formulario.

**Casos de uso:** Abrir expediente sin duplicar persona; corregir datos administrativos; consultar expediente integrado; archivar/restaurar expediente; registrar consentimiento/revocación y transición con motivo.

**Pantallas necesarias:** ResidentesIndex, ResidenteForm, ExpedienteShow, EstadoModal, ConsentimientosPanel. Ruta de vistas: resources/views/modules/residentes/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Identidad es fuente de persona; Admisiones de estancia/cama; Clínica/VGI/Cuidados aportan paneles de lectura; Social red de apoyo.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: residents.archivar,residents.restaurar,consents.revocar,resident_status_changes.registrar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| Resident: Expediente institucional persistente entre ingresos | residents | person_id → persons | Perfil M de 06; U(person_id), U(public_code); no guardar cama ni fecha única de ingreso aquí |
| ResidentStatusChange: Evolución administrativa o asistencial explícita | resident_status_changes | resident_id → residents; admission_id → admissions; evidence_version_id → document_versions | Perfil H de 06; Dimensión distingue expediente, estancia y condición; no codificar diagnóstico como estado de cuenta |
| Consent: Otorgamiento y revocación verificable | consents | person_id → persons; granted_by_person_id → persons; evidence_version_id → document_versions; revokes_id → consents | Perfil H de 06; Revocar agrega fila; booleanos heredados no prueban firma; identidad del representante verificable |

## 4. Admisiones y Ocupación

**Justificación desde código:** PreadmisionesPanel aprueba y crea adulto/familiar; DecisionAdmisionModal modifica estado del adulto; ocupación duplicada en tres lugares.

**Casos de uso:** Registrar caso y requisitos; asignar valorador disponible; valorar desde Clínica/VGI; decidir admisión/derivación/rechazo; abrir estancia una sola vez; asignar/trasladar/liberar cama; alta/reingreso.

**Pantallas necesarias:** CasosIndex, PreadmisionWizard, CasoShow, DecisionModal, EstanciasIndex, IngresoModal, OcupacionPanel, TrasladoModal, AltaModal, HabitacionesIndex. Ruta de vistas: resources/views/modules/admisiones/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Identidad primero, Documentos requisitos, VGI/Clínica evidencia, Institución personal. Alta finaliza ocupación y asignaciones activas en transacción.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: admission_cases.asignar,admission_decisions.decidir,admissions.ingresar,admissions.dar_alta,bed_assignments.trasladar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| AdmissionCase: Solicitud previa, evaluación y decisión | admission_cases | person_id → persons; institution_id → institutions; contact_person_id → persons; assigned_staff_id → staff_assignments | Perfil W de 06; U(request_key); no copiar datos personales vivos; documentación completa se calcula |
| AdmissionDecision: Decisiones de aprobación, rechazo y derivación | admission_decisions | admission_case_id → admission_cases; professional_id → professionals | Perfil H de 06; Caso bloqueado al decidir; decisión clínica firmada y administrativa diferenciadas en decision |
| Admission: Estancia concreta, alta y reingreso | admissions | resident_id → residents; admission_case_id → admission_cases; institution_id → institutions | Perfil W de 06; U(admission_case_id) no nulo; un ingreso abierto por residente; histórico importado puede carecer de caso |
| Room: Espacio físico | rooms | institution_id → institutions; area_id → areas | Perfil M de 06; U(institution_id,code); capacidad autorizada distinta de cantidad de camas |
| Bed: Plaza física habilitada | beds | room_id → rooms | Perfil M de 06; U(room_id,code); ocupación derivada, mantenimiento no significa ocupada |
| BedAssignment: Ocupación histórica por estancia | bed_assignments | admission_id → admissions; bed_id → beds | Perfil T de 06; Excluir intervalos solapados por cama y por estancia; habitación deriva de cama |

## 5. Clínica

**Justificación desde código:** Notas, signos y ficha existen; rutas de fisioterapia/nutrición son en gran parte alias. Se completan mediante atención/plan común, sin tabla por profesión.

**Casos de uso:** Abrir atención/interconsulta; firmar evolución SOAP; registrar antecedentes y condiciones; confirmar/corregir alergia; medir signos; ver tendencias; atender derivación; registrar sesión de rehabilitación o atención nutricional en encounter.

**Pantallas necesarias:** AtencionesIndex, AtencionForm, NotaForm, NotaShow, DiagnosticosPanel, AlergiasPanel, SignosPanel, InterconsultasIndex, HistorialClinicoShow. Ruta de vistas: resources/views/modules/clinica/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Persona siempre; estancia/caso opcional coherente; Institución valida competencia; Cuidados consume indicaciones; Documentos aporta soporte.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: clinical_notes.firmar,diagnoses.confirmar,allergies.confirmar,encounters.derivar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| EncounterType: Catálogo de atención interdisciplinaria | encounter_types | Catálogo raíz | Perfil M de 06; U(name); conserva tipo_atenciones_adulto |
| Encounter: Atención, consulta, interconsulta o sesión | encounters | person_id → persons; admission_id → admissions; admission_case_id → admission_cases; encounter_type_id → encounter_types; professional_id → professionals; referral_encounter_id → encounters; referral_to_profession_id → professions | Perfil H de 06; Una tabla de atenciones para todas las profesiones; no deduplicar solo por fecha |
| ClinicalNote: Nota clínica firmada y antecedentes narrativos | clinical_notes | encounter_id → encounters; vital_sign_id → vital_signs | Perfil H de 06; Corrección agrega versión enlazada; borrador editable antes de firma |
| Diagnosis: Condición longitudinal con grado de certeza | diagnoses | person_id → persons; encounter_id → encounters | Perfil H de 06; No transformar un false heredado en ausencia clínica confirmada |
| Allergy: Alergia o intolerancia documentada | allergies | person_id → persons; encounter_id → encounters | Perfil H de 06; Distinguir desconocido, negación explícita y alergia; texto no equivale a catálogo validado |
| VitalSign: Medición con unidades y momento | vital_signs | person_id → persons; encounter_id → encounters; admission_id → admissions | Perfil H de 06; IMC derivado kg/m²; distinguir cero de no medido; no duplicar signos en nota JSON |

## 6. Medicación

**Justificación desde código:** MedicacionAdulto mezcla cabecera/línea y prescriptor textual; administración separada ya existe pero integridad/idempotencia incompletas.

**Casos de uso:** Conciliar medicación de ingreso; prescribir y firmar; modificar pauta con nueva versión; suspender/finalizar; preparar agenda de dosis; registrar administración u omisión; corregir registro sin redosificar.

**Pantallas necesarias:** PrescripcionesIndex, PrescripcionForm, PrescripcionShow, AdministracionesPanel, AdministracionModal, ConciliacionPanel. Ruta de vistas: resources/views/modules/medicacion/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Clínica/persona, profesional habilitado, estancia, evidencia documental; Cuidados referencia administración en ejecución.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: prescriptions.firmar,prescriptions.suspender,prescriptions.finalizar,medication_administrations.registrar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| Prescription: Orden firmada de tratamiento | prescriptions | person_id → persons; admission_id → admissions; encounter_id → encounters; prescriber_id → professionals; evidence_version_id → document_versions | Perfil H de 06; Prescriptor interno acreditado o externo identificado; suspension no borra administraciones |
| PrescriptionItem: Línea y pauta de un fármaco | prescription_items | prescription_id → prescriptions | Perfil H de 06; Pauta versionada; texto ambiguo requiere conciliación antes de generar dosis |
| MedicationAdministration: Dosis dada u omitida, independiente de la orden | medication_administrations | prescription_item_id → prescription_items | Perfil H de 06; U(request_key); una versión vigente por administration_group; corrección no crea nueva dosis |

## 7. Valoración Geriátrica Integral

**Justificación desde código:** 26 instrumentos del seeder y Barthel/valoración funcional/ingreso; catálogo no demuestra 26 formularios clínicamente validados.

**Casos de uso:** Configurar dominio e instrumento; publicar versión aprobada; aplicar formulario; validar respuestas y puntuación; finalizar/anular/corregir; comparar evolución conservando versión; valorar preingreso.

**Pantallas necesarias:** InstrumentosIndex, VersionInstrumentoForm, EvaluacionesIndex, EvaluacionForm, EvaluacionShow, EvolucionVgiPanel. Ruta de vistas: resources/views/modules/vgi/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Persona/caso/estancia, profesional, signos; resultados alimentan Cuidados y Experto después de firma.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: instrument_versions.publicar,assessments.finalizar,assessments.corregir. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| AssessmentDomain: Dimensión geriátrica, no área laboral | assessment_domains | Catálogo raíz | Perfil M de 06; U(name) |
| AssessmentInstrument: Identidad estable del instrumento | assessment_instruments | assessment_domain_id → assessment_domains | Perfil M de 06; U(acronym); Barthel e ingreso de enfermería como instrumentos explícitos |
| InstrumentVersion: Formulario y reglas inmutables publicadas | instrument_versions | assessment_instrument_id → assessment_instruments | Perfil V de 06; U(assessment_instrument_id,version); no cambiar puntos de corte retrospectivamente |
| Assessment: Valoración funcional, cognitiva o de ingreso | assessments | person_id → persons; admission_id → admissions; admission_case_id → admission_cases; instrument_version_id → instrument_versions; evaluator_id → professionals; vital_sign_id → vital_signs | Perfil H de 06; Solo un score y un estado de documento; riesgo no es estado; respuestas validadas según versión |

## 8. Cuidados

**Justificación desde código:** PlanCuidadoPanel, TareasPlanPanel, SeguimientoDiarioPanel y PaseTurnoPanel sí persisten; actividades/reportes enfermería son alias del dashboard.

**Casos de uso:** Asignar equipo por turno; validar plan interdisciplinario; versionar objetivos funcionales/nutricionales; programar tarea; ejecutar/omitir/transferir; registrar seguimiento; emitir/recibir pase.

**Pantallas necesarias:** EquipoResidentePanel, PlanesIndex, PlanVersionForm, TareasIndex, ResultadoTareaModal, SeguimientoForm, PasesIndex, PaseShow. Ruta de vistas: resources/views/modules/cuidados/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Admisiones aporta estancia, Institución jornada, Clínica/VGI decisiones y signos, Medicación dosis; Seguridad alertas.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: care_plan_versions.validar,care_tasks.transferir,care_task_executions.registrar,handovers.emitir,handovers.recibir. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| CareAssignment: Equipo responsable de residente por intervalo | care_assignments | admission_id → admissions; staff_assignment_id → staff_assignments; roster_assignment_id → roster_assignments | Perfil T de 06; Asignación asistencial independiente de cama y de tarea; varios profesionales compatibles |
| CarePlan: Raíz estable del plan interdisciplinario | care_plans | resident_id → residents; admission_id → admissions | Perfil W de 06; Plan general o funcional/nutricional sin tabla por profesión |
| CarePlanVersion: Objetivos e indicaciones de una versión | care_plan_versions | care_plan_id → care_plans; validated_by → users | Perfil V de 06; U(care_plan_id,version); una versión vigente; JSON nutrición con esquema validado |
| CareTask: Tarea programada de una versión | care_tasks | care_plan_version_id → care_plan_versions; shift_id → shifts; assigned_staff_id → staff_assignments; area_id → areas; transferred_to_shift_id → shifts | Perfil W de 06; Residente deriva del plan; reprogramación auditada; tarea no equivale a ejecución |
| CareTaskExecution: Resultado de una ocurrencia de tarea | care_task_executions | care_task_id → care_tasks; vital_sign_id → vital_signs; administration_id → medication_administrations | Perfil H de 06; U(request_key); repetición genera ocurrencia distinta, nunca sobrescribe el resultado |
| DailyObservation: Seguimiento por turno y observación eventual | daily_observations | person_id → persons; admission_id → admissions; care_plan_version_id → care_plan_versions; shift_id → shifts | Perfil H de 06; Esquema versionado para alimentación, hidratación, movilidad, higiene, sueño, orientación, conducta; no diagnostica |
| Handover: Pase de turno emitido y recibido | handovers | admission_id → admissions; outgoing_roster_id → roster_assignments; incoming_roster_id → roster_assignments; outgoing_staff_id → staff_assignments; incoming_staff_id → staff_assignments; outgoing_shift_id → shifts; incoming_shift_id → shifts; received_by → users | Perfil H de 06; Snapshot histórico de pendientes, no lista operativa editable; receptor distinto comprobado |

## 9. Social y Familia

**Justificación desde código:** RedApoyoPanel funcional; visitas/ficha-social usan base.blade.php con mensaje de próxima fase; resumen tolera tablas inexistentes.

**Casos de uso:** Vincular contacto sin crear credenciales ficticias; designar responsable/emergencia; comprobar acceso familiar; programar y registrar visita; ficha social y revisión; portal familiar limitado.

**Pantallas necesarias:** RedApoyoPanel, ContactoForm, VisitasIndex, VisitaForm, FichaSocialShow, SeguimientoSocialForm, PortalFamiliarShow. Ruta de vistas: resources/views/modules/social/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Identidad persona/cuenta; Residentes consentimiento; Admisiones/Clínica resumen autorizado; Documentos documentos compartidos explícitos.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: person_contacts.designar_responsable,visits.registrar_entrada,visits.registrar_salida,social_followups.firmar,portal_familiar.ver. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| PersonContact: Parentesco y responsabilidad contextual | person_contacts | person_id → persons; contact_person_id → persons | Perfil T de 06; No autoriza solo por ser familiar; consentimiento vigente adicional; sin vínculos reflexivos |
| Visit: Visita programada, ingreso y salida | visits | resident_id → residents; visitor_person_id → persons | Perfil W de 06; Función hoy de vista base; privacidad de visitantes; no convertir voluntariado automáticamente |
| SocialFollowup: Evaluación social y seguimiento de red | social_followups | resident_id → residents; staff_assignment_id → staff_assignments | Perfil H de 06; Función anunciada pero ficha social sin persistencia actual |

## 10. Actividades y Voluntariado

**Justificación desde código:** Cinco paneles Actividades usan misma tabla actividades_adulto; cinco paneles Voluntariado tienen operaciones reales y SQL directo.

**Casos de uso:** Gestionar tipos/eventos; inscribir residentes; registrar asistencia/resultado; reprogramar/cancelar; alta voluntario; disponibilidad; asignación con excepción justificada; asistencia con/sin evento; portal de tareas propias.

**Pantallas necesarias:** TiposActividadIndex, ActividadesIndex, ActividadForm, ParticipacionPanel, AsistenciaActividadPanel, VoluntariosIndex, PerfilVoluntarioShow, DisponibilidadPanel, AsignacionesPanel, AsistenciaVoluntarioPanel, MiVoluntariadoShow. Ruta de vistas: resources/views/modules/actividades/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Residentes participación; Identidad persona; Institución áreas; Seguridad incidentes; Reportes colaboración.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: activity_participations.registrar_asistencia,volunteer_assignments.exceptuar_disponibilidad,volunteer_attendances.registrar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| ActivityType: Catálogo de actividades | activity_types | Catálogo raíz | Perfil M de 06; U(name) |
| Activity: Evento programado individual o colectivo | activities | activity_type_id → activity_types; institution_id → institutions | Perfil W de 06; No fusionar eventos antiguos por coincidencia horaria sin evidencia |
| ActivityParticipation: Inscripción, asistencia y resultado por residente | activity_participations | activity_id → activities; resident_id → residents | Perfil H de 06; U(activity_id,resident_id) vigente; planificar no confirma asistencia |
| VolunteerProfile: Vinculación voluntaria sin duplicar identidad | volunteer_profiles | person_id → persons; preferred_area_id → areas | Perfil M de 06; U(person_id); profesión declarada no es acreditación |
| VolunteerAvailability: Disponibilidad semanal temporal | volunteer_availabilities | volunteer_profile_id → volunteer_profiles | Perfil T de 06; No solapamiento vigente; cambio crea nueva vigencia |
| VolunteerAssignment: Colaboración programada con o sin evento | volunteer_assignments | volunteer_profile_id → volunteer_profiles; resident_id → residents; activity_id → activities | Perfil W de 06; Excepción de disponibilidad exige permiso y motivo |
| VolunteerAttendance: Asistencia real o ausencia justificada | volunteer_attendances | volunteer_profile_id → volunteer_profiles; volunteer_assignment_id → volunteer_assignments; resident_id → residents | Perfil H de 06; La asignación es opcional para legado o asistencia extraordinaria; coincidencia no implica FK |

## 11. Seguridad Asistencial

**Justificación desde código:** AlertasPanel y acciones existentes; incidente solo en seguimiento; umbrales calculados en varios paneles sin motor versionado.

**Casos de uso:** Notificar incidente; documentar medidas; generar/atender/cerrar/reabrir alerta; revisar riesgos pendientes sin convertirlos en diagnóstico.

**Pantallas necesarias:** IncidentesIndex, IncidenteForm, IncidenteShow, AlertasIndex, AccionAlertaModal. Ruta de vistas: resources/views/modules/seguridad_asistencial/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Clínica/Cuidados/VGI/Experto generan evidencia; persona y estancia; avisos internos sin datos sensibles innecesarios.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: incidents.revisar,alerts.atender,alerts.cerrar,alerts.reabrir. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| Incident: Hecho de seguridad asistencial | incidents | person_id → persons; admission_id → admissions; reviewed_by → users | Perfil H de 06; Diferente de alerta de riesgo; no crear incidente solo desde booleano sin descripción |
| Alert: Riesgo que requiere atención y cierre | alerts | person_id → persons; admission_id → admissions; shift_id → shifts; incident_id → incidents; expert_run_id → expert_runs; assigned_to → users | Perfil W de 06; Único dedup_key mientras abierta; cierre exige acción y motivo |
| AlertAction: Acción, atención, cierre o reapertura | alert_actions | alert_id → alerts | Perfil H de 06; La bitácora de acciones determina atendido/cerrado; no duplicar campos en alerta |

## 12. Documentos

**Justificación desde código:** Tres tablas, servicios documentales, PDFs y correo ya existen; no perder obligatoriedad, plazo48h, vencimiento ni validación.

**Casos de uso:** Configurar requisito; subir privado; observar/validar; reemplazar conservando versión; generar PDFs; imprimir/descargar autorizado; enviar por correo a destinatario autorizado; registrar entrega/firma; archivar.

**Pantallas necesarias:** TiposDocumentoIndex, ChecklistPanel, DocumentoShow, SubirDocumentoModal, VersionesPanel, ValidacionModal, EntregasPanel. Ruta de vistas: resources/views/modules/documentos/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Titular persona/caso; política delega contexto al módulo propietario; consentimientos y acreditaciones apuntan versión concreta.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: document_versions.descargar,document_versions.generar,document_versions.enviar,document_events.validar,document_events.firmar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| DocumentType: Catálogo documental reutilizable | document_types | Catálogo raíz | Perfil M de 06; U(code); requisitos no son permisos; revisión de roles antiguos a perfiles nuevos |
| Document: Identidad del documento y titular | documents | document_type_id → document_types; person_id → persons; admission_case_id → admission_cases | Perfil W de 06; Titular obligatorio: persona o caso, ambos solo si coinciden; no duplicar archivo al aprobar caso |
| DocumentVersion: Archivo privado e inmutable | document_versions | document_id → documents | Perfil V de 06; U(document_id,version), U(storage_disk,storage_path); reemplaza no sobrescribe binario |
| DocumentEvent: Validación, observación, entrega o firma | document_events | document_version_id → document_versions; actor_person_id → persons | Perfil H de 06; Múltiples envíos y validaciones trazables; sin guardar contraseñas ni contenido del correo sensible |

## 13. Sistema Experto

**Justificación desde código:** No hay motor con reglas/versiones/ejecuciones persistidas; alertasInteligentes y clasificación de paneles son heurísticas locales.

**Casos de uso:** Definir y aprobar reglas; ejecutar sobre snapshot; explicar resultado y faltantes; revisar con profesional; vincular recomendación aceptada a atención; medir concordancia.

**Pantallas necesarias:** ReglasIndex, VersionReglaForm, EjecucionesIndex, EjecucionShow, RevisionExpertaModal. Ruta de vistas: resources/views/modules/experto/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Solo lee versiones firmadas de Clínica/VGI/Cuidados; produce recomendaciones, revisión y alertas. No modifica prescripción autónomamente.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: expert_rule_versions.aprobar,expert_runs.ejecutar,expert_reviews.revisar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| ExpertRuleSet: Familia de reglas de apoyo clínico | expert_rule_sets | Catálogo raíz | Perfil M de 06; U(name); no diagnóstico autónomo |
| ExpertRuleVersion: Reglas reproducibles aprobadas | expert_rule_versions | expert_rule_set_id → expert_rule_sets; approved_by → users | Perfil V de 06; U(expert_rule_set_id,version); despliegue requiere validación clínica |
| ExpertRun: Ejecución con entradas y explicación congeladas | expert_runs | person_id → persons; expert_rule_version_id → expert_rule_versions | Perfil H de 06; U(request_key); snapshot no fuente editable de clínica, referencias con tipo/id/versión/hash |
| ExpertReview: Revisión humana del resultado | expert_reviews | expert_run_id → expert_runs; professional_id → professionals; encounter_id → encounters | Perfil H de 06; Aceptar recomendación no registra prescripción automáticamente |

## 14. Reportes

**Justificación desde código:** Servicios y siete controladores de reportes reales; algunos llaman modelo inexistente o clasifican estado como entero. Migración no es pantalla clínica.

**Casos de uso:** Consultar vistas previas y gráficos; filtrar por periodo/área/residente; exportar PDF/Excel/CSV autorizados; reportes de salud, equipo, familia, actividad, ocupación y bitácora; conciliar migración.

**Pantallas necesarias:** ReportesIndex, ReportePreview, ExportacionesIndex, ExportacionShow; conciliación solo comando interno. Ruta de vistas: resources/views/modules/reportes/. Se pueden componer fichas y modales, sin exigir una URL por tabla.

**Dependencias:** Lectura de cada módulo a través de Queries autorizadas; Documentos almacena exportación privada; Identidad auditoría.

**Permisos:** catálogo por entidad y operaciones de 07; acciones particulares: report_exports.generar,report_exports.descargar,migration_links.auditar. Nunca conceder por profesión automáticamente.

| Entidad / modelo | Tabla propietaria | Relaciones de negocio | CRUD y regla |
|---|---|---|---|
| ReportExport: Evidencia de exportación autorizada | report_exports | requested_by → users; document_version_id → document_versions | Perfil W de 06; Reautorizar descarga; no persistir agregados como verdad clínica |
| MigrationLink: Procedencia y conciliación de migración | migration_links | Catálogo raíz | Perfil S de 06; U(batch_key,source_table,source_key,target_table,target_key); acceso exclusivo migración/auditoría; no consultas de negocio |

## Flujos compartidos sin duplicar escritura

- Expediente del residente monta paneles de Clínica, Medicación, VGI, Cuidados, Social y Documentos con la misma Policy y Action utilizadas en su pantalla especializada.
- Aprobación de caso coordina creación/enlace de residente, estancia, contactos y requisitos mediante una transacción; archivos/correo se procesan después del commit con identificador idempotente. Ningún módulo crea un segundo User por conveniencia.
- Alta de estancia finaliza camas/equipo/tareas pendientes según motivo y reglas; no anula retrospectivamente medicación administrada ni evaluaciones.
- Familia obtiene proyección de datos compartibles, nunca el modelo clínico serializado. Voluntario consulta sus asignaciones/disponibilidad/asistencia, sin expediente médico.
- Dashboards por trabajo (dirección, admisiones/ocupación, consulta clínica, turno, portal familiar/voluntario, administración técnica) son composiciones de Queries, no tablas ni módulos nuevos.

## Definición de módulo funcional

Ruta real y enlace autorizados; lista/filtros/paginación; crear y corregir donde corresponde; estados y motivos; validación server-side; detalle e historial; política contextual en HTTP y llamada Livewire directa; comportamiento vacío/error; archivos/exportaciones privados; pruebas de caso feliz y rechazo; conexión comprobada con módulos dependientes. Un dashboard genérico o botón sin acción no satisface esta definición. Todas las filas pendientes del inventario se completan dentro del plan, salvo alcance financiero sujeto a D12.

