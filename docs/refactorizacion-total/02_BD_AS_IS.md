# Base de datos AS-IS

Fecha: 10/09/2026. Rama MEJORA-SYS. **61 tablas físicas en public**, verificadas con SELECT a information_schema y pg_constraint dentro de BEGIN READ ONLY / ROLLBACK. No se leyeron registros personales para este inventario. Las 60 migraciones se inspeccionaron como evolución; su cantidad no es la cantidad de tablas. El esquema local utiliza PostgreSQL, aunque AGENTS.md menciona MySQL. No se ejecutaron migraciones ni comparación mediante reconstrucción de BD.

Clasificación expresa el destino estructural, no autorización de borrado. CONSERVAR permite renombrado y adaptación de clave. No se propone perder ninguna tabla con información útil: las estructuras redundantes se retiran **después** de migrar y conciliar. Timestamps/soft delete no equivalen por sí solos a historial clínico inmutable.

## Resumen de decisiones

| Tabla | Clasificación | Destino |
|---|---|---|
| acciones_alerta | MODIFICAR | alert_actions |
| actividades_adulto | DIVIDIR | activities, activity_participations |
| activity_log | DIVIDIR | activity_log, encounters, clinical_notes, vital_signs, admission_decisions |
| administracion_medicacion | MODIFICAR | medication_administrations |
| adulto_mayor | DIVIDIR | persons, person_identifiers, residents, person_contacts, consents, admissions, bed_assignments, allergies, resident_status_changes |
| alertas_adulto | DIVIDIR | alerts, alert_actions |
| areas_geriatricas | CONSERVAR | assessment_domains |
| areas_institucionales | MODIFICAR | areas, staff_assignments |
| asignacion_adulto_mayor | MODIFICAR | bed_assignments |
| asignacion_voluntarios | MODIFICAR | volunteer_assignments |
| asignaciones_plazas_enfermeria | DIVIDIR | staffing_slots, staffing_slot_assignments |
| asignaciones_turno_adulto | MODIFICAR | care_assignments |
| asistencia_voluntarios | MODIFICAR | volunteer_attendances |
| atenciones_adulto | MODIFICAR | encounters |
| cache | CONSERVAR | cache |
| cache_locks | CONSERVAR | cache_locks |
| camas | MODIFICAR | beds |
| disponibilidad_voluntarios | MODIFICAR | volunteer_availabilities |
| documentos_adulto_mayor | FUSIONAR | documents, document_versions, document_events |
| documentos_preadmision | FUSIONAR | documents, document_versions, document_events, document_types |
| documentos_usuarios | FUSIONAR | documents, document_versions, document_events |
| estado_adulto | REEMPLAZAR | resident_status_changes, admissions, admission_cases |
| evaluaciones_geriatricas | MODIFICAR | assessments |
| failed_jobs | CONSERVAR | failed_jobs |
| familiar_adulto | MODIFICAR | person_contacts |
| familiares | FUSIONAR | persons, person_identifiers, person_contacts |
| ficha_medica_adulto | DIVIDIR | encounters, clinical_notes, diagnoses, allergies |
| habitaciones | MODIFICAR | rooms |
| historial_estado_adulto | MODIFICAR | resident_status_changes |
| horarios_personal_admin | FUSIONAR | staff_schedules, staff_assignments |
| horarios_personal_salud | FUSIONAR | staff_schedules, staff_assignments |
| instrumentos_geriatricos | DIVIDIR | assessment_instruments, instrument_versions |
| job_batches | CONSERVAR | job_batches |
| jobs | CONSERVAR | jobs |
| medicacion_adulto | DIVIDIR | prescriptions, prescription_items |
| migrations | CONSERVAR | migrations |
| model_has_permissions | CONSERVAR | model_has_permissions |
| model_has_roles | CONSERVAR | model_has_roles |
| notas_evolucion_medica | DIVIDIR | encounters, clinical_notes, vital_signs |
| obs_adulto | FUSIONAR | daily_observations |
| pases_turno | MODIFICAR | handovers |
| password_reset_tokens | CONSERVAR | password_reset_tokens |
| permissions | CONSERVAR | permissions |
| personal_access_tokens | CONSERVAR | personal_access_tokens |
| planes_cuidado | DIVIDIR | care_plans, care_plan_versions |
| preadmisiones | DIVIDIR | persons, person_identifiers, person_contacts, admission_cases, admission_decisions |
| role_has_permissions | CONSERVAR | role_has_permissions |
| roles | CONSERVAR | roles |
| seguimientos_diarios | FUSIONAR | daily_observations, incidents |
| sessions | CONSERVAR | sessions |
| signos_vitales_adulto | MODIFICAR | vital_signs |
| tareas_plan_cuidado | DIVIDIR | care_tasks, care_task_executions |
| tipo_actividades_adulto | CONSERVAR | activity_types |
| tipo_atenciones_adulto | CONSERVAR | encounter_types |
| tipos_documentos_usuario | MODIFICAR | document_types |
| turnos_enfermeria | FUSIONAR | shifts |
| turnos_institucionales | FUSIONAR | shifts |
| users | DIVIDIR | persons, person_identifiers, users, professionals, staff_assignments, person_contacts |
| valoracion_enfermeria_admision | REEMPLAZAR | assessments, vital_signs |
| valoracion_funcional_adulto | FUSIONAR | assessments |
| voluntarios | DIVIDIR | persons, person_identifiers, volunteer_profiles |

## Diccionario físico completo

Tipos PostgreSQL observados; `?` permite NULL. Se incluyen todas las columnas y restricciones declaradas, sin inferir FKs por nombre. Las relaciones Eloquent se contrastan con estas restricciones; las referencias polimórficas no tienen integridad FK automática.

### acciones_alerta

- Propósito: Acciones de atención de alertas.
- Modelo: App\Models\AccionAlerta, PK cod_accion_alerta.
- Módulo propietario actual aproximado: Seguridad Asistencial; consumidores: `app/Livewire/Admin/Enfermeria/AlertasPanel.php`.
- Problemas y duplicación: Historial por filas; falta inmutabilidad y corrección.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → alert_actions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_accion_alerta | varchar | No |
| cod_alerta | varchar | No |
| accion | text | No |
| responsable_id | varchar | Sí |
| fecha_accion | timestamp | No |
| estado | varchar | No |
| observacion | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL accion`
- `NOT NULL cod_accion_alerta`
- `FOREIGN KEY (cod_alerta) REFERENCES alertas_adulto(cod_alerta) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_alerta`
- `NOT NULL estado`
- `NOT NULL fecha_accion`
- `PRIMARY KEY (cod_accion_alerta)`
- `FOREIGN KEY (responsable_id) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### actividades_adulto

- Propósito: Programación y asistencia individual en la misma fila.
- Modelo: App\Models\ActividadAdulto, PK cod_act_adul.
- Módulo propietario actual aproximado: Actividades y Voluntariado; consumidores: `app/Livewire/Admin/Actividades/ActividadesPanel.php`, `app/Livewire/Admin/Actividades/AsistenciaPanel.php`, `app/Livewire/Admin/Actividades/ParticipacionPanel.php`, `app/Livewire/Admin/Actividades/ReportesActividadesPanel.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorActividadController.php`.
- Problemas y duplicación: Se confunde evento con participación y se sobrescribe resultado.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → activities, activity_participations. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_act_adul | varchar | No |
| cod_am | varchar | No |
| cod_tipo_act | varchar | No |
| fecha | date | No |
| hora_inicio | time | Sí |
| hora_fin | time | Sí |
| observacion | text | Sí |
| estado | varchar | No |
| registrado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL cod_act_adul`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `FOREIGN KEY (cod_tipo_act) REFERENCES tipo_actividades_adulto(cod_tipo_act) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_tipo_act`
- `NOT NULL estado`
- `NOT NULL fecha`
- `PRIMARY KEY (cod_act_adul)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### activity_log

- Propósito: Auditoría transversal.
- Modelo: Spatie\Activitylog\Models\Activity.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: ValoracionMedicaModal guarda parte de la valoración solo en properties; DecisionAdmisionModal también registra decisiones allí. Es preciso extraer esos hechos verificables a entidades clínicas y conservar la auditoría original.
- Historial: Evidencia técnica y parte de la evidencia clínica heredada; no constituye un historial clínico completo ni recupera campos nunca guardados.
- Decisión: **DIVIDIR** → activity_log, encounters, clinical_notes, vital_signs, admission_decisions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int8 | No |
| log_name | varchar | Sí |
| description | text | No |
| subject_type | varchar | Sí |
| subject_id | varchar | Sí |
| causer_type | varchar | Sí |
| causer_id | varchar | Sí |
| event | varchar | Sí |
| batch_uuid | bpchar | Sí |
| properties | json | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL description`
- `NOT NULL id`
- `PRIMARY KEY (id)`

### administracion_medicacion

- Propósito: Dosis registrada u omitida.
- Modelo: App\Models\AdministracionMedicacion, PK cod_admin_med.
- Módulo propietario actual aproximado: Medicación; consumidores: `app/Livewire/Admin/AdultosMayores/Salud/AdministracionMedicacionModal.php`, `app/Livewire/Admin/Enfermeria/DashboardTurno.php`, `app/Livewire/Admin/SaludSeguimiento/SaludAdministracionMedicacionPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludSeguimientoListPanel.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorAdministracionMedicacionController.php`.
- Problemas y duplicación: Duplica residente de prescripción; falta idempotencia y corrección.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → medication_administrations. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_admin_med | varchar | No |
| cod_med_adulto | varchar | No |
| cod_am | varchar | No |
| fecha | date | No |
| hora_programada | time | No |
| hora_real | time | Sí |
| administrado | bool | No |
| motivo_omision | text | Sí |
| efecto_observado | text | Sí |
| observacion | text | Sí |
| registrado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL administrado`
- `NOT NULL cod_admin_med`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `FOREIGN KEY (cod_med_adulto) REFERENCES medicacion_adulto(cod_med_adulto) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_med_adulto`
- `NOT NULL fecha`
- `NOT NULL hora_programada`
- `PRIMARY KEY (cod_admin_med)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### adulto_mayor

- Propósito: Identidad, expediente, ingreso, cama y consentimiento juntos.
- Modelo: App\Models\AdultoMayor, PK cod_am.
- Módulo propietario actual aproximado: Residentes; consumidores: `app/Livewire/Admin/Actividades/ActividadesPanel.php`, `app/Livewire/Admin/Actividades/ParticipacionPanel.php`, `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php`, `app/Livewire/Admin/AdultosMayores/AdultoMayorFormModal.php`, `app/Livewire/Admin/AdultosMayores/AdultosMayoresPanel.php`, `app/Livewire/Admin/AdultosMayores/AlertasPendientesPanel.php`, `app/Livewire/Admin/AdultosMayores/Reportes/ReportesAdultoPanel.php`, `app/Livewire/Admin/AdultosMayores/ReportesInstitucionalesPanel.php`, `app/Livewire/Admin/Enfermeria/AlertasPanel.php`, `app/Livewire/Admin/Enfermeria/AsignacionTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/FichaPaciente.php`, `app/Livewire/Admin/Enfermeria/MisPacientes.php`, `app/Livewire/Admin/Enfermeria/PaseTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/PlanCuidadoPanel.php`, `app/Livewire/Admin/Enfermeria/SeguimientoDiarioPanel.php`, `app/Livewire/Admin/Enfermeria/ValoracionInicialModal.php`, `app/Livewire/Admin/Enfermeria/ValoracionMedicaPanel.php`, `app/Livewire/Admin/FamiliaSocial/RedApoyoPanel.php`, `app/Livewire/Admin/Medico/DashboardMedico.php`, `app/Livewire/Admin/Medico/DecisionAdmisionModal.php`, `app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php`, `app/Livewire/Admin/Medico/NotaEvolucionMedicaModal.php`, `app/Livewire/Admin/Medico/PacientesSeguimientoPanel.php`, `app/Livewire/Admin/Medico/RegistroSignosVitalesModal.php`, `app/Livewire/Admin/Medico/SignosVitalesPanel.php`, `app/Livewire/Admin/Medico/ValoracionBarthelModal.php`, `app/Livewire/Admin/Medico/ValoracionMedicaModal.php`, `app/Livewire/Admin/Psicologia/DashboardPsicologo.php`, `app/Livewire/Admin/Psicologia/EvaluacionGeriatricaAreaModal.php`, `app/Livewire/Admin/SaludSeguimiento/SaludAdministracionMedicacionPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludAlertasPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludEvaluacionesGeriatricasPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludFichaPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludMedicacionPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludReportesPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludResumenPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludSeguimientoListPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludSignosPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludValoracionPanel.php`, `app/Services/Admin/AdultoMayorService.php`, `app/Services/Reportes/ReportChartDataService.php`, `app/Http/Controllers/Admin/AdultoMayorController.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorActividadController.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorAtencionController.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorDocumentoController.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorEvaluacionController.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorFamiliarController.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorObservacionController.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorAdministracionMedicacionController.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorFichaMedicaController.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorMedicacionController.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorSignosVitalesController.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorValoracionFuncionalController.php`, `app/Http/Controllers/Admin/Enfermeria/FichaPacienteReporteController.php`.
- Problemas y duplicación: Duplica preadmisión/familiares/alergias/ocupación; archivado no historial de cambios.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → persons, person_identifiers, residents, person_contacts, consents, admissions, bed_assignments, allergies, resident_status_changes. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_am | varchar | No |
| nombres | varchar | No |
| ap_paterno | varchar | No |
| ap_materno | varchar | Sí |
| ci | varchar | Sí |
| complemento_ci | varchar | Sí |
| expedicion_ci | varchar | Sí |
| fecha_nac | date | No |
| genero | varchar | No |
| estado_civil | varchar | Sí |
| telefono | varchar | Sí |
| tiene_celular | varchar | No |
| celular | varchar | Sí |
| sabe_usar_whatsapp | varchar | No |
| telefono_fijo | varchar | Sí |
| departamento_residencia | varchar | Sí |
| ciudad_municipio | varchar | Sí |
| zona | varchar | Sí |
| calle | varchar | Sí |
| fecha_ing | date | No |
| hora_ing | time | Sí |
| tipo_ing | varchar | No |
| permanencia | varchar | Sí |
| nivel_educat | varchar | Sí |
| grupo_sanguineo | varchar | Sí |
| factor_rh | varchar | Sí |
| alergias | text | Sí |
| seguro_salud | varchar | Sí |
| contacto_emergencia_nombre | varchar | Sí |
| contacto_emergencia_parentesco | varchar | Sí |
| contacto_emergencia_celular | varchar | Sí |
| contacto_emergencia_direccion | varchar | Sí |
| responsable_principal | varchar | Sí |
| autorizado_informacion_medica | varchar | Sí |
| consentimiento_datos | bool | No |
| observaciones | text | Sí |
| cod_est_adul | varchar | No |
| foto | varchar | Sí |
| archivado_en | timestamp | Sí |
| motivo_archivado | text | Sí |
| motivo_ingreso | text | Sí |
| procedencia_ingreso | varchar | Sí |
| cod_habitacion | varchar | Sí |
| cod_cama | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| cod_pre_origen | varchar | Sí |

Restricciones físicas:

- `NOT NULL ap_paterno`
- `UNIQUE (ci)`
- `NOT NULL cod_am`
- `FOREIGN KEY (cod_cama) REFERENCES camas(cod_cama) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_est_adul) REFERENCES estado_adulto(cod_est_adul) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_est_adul`
- `FOREIGN KEY (cod_habitacion) REFERENCES habitaciones(cod_habitacion) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_pre_origen) REFERENCES preadmisiones(cod_pre) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL consentimiento_datos`
- `NOT NULL fecha_ing`
- `NOT NULL fecha_nac`
- `NOT NULL genero`
- `NOT NULL nombres`
- `PRIMARY KEY (cod_am)`
- `NOT NULL sabe_usar_whatsapp`
- `NOT NULL tiene_celular`
- `NOT NULL tipo_ing`

### alertas_adulto

- Propósito: Riesgos, atención y cierre actuales.
- Modelo: App\Models\AlertaAdulto, PK cod_alerta.
- Módulo propietario actual aproximado: Seguridad Asistencial; consumidores: `app/Livewire/Admin/Enfermeria/AlertasPanel.php`, `app/Livewire/Admin/Enfermeria/DashboardTurno.php`, `app/Livewire/Admin/Enfermeria/PaseTurnoPanel.php`.
- Problemas y duplicación: accion_tomada duplica acciones; estado es mutable.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → alerts, alert_actions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_alerta | varchar | No |
| cod_am | varchar | No |
| cod_turno | varchar | Sí |
| origen | varchar | No |
| tipo_alerta | varchar | No |
| nivel | varchar | No |
| motivo | text | No |
| responsable_id | varchar | Sí |
| estado | varchar | No |
| accion_tomada | text | Sí |
| fecha_atencion | timestamp | Sí |
| atendido_por | varchar | Sí |
| fecha_cierre | timestamp | Sí |
| cerrado_por | varchar | Sí |
| observacion_cierre | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (atendido_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cerrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL cod_alerta`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_am`
- `FOREIGN KEY (cod_turno) REFERENCES turnos_enfermeria(cod_turno) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL estado`
- `NOT NULL motivo`
- `NOT NULL nivel`
- `NOT NULL origen`
- `PRIMARY KEY (cod_alerta)`
- `FOREIGN KEY (responsable_id) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL tipo_alerta`

### areas_geriatricas

- Propósito: Dimensiones de VGI.
- Modelo: App\Models\AreaGeriatrica, PK cod_area.
- Módulo propietario actual aproximado: Valoración Geriátrica Integral; consumidores: `app/Livewire/Admin/AdultosMayores/Evaluaciones/EvaluacionGeriatricaModal.php`, `app/Livewire/Admin/Psicologia/DashboardPsicologo.php`, `app/Livewire/Admin/Psicologia/EvaluacionesAreaPanel.php`, `app/Livewire/Admin/Psicologia/EvaluacionGeriatricaAreaModal.php`.
- Problemas y duplicación: No es área institucional; catálogo sin versionado de cambios.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **CONSERVAR** → assessment_domains. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_area | varchar | No |
| nombre | varchar | No |
| descripcion | text | Sí |
| estado | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL cod_area`
- `NOT NULL estado`
- `NOT NULL nombre`
- `UNIQUE (nombre)`
- `PRIMARY KEY (cod_area)`

### areas_institucionales

- Propósito: Unidades con responsable y presentación.
- Modelo: App\Models\AreaInstitucional, PK cod_area.
- Módulo propietario actual aproximado: Institución y Personal; consumidores: `app/Livewire/Admin/AreasInstitucionales/AreasInstitucionalesPanel.php`, `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalForm.php`, `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalHorarios.php`, `app/Livewire/Admin/Usuarios/UsuariosPanel.php`, `app/Services/Reportes/AreasReportDataService.php`, `app/Services/Reportes/ReportChartDataService.php`, `app/Http/Controllers/Admin/AreasInstitucionales/AreaReporteController.php`, `tests/Feature/PersonalInstitucionalHorariosTest.php`.
- Problemas y duplicación: roles_sugeridos no deben imponer profesión/permisos; responsable sin vigencia.
- Historial: Estado mutable con timestamps y soft delete; no historial completo de atributos.
- Decisión: **MODIFICAR** → areas, staff_assignments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_area | varchar | No |
| nombre | varchar | No |
| slug | varchar | No |
| tipo_area | varchar | No |
| descripcion | text | Sí |
| responsable_id | varchar | Sí |
| roles_sugeridos | json | Sí |
| modulos_relacionados | json | Sí |
| color | varchar | Sí |
| icono | varchar | No |
| estado | varchar | No |
| orden | int4 | No |
| observaciones | text | Sí |
| imagen_area | varchar | Sí |
| creado_por | varchar | Sí |
| actualizado_por | varchar | Sí |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL cod_area`
- `NOT NULL estado`
- `NOT NULL icono`
- `NOT NULL nombre`
- `UNIQUE (nombre)`
- `NOT NULL orden`
- `PRIMARY KEY (cod_area)`
- `NOT NULL slug`
- `UNIQUE (slug)`
- `NOT NULL tipo_area`

### asignacion_adulto_mayor

- Propósito: Ubicación del adulto y registro de asignación.
- Modelo: App\Models\AsignacionAdultoMayor, PK cod_asig_adulto.
- Módulo propietario actual aproximado: Admisiones y Ocupación; consumidores: `app/Livewire/Admin/Enfermeria/PlanCuidadoPanel.php`.
- Problemas y duplicación: Duplica habitación/cama de adulto; no fin temporal explícito ni exclusión concurrente.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → bed_assignments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_asig_adulto | varchar | No |
| cod_am | varchar | No |
| cod_habitacion | varchar | Sí |
| cod_cama | varchar | Sí |
| fecha_asignacion | date | No |
| hora_asignacion | time | Sí |
| estado | varchar | No |
| observaciones | text | Sí |
| registrado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_asig_adulto`
- `FOREIGN KEY (cod_cama) REFERENCES camas(cod_cama) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_habitacion) REFERENCES habitaciones(cod_habitacion) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL estado`
- `NOT NULL fecha_asignacion`
- `PRIMARY KEY (cod_asig_adulto)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### asignacion_voluntarios

- Propósito: Programa colaboración con residente.
- Modelo: App\Models\AsignacionVoluntario, PK cod_asig_vol.
- Módulo propietario actual aproximado: Actividades y Voluntariado; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Sin FK de asistencia ni disponibilidad; estados heterogéneos.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **MODIFICAR** → volunteer_assignments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_asig_vol | varchar | No |
| cod_vol | varchar | No |
| cod_am | varchar | No |
| fecha_asig | date | No |
| fecha_fin | date | Sí |
| estado | varchar | No |
| obser | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_asig_vol`
- `FOREIGN KEY (cod_vol) REFERENCES voluntarios(cod_vol) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_vol`
- `NOT NULL estado`
- `NOT NULL fecha_asig`
- `PRIMARY KEY (cod_asig_vol)`

### asignaciones_plazas_enfermeria

- Propósito: Asigna personas a plazas rotativas.
- Modelo: App\Models\AsignacionPlazaEnfermeria, PK id.
- Módulo propietario actual aproximado: Institución y Personal; consumidores: `app/Livewire/Admin/Admisiones/PreadmisionWizard.php`.
- Problemas y duplicación: Plaza textual y vigencia incompleta; no es asignación de cama.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → staffing_slots, staffing_slot_assignments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int8 | No |
| plaza | varchar | No |
| cod_usu | varchar | Sí |
| tipo | varchar | No |
| fecha | date | Sí |
| motivo | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_usu) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL id`
- `PRIMARY KEY (id)`
- `NOT NULL plaza`
- `NOT NULL tipo`

### asignaciones_turno_adulto

- Propósito: Responsable de residente por turno.
- Modelo: App\Models\AsignacionTurnoAdulto, PK cod_asig_turno.
- Módulo propietario actual aproximado: Cuidados; consumidores: `app/Livewire/Admin/Enfermeria/AsignacionTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/DashboardTurno.php`.
- Problemas y duplicación: Duplica ubicación física y mezcla plantilla de turno con jornada concreta.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → care_assignments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_asig_turno | varchar | No |
| cod_am | varchar | No |
| cod_turno | varchar | No |
| cod_usu_enfermero | varchar | No |
| cod_habitacion | varchar | Sí |
| cod_cama | varchar | Sí |
| fecha_inicio | date | No |
| fecha_fin | date | Sí |
| nivel_supervision | varchar | No |
| estado | varchar | No |
| motivo_asignacion | text | Sí |
| asignado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (asignado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_asig_turno`
- `FOREIGN KEY (cod_cama) REFERENCES camas(cod_cama) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_habitacion) REFERENCES habitaciones(cod_habitacion) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_turno) REFERENCES turnos_enfermeria(cod_turno) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_turno`
- `FOREIGN KEY (cod_usu_enfermero) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_usu_enfermero`
- `NOT NULL estado`
- `NOT NULL fecha_inicio`
- `NOT NULL nivel_supervision`
- `PRIMARY KEY (cod_asig_turno)`

### asistencia_voluntarios

- Propósito: Entradas/salidas y ausencias.
- Modelo: App\Models\AsistenciaVoluntario, PK cod_asis_vol.
- Módulo propietario actual aproximado: Actividades y Voluntariado; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: No FK de asignación; observaciones duplicadas; inferencia por voluntario/fecha ambigua.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → volunteer_attendances. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_asis_vol | varchar | No |
| cod_vol | varchar | No |
| cod_am | varchar | Sí |
| fecha | date | No |
| hora_entrada | time | Sí |
| hora_salida | time | Sí |
| actividad_realizada | text | Sí |
| novedades_observaciones | text | Sí |
| estado | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| observaciones | text | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL cod_asis_vol`
- `FOREIGN KEY (cod_vol) REFERENCES voluntarios(cod_vol) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_vol`
- `NOT NULL estado`
- `NOT NULL fecha`
- `PRIMARY KEY (cod_asis_vol)`

### atenciones_adulto

- Propósito: Atención tipificada por residente.
- Modelo: App\Models\AtencionAdulto, PK cod_aten_adul.
- Módulo propietario actual aproximado: Clínica; consumidores: `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorAtencionController.php`.
- Problemas y duplicación: Histórico por filas pero mutable; falta profesional/episodio explícito.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → encounters. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_aten_adul | varchar | No |
| cod_am | varchar | No |
| cod_tipo_aten | varchar | No |
| fecha | date | No |
| hora | time | Sí |
| observacion | text | Sí |
| estado | varchar | No |
| registrado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_aten_adul`
- `FOREIGN KEY (cod_tipo_aten) REFERENCES tipo_atenciones_adulto(cod_tipo_aten) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_tipo_aten`
- `NOT NULL estado`
- `NOT NULL fecha`
- `PRIMARY KEY (cod_aten_adul)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### cache

- Propósito: Caché.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → cache. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| key | varchar | No |
| value | text | No |
| expiration | int8 | No |

Restricciones físicas:

- `NOT NULL expiration`
- `NOT NULL key`
- `PRIMARY KEY (key)`
- `NOT NULL value`

### cache_locks

- Propósito: Locks de caché.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → cache_locks. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| key | varchar | No |
| owner | varchar | No |
| expiration | int8 | No |

Restricciones físicas:

- `NOT NULL expiration`
- `NOT NULL key`
- `NOT NULL owner`
- `PRIMARY KEY (key)`

### camas

- Propósito: Inventario físico de camas.
- Modelo: App\Models\Cama, PK cod_cama.
- Módulo propietario actual aproximado: Admisiones y Ocupación; consumidores: `app/Livewire/Admin/Enfermeria/AsignacionTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/HabitacionesPanel.php`.
- Problemas y duplicación: Estado mezcla disponibilidad y ocupación; observacion/observaciones duplicadas.
- Historial: Estado mutable con timestamps y soft delete; no historial completo de atributos.
- Decisión: **MODIFICAR** → beds. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_cama | varchar | No |
| cod_habitacion | varchar | No |
| codigo | varchar | No |
| estado | varchar | No |
| observaciones | text | Sí |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| observacion | text | Sí |
| numero | int2 | Sí |

Restricciones físicas:

- `NOT NULL cod_cama`
- `FOREIGN KEY (cod_habitacion) REFERENCES habitaciones(cod_habitacion) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_habitacion`
- `NOT NULL codigo`
- `UNIQUE (codigo)`
- `NOT NULL estado`
- `PRIMARY KEY (cod_cama)`

### disponibilidad_voluntarios

- Propósito: Horario semanal de colaboración.
- Modelo: App\Models\DisponibilidadVoluntario, PK cod_hor_vol.
- Módulo propietario actual aproximado: Actividades y Voluntariado; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: obser/observaciones duplicadas; vigencia implícita.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → volunteer_availabilities. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_hor_vol | varchar | No |
| cod_vol | varchar | No |
| dia_semana | varchar | No |
| hora_inicio | time | No |
| hora_fin | time | No |
| estado | varchar | No |
| obser | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| observaciones | text | Sí |

Restricciones físicas:

- `NOT NULL cod_hor_vol`
- `FOREIGN KEY (cod_vol) REFERENCES voluntarios(cod_vol) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_vol`
- `NOT NULL dia_semana`
- `NOT NULL estado`
- `NOT NULL hora_fin`
- `NOT NULL hora_inicio`
- `PRIMARY KEY (cod_hor_vol)`

### documentos_adulto_mayor

- Propósito: Archivos del expediente y recetas.
- Modelo: App\Models\DocumentoAdultoMayor, PK cod_doc_am.
- Módulo propietario actual aproximado: Documentos; consumidores: `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorDocumentoController.php`.
- Problemas y duplicación: Repite infraestructura documental; sin versiones formales.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **FUSIONAR** → documents, document_versions, document_events. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_doc_am | varchar | No |
| cod_am | varchar | No |
| nombre | varchar | No |
| tipo_documento | varchar | No |
| ruta_archivo | varchar | No |
| fecha_subida | date | No |
| estado | varchar | No |
| observaciones | text | Sí |
| modulo_ref | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_doc_am`
- `NOT NULL estado`
- `NOT NULL fecha_subida`
- `NOT NULL nombre`
- `PRIMARY KEY (cod_doc_am)`
- `NOT NULL ruta_archivo`
- `NOT NULL tipo_documento`

### documentos_preadmision

- Propósito: Checklist, archivos y generación inicial.
- Modelo: App\Models\DocumentoPreadmision, PK cod_doc_pre.
- Módulo propietario actual aproximado: Documentos; consumidores: `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php`, `app/Livewire/Admin/Admisiones/PreadmisionWizard.php`.
- Problemas y duplicación: Repite documentos del residente al aprobar; requisitos mezclados con entrega.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **FUSIONAR** → documents, document_versions, document_events, document_types. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_doc_pre | varchar | No |
| cod_pre | varchar | No |
| tipo_documento | varchar | No |
| nombre_documento | varchar | No |
| archivo_path | varchar | Sí |
| nombre_original | varchar | Sí |
| es_institucional | bool | No |
| obligatorio | bool | No |
| estado | varchar | No |
| observaciones | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| grupo_documento | varchar | No |
| es_generado_sistema | bool | No |
| bloquea_avance | bool | No |
| permite_48h | bool | No |
| fecha_limite_entrega | timestamp | Sí |
| fecha_generacion | timestamp | Sí |

Restricciones físicas:

- `NOT NULL bloquea_avance`
- `NOT NULL cod_doc_pre`
- `FOREIGN KEY (cod_pre) REFERENCES preadmisiones(cod_pre) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_pre`
- `NOT NULL es_generado_sistema`
- `NOT NULL es_institucional`
- `NOT NULL estado`
- `NOT NULL grupo_documento`
- `NOT NULL nombre_documento`
- `NOT NULL obligatorio`
- `NOT NULL permite_48h`
- `PRIMARY KEY (cod_doc_pre)`
- `NOT NULL tipo_documento`

### documentos_usuarios

- Propósito: Expediente documental del usuario.
- Modelo: App\Models\DocumentoUsuario, PK cod_doc_usu.
- Módulo propietario actual aproximado: Documentos; consumidores: `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalDocumentos.php`, `app/Livewire/Admin/Usuarios/UsuarioFichaPanel.php`, `app/Services/Usuarios/DocumentacionUsuarioService.php`.
- Problemas y duplicación: archivo/archivo_path y tipo duplicados; reemplaza_a aproxima versiones.
- Historial: Estado mutable con timestamps y soft delete; no historial completo de atributos.
- Decisión: **FUSIONAR** → documents, document_versions, document_events. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_doc_usu | varchar | No |
| cod_usu | varchar | No |
| cod_tipo_doc | varchar | No |
| archivo_path | varchar | No |
| nombre_original | varchar | No |
| fecha_subida | timestamp | No |
| fecha_vencimiento | date | Sí |
| observaciones | text | Sí |
| estado | varchar | No |
| validado_por | varchar | Sí |
| fecha_validacion | timestamp | Sí |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| tipo_documento | varchar | Sí |
| nombre_documento | varchar | Sí |
| archivo | varchar | Sí |
| mime_type | varchar | Sí |
| extension | varchar | Sí |
| tamanio | int8 | Sí |
| fecha_emision | date | Sí |
| motivo_observacion | text | Sí |
| subido_por | varchar | Sí |
| reemplaza_a | varchar | Sí |
| creado_por | varchar | Sí |
| actualizado_por | varchar | Sí |

Restricciones físicas:

- `NOT NULL archivo_path`
- `NOT NULL cod_doc_usu`
- `FOREIGN KEY (cod_tipo_doc) REFERENCES tipos_documentos_usuario(cod_tipo_doc) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_tipo_doc`
- `FOREIGN KEY (cod_usu) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_usu`
- `NOT NULL estado`
- `NOT NULL fecha_subida`
- `NOT NULL nombre_original`
- `PRIMARY KEY (cod_doc_usu)`
- `FOREIGN KEY (validado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### estado_adulto

- Propósito: Catálogo que mezcla flujo y condición del adulto.
- Modelo: App\Models\EstadoAdulto, PK cod_est_adul.
- Módulo propietario actual aproximado: Residentes; consumidores: `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php`, `app/Livewire/Admin/AdultosMayores/ReportesInstitucionalesPanel.php`, `app/Livewire/Admin/Enfermeria/ValoracionInicialModal.php`, `app/Http/Controllers/Admin/AdultoMayorController.php`.
- Problemas y duplicación: No confundir estado clínico, preadmisión y estancia; definición pasa a enums tipados de código.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **REEMPLAZAR** → resident_status_changes, admissions, admission_cases. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_est_adul | varchar | No |
| estado | varchar | No |

Restricciones físicas:

- `NOT NULL cod_est_adul`
- `NOT NULL estado`
- `PRIMARY KEY (cod_est_adul)`

### evaluaciones_geriatricas

- Propósito: Resultado y formulario de VGI.
- Modelo: App\Models\EvaluacionGeriatrica, PK cod_eval_ger.
- Módulo propietario actual aproximado: Valoración Geriátrica Integral; consumidores: `app/Livewire/Admin/AdultosMayores/Evaluaciones/EvaluacionGeriatricaModal.php`, `app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php`, `app/Livewire/Admin/Psicologia/DashboardPsicologo.php`, `app/Livewire/Admin/Psicologia/EvaluacionesAreaPanel.php`, `app/Livewire/Admin/Psicologia/EvaluacionGeriatricaAreaModal.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorEvaluacionController.php`.
- Problemas y duplicación: Puntajes/estados/autores duplicados, interpretación se pisa con riesgo; anulador mal relacionado.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → assessments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_eval_ger | varchar | No |
| cod_am | varchar | No |
| cod_instrumento | varchar | No |
| fecha_eval | date | No |
| puntaje | numeric | Sí |
| resultado_cualitativo | varchar | Sí |
| observaciones | text | Sí |
| evaluador_id | varchar | Sí |
| estado | varchar | No |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| registrado_por | varchar | Sí |
| hora_eval | time | Sí |
| puntaje_total | numeric | Sí |
| categoria_resultado | varchar | Sí |
| nivel_alerta | varchar | Sí |
| nivel_riesgo | varchar | Sí |
| estado_eval | varchar | Sí |
| datos_formulario | json | Sí |
| motivo_anulacion | text | Sí |
| anulado_por | varchar | Sí |
| anulado_en | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_eval_ger`
- `FOREIGN KEY (cod_instrumento) REFERENCES instrumentos_geriatricos(cod_instrumento) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_instrumento`
- `NOT NULL estado`
- `FOREIGN KEY (evaluador_id) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL fecha_eval`
- `PRIMARY KEY (cod_eval_ger)`

### failed_jobs

- Propósito: Fallos de trabajos.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Evidencia técnica; no reemplaza historia clínica.
- Decisión: **CONSERVAR** → failed_jobs. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int8 | No |
| uuid | varchar | No |
| connection | text | No |
| queue | text | No |
| payload | text | No |
| exception | text | No |
| failed_at | timestamp | No |

Restricciones físicas:

- `NOT NULL connection`
- `NOT NULL exception`
- `NOT NULL failed_at`
- `NOT NULL id`
- `NOT NULL payload`
- `PRIMARY KEY (id)`
- `NOT NULL queue`
- `NOT NULL uuid`
- `UNIQUE (uuid)`

### familiar_adulto

- Propósito: Vínculo con responsabilidad específica.
- Modelo: App\Models\FamiliarAdulto, PK id.
- Módulo propietario actual aproximado: Social y Familia; consumidores: `app/Livewire/Admin/FamiliaSocial/RedApoyoPanel.php`.
- Problemas y duplicación: Parentesco también en familiares; soft delete no preserva correcciones.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → person_contacts. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int8 | No |
| cod_fam | varchar | No |
| cod_am | varchar | No |
| parentesco_vinculo | varchar | Sí |
| es_responsable | bool | No |
| estado | varchar | No |
| observaciones | text | Sí |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `FOREIGN KEY (cod_fam) REFERENCES familiares(cod_fam) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_fam`
- `NOT NULL es_responsable`
- `NOT NULL estado`
- `NOT NULL id`
- `PRIMARY KEY (id)`
- `UNIQUE (cod_fam, cod_am)`

### familiares

- Propósito: Identidad del contacto con cuenta opcional.
- Modelo: App\Models\Familiar, PK cod_fam.
- Módulo propietario actual aproximado: Social y Familia; consumidores: `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php`, `app/Livewire/Admin/FamiliaSocial/RedApoyoPanel.php`, `app/Http/Controllers/Admin/AdultoMayorController.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorFamiliarController.php`.
- Problemas y duplicación: Duplica users; parentesco global inválido con varios residentes.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **FUSIONAR** → persons, person_identifiers, person_contacts. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_fam | varchar | No |
| nombres | varchar | No |
| ap_paterno | varchar | No |
| ap_materno | varchar | Sí |
| ci | varchar | Sí |
| parentesco_vinculo | varchar | Sí |
| telefono | varchar | Sí |
| celular | varchar | Sí |
| correo | varchar | Sí |
| direccion | varchar | Sí |
| zona | varchar | Sí |
| es_responsable | bool | No |
| estado | varchar | No |
| observaciones | text | Sí |
| cod_usu | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL ap_paterno`
- `UNIQUE (ci)`
- `NOT NULL cod_fam`
- `FOREIGN KEY (cod_usu) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL es_responsable`
- `NOT NULL estado`
- `NOT NULL nombres`
- `PRIMARY KEY (cod_fam)`

### ficha_medica_adulto

- Propósito: Antecedentes y lista fija de enfermedades.
- Modelo: App\Models\FichaMedicaAdulto, PK cod_ficha_medica.
- Módulo propietario actual aproximado: Clínica; consumidores: `app/Livewire/Admin/AdultosMayores/Salud/FichaMedicaAdultoModal.php`, `app/Livewire/Admin/Enfermeria/ValoracionMedicaPanel.php`, `app/Livewire/Admin/Medico/DashboardMedico.php`, `app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludFichaPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludSeguimientoListPanel.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorFichaMedicaController.php`.
- Problemas y duplicación: Ficha editable, alergias duplicadas; booleanos sin certeza ni fecha diagnóstica.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **DIVIDIR** → encounters, clinical_notes, diagnoses, allergies. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_ficha_medica | varchar | No |
| cod_am | varchar | No |
| hipertension | bool | No |
| diabetes | bool | No |
| problemas_cardiacos | bool | No |
| acv | bool | No |
| parkinson | bool | No |
| epilepsia | bool | No |
| alzheimer_diagnosticado | bool | No |
| depresion | bool | No |
| ansiedad | bool | No |
| problemas_sueno | bool | No |
| problemas_visuales | bool | No |
| problemas_auditivos | bool | No |
| dolor_cronico | bool | No |
| alergias | text | Sí |
| restricciones_alimentarias | text | Sí |
| hospitalizaciones | text | Sí |
| cirugias | text | Sí |
| observacion_medica | text | Sí |
| registrado_por | varchar | Sí |
| estado | varchar | No |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL acv`
- `NOT NULL alzheimer_diagnosticado`
- `NOT NULL ansiedad`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_ficha_medica`
- `NOT NULL depresion`
- `NOT NULL diabetes`
- `NOT NULL dolor_cronico`
- `NOT NULL epilepsia`
- `NOT NULL estado`
- `NOT NULL hipertension`
- `NOT NULL parkinson`
- `PRIMARY KEY (cod_ficha_medica)`
- `NOT NULL problemas_auditivos`
- `NOT NULL problemas_cardiacos`
- `NOT NULL problemas_sueno`
- `NOT NULL problemas_visuales`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### habitaciones

- Propósito: Espacios, capacidad y ubicación.
- Modelo: App\Models\Habitacion, PK cod_habitacion.
- Módulo propietario actual aproximado: Admisiones y Ocupación; consumidores: `app/Livewire/Admin/Enfermeria/AsignacionTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/HabitacionesPanel.php`.
- Problemas y duplicación: Campos observacion/observaciones; capacidad puede diferir de camas.
- Historial: Estado mutable con timestamps y soft delete; no historial completo de atributos.
- Decisión: **MODIFICAR** → rooms. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_habitacion | varchar | No |
| codigo | varchar | No |
| nombre | varchar | No |
| tipo_habitacion | varchar | No |
| capacidad | int4 | No |
| estado | varchar | No |
| observaciones | text | Sí |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| ubicacion | varchar | Sí |
| observacion | text | Sí |
| piso | int2 | Sí |
| descripcion | text | Sí |

Restricciones físicas:

- `NOT NULL capacidad`
- `NOT NULL cod_habitacion`
- `NOT NULL codigo`
- `UNIQUE (codigo)`
- `NOT NULL estado`
- `NOT NULL nombre`
- `PRIMARY KEY (cod_habitacion)`
- `NOT NULL tipo_habitacion`

### historial_estado_adulto

- Propósito: Transiciones con motivo y soporte.
- Modelo: App\Models\HistorialEstadoAdulto, PK cod_hist_estado.
- Módulo propietario actual aproximado: Residentes; consumidores: `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php`, `app/Livewire/Admin/AdultosMayores/Salud/HistorialEstadoAdultoPanel.php`, `app/Livewire/Admin/Enfermeria/ValoracionMedicaPanel.php`, `app/Livewire/Admin/Medico/DecisionAdmisionModal.php`.
- Problemas y duplicación: Sí historial; mezcla dimensiones y no sustituye estancia temporal.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → resident_status_changes. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_hist_estado | varchar | No |
| cod_am | varchar | No |
| estado_anterior | varchar | Sí |
| estado_nuevo | varchar | No |
| fecha_cambio | timestamp | No |
| motivo | text | No |
| documento_respaldo | varchar | Sí |
| cambiado_por | varchar | Sí |
| observacion | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cambiado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_am`
- `NOT NULL cod_hist_estado`
- `FOREIGN KEY (documento_respaldo) REFERENCES documentos_adulto_mayor(cod_doc_am) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (estado_anterior) REFERENCES estado_adulto(cod_est_adul) ON UPDATE CASCADE ON DELETE RESTRICT`
- `FOREIGN KEY (estado_nuevo) REFERENCES estado_adulto(cod_est_adul) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL estado_nuevo`
- `NOT NULL fecha_cambio`
- `NOT NULL motivo`
- `PRIMARY KEY (cod_hist_estado)`

### horarios_personal_admin

- Propósito: Horarios de personal administrativo.
- Modelo: App\Models\HorarioPersonalAdmin, PK cod_hor_per_admin.
- Módulo propietario actual aproximado: Institución y Personal; consumidores: `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalHorarios.php`, `app/Livewire/Admin/PersonalInstitucional/TurnosAsignacionesPanel.php`.
- Problemas y duplicación: Misma estructura que salud; fechas y metadata inferidas de timestamps/JSON.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **FUSIONAR** → staff_schedules, staff_assignments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_hor_per_admin | varchar | No |
| dia_semana | varchar | No |
| hora_inicio | time | No |
| hora_fin | time | No |
| turno | varchar | No |
| estado | varchar | No |
| observaciones | text | Sí |
| cod_usu | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| cod_per_adm | varchar | Sí |

Restricciones físicas:

- `NOT NULL cod_hor_per_admin`
- `FOREIGN KEY (cod_usu) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_usu`
- `NOT NULL dia_semana`
- `NOT NULL estado`
- `NOT NULL hora_fin`
- `NOT NULL hora_inicio`
- `PRIMARY KEY (cod_hor_per_admin)`
- `NOT NULL turno`

### horarios_personal_salud

- Propósito: Horarios de personal de salud.
- Modelo: App\Models\HorarioPersonalSalud, PK cod_hor_per_sal.
- Módulo propietario actual aproximado: Institución y Personal; consumidores: `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalHorarios.php`, `app/Livewire/Admin/PersonalInstitucional/TurnosAsignacionesPanel.php`, `tests/Feature/PersonalInstitucionalHorariosTest.php`.
- Problemas y duplicación: Misma estructura que admin; cod_per_sal no modelo real.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **FUSIONAR** → staff_schedules, staff_assignments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_hor_per_sal | varchar | No |
| dia_semana | varchar | No |
| hora_inicio | time | No |
| hora_fin | time | No |
| turno | varchar | No |
| estado | varchar | No |
| observaciones | text | Sí |
| cod_usu | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| cod_per_sal | varchar | Sí |

Restricciones físicas:

- `NOT NULL cod_hor_per_sal`
- `FOREIGN KEY (cod_usu) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_usu`
- `NOT NULL dia_semana`
- `NOT NULL estado`
- `NOT NULL hora_fin`
- `NOT NULL hora_inicio`
- `PRIMARY KEY (cod_hor_per_sal)`
- `NOT NULL turno`

### instrumentos_geriatricos

- Propósito: Catálogo, límites y cortes en registro editable.
- Modelo: App\Models\InstrumentoGeriatrico, PK cod_instrumento.
- Módulo propietario actual aproximado: Valoración Geriátrica Integral; consumidores: `app/Livewire/Admin/AdultosMayores/Evaluaciones/EvaluacionGeriatricaModal.php`, `app/Livewire/Admin/Psicologia/EvaluacionesAreaPanel.php`, `app/Livewire/Admin/Psicologia/EvaluacionGeriatricaAreaModal.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorEvaluacionController.php`.
- Problemas y duplicación: No congela reglas utilizadas por evaluación histórica.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → assessment_instruments, instrument_versions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_instrumento | varchar | No |
| cod_area | varchar | No |
| nombre | varchar | No |
| siglas | varchar | Sí |
| tipo_resultado | varchar | No |
| puntaje_maximo | numeric | Sí |
| punto_corte_normal | numeric | Sí |
| punto_corte_riesgo | numeric | Sí |
| descripcion | text | Sí |
| estado | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_area) REFERENCES areas_geriatricas(cod_area) ON DELETE RESTRICT`
- `NOT NULL cod_area`
- `NOT NULL cod_instrumento`
- `NOT NULL estado`
- `NOT NULL nombre`
- `PRIMARY KEY (cod_instrumento)`
- `NOT NULL tipo_resultado`

### job_batches

- Propósito: Lotes de trabajos.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Evidencia técnica; no reemplaza historia clínica.
- Decisión: **CONSERVAR** → job_batches. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | varchar | No |
| name | varchar | No |
| total_jobs | int4 | No |
| pending_jobs | int4 | No |
| failed_jobs | int4 | No |
| failed_job_ids | text | No |
| options | text | Sí |
| cancelled_at | int4 | Sí |
| created_at | int4 | No |
| finished_at | int4 | Sí |

Restricciones físicas:

- `NOT NULL created_at`
- `NOT NULL failed_job_ids`
- `NOT NULL failed_jobs`
- `NOT NULL id`
- `NOT NULL name`
- `NOT NULL pending_jobs`
- `PRIMARY KEY (id)`
- `NOT NULL total_jobs`

### jobs

- Propósito: Cola pendiente.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → jobs. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int8 | No |
| queue | varchar | No |
| payload | text | No |
| attempts | int2 | No |
| reserved_at | int4 | Sí |
| available_at | int4 | No |
| created_at | int4 | No |

Restricciones físicas:

- `NOT NULL attempts`
- `NOT NULL available_at`
- `NOT NULL created_at`
- `NOT NULL id`
- `NOT NULL payload`
- `PRIMARY KEY (id)`
- `NOT NULL queue`

### medicacion_adulto

- Propósito: Orden y pauta individual con prescriptor textual.
- Modelo: App\Models\MedicacionAdulto, PK cod_med_adulto.
- Módulo propietario actual aproximado: Medicación; consumidores: `app/Livewire/Admin/AdultosMayores/Salud/AdministracionMedicacionModal.php`, `app/Livewire/Admin/AdultosMayores/Salud/MedicacionAdultoModal.php`, `app/Livewire/Admin/Medico/DashboardMedico.php`, `app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php`, `app/Livewire/Admin/Medico/PacientesSeguimientoPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludAdministracionMedicacionPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludMedicacionPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludSeguimientoListPanel.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorMedicacionController.php`.
- Problemas y duplicación: No firma/versiones; médico puede ser texto; no catálogo fármacos validado.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **DIVIDIR** → prescriptions, prescription_items. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_med_adulto | varchar | No |
| cod_am | varchar | No |
| nombre_medicamento | varchar | No |
| dosis | varchar | No |
| frecuencia | varchar | No |
| via_administracion | varchar | Sí |
| hora_programada | time | Sí |
| fecha_inicio | date | No |
| fecha_fin | date | Sí |
| medico_indica | varchar | Sí |
| documento_receta | varchar | Sí |
| estado | varchar | No |
| observacion | text | Sí |
| registrado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| deleted_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_med_adulto`
- `FOREIGN KEY (documento_receta) REFERENCES documentos_adulto_mayor(cod_doc_am) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL dosis`
- `NOT NULL estado`
- `NOT NULL fecha_inicio`
- `NOT NULL frecuencia`
- `NOT NULL nombre_medicamento`
- `PRIMARY KEY (cod_med_adulto)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### migrations

- Propósito: Control del esquema.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Evidencia técnica; no reemplaza historia clínica.
- Decisión: **CONSERVAR** → migrations. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int4 | No |
| migration | varchar | No |
| batch | int4 | No |

Restricciones físicas:

- `NOT NULL batch`
- `NOT NULL id`
- `NOT NULL migration`
- `PRIMARY KEY (id)`

### model_has_permissions

- Propósito: Permisos directos.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → model_has_permissions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| permission_id | int8 | No |
| model_type | varchar | No |
| model_id | varchar | No |

Restricciones físicas:

- `NOT NULL model_id`
- `NOT NULL model_type`
- `FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE`
- `NOT NULL permission_id`
- `PRIMARY KEY (permission_id, model_id, model_type)`

### model_has_roles

- Propósito: Roles asignados.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → model_has_roles. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| role_id | int8 | No |
| model_type | varchar | No |
| model_id | varchar | No |

Restricciones físicas:

- `NOT NULL model_id`
- `NOT NULL model_type`
- `PRIMARY KEY (role_id, model_id, model_type)`
- `FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE`
- `NOT NULL role_id`

### notas_evolucion_medica

- Propósito: SOAP/interconsulta y signos copiados.
- Modelo: App\Models\NotaEvolucionMedica, PK cod_nota.
- Módulo propietario actual aproximado: Clínica; consumidores: `app/Livewire/Admin/Medico/DashboardMedico.php`, `app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php`, `app/Livewire/Admin/Medico/NotaEvolucionMedicaModal.php`, `app/Livewire/Admin/Medico/PacientesSeguimientoPanel.php`.
- Problemas y duplicación: Signos duplican mediciones; anulación existe pero no cadena de corrección.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **DIVIDIR** → encounters, clinical_notes, vital_signs. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_nota | varchar | No |
| cod_am | varchar | No |
| fecha | date | No |
| hora | time | Sí |
| tipo_nota | varchar | No |
| subjetivo | text | Sí |
| objetivo | text | Sí |
| valoracion | text | No |
| plan | text | No |
| observaciones | text | Sí |
| pa_sistolica | int2 | Sí |
| pa_diastolica | int2 | Sí |
| fc | int2 | Sí |
| fr | int2 | Sí |
| temperatura | numeric | Sí |
| saturacion | int2 | Sí |
| glucosa | numeric | Sí |
| peso | numeric | Sí |
| registrado_por | varchar | Sí |
| estado | varchar | No |
| motivo_anulacion | text | Sí |
| anulado_por | varchar | Sí |
| anulado_en | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| deleted_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_nota`
- `CHECK (((estado)::text = ANY ((ARRAY['ACTIVO'::character varying, 'ANULADO'::character varying])::text[])))`
- `NOT NULL estado`
- `NOT NULL fecha`
- `PRIMARY KEY (cod_nota)`
- `NOT NULL plan`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON DELETE SET NULL`
- `CHECK (((tipo_nota)::text = ANY ((ARRAY['EVOLUCION'::character varying, 'INGRESO'::character varying, 'EGRESO'::character varying, 'INTERCONSULTA'::character varying, 'URGENCIA'::character varying, 'PROCEDIMIENTO'::character varying])::text[])))`
- `NOT NULL tipo_nota`
- `NOT NULL valoracion`

### obs_adulto

- Propósito: Observación de seguimiento con riesgo.
- Modelo: App\Models\ObsAdulto, PK cod_obs_adul.
- Módulo propietario actual aproximado: Cuidados; consumidores: `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorObservacionController.php`.
- Problemas y duplicación: descripcion/observacion, creado_por/registrado_por y categorías paralelas.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **FUSIONAR** → daily_observations. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_obs_adul | varchar | No |
| cod_am | varchar | No |
| cod_est_adul | varchar | Sí |
| creado_por | varchar | No |
| observacion | text | No |
| fecha | date | No |
| categoria | varchar | No |
| nivel_riesgo | varchar | No |
| estado | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| tipo_obs | varchar | Sí |
| descripcion | text | Sí |
| registrado_por | varchar | Sí |
| nivel_importancia | varchar | Sí |
| deleted_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL categoria`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `FOREIGN KEY (cod_est_adul) REFERENCES estado_adulto(cod_est_adul) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL cod_obs_adul`
- `FOREIGN KEY (creado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL creado_por`
- `NOT NULL estado`
- `NOT NULL fecha`
- `NOT NULL nivel_riesgo`
- `NOT NULL observacion`
- `PRIMARY KEY (cod_obs_adul)`

### pases_turno

- Propósito: Resumen y recepción por turno.
- Modelo: App\Models\PaseTurno, PK cod_pase.
- Módulo propietario actual aproximado: Cuidados; consumidores: `app/Livewire/Admin/Enfermeria/DashboardTurno.php`, `app/Livewire/Admin/Enfermeria/PaseTurnoPanel.php`.
- Problemas y duplicación: Sí snapshots JSON; faltan referencias a jornadas fechadas y corrección.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → handovers. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_pase | varchar | No |
| cod_am | varchar | No |
| turno_saliente_id | varchar | No |
| turno_entrante_id | varchar | No |
| enfermero_saliente_id | varchar | Sí |
| enfermero_entrante_id | varchar | Sí |
| fecha | date | No |
| estado_general_cierre | varchar | Sí |
| resumen_turno | text | No |
| tareas_realizadas_json | json | Sí |
| tareas_pendientes_json | json | Sí |
| alertas_activas_json | json | Sí |
| recomendacion_siguiente_turno | text | Sí |
| requiere_vigilancia_especial | bool | No |
| motivo_vigilancia | text | Sí |
| estado | varchar | No |
| fecha_recibido | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_am`
- `NOT NULL cod_pase`
- `FOREIGN KEY (enfermero_entrante_id) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (enfermero_saliente_id) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL estado`
- `NOT NULL fecha`
- `PRIMARY KEY (cod_pase)`
- `NOT NULL requiere_vigilancia_especial`
- `NOT NULL resumen_turno`
- `FOREIGN KEY (turno_entrante_id) REFERENCES turnos_enfermeria(cod_turno) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL turno_entrante_id`
- `FOREIGN KEY (turno_saliente_id) REFERENCES turnos_enfermeria(cod_turno) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL turno_saliente_id`

### password_reset_tokens

- Propósito: Recuperación de contraseña.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → password_reset_tokens. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| email | varchar | No |
| token | varchar | No |
| created_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL email`
- `PRIMARY KEY (email)`
- `NOT NULL token`

### permissions

- Propósito: Capacidades Spatie.
- Modelo: Spatie\Permission\Models\Permission.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → permissions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int8 | No |
| name | varchar | No |
| guard_name | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL guard_name`
- `NOT NULL id`
- `UNIQUE (name, guard_name)`
- `NOT NULL name`
- `PRIMARY KEY (id)`

### personal_access_tokens

- Propósito: Tokens Sanctum.
- Modelo: Laravel\Sanctum\PersonalAccessToken.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → personal_access_tokens. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int8 | No |
| tokenable_type | varchar | No |
| tokenable_id | int8 | No |
| name | text | No |
| token | varchar | No |
| abilities | text | Sí |
| last_used_at | timestamp | Sí |
| expires_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL id`
- `NOT NULL name`
- `PRIMARY KEY (id)`
- `NOT NULL token`
- `UNIQUE (token)`
- `NOT NULL tokenable_id`
- `NOT NULL tokenable_type`

### planes_cuidado

- Propósito: Plan activo, nivel y número de versión.
- Modelo: App\Models\PlanCuidado, PK cod_plan.
- Módulo propietario actual aproximado: Cuidados; consumidores: `app/Livewire/Admin/Enfermeria/PlanCuidadoPanel.php`, `app/Livewire/Admin/Enfermeria/SeguimientoDiarioPanel.php`, `app/Livewire/Admin/Enfermeria/TareasPlanPanel.php`.
- Problemas y duplicación: Versión sin raíz estable; evitar múltiples planes generales activos.
- Historial: Estado mutable con timestamps y soft delete; no historial completo de atributos.
- Decisión: **DIVIDIR** → care_plans, care_plan_versions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_plan | varchar | No |
| cod_am | varchar | No |
| tipo_plan | varchar | No |
| version | int2 | No |
| nivel_cuidado | varchar | No |
| estado | varchar | No |
| origen | varchar | Sí |
| resumen | text | Sí |
| fecha_inicio | date | No |
| fecha_fin | date | Sí |
| creado_por | varchar | Sí |
| validado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| deleted_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_am`
- `NOT NULL cod_plan`
- `FOREIGN KEY (creado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL estado`
- `NOT NULL fecha_inicio`
- `NOT NULL nivel_cuidado`
- `PRIMARY KEY (cod_plan)`
- `NOT NULL tipo_plan`
- `FOREIGN KEY (validado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL version`

### preadmisiones

- Propósito: Solicitud, familiar, asignación y aprobación.
- Modelo: App\Models\Preadmision, PK cod_pre.
- Módulo propietario actual aproximado: Admisiones y Ocupación; consumidores: `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php`, `app/Livewire/Admin/Admisiones/PreadmisionWizard.php`, `app/Livewire/Admin/Enfermeria/DashboardTurno.php`, `app/Livewire/Admin/Enfermeria/ValoracionInicialModal.php`, `tests/Feature/CasosPreadmisionTest.php`.
- Problemas y duplicación: Duplica identidad y links bidireccionales; checklists booleanos redundantes.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → persons, person_identifiers, person_contacts, admission_cases, admission_decisions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_pre | varchar | No |
| estado | varchar | No |
| fecha_solicitud | date | No |
| fecha_asignacion | timestamp | Sí |
| nombres | varchar | No |
| ap_paterno | varchar | No |
| ap_materno | varchar | Sí |
| ci | varchar | No |
| expedicion_ci | varchar | Sí |
| fecha_nac | date | No |
| genero | varchar | No |
| estado_civil | varchar | Sí |
| telefono | varchar | Sí |
| celular | varchar | Sí |
| departamento_residencia | varchar | Sí |
| ciudad_municipio | varchar | Sí |
| zona | varchar | Sí |
| calle | varchar | Sí |
| direccion_referencia | text | Sí |
| familiar_nombres | varchar | No |
| familiar_ap_paterno | varchar | Sí |
| familiar_ap_materno | varchar | Sí |
| familiar_ci | varchar | Sí |
| familiar_parentesco | varchar | No |
| familiar_celular | varchar | No |
| familiar_correo | varchar | Sí |
| familiar_direccion | text | Sí |
| motivo_ingreso | varchar | Sí |
| procedencia_ingreso | varchar | Sí |
| tipo_ingreso | varchar | Sí |
| permanencia | varchar | Sí |
| prioridad | varchar | Sí |
| descripcion_caso | text | Sí |
| documentos_iniciales_completos | bool | No |
| documentos_institucionales_generados | bool | No |
| enfermero_asignado | varchar | Sí |
| creado_por | varchar | Sí |
| observaciones | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| motivo_rechazo | varchar | Sí |
| observacion_rechazo | text | Sí |
| fecha_rechazo | timestamp | Sí |
| rechazado_por | varchar | Sí |
| fecha_aprobacion | timestamp | Sí |
| aprobado_por | varchar | Sí |
| cod_am_generado | varchar | Sí |
| cod_fam_generado | varchar | Sí |

Restricciones físicas:

- `NOT NULL ap_paterno`
- `FOREIGN KEY (aprobado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL ci`
- `FOREIGN KEY (cod_am_generado) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_fam_generado) REFERENCES familiares(cod_fam) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL cod_pre`
- `FOREIGN KEY (creado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL documentos_iniciales_completos`
- `NOT NULL documentos_institucionales_generados`
- `FOREIGN KEY (enfermero_asignado) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL estado`
- `NOT NULL familiar_celular`
- `NOT NULL familiar_nombres`
- `NOT NULL familiar_parentesco`
- `NOT NULL fecha_nac`
- `NOT NULL fecha_solicitud`
- `NOT NULL genero`
- `NOT NULL nombres`
- `PRIMARY KEY (cod_pre)`
- `FOREIGN KEY (rechazado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `UNIQUE (ci, expedicion_ci)`

### role_has_permissions

- Propósito: Permisos del rol.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → role_has_permissions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| permission_id | int8 | No |
| role_id | int8 | No |

Restricciones físicas:

- `FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE`
- `NOT NULL permission_id`
- `PRIMARY KEY (permission_id, role_id)`
- `FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE`
- `NOT NULL role_id`

### roles

- Propósito: Roles Spatie.
- Modelo: Spatie\Permission\Models\Role.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → roles. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | int8 | No |
| name | varchar | No |
| guard_name | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL guard_name`
- `NOT NULL id`
- `UNIQUE (name, guard_name)`
- `NOT NULL name`
- `PRIMARY KEY (id)`

### seguimientos_diarios

- Propósito: Observación estructurada del turno.
- Modelo: App\Models\SeguimientoDiario, PK cod_seg_diario.
- Módulo propietario actual aproximado: Cuidados; consumidores: `app/Livewire/Admin/Enfermeria/DashboardTurno.php`, `app/Livewire/Admin/Enfermeria/SeguimientoDiarioPanel.php`.
- Problemas y duplicación: Único por adulto/fecha/turno; no versiones; incidente como texto/flag.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **FUSIONAR** → daily_observations, incidents. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_seg_diario | varchar | No |
| cod_am | varchar | No |
| cod_turno | varchar | No |
| cod_plan | varchar | Sí |
| registrado_por | varchar | Sí |
| fecha | date | No |
| hora_inicio | time | Sí |
| hora_fin | time | Sí |
| estado_general | varchar | Sí |
| alimentacion | varchar | Sí |
| porcentaje_alimentacion | int2 | Sí |
| hidratacion | varchar | Sí |
| movilidad | varchar | Sí |
| intento_caminar_solo | bool | No |
| higiene | varchar | Sí |
| sueno | varchar | Sí |
| orientacion | varchar | Sí |
| repite_preguntas | bool | No |
| confusion_observable | bool | No |
| conducta | varchar | Sí |
| participacion | varchar | Sí |
| incidente | bool | No |
| requiere_medico | bool | No |
| observacion | text | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_am`
- `FOREIGN KEY (cod_plan) REFERENCES planes_cuidado(cod_plan) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL cod_seg_diario`
- `FOREIGN KEY (cod_turno) REFERENCES turnos_enfermeria(cod_turno) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_turno`
- `NOT NULL confusion_observable`
- `NOT NULL fecha`
- `NOT NULL incidente`
- `NOT NULL intento_caminar_solo`
- `PRIMARY KEY (cod_seg_diario)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL repite_preguntas`
- `NOT NULL requiere_medico`
- `UNIQUE (cod_am, fecha, cod_turno)`

### sessions

- Propósito: Sesiones web.
- Modelo: Framework/tabla técnica, sin modelo de dominio propio.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Tabla técnica: conservar contrato del framework; migrar referencias y política de retención, no tratar como entidad clínica..
- Historial: Estado técnico o transitorio, no historia clínica.
- Decisión: **CONSERVAR** → sessions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| id | varchar | No |
| user_id | varchar | Sí |
| ip_address | varchar | Sí |
| user_agent | text | Sí |
| payload | text | No |
| last_activity | int4 | No |

Restricciones físicas:

- `NOT NULL id`
- `NOT NULL last_activity`
- `NOT NULL payload`
- `PRIMARY KEY (id)`

### signos_vitales_adulto

- Propósito: Serie temporal de signos.
- Modelo: App\Models\SignosVitalesAdulto, PK cod_signo.
- Módulo propietario actual aproximado: Clínica; consumidores: `app/Livewire/Admin/AdultosMayores/Salud/SignosVitalesAdultoModal.php`, `app/Livewire/Admin/Medico/DashboardMedico.php`, `app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php`, `app/Livewire/Admin/Medico/PacientesSeguimientoPanel.php`, `app/Livewire/Admin/Medico/RegistroSignosVitalesModal.php`, `app/Livewire/Admin/Medico/SignosVitalesPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludSeguimientoListPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludSignosPanel.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorSignosVitalesController.php`.
- Problemas y duplicación: Presión texto y numérica duplicada; IMC derivable; int en métodos contra PK string.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **MODIFICAR** → vital_signs. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_signo | varchar | No |
| cod_am | varchar | No |
| fecha | date | No |
| hora | time | No |
| presion_arterial | varchar | Sí |
| presion_sistolica | int4 | Sí |
| presion_diastolica | int4 | Sí |
| frecuencia_cardiaca | int4 | Sí |
| frecuencia_respiratoria | int4 | Sí |
| temperatura | numeric | Sí |
| saturacion | int4 | Sí |
| glucosa | numeric | Sí |
| peso | numeric | Sí |
| talla | numeric | Sí |
| imc | numeric | Sí |
| dolor | varchar | Sí |
| observacion | text | Sí |
| registrado_por | varchar | Sí |
| estado | varchar | No |
| motivo_anulacion | text | Sí |
| anulado_por | varchar | Sí |
| fecha_anulacion | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (anulado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_am`
- `NOT NULL cod_signo`
- `NOT NULL estado`
- `NOT NULL fecha`
- `NOT NULL hora`
- `PRIMARY KEY (cod_signo)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### tareas_plan_cuidado

- Propósito: Definición, programación y ejecución de tarea.
- Modelo: App\Models\TareaPlanCuidado, PK cod_tarea.
- Módulo propietario actual aproximado: Cuidados; consumidores: `app/Livewire/Admin/Enfermeria/DashboardTurno.php`, `app/Livewire/Admin/Enfermeria/PaseTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/TareasPlanPanel.php`.
- Problemas y duplicación: Resultado sobrescribible; cod_am duplica plan; relaciones a cod_tarea inexistente.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → care_tasks, care_task_executions. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_tarea | varchar | No |
| cod_plan | varchar | No |
| cod_am | varchar | No |
| cod_turno | varchar | No |
| responsable_id | varchar | Sí |
| area | varchar | No |
| titulo | varchar | No |
| descripcion | text | Sí |
| frecuencia | varchar | Sí |
| fecha_programada | date | No |
| hora_programada | time | Sí |
| prioridad | varchar | No |
| estado | varchar | No |
| fecha_realizada | timestamp | Sí |
| resultado | text | Sí |
| observacion | text | Sí |
| motivo_omision | text | Sí |
| transferida_a_turno_id | varchar | Sí |
| registrado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL area`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_am`
- `FOREIGN KEY (cod_plan) REFERENCES planes_cuidado(cod_plan) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_plan`
- `NOT NULL cod_tarea`
- `FOREIGN KEY (cod_turno) REFERENCES turnos_enfermeria(cod_turno) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_turno`
- `NOT NULL estado`
- `NOT NULL fecha_programada`
- `PRIMARY KEY (cod_tarea)`
- `NOT NULL prioridad`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `FOREIGN KEY (responsable_id) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL titulo`
- `FOREIGN KEY (transferida_a_turno_id) REFERENCES turnos_enfermeria(cod_turno) ON UPDATE CASCADE ON DELETE SET NULL`

### tipo_actividades_adulto

- Propósito: Catálogo usado por actividad y reportes.
- Modelo: App\Models\TipoActividadAdulto, PK cod_tipo_act.
- Módulo propietario actual aproximado: Actividades y Voluntariado; consumidores: `app/Livewire/Admin/Actividades/ActividadesPanel.php`, `app/Livewire/Admin/Actividades/AsistenciaPanel.php`, `app/Livewire/Admin/Actividades/ParticipacionPanel.php`, `app/Livewire/Admin/Actividades/ReportesActividadesPanel.php`, `app/Livewire/Admin/Actividades/TiposActividadPanel.php`, `app/Http/Controllers/Admin/AdultoMayorController.php`.
- Problemas y duplicación: Mantener inactivación, no borrar tipos usados.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **CONSERVAR** → activity_types. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_tipo_act | varchar | No |
| nombre | varchar | No |
| descripcion | text | Sí |
| estado | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL cod_tipo_act`
- `NOT NULL estado`
- `NOT NULL nombre`
- `UNIQUE (nombre)`
- `PRIMARY KEY (cod_tipo_act)`

### tipo_atenciones_adulto

- Propósito: Catálogo de atenciones.
- Modelo: App\Models\TipoAtencionAdulto, PK cod_tipo_aten.
- Módulo propietario actual aproximado: Clínica; consumidores: `app/Http/Controllers/Admin/AdultoMayorController.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorAtencionController.php`.
- Problemas y duplicación: Mantener inactivación, no es catálogo de profesiones.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **CONSERVAR** → encounter_types. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_tipo_aten | varchar | No |
| nombre | varchar | No |
| descripcion | text | Sí |
| estado | varchar | No |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL cod_tipo_aten`
- `NOT NULL estado`
- `NOT NULL nombre`
- `UNIQUE (nombre)`
- `PRIMARY KEY (cod_tipo_aten)`

### tipos_documentos_usuario

- Propósito: Requisitos documentales por roles.
- Modelo: App\Models\TipoDocumentoUsuario, PK cod_tipo_doc.
- Módulo propietario actual aproximado: Documentos; consumidores: `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalDocumentos.php`, `app/Livewire/Admin/Usuarios/UsuarioFichaPanel.php`, `app/Services/Usuarios/DocumentacionUsuarioService.php`.
- Problemas y duplicación: Generalizar contextos manteniendo obligatorio/vencimiento/validación.
- Historial: Estado mutable con timestamps y soft delete; no historial completo de atributos.
- Decisión: **MODIFICAR** → document_types. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_tipo_doc | varchar | No |
| nombre | varchar | No |
| descripcion | text | Sí |
| aplica_roles | json | Sí |
| obligatorio | bool | No |
| requiere_vencimiento | bool | No |
| requiere_validacion | bool | No |
| estado | varchar | No |
| orden | int4 | No |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL cod_tipo_doc`
- `NOT NULL estado`
- `NOT NULL nombre`
- `UNIQUE (nombre)`
- `NOT NULL obligatorio`
- `NOT NULL orden`
- `PRIMARY KEY (cod_tipo_doc)`
- `NOT NULL requiere_validacion`
- `NOT NULL requiere_vencimiento`

### turnos_enfermeria

- Propósito: Catálogo horario asistencial.
- Modelo: App\Models\TurnoEnfermeria, PK cod_turno.
- Módulo propietario actual aproximado: Institución y Personal; consumidores: `app/Livewire/Admin/Enfermeria/AlertasPanel.php`, `app/Livewire/Admin/Enfermeria/AsignacionTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/DashboardTurno.php`, `app/Livewire/Admin/Enfermeria/MisPacientes.php`, `app/Livewire/Admin/Enfermeria/PaseTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/SeguimientoDiarioPanel.php`, `app/Livewire/Admin/Enfermeria/TareasPlanPanel.php`, `app/Livewire/Admin/Enfermeria/TurnosEnfermeriaPanel.php`.
- Problemas y duplicación: Se solapa con turnos_institucionales; nombres iguales no prueban horas iguales.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **FUSIONAR** → shifts. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_turno | varchar | No |
| nombre | varchar | No |
| hora_inicio | time | No |
| hora_fin | time | No |
| orden | int2 | No |
| estado | varchar | No |
| observacion | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `NOT NULL cod_turno`
- `NOT NULL estado`
- `NOT NULL hora_fin`
- `NOT NULL hora_inicio`
- `NOT NULL nombre`
- `UNIQUE (nombre)`
- `NOT NULL orden`
- `UNIQUE (orden)`
- `PRIMARY KEY (cod_turno)`

### turnos_institucionales

- Propósito: Catálogo de horario institucional.
- Modelo: App\Models\TurnoInstitucional, PK cod_turno.
- Módulo propietario actual aproximado: Institución y Personal; consumidores: `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalHorarios.php`, `app/Livewire/Admin/PersonalInstitucional/TurnosAsignacionesPanel.php`, `tests/Feature/PersonalInstitucionalHorariosTest.php`.
- Problemas y duplicación: Solapado con enfermería, información visual adicional.
- Historial: Estado mutable con timestamps y soft delete; no historial completo de atributos.
- Decisión: **FUSIONAR** → shifts. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_turno | varchar | No |
| nombre | varchar | No |
| hora_inicio | time | Sí |
| hora_fin | time | Sí |
| estado | varchar | No |
| descripcion | text | Sí |
| color | varchar | Sí |
| observaciones | text | Sí |
| creado_por | varchar | Sí |
| actualizado_por | varchar | Sí |
| deleted_at | timestamp | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |

Restricciones físicas:

- `FOREIGN KEY (actualizado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL cod_turno`
- `FOREIGN KEY (creado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL estado`
- `NOT NULL nombre`
- `PRIMARY KEY (cod_turno)`

### users

- Propósito: Cuenta, identidad y adaptación laboral virtual.
- Modelo: App\Models\User, PK cod_usu.
- Módulo propietario actual aproximado: Identidad y Seguridad; consumidores: `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php`, `app/Livewire/Admin/Admisiones/PreadmisionWizard.php`, `app/Livewire/Admin/AreasInstitucionales/AreasInstitucionalesPanel.php`, `app/Livewire/Admin/Enfermeria/AsignacionTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/MisPacientes.php`, `app/Livewire/Admin/Enfermeria/PaseTurnoPanel.php`, `app/Livewire/Admin/Enfermeria/TareasPlanPanel.php`, `app/Livewire/Admin/FamiliaSocial/RedApoyoPanel.php`, `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalDocumentos.php`, `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalForm.php`, `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalHorarios.php`, `app/Livewire/Admin/PersonalInstitucional/PersonalInstitucionalPanel.php`, `app/Livewire/Admin/PersonalInstitucional/TurnosAsignacionesPanel.php`, `app/Livewire/Admin/Usuarios/UsuarioFichaPanel.php`, `app/Livewire/Admin/Usuarios/UsuarioFormModal.php`, `app/Livewire/Admin/Usuarios/UsuariosPanel.php`, `app/Livewire/Admin/Voluntariado/VoluntariosPanel.php`, `app/Services/Enfermeria/GeneradorPlanillaEnfermeriaService.php`, `app/Services/Reportes/AreasReportDataService.php`, `app/Services/Reportes/ReportChartDataService.php`, `app/Services/Reportes/ReporteDataService.php`, `app/Services/Usuarios/DocumentacionUsuarioService.php`, `app/Services/Usuarios/DocumentosUsuarioService.php`, `app/Services/Usuarios/UsuarioFichaService.php`, `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorFamiliarController.php`, `app/Http/Controllers/Admin/AreasInstitucionales/AreaReporteController.php`, `app/Http/Controllers/Admin/UsuarioController.php`, `app/Http/Controllers/Admin/Usuarios/DocumentosUsuarioController.php`, `tests/Feature/ApiTokenPermissionsTest.php`, `tests/Feature/AuthenticationTest.php`, `tests/Feature/BrowserSessionsTest.php`, `tests/Feature/CasosPreadmisionTest.php`, `tests/Feature/CreateApiTokenTest.php`, `tests/Feature/DeleteAccountTest.php`, `tests/Feature/DeleteApiTokenTest.php`, `tests/Feature/EmailVerificationTest.php`, `tests/Feature/PasswordConfirmationTest.php`, `tests/Feature/PasswordResetTest.php`, `tests/Feature/PersonalInstitucionalHorariosTest.php`, `tests/Feature/ProfileInformationTest.php`, `tests/Feature/TwoFactorAuthenticationSettingsTest.php`, `tests/Feature/UpdatePasswordTest.php`, `tests/Feature/UsuariosRoutesPermissionsTest.php`.
- Problemas y duplicación: Rol principal deriva profesión/cargo/área, código MAX no atómico, acceso_sistema no gate login.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → persons, person_identifiers, users, professionals, staff_assignments, person_contacts. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_usu | varchar | No |
| nombres | varchar | No |
| ap_paterno | varchar | Sí |
| ap_materno | varchar | Sí |
| pais_documento | varchar | Sí |
| tipo_documento | varchar | Sí |
| numero_documento | varchar | Sí |
| expedido | varchar | Sí |
| correo | varchar | No |
| email_verified_at | timestamp | Sí |
| password | varchar | No |
| telefono | varchar | Sí |
| pais_telefono | varchar | Sí |
| codigo_telefono | varchar | Sí |
| genero | varchar | Sí |
| fecha_nacimiento | date | Sí |
| foto_de_perfil | varchar | Sí |
| estado | varchar | No |
| acceso_sistema | varchar | No |
| ultimo_acceso | timestamp | Sí |
| observaciones | text | Sí |
| debe_cambiar_password | bool | No |
| password_changed_at | timestamp | Sí |
| direccion | varchar | Sí |
| zona | varchar | Sí |
| ciudad | varchar | Sí |
| calle | varchar | Sí |
| nro_domicilio | varchar | Sí |
| contacto_emergencia | varchar | Sí |
| parentesco_emergencia | varchar | Sí |
| celular_emergencia | varchar | Sí |
| ap_paterno_emergencia | varchar | Sí |
| ap_materno_emergencia | varchar | Sí |
| tipo_vinculacion | varchar | Sí |
| current_team_id | int8 | Sí |
| two_factor_secret | text | Sí |
| two_factor_recovery_codes | text | Sí |
| two_factor_confirmed_at | timestamp | Sí |
| remember_token | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| cod_area | varchar | Sí |

Restricciones físicas:

- `NOT NULL acceso_sistema`
- `FOREIGN KEY (cod_area) REFERENCES areas_institucionales(cod_area) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL cod_usu`
- `NOT NULL correo`
- `UNIQUE (correo)`
- `NOT NULL debe_cambiar_password`
- `NOT NULL estado`
- `NOT NULL nombres`
- `NOT NULL password`
- `PRIMARY KEY (cod_usu)`

### valoracion_enfermeria_admision

- Propósito: Valoración inicial asociada a caso o adulto.
- Modelo: App\Models\ValoracionEnfermeriaAdmision, PK cod_val_enf.
- Módulo propietario actual aproximado: Valoración Geriátrica Integral; consumidores: `app/Livewire/Admin/Enfermeria/DashboardTurno.php`, `app/Livewire/Admin/Enfermeria/ValoracionEnfermeriaPanel.php`, `app/Livewire/Admin/Enfermeria/ValoracionInicialModal.php`.
- Problemas y duplicación: Campos estructurados y JSON en texto; no convertir en tabla por profesión.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **REEMPLAZAR** → assessments, vital_signs. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_val_enf | varchar | No |
| cod_am | varchar | Sí |
| fecha_valoracion | date | No |
| hora_valoracion | time | Sí |
| estado_general | varchar | Sí |
| nivel_conciencia | varchar | Sí |
| orientacion | varchar | Sí |
| comunicacion | varchar | Sí |
| hay_dolor | bool | No |
| intensidad_dolor | int2 | Sí |
| ubicacion_dolor | varchar | Sí |
| movilidad | varchar | Sí |
| apoyo_movilidad | varchar | Sí |
| riesgo_caida | varchar | Sí |
| piel_estado | varchar | Sí |
| hay_heridas | bool | No |
| ubicacion_heridas | text | Sí |
| higiene_ingreso | varchar | Sí |
| continencia_basica | varchar | Sí |
| alimentacion_aparente | varchar | Sí |
| signos_vitales_iniciales | text | Sí |
| observacion | text | Sí |
| recomendacion_enfermeria | text | Sí |
| estado | varchar | No |
| registrado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| cod_pre | varchar | Sí |

Restricciones físicas:

- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE CASCADE`
- `FOREIGN KEY (cod_pre) REFERENCES preadmisiones(cod_pre) ON UPDATE CASCADE ON DELETE CASCADE`
- `NOT NULL cod_val_enf`
- `NOT NULL estado`
- `NOT NULL fecha_valoracion`
- `NOT NULL hay_dolor`
- `NOT NULL hay_heridas`
- `PRIMARY KEY (cod_val_enf)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`

### valoracion_funcional_adulto

- Propósito: Dependencia, ayudas, riesgo y Barthel.
- Modelo: App\Models\ValoracionFuncionalAdulto, PK cod_val_func.
- Módulo propietario actual aproximado: Valoración Geriátrica Integral; consumidores: `app/Livewire/Admin/AdultosMayores/Salud/ValoracionFuncionalAdultoModal.php`, `app/Livewire/Admin/Medico/DashboardMedico.php`, `app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php`, `app/Livewire/Admin/Medico/ValoracionBarthelModal.php`, `app/Livewire/Admin/SaludSeguimiento/SaludSeguimientoListPanel.php`, `app/Livewire/Admin/SaludSeguimiento/SaludValoracionPanel.php`, `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorValoracionFuncionalController.php`.
- Problemas y duplicación: Duplica VGI; total Barthel sin todas respuestas implica legacy sin reconstrucción inventada.
- Historial: Hay registros temporales o estados, pero la inmutabilidad no está garantizada uniformemente; conservar filas y correcciones disponibles.
- Decisión: **FUSIONAR** → assessments. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_val_func | varchar | No |
| cod_am | varchar | No |
| fecha_valoracion | date | No |
| come_solo | bool | No |
| se_bana_solo | bool | No |
| se_viste_solo | bool | No |
| va_bano_solo | bool | No |
| camina_solo | bool | No |
| usa_baston | bool | No |
| usa_andador | bool | No |
| usa_silla_ruedas | bool | No |
| baja_vision | bool | No |
| baja_audicion | bool | No |
| dificultad_hablar | bool | No |
| molestia_luz | bool | No |
| molestia_ruido | bool | No |
| se_asusta_facil | bool | No |
| necesita_supervision | bool | No |
| nivel_dependencia | varchar | No |
| observacion | text | Sí |
| registrado_por | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| riesgo_caida | varchar | Sí |
| indice_barthel | int2 | Sí |
| estado | varchar | No |
| motivo_anulacion | text | Sí |
| anulado_por | varchar | Sí |
| fecha_anulacion | timestamp | Sí |

Restricciones físicas:

- `NOT NULL baja_audicion`
- `NOT NULL baja_vision`
- `NOT NULL camina_solo`
- `FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE RESTRICT`
- `NOT NULL cod_am`
- `NOT NULL cod_val_func`
- `NOT NULL come_solo`
- `NOT NULL dificultad_hablar`
- `NOT NULL estado`
- `NOT NULL fecha_valoracion`
- `NOT NULL molestia_luz`
- `NOT NULL molestia_ruido`
- `NOT NULL necesita_supervision`
- `NOT NULL nivel_dependencia`
- `PRIMARY KEY (cod_val_func)`
- `FOREIGN KEY (registrado_por) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL se_asusta_facil`
- `NOT NULL se_bana_solo`
- `NOT NULL se_viste_solo`
- `NOT NULL usa_andador`
- `NOT NULL usa_baston`
- `NOT NULL usa_silla_ruedas`
- `NOT NULL va_bano_solo`

### voluntarios

- Propósito: Identidad y vinculación voluntaria.
- Modelo: App\Models\Voluntario, PK cod_vol.
- Módulo propietario actual aproximado: Actividades y Voluntariado; consumidores: acceso SQL directo o infraestructura del framework; véase inventario funcional.
- Problemas y duplicación: Duplica users; disponibilidad inicial textual diferente a horario.
- Historial: Estado mutable con timestamps; no historial completo de atributos.
- Decisión: **DIVIDIR** → persons, person_identifiers, volunteer_profiles. Conservar información y procedencia conforme a 04.

| Columna | Tipo | NULL |
|---|---|---|
| cod_vol | varchar | No |
| nombres | varchar | No |
| ap_paterno | varchar | No |
| ap_materno | varchar | Sí |
| ci | varchar | Sí |
| celular | varchar | Sí |
| correo | varchar | Sí |
| fecha_nac | date | Sí |
| profesion_ocupacion | varchar | Sí |
| estado | varchar | No |
| observaciones | text | Sí |
| cod_usu | varchar | Sí |
| created_at | timestamp | Sí |
| updated_at | timestamp | Sí |
| fecha_ing | date | Sí |
| disponibilidad_inicial | varchar | Sí |
| area_apoyo_preferente | varchar | Sí |
| archivado_en | timestamp | Sí |

Restricciones físicas:

- `NOT NULL ap_paterno`
- `UNIQUE (ci)`
- `FOREIGN KEY (cod_usu) REFERENCES users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL`
- `NOT NULL cod_vol`
- `NOT NULL estado`
- `NOT NULL nombres`
- `PRIMARY KEY (cod_vol)`

## Hallazgos estructurales transversales

1. Claves de negocio string generadas por búsqueda del máximo: User::booted y GeneraCodigo; carrera concurrente y acoplamiento del código visible a PK.
2. Persona duplicada en users, adulto_mayor, familiares, voluntarios y preadmisiones. User inventa personalSalud/personalAdmin/especialidad/cargo desde el primer rol: no hay tablas profesionales actuales que respalden esa identidad.
3. Ocupación en adulto_mayor, asignacion_adulto_mayor y asignaciones_turno_adulto. Las FKs individuales no impiden dos asignaciones a una cama ni habitación inconsistente.
4. Ficha clínica mutable y booleanos de enfermedades no expresan diagnóstico, certeza, autor clínico y evolución. Actividad de Spatie no sustituye un documento clínico versionado.
5. EvaluacionGeriatrica tiene pares puntaje/puntaje_total, estado/estado_eval, resultado/categoría y autores; sus setters pueden mezclar riesgo con resultado. anulador() usa evaluador_id. TareaPlanCuidado declara relaciones por cod_tarea que no existe en signos ni administraciones.
6. La presencia de ON DELETE CASCADE en antecedentes, medicación y otros hijos exige retirar eliminación física de padres antes de cualquier refactor. No basta añadir soft delete a unas pantallas.
7. Instrumentos no versionados; planes con número de versión sin raíz estable; tareas mezclan ocurrencia y definición; documentos de tres orígenes con diferentes estados.
8. No existen tablas actuales de visitas, ficha social, facturación ni ejecuciones del sistema experto. No se cuentan como AS-IS aunque haya menú o consultas tolerantes a su ausencia.
9. sessions.user_id, morph IDs de Spatie/Sanctum/actividad y trazas antiguas deben migrarse de manera consistente al cambiar users.cod_usu. Las restricciones anteriores muestran dónde no hay FK física.

## Excepción clínica de activity_log

Clasificación definitiva de activity_log: **DIVIDIR**, conservando la tabla técnica y extrayendo a encounters/clinical_notes/vital_signs/admission_decisions los hechos que hoy solo sobreviven en properties de ValoracionMedicaModal y DecisionAdmisionModal. No duplicar hechos ya presentes en tablas clínicas; no inferir firma o valor perdido. Autor/sujeto/evento/hora/hash deben enlazar ambas evidencias. El dictamen específico amplía la descripción técnica del diccionario anterior.
