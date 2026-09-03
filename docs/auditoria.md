# Auditoría de Base de Datos - RememberMind

### 1. Inventario de Tablas (64 tablas)

- **acciones_alerta** (Columnas: 9)
- **actividades_adulto** (Columnas: 13)
- **activity_log** (Columnas: 12)
- **administracion_medicacion** (Columnas: 18)
- **adulto_mayor** (Columnas: 46)
- **alertas_adulto** (Columnas: 17)
- **areas_geriatricas** (Columnas: 6)
- **areas_institucionales** (Columnas: 19)
- **asignacion_voluntarios** (Columnas: 7)
- **asignaciones_turno** (Columnas: 15)
- **asignaciones_turno_adulto** (Columnas: 15)
- **asistencia_voluntarios** (Columnas: 8)
- **atenciones_adulto** (Columnas: 12)
- **cache** (Columnas: 3)
- **cache_locks** (Columnas: 3)
- **camas** (Columnas: 8)
- **cargos_administrativos** (Columnas: 6)
- **disponibilidad_voluntarios** (Columnas: 6)
- **documentos_adulto_mayor** (Columnas: 16)
- **documentos_usuarios** (Columnas: 23)
- **especialidades** (Columnas: 3)
- **estado_adulto** (Columnas: 6)
- **evaluaciones_cognitivas** (Columnas: 15)
- **evaluaciones_geriatricas** (Columnas: 21)
- **failed_jobs** (Columnas: 7)
- **familiar_adulto** (Columnas: 10)
- **familiares** (Columnas: 11)
- **ficha_medica_adulto** (Columnas: 25)
- **habitaciones** (Columnas: 11)
- **historial_estado_adulto** (Columnas: 11)
- **horarios_personal_admin** (Columnas: 8)
- **horarios_personal_salud** (Columnas: 8)
- **instrumentos_geriatricos** (Columnas: 12)
- **job_batches** (Columnas: 10)
- **jobs** (Columnas: 7)
- **medicacion_adulto** (Columnas: 19)
- **migrations** (Columnas: 3)
- **model_has_permissions** (Columnas: 3)
- **model_has_roles** (Columnas: 3)
- **obs_adulto** (Columnas: 12)
- **pases_turno** (Columnas: 19)
- **password_reset_tokens** (Columnas: 3)
- **permissions** (Columnas: 5)
- **personal_access_tokens** (Columnas: 10)
- **personal_admin** (Columnas: 12)
- **personal_salud** (Columnas: 16)
- **planes_cuidado** (Columnas: 15)
- **role_has_permissions** (Columnas: 2)
- **roles** (Columnas: 5)
- **seguimientos_diarios** (Columnas: 26)
- **sessions** (Columnas: 6)
- **signos_vitales_adulto** (Columnas: 27)
- **tareas_plan_cuidado** (Columnas: 21)
- **tipo_actividades_adulto** (Columnas: 3)
- **tipo_atenciones_adulto** (Columnas: 3)
- **tipo_evaluacion_cognitiva** (Columnas: 9)
- **tipos_documentos_usuario** (Columnas: 12)
- **turnos_enfermeria** (Columnas: 9)
- **turnos_institucionales** (Columnas: 13)
- **users** (Columnas: 42)
- **valoracion_funcional_adulto** (Columnas: 29)
- **valoraciones_enfermeria_admision** (Columnas: 27)
- **valoraciones_medicas_admision** (Columnas: 21)
- **voluntarios** (Columnas: 12)

### 2. Tabla de Relaciones Principales

| Tabla Origen | Clave Foránea | Tabla Destino | Claves Destino |
|---|---|---|---|
| acciones_alerta | cod_alerta | alertas_adulto | cod_alerta |
| acciones_alerta | responsable_id | users | cod_usu |
| actividades_adulto | cod_am | adulto_mayor | cod_am |
| actividades_adulto | cod_tipo_act | tipo_actividades_adulto | cod_tipo_act |
| administracion_medicacion | cod_am | adulto_mayor | cod_am |
| administracion_medicacion | cod_med_adulto | medicacion_adulto | cod_med_adulto |
| administracion_medicacion | cod_tarea | tareas_plan_cuidado | cod_tarea |
| administracion_medicacion | cod_turno | turnos_enfermeria | cod_turno |
| administracion_medicacion | registrado_por | users | cod_usu |
| adulto_mayor | cod_cama | camas | cod_cama |
| adulto_mayor | cod_est_adul | estado_adulto | cod_est_adul |
| adulto_mayor | cod_habitacion | habitaciones | cod_habitacion |
| alertas_adulto | atendido_por | users | cod_usu |
| alertas_adulto | cerrado_por | users | cod_usu |
| alertas_adulto | cod_am | adulto_mayor | cod_am |
| alertas_adulto | cod_turno | turnos_enfermeria | cod_turno |
| alertas_adulto | responsable_id | users | cod_usu |
| areas_institucionales | actualizado_por | users | cod_usu |
| areas_institucionales | creado_por | users | cod_usu |
| areas_institucionales | responsable_id | users | cod_usu |
| asignacion_voluntarios | cod_am | adulto_mayor | cod_am |
| asignacion_voluntarios | cod_vol | voluntarios | cod_vol |
| asignaciones_turno | actualizado_por | users | cod_usu |
| asignaciones_turno | cod_area | areas_institucionales | cod_area |
| asignaciones_turno | cod_turno | turnos_institucionales | cod_turno |
| asignaciones_turno | cod_usu | users | cod_usu |
| asignaciones_turno | creado_por | users | cod_usu |
| asignaciones_turno_adulto | asignado_por | users | cod_usu |
| asignaciones_turno_adulto | cod_am | adulto_mayor | cod_am |
| asignaciones_turno_adulto | cod_cama | camas | cod_cama |
| asignaciones_turno_adulto | cod_habitacion | habitaciones | cod_habitacion |
| asignaciones_turno_adulto | cod_turno | turnos_enfermeria | cod_turno |
| asignaciones_turno_adulto | cod_usu_enfermero | users | cod_usu |
| asistencia_voluntarios | cod_vol | voluntarios | cod_vol |
| atenciones_adulto | cod_am | adulto_mayor | cod_am |
| atenciones_adulto | cod_tipo_aten | tipo_atenciones_adulto | cod_tipo_aten |
| camas | cod_habitacion | habitaciones | cod_habitacion |
| disponibilidad_voluntarios | cod_vol | voluntarios | cod_vol |
| documentos_adulto_mayor | cod_am | adulto_mayor | cod_am |
| documentos_usuarios | cod_tipo_doc | tipos_documentos_usuario | cod_tipo_doc |
| documentos_usuarios | cod_usu | users | cod_usu |
| documentos_usuarios | reemplaza_a | documentos_usuarios | cod_doc_usu |
| documentos_usuarios | subido_por | users | cod_usu |
| documentos_usuarios | validado_por | users | cod_usu |
| evaluaciones_cognitivas | cod_am | adulto_mayor | cod_am |
| evaluaciones_cognitivas | cod_per_sal | personal_salud | cod_per_sal |
| evaluaciones_cognitivas | cod_tipo_eval | tipo_evaluacion_cognitiva | cod_tipo_eval |
| evaluaciones_geriatricas | anulado_por | users | cod_usu |
| evaluaciones_geriatricas | cod_am | adulto_mayor | cod_am |
| evaluaciones_geriatricas | cod_instrumento | instrumentos_geriatricos | cod_instrumento |
| evaluaciones_geriatricas | registrado_por | users | cod_usu |
| familiar_adulto | cod_am | adulto_mayor | cod_am |
| familiar_adulto | cod_fam | familiares | cod_fam |
| familiares | cod_usu | users | cod_usu |
| ficha_medica_adulto | cod_am | adulto_mayor | cod_am |
| ficha_medica_adulto | registrado_por | users | cod_usu |
| historial_estado_adulto | cambiado_por | users | cod_usu |
| historial_estado_adulto | cod_am | adulto_mayor | cod_am |
| historial_estado_adulto | documento_respaldo | documentos_adulto_mayor | cod_doc_am |
| historial_estado_adulto | estado_anterior | estado_adulto | cod_est_adul |
| historial_estado_adulto | estado_nuevo | estado_adulto | cod_est_adul |
| horarios_personal_admin | cod_per_adm | personal_admin | cod_per_adm |
| horarios_personal_salud | cod_per_sal | personal_salud | cod_per_sal |
| instrumentos_geriatricos | cod_area | areas_geriatricas | cod_area |
| medicacion_adulto | cod_am | adulto_mayor | cod_am |
| medicacion_adulto | documento_receta | documentos_adulto_mayor | cod_doc_am |
| medicacion_adulto | registrado_por | users | cod_usu |
| model_has_permissions | permission_id | permissions | id |
| model_has_roles | role_id | roles | id |
| obs_adulto | cod_am | adulto_mayor | cod_am |
| obs_adulto | cod_est_adul | estado_adulto | cod_est_adul |
| obs_adulto | registrado_por | users | cod_usu |
| pases_turno | cod_am | adulto_mayor | cod_am |
| pases_turno | enfermero_entrante_id | users | cod_usu |
| pases_turno | enfermero_saliente_id | users | cod_usu |
| pases_turno | turno_entrante_id | turnos_enfermeria | cod_turno |
| pases_turno | turno_saliente_id | turnos_enfermeria | cod_turno |
| personal_admin | cod_cargo_admin | cargos_administrativos | cod_cargo_admin |
| personal_admin | cod_usu | users | cod_usu |
| personal_salud | cod_esp | especialidades | cod_esp |
| personal_salud | cod_usu | users | cod_usu |
| planes_cuidado | cod_am | adulto_mayor | cod_am |
| planes_cuidado | creado_por | users | cod_usu |
| planes_cuidado | validado_por | users | cod_usu |
| role_has_permissions | permission_id | permissions | id |
| role_has_permissions | role_id | roles | id |
| seguimientos_diarios | cod_am | adulto_mayor | cod_am |
| seguimientos_diarios | cod_plan | planes_cuidado | cod_plan |
| seguimientos_diarios | cod_turno | turnos_enfermeria | cod_turno |
| seguimientos_diarios | registrado_por | users | cod_usu |
| signos_vitales_adulto | anulado_por | users | cod_usu |
| signos_vitales_adulto | cod_am | adulto_mayor | cod_am |
| signos_vitales_adulto | cod_tarea | tareas_plan_cuidado | cod_tarea |
| signos_vitales_adulto | cod_turno | turnos_enfermeria | cod_turno |
| signos_vitales_adulto | registrado_por | users | cod_usu |
| tareas_plan_cuidado | cod_am | adulto_mayor | cod_am |
| tareas_plan_cuidado | cod_plan | planes_cuidado | cod_plan |
| tareas_plan_cuidado | cod_turno | turnos_enfermeria | cod_turno |
| tareas_plan_cuidado | registrado_por | users | cod_usu |
| tareas_plan_cuidado | responsable_id | users | cod_usu |
| tareas_plan_cuidado | transferida_a_turno_id | turnos_enfermeria | cod_turno |
| turnos_institucionales | actualizado_por | users | cod_usu |
| turnos_institucionales | creado_por | users | cod_usu |
| users | cod_area | areas_institucionales | cod_area |
| valoracion_funcional_adulto | anulado_por | users | cod_usu |
| valoracion_funcional_adulto | cod_am | adulto_mayor | cod_am |
| valoracion_funcional_adulto | registrado_por | users | cod_usu |
| valoraciones_enfermeria_admision | cod_am | adulto_mayor | cod_am |
| valoraciones_enfermeria_admision | registrado_por | users | cod_usu |
| valoraciones_medicas_admision | cod_am | adulto_mayor | cod_am |
| valoraciones_medicas_admision | cod_val_enf | valoraciones_enfermeria_admision | cod_val_enf |
| valoraciones_medicas_admision | registrado_por | users | cod_usu |
| voluntarios | cod_usu | users | cod_usu |

### 3. Inconsistencias Detectadas

- **asignacion_voluntarios**: Falta columna `created_at`/`updated_at`.
- **asistencia_voluntarios**: Falta columna `created_at`/`updated_at`.
- **disponibilidad_voluntarios**: Falta columna `created_at`/`updated_at`.
- **especialidades**: Falta columna `created_at`/`updated_at`.
- **failed_jobs**: Falta columna `created_at`/`updated_at`.
- **horarios_personal_admin**: Falta columna `created_at`/`updated_at`.
- **horarios_personal_salud**: Falta columna `created_at`/`updated_at`.
- **tipo_actividades_adulto**: Falta columna `created_at`/`updated_at`.
- **tipo_atenciones_adulto**: Falta columna `created_at`/`updated_at`.

