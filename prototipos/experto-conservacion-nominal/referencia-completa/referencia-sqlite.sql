CREATE TABLE releases_nominales (
            huella_release CHAR(64) PRIMARY KEY,
            cod_release_motor VARCHAR(20) NOT NULL,
            huella_zip CHAR(64) NOT NULL,
            huella_adaptador CHAR(64) NOT NULL,
            revision_base CHAR(40) NOT NULL,
            php_version VARCHAR(30) NOT NULL,
            cantidad_archivos BIGINT NOT NULL CHECK(cantidad_archivos>0),
            huella_src CHAR(64) NOT NULL,
            huella_runtime_zip CHAR(64) NOT NULL,
            huella_php_ejecutable CHAR(64) NOT NULL
        );

CREATE TABLE evaluaciones_nominales (
            cod_evaluacion_experta VARCHAR(20) PRIMARY KEY,
            solicitud_uuid VARCHAR(36) NOT NULL UNIQUE,
            cod_residente VARCHAR(20) NOT NULL REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
            cod_usuario VARCHAR(20) NOT NULL REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT,
            huella_entrada CHAR(64) NOT NULL CHECK(length(huella_entrada)=64),
            huella_compuesta CHAR(64) NOT NULL CHECK(length(huella_compuesta)=64),
            huella_release CHAR(64) NOT NULL REFERENCES releases_nominales(huella_release) ON DELETE RESTRICT,
            contrato VARCHAR(40) NOT NULL CHECK(contrato='REFERENCIA-COMPLETA-NOMINAL-1'),
            estado VARCHAR(8) NOT NULL CHECK(estado IN ('ABIERTA','CERRADA'))
        );

CREATE TABLE entradas_institucionales (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    contrato__presente INTEGER NOT NULL,
    contrato TEXT,
    ejecutor__estado VARCHAR(8) NOT NULL CHECK(ejecutor__estado IN ('AUSENTE','NULL','PRESENTE')),
    ejecutor_cantidad_archivos__presente INTEGER NOT NULL,
    ejecutor_cantidad_archivos BIGINT,
    ejecutor_huella_src__presente INTEGER NOT NULL,
    ejecutor_huella_src TEXT,
    instrumental__estado VARCHAR(8) NOT NULL CHECK(instrumental__estado IN ('AUSENTE','NULL','PRESENTE')),
    i__estado VARCHAR(8) NOT NULL CHECK(i__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_cabecera__estado VARCHAR(8) NOT NULL CHECK(i_cabecera__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_cabecera_cod_version_modelo__presente INTEGER NOT NULL,
    i_cabecera_cod_version_modelo TEXT,
    i_capturas__estado VARCHAR(8) NOT NULL CHECK(i_capturas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_capturas_capturas__estado VARCHAR(8) NOT NULL CHECK(i_capturas_capturas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_capturas_capturas__cantidad BIGINT NOT NULL CHECK(i_capturas_capturas__cantidad>=0),
    i_capturas_disponible__presente INTEGER NOT NULL,
    i_capturas_disponible INTEGER,
    i_capturas_fecha_corte__presente INTEGER NOT NULL,
    i_capturas_fecha_corte TEXT,
    i_capturas_persistido__presente INTEGER NOT NULL,
    i_capturas_persistido INTEGER,
    i_k__estado VARCHAR(8) NOT NULL CHECK(i_k__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_contratos_extraccion__estado VARCHAR(8) NOT NULL CHECK(i_k_contratos_extraccion__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_contratos_extraccion_respuesta_institucional__presente INTEGER NOT NULL,
    i_k_contratos_extraccion_respuesta_institucional TEXT,
    i_k_contratos_relaciones__estado VARCHAR(8) NOT NULL CHECK(i_k_contratos_relaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_contratos_relaciones__cantidad BIGINT NOT NULL CHECK(i_k_contratos_relaciones__cantidad>=0),
    i_k_solo_pruebas_tecnicas__presente INTEGER NOT NULL,
    i_k_solo_pruebas_tecnicas INTEGER,
    i_k_tablas__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_condiciones_regla_experta__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_condiciones_regla_experta__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_condiciones_regla_experta__cantidad BIGINT NOT NULL CHECK(i_k_tablas_condiciones_regla_experta__cantidad>=0),
    i_k_tablas_consecuencias_regla_experta__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_consecuencias_regla_experta__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_consecuencias_regla_experta__cantidad BIGINT NOT NULL CHECK(i_k_tablas_consecuencias_regla_experta__cantidad>=0),
    i_k_tablas_criterios_dominios_r__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_criterios_dominios_r__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_criterios_dominios_r__cantidad BIGINT NOT NULL CHECK(i_k_tablas_criterios_dominios_r__cantidad>=0),
    i_k_tablas_dominios_valores_expertos__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_dominios_valores_expertos__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_dominios_valores_expertos__cantidad BIGINT NOT NULL CHECK(i_k_tablas_dominios_valores_expertos__cantidad>=0),
    i_k_tablas_fuentes_datos_expertas__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_fuentes_datos_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_fuentes_datos_expertas__cantidad BIGINT NOT NULL CHECK(i_k_tablas_fuentes_datos_expertas__cantidad>=0),
    i_k_tablas_mapeos_valores_fuente__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_mapeos_valores_fuente__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_mapeos_valores_fuente__cantidad BIGINT NOT NULL CHECK(i_k_tablas_mapeos_valores_fuente__cantidad>=0),
    i_k_tablas_mapeos_variables_fuente__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_mapeos_variables_fuente__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_mapeos_variables_fuente__cantidad BIGINT NOT NULL CHECK(i_k_tablas_mapeos_variables_fuente__cantidad>=0),
    i_k_tablas_nodos_semanticos__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_nodos_semanticos__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_nodos_semanticos__cantidad BIGINT NOT NULL CHECK(i_k_tablas_nodos_semanticos__cantidad>=0),
    i_k_tablas_reglas_expertas__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_reglas_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_reglas_expertas__cantidad BIGINT NOT NULL CHECK(i_k_tablas_reglas_expertas__cantidad>=0),
    i_k_tablas_relaciones_semanticas__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_relaciones_semanticas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_relaciones_semanticas__cantidad BIGINT NOT NULL CHECK(i_k_tablas_relaciones_semanticas__cantidad>=0),
    i_k_tablas_valores_semanticos__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_valores_semanticos__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_valores_semanticos__cantidad BIGINT NOT NULL CHECK(i_k_tablas_valores_semanticos__cantidad>=0),
    i_k_tablas_variables_expertas__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_variables_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_variables_expertas__cantidad BIGINT NOT NULL CHECK(i_k_tablas_variables_expertas__cantidad>=0),
    i_k_tipo_regla_r__presente INTEGER NOT NULL,
    i_k_tipo_regla_r TEXT,
    i_k_version__presente INTEGER NOT NULL,
    i_k_version TEXT,
    i_contexto__estado VARCHAR(8) NOT NULL CHECK(i_contexto__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_contexto_evaluacion__presente INTEGER NOT NULL,
    i_contexto_evaluacion TEXT,
    i_contexto_fecha_corte__estado VARCHAR(8) NOT NULL CHECK(i_contexto_fecha_corte__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_contexto_fecha_corte_corte_offset__presente INTEGER NOT NULL,
    i_contexto_fecha_corte_corte_offset BIGINT,
    i_contexto_fecha_corte_corte_utc__presente INTEGER NOT NULL,
    i_contexto_fecha_corte_corte_utc TEXT,
    i_contexto_fecha_corte_corte_zona__presente INTEGER NOT NULL,
    i_contexto_fecha_corte_corte_zona TEXT,
    i_contexto_residente__presente INTEGER NOT NULL,
    i_contexto_residente TEXT,
    i_contexto_usuario_ejecucion__presente INTEGER NOT NULL,
    i_contexto_usuario_ejecucion TEXT,
    i_contexto_version__presente INTEGER NOT NULL,
    i_contexto_version TEXT,
    i_contrato__presente INTEGER NOT NULL,
    i_contrato TEXT,
    i_fuente_conceptual__estado VARCHAR(8) NOT NULL CHECK(i_fuente_conceptual__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_fuente_conceptual_documento__presente INTEGER NOT NULL,
    i_fuente_conceptual_documento TEXT,
    i_fuente_conceptual_documento_maestro_sha256__presente INTEGER NOT NULL,
    i_fuente_conceptual_documento_maestro_sha256 TEXT,
    i_fuente_conceptual_documento_url__presente INTEGER NOT NULL,
    i_fuente_conceptual_documento_url TEXT,
    i_fuente_conceptual_fecha_consulta__presente INTEGER NOT NULL,
    i_fuente_conceptual_fecha_consulta TEXT,
    i_fuente_conceptual_limite__presente INTEGER NOT NULL,
    i_fuente_conceptual_limite TEXT,
    i_fuente_conceptual_matrices_sha256__presente INTEGER NOT NULL,
    i_fuente_conceptual_matrices_sha256 TEXT,
    i_fuente_conceptual_matrices_url__presente INTEGER NOT NULL,
    i_fuente_conceptual_matrices_url TEXT,
    i_fuente_conceptual_nodos__estado VARCHAR(8) NOT NULL CHECK(i_fuente_conceptual_nodos__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_fuente_conceptual_nodos__cantidad BIGINT NOT NULL CHECK(i_fuente_conceptual_nodos__cantidad>=0),
    i_fuente_conceptual_relaciones__estado VARCHAR(8) NOT NULL CHECK(i_fuente_conceptual_relaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_fuente_conceptual_relaciones__cantidad BIGINT NOT NULL CHECK(i_fuente_conceptual_relaciones__cantidad>=0),
    i_fue_con_rel_doc_no_mat__presente INTEGER NOT NULL,
    i_fue_con_rel_doc_no_mat BIGINT,
    i_s__estado VARCHAR(8) NOT NULL CHECK(i_s__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_fuentes_excluidas__estado VARCHAR(8) NOT NULL CHECK(i_s_fuentes_excluidas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_adquisicion__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_adquisicion__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_adquisicion_aplicaciones__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_adquisicion_aplicaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_adquisicion_aplicaciones__cantidad BIGINT NOT NULL CHECK(i_s_perfil_adquisicion_aplicaciones__cantidad>=0),
    i_s_per_adq_aut_int_cli__presente INTEGER NOT NULL,
    i_s_per_adq_aut_int_cli INTEGER,
    i_s_perfil_adquisicion_contrato__presente INTEGER NOT NULL,
    i_s_perfil_adquisicion_contrato TEXT,
    i_s_perfil_adquisicion_metricas_entradas_60_o_mas__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_adquisicion_metricas_entradas_60_o_mas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_per_adq_met_ent_60_o_mas_apl_obs_sin_nor__presente INTEGER NOT NULL,
    i_s_per_adq_met_ent_60_o_mas_apl_obs_sin_nor BIGINT,
    i_s_per_adq_met_ent_60_o_mas_apl_sin_ext__presente INTEGER NOT NULL,
    i_s_per_adq_met_ent_60_o_mas_apl_sin_ext BIGINT,
    i_s_per_adq_met_ent_60_o_mas_den__presente INTEGER NOT NULL,
    i_s_per_adq_met_ent_60_o_mas_den TEXT,
    i_s_per_adq_met_ent_60_o_mas_ent_con_dat_tip__presente INTEGER NOT NULL,
    i_s_per_adq_met_ent_60_o_mas_ent_con_dat_tip BIGINT,
    i_s_per_adq_met_ent_60_o_mas_ent_fal_o_inv__presente INTEGER NOT NULL,
    i_s_per_adq_met_ent_60_o_mas_ent_fal_o_inv BIGINT,
    i_s_per_adq_met_ent_60_o_mas_ent_req__presente INTEGER NOT NULL,
    i_s_per_adq_met_ent_60_o_mas_ent_req BIGINT,
    i_s_per_adq_met_ent_60_o_mas_lim__presente INTEGER NOT NULL,
    i_s_per_adq_met_ent_60_o_mas_lim TEXT,
    i_s_per_adq_met_ent_60_o_mas_por_com__presente INTEGER NOT NULL,
    i_s_per_adq_met_ent_60_o_mas_por_com REAL,
    i_s_per_adq_met_ent_60_o_mas_por_com__cero_negativo INTEGER NOT NULL DEFAULT 0,
    i_s_perfil_contrato__presente INTEGER NOT NULL,
    i_s_perfil_contrato TEXT,
    i_s_perfil_diagnostico__presente INTEGER NOT NULL,
    i_s_perfil_diagnostico TEXT,
    i_s_perfil_dominios__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_dominios__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_dominios__cantidad BIGINT NOT NULL CHECK(i_s_perfil_dominios__cantidad>=0),
    i_s_perfil_metricas_60_o_mas__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_metricas_60_o_mas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_per_met_60_o_mas_apl_60_o_mas__presente INTEGER NOT NULL,
    i_s_per_met_60_o_mas_apl_60_o_mas BIGINT,
    i_s_per_met_60_o_mas_apl_eda_des__presente INTEGER NOT NULL,
    i_s_per_met_60_o_mas_apl_eda_des BIGINT,
    i_s_per_met_60_o_mas_apl_exa__presente INTEGER NOT NULL,
    i_s_per_met_60_o_mas_apl_exa BIGINT,
    i_s_per_met_60_o_mas_apl_men_60__presente INTEGER NOT NULL,
    i_s_per_met_60_o_mas_apl_men_60 BIGINT,
    i_s_per_met_60_o_mas_com_con_com__presente INTEGER NOT NULL,
    i_s_per_met_60_o_mas_com_con_com BIGINT,
    i_s_per_met_60_o_mas_com_del_per__presente INTEGER NOT NULL,
    i_s_per_met_60_o_mas_com_del_per BIGINT,
    i_s_perfil_metricas_60_o_mas_denominador__presente INTEGER NOT NULL,
    i_s_perfil_metricas_60_o_mas_denominador TEXT,
    i_s_per_met_60_o_mas_por_cob_com__presente INTEGER NOT NULL,
    i_s_per_met_60_o_mas_por_cob_com REAL,
    i_s_per_met_60_o_mas_por_cob_com__cero_negativo INTEGER NOT NULL DEFAULT 0,
    i_s_perfil_metricas_60_o_mas_tipo_metrica__presente INTEGER NOT NULL,
    i_s_perfil_metricas_60_o_mas_tipo_metrica TEXT,
    i_s_perfil_persistido__presente INTEGER NOT NULL,
    i_s_perfil_persistido INTEGER,
    i_s_perfil_red__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_red__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_red_contratos__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_red_contratos__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_red_contratos__cantidad BIGINT NOT NULL CHECK(i_s_perfil_red_contratos__cantidad>=0),
    i_s_perfil_red_documento_sha256__presente INTEGER NOT NULL,
    i_s_perfil_red_documento_sha256 TEXT,
    i_s_perfil_red_dominios__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_red_dominios__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_red_dominios__cantidad BIGINT NOT NULL CHECK(i_s_perfil_red_dominios__cantidad>=0),
    i_s_perfil_red_ejecutable_clinico__presente INTEGER NOT NULL,
    i_s_perfil_red_ejecutable_clinico INTEGER,
    i_s_perfil_red_matrices_sha256__presente INTEGER NOT NULL,
    i_s_perfil_red_matrices_sha256 TEXT,
    i_s_perfil_red_nodos__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_red_nodos__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_red_nodos__cantidad BIGINT NOT NULL CHECK(i_s_perfil_red_nodos__cantidad>=0),
    i_s_perfil_red_relaciones__estado VARCHAR(8) NOT NULL CHECK(i_s_perfil_red_relaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_perfil_red_relaciones__cantidad BIGINT NOT NULL CHECK(i_s_perfil_red_relaciones__cantidad>=0),
    i_s_perfil_red_version__presente INTEGER NOT NULL,
    i_s_perfil_red_version TEXT,
    i_s_perfil_revision_profesional__presente INTEGER NOT NULL,
    i_s_perfil_revision_profesional TEXT,
    i_s_r__estado VARCHAR(8) NOT NULL CHECK(i_s_r__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_r_alcance__presente INTEGER NOT NULL,
    i_s_r_alcance TEXT,
    i_s_r_clasificacion_dcl__presente INTEGER NOT NULL,
    i_s_r_clasificacion_dcl TEXT,
    i_s_r_contrato_institucional__presente INTEGER NOT NULL,
    i_s_r_contrato_institucional TEXT,
    i_s_r_evaluacion__presente INTEGER NOT NULL,
    i_s_r_evaluacion TEXT,
    i_s_r_fecha_corte__presente INTEGER NOT NULL,
    i_s_r_fecha_corte TEXT,
    i_s_r_observaciones_por_componente__estado VARCHAR(8) NOT NULL CHECK(i_s_r_observaciones_por_componente__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_r_perfil__estado VARCHAR(8) NOT NULL CHECK(i_s_r_perfil__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_r_perfil__cantidad BIGINT NOT NULL CHECK(i_s_r_perfil__cantidad>=0),
    i_s_r_persistido__presente INTEGER NOT NULL,
    i_s_r_persistido INTEGER,
    i_s_r_red__estado VARCHAR(8) NOT NULL CHECK(i_s_r_red__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_r_red_componentes__estado VARCHAR(8) NOT NULL CHECK(i_s_r_red_componentes__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_r_red_componentes__cantidad BIGINT NOT NULL CHECK(i_s_r_red_componentes__cantidad>=0),
    i_s_r_red_dominios__estado VARCHAR(8) NOT NULL CHECK(i_s_r_red_dominios__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_r_red_dominios__cantidad BIGINT NOT NULL CHECK(i_s_r_red_dominios__cantidad>=0),
    i_s_r_red_rutas_reglas__estado VARCHAR(8) NOT NULL CHECK(i_s_r_red_rutas_reglas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_s_r_red_rutas_reglas__cantidad BIGINT NOT NULL CHECK(i_s_r_red_rutas_reglas__cantidad>=0),
    i_s_r_red_version__presente INTEGER NOT NULL,
    i_s_r_red_version TEXT,
    i_s_r_residente__presente INTEGER NOT NULL,
    i_s_r_residente TEXT,
    i_s_r_version__presente INTEGER NOT NULL,
    i_s_r_version TEXT,
    i_vinculos__estado VARCHAR(8) NOT NULL CHECK(i_vinculos__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_vinculos__cantidad BIGINT NOT NULL CHECK(i_vinculos__cantidad>=0),
    instrumental_huella__presente INTEGER NOT NULL,
    instrumental_huella TEXT,
    persistido__presente INTEGER NOT NULL,
    persistido INTEGER,
    petersen__estado VARCHAR(8) NOT NULL CHECK(petersen__estado IN ('AUSENTE','NULL','PRESENTE')),
    p__estado VARCHAR(8) NOT NULL CHECK(p__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_adquisicion__estado VARCHAR(8) NOT NULL CHECK(p_adquisicion__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_adquisicion_conservacion_completa__presente INTEGER NOT NULL,
    p_adquisicion_conservacion_completa INTEGER,
    p_adquisicion_exclusiones__estado VARCHAR(8) NOT NULL CHECK(p_adquisicion_exclusiones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_adquisicion_exclusiones__cantidad BIGINT NOT NULL CHECK(p_adquisicion_exclusiones__cantidad>=0),
    p_adquisicion_fuentes_originales_mutables__presente INTEGER NOT NULL,
    p_adquisicion_fuentes_originales_mutables INTEGER,
    p_adquisicion_valoraciones_al_corte__presente INTEGER NOT NULL,
    p_adquisicion_valoraciones_al_corte BIGINT,
    p_constancias_documentales__estado VARCHAR(8) NOT NULL CHECK(p_constancias_documentales__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_constancias_documentales__cantidad BIGINT NOT NULL CHECK(p_constancias_documentales__cantidad>=0),
    p_contexto__estado VARCHAR(8) NOT NULL CHECK(p_contexto__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_contexto_evaluacion__presente INTEGER NOT NULL,
    p_contexto_evaluacion TEXT,
    p_contexto_fecha_corte__estado VARCHAR(8) NOT NULL CHECK(p_contexto_fecha_corte__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_contexto_fecha_corte_corte_offset__presente INTEGER NOT NULL,
    p_contexto_fecha_corte_corte_offset BIGINT,
    p_contexto_fecha_corte_corte_utc__presente INTEGER NOT NULL,
    p_contexto_fecha_corte_corte_utc TEXT,
    p_contexto_fecha_corte_corte_zona__presente INTEGER NOT NULL,
    p_contexto_fecha_corte_corte_zona TEXT,
    p_contexto_residente__presente INTEGER NOT NULL,
    p_contexto_residente TEXT,
    p_contexto_usuario_ejecucion__presente INTEGER NOT NULL,
    p_contexto_usuario_ejecucion TEXT,
    p_contexto_version__presente INTEGER NOT NULL,
    p_contexto_version TEXT,
    p_contrato__presente INTEGER NOT NULL,
    p_contrato TEXT,
    p_gobierno__estado VARCHAR(8) NOT NULL CHECK(p_gobierno__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_cabecera__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_cabecera__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_cabecera_cod_usuario_creacion__presente INTEGER NOT NULL,
    p_gobierno_cabecera_cod_usuario_creacion TEXT,
    p_gobierno_cabecera_cod_version_anterior__presente INTEGER NOT NULL,
    p_gobierno_cabecera_cod_version_anterior TEXT,
    p_gobierno_cabecera_cod_version_modelo__presente INTEGER NOT NULL,
    p_gobierno_cabecera_cod_version_modelo TEXT,
    p_gobierno_cabecera_clave_version__presente INTEGER NOT NULL,
    p_gobierno_cabecera_clave_version TEXT,
    p_gobierno_cabecera_descripcion__presente INTEGER NOT NULL,
    p_gobierno_cabecera_descripcion TEXT,
    p_gobierno_cabecera_estado__presente INTEGER NOT NULL,
    p_gobierno_cabecera_estado TEXT,
    p_gobierno_cabecera_fecha_creacion__presente INTEGER NOT NULL,
    p_gobierno_cabecera_fecha_creacion TEXT,
    p_gobierno_cabecera_fecha_retiro__presente INTEGER NOT NULL,
    p_gobierno_cabecera_fecha_retiro TEXT,
    p_gobierno_cabecera_fecha_vigencia__presente INTEGER NOT NULL,
    p_gobierno_cabecera_fecha_vigencia TEXT,
    p_gobierno_cabecera_motivo_cambio__presente INTEGER NOT NULL,
    p_gobierno_cabecera_motivo_cambio TEXT,
    p_gobierno_cabecera_nombre__presente INTEGER NOT NULL,
    p_gobierno_cabecera_nombre TEXT,
    p_gobierno_cabecera_observacion__presente INTEGER NOT NULL,
    p_gobierno_cabecera_observacion TEXT,
    p_gobierno_k__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_contratos_extraccion__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_contratos_extraccion__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_contratos_relaciones__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_contratos_relaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_contratos_relaciones__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_contratos_relaciones__cantidad>=0),
    p_gobierno_k_solo_pruebas_tecnicas__presente INTEGER NOT NULL,
    p_gobierno_k_solo_pruebas_tecnicas INTEGER,
    p_gobierno_k_tablas__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_condiciones_regla_experta__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_condiciones_regla_experta__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_condiciones_regla_experta__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_condiciones_regla_experta__cantidad>=0),
    p_gobierno_k_tablas_consecuencias_regla_experta__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_consecuencias_regla_experta__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_consecuencias_regla_experta__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_consecuencias_regla_experta__cantidad>=0),
    p_gobierno_k_tablas_criterios_dominios_r__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_criterios_dominios_r__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_criterios_dominios_r__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_criterios_dominios_r__cantidad>=0),
    p_gobierno_k_tablas_dominios_valores_expertos__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_dominios_valores_expertos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_dominios_valores_expertos__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_dominios_valores_expertos__cantidad>=0),
    p_gobierno_k_tablas_fuentes_datos_expertas__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_fuentes_datos_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_mapeos_valores_fuente__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_mapeos_valores_fuente__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_mapeos_variables_fuente__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_mapeos_variables_fuente__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_nodos_semanticos__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_nodos_semanticos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_nodos_semanticos__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_nodos_semanticos__cantidad>=0),
    p_gobierno_k_tablas_reglas_expertas__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_reglas_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_reglas_expertas__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_reglas_expertas__cantidad>=0),
    p_gobierno_k_tablas_relaciones_semanticas__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_relaciones_semanticas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_relaciones_semanticas__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_relaciones_semanticas__cantidad>=0),
    p_gobierno_k_tablas_valores_semanticos__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_valores_semanticos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_valores_semanticos__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_valores_semanticos__cantidad>=0),
    p_gobierno_k_tablas_variables_expertas__estado VARCHAR(8) NOT NULL CHECK(p_gobierno_k_tablas_variables_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_gobierno_k_tablas_variables_expertas__cantidad BIGINT NOT NULL CHECK(p_gobierno_k_tablas_variables_expertas__cantidad>=0),
    p_gobierno_k_tipo_regla_r__presente INTEGER NOT NULL,
    p_gobierno_k_tipo_regla_r TEXT,
    p_gobierno_k_version__presente INTEGER NOT NULL,
    p_gobierno_k_version TEXT,
    p_limites__estado VARCHAR(8) NOT NULL CHECK(p_limites__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_limites_persistido__presente INTEGER NOT NULL,
    p_limites_persistido INTEGER,
    p_limites_reproduce_adquisicion_sql__presente INTEGER NOT NULL,
    p_limites_reproduce_adquisicion_sql INTEGER,
    p_limites_revalida_bytes_documentales__presente INTEGER NOT NULL,
    p_limites_revalida_bytes_documentales INTEGER,
    p_limites_revalida_permisos_historicos__presente INTEGER NOT NULL,
    p_limites_revalida_permisos_historicos INTEGER,
    p_membresias_soportes__estado VARCHAR(8) NOT NULL CHECK(p_membresias_soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_membresias_soportes__cantidad BIGINT NOT NULL CHECK(p_membresias_soportes__cantidad>=0),
    p_procedencia_valoraciones__estado VARCHAR(8) NOT NULL CHECK(p_procedencia_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_procedencia_valoraciones__cantidad BIGINT NOT NULL CHECK(p_procedencia_valoraciones__cantidad>=0),
    p_s__estado VARCHAR(8) NOT NULL CHECK(p_s__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_contrato__presente INTEGER NOT NULL,
    p_s_contrato TEXT,
    p_s_dominios__estado VARCHAR(8) NOT NULL CHECK(p_s_dominios__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_dominios__cantidad BIGINT NOT NULL CHECK(p_s_dominios__cantidad>=0),
    p_s_estado__presente INTEGER NOT NULL,
    p_s_estado TEXT,
    p_s_evaluacion__presente INTEGER NOT NULL,
    p_s_evaluacion TEXT,
    p_s_fecha_corte__presente INTEGER NOT NULL,
    p_s_fecha_corte TEXT,
    p_s_gobierno__estado VARCHAR(8) NOT NULL CHECK(p_s_gobierno__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_gobierno_bloqueada_por__estado VARCHAR(8) NOT NULL CHECK(p_s_gobierno_bloqueada_por__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_gobierno_bloqueada_por__cantidad BIGINT NOT NULL CHECK(p_s_gobierno_bloqueada_por__cantidad>=0),
    p_s_gobierno_clave_version__presente INTEGER NOT NULL,
    p_s_gobierno_clave_version TEXT,
    p_s_gobierno_disponible__presente INTEGER NOT NULL,
    p_s_gobierno_disponible INTEGER,
    p_s_gobierno_estados_derivados__estado VARCHAR(8) NOT NULL CHECK(p_s_gobierno_estados_derivados__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_gobierno_estados_derivados__cantidad BIGINT NOT NULL CHECK(p_s_gobierno_estados_derivados__cantidad>=0),
    p_s_gobierno_origen__presente INTEGER NOT NULL,
    p_s_gobierno_origen TEXT,
    p_s_gobierno_patron__presente INTEGER NOT NULL,
    p_s_gobierno_patron TEXT,
    p_s_gobierno_persistido__presente INTEGER NOT NULL,
    p_s_gobierno_persistido INTEGER,
    p_s_gobierno_reglas__estado VARCHAR(8) NOT NULL CHECK(p_s_gobierno_reglas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_gobierno_reglas__cantidad BIGINT NOT NULL CHECK(p_s_gobierno_reglas__cantidad>=0),
    p_s_gobierno_version__presente INTEGER NOT NULL,
    p_s_gobierno_version TEXT,
    p_s_impedimentos__estado VARCHAR(8) NOT NULL CHECK(p_s_impedimentos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_impedimentos__cantidad BIGINT NOT NULL CHECK(p_s_impedimentos__cantidad>=0),
    p_s_patron__presente INTEGER NOT NULL,
    p_s_patron TEXT,
    p_s_persistido__presente INTEGER NOT NULL,
    p_s_persistido INTEGER,
    p_s_requisitos__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c01__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c01__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c01_estado__presente INTEGER NOT NULL,
    p_s_requisitos_c01_estado TEXT,
    p_s_requisitos_c01_indices_valoraciones__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c01_indices_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c01_indices_valoraciones__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c01_indices_valoraciones__cantidad>=0),
    p_s_requisitos_c01_motivos__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c01_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c01_motivos__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c01_motivos__cantidad>=0),
    p_s_requisitos_c02__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c02__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c02_derivado_de__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c02_derivado_de__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c02_derivado_de__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c02_derivado_de__cantidad>=0),
    p_s_requisitos_c02_estado__presente INTEGER NOT NULL,
    p_s_requisitos_c02_estado TEXT,
    p_s_requisitos_c02_indices_valoraciones__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c02_indices_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c02_motivos__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c02_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c02_motivos__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c02_motivos__cantidad>=0),
    p_s_requisitos_c03__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c03__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c03_estado__presente INTEGER NOT NULL,
    p_s_requisitos_c03_estado TEXT,
    p_s_requisitos_c03_indices_valoraciones__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c03_indices_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c03_indices_valoraciones__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c03_indices_valoraciones__cantidad>=0),
    p_s_requisitos_c03_motivos__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c03_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c03_motivos__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c03_motivos__cantidad>=0),
    p_s_requisitos_c04__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c04__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c04_estado__presente INTEGER NOT NULL,
    p_s_requisitos_c04_estado TEXT,
    p_s_requisitos_c04_indices_valoraciones__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c04_indices_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c04_indices_valoraciones__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c04_indices_valoraciones__cantidad>=0),
    p_s_requisitos_c04_motivos__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c04_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c04_motivos__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c04_motivos__cantidad>=0),
    p_s_requisitos_c05__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c05__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c05_estado__presente INTEGER NOT NULL,
    p_s_requisitos_c05_estado TEXT,
    p_s_requisitos_c05_indices_valoraciones__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c05_indices_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c05_indices_valoraciones__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c05_indices_valoraciones__cantidad>=0),
    p_s_requisitos_c05_motivos__estado VARCHAR(8) NOT NULL CHECK(p_s_requisitos_c05_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_requisitos_c05_motivos__cantidad BIGINT NOT NULL CHECK(p_s_requisitos_c05_motivos__cantidad>=0),
    p_s_residente__presente INTEGER NOT NULL,
    p_s_residente TEXT,
    p_s_revision_profesional__presente INTEGER NOT NULL,
    p_s_revision_profesional TEXT,
    p_s_valoraciones_excluidas__estado VARCHAR(8) NOT NULL CHECK(p_s_valoraciones_excluidas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_s_valoraciones_excluidas__cantidad BIGINT NOT NULL CHECK(p_s_valoraciones_excluidas__cantidad>=0),
    p_s_version__presente INTEGER NOT NULL,
    p_s_version TEXT,
    p_valoraciones__estado VARCHAR(8) NOT NULL CHECK(p_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_valoraciones__cantidad BIGINT NOT NULL CHECK(p_valoraciones__cantidad>=0),
    petersen_huella__presente INTEGER NOT NULL,
    petersen_huella TEXT,
    release__estado VARCHAR(8) NOT NULL CHECK(release__estado IN ('AUSENTE','NULL','PRESENTE')),
    release_archivos__estado VARCHAR(8) NOT NULL CHECK(release_archivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    release_archivos__cantidad BIGINT NOT NULL CHECK(release_archivos__cantidad>=0),
    release_contrato__presente INTEGER NOT NULL,
    release_contrato TEXT,
    release_huella__presente INTEGER NOT NULL,
    release_huella TEXT,
    release_incluye_datos_clinicos__presente INTEGER NOT NULL,
    release_incluye_datos_clinicos INTEGER,
    release_php_requerido__presente INTEGER NOT NULL,
    release_php_requerido TEXT,
    release_revision_base__presente INTEGER NOT NULL,
    release_revision_base TEXT,
    release_retenido__presente INTEGER NOT NULL,
    release_retenido INTEGER,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    UNIQUE(cod_evaluacion_experta),
    CHECK(contrato__presente IN (0,1)),
    CHECK(contrato__presente=1 OR contrato IS NULL),
    CHECK(ejecutor_cantidad_archivos__presente IN (0,1)),
    CHECK(ejecutor_cantidad_archivos__presente=1 OR ejecutor_cantidad_archivos IS NULL),
    CHECK(ejecutor_huella_src__presente IN (0,1)),
    CHECK(ejecutor_huella_src__presente=1 OR ejecutor_huella_src IS NULL),
    CHECK(i_cabecera_cod_version_modelo__presente IN (0,1)),
    CHECK(i_cabecera_cod_version_modelo__presente=1 OR i_cabecera_cod_version_modelo IS NULL),
    CHECK(i_capturas_capturas__estado='PRESENTE' OR i_capturas_capturas__cantidad=0),
    CHECK(i_capturas_disponible__presente IN (0,1)),
    CHECK(i_capturas_disponible__presente=1 OR i_capturas_disponible IS NULL),
    CHECK(i_capturas_disponible IS NULL OR i_capturas_disponible IN (0,1)),
    CHECK(i_capturas_fecha_corte__presente IN (0,1)),
    CHECK(i_capturas_fecha_corte__presente=1 OR i_capturas_fecha_corte IS NULL),
    CHECK(i_capturas_persistido__presente IN (0,1)),
    CHECK(i_capturas_persistido__presente=1 OR i_capturas_persistido IS NULL),
    CHECK(i_capturas_persistido IS NULL OR i_capturas_persistido IN (0,1)),
    CHECK(i_k_contratos_extraccion_respuesta_institucional__presente IN (0,1)),
    CHECK(i_k_contratos_extraccion_respuesta_institucional__presente=1 OR i_k_contratos_extraccion_respuesta_institucional IS NULL),
    CHECK(i_k_contratos_relaciones__estado='PRESENTE' OR i_k_contratos_relaciones__cantidad=0),
    CHECK(i_k_solo_pruebas_tecnicas__presente IN (0,1)),
    CHECK(i_k_solo_pruebas_tecnicas__presente=1 OR i_k_solo_pruebas_tecnicas IS NULL),
    CHECK(i_k_solo_pruebas_tecnicas IS NULL OR i_k_solo_pruebas_tecnicas IN (0,1)),
    CHECK(i_k_tablas_condiciones_regla_experta__estado='PRESENTE' OR i_k_tablas_condiciones_regla_experta__cantidad=0),
    CHECK(i_k_tablas_consecuencias_regla_experta__estado='PRESENTE' OR i_k_tablas_consecuencias_regla_experta__cantidad=0),
    CHECK(i_k_tablas_criterios_dominios_r__estado='PRESENTE' OR i_k_tablas_criterios_dominios_r__cantidad=0),
    CHECK(i_k_tablas_dominios_valores_expertos__estado='PRESENTE' OR i_k_tablas_dominios_valores_expertos__cantidad=0),
    CHECK(i_k_tablas_fuentes_datos_expertas__estado='PRESENTE' OR i_k_tablas_fuentes_datos_expertas__cantidad=0),
    CHECK(i_k_tablas_mapeos_valores_fuente__estado='PRESENTE' OR i_k_tablas_mapeos_valores_fuente__cantidad=0),
    CHECK(i_k_tablas_mapeos_variables_fuente__estado='PRESENTE' OR i_k_tablas_mapeos_variables_fuente__cantidad=0),
    CHECK(i_k_tablas_nodos_semanticos__estado='PRESENTE' OR i_k_tablas_nodos_semanticos__cantidad=0),
    CHECK(i_k_tablas_reglas_expertas__estado='PRESENTE' OR i_k_tablas_reglas_expertas__cantidad=0),
    CHECK(i_k_tablas_relaciones_semanticas__estado='PRESENTE' OR i_k_tablas_relaciones_semanticas__cantidad=0),
    CHECK(i_k_tablas_valores_semanticos__estado='PRESENTE' OR i_k_tablas_valores_semanticos__cantidad=0),
    CHECK(i_k_tablas_variables_expertas__estado='PRESENTE' OR i_k_tablas_variables_expertas__cantidad=0),
    CHECK(i_k_tipo_regla_r__presente IN (0,1)),
    CHECK(i_k_tipo_regla_r__presente=1 OR i_k_tipo_regla_r IS NULL),
    CHECK(i_k_version__presente IN (0,1)),
    CHECK(i_k_version__presente=1 OR i_k_version IS NULL),
    CHECK(i_contexto_evaluacion__presente IN (0,1)),
    CHECK(i_contexto_evaluacion__presente=1 OR i_contexto_evaluacion IS NULL),
    CHECK(i_contexto_fecha_corte_corte_offset__presente IN (0,1)),
    CHECK(i_contexto_fecha_corte_corte_offset__presente=1 OR i_contexto_fecha_corte_corte_offset IS NULL),
    CHECK(i_contexto_fecha_corte_corte_utc__presente IN (0,1)),
    CHECK(i_contexto_fecha_corte_corte_utc__presente=1 OR i_contexto_fecha_corte_corte_utc IS NULL),
    CHECK(i_contexto_fecha_corte_corte_zona__presente IN (0,1)),
    CHECK(i_contexto_fecha_corte_corte_zona__presente=1 OR i_contexto_fecha_corte_corte_zona IS NULL),
    CHECK(i_contexto_residente__presente IN (0,1)),
    CHECK(i_contexto_residente__presente=1 OR i_contexto_residente IS NULL),
    CHECK(i_contexto_usuario_ejecucion__presente IN (0,1)),
    CHECK(i_contexto_usuario_ejecucion__presente=1 OR i_contexto_usuario_ejecucion IS NULL),
    CHECK(i_contexto_version__presente IN (0,1)),
    CHECK(i_contexto_version__presente=1 OR i_contexto_version IS NULL),
    CHECK(i_contrato__presente IN (0,1)),
    CHECK(i_contrato__presente=1 OR i_contrato IS NULL),
    CHECK(i_fuente_conceptual_documento__presente IN (0,1)),
    CHECK(i_fuente_conceptual_documento__presente=1 OR i_fuente_conceptual_documento IS NULL),
    CHECK(i_fuente_conceptual_documento_maestro_sha256__presente IN (0,1)),
    CHECK(i_fuente_conceptual_documento_maestro_sha256__presente=1 OR i_fuente_conceptual_documento_maestro_sha256 IS NULL),
    CHECK(i_fuente_conceptual_documento_url__presente IN (0,1)),
    CHECK(i_fuente_conceptual_documento_url__presente=1 OR i_fuente_conceptual_documento_url IS NULL),
    CHECK(i_fuente_conceptual_fecha_consulta__presente IN (0,1)),
    CHECK(i_fuente_conceptual_fecha_consulta__presente=1 OR i_fuente_conceptual_fecha_consulta IS NULL),
    CHECK(i_fuente_conceptual_limite__presente IN (0,1)),
    CHECK(i_fuente_conceptual_limite__presente=1 OR i_fuente_conceptual_limite IS NULL),
    CHECK(i_fuente_conceptual_matrices_sha256__presente IN (0,1)),
    CHECK(i_fuente_conceptual_matrices_sha256__presente=1 OR i_fuente_conceptual_matrices_sha256 IS NULL),
    CHECK(i_fuente_conceptual_matrices_url__presente IN (0,1)),
    CHECK(i_fuente_conceptual_matrices_url__presente=1 OR i_fuente_conceptual_matrices_url IS NULL),
    CHECK(i_fuente_conceptual_nodos__estado='PRESENTE' OR i_fuente_conceptual_nodos__cantidad=0),
    CHECK(i_fuente_conceptual_relaciones__estado='PRESENTE' OR i_fuente_conceptual_relaciones__cantidad=0),
    CHECK(i_fue_con_rel_doc_no_mat__presente IN (0,1)),
    CHECK(i_fue_con_rel_doc_no_mat__presente=1 OR i_fue_con_rel_doc_no_mat IS NULL),
    CHECK(i_s_perfil_adquisicion_aplicaciones__estado='PRESENTE' OR i_s_perfil_adquisicion_aplicaciones__cantidad=0),
    CHECK(i_s_per_adq_aut_int_cli__presente IN (0,1)),
    CHECK(i_s_per_adq_aut_int_cli__presente=1 OR i_s_per_adq_aut_int_cli IS NULL),
    CHECK(i_s_per_adq_aut_int_cli IS NULL OR i_s_per_adq_aut_int_cli IN (0,1)),
    CHECK(i_s_perfil_adquisicion_contrato__presente IN (0,1)),
    CHECK(i_s_perfil_adquisicion_contrato__presente=1 OR i_s_perfil_adquisicion_contrato IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_apl_obs_sin_nor__presente IN (0,1)),
    CHECK(i_s_per_adq_met_ent_60_o_mas_apl_obs_sin_nor__presente=1 OR i_s_per_adq_met_ent_60_o_mas_apl_obs_sin_nor IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_apl_sin_ext__presente IN (0,1)),
    CHECK(i_s_per_adq_met_ent_60_o_mas_apl_sin_ext__presente=1 OR i_s_per_adq_met_ent_60_o_mas_apl_sin_ext IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_den__presente IN (0,1)),
    CHECK(i_s_per_adq_met_ent_60_o_mas_den__presente=1 OR i_s_per_adq_met_ent_60_o_mas_den IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_ent_con_dat_tip__presente IN (0,1)),
    CHECK(i_s_per_adq_met_ent_60_o_mas_ent_con_dat_tip__presente=1 OR i_s_per_adq_met_ent_60_o_mas_ent_con_dat_tip IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_ent_fal_o_inv__presente IN (0,1)),
    CHECK(i_s_per_adq_met_ent_60_o_mas_ent_fal_o_inv__presente=1 OR i_s_per_adq_met_ent_60_o_mas_ent_fal_o_inv IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_ent_req__presente IN (0,1)),
    CHECK(i_s_per_adq_met_ent_60_o_mas_ent_req__presente=1 OR i_s_per_adq_met_ent_60_o_mas_ent_req IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_lim__presente IN (0,1)),
    CHECK(i_s_per_adq_met_ent_60_o_mas_lim__presente=1 OR i_s_per_adq_met_ent_60_o_mas_lim IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_por_com__presente IN (0,1)),
    CHECK(i_s_per_adq_met_ent_60_o_mas_por_com__presente=1 OR i_s_per_adq_met_ent_60_o_mas_por_com IS NULL),
    CHECK(i_s_per_adq_met_ent_60_o_mas_por_com__cero_negativo=0 OR i_s_per_adq_met_ent_60_o_mas_por_com=0),
    CHECK(i_s_perfil_contrato__presente IN (0,1)),
    CHECK(i_s_perfil_contrato__presente=1 OR i_s_perfil_contrato IS NULL),
    CHECK(i_s_perfil_diagnostico__presente IN (0,1)),
    CHECK(i_s_perfil_diagnostico__presente=1 OR i_s_perfil_diagnostico IS NULL),
    CHECK(i_s_perfil_dominios__estado='PRESENTE' OR i_s_perfil_dominios__cantidad=0),
    CHECK(i_s_per_met_60_o_mas_apl_60_o_mas__presente IN (0,1)),
    CHECK(i_s_per_met_60_o_mas_apl_60_o_mas__presente=1 OR i_s_per_met_60_o_mas_apl_60_o_mas IS NULL),
    CHECK(i_s_per_met_60_o_mas_apl_eda_des__presente IN (0,1)),
    CHECK(i_s_per_met_60_o_mas_apl_eda_des__presente=1 OR i_s_per_met_60_o_mas_apl_eda_des IS NULL),
    CHECK(i_s_per_met_60_o_mas_apl_exa__presente IN (0,1)),
    CHECK(i_s_per_met_60_o_mas_apl_exa__presente=1 OR i_s_per_met_60_o_mas_apl_exa IS NULL),
    CHECK(i_s_per_met_60_o_mas_apl_men_60__presente IN (0,1)),
    CHECK(i_s_per_met_60_o_mas_apl_men_60__presente=1 OR i_s_per_met_60_o_mas_apl_men_60 IS NULL),
    CHECK(i_s_per_met_60_o_mas_com_con_com__presente IN (0,1)),
    CHECK(i_s_per_met_60_o_mas_com_con_com__presente=1 OR i_s_per_met_60_o_mas_com_con_com IS NULL),
    CHECK(i_s_per_met_60_o_mas_com_del_per__presente IN (0,1)),
    CHECK(i_s_per_met_60_o_mas_com_del_per__presente=1 OR i_s_per_met_60_o_mas_com_del_per IS NULL),
    CHECK(i_s_perfil_metricas_60_o_mas_denominador__presente IN (0,1)),
    CHECK(i_s_perfil_metricas_60_o_mas_denominador__presente=1 OR i_s_perfil_metricas_60_o_mas_denominador IS NULL),
    CHECK(i_s_per_met_60_o_mas_por_cob_com__presente IN (0,1)),
    CHECK(i_s_per_met_60_o_mas_por_cob_com__presente=1 OR i_s_per_met_60_o_mas_por_cob_com IS NULL),
    CHECK(i_s_per_met_60_o_mas_por_cob_com__cero_negativo=0 OR i_s_per_met_60_o_mas_por_cob_com=0),
    CHECK(i_s_perfil_metricas_60_o_mas_tipo_metrica__presente IN (0,1)),
    CHECK(i_s_perfil_metricas_60_o_mas_tipo_metrica__presente=1 OR i_s_perfil_metricas_60_o_mas_tipo_metrica IS NULL),
    CHECK(i_s_perfil_persistido__presente IN (0,1)),
    CHECK(i_s_perfil_persistido__presente=1 OR i_s_perfil_persistido IS NULL),
    CHECK(i_s_perfil_persistido IS NULL OR i_s_perfil_persistido IN (0,1)),
    CHECK(i_s_perfil_red_contratos__estado='PRESENTE' OR i_s_perfil_red_contratos__cantidad=0),
    CHECK(i_s_perfil_red_documento_sha256__presente IN (0,1)),
    CHECK(i_s_perfil_red_documento_sha256__presente=1 OR i_s_perfil_red_documento_sha256 IS NULL),
    CHECK(i_s_perfil_red_dominios__estado='PRESENTE' OR i_s_perfil_red_dominios__cantidad=0),
    CHECK(i_s_perfil_red_ejecutable_clinico__presente IN (0,1)),
    CHECK(i_s_perfil_red_ejecutable_clinico__presente=1 OR i_s_perfil_red_ejecutable_clinico IS NULL),
    CHECK(i_s_perfil_red_ejecutable_clinico IS NULL OR i_s_perfil_red_ejecutable_clinico IN (0,1)),
    CHECK(i_s_perfil_red_matrices_sha256__presente IN (0,1)),
    CHECK(i_s_perfil_red_matrices_sha256__presente=1 OR i_s_perfil_red_matrices_sha256 IS NULL),
    CHECK(i_s_perfil_red_nodos__estado='PRESENTE' OR i_s_perfil_red_nodos__cantidad=0),
    CHECK(i_s_perfil_red_relaciones__estado='PRESENTE' OR i_s_perfil_red_relaciones__cantidad=0),
    CHECK(i_s_perfil_red_version__presente IN (0,1)),
    CHECK(i_s_perfil_red_version__presente=1 OR i_s_perfil_red_version IS NULL),
    CHECK(i_s_perfil_revision_profesional__presente IN (0,1)),
    CHECK(i_s_perfil_revision_profesional__presente=1 OR i_s_perfil_revision_profesional IS NULL),
    CHECK(i_s_r_alcance__presente IN (0,1)),
    CHECK(i_s_r_alcance__presente=1 OR i_s_r_alcance IS NULL),
    CHECK(i_s_r_clasificacion_dcl__presente IN (0,1)),
    CHECK(i_s_r_clasificacion_dcl__presente=1 OR i_s_r_clasificacion_dcl IS NULL),
    CHECK(i_s_r_contrato_institucional__presente IN (0,1)),
    CHECK(i_s_r_contrato_institucional__presente=1 OR i_s_r_contrato_institucional IS NULL),
    CHECK(i_s_r_evaluacion__presente IN (0,1)),
    CHECK(i_s_r_evaluacion__presente=1 OR i_s_r_evaluacion IS NULL),
    CHECK(i_s_r_fecha_corte__presente IN (0,1)),
    CHECK(i_s_r_fecha_corte__presente=1 OR i_s_r_fecha_corte IS NULL),
    CHECK(i_s_r_perfil__estado='PRESENTE' OR i_s_r_perfil__cantidad=0),
    CHECK(i_s_r_persistido__presente IN (0,1)),
    CHECK(i_s_r_persistido__presente=1 OR i_s_r_persistido IS NULL),
    CHECK(i_s_r_persistido IS NULL OR i_s_r_persistido IN (0,1)),
    CHECK(i_s_r_red_componentes__estado='PRESENTE' OR i_s_r_red_componentes__cantidad=0),
    CHECK(i_s_r_red_dominios__estado='PRESENTE' OR i_s_r_red_dominios__cantidad=0),
    CHECK(i_s_r_red_rutas_reglas__estado='PRESENTE' OR i_s_r_red_rutas_reglas__cantidad=0),
    CHECK(i_s_r_red_version__presente IN (0,1)),
    CHECK(i_s_r_red_version__presente=1 OR i_s_r_red_version IS NULL),
    CHECK(i_s_r_residente__presente IN (0,1)),
    CHECK(i_s_r_residente__presente=1 OR i_s_r_residente IS NULL),
    CHECK(i_s_r_version__presente IN (0,1)),
    CHECK(i_s_r_version__presente=1 OR i_s_r_version IS NULL),
    CHECK(i_vinculos__estado='PRESENTE' OR i_vinculos__cantidad=0),
    CHECK(instrumental_huella__presente IN (0,1)),
    CHECK(instrumental_huella__presente=1 OR instrumental_huella IS NULL),
    CHECK(persistido__presente IN (0,1)),
    CHECK(persistido__presente=1 OR persistido IS NULL),
    CHECK(persistido IS NULL OR persistido IN (0,1)),
    CHECK(p_adquisicion_conservacion_completa__presente IN (0,1)),
    CHECK(p_adquisicion_conservacion_completa__presente=1 OR p_adquisicion_conservacion_completa IS NULL),
    CHECK(p_adquisicion_conservacion_completa IS NULL OR p_adquisicion_conservacion_completa IN (0,1)),
    CHECK(p_adquisicion_exclusiones__estado='PRESENTE' OR p_adquisicion_exclusiones__cantidad=0),
    CHECK(p_adquisicion_fuentes_originales_mutables__presente IN (0,1)),
    CHECK(p_adquisicion_fuentes_originales_mutables__presente=1 OR p_adquisicion_fuentes_originales_mutables IS NULL),
    CHECK(p_adquisicion_fuentes_originales_mutables IS NULL OR p_adquisicion_fuentes_originales_mutables IN (0,1)),
    CHECK(p_adquisicion_valoraciones_al_corte__presente IN (0,1)),
    CHECK(p_adquisicion_valoraciones_al_corte__presente=1 OR p_adquisicion_valoraciones_al_corte IS NULL),
    CHECK(p_constancias_documentales__estado='PRESENTE' OR p_constancias_documentales__cantidad=0),
    CHECK(p_contexto_evaluacion__presente IN (0,1)),
    CHECK(p_contexto_evaluacion__presente=1 OR p_contexto_evaluacion IS NULL),
    CHECK(p_contexto_fecha_corte_corte_offset__presente IN (0,1)),
    CHECK(p_contexto_fecha_corte_corte_offset__presente=1 OR p_contexto_fecha_corte_corte_offset IS NULL),
    CHECK(p_contexto_fecha_corte_corte_utc__presente IN (0,1)),
    CHECK(p_contexto_fecha_corte_corte_utc__presente=1 OR p_contexto_fecha_corte_corte_utc IS NULL),
    CHECK(p_contexto_fecha_corte_corte_zona__presente IN (0,1)),
    CHECK(p_contexto_fecha_corte_corte_zona__presente=1 OR p_contexto_fecha_corte_corte_zona IS NULL),
    CHECK(p_contexto_residente__presente IN (0,1)),
    CHECK(p_contexto_residente__presente=1 OR p_contexto_residente IS NULL),
    CHECK(p_contexto_usuario_ejecucion__presente IN (0,1)),
    CHECK(p_contexto_usuario_ejecucion__presente=1 OR p_contexto_usuario_ejecucion IS NULL),
    CHECK(p_contexto_version__presente IN (0,1)),
    CHECK(p_contexto_version__presente=1 OR p_contexto_version IS NULL),
    CHECK(p_contrato__presente IN (0,1)),
    CHECK(p_contrato__presente=1 OR p_contrato IS NULL),
    CHECK(p_gobierno_cabecera_cod_usuario_creacion__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_cod_usuario_creacion__presente=1 OR p_gobierno_cabecera_cod_usuario_creacion IS NULL),
    CHECK(p_gobierno_cabecera_cod_version_anterior__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_cod_version_anterior__presente=1 OR p_gobierno_cabecera_cod_version_anterior IS NULL),
    CHECK(p_gobierno_cabecera_cod_version_modelo__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_cod_version_modelo__presente=1 OR p_gobierno_cabecera_cod_version_modelo IS NULL),
    CHECK(p_gobierno_cabecera_clave_version__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_clave_version__presente=1 OR p_gobierno_cabecera_clave_version IS NULL),
    CHECK(p_gobierno_cabecera_descripcion__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_descripcion__presente=1 OR p_gobierno_cabecera_descripcion IS NULL),
    CHECK(p_gobierno_cabecera_estado__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_estado__presente=1 OR p_gobierno_cabecera_estado IS NULL),
    CHECK(p_gobierno_cabecera_fecha_creacion__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_fecha_creacion__presente=1 OR p_gobierno_cabecera_fecha_creacion IS NULL),
    CHECK(p_gobierno_cabecera_fecha_retiro__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_fecha_retiro__presente=1 OR p_gobierno_cabecera_fecha_retiro IS NULL),
    CHECK(p_gobierno_cabecera_fecha_vigencia__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_fecha_vigencia__presente=1 OR p_gobierno_cabecera_fecha_vigencia IS NULL),
    CHECK(p_gobierno_cabecera_motivo_cambio__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_motivo_cambio__presente=1 OR p_gobierno_cabecera_motivo_cambio IS NULL),
    CHECK(p_gobierno_cabecera_nombre__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_nombre__presente=1 OR p_gobierno_cabecera_nombre IS NULL),
    CHECK(p_gobierno_cabecera_observacion__presente IN (0,1)),
    CHECK(p_gobierno_cabecera_observacion__presente=1 OR p_gobierno_cabecera_observacion IS NULL),
    CHECK(p_gobierno_k_contratos_relaciones__estado='PRESENTE' OR p_gobierno_k_contratos_relaciones__cantidad=0),
    CHECK(p_gobierno_k_solo_pruebas_tecnicas__presente IN (0,1)),
    CHECK(p_gobierno_k_solo_pruebas_tecnicas__presente=1 OR p_gobierno_k_solo_pruebas_tecnicas IS NULL),
    CHECK(p_gobierno_k_solo_pruebas_tecnicas IS NULL OR p_gobierno_k_solo_pruebas_tecnicas IN (0,1)),
    CHECK(p_gobierno_k_tablas_condiciones_regla_experta__estado='PRESENTE' OR p_gobierno_k_tablas_condiciones_regla_experta__cantidad=0),
    CHECK(p_gobierno_k_tablas_consecuencias_regla_experta__estado='PRESENTE' OR p_gobierno_k_tablas_consecuencias_regla_experta__cantidad=0),
    CHECK(p_gobierno_k_tablas_criterios_dominios_r__estado='PRESENTE' OR p_gobierno_k_tablas_criterios_dominios_r__cantidad=0),
    CHECK(p_gobierno_k_tablas_dominios_valores_expertos__estado='PRESENTE' OR p_gobierno_k_tablas_dominios_valores_expertos__cantidad=0),
    CHECK(p_gobierno_k_tablas_nodos_semanticos__estado='PRESENTE' OR p_gobierno_k_tablas_nodos_semanticos__cantidad=0),
    CHECK(p_gobierno_k_tablas_reglas_expertas__estado='PRESENTE' OR p_gobierno_k_tablas_reglas_expertas__cantidad=0),
    CHECK(p_gobierno_k_tablas_relaciones_semanticas__estado='PRESENTE' OR p_gobierno_k_tablas_relaciones_semanticas__cantidad=0),
    CHECK(p_gobierno_k_tablas_valores_semanticos__estado='PRESENTE' OR p_gobierno_k_tablas_valores_semanticos__cantidad=0),
    CHECK(p_gobierno_k_tablas_variables_expertas__estado='PRESENTE' OR p_gobierno_k_tablas_variables_expertas__cantidad=0),
    CHECK(p_gobierno_k_tipo_regla_r__presente IN (0,1)),
    CHECK(p_gobierno_k_tipo_regla_r__presente=1 OR p_gobierno_k_tipo_regla_r IS NULL),
    CHECK(p_gobierno_k_version__presente IN (0,1)),
    CHECK(p_gobierno_k_version__presente=1 OR p_gobierno_k_version IS NULL),
    CHECK(p_limites_persistido__presente IN (0,1)),
    CHECK(p_limites_persistido__presente=1 OR p_limites_persistido IS NULL),
    CHECK(p_limites_persistido IS NULL OR p_limites_persistido IN (0,1)),
    CHECK(p_limites_reproduce_adquisicion_sql__presente IN (0,1)),
    CHECK(p_limites_reproduce_adquisicion_sql__presente=1 OR p_limites_reproduce_adquisicion_sql IS NULL),
    CHECK(p_limites_reproduce_adquisicion_sql IS NULL OR p_limites_reproduce_adquisicion_sql IN (0,1)),
    CHECK(p_limites_revalida_bytes_documentales__presente IN (0,1)),
    CHECK(p_limites_revalida_bytes_documentales__presente=1 OR p_limites_revalida_bytes_documentales IS NULL),
    CHECK(p_limites_revalida_bytes_documentales IS NULL OR p_limites_revalida_bytes_documentales IN (0,1)),
    CHECK(p_limites_revalida_permisos_historicos__presente IN (0,1)),
    CHECK(p_limites_revalida_permisos_historicos__presente=1 OR p_limites_revalida_permisos_historicos IS NULL),
    CHECK(p_limites_revalida_permisos_historicos IS NULL OR p_limites_revalida_permisos_historicos IN (0,1)),
    CHECK(p_membresias_soportes__estado='PRESENTE' OR p_membresias_soportes__cantidad=0),
    CHECK(p_procedencia_valoraciones__estado='PRESENTE' OR p_procedencia_valoraciones__cantidad=0),
    CHECK(p_s_contrato__presente IN (0,1)),
    CHECK(p_s_contrato__presente=1 OR p_s_contrato IS NULL),
    CHECK(p_s_dominios__estado='PRESENTE' OR p_s_dominios__cantidad=0),
    CHECK(p_s_estado__presente IN (0,1)),
    CHECK(p_s_estado__presente=1 OR p_s_estado IS NULL),
    CHECK(p_s_evaluacion__presente IN (0,1)),
    CHECK(p_s_evaluacion__presente=1 OR p_s_evaluacion IS NULL),
    CHECK(p_s_fecha_corte__presente IN (0,1)),
    CHECK(p_s_fecha_corte__presente=1 OR p_s_fecha_corte IS NULL),
    CHECK(p_s_gobierno_bloqueada_por__estado='PRESENTE' OR p_s_gobierno_bloqueada_por__cantidad=0),
    CHECK(p_s_gobierno_clave_version__presente IN (0,1)),
    CHECK(p_s_gobierno_clave_version__presente=1 OR p_s_gobierno_clave_version IS NULL),
    CHECK(p_s_gobierno_disponible__presente IN (0,1)),
    CHECK(p_s_gobierno_disponible__presente=1 OR p_s_gobierno_disponible IS NULL),
    CHECK(p_s_gobierno_disponible IS NULL OR p_s_gobierno_disponible IN (0,1)),
    CHECK(p_s_gobierno_estados_derivados__estado='PRESENTE' OR p_s_gobierno_estados_derivados__cantidad=0),
    CHECK(p_s_gobierno_origen__presente IN (0,1)),
    CHECK(p_s_gobierno_origen__presente=1 OR p_s_gobierno_origen IS NULL),
    CHECK(p_s_gobierno_patron__presente IN (0,1)),
    CHECK(p_s_gobierno_patron__presente=1 OR p_s_gobierno_patron IS NULL),
    CHECK(p_s_gobierno_persistido__presente IN (0,1)),
    CHECK(p_s_gobierno_persistido__presente=1 OR p_s_gobierno_persistido IS NULL),
    CHECK(p_s_gobierno_persistido IS NULL OR p_s_gobierno_persistido IN (0,1)),
    CHECK(p_s_gobierno_reglas__estado='PRESENTE' OR p_s_gobierno_reglas__cantidad=0),
    CHECK(p_s_gobierno_version__presente IN (0,1)),
    CHECK(p_s_gobierno_version__presente=1 OR p_s_gobierno_version IS NULL),
    CHECK(p_s_impedimentos__estado='PRESENTE' OR p_s_impedimentos__cantidad=0),
    CHECK(p_s_patron__presente IN (0,1)),
    CHECK(p_s_patron__presente=1 OR p_s_patron IS NULL),
    CHECK(p_s_persistido__presente IN (0,1)),
    CHECK(p_s_persistido__presente=1 OR p_s_persistido IS NULL),
    CHECK(p_s_persistido IS NULL OR p_s_persistido IN (0,1)),
    CHECK(p_s_requisitos_c01_estado__presente IN (0,1)),
    CHECK(p_s_requisitos_c01_estado__presente=1 OR p_s_requisitos_c01_estado IS NULL),
    CHECK(p_s_requisitos_c01_indices_valoraciones__estado='PRESENTE' OR p_s_requisitos_c01_indices_valoraciones__cantidad=0),
    CHECK(p_s_requisitos_c01_motivos__estado='PRESENTE' OR p_s_requisitos_c01_motivos__cantidad=0),
    CHECK(p_s_requisitos_c02_derivado_de__estado='PRESENTE' OR p_s_requisitos_c02_derivado_de__cantidad=0),
    CHECK(p_s_requisitos_c02_estado__presente IN (0,1)),
    CHECK(p_s_requisitos_c02_estado__presente=1 OR p_s_requisitos_c02_estado IS NULL),
    CHECK(p_s_requisitos_c02_motivos__estado='PRESENTE' OR p_s_requisitos_c02_motivos__cantidad=0),
    CHECK(p_s_requisitos_c03_estado__presente IN (0,1)),
    CHECK(p_s_requisitos_c03_estado__presente=1 OR p_s_requisitos_c03_estado IS NULL),
    CHECK(p_s_requisitos_c03_indices_valoraciones__estado='PRESENTE' OR p_s_requisitos_c03_indices_valoraciones__cantidad=0),
    CHECK(p_s_requisitos_c03_motivos__estado='PRESENTE' OR p_s_requisitos_c03_motivos__cantidad=0),
    CHECK(p_s_requisitos_c04_estado__presente IN (0,1)),
    CHECK(p_s_requisitos_c04_estado__presente=1 OR p_s_requisitos_c04_estado IS NULL),
    CHECK(p_s_requisitos_c04_indices_valoraciones__estado='PRESENTE' OR p_s_requisitos_c04_indices_valoraciones__cantidad=0),
    CHECK(p_s_requisitos_c04_motivos__estado='PRESENTE' OR p_s_requisitos_c04_motivos__cantidad=0),
    CHECK(p_s_requisitos_c05_estado__presente IN (0,1)),
    CHECK(p_s_requisitos_c05_estado__presente=1 OR p_s_requisitos_c05_estado IS NULL),
    CHECK(p_s_requisitos_c05_indices_valoraciones__estado='PRESENTE' OR p_s_requisitos_c05_indices_valoraciones__cantidad=0),
    CHECK(p_s_requisitos_c05_motivos__estado='PRESENTE' OR p_s_requisitos_c05_motivos__cantidad=0),
    CHECK(p_s_residente__presente IN (0,1)),
    CHECK(p_s_residente__presente=1 OR p_s_residente IS NULL),
    CHECK(p_s_revision_profesional__presente IN (0,1)),
    CHECK(p_s_revision_profesional__presente=1 OR p_s_revision_profesional IS NULL),
    CHECK(p_s_valoraciones_excluidas__estado='PRESENTE' OR p_s_valoraciones_excluidas__cantidad=0),
    CHECK(p_s_version__presente IN (0,1)),
    CHECK(p_s_version__presente=1 OR p_s_version IS NULL),
    CHECK(p_valoraciones__estado='PRESENTE' OR p_valoraciones__cantidad=0),
    CHECK(petersen_huella__presente IN (0,1)),
    CHECK(petersen_huella__presente=1 OR petersen_huella IS NULL),
    CHECK(release_archivos__estado='PRESENTE' OR release_archivos__cantidad=0),
    CHECK(release_contrato__presente IN (0,1)),
    CHECK(release_contrato__presente=1 OR release_contrato IS NULL),
    CHECK(release_huella__presente IN (0,1)),
    CHECK(release_huella__presente=1 OR release_huella IS NULL),
    CHECK(release_incluye_datos_clinicos__presente IN (0,1)),
    CHECK(release_incluye_datos_clinicos__presente=1 OR release_incluye_datos_clinicos IS NULL),
    CHECK(release_incluye_datos_clinicos IS NULL OR release_incluye_datos_clinicos IN (0,1)),
    CHECK(release_php_requerido__presente IN (0,1)),
    CHECK(release_php_requerido__presente=1 OR release_php_requerido IS NULL),
    CHECK(release_revision_base__presente IN (0,1)),
    CHECK(release_revision_base__presente=1 OR release_revision_base IS NULL),
    CHECK(release_retenido__presente IN (0,1)),
    CHECK(release_retenido__presente=1 OR release_retenido IS NULL),
    CHECK(release_retenido IS NULL OR release_retenido IN (0,1)),
    CHECK(dto__estado='PRESENTE' OR (contrato__presente=0 AND ejecutor__estado='AUSENTE' AND instrumental__estado='AUSENTE' AND persistido__presente=0 AND petersen__estado='AUSENTE' AND release__estado='AUSENTE' AND release_retenido__presente=0)),
    CHECK(ejecutor__estado='PRESENTE' OR (ejecutor_cantidad_archivos__presente=0 AND ejecutor_huella_src__presente=0)),
    CHECK(instrumental__estado='PRESENTE' OR (i__estado='AUSENTE' AND instrumental_huella__presente=0)),
    CHECK(i__estado='PRESENTE' OR (i_cabecera__estado='AUSENTE' AND i_capturas__estado='AUSENTE' AND i_k__estado='AUSENTE' AND i_contexto__estado='AUSENTE' AND i_contrato__presente=0 AND i_fuente_conceptual__estado='AUSENTE' AND i_s__estado='AUSENTE' AND i_vinculos__estado='AUSENTE')),
    CHECK(i_cabecera__estado='PRESENTE' OR (i_cabecera_cod_version_modelo__presente=0)),
    CHECK(i_capturas__estado='PRESENTE' OR (i_capturas_capturas__estado='AUSENTE' AND i_capturas_disponible__presente=0 AND i_capturas_fecha_corte__presente=0 AND i_capturas_persistido__presente=0)),
    CHECK(i_k__estado='PRESENTE' OR (i_k_contratos_extraccion__estado='AUSENTE' AND i_k_contratos_relaciones__estado='AUSENTE' AND i_k_solo_pruebas_tecnicas__presente=0 AND i_k_tablas__estado='AUSENTE' AND i_k_tipo_regla_r__presente=0 AND i_k_version__presente=0)),
    CHECK(i_k_contratos_extraccion__estado='PRESENTE' OR (i_k_contratos_extraccion_respuesta_institucional__presente=0)),
    CHECK(i_k_tablas__estado='PRESENTE' OR (i_k_tablas_condiciones_regla_experta__estado='AUSENTE' AND i_k_tablas_consecuencias_regla_experta__estado='AUSENTE' AND i_k_tablas_criterios_dominios_r__estado='AUSENTE' AND i_k_tablas_dominios_valores_expertos__estado='AUSENTE' AND i_k_tablas_fuentes_datos_expertas__estado='AUSENTE' AND i_k_tablas_mapeos_valores_fuente__estado='AUSENTE' AND i_k_tablas_mapeos_variables_fuente__estado='AUSENTE' AND i_k_tablas_nodos_semanticos__estado='AUSENTE' AND i_k_tablas_reglas_expertas__estado='AUSENTE' AND i_k_tablas_relaciones_semanticas__estado='AUSENTE' AND i_k_tablas_valores_semanticos__estado='AUSENTE' AND i_k_tablas_variables_expertas__estado='AUSENTE')),
    CHECK(i_contexto__estado='PRESENTE' OR (i_contexto_evaluacion__presente=0 AND i_contexto_fecha_corte__estado='AUSENTE' AND i_contexto_residente__presente=0 AND i_contexto_usuario_ejecucion__presente=0 AND i_contexto_version__presente=0)),
    CHECK(i_contexto_fecha_corte__estado='PRESENTE' OR (i_contexto_fecha_corte_corte_offset__presente=0 AND i_contexto_fecha_corte_corte_utc__presente=0 AND i_contexto_fecha_corte_corte_zona__presente=0)),
    CHECK(i_fuente_conceptual__estado='PRESENTE' OR (i_fuente_conceptual_documento__presente=0 AND i_fuente_conceptual_documento_maestro_sha256__presente=0 AND i_fuente_conceptual_documento_url__presente=0 AND i_fuente_conceptual_fecha_consulta__presente=0 AND i_fuente_conceptual_limite__presente=0 AND i_fuente_conceptual_matrices_sha256__presente=0 AND i_fuente_conceptual_matrices_url__presente=0 AND i_fuente_conceptual_nodos__estado='AUSENTE' AND i_fuente_conceptual_relaciones__estado='AUSENTE' AND i_fue_con_rel_doc_no_mat__presente=0)),
    CHECK(i_s__estado='PRESENTE' OR (i_s_fuentes_excluidas__estado='AUSENTE' AND i_s_perfil__estado='AUSENTE' AND i_s_r__estado='AUSENTE')),
    CHECK(i_s_perfil__estado='PRESENTE' OR (i_s_perfil_adquisicion__estado='AUSENTE' AND i_s_perfil_contrato__presente=0 AND i_s_perfil_diagnostico__presente=0 AND i_s_perfil_dominios__estado='AUSENTE' AND i_s_perfil_metricas_60_o_mas__estado='AUSENTE' AND i_s_perfil_persistido__presente=0 AND i_s_perfil_red__estado='AUSENTE' AND i_s_perfil_revision_profesional__presente=0)),
    CHECK(i_s_perfil_adquisicion__estado='PRESENTE' OR (i_s_perfil_adquisicion_aplicaciones__estado='AUSENTE' AND i_s_per_adq_aut_int_cli__presente=0 AND i_s_perfil_adquisicion_contrato__presente=0 AND i_s_perfil_adquisicion_metricas_entradas_60_o_mas__estado='AUSENTE')),
    CHECK(i_s_perfil_adquisicion_metricas_entradas_60_o_mas__estado='PRESENTE' OR (i_s_per_adq_met_ent_60_o_mas_apl_obs_sin_nor__presente=0 AND i_s_per_adq_met_ent_60_o_mas_apl_sin_ext__presente=0 AND i_s_per_adq_met_ent_60_o_mas_den__presente=0 AND i_s_per_adq_met_ent_60_o_mas_ent_con_dat_tip__presente=0 AND i_s_per_adq_met_ent_60_o_mas_ent_fal_o_inv__presente=0 AND i_s_per_adq_met_ent_60_o_mas_ent_req__presente=0 AND i_s_per_adq_met_ent_60_o_mas_lim__presente=0 AND i_s_per_adq_met_ent_60_o_mas_por_com__presente=0)),
    CHECK(i_s_perfil_metricas_60_o_mas__estado='PRESENTE' OR (i_s_per_met_60_o_mas_apl_60_o_mas__presente=0 AND i_s_per_met_60_o_mas_apl_eda_des__presente=0 AND i_s_per_met_60_o_mas_apl_exa__presente=0 AND i_s_per_met_60_o_mas_apl_men_60__presente=0 AND i_s_per_met_60_o_mas_com_con_com__presente=0 AND i_s_per_met_60_o_mas_com_del_per__presente=0 AND i_s_perfil_metricas_60_o_mas_denominador__presente=0 AND i_s_per_met_60_o_mas_por_cob_com__presente=0 AND i_s_perfil_metricas_60_o_mas_tipo_metrica__presente=0)),
    CHECK(i_s_perfil_red__estado='PRESENTE' OR (i_s_perfil_red_contratos__estado='AUSENTE' AND i_s_perfil_red_documento_sha256__presente=0 AND i_s_perfil_red_dominios__estado='AUSENTE' AND i_s_perfil_red_ejecutable_clinico__presente=0 AND i_s_perfil_red_matrices_sha256__presente=0 AND i_s_perfil_red_nodos__estado='AUSENTE' AND i_s_perfil_red_relaciones__estado='AUSENTE' AND i_s_perfil_red_version__presente=0)),
    CHECK(i_s_r__estado='PRESENTE' OR (i_s_r_alcance__presente=0 AND i_s_r_clasificacion_dcl__presente=0 AND i_s_r_contrato_institucional__presente=0 AND i_s_r_evaluacion__presente=0 AND i_s_r_fecha_corte__presente=0 AND i_s_r_observaciones_por_componente__estado='AUSENTE' AND i_s_r_perfil__estado='AUSENTE' AND i_s_r_persistido__presente=0 AND i_s_r_red__estado='AUSENTE' AND i_s_r_residente__presente=0 AND i_s_r_version__presente=0)),
    CHECK(i_s_r_red__estado='PRESENTE' OR (i_s_r_red_componentes__estado='AUSENTE' AND i_s_r_red_dominios__estado='AUSENTE' AND i_s_r_red_rutas_reglas__estado='AUSENTE' AND i_s_r_red_version__presente=0)),
    CHECK(petersen__estado='PRESENTE' OR (p__estado='AUSENTE' AND petersen_huella__presente=0)),
    CHECK(p__estado='PRESENTE' OR (p_adquisicion__estado='AUSENTE' AND p_constancias_documentales__estado='AUSENTE' AND p_contexto__estado='AUSENTE' AND p_contrato__presente=0 AND p_gobierno__estado='AUSENTE' AND p_limites__estado='AUSENTE' AND p_membresias_soportes__estado='AUSENTE' AND p_procedencia_valoraciones__estado='AUSENTE' AND p_s__estado='AUSENTE' AND p_valoraciones__estado='AUSENTE')),
    CHECK(p_adquisicion__estado='PRESENTE' OR (p_adquisicion_conservacion_completa__presente=0 AND p_adquisicion_exclusiones__estado='AUSENTE' AND p_adquisicion_fuentes_originales_mutables__presente=0 AND p_adquisicion_valoraciones_al_corte__presente=0)),
    CHECK(p_contexto__estado='PRESENTE' OR (p_contexto_evaluacion__presente=0 AND p_contexto_fecha_corte__estado='AUSENTE' AND p_contexto_residente__presente=0 AND p_contexto_usuario_ejecucion__presente=0 AND p_contexto_version__presente=0)),
    CHECK(p_contexto_fecha_corte__estado='PRESENTE' OR (p_contexto_fecha_corte_corte_offset__presente=0 AND p_contexto_fecha_corte_corte_utc__presente=0 AND p_contexto_fecha_corte_corte_zona__presente=0)),
    CHECK(p_gobierno__estado='PRESENTE' OR (p_gobierno_cabecera__estado='AUSENTE' AND p_gobierno_k__estado='AUSENTE')),
    CHECK(p_gobierno_cabecera__estado='PRESENTE' OR (p_gobierno_cabecera_cod_usuario_creacion__presente=0 AND p_gobierno_cabecera_cod_version_anterior__presente=0 AND p_gobierno_cabecera_cod_version_modelo__presente=0 AND p_gobierno_cabecera_clave_version__presente=0 AND p_gobierno_cabecera_descripcion__presente=0 AND p_gobierno_cabecera_estado__presente=0 AND p_gobierno_cabecera_fecha_creacion__presente=0 AND p_gobierno_cabecera_fecha_retiro__presente=0 AND p_gobierno_cabecera_fecha_vigencia__presente=0 AND p_gobierno_cabecera_motivo_cambio__presente=0 AND p_gobierno_cabecera_nombre__presente=0 AND p_gobierno_cabecera_observacion__presente=0)),
    CHECK(p_gobierno_k__estado='PRESENTE' OR (p_gobierno_k_contratos_extraccion__estado='AUSENTE' AND p_gobierno_k_contratos_relaciones__estado='AUSENTE' AND p_gobierno_k_solo_pruebas_tecnicas__presente=0 AND p_gobierno_k_tablas__estado='AUSENTE' AND p_gobierno_k_tipo_regla_r__presente=0 AND p_gobierno_k_version__presente=0)),
    CHECK(p_gobierno_k_tablas__estado='PRESENTE' OR (p_gobierno_k_tablas_condiciones_regla_experta__estado='AUSENTE' AND p_gobierno_k_tablas_consecuencias_regla_experta__estado='AUSENTE' AND p_gobierno_k_tablas_criterios_dominios_r__estado='AUSENTE' AND p_gobierno_k_tablas_dominios_valores_expertos__estado='AUSENTE' AND p_gobierno_k_tablas_fuentes_datos_expertas__estado='AUSENTE' AND p_gobierno_k_tablas_mapeos_valores_fuente__estado='AUSENTE' AND p_gobierno_k_tablas_mapeos_variables_fuente__estado='AUSENTE' AND p_gobierno_k_tablas_nodos_semanticos__estado='AUSENTE' AND p_gobierno_k_tablas_reglas_expertas__estado='AUSENTE' AND p_gobierno_k_tablas_relaciones_semanticas__estado='AUSENTE' AND p_gobierno_k_tablas_valores_semanticos__estado='AUSENTE' AND p_gobierno_k_tablas_variables_expertas__estado='AUSENTE')),
    CHECK(p_limites__estado='PRESENTE' OR (p_limites_persistido__presente=0 AND p_limites_reproduce_adquisicion_sql__presente=0 AND p_limites_revalida_bytes_documentales__presente=0 AND p_limites_revalida_permisos_historicos__presente=0)),
    CHECK(p_s__estado='PRESENTE' OR (p_s_contrato__presente=0 AND p_s_dominios__estado='AUSENTE' AND p_s_estado__presente=0 AND p_s_evaluacion__presente=0 AND p_s_fecha_corte__presente=0 AND p_s_gobierno__estado='AUSENTE' AND p_s_impedimentos__estado='AUSENTE' AND p_s_patron__presente=0 AND p_s_persistido__presente=0 AND p_s_requisitos__estado='AUSENTE' AND p_s_residente__presente=0 AND p_s_revision_profesional__presente=0 AND p_s_valoraciones_excluidas__estado='AUSENTE' AND p_s_version__presente=0)),
    CHECK(p_s_gobierno__estado='PRESENTE' OR (p_s_gobierno_bloqueada_por__estado='AUSENTE' AND p_s_gobierno_clave_version__presente=0 AND p_s_gobierno_disponible__presente=0 AND p_s_gobierno_estados_derivados__estado='AUSENTE' AND p_s_gobierno_origen__presente=0 AND p_s_gobierno_patron__presente=0 AND p_s_gobierno_persistido__presente=0 AND p_s_gobierno_reglas__estado='AUSENTE' AND p_s_gobierno_version__presente=0)),
    CHECK(p_s_requisitos__estado='PRESENTE' OR (p_s_requisitos_c01__estado='AUSENTE' AND p_s_requisitos_c02__estado='AUSENTE' AND p_s_requisitos_c03__estado='AUSENTE' AND p_s_requisitos_c04__estado='AUSENTE' AND p_s_requisitos_c05__estado='AUSENTE')),
    CHECK(p_s_requisitos_c01__estado='PRESENTE' OR (p_s_requisitos_c01_estado__presente=0 AND p_s_requisitos_c01_indices_valoraciones__estado='AUSENTE' AND p_s_requisitos_c01_motivos__estado='AUSENTE')),
    CHECK(p_s_requisitos_c02__estado='PRESENTE' OR (p_s_requisitos_c02_derivado_de__estado='AUSENTE' AND p_s_requisitos_c02_estado__presente=0 AND p_s_requisitos_c02_indices_valoraciones__estado='AUSENTE' AND p_s_requisitos_c02_motivos__estado='AUSENTE')),
    CHECK(p_s_requisitos_c03__estado='PRESENTE' OR (p_s_requisitos_c03_estado__presente=0 AND p_s_requisitos_c03_indices_valoraciones__estado='AUSENTE' AND p_s_requisitos_c03_motivos__estado='AUSENTE')),
    CHECK(p_s_requisitos_c04__estado='PRESENTE' OR (p_s_requisitos_c04_estado__presente=0 AND p_s_requisitos_c04_indices_valoraciones__estado='AUSENTE' AND p_s_requisitos_c04_motivos__estado='AUSENTE')),
    CHECK(p_s_requisitos_c05__estado='PRESENTE' OR (p_s_requisitos_c05_estado__presente=0 AND p_s_requisitos_c05_indices_valoraciones__estado='AUSENTE' AND p_s_requisitos_c05_motivos__estado='AUSENTE')),
    CHECK(release__estado='PRESENTE' OR (release_archivos__estado='AUSENTE' AND release_contrato__presente=0 AND release_huella__presente=0 AND release_incluye_datos_clinicos__presente=0 AND release_php_requerido__presente=0 AND release_revision_base__presente=0))
);

CREATE TABLE ret_contenido_capturas_capturas_c002 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicacion_actual__estado VARCHAR(8) NOT NULL CHECK(aplicacion_actual__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicacion_actual_clasificacion__presente INTEGER NOT NULL,
    aplicacion_actual_clasificacion TEXT,
    aplicacion_actual_cod_aplicacion__presente INTEGER NOT NULL,
    aplicacion_actual_cod_aplicacion TEXT,
    aplicacion_actual_cod_aplicacion_anterior__presente INTEGER NOT NULL,
    aplicacion_actual_cod_aplicacion_anterior TEXT,
    aplicacion_actual_cod_atencion__presente INTEGER NOT NULL,
    aplicacion_actual_cod_atencion TEXT,
    aplicacion_actual_cod_documento_clinico__presente INTEGER NOT NULL,
    aplicacion_actual_cod_documento_clinico TEXT,
    aplicacion_actual_cod_instrumento__presente INTEGER NOT NULL,
    aplicacion_actual_cod_instrumento TEXT,
    aplicacion_actual_cod_personal__presente INTEGER NOT NULL,
    aplicacion_actual_cod_personal TEXT,
    aplicacion_actual_cod_residente__presente INTEGER NOT NULL,
    aplicacion_actual_cod_residente TEXT,
    aplicacion_actual_cod_usuario_registro__presente INTEGER NOT NULL,
    aplicacion_actual_cod_usuario_registro TEXT,
    aplicacion_actual_estado__presente INTEGER NOT NULL,
    aplicacion_actual_estado TEXT,
    aplicacion_actual_fecha__presente INTEGER NOT NULL,
    aplicacion_actual_fecha TEXT,
    aplicacion_actual_interpretacion__presente INTEGER NOT NULL,
    aplicacion_actual_interpretacion TEXT,
    aplicacion_actual_localizador_fuente__presente INTEGER NOT NULL,
    aplicacion_actual_localizador_fuente TEXT,
    aplicacion_actual_observacion__presente INTEGER NOT NULL,
    aplicacion_actual_observacion TEXT,
    aplicacion_actual_puntaje_maximo__presente INTEGER NOT NULL,
    aplicacion_actual_puntaje_maximo TEXT,
    aplicacion_actual_puntaje_total__presente INTEGER NOT NULL,
    aplicacion_actual_puntaje_total TEXT,
    aplicacion_actual_registrado_en__presente INTEGER NOT NULL,
    aplicacion_actual_registrado_en TEXT,
    atencion_original__estado VARCHAR(8) NOT NULL CHECK(atencion_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    atencion_original_cod_atencion__presente INTEGER NOT NULL,
    atencion_original_cod_atencion TEXT,
    atencion_original_cod_residente__presente INTEGER NOT NULL,
    atencion_original_cod_residente TEXT,
    autor__presente INTEGER NOT NULL,
    autor TEXT,
    cod_aplicacion__presente INTEGER NOT NULL,
    cod_aplicacion TEXT,
    cod_instrumento__presente INTEGER NOT NULL,
    cod_instrumento TEXT,
    cod_personal__presente INTEGER NOT NULL,
    cod_personal TEXT,
    cod_residente__presente INTEGER NOT NULL,
    cod_residente TEXT,
    clave_instrumento__presente INTEGER NOT NULL,
    clave_instrumento TEXT,
    documento_original__estado VARCHAR(8) NOT NULL CHECK(documento_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    documento_original_cod_documento_clinico__presente INTEGER NOT NULL,
    documento_original_cod_documento_clinico TEXT,
    documento_original_cod_personal__presente INTEGER NOT NULL,
    documento_original_cod_personal TEXT,
    documento_original_cod_residente__presente INTEGER NOT NULL,
    documento_original_cod_residente TEXT,
    documento_original_estado__presente INTEGER NOT NULL,
    documento_original_estado TEXT,
    documento_original_fecha__presente INTEGER NOT NULL,
    documento_original_fecha TEXT,
    documento_original_hash_archivo__presente INTEGER NOT NULL,
    documento_original_hash_archivo TEXT,
    edad_en_aplicacion__presente INTEGER NOT NULL,
    edad_en_aplicacion BIGINT,
    estado_original__presente INTEGER NOT NULL,
    estado_original TEXT,
    extraccion__estado VARCHAR(8) NOT NULL CHECK(extraccion__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_aplicabilidad_geriatrica__estado VARCHAR(8) NOT NULL CHECK(extraccion_aplicabilidad_geriatrica__estado IN ('AUSENTE','NULL','PRESENTE')),
    ext_apl_ger_alc_eta_com__presente INTEGER NOT NULL,
    ext_apl_ger_alc_eta_com INTEGER,
    ext_apl_ger_aut_int_cli__presente INTEGER NOT NULL,
    ext_apl_ger_aut_int_cli INTEGER,
    ext_apl_ger_eda_en_apl__presente INTEGER NOT NULL,
    ext_apl_ger_eda_en_apl BIGINT,
    ext_apl_ger_esc_ori__presente INTEGER NOT NULL,
    ext_apl_ger_esc_ori BIGINT,
    extraccion_aplicabilidad_geriatrica_fuente__presente INTEGER NOT NULL,
    extraccion_aplicabilidad_geriatrica_fuente TEXT,
    ext_apl_ger_idi_dec__presente INTEGER NOT NULL,
    ext_apl_ger_idi_dec TEXT,
    extraccion_aplicabilidad_geriatrica_motivos__estado VARCHAR(8) NOT NULL CHECK(extraccion_aplicabilidad_geriatrica_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    ext_apl_ger_per_a_pob_obj__presente INTEGER NOT NULL,
    ext_apl_ger_per_a_pob_obj INTEGER,
    ext_apl_ger_pob_obj__presente INTEGER NOT NULL,
    ext_apl_ger_pob_obj TEXT,
    ext_apl_ger_ran_eda_ref__estado VARCHAR(8) NOT NULL CHECK(ext_apl_ger_ran_eda_ref__estado IN ('AUSENTE','NULL','PRESENTE')),
    ext_apl_ger_ran_eda_ref_max__presente INTEGER NOT NULL,
    ext_apl_ger_ran_eda_ref_max BIGINT,
    ext_apl_ger_ran_eda_ref_min__presente INTEGER NOT NULL,
    ext_apl_ger_ran_eda_ref_min BIGINT,
    ext_apl_ger_req_ver__estado VARCHAR(8) NOT NULL CHECK(ext_apl_ger_req_ver__estado IN ('AUSENTE','NULL','PRESENTE')),
    ext_apl_ger_req_ver__cantidad BIGINT NOT NULL CHECK(ext_apl_ger_req_ver__cantidad>=0),
    extraccion_captura__estado VARCHAR(8) NOT NULL CHECK(extraccion_captura__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_captura_aciertos_clave_semantica__presente INTEGER NOT NULL,
    extraccion_captura_aciertos_clave_semantica BIGINT,
    extraccion_captura_aciertos_espontaneos__presente INTEGER NOT NULL,
    extraccion_captura_aciertos_espontaneos BIGINT,
    extraccion_captura_administracion_estandarizada__presente INTEGER NOT NULL,
    extraccion_captura_administracion_estandarizada INTEGER,
    extraccion_captura_audicion_compatible__presente INTEGER NOT NULL,
    extraccion_captura_audicion_compatible INTEGER,
    extraccion_captura_condiciones_sensoriales__presente INTEGER NOT NULL,
    extraccion_captura_condiciones_sensoriales INTEGER,
    extraccion_captura_demora_minutos__presente INTEGER NOT NULL,
    extraccion_captura_demora_minutos REAL,
    extraccion_captura_demora_minutos__cero_negativo INTEGER NOT NULL DEFAULT 0,
    extraccion_captura_edad_anios__presente INTEGER NOT NULL,
    extraccion_captura_edad_anios BIGINT,
    extraccion_captura_edicion_protocolo__presente INTEGER NOT NULL,
    extraccion_captura_edicion_protocolo TEXT,
    extraccion_captura_elementos_revisados__presente INTEGER NOT NULL,
    extraccion_captura_elementos_revisados BIGINT,
    extraccion_captura_ensayo_1__presente INTEGER NOT NULL,
    extraccion_captura_ensayo_1 BIGINT,
    extraccion_captura_ensayo_2__presente INTEGER NOT NULL,
    extraccion_captura_ensayo_2 BIGINT,
    extraccion_captura_ensayo_3__presente INTEGER NOT NULL,
    extraccion_captura_ensayo_3 BIGINT,
    extraccion_captura_ensayos__estado VARCHAR(8) NOT NULL CHECK(extraccion_captura_ensayos__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_captura_ensayos__cantidad BIGINT NOT NULL CHECK(extraccion_captura_ensayos__cantidad>=0),
    extraccion_captura_errores_observados__presente INTEGER NOT NULL,
    extraccion_captura_errores_observados BIGINT,
    extraccion_captura_escolaridad_anios__presente INTEGER NOT NULL,
    extraccion_captura_escolaridad_anios BIGINT,
    extraccion_captura_espanol_nativo__presente INTEGER NOT NULL,
    extraccion_captura_espanol_nativo INTEGER,
    extraccion_captura_finalizada__presente INTEGER NOT NULL,
    extraccion_captura_finalizada INTEGER,
    extraccion_captura_forma__presente INTEGER NOT NULL,
    extraccion_captura_forma BIGINT,
    extraccion_captura_forma_l_aciertos__presente INTEGER NOT NULL,
    extraccion_captura_forma_l_aciertos BIGINT,
    extraccion_captura_forma_n_aciertos__presente INTEGER NOT NULL,
    extraccion_captura_forma_n_aciertos BIGINT,
    extraccion_captura_idioma__presente INTEGER NOT NULL,
    extraccion_captura_idioma TEXT,
    extraccion_captura_instrumento__presente INTEGER NOT NULL,
    extraccion_captura_instrumento TEXT,
    extraccion_captura_lectoescritura__presente INTEGER NOT NULL,
    extraccion_captura_lectoescritura INTEGER,
    extraccion_captura_nacimiento_bolivia__presente INTEGER NOT NULL,
    extraccion_captura_nacimiento_bolivia INTEGER,
    extraccion_captura_protocolo__presente INTEGER NOT NULL,
    extraccion_captura_protocolo TEXT,
    extraccion_captura_puntaje_copia__presente INTEGER NOT NULL,
    extraccion_captura_puntaje_copia REAL,
    extraccion_captura_puntaje_copia__cero_negativo INTEGER NOT NULL DEFAULT 0,
    extraccion_captura_recuerdo_diferido__presente INTEGER NOT NULL,
    extraccion_captura_recuerdo_diferido BIGINT,
    extraccion_captura_residencia_bolivia__presente INTEGER NOT NULL,
    extraccion_captura_residencia_bolivia INTEGER,
    extraccion_captura_segundos_parte_b__presente INTEGER NOT NULL,
    extraccion_captura_segundos_parte_b REAL,
    extraccion_captura_segundos_parte_b__cero_negativo INTEGER NOT NULL DEFAULT 0,
    extraccion_fuentes_entradas__estado VARCHAR(8) NOT NULL CHECK(extraccion_fuentes_entradas__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_fuentes_entradas__cantidad BIGINT NOT NULL CHECK(extraccion_fuentes_entradas__cantidad>=0),
    extraccion_identidad_declarada__estado VARCHAR(8) NOT NULL CHECK(extraccion_identidad_declarada__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_identidad_declarada_edicion_protocolo__presente INTEGER NOT NULL,
    extraccion_identidad_declarada_edicion_protocolo TEXT,
    extraccion_identidad_declarada_forma__presente INTEGER NOT NULL,
    extraccion_identidad_declarada_forma BIGINT,
    extraccion_identidad_declarada_idioma__presente INTEGER NOT NULL,
    extraccion_identidad_declarada_idioma TEXT,
    extraccion_identidad_declarada_instrumento__presente INTEGER NOT NULL,
    extraccion_identidad_declarada_instrumento TEXT,
    extraccion_identidad_requerida_metodo__estado VARCHAR(8) NOT NULL CHECK(extraccion_identidad_requerida_metodo__estado IN ('AUSENTE','NULL','PRESENTE')),
    ext_ide_req_met_edi_pro__presente INTEGER NOT NULL,
    ext_ide_req_met_edi_pro TEXT,
    extraccion_identidad_requerida_metodo_forma__presente INTEGER NOT NULL,
    extraccion_identidad_requerida_metodo_forma BIGINT,
    extraccion_identidad_requerida_metodo_idioma__presente INTEGER NOT NULL,
    extraccion_identidad_requerida_metodo_idioma TEXT,
    extraccion_identidad_requerida_metodo_instrumento__presente INTEGER NOT NULL,
    extraccion_identidad_requerida_metodo_instrumento TEXT,
    extraccion_metodo__presente INTEGER NOT NULL,
    extraccion_metodo TEXT,
    extraccion_motivos_captura__estado VARCHAR(8) NOT NULL CHECK(extraccion_motivos_captura__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_normalizacion__estado VARCHAR(8) NOT NULL CHECK(extraccion_normalizacion__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_normalizacion_bruto__estado VARCHAR(8) NOT NULL CHECK(extraccion_normalizacion_bruto__estado IN ('AUSENTE','NULL','PRESENTE')),
    ext_nor_bru_aci_cla_sem__presente INTEGER NOT NULL,
    ext_nor_bru_aci_cla_sem BIGINT,
    ext_nor_bru_aci_esp__presente INTEGER NOT NULL,
    ext_nor_bru_aci_esp BIGINT,
    ext_nor_bru_adm_est__presente INTEGER NOT NULL,
    ext_nor_bru_adm_est INTEGER,
    ext_nor_bru_aud_com__presente INTEGER NOT NULL,
    ext_nor_bru_aud_com INTEGER,
    ext_nor_bru_con_sen__presente INTEGER NOT NULL,
    ext_nor_bru_con_sen INTEGER,
    extraccion_normalizacion_bruto_demora_minutos__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_demora_minutos REAL,
    extraccion_normalizacion_bruto_demora_minutos__cero_negativo INTEGER NOT NULL DEFAULT 0,
    ext_nor_bru_ele_rev__presente INTEGER NOT NULL,
    ext_nor_bru_ele_rev BIGINT,
    extraccion_normalizacion_bruto_ensayos__estado VARCHAR(8) NOT NULL CHECK(extraccion_normalizacion_bruto_ensayos__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_normalizacion_bruto_ensayos__cantidad BIGINT NOT NULL CHECK(extraccion_normalizacion_bruto_ensayos__cantidad>=0),
    extraccion_normalizacion_bruto_errores_observados__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_errores_observados BIGINT,
    extraccion_normalizacion_bruto_escolaridad_anios__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_escolaridad_anios BIGINT,
    extraccion_normalizacion_bruto_espanol_nativo__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_espanol_nativo INTEGER,
    extraccion_normalizacion_bruto_finalizada__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_finalizada INTEGER,
    extraccion_normalizacion_bruto_forma_l_aciertos__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_forma_l_aciertos BIGINT,
    extraccion_normalizacion_bruto_forma_n_aciertos__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_forma_n_aciertos BIGINT,
    extraccion_normalizacion_bruto_lectoescritura__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_lectoescritura INTEGER,
    extraccion_normalizacion_bruto_nacimiento_bolivia__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_nacimiento_bolivia INTEGER,
    extraccion_normalizacion_bruto_puntaje_copia__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_puntaje_copia REAL,
    extraccion_normalizacion_bruto_puntaje_copia__cero_negativo INTEGER NOT NULL DEFAULT 0,
    extraccion_normalizacion_bruto_recuerdo_diferido__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_recuerdo_diferido BIGINT,
    extraccion_normalizacion_bruto_residencia_bolivia__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_residencia_bolivia INTEGER,
    extraccion_normalizacion_bruto_segundos_parte_b__presente INTEGER NOT NULL,
    extraccion_normalizacion_bruto_segundos_parte_b REAL,
    extraccion_normalizacion_bruto_segundos_parte_b__cero_negativo INTEGER NOT NULL DEFAULT 0,
    extraccion_normalizacion_componentes__estado VARCHAR(8) NOT NULL CHECK(extraccion_normalizacion_componentes__estado IN ('AUSENTE','NULL','PRESENTE')),
    extraccion_normalizacion_componentes__cantidad BIGINT NOT NULL CHECK(extraccion_normalizacion_componentes__cantidad>=0),
    extraccion_normalizacion_contexto_aplicacion__estado VARCHAR(8) NOT NULL CHECK(extraccion_normalizacion_contexto_aplicacion__estado IN ('AUSENTE','NULL','PRESENTE')),
    ext_nor_con_apl_aci_cla_sem__presente INTEGER NOT NULL,
    ext_nor_con_apl_aci_cla_sem BIGINT,
    ext_nor_con_apl_aci_esp__presente INTEGER NOT NULL,
    ext_nor_con_apl_aci_esp BIGINT,
    ext_nor_con_apl_adm_est__presente INTEGER NOT NULL,
    ext_nor_con_apl_adm_est INTEGER,
    ext_nor_con_apl_aud_com__presente INTEGER NOT NULL,
    ext_nor_con_apl_aud_com INTEGER,
    ext_nor_con_apl_con_sen__presente INTEGER NOT NULL,
    ext_nor_con_apl_con_sen INTEGER,
    ext_nor_con_apl_eda_ani__presente INTEGER NOT NULL,
    ext_nor_con_apl_eda_ani BIGINT,
    ext_nor_con_apl_edi_pro__presente INTEGER NOT NULL,
    ext_nor_con_apl_edi_pro TEXT,
    ext_nor_con_apl_ele_rev__presente INTEGER NOT NULL,
    ext_nor_con_apl_ele_rev BIGINT,
    ext_nor_con_apl_err_obs__presente INTEGER NOT NULL,
    ext_nor_con_apl_err_obs BIGINT,
    ext_nor_con_apl_esc_ani__presente INTEGER NOT NULL,
    ext_nor_con_apl_esc_ani BIGINT,
    ext_nor_con_apl_esp_nat__presente INTEGER NOT NULL,
    ext_nor_con_apl_esp_nat INTEGER,
    ext_nor_con_apl_fin__presente INTEGER NOT NULL,
    ext_nor_con_apl_fin INTEGER,
    ext_nor_con_apl_for__presente INTEGER NOT NULL,
    ext_nor_con_apl_for BIGINT,
    ext_nor_con_apl_for_l_aci__presente INTEGER NOT NULL,
    ext_nor_con_apl_for_l_aci BIGINT,
    ext_nor_con_apl_for_n_aci__presente INTEGER NOT NULL,
    ext_nor_con_apl_for_n_aci BIGINT,
    ext_nor_con_apl_idi__presente INTEGER NOT NULL,
    ext_nor_con_apl_idi TEXT,
    ext_nor_con_apl_ins__presente INTEGER NOT NULL,
    ext_nor_con_apl_ins TEXT,
    ext_nor_con_apl_lec__presente INTEGER NOT NULL,
    ext_nor_con_apl_lec INTEGER,
    ext_nor_con_apl_nac_bol__presente INTEGER NOT NULL,
    ext_nor_con_apl_nac_bol INTEGER,
    ext_nor_con_apl_pro__presente INTEGER NOT NULL,
    ext_nor_con_apl_pro TEXT,
    ext_nor_con_apl_pun_cop__presente INTEGER NOT NULL,
    ext_nor_con_apl_pun_cop REAL,
    ext_nor_con_apl_pun_cop__cero_negativo INTEGER NOT NULL DEFAULT 0,
    ext_nor_con_apl_res_bol__presente INTEGER NOT NULL,
    ext_nor_con_apl_res_bol INTEGER,
    ext_nor_con_apl_seg_par_b__presente INTEGER NOT NULL,
    ext_nor_con_apl_seg_par_b REAL,
    ext_nor_con_apl_seg_par_b__cero_negativo INTEGER NOT NULL DEFAULT 0,
    extraccion_normalizacion_diagnostico__presente INTEGER NOT NULL,
    extraccion_normalizacion_diagnostico TEXT,
    extraccion_normalizacion_limite__presente INTEGER NOT NULL,
    extraccion_normalizacion_limite TEXT,
    extraccion_normalizacion_localizador__presente INTEGER NOT NULL,
    extraccion_normalizacion_localizador TEXT,
    extraccion_normalizacion_metodo__presente INTEGER NOT NULL,
    extraccion_normalizacion_metodo TEXT,
    extraccion_normalizacion_referencia__presente INTEGER NOT NULL,
    extraccion_normalizacion_referencia TEXT,
    extraccion_normalizacion_revision_profesional__presente INTEGER NOT NULL,
    extraccion_normalizacion_revision_profesional TEXT,
    extraccion_perfil__presente INTEGER NOT NULL,
    extraccion_perfil TEXT,
    fecha__presente INTEGER NOT NULL,
    fecha TEXT,
    fuente_edad__estado VARCHAR(8) NOT NULL CHECK(fuente_edad__estado IN ('AUSENTE','NULL','PRESENTE')),
    fuente_edad_campo__presente INTEGER NOT NULL,
    fuente_edad_campo TEXT,
    fuente_edad_fecha_aplicacion__presente INTEGER NOT NULL,
    fuente_edad_fecha_aplicacion TEXT,
    fuente_edad_fecha_nacimiento__presente INTEGER NOT NULL,
    fuente_edad_fecha_nacimiento TEXT,
    fuente_edad_registro__presente INTEGER NOT NULL,
    fuente_edad_registro TEXT,
    fuente_edad_tabla__presente INTEGER NOT NULL,
    fuente_edad_tabla TEXT,
    instrumento_actual__estado VARCHAR(8) NOT NULL CHECK(instrumento_actual__estado IN ('AUSENTE','NULL','PRESENTE')),
    instrumento_actual_cod_instrumento__presente INTEGER NOT NULL,
    instrumento_actual_cod_instrumento TEXT,
    instrumento_actual_codigo__presente INTEGER NOT NULL,
    instrumento_actual_codigo TEXT,
    instrumento_actual_descripcion__presente INTEGER NOT NULL,
    instrumento_actual_descripcion TEXT,
    instrumento_actual_estado__presente INTEGER NOT NULL,
    instrumento_actual_estado TEXT,
    instrumento_actual_nombre__presente INTEGER NOT NULL,
    instrumento_actual_nombre TEXT,
    instrumento_actual_puntaje_maximo__presente INTEGER NOT NULL,
    instrumento_actual_puntaje_maximo TEXT,
    instrumento_actual_tipo__presente INTEGER NOT NULL,
    instrumento_actual_tipo TEXT,
    instrumento_actual_version__presente INTEGER NOT NULL,
    instrumento_actual_version TEXT,
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    motivos__cantidad BIGINT NOT NULL CHECK(motivos__cantidad>=0),
    nombre_instrumento__presente INTEGER NOT NULL,
    nombre_instrumento TEXT,
    perfil_captura_original__estado VARCHAR(8) NOT NULL CHECK(perfil_captura_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_captura_original_cod_instrumento__presente INTEGER NOT NULL,
    perfil_captura_original_cod_instrumento TEXT,
    perfil_captura_original_clave_instrumento__presente INTEGER NOT NULL,
    perfil_captura_original_clave_instrumento TEXT,
    perfil_captura_original_id__presente INTEGER NOT NULL,
    perfil_captura_original_id TEXT,
    perfil_captura_original_identidad_instrumento__estado VARCHAR(8) NOT NULL CHECK(perfil_captura_original_identidad_instrumento__estado IN ('AUSENTE','NULL','PRESENTE')),
    per_cap_ori_ide_ins_edi_pro__presente INTEGER NOT NULL,
    per_cap_ori_ide_ins_edi_pro TEXT,
    per_cap_ori_ide_ins_for__presente INTEGER NOT NULL,
    per_cap_ori_ide_ins_for BIGINT,
    per_cap_ori_ide_ins_idi__presente INTEGER NOT NULL,
    per_cap_ori_ide_ins_idi TEXT,
    per_cap_ori_ide_ins_ins__presente INTEGER NOT NULL,
    per_cap_ori_ide_ins_ins TEXT,
    perfil_captura_original_metodo__presente INTEGER NOT NULL,
    perfil_captura_original_metodo TEXT,
    perfil_captura_original_selectores__estado VARCHAR(8) NOT NULL CHECK(perfil_captura_original_selectores__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_captura_original_selectores__cantidad BIGINT NOT NULL CHECK(perfil_captura_original_selectores__cantidad>=0),
    perfil_captura_original_version_instrumento__presente INTEGER NOT NULL,
    perfil_captura_original_version_instrumento TEXT,
    preguntas_declarativas_originales__estado VARCHAR(8) NOT NULL CHECK(preguntas_declarativas_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    preguntas_declarativas_originales__cantidad BIGINT NOT NULL CHECK(preguntas_declarativas_originales__cantidad>=0),
    procedencia__estado VARCHAR(8) NOT NULL CHECK(procedencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia_cod_aplicacion_anterior__presente INTEGER NOT NULL,
    procedencia_cod_aplicacion_anterior TEXT,
    procedencia_cod_documento_clinico__presente INTEGER NOT NULL,
    procedencia_cod_documento_clinico TEXT,
    procedencia_cod_usuario_registro__presente INTEGER NOT NULL,
    procedencia_cod_usuario_registro TEXT,
    procedencia_localizador_fuente__presente INTEGER NOT NULL,
    procedencia_localizador_fuente TEXT,
    procedencia_registrado_en__presente INTEGER NOT NULL,
    procedencia_registrado_en TEXT,
    respuestas_originales__estado VARCHAR(8) NOT NULL CHECK(respuestas_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    respuestas_originales__cantidad BIGINT NOT NULL CHECK(respuestas_originales__cantidad>=0),
    version_instrumento__presente INTEGER NOT NULL,
    version_instrumento TEXT,
    vinculos_preguntas_actuales__estado VARCHAR(8) NOT NULL CHECK(vinculos_preguntas_actuales__estado IN ('AUSENTE','NULL','PRESENTE')),
    vinculos_preguntas_actuales__cantidad BIGINT NOT NULL CHECK(vinculos_preguntas_actuales__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(aplicacion_actual_clasificacion__presente IN (0,1)),
    CHECK(aplicacion_actual_clasificacion__presente=1 OR aplicacion_actual_clasificacion IS NULL),
    CHECK(aplicacion_actual_cod_aplicacion__presente IN (0,1)),
    CHECK(aplicacion_actual_cod_aplicacion__presente=1 OR aplicacion_actual_cod_aplicacion IS NULL),
    CHECK(aplicacion_actual_cod_aplicacion_anterior__presente IN (0,1)),
    CHECK(aplicacion_actual_cod_aplicacion_anterior__presente=1 OR aplicacion_actual_cod_aplicacion_anterior IS NULL),
    CHECK(aplicacion_actual_cod_atencion__presente IN (0,1)),
    CHECK(aplicacion_actual_cod_atencion__presente=1 OR aplicacion_actual_cod_atencion IS NULL),
    CHECK(aplicacion_actual_cod_documento_clinico__presente IN (0,1)),
    CHECK(aplicacion_actual_cod_documento_clinico__presente=1 OR aplicacion_actual_cod_documento_clinico IS NULL),
    CHECK(aplicacion_actual_cod_instrumento__presente IN (0,1)),
    CHECK(aplicacion_actual_cod_instrumento__presente=1 OR aplicacion_actual_cod_instrumento IS NULL),
    CHECK(aplicacion_actual_cod_personal__presente IN (0,1)),
    CHECK(aplicacion_actual_cod_personal__presente=1 OR aplicacion_actual_cod_personal IS NULL),
    CHECK(aplicacion_actual_cod_residente__presente IN (0,1)),
    CHECK(aplicacion_actual_cod_residente__presente=1 OR aplicacion_actual_cod_residente IS NULL),
    CHECK(aplicacion_actual_cod_usuario_registro__presente IN (0,1)),
    CHECK(aplicacion_actual_cod_usuario_registro__presente=1 OR aplicacion_actual_cod_usuario_registro IS NULL),
    CHECK(aplicacion_actual_estado__presente IN (0,1)),
    CHECK(aplicacion_actual_estado__presente=1 OR aplicacion_actual_estado IS NULL),
    CHECK(aplicacion_actual_fecha__presente IN (0,1)),
    CHECK(aplicacion_actual_fecha__presente=1 OR aplicacion_actual_fecha IS NULL),
    CHECK(aplicacion_actual_interpretacion__presente IN (0,1)),
    CHECK(aplicacion_actual_interpretacion__presente=1 OR aplicacion_actual_interpretacion IS NULL),
    CHECK(aplicacion_actual_localizador_fuente__presente IN (0,1)),
    CHECK(aplicacion_actual_localizador_fuente__presente=1 OR aplicacion_actual_localizador_fuente IS NULL),
    CHECK(aplicacion_actual_observacion__presente IN (0,1)),
    CHECK(aplicacion_actual_observacion__presente=1 OR aplicacion_actual_observacion IS NULL),
    CHECK(aplicacion_actual_puntaje_maximo__presente IN (0,1)),
    CHECK(aplicacion_actual_puntaje_maximo__presente=1 OR aplicacion_actual_puntaje_maximo IS NULL),
    CHECK(aplicacion_actual_puntaje_total__presente IN (0,1)),
    CHECK(aplicacion_actual_puntaje_total__presente=1 OR aplicacion_actual_puntaje_total IS NULL),
    CHECK(aplicacion_actual_registrado_en__presente IN (0,1)),
    CHECK(aplicacion_actual_registrado_en__presente=1 OR aplicacion_actual_registrado_en IS NULL),
    CHECK(atencion_original_cod_atencion__presente IN (0,1)),
    CHECK(atencion_original_cod_atencion__presente=1 OR atencion_original_cod_atencion IS NULL),
    CHECK(atencion_original_cod_residente__presente IN (0,1)),
    CHECK(atencion_original_cod_residente__presente=1 OR atencion_original_cod_residente IS NULL),
    CHECK(autor__presente IN (0,1)),
    CHECK(autor__presente=1 OR autor IS NULL),
    CHECK(cod_aplicacion__presente IN (0,1)),
    CHECK(cod_aplicacion__presente=1 OR cod_aplicacion IS NULL),
    CHECK(cod_instrumento__presente IN (0,1)),
    CHECK(cod_instrumento__presente=1 OR cod_instrumento IS NULL),
    CHECK(cod_personal__presente IN (0,1)),
    CHECK(cod_personal__presente=1 OR cod_personal IS NULL),
    CHECK(cod_residente__presente IN (0,1)),
    CHECK(cod_residente__presente=1 OR cod_residente IS NULL),
    CHECK(clave_instrumento__presente IN (0,1)),
    CHECK(clave_instrumento__presente=1 OR clave_instrumento IS NULL),
    CHECK(documento_original_cod_documento_clinico__presente IN (0,1)),
    CHECK(documento_original_cod_documento_clinico__presente=1 OR documento_original_cod_documento_clinico IS NULL),
    CHECK(documento_original_cod_personal__presente IN (0,1)),
    CHECK(documento_original_cod_personal__presente=1 OR documento_original_cod_personal IS NULL),
    CHECK(documento_original_cod_residente__presente IN (0,1)),
    CHECK(documento_original_cod_residente__presente=1 OR documento_original_cod_residente IS NULL),
    CHECK(documento_original_estado__presente IN (0,1)),
    CHECK(documento_original_estado__presente=1 OR documento_original_estado IS NULL),
    CHECK(documento_original_fecha__presente IN (0,1)),
    CHECK(documento_original_fecha__presente=1 OR documento_original_fecha IS NULL),
    CHECK(documento_original_hash_archivo__presente IN (0,1)),
    CHECK(documento_original_hash_archivo__presente=1 OR documento_original_hash_archivo IS NULL),
    CHECK(edad_en_aplicacion__presente IN (0,1)),
    CHECK(edad_en_aplicacion__presente=1 OR edad_en_aplicacion IS NULL),
    CHECK(estado_original__presente IN (0,1)),
    CHECK(estado_original__presente=1 OR estado_original IS NULL),
    CHECK(ext_apl_ger_alc_eta_com__presente IN (0,1)),
    CHECK(ext_apl_ger_alc_eta_com__presente=1 OR ext_apl_ger_alc_eta_com IS NULL),
    CHECK(ext_apl_ger_alc_eta_com IS NULL OR ext_apl_ger_alc_eta_com IN (0,1)),
    CHECK(ext_apl_ger_aut_int_cli__presente IN (0,1)),
    CHECK(ext_apl_ger_aut_int_cli__presente=1 OR ext_apl_ger_aut_int_cli IS NULL),
    CHECK(ext_apl_ger_aut_int_cli IS NULL OR ext_apl_ger_aut_int_cli IN (0,1)),
    CHECK(ext_apl_ger_eda_en_apl__presente IN (0,1)),
    CHECK(ext_apl_ger_eda_en_apl__presente=1 OR ext_apl_ger_eda_en_apl IS NULL),
    CHECK(ext_apl_ger_esc_ori__presente IN (0,1)),
    CHECK(ext_apl_ger_esc_ori__presente=1 OR ext_apl_ger_esc_ori IS NULL),
    CHECK(extraccion_aplicabilidad_geriatrica_fuente__presente IN (0,1)),
    CHECK(extraccion_aplicabilidad_geriatrica_fuente__presente=1 OR extraccion_aplicabilidad_geriatrica_fuente IS NULL),
    CHECK(ext_apl_ger_idi_dec__presente IN (0,1)),
    CHECK(ext_apl_ger_idi_dec__presente=1 OR ext_apl_ger_idi_dec IS NULL),
    CHECK(ext_apl_ger_per_a_pob_obj__presente IN (0,1)),
    CHECK(ext_apl_ger_per_a_pob_obj__presente=1 OR ext_apl_ger_per_a_pob_obj IS NULL),
    CHECK(ext_apl_ger_per_a_pob_obj IS NULL OR ext_apl_ger_per_a_pob_obj IN (0,1)),
    CHECK(ext_apl_ger_pob_obj__presente IN (0,1)),
    CHECK(ext_apl_ger_pob_obj__presente=1 OR ext_apl_ger_pob_obj IS NULL),
    CHECK(ext_apl_ger_ran_eda_ref_max__presente IN (0,1)),
    CHECK(ext_apl_ger_ran_eda_ref_max__presente=1 OR ext_apl_ger_ran_eda_ref_max IS NULL),
    CHECK(ext_apl_ger_ran_eda_ref_min__presente IN (0,1)),
    CHECK(ext_apl_ger_ran_eda_ref_min__presente=1 OR ext_apl_ger_ran_eda_ref_min IS NULL),
    CHECK(ext_apl_ger_req_ver__estado='PRESENTE' OR ext_apl_ger_req_ver__cantidad=0),
    CHECK(extraccion_captura_aciertos_clave_semantica__presente IN (0,1)),
    CHECK(extraccion_captura_aciertos_clave_semantica__presente=1 OR extraccion_captura_aciertos_clave_semantica IS NULL),
    CHECK(extraccion_captura_aciertos_espontaneos__presente IN (0,1)),
    CHECK(extraccion_captura_aciertos_espontaneos__presente=1 OR extraccion_captura_aciertos_espontaneos IS NULL),
    CHECK(extraccion_captura_administracion_estandarizada__presente IN (0,1)),
    CHECK(extraccion_captura_administracion_estandarizada__presente=1 OR extraccion_captura_administracion_estandarizada IS NULL),
    CHECK(extraccion_captura_administracion_estandarizada IS NULL OR extraccion_captura_administracion_estandarizada IN (0,1)),
    CHECK(extraccion_captura_audicion_compatible__presente IN (0,1)),
    CHECK(extraccion_captura_audicion_compatible__presente=1 OR extraccion_captura_audicion_compatible IS NULL),
    CHECK(extraccion_captura_audicion_compatible IS NULL OR extraccion_captura_audicion_compatible IN (0,1)),
    CHECK(extraccion_captura_condiciones_sensoriales__presente IN (0,1)),
    CHECK(extraccion_captura_condiciones_sensoriales__presente=1 OR extraccion_captura_condiciones_sensoriales IS NULL),
    CHECK(extraccion_captura_condiciones_sensoriales IS NULL OR extraccion_captura_condiciones_sensoriales IN (0,1)),
    CHECK(extraccion_captura_demora_minutos__presente IN (0,1)),
    CHECK(extraccion_captura_demora_minutos__presente=1 OR extraccion_captura_demora_minutos IS NULL),
    CHECK(extraccion_captura_demora_minutos__cero_negativo=0 OR extraccion_captura_demora_minutos=0),
    CHECK(extraccion_captura_edad_anios__presente IN (0,1)),
    CHECK(extraccion_captura_edad_anios__presente=1 OR extraccion_captura_edad_anios IS NULL),
    CHECK(extraccion_captura_edicion_protocolo__presente IN (0,1)),
    CHECK(extraccion_captura_edicion_protocolo__presente=1 OR extraccion_captura_edicion_protocolo IS NULL),
    CHECK(extraccion_captura_elementos_revisados__presente IN (0,1)),
    CHECK(extraccion_captura_elementos_revisados__presente=1 OR extraccion_captura_elementos_revisados IS NULL),
    CHECK(extraccion_captura_ensayo_1__presente IN (0,1)),
    CHECK(extraccion_captura_ensayo_1__presente=1 OR extraccion_captura_ensayo_1 IS NULL),
    CHECK(extraccion_captura_ensayo_2__presente IN (0,1)),
    CHECK(extraccion_captura_ensayo_2__presente=1 OR extraccion_captura_ensayo_2 IS NULL),
    CHECK(extraccion_captura_ensayo_3__presente IN (0,1)),
    CHECK(extraccion_captura_ensayo_3__presente=1 OR extraccion_captura_ensayo_3 IS NULL),
    CHECK(extraccion_captura_ensayos__estado='PRESENTE' OR extraccion_captura_ensayos__cantidad=0),
    CHECK(extraccion_captura_errores_observados__presente IN (0,1)),
    CHECK(extraccion_captura_errores_observados__presente=1 OR extraccion_captura_errores_observados IS NULL),
    CHECK(extraccion_captura_escolaridad_anios__presente IN (0,1)),
    CHECK(extraccion_captura_escolaridad_anios__presente=1 OR extraccion_captura_escolaridad_anios IS NULL),
    CHECK(extraccion_captura_espanol_nativo__presente IN (0,1)),
    CHECK(extraccion_captura_espanol_nativo__presente=1 OR extraccion_captura_espanol_nativo IS NULL),
    CHECK(extraccion_captura_espanol_nativo IS NULL OR extraccion_captura_espanol_nativo IN (0,1)),
    CHECK(extraccion_captura_finalizada__presente IN (0,1)),
    CHECK(extraccion_captura_finalizada__presente=1 OR extraccion_captura_finalizada IS NULL),
    CHECK(extraccion_captura_finalizada IS NULL OR extraccion_captura_finalizada IN (0,1)),
    CHECK(extraccion_captura_forma__presente IN (0,1)),
    CHECK(extraccion_captura_forma__presente=1 OR extraccion_captura_forma IS NULL),
    CHECK(extraccion_captura_forma_l_aciertos__presente IN (0,1)),
    CHECK(extraccion_captura_forma_l_aciertos__presente=1 OR extraccion_captura_forma_l_aciertos IS NULL),
    CHECK(extraccion_captura_forma_n_aciertos__presente IN (0,1)),
    CHECK(extraccion_captura_forma_n_aciertos__presente=1 OR extraccion_captura_forma_n_aciertos IS NULL),
    CHECK(extraccion_captura_idioma__presente IN (0,1)),
    CHECK(extraccion_captura_idioma__presente=1 OR extraccion_captura_idioma IS NULL),
    CHECK(extraccion_captura_instrumento__presente IN (0,1)),
    CHECK(extraccion_captura_instrumento__presente=1 OR extraccion_captura_instrumento IS NULL),
    CHECK(extraccion_captura_lectoescritura__presente IN (0,1)),
    CHECK(extraccion_captura_lectoescritura__presente=1 OR extraccion_captura_lectoescritura IS NULL),
    CHECK(extraccion_captura_lectoescritura IS NULL OR extraccion_captura_lectoescritura IN (0,1)),
    CHECK(extraccion_captura_nacimiento_bolivia__presente IN (0,1)),
    CHECK(extraccion_captura_nacimiento_bolivia__presente=1 OR extraccion_captura_nacimiento_bolivia IS NULL),
    CHECK(extraccion_captura_nacimiento_bolivia IS NULL OR extraccion_captura_nacimiento_bolivia IN (0,1)),
    CHECK(extraccion_captura_protocolo__presente IN (0,1)),
    CHECK(extraccion_captura_protocolo__presente=1 OR extraccion_captura_protocolo IS NULL),
    CHECK(extraccion_captura_puntaje_copia__presente IN (0,1)),
    CHECK(extraccion_captura_puntaje_copia__presente=1 OR extraccion_captura_puntaje_copia IS NULL),
    CHECK(extraccion_captura_puntaje_copia__cero_negativo=0 OR extraccion_captura_puntaje_copia=0),
    CHECK(extraccion_captura_recuerdo_diferido__presente IN (0,1)),
    CHECK(extraccion_captura_recuerdo_diferido__presente=1 OR extraccion_captura_recuerdo_diferido IS NULL),
    CHECK(extraccion_captura_residencia_bolivia__presente IN (0,1)),
    CHECK(extraccion_captura_residencia_bolivia__presente=1 OR extraccion_captura_residencia_bolivia IS NULL),
    CHECK(extraccion_captura_residencia_bolivia IS NULL OR extraccion_captura_residencia_bolivia IN (0,1)),
    CHECK(extraccion_captura_segundos_parte_b__presente IN (0,1)),
    CHECK(extraccion_captura_segundos_parte_b__presente=1 OR extraccion_captura_segundos_parte_b IS NULL),
    CHECK(extraccion_captura_segundos_parte_b__cero_negativo=0 OR extraccion_captura_segundos_parte_b=0),
    CHECK(extraccion_fuentes_entradas__estado='PRESENTE' OR extraccion_fuentes_entradas__cantidad=0),
    CHECK(extraccion_identidad_declarada_edicion_protocolo__presente IN (0,1)),
    CHECK(extraccion_identidad_declarada_edicion_protocolo__presente=1 OR extraccion_identidad_declarada_edicion_protocolo IS NULL),
    CHECK(extraccion_identidad_declarada_forma__presente IN (0,1)),
    CHECK(extraccion_identidad_declarada_forma__presente=1 OR extraccion_identidad_declarada_forma IS NULL),
    CHECK(extraccion_identidad_declarada_idioma__presente IN (0,1)),
    CHECK(extraccion_identidad_declarada_idioma__presente=1 OR extraccion_identidad_declarada_idioma IS NULL),
    CHECK(extraccion_identidad_declarada_instrumento__presente IN (0,1)),
    CHECK(extraccion_identidad_declarada_instrumento__presente=1 OR extraccion_identidad_declarada_instrumento IS NULL),
    CHECK(ext_ide_req_met_edi_pro__presente IN (0,1)),
    CHECK(ext_ide_req_met_edi_pro__presente=1 OR ext_ide_req_met_edi_pro IS NULL),
    CHECK(extraccion_identidad_requerida_metodo_forma__presente IN (0,1)),
    CHECK(extraccion_identidad_requerida_metodo_forma__presente=1 OR extraccion_identidad_requerida_metodo_forma IS NULL),
    CHECK(extraccion_identidad_requerida_metodo_idioma__presente IN (0,1)),
    CHECK(extraccion_identidad_requerida_metodo_idioma__presente=1 OR extraccion_identidad_requerida_metodo_idioma IS NULL),
    CHECK(extraccion_identidad_requerida_metodo_instrumento__presente IN (0,1)),
    CHECK(extraccion_identidad_requerida_metodo_instrumento__presente=1 OR extraccion_identidad_requerida_metodo_instrumento IS NULL),
    CHECK(extraccion_metodo__presente IN (0,1)),
    CHECK(extraccion_metodo__presente=1 OR extraccion_metodo IS NULL),
    CHECK(ext_nor_bru_aci_cla_sem__presente IN (0,1)),
    CHECK(ext_nor_bru_aci_cla_sem__presente=1 OR ext_nor_bru_aci_cla_sem IS NULL),
    CHECK(ext_nor_bru_aci_esp__presente IN (0,1)),
    CHECK(ext_nor_bru_aci_esp__presente=1 OR ext_nor_bru_aci_esp IS NULL),
    CHECK(ext_nor_bru_adm_est__presente IN (0,1)),
    CHECK(ext_nor_bru_adm_est__presente=1 OR ext_nor_bru_adm_est IS NULL),
    CHECK(ext_nor_bru_adm_est IS NULL OR ext_nor_bru_adm_est IN (0,1)),
    CHECK(ext_nor_bru_aud_com__presente IN (0,1)),
    CHECK(ext_nor_bru_aud_com__presente=1 OR ext_nor_bru_aud_com IS NULL),
    CHECK(ext_nor_bru_aud_com IS NULL OR ext_nor_bru_aud_com IN (0,1)),
    CHECK(ext_nor_bru_con_sen__presente IN (0,1)),
    CHECK(ext_nor_bru_con_sen__presente=1 OR ext_nor_bru_con_sen IS NULL),
    CHECK(ext_nor_bru_con_sen IS NULL OR ext_nor_bru_con_sen IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_demora_minutos__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_demora_minutos__presente=1 OR extraccion_normalizacion_bruto_demora_minutos IS NULL),
    CHECK(extraccion_normalizacion_bruto_demora_minutos__cero_negativo=0 OR extraccion_normalizacion_bruto_demora_minutos=0),
    CHECK(ext_nor_bru_ele_rev__presente IN (0,1)),
    CHECK(ext_nor_bru_ele_rev__presente=1 OR ext_nor_bru_ele_rev IS NULL),
    CHECK(extraccion_normalizacion_bruto_ensayos__estado='PRESENTE' OR extraccion_normalizacion_bruto_ensayos__cantidad=0),
    CHECK(extraccion_normalizacion_bruto_errores_observados__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_errores_observados__presente=1 OR extraccion_normalizacion_bruto_errores_observados IS NULL),
    CHECK(extraccion_normalizacion_bruto_escolaridad_anios__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_escolaridad_anios__presente=1 OR extraccion_normalizacion_bruto_escolaridad_anios IS NULL),
    CHECK(extraccion_normalizacion_bruto_espanol_nativo__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_espanol_nativo__presente=1 OR extraccion_normalizacion_bruto_espanol_nativo IS NULL),
    CHECK(extraccion_normalizacion_bruto_espanol_nativo IS NULL OR extraccion_normalizacion_bruto_espanol_nativo IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_finalizada__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_finalizada__presente=1 OR extraccion_normalizacion_bruto_finalizada IS NULL),
    CHECK(extraccion_normalizacion_bruto_finalizada IS NULL OR extraccion_normalizacion_bruto_finalizada IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_forma_l_aciertos__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_forma_l_aciertos__presente=1 OR extraccion_normalizacion_bruto_forma_l_aciertos IS NULL),
    CHECK(extraccion_normalizacion_bruto_forma_n_aciertos__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_forma_n_aciertos__presente=1 OR extraccion_normalizacion_bruto_forma_n_aciertos IS NULL),
    CHECK(extraccion_normalizacion_bruto_lectoescritura__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_lectoescritura__presente=1 OR extraccion_normalizacion_bruto_lectoescritura IS NULL),
    CHECK(extraccion_normalizacion_bruto_lectoescritura IS NULL OR extraccion_normalizacion_bruto_lectoescritura IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_nacimiento_bolivia__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_nacimiento_bolivia__presente=1 OR extraccion_normalizacion_bruto_nacimiento_bolivia IS NULL),
    CHECK(extraccion_normalizacion_bruto_nacimiento_bolivia IS NULL OR extraccion_normalizacion_bruto_nacimiento_bolivia IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_puntaje_copia__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_puntaje_copia__presente=1 OR extraccion_normalizacion_bruto_puntaje_copia IS NULL),
    CHECK(extraccion_normalizacion_bruto_puntaje_copia__cero_negativo=0 OR extraccion_normalizacion_bruto_puntaje_copia=0),
    CHECK(extraccion_normalizacion_bruto_recuerdo_diferido__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_recuerdo_diferido__presente=1 OR extraccion_normalizacion_bruto_recuerdo_diferido IS NULL),
    CHECK(extraccion_normalizacion_bruto_residencia_bolivia__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_residencia_bolivia__presente=1 OR extraccion_normalizacion_bruto_residencia_bolivia IS NULL),
    CHECK(extraccion_normalizacion_bruto_residencia_bolivia IS NULL OR extraccion_normalizacion_bruto_residencia_bolivia IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_segundos_parte_b__presente IN (0,1)),
    CHECK(extraccion_normalizacion_bruto_segundos_parte_b__presente=1 OR extraccion_normalizacion_bruto_segundos_parte_b IS NULL),
    CHECK(extraccion_normalizacion_bruto_segundos_parte_b__cero_negativo=0 OR extraccion_normalizacion_bruto_segundos_parte_b=0),
    CHECK(extraccion_normalizacion_componentes__estado='PRESENTE' OR extraccion_normalizacion_componentes__cantidad=0),
    CHECK(ext_nor_con_apl_aci_cla_sem__presente IN (0,1)),
    CHECK(ext_nor_con_apl_aci_cla_sem__presente=1 OR ext_nor_con_apl_aci_cla_sem IS NULL),
    CHECK(ext_nor_con_apl_aci_esp__presente IN (0,1)),
    CHECK(ext_nor_con_apl_aci_esp__presente=1 OR ext_nor_con_apl_aci_esp IS NULL),
    CHECK(ext_nor_con_apl_adm_est__presente IN (0,1)),
    CHECK(ext_nor_con_apl_adm_est__presente=1 OR ext_nor_con_apl_adm_est IS NULL),
    CHECK(ext_nor_con_apl_adm_est IS NULL OR ext_nor_con_apl_adm_est IN (0,1)),
    CHECK(ext_nor_con_apl_aud_com__presente IN (0,1)),
    CHECK(ext_nor_con_apl_aud_com__presente=1 OR ext_nor_con_apl_aud_com IS NULL),
    CHECK(ext_nor_con_apl_aud_com IS NULL OR ext_nor_con_apl_aud_com IN (0,1)),
    CHECK(ext_nor_con_apl_con_sen__presente IN (0,1)),
    CHECK(ext_nor_con_apl_con_sen__presente=1 OR ext_nor_con_apl_con_sen IS NULL),
    CHECK(ext_nor_con_apl_con_sen IS NULL OR ext_nor_con_apl_con_sen IN (0,1)),
    CHECK(ext_nor_con_apl_eda_ani__presente IN (0,1)),
    CHECK(ext_nor_con_apl_eda_ani__presente=1 OR ext_nor_con_apl_eda_ani IS NULL),
    CHECK(ext_nor_con_apl_edi_pro__presente IN (0,1)),
    CHECK(ext_nor_con_apl_edi_pro__presente=1 OR ext_nor_con_apl_edi_pro IS NULL),
    CHECK(ext_nor_con_apl_ele_rev__presente IN (0,1)),
    CHECK(ext_nor_con_apl_ele_rev__presente=1 OR ext_nor_con_apl_ele_rev IS NULL),
    CHECK(ext_nor_con_apl_err_obs__presente IN (0,1)),
    CHECK(ext_nor_con_apl_err_obs__presente=1 OR ext_nor_con_apl_err_obs IS NULL),
    CHECK(ext_nor_con_apl_esc_ani__presente IN (0,1)),
    CHECK(ext_nor_con_apl_esc_ani__presente=1 OR ext_nor_con_apl_esc_ani IS NULL),
    CHECK(ext_nor_con_apl_esp_nat__presente IN (0,1)),
    CHECK(ext_nor_con_apl_esp_nat__presente=1 OR ext_nor_con_apl_esp_nat IS NULL),
    CHECK(ext_nor_con_apl_esp_nat IS NULL OR ext_nor_con_apl_esp_nat IN (0,1)),
    CHECK(ext_nor_con_apl_fin__presente IN (0,1)),
    CHECK(ext_nor_con_apl_fin__presente=1 OR ext_nor_con_apl_fin IS NULL),
    CHECK(ext_nor_con_apl_fin IS NULL OR ext_nor_con_apl_fin IN (0,1)),
    CHECK(ext_nor_con_apl_for__presente IN (0,1)),
    CHECK(ext_nor_con_apl_for__presente=1 OR ext_nor_con_apl_for IS NULL),
    CHECK(ext_nor_con_apl_for_l_aci__presente IN (0,1)),
    CHECK(ext_nor_con_apl_for_l_aci__presente=1 OR ext_nor_con_apl_for_l_aci IS NULL),
    CHECK(ext_nor_con_apl_for_n_aci__presente IN (0,1)),
    CHECK(ext_nor_con_apl_for_n_aci__presente=1 OR ext_nor_con_apl_for_n_aci IS NULL),
    CHECK(ext_nor_con_apl_idi__presente IN (0,1)),
    CHECK(ext_nor_con_apl_idi__presente=1 OR ext_nor_con_apl_idi IS NULL),
    CHECK(ext_nor_con_apl_ins__presente IN (0,1)),
    CHECK(ext_nor_con_apl_ins__presente=1 OR ext_nor_con_apl_ins IS NULL),
    CHECK(ext_nor_con_apl_lec__presente IN (0,1)),
    CHECK(ext_nor_con_apl_lec__presente=1 OR ext_nor_con_apl_lec IS NULL),
    CHECK(ext_nor_con_apl_lec IS NULL OR ext_nor_con_apl_lec IN (0,1)),
    CHECK(ext_nor_con_apl_nac_bol__presente IN (0,1)),
    CHECK(ext_nor_con_apl_nac_bol__presente=1 OR ext_nor_con_apl_nac_bol IS NULL),
    CHECK(ext_nor_con_apl_nac_bol IS NULL OR ext_nor_con_apl_nac_bol IN (0,1)),
    CHECK(ext_nor_con_apl_pro__presente IN (0,1)),
    CHECK(ext_nor_con_apl_pro__presente=1 OR ext_nor_con_apl_pro IS NULL),
    CHECK(ext_nor_con_apl_pun_cop__presente IN (0,1)),
    CHECK(ext_nor_con_apl_pun_cop__presente=1 OR ext_nor_con_apl_pun_cop IS NULL),
    CHECK(ext_nor_con_apl_pun_cop__cero_negativo=0 OR ext_nor_con_apl_pun_cop=0),
    CHECK(ext_nor_con_apl_res_bol__presente IN (0,1)),
    CHECK(ext_nor_con_apl_res_bol__presente=1 OR ext_nor_con_apl_res_bol IS NULL),
    CHECK(ext_nor_con_apl_res_bol IS NULL OR ext_nor_con_apl_res_bol IN (0,1)),
    CHECK(ext_nor_con_apl_seg_par_b__presente IN (0,1)),
    CHECK(ext_nor_con_apl_seg_par_b__presente=1 OR ext_nor_con_apl_seg_par_b IS NULL),
    CHECK(ext_nor_con_apl_seg_par_b__cero_negativo=0 OR ext_nor_con_apl_seg_par_b=0),
    CHECK(extraccion_normalizacion_diagnostico__presente IN (0,1)),
    CHECK(extraccion_normalizacion_diagnostico__presente=1 OR extraccion_normalizacion_diagnostico IS NULL),
    CHECK(extraccion_normalizacion_limite__presente IN (0,1)),
    CHECK(extraccion_normalizacion_limite__presente=1 OR extraccion_normalizacion_limite IS NULL),
    CHECK(extraccion_normalizacion_localizador__presente IN (0,1)),
    CHECK(extraccion_normalizacion_localizador__presente=1 OR extraccion_normalizacion_localizador IS NULL),
    CHECK(extraccion_normalizacion_metodo__presente IN (0,1)),
    CHECK(extraccion_normalizacion_metodo__presente=1 OR extraccion_normalizacion_metodo IS NULL),
    CHECK(extraccion_normalizacion_referencia__presente IN (0,1)),
    CHECK(extraccion_normalizacion_referencia__presente=1 OR extraccion_normalizacion_referencia IS NULL),
    CHECK(extraccion_normalizacion_revision_profesional__presente IN (0,1)),
    CHECK(extraccion_normalizacion_revision_profesional__presente=1 OR extraccion_normalizacion_revision_profesional IS NULL),
    CHECK(extraccion_perfil__presente IN (0,1)),
    CHECK(extraccion_perfil__presente=1 OR extraccion_perfil IS NULL),
    CHECK(fecha__presente IN (0,1)),
    CHECK(fecha__presente=1 OR fecha IS NULL),
    CHECK(fuente_edad_campo__presente IN (0,1)),
    CHECK(fuente_edad_campo__presente=1 OR fuente_edad_campo IS NULL),
    CHECK(fuente_edad_fecha_aplicacion__presente IN (0,1)),
    CHECK(fuente_edad_fecha_aplicacion__presente=1 OR fuente_edad_fecha_aplicacion IS NULL),
    CHECK(fuente_edad_fecha_nacimiento__presente IN (0,1)),
    CHECK(fuente_edad_fecha_nacimiento__presente=1 OR fuente_edad_fecha_nacimiento IS NULL),
    CHECK(fuente_edad_registro__presente IN (0,1)),
    CHECK(fuente_edad_registro__presente=1 OR fuente_edad_registro IS NULL),
    CHECK(fuente_edad_tabla__presente IN (0,1)),
    CHECK(fuente_edad_tabla__presente=1 OR fuente_edad_tabla IS NULL),
    CHECK(instrumento_actual_cod_instrumento__presente IN (0,1)),
    CHECK(instrumento_actual_cod_instrumento__presente=1 OR instrumento_actual_cod_instrumento IS NULL),
    CHECK(instrumento_actual_codigo__presente IN (0,1)),
    CHECK(instrumento_actual_codigo__presente=1 OR instrumento_actual_codigo IS NULL),
    CHECK(instrumento_actual_descripcion__presente IN (0,1)),
    CHECK(instrumento_actual_descripcion__presente=1 OR instrumento_actual_descripcion IS NULL),
    CHECK(instrumento_actual_estado__presente IN (0,1)),
    CHECK(instrumento_actual_estado__presente=1 OR instrumento_actual_estado IS NULL),
    CHECK(instrumento_actual_nombre__presente IN (0,1)),
    CHECK(instrumento_actual_nombre__presente=1 OR instrumento_actual_nombre IS NULL),
    CHECK(instrumento_actual_puntaje_maximo__presente IN (0,1)),
    CHECK(instrumento_actual_puntaje_maximo__presente=1 OR instrumento_actual_puntaje_maximo IS NULL),
    CHECK(instrumento_actual_tipo__presente IN (0,1)),
    CHECK(instrumento_actual_tipo__presente=1 OR instrumento_actual_tipo IS NULL),
    CHECK(instrumento_actual_version__presente IN (0,1)),
    CHECK(instrumento_actual_version__presente=1 OR instrumento_actual_version IS NULL),
    CHECK(motivos__estado='PRESENTE' OR motivos__cantidad=0),
    CHECK(nombre_instrumento__presente IN (0,1)),
    CHECK(nombre_instrumento__presente=1 OR nombre_instrumento IS NULL),
    CHECK(perfil_captura_original_cod_instrumento__presente IN (0,1)),
    CHECK(perfil_captura_original_cod_instrumento__presente=1 OR perfil_captura_original_cod_instrumento IS NULL),
    CHECK(perfil_captura_original_clave_instrumento__presente IN (0,1)),
    CHECK(perfil_captura_original_clave_instrumento__presente=1 OR perfil_captura_original_clave_instrumento IS NULL),
    CHECK(perfil_captura_original_id__presente IN (0,1)),
    CHECK(perfil_captura_original_id__presente=1 OR perfil_captura_original_id IS NULL),
    CHECK(per_cap_ori_ide_ins_edi_pro__presente IN (0,1)),
    CHECK(per_cap_ori_ide_ins_edi_pro__presente=1 OR per_cap_ori_ide_ins_edi_pro IS NULL),
    CHECK(per_cap_ori_ide_ins_for__presente IN (0,1)),
    CHECK(per_cap_ori_ide_ins_for__presente=1 OR per_cap_ori_ide_ins_for IS NULL),
    CHECK(per_cap_ori_ide_ins_idi__presente IN (0,1)),
    CHECK(per_cap_ori_ide_ins_idi__presente=1 OR per_cap_ori_ide_ins_idi IS NULL),
    CHECK(per_cap_ori_ide_ins_ins__presente IN (0,1)),
    CHECK(per_cap_ori_ide_ins_ins__presente=1 OR per_cap_ori_ide_ins_ins IS NULL),
    CHECK(perfil_captura_original_metodo__presente IN (0,1)),
    CHECK(perfil_captura_original_metodo__presente=1 OR perfil_captura_original_metodo IS NULL),
    CHECK(perfil_captura_original_selectores__estado='PRESENTE' OR perfil_captura_original_selectores__cantidad=0),
    CHECK(perfil_captura_original_version_instrumento__presente IN (0,1)),
    CHECK(perfil_captura_original_version_instrumento__presente=1 OR perfil_captura_original_version_instrumento IS NULL),
    CHECK(preguntas_declarativas_originales__estado='PRESENTE' OR preguntas_declarativas_originales__cantidad=0),
    CHECK(procedencia_cod_aplicacion_anterior__presente IN (0,1)),
    CHECK(procedencia_cod_aplicacion_anterior__presente=1 OR procedencia_cod_aplicacion_anterior IS NULL),
    CHECK(procedencia_cod_documento_clinico__presente IN (0,1)),
    CHECK(procedencia_cod_documento_clinico__presente=1 OR procedencia_cod_documento_clinico IS NULL),
    CHECK(procedencia_cod_usuario_registro__presente IN (0,1)),
    CHECK(procedencia_cod_usuario_registro__presente=1 OR procedencia_cod_usuario_registro IS NULL),
    CHECK(procedencia_localizador_fuente__presente IN (0,1)),
    CHECK(procedencia_localizador_fuente__presente=1 OR procedencia_localizador_fuente IS NULL),
    CHECK(procedencia_registrado_en__presente IN (0,1)),
    CHECK(procedencia_registrado_en__presente=1 OR procedencia_registrado_en IS NULL),
    CHECK(respuestas_originales__estado='PRESENTE' OR respuestas_originales__cantidad=0),
    CHECK(version_instrumento__presente IN (0,1)),
    CHECK(version_instrumento__presente=1 OR version_instrumento IS NULL),
    CHECK(vinculos_preguntas_actuales__estado='PRESENTE' OR vinculos_preguntas_actuales__cantidad=0),
    CHECK(dto__estado='PRESENTE' OR (aplicacion_actual__estado='AUSENTE' AND atencion_original__estado='AUSENTE' AND autor__presente=0 AND cod_aplicacion__presente=0 AND cod_instrumento__presente=0 AND cod_personal__presente=0 AND cod_residente__presente=0 AND clave_instrumento__presente=0 AND documento_original__estado='AUSENTE' AND edad_en_aplicacion__presente=0 AND estado_original__presente=0 AND extraccion__estado='AUSENTE' AND fecha__presente=0 AND fuente_edad__estado='AUSENTE' AND instrumento_actual__estado='AUSENTE' AND motivos__estado='AUSENTE' AND nombre_instrumento__presente=0 AND perfil_captura_original__estado='AUSENTE' AND preguntas_declarativas_originales__estado='AUSENTE' AND procedencia__estado='AUSENTE' AND respuestas_originales__estado='AUSENTE' AND version_instrumento__presente=0 AND vinculos_preguntas_actuales__estado='AUSENTE')),
    CHECK(aplicacion_actual__estado='PRESENTE' OR (aplicacion_actual_clasificacion__presente=0 AND aplicacion_actual_cod_aplicacion__presente=0 AND aplicacion_actual_cod_aplicacion_anterior__presente=0 AND aplicacion_actual_cod_atencion__presente=0 AND aplicacion_actual_cod_documento_clinico__presente=0 AND aplicacion_actual_cod_instrumento__presente=0 AND aplicacion_actual_cod_personal__presente=0 AND aplicacion_actual_cod_residente__presente=0 AND aplicacion_actual_cod_usuario_registro__presente=0 AND aplicacion_actual_estado__presente=0 AND aplicacion_actual_fecha__presente=0 AND aplicacion_actual_interpretacion__presente=0 AND aplicacion_actual_localizador_fuente__presente=0 AND aplicacion_actual_observacion__presente=0 AND aplicacion_actual_puntaje_maximo__presente=0 AND aplicacion_actual_puntaje_total__presente=0 AND aplicacion_actual_registrado_en__presente=0)),
    CHECK(atencion_original__estado='PRESENTE' OR (atencion_original_cod_atencion__presente=0 AND atencion_original_cod_residente__presente=0)),
    CHECK(documento_original__estado='PRESENTE' OR (documento_original_cod_documento_clinico__presente=0 AND documento_original_cod_personal__presente=0 AND documento_original_cod_residente__presente=0 AND documento_original_estado__presente=0 AND documento_original_fecha__presente=0 AND documento_original_hash_archivo__presente=0)),
    CHECK(extraccion__estado='PRESENTE' OR (extraccion_aplicabilidad_geriatrica__estado='AUSENTE' AND extraccion_captura__estado='AUSENTE' AND extraccion_fuentes_entradas__estado='AUSENTE' AND extraccion_identidad_declarada__estado='AUSENTE' AND extraccion_identidad_requerida_metodo__estado='AUSENTE' AND extraccion_metodo__presente=0 AND extraccion_motivos_captura__estado='AUSENTE' AND extraccion_normalizacion__estado='AUSENTE' AND extraccion_perfil__presente=0)),
    CHECK(extraccion_aplicabilidad_geriatrica__estado='PRESENTE' OR (ext_apl_ger_alc_eta_com__presente=0 AND ext_apl_ger_aut_int_cli__presente=0 AND ext_apl_ger_eda_en_apl__presente=0 AND ext_apl_ger_esc_ori__presente=0 AND extraccion_aplicabilidad_geriatrica_fuente__presente=0 AND ext_apl_ger_idi_dec__presente=0 AND extraccion_aplicabilidad_geriatrica_motivos__estado='AUSENTE' AND ext_apl_ger_per_a_pob_obj__presente=0 AND ext_apl_ger_pob_obj__presente=0 AND ext_apl_ger_ran_eda_ref__estado='AUSENTE' AND ext_apl_ger_req_ver__estado='AUSENTE')),
    CHECK(ext_apl_ger_ran_eda_ref__estado='PRESENTE' OR (ext_apl_ger_ran_eda_ref_max__presente=0 AND ext_apl_ger_ran_eda_ref_min__presente=0)),
    CHECK(extraccion_captura__estado='PRESENTE' OR (extraccion_captura_aciertos_clave_semantica__presente=0 AND extraccion_captura_aciertos_espontaneos__presente=0 AND extraccion_captura_administracion_estandarizada__presente=0 AND extraccion_captura_audicion_compatible__presente=0 AND extraccion_captura_condiciones_sensoriales__presente=0 AND extraccion_captura_demora_minutos__presente=0 AND extraccion_captura_edad_anios__presente=0 AND extraccion_captura_edicion_protocolo__presente=0 AND extraccion_captura_elementos_revisados__presente=0 AND extraccion_captura_ensayo_1__presente=0 AND extraccion_captura_ensayo_2__presente=0 AND extraccion_captura_ensayo_3__presente=0 AND extraccion_captura_ensayos__estado='AUSENTE' AND extraccion_captura_errores_observados__presente=0 AND extraccion_captura_escolaridad_anios__presente=0 AND extraccion_captura_espanol_nativo__presente=0 AND extraccion_captura_finalizada__presente=0 AND extraccion_captura_forma__presente=0 AND extraccion_captura_forma_l_aciertos__presente=0 AND extraccion_captura_forma_n_aciertos__presente=0 AND extraccion_captura_idioma__presente=0 AND extraccion_captura_instrumento__presente=0 AND extraccion_captura_lectoescritura__presente=0 AND extraccion_captura_nacimiento_bolivia__presente=0 AND extraccion_captura_protocolo__presente=0 AND extraccion_captura_puntaje_copia__presente=0 AND extraccion_captura_recuerdo_diferido__presente=0 AND extraccion_captura_residencia_bolivia__presente=0 AND extraccion_captura_segundos_parte_b__presente=0)),
    CHECK(extraccion_identidad_declarada__estado='PRESENTE' OR (extraccion_identidad_declarada_edicion_protocolo__presente=0 AND extraccion_identidad_declarada_forma__presente=0 AND extraccion_identidad_declarada_idioma__presente=0 AND extraccion_identidad_declarada_instrumento__presente=0)),
    CHECK(extraccion_identidad_requerida_metodo__estado='PRESENTE' OR (ext_ide_req_met_edi_pro__presente=0 AND extraccion_identidad_requerida_metodo_forma__presente=0 AND extraccion_identidad_requerida_metodo_idioma__presente=0 AND extraccion_identidad_requerida_metodo_instrumento__presente=0)),
    CHECK(extraccion_normalizacion__estado='PRESENTE' OR (extraccion_normalizacion_bruto__estado='AUSENTE' AND extraccion_normalizacion_componentes__estado='AUSENTE' AND extraccion_normalizacion_contexto_aplicacion__estado='AUSENTE' AND extraccion_normalizacion_diagnostico__presente=0 AND extraccion_normalizacion_limite__presente=0 AND extraccion_normalizacion_localizador__presente=0 AND extraccion_normalizacion_metodo__presente=0 AND extraccion_normalizacion_referencia__presente=0 AND extraccion_normalizacion_revision_profesional__presente=0)),
    CHECK(extraccion_normalizacion_bruto__estado='PRESENTE' OR (ext_nor_bru_aci_cla_sem__presente=0 AND ext_nor_bru_aci_esp__presente=0 AND ext_nor_bru_adm_est__presente=0 AND ext_nor_bru_aud_com__presente=0 AND ext_nor_bru_con_sen__presente=0 AND extraccion_normalizacion_bruto_demora_minutos__presente=0 AND ext_nor_bru_ele_rev__presente=0 AND extraccion_normalizacion_bruto_ensayos__estado='AUSENTE' AND extraccion_normalizacion_bruto_errores_observados__presente=0 AND extraccion_normalizacion_bruto_escolaridad_anios__presente=0 AND extraccion_normalizacion_bruto_espanol_nativo__presente=0 AND extraccion_normalizacion_bruto_finalizada__presente=0 AND extraccion_normalizacion_bruto_forma_l_aciertos__presente=0 AND extraccion_normalizacion_bruto_forma_n_aciertos__presente=0 AND extraccion_normalizacion_bruto_lectoescritura__presente=0 AND extraccion_normalizacion_bruto_nacimiento_bolivia__presente=0 AND extraccion_normalizacion_bruto_puntaje_copia__presente=0 AND extraccion_normalizacion_bruto_recuerdo_diferido__presente=0 AND extraccion_normalizacion_bruto_residencia_bolivia__presente=0 AND extraccion_normalizacion_bruto_segundos_parte_b__presente=0)),
    CHECK(extraccion_normalizacion_contexto_aplicacion__estado='PRESENTE' OR (ext_nor_con_apl_aci_cla_sem__presente=0 AND ext_nor_con_apl_aci_esp__presente=0 AND ext_nor_con_apl_adm_est__presente=0 AND ext_nor_con_apl_aud_com__presente=0 AND ext_nor_con_apl_con_sen__presente=0 AND ext_nor_con_apl_eda_ani__presente=0 AND ext_nor_con_apl_edi_pro__presente=0 AND ext_nor_con_apl_ele_rev__presente=0 AND ext_nor_con_apl_err_obs__presente=0 AND ext_nor_con_apl_esc_ani__presente=0 AND ext_nor_con_apl_esp_nat__presente=0 AND ext_nor_con_apl_fin__presente=0 AND ext_nor_con_apl_for__presente=0 AND ext_nor_con_apl_for_l_aci__presente=0 AND ext_nor_con_apl_for_n_aci__presente=0 AND ext_nor_con_apl_idi__presente=0 AND ext_nor_con_apl_ins__presente=0 AND ext_nor_con_apl_lec__presente=0 AND ext_nor_con_apl_nac_bol__presente=0 AND ext_nor_con_apl_pro__presente=0 AND ext_nor_con_apl_pun_cop__presente=0 AND ext_nor_con_apl_res_bol__presente=0 AND ext_nor_con_apl_seg_par_b__presente=0)),
    CHECK(fuente_edad__estado='PRESENTE' OR (fuente_edad_campo__presente=0 AND fuente_edad_fecha_aplicacion__presente=0 AND fuente_edad_fecha_nacimiento__presente=0 AND fuente_edad_registro__presente=0 AND fuente_edad_tabla__presente=0)),
    CHECK(instrumento_actual__estado='PRESENTE' OR (instrumento_actual_cod_instrumento__presente=0 AND instrumento_actual_codigo__presente=0 AND instrumento_actual_descripcion__presente=0 AND instrumento_actual_estado__presente=0 AND instrumento_actual_nombre__presente=0 AND instrumento_actual_puntaje_maximo__presente=0 AND instrumento_actual_tipo__presente=0 AND instrumento_actual_version__presente=0)),
    CHECK(perfil_captura_original__estado='PRESENTE' OR (perfil_captura_original_cod_instrumento__presente=0 AND perfil_captura_original_clave_instrumento__presente=0 AND perfil_captura_original_id__presente=0 AND perfil_captura_original_identidad_instrumento__estado='AUSENTE' AND perfil_captura_original_metodo__presente=0 AND perfil_captura_original_selectores__estado='AUSENTE' AND perfil_captura_original_version_instrumento__presente=0)),
    CHECK(perfil_captura_original_identidad_instrumento__estado='PRESENTE' OR (per_cap_ori_ide_ins_edi_pro__presente=0 AND per_cap_ori_ide_ins_for__presente=0 AND per_cap_ori_ide_ins_idi__presente=0 AND per_cap_ori_ide_ins_ins__presente=0)),
    CHECK(procedencia__estado='PRESENTE' OR (procedencia_cod_aplicacion_anterior__presente=0 AND procedencia_cod_documento_clinico__presente=0 AND procedencia_cod_usuario_registro__presente=0 AND procedencia_localizador_fuente__presente=0 AND procedencia_registrado_en__presente=0))
);

CREATE TABLE ret_extraccion_aplicabilidad_geriatrica_re_c003 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_extraccion_captura_ensayos_c004 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_capturas_extraccion_fuentes_entradas_c005 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicacion__presente INTEGER NOT NULL,
    aplicacion TEXT,
    bruto__presente INTEGER NOT NULL,
    bruto__tipo VARCHAR(6) NOT NULL CHECK(bruto__tipo IN ('NULL','BOOL','STRING')),
    bruto_logico INTEGER,
    bruto_texto TEXT,
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    pregunta__presente INTEGER NOT NULL,
    pregunta TEXT,
    respuesta__presente INTEGER NOT NULL,
    respuesta TEXT,
    valor__presente INTEGER NOT NULL,
    valor__tipo VARCHAR(6) NOT NULL CHECK(valor__tipo IN ('NULL','BOOL','FLOAT','INT')),
    valor_logico INTEGER,
    valor_real REAL,
    valor_entero BIGINT,
    valor__cero_negativo INTEGER NOT NULL DEFAULT 0,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(aplicacion__presente IN (0,1)),
    CHECK(aplicacion__presente=1 OR aplicacion IS NULL),
    CHECK(bruto__presente IN (0,1)),
    CHECK(bruto__presente=1 OR bruto__tipo='NULL'),
    CHECK((bruto__tipo='NULL' AND bruto_logico IS NULL AND bruto_texto IS NULL) OR (bruto__tipo='BOOL' AND bruto_logico IS NOT NULL AND bruto_texto IS NULL) OR (bruto__tipo='STRING' AND bruto_texto IS NOT NULL AND bruto_logico IS NULL)),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(pregunta__presente IN (0,1)),
    CHECK(pregunta__presente=1 OR pregunta IS NULL),
    CHECK(respuesta__presente IN (0,1)),
    CHECK(respuesta__presente=1 OR respuesta IS NULL),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor__tipo='NULL'),
    CHECK((valor__tipo='NULL' AND valor_logico IS NULL AND valor_real IS NULL AND valor_entero IS NULL) OR (valor__tipo='BOOL' AND valor_logico IS NOT NULL AND valor_real IS NULL AND valor_entero IS NULL) OR (valor__tipo='FLOAT' AND valor_real IS NOT NULL AND valor_logico IS NULL AND valor_entero IS NULL) OR (valor__tipo='INT' AND valor_entero IS NOT NULL AND valor_logico IS NULL AND valor_real IS NULL)),
    CHECK(valor__cero_negativo=0 OR (valor__tipo='FLOAT' AND valor_real=0)),
    CHECK(dto__estado='PRESENTE' OR (aplicacion__presente=0 AND bruto__presente=0 AND campo__presente=0 AND pregunta__presente=0 AND respuesta__presente=0 AND valor__presente=0))
);

CREATE TABLE ret_normalizacion_bruto_ensayos_c006 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_extraccion_normalizacion_componentes_c007 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    clasificacion_clinica__presente INTEGER NOT NULL,
    clasificacion_clinica TEXT,
    de_residual__presente INTEGER NOT NULL,
    de_residual REAL,
    de_residual__cero_negativo INTEGER NOT NULL DEFAULT 0,
    direccion__presente INTEGER NOT NULL,
    direccion TEXT,
    esperado__presente INTEGER NOT NULL,
    esperado REAL,
    esperado__cero_negativo INTEGER NOT NULL DEFAULT 0,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    interpretacion_matematica__estado VARCHAR(8) NOT NULL CHECK(interpretacion_matematica__estado IN ('AUSENTE','NULL','PRESENTE')),
    interpretacion_matematica_clasificacion_clinica__presente INTEGER NOT NULL,
    interpretacion_matematica_clasificacion_clinica TEXT,
    interpretacion_matematica_contrato__presente INTEGER NOT NULL,
    interpretacion_matematica_contrato TEXT,
    interpretacion_matematica_estado__presente INTEGER NOT NULL,
    interpretacion_matematica_estado TEXT,
    interpretacion_matematica_limite__presente INTEGER NOT NULL,
    interpretacion_matematica_limite TEXT,
    interpretacion_matematica_posicion__presente INTEGER NOT NULL,
    interpretacion_matematica_posicion TEXT,
    interpretacion_matematica_texto__presente INTEGER NOT NULL,
    interpretacion_matematica_texto TEXT,
    medida__presente INTEGER NOT NULL,
    medida TEXT,
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    puntuacion_bruta__presente INTEGER NOT NULL,
    puntuacion_bruta__tipo VARCHAR(6) NOT NULL CHECK(puntuacion_bruta__tipo IN ('NULL','FLOAT','INT')),
    puntuacion_bruta_real REAL,
    puntuacion_bruta_entero BIGINT,
    puntuacion_bruta__cero_negativo INTEGER NOT NULL DEFAULT 0,
    z__presente INTEGER NOT NULL,
    z REAL,
    z__cero_negativo INTEGER NOT NULL DEFAULT 0,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(clasificacion_clinica__presente IN (0,1)),
    CHECK(clasificacion_clinica__presente=1 OR clasificacion_clinica IS NULL),
    CHECK(de_residual__presente IN (0,1)),
    CHECK(de_residual__presente=1 OR de_residual IS NULL),
    CHECK(de_residual__cero_negativo=0 OR de_residual=0),
    CHECK(direccion__presente IN (0,1)),
    CHECK(direccion__presente=1 OR direccion IS NULL),
    CHECK(esperado__presente IN (0,1)),
    CHECK(esperado__presente=1 OR esperado IS NULL),
    CHECK(esperado__cero_negativo=0 OR esperado=0),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(interpretacion_matematica_clasificacion_clinica__presente IN (0,1)),
    CHECK(interpretacion_matematica_clasificacion_clinica__presente=1 OR interpretacion_matematica_clasificacion_clinica IS NULL),
    CHECK(interpretacion_matematica_contrato__presente IN (0,1)),
    CHECK(interpretacion_matematica_contrato__presente=1 OR interpretacion_matematica_contrato IS NULL),
    CHECK(interpretacion_matematica_estado__presente IN (0,1)),
    CHECK(interpretacion_matematica_estado__presente=1 OR interpretacion_matematica_estado IS NULL),
    CHECK(interpretacion_matematica_limite__presente IN (0,1)),
    CHECK(interpretacion_matematica_limite__presente=1 OR interpretacion_matematica_limite IS NULL),
    CHECK(interpretacion_matematica_posicion__presente IN (0,1)),
    CHECK(interpretacion_matematica_posicion__presente=1 OR interpretacion_matematica_posicion IS NULL),
    CHECK(interpretacion_matematica_texto__presente IN (0,1)),
    CHECK(interpretacion_matematica_texto__presente=1 OR interpretacion_matematica_texto IS NULL),
    CHECK(medida__presente IN (0,1)),
    CHECK(medida__presente=1 OR medida IS NULL),
    CHECK(puntuacion_bruta__presente IN (0,1)),
    CHECK(puntuacion_bruta__presente=1 OR puntuacion_bruta__tipo='NULL'),
    CHECK((puntuacion_bruta__tipo='NULL' AND puntuacion_bruta_real IS NULL AND puntuacion_bruta_entero IS NULL) OR (puntuacion_bruta__tipo='FLOAT' AND puntuacion_bruta_real IS NOT NULL AND puntuacion_bruta_entero IS NULL) OR (puntuacion_bruta__tipo='INT' AND puntuacion_bruta_entero IS NOT NULL AND puntuacion_bruta_real IS NULL)),
    CHECK(puntuacion_bruta__cero_negativo=0 OR (puntuacion_bruta__tipo='FLOAT' AND puntuacion_bruta_real=0)),
    CHECK(z__presente IN (0,1)),
    CHECK(z__presente=1 OR z IS NULL),
    CHECK(z__cero_negativo=0 OR z=0),
    CHECK(dto__estado='PRESENTE' OR (clasificacion_clinica__presente=0 AND de_residual__presente=0 AND direccion__presente=0 AND esperado__presente=0 AND estado__presente=0 AND interpretacion_matematica__estado='AUSENTE' AND medida__presente=0 AND motivos__estado='AUSENTE' AND puntuacion_bruta__presente=0 AND z__presente=0)),
    CHECK(interpretacion_matematica__estado='PRESENTE' OR (interpretacion_matematica_clasificacion_clinica__presente=0 AND interpretacion_matematica_contrato__presente=0 AND interpretacion_matematica_estado__presente=0 AND interpretacion_matematica_limite__presente=0 AND interpretacion_matematica_posicion__presente=0 AND interpretacion_matematica_texto__presente=0))
);

CREATE TABLE ret_capturas_capturas_motivos_c008 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_capturas_perfil_captura_original_selec_c009 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo__presente=0 AND cod_pregunta__presente=0))
);

CREATE TABLE ret_capturas_capturas_preguntas_declarativ_c010 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_instrumento__presente INTEGER NOT NULL,
    cod_instrumento TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    codigo__presente INTEGER NOT NULL,
    codigo TEXT,
    dominio__presente INTEGER NOT NULL,
    dominio TEXT,
    enunciado__presente INTEGER NOT NULL,
    enunciado TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    orden__presente INTEGER NOT NULL,
    orden BIGINT,
    puntaje_maximo__presente INTEGER NOT NULL,
    puntaje_maximo TEXT,
    tipo_respuesta__presente INTEGER NOT NULL,
    tipo_respuesta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(cod_instrumento__presente IN (0,1)),
    CHECK(cod_instrumento__presente=1 OR cod_instrumento IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(codigo__presente IN (0,1)),
    CHECK(codigo__presente=1 OR codigo IS NULL),
    CHECK(dominio__presente IN (0,1)),
    CHECK(dominio__presente=1 OR dominio IS NULL),
    CHECK(enunciado__presente IN (0,1)),
    CHECK(enunciado__presente=1 OR enunciado IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(orden__presente IN (0,1)),
    CHECK(orden__presente=1 OR orden IS NULL),
    CHECK(puntaje_maximo__presente IN (0,1)),
    CHECK(puntaje_maximo__presente=1 OR puntaje_maximo IS NULL),
    CHECK(tipo_respuesta__presente IN (0,1)),
    CHECK(tipo_respuesta__presente=1 OR tipo_respuesta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_instrumento__presente=0 AND cod_pregunta__presente=0 AND codigo__presente=0 AND dominio__presente=0 AND enunciado__presente=0 AND estado__presente=0 AND orden__presente=0 AND puntaje_maximo__presente=0 AND tipo_respuesta__presente=0))
);

CREATE TABLE ret_capturas_capturas_respuestas_originale_c011 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_aplicacion__presente INTEGER NOT NULL,
    cod_aplicacion TEXT,
    cod_opcion__presente INTEGER NOT NULL,
    cod_opcion TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    cod_respuesta__presente INTEGER NOT NULL,
    cod_respuesta TEXT,
    clave_pregunta__presente INTEGER NOT NULL,
    clave_pregunta TEXT,
    coherente__presente INTEGER NOT NULL,
    coherente INTEGER,
    dominio_original__presente INTEGER NOT NULL,
    dominio_original TEXT,
    estado_pregunta_actual__presente INTEGER NOT NULL,
    estado_pregunta_actual TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    opcion_actual__presente INTEGER NOT NULL,
    opcion_actual TEXT,
    pregunta__presente INTEGER NOT NULL,
    pregunta TEXT,
    pregunta_actual__estado VARCHAR(8) NOT NULL CHECK(pregunta_actual__estado IN ('AUSENTE','NULL','PRESENTE')),
    pregunta_actual_cod_instrumento__presente INTEGER NOT NULL,
    pregunta_actual_cod_instrumento TEXT,
    pregunta_actual_cod_pregunta__presente INTEGER NOT NULL,
    pregunta_actual_cod_pregunta TEXT,
    pregunta_actual_codigo__presente INTEGER NOT NULL,
    pregunta_actual_codigo TEXT,
    pregunta_actual_dominio__presente INTEGER NOT NULL,
    pregunta_actual_dominio TEXT,
    pregunta_actual_enunciado__presente INTEGER NOT NULL,
    pregunta_actual_enunciado TEXT,
    pregunta_actual_estado__presente INTEGER NOT NULL,
    pregunta_actual_estado TEXT,
    pregunta_actual_orden__presente INTEGER NOT NULL,
    pregunta_actual_orden BIGINT,
    pregunta_actual_puntaje_maximo__presente INTEGER NOT NULL,
    pregunta_actual_puntaje_maximo TEXT,
    pregunta_actual_tipo_respuesta__presente INTEGER NOT NULL,
    pregunta_actual_tipo_respuesta TEXT,
    puntaje__presente INTEGER NOT NULL,
    puntaje TEXT,
    tipo_respuesta__presente INTEGER NOT NULL,
    tipo_respuesta TEXT,
    valor_logico__presente INTEGER NOT NULL,
    valor_logico INTEGER,
    valor_numero__presente INTEGER NOT NULL,
    valor_numero TEXT,
    valor_texto__presente INTEGER NOT NULL,
    valor_texto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_aplicacion__presente IN (0,1)),
    CHECK(cod_aplicacion__presente=1 OR cod_aplicacion IS NULL),
    CHECK(cod_opcion__presente IN (0,1)),
    CHECK(cod_opcion__presente=1 OR cod_opcion IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(cod_respuesta__presente IN (0,1)),
    CHECK(cod_respuesta__presente=1 OR cod_respuesta IS NULL),
    CHECK(clave_pregunta__presente IN (0,1)),
    CHECK(clave_pregunta__presente=1 OR clave_pregunta IS NULL),
    CHECK(coherente__presente IN (0,1)),
    CHECK(coherente__presente=1 OR coherente IS NULL),
    CHECK(coherente IS NULL OR coherente IN (0,1)),
    CHECK(dominio_original__presente IN (0,1)),
    CHECK(dominio_original__presente=1 OR dominio_original IS NULL),
    CHECK(estado_pregunta_actual__presente IN (0,1)),
    CHECK(estado_pregunta_actual__presente=1 OR estado_pregunta_actual IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(opcion_actual__presente IN (0,1)),
    CHECK(opcion_actual__presente=1 OR opcion_actual IS NULL),
    CHECK(pregunta__presente IN (0,1)),
    CHECK(pregunta__presente=1 OR pregunta IS NULL),
    CHECK(pregunta_actual_cod_instrumento__presente IN (0,1)),
    CHECK(pregunta_actual_cod_instrumento__presente=1 OR pregunta_actual_cod_instrumento IS NULL),
    CHECK(pregunta_actual_cod_pregunta__presente IN (0,1)),
    CHECK(pregunta_actual_cod_pregunta__presente=1 OR pregunta_actual_cod_pregunta IS NULL),
    CHECK(pregunta_actual_codigo__presente IN (0,1)),
    CHECK(pregunta_actual_codigo__presente=1 OR pregunta_actual_codigo IS NULL),
    CHECK(pregunta_actual_dominio__presente IN (0,1)),
    CHECK(pregunta_actual_dominio__presente=1 OR pregunta_actual_dominio IS NULL),
    CHECK(pregunta_actual_enunciado__presente IN (0,1)),
    CHECK(pregunta_actual_enunciado__presente=1 OR pregunta_actual_enunciado IS NULL),
    CHECK(pregunta_actual_estado__presente IN (0,1)),
    CHECK(pregunta_actual_estado__presente=1 OR pregunta_actual_estado IS NULL),
    CHECK(pregunta_actual_orden__presente IN (0,1)),
    CHECK(pregunta_actual_orden__presente=1 OR pregunta_actual_orden IS NULL),
    CHECK(pregunta_actual_puntaje_maximo__presente IN (0,1)),
    CHECK(pregunta_actual_puntaje_maximo__presente=1 OR pregunta_actual_puntaje_maximo IS NULL),
    CHECK(pregunta_actual_tipo_respuesta__presente IN (0,1)),
    CHECK(pregunta_actual_tipo_respuesta__presente=1 OR pregunta_actual_tipo_respuesta IS NULL),
    CHECK(puntaje__presente IN (0,1)),
    CHECK(puntaje__presente=1 OR puntaje IS NULL),
    CHECK(tipo_respuesta__presente IN (0,1)),
    CHECK(tipo_respuesta__presente=1 OR tipo_respuesta IS NULL),
    CHECK(valor_logico__presente IN (0,1)),
    CHECK(valor_logico__presente=1 OR valor_logico IS NULL),
    CHECK(valor_logico IS NULL OR valor_logico IN (0,1)),
    CHECK(valor_numero__presente IN (0,1)),
    CHECK(valor_numero__presente=1 OR valor_numero IS NULL),
    CHECK(valor_texto__presente IN (0,1)),
    CHECK(valor_texto__presente=1 OR valor_texto IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_aplicacion__presente=0 AND cod_opcion__presente=0 AND cod_pregunta__presente=0 AND cod_respuesta__presente=0 AND clave_pregunta__presente=0 AND coherente__presente=0 AND dominio_original__presente=0 AND estado_pregunta_actual__presente=0 AND observacion__presente=0 AND opcion_actual__presente=0 AND pregunta__presente=0 AND pregunta_actual__estado='AUSENTE' AND puntaje__presente=0 AND tipo_respuesta__presente=0 AND valor_logico__presente=0 AND valor_numero__presente=0 AND valor_texto__presente=0)),
    CHECK(pregunta_actual__estado='PRESENTE' OR (pregunta_actual_cod_instrumento__presente=0 AND pregunta_actual_cod_pregunta__presente=0 AND pregunta_actual_codigo__presente=0 AND pregunta_actual_dominio__presente=0 AND pregunta_actual_enunciado__presente=0 AND pregunta_actual_estado__presente=0 AND pregunta_actual_orden__presente=0 AND pregunta_actual_puntaje_maximo__presente=0 AND pregunta_actual_tipo_respuesta__presente=0))
);

CREATE TABLE ret_capturas_capturas_vinculos_preguntas_a_c012 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    pertenece_al_instrumento__presente INTEGER NOT NULL,
    pertenece_al_instrumento INTEGER,
    pregunta_actual__estado VARCHAR(8) NOT NULL CHECK(pregunta_actual__estado IN ('AUSENTE','NULL','PRESENTE')),
    pregunta_actual_cod_instrumento__presente INTEGER NOT NULL,
    pregunta_actual_cod_instrumento TEXT,
    pregunta_actual_cod_pregunta__presente INTEGER NOT NULL,
    pregunta_actual_cod_pregunta TEXT,
    pregunta_actual_codigo__presente INTEGER NOT NULL,
    pregunta_actual_codigo TEXT,
    pregunta_actual_dominio__presente INTEGER NOT NULL,
    pregunta_actual_dominio TEXT,
    pregunta_actual_enunciado__presente INTEGER NOT NULL,
    pregunta_actual_enunciado TEXT,
    pregunta_actual_estado__presente INTEGER NOT NULL,
    pregunta_actual_estado TEXT,
    pregunta_actual_orden__presente INTEGER NOT NULL,
    pregunta_actual_orden BIGINT,
    pregunta_actual_puntaje_maximo__presente INTEGER NOT NULL,
    pregunta_actual_puntaje_maximo TEXT,
    pregunta_actual_tipo_respuesta__presente INTEGER NOT NULL,
    pregunta_actual_tipo_respuesta TEXT,
    selector__estado VARCHAR(8) NOT NULL CHECK(selector__estado IN ('AUSENTE','NULL','PRESENTE')),
    selector_campo__presente INTEGER NOT NULL,
    selector_campo TEXT,
    selector_cod_pregunta__presente INTEGER NOT NULL,
    selector_cod_pregunta TEXT,
    tiene_respuesta__presente INTEGER NOT NULL,
    tiene_respuesta INTEGER,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_capturas_capturas_c002(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(pertenece_al_instrumento__presente IN (0,1)),
    CHECK(pertenece_al_instrumento__presente=1 OR pertenece_al_instrumento IS NULL),
    CHECK(pertenece_al_instrumento IS NULL OR pertenece_al_instrumento IN (0,1)),
    CHECK(pregunta_actual_cod_instrumento__presente IN (0,1)),
    CHECK(pregunta_actual_cod_instrumento__presente=1 OR pregunta_actual_cod_instrumento IS NULL),
    CHECK(pregunta_actual_cod_pregunta__presente IN (0,1)),
    CHECK(pregunta_actual_cod_pregunta__presente=1 OR pregunta_actual_cod_pregunta IS NULL),
    CHECK(pregunta_actual_codigo__presente IN (0,1)),
    CHECK(pregunta_actual_codigo__presente=1 OR pregunta_actual_codigo IS NULL),
    CHECK(pregunta_actual_dominio__presente IN (0,1)),
    CHECK(pregunta_actual_dominio__presente=1 OR pregunta_actual_dominio IS NULL),
    CHECK(pregunta_actual_enunciado__presente IN (0,1)),
    CHECK(pregunta_actual_enunciado__presente=1 OR pregunta_actual_enunciado IS NULL),
    CHECK(pregunta_actual_estado__presente IN (0,1)),
    CHECK(pregunta_actual_estado__presente=1 OR pregunta_actual_estado IS NULL),
    CHECK(pregunta_actual_orden__presente IN (0,1)),
    CHECK(pregunta_actual_orden__presente=1 OR pregunta_actual_orden IS NULL),
    CHECK(pregunta_actual_puntaje_maximo__presente IN (0,1)),
    CHECK(pregunta_actual_puntaje_maximo__presente=1 OR pregunta_actual_puntaje_maximo IS NULL),
    CHECK(pregunta_actual_tipo_respuesta__presente IN (0,1)),
    CHECK(pregunta_actual_tipo_respuesta__presente=1 OR pregunta_actual_tipo_respuesta IS NULL),
    CHECK(selector_campo__presente IN (0,1)),
    CHECK(selector_campo__presente=1 OR selector_campo IS NULL),
    CHECK(selector_cod_pregunta__presente IN (0,1)),
    CHECK(selector_cod_pregunta__presente=1 OR selector_cod_pregunta IS NULL),
    CHECK(tiene_respuesta__presente IN (0,1)),
    CHECK(tiene_respuesta__presente=1 OR tiene_respuesta IS NULL),
    CHECK(tiene_respuesta IS NULL OR tiene_respuesta IN (0,1)),
    CHECK(dto__estado='PRESENTE' OR (pertenece_al_instrumento__presente=0 AND pregunta_actual__estado='AUSENTE' AND selector__estado='AUSENTE' AND tiene_respuesta__presente=0)),
    CHECK(pregunta_actual__estado='PRESENTE' OR (pregunta_actual_cod_instrumento__presente=0 AND pregunta_actual_cod_pregunta__presente=0 AND pregunta_actual_codigo__presente=0 AND pregunta_actual_dominio__presente=0 AND pregunta_actual_enunciado__presente=0 AND pregunta_actual_estado__presente=0 AND pregunta_actual_orden__presente=0 AND pregunta_actual_puntaje_maximo__presente=0 AND pregunta_actual_tipo_respuesta__presente=0)),
    CHECK(selector__estado='PRESENTE' OR (selector_campo__presente=0 AND selector_cod_pregunta__presente=0))
);

CREATE TABLE ret_contenido_conocimiento_contratos_relac_c013 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    habilita_participacion__presente INTEGER NOT NULL,
    habilita_participacion INTEGER,
    modalidades__estado VARCHAR(8) NOT NULL CHECK(modalidades__estado IN ('AUSENTE','NULL','PRESENTE')),
    modalidades__cantidad BIGINT NOT NULL CHECK(modalidades__cantidad>=0),
    tipo_destino__presente INTEGER NOT NULL,
    tipo_destino TEXT,
    tipo_origen__presente INTEGER NOT NULL,
    tipo_origen TEXT,
    tipo_relacion__presente INTEGER NOT NULL,
    tipo_relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(habilita_participacion__presente IN (0,1)),
    CHECK(habilita_participacion__presente=1 OR habilita_participacion IS NULL),
    CHECK(habilita_participacion IS NULL OR habilita_participacion IN (0,1)),
    CHECK(modalidades__estado='PRESENTE' OR modalidades__cantidad=0),
    CHECK(tipo_destino__presente IN (0,1)),
    CHECK(tipo_destino__presente=1 OR tipo_destino IS NULL),
    CHECK(tipo_origen__presente IN (0,1)),
    CHECK(tipo_origen__presente=1 OR tipo_origen IS NULL),
    CHECK(tipo_relacion__presente IN (0,1)),
    CHECK(tipo_relacion__presente=1 OR tipo_relacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (habilita_participacion__presente=0 AND modalidades__estado='AUSENTE' AND tipo_destino__presente=0 AND tipo_origen__presente=0 AND tipo_relacion__presente=0))
);

CREATE TABLE ret_conocimiento_contratos_relaciones_moda_c014 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_conocimiento_contratos_relac_c013(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_conocimiento_tablas_condiciones_regla__c015 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_condicion_regla__presente INTEGER NOT NULL,
    cod_condicion_regla TEXT,
    cod_regla_experta__presente INTEGER NOT NULL,
    cod_regla_experta TEXT,
    cod_valor_semantico__presente INTEGER NOT NULL,
    cod_valor_semantico TEXT,
    cod_variable_experta__presente INTEGER NOT NULL,
    cod_variable_experta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_condicion_regla__presente IN (0,1)),
    CHECK(cod_condicion_regla__presente=1 OR cod_condicion_regla IS NULL),
    CHECK(cod_regla_experta__presente IN (0,1)),
    CHECK(cod_regla_experta__presente=1 OR cod_regla_experta IS NULL),
    CHECK(cod_valor_semantico__presente IN (0,1)),
    CHECK(cod_valor_semantico__presente=1 OR cod_valor_semantico IS NULL),
    CHECK(cod_variable_experta__presente IN (0,1)),
    CHECK(cod_variable_experta__presente=1 OR cod_variable_experta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_condicion_regla__presente=0 AND cod_regla_experta__presente=0 AND cod_valor_semantico__presente=0 AND cod_variable_experta__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_consecuencias_regl_c016 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_consecuencia_regla__presente INTEGER NOT NULL,
    cod_consecuencia_regla TEXT,
    cod_criterio_dominio_r__presente INTEGER NOT NULL,
    cod_criterio_dominio_r TEXT,
    cod_regla_experta__presente INTEGER NOT NULL,
    cod_regla_experta TEXT,
    cod_valor_semantico__presente INTEGER NOT NULL,
    cod_valor_semantico TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_consecuencia_regla__presente IN (0,1)),
    CHECK(cod_consecuencia_regla__presente=1 OR cod_consecuencia_regla IS NULL),
    CHECK(cod_criterio_dominio_r__presente IN (0,1)),
    CHECK(cod_criterio_dominio_r__presente=1 OR cod_criterio_dominio_r IS NULL),
    CHECK(cod_regla_experta__presente IN (0,1)),
    CHECK(cod_regla_experta__presente=1 OR cod_regla_experta IS NULL),
    CHECK(cod_valor_semantico__presente IN (0,1)),
    CHECK(cod_valor_semantico__presente=1 OR cod_valor_semantico IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_consecuencia_regla__presente=0 AND cod_criterio_dominio_r__presente=0 AND cod_regla_experta__presente=0 AND cod_valor_semantico__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_criterios_dominios_c017 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_criterio_dominio_r__presente INTEGER NOT NULL,
    cod_criterio_dominio_r TEXT,
    cod_dominio_valores__presente INTEGER NOT NULL,
    cod_dominio_valores TEXT,
    cod_nodo_criterio__presente INTEGER NOT NULL,
    cod_nodo_criterio TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_criterio_dominio_r__presente IN (0,1)),
    CHECK(cod_criterio_dominio_r__presente=1 OR cod_criterio_dominio_r IS NULL),
    CHECK(cod_dominio_valores__presente IN (0,1)),
    CHECK(cod_dominio_valores__presente=1 OR cod_dominio_valores IS NULL),
    CHECK(cod_nodo_criterio__presente IN (0,1)),
    CHECK(cod_nodo_criterio__presente=1 OR cod_nodo_criterio IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_criterio_dominio_r__presente=0 AND cod_dominio_valores__presente=0 AND cod_nodo_criterio__presente=0 AND cod_usuario_creacion__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND observacion__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_dominios_valores_e_c018 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente INTEGER NOT NULL,
    cod_dominio_valores TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    clave_dominio__presente INTEGER NOT NULL,
    clave_dominio TEXT,
    descripcion__presente INTEGER NOT NULL,
    descripcion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    tipo_dominio__presente INTEGER NOT NULL,
    tipo_dominio TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente IN (0,1)),
    CHECK(cod_dominio_valores__presente=1 OR cod_dominio_valores IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(clave_dominio__presente IN (0,1)),
    CHECK(clave_dominio__presente=1 OR clave_dominio IS NULL),
    CHECK(descripcion__presente IN (0,1)),
    CHECK(descripcion__presente=1 OR descripcion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(tipo_dominio__presente IN (0,1)),
    CHECK(tipo_dominio__presente=1 OR tipo_dominio IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_dominio_valores__presente=0 AND cod_usuario_creacion__presente=0 AND cod_version_modelo__presente=0 AND clave_dominio__presente=0 AND descripcion__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND nombre__presente=0 AND observacion__presente=0 AND tipo_dominio__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_fuentes_datos_expe_c019 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo_personal__presente INTEGER NOT NULL,
    campo_personal TEXT,
    campo_pk_raiz__presente INTEGER NOT NULL,
    campo_pk_raiz TEXT,
    campo_residente__presente INTEGER NOT NULL,
    campo_residente TEXT,
    campo_temporal__presente INTEGER NOT NULL,
    campo_temporal TEXT,
    cod_fuente_dato_experta__presente INTEGER NOT NULL,
    cod_fuente_dato_experta TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    clave_fuente__presente INTEGER NOT NULL,
    clave_fuente TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    tabla_raiz__presente INTEGER NOT NULL,
    tabla_raiz TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(campo_personal__presente IN (0,1)),
    CHECK(campo_personal__presente=1 OR campo_personal IS NULL),
    CHECK(campo_pk_raiz__presente IN (0,1)),
    CHECK(campo_pk_raiz__presente=1 OR campo_pk_raiz IS NULL),
    CHECK(campo_residente__presente IN (0,1)),
    CHECK(campo_residente__presente=1 OR campo_residente IS NULL),
    CHECK(campo_temporal__presente IN (0,1)),
    CHECK(campo_temporal__presente=1 OR campo_temporal IS NULL),
    CHECK(cod_fuente_dato_experta__presente IN (0,1)),
    CHECK(cod_fuente_dato_experta__presente=1 OR cod_fuente_dato_experta IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(clave_fuente__presente IN (0,1)),
    CHECK(clave_fuente__presente=1 OR clave_fuente IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(tabla_raiz__presente IN (0,1)),
    CHECK(tabla_raiz__presente=1 OR tabla_raiz IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo_personal__presente=0 AND campo_pk_raiz__presente=0 AND campo_residente__presente=0 AND campo_temporal__presente=0 AND cod_fuente_dato_experta__presente=0 AND cod_version_modelo__presente=0 AND clave_fuente__presente=0 AND estado__presente=0 AND tabla_raiz__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_mapeos_valores_fue_c020 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_mapeo_valor_fuente__presente INTEGER NOT NULL,
    cod_mapeo_valor_fuente TEXT,
    cod_mapeo_variable_fuente__presente INTEGER NOT NULL,
    cod_mapeo_variable_fuente TEXT,
    cod_valor_semantico__presente INTEGER NOT NULL,
    cod_valor_semantico TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    estado_aprobacion__presente INTEGER NOT NULL,
    estado_aprobacion TEXT,
    fecha_vigencia_desde__presente INTEGER NOT NULL,
    fecha_vigencia_desde TEXT,
    fecha_vigencia_hasta__presente INTEGER NOT NULL,
    fecha_vigencia_hasta TEXT,
    tipo_valor_fuente__presente INTEGER NOT NULL,
    tipo_valor_fuente TEXT,
    valor_fuente_exacto__presente INTEGER NOT NULL,
    valor_fuente_exacto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_mapeo_valor_fuente__presente IN (0,1)),
    CHECK(cod_mapeo_valor_fuente__presente=1 OR cod_mapeo_valor_fuente IS NULL),
    CHECK(cod_mapeo_variable_fuente__presente IN (0,1)),
    CHECK(cod_mapeo_variable_fuente__presente=1 OR cod_mapeo_variable_fuente IS NULL),
    CHECK(cod_valor_semantico__presente IN (0,1)),
    CHECK(cod_valor_semantico__presente=1 OR cod_valor_semantico IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(estado_aprobacion__presente IN (0,1)),
    CHECK(estado_aprobacion__presente=1 OR estado_aprobacion IS NULL),
    CHECK(fecha_vigencia_desde__presente IN (0,1)),
    CHECK(fecha_vigencia_desde__presente=1 OR fecha_vigencia_desde IS NULL),
    CHECK(fecha_vigencia_hasta__presente IN (0,1)),
    CHECK(fecha_vigencia_hasta__presente=1 OR fecha_vigencia_hasta IS NULL),
    CHECK(tipo_valor_fuente__presente IN (0,1)),
    CHECK(tipo_valor_fuente__presente=1 OR tipo_valor_fuente IS NULL),
    CHECK(valor_fuente_exacto__presente IN (0,1)),
    CHECK(valor_fuente_exacto__presente=1 OR valor_fuente_exacto IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_mapeo_valor_fuente__presente=0 AND cod_mapeo_variable_fuente__presente=0 AND cod_valor_semantico__presente=0 AND estado__presente=0 AND estado_aprobacion__presente=0 AND fecha_vigencia_desde__presente=0 AND fecha_vigencia_hasta__presente=0 AND tipo_valor_fuente__presente=0 AND valor_fuente_exacto__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_mapeos_variables_f_c021 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo_valor__presente INTEGER NOT NULL,
    campo_valor TEXT,
    clave_selector__presente INTEGER NOT NULL,
    clave_selector TEXT,
    cod_fuente_dato_experta__presente INTEGER NOT NULL,
    cod_fuente_dato_experta TEXT,
    cod_mapeo_variable_fuente__presente INTEGER NOT NULL,
    cod_mapeo_variable_fuente TEXT,
    cod_variable_experta__presente INTEGER NOT NULL,
    cod_variable_experta TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    tipo_extraccion__presente INTEGER NOT NULL,
    tipo_extraccion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(campo_valor__presente IN (0,1)),
    CHECK(campo_valor__presente=1 OR campo_valor IS NULL),
    CHECK(clave_selector__presente IN (0,1)),
    CHECK(clave_selector__presente=1 OR clave_selector IS NULL),
    CHECK(cod_fuente_dato_experta__presente IN (0,1)),
    CHECK(cod_fuente_dato_experta__presente=1 OR cod_fuente_dato_experta IS NULL),
    CHECK(cod_mapeo_variable_fuente__presente IN (0,1)),
    CHECK(cod_mapeo_variable_fuente__presente=1 OR cod_mapeo_variable_fuente IS NULL),
    CHECK(cod_variable_experta__presente IN (0,1)),
    CHECK(cod_variable_experta__presente=1 OR cod_variable_experta IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(tipo_extraccion__presente IN (0,1)),
    CHECK(tipo_extraccion__presente=1 OR tipo_extraccion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo_valor__presente=0 AND clave_selector__presente=0 AND cod_fuente_dato_experta__presente=0 AND cod_mapeo_variable_fuente__presente=0 AND cod_variable_experta__presente=0 AND cod_version_modelo__presente=0 AND estado__presente=0 AND tipo_extraccion__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_nodos_semanticos_c022 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_nodo_semantico__presente INTEGER NOT NULL,
    cod_nodo_semantico TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    clave_semantico__presente INTEGER NOT NULL,
    clave_semantico TEXT,
    definicion__presente INTEGER NOT NULL,
    definicion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    tipo_nodo__presente INTEGER NOT NULL,
    tipo_nodo TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_nodo_semantico__presente IN (0,1)),
    CHECK(cod_nodo_semantico__presente=1 OR cod_nodo_semantico IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(clave_semantico__presente IN (0,1)),
    CHECK(clave_semantico__presente=1 OR clave_semantico IS NULL),
    CHECK(definicion__presente IN (0,1)),
    CHECK(definicion__presente=1 OR definicion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(tipo_nodo__presente IN (0,1)),
    CHECK(tipo_nodo__presente=1 OR tipo_nodo IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_nodo_semantico__presente=0 AND cod_usuario_creacion__presente=0 AND cod_version_modelo__presente=0 AND clave_semantico__presente=0 AND definicion__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND nombre__presente=0 AND observacion__presente=0 AND tipo_nodo__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_reglas_expertas_c023 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_regla_anterior__presente INTEGER NOT NULL,
    cod_regla_anterior TEXT,
    cod_regla_experta__presente INTEGER NOT NULL,
    cod_regla_experta TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_usuario_revision__presente INTEGER NOT NULL,
    cod_usuario_revision TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    clave_regla__presente INTEGER NOT NULL,
    clave_regla TEXT,
    descripcion__presente INTEGER NOT NULL,
    descripcion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    estado_revision__presente INTEGER NOT NULL,
    estado_revision TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    fecha_revision__presente INTEGER NOT NULL,
    fecha_revision TEXT,
    justificacion_revision__presente INTEGER NOT NULL,
    justificacion_revision TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    tipo_regla__presente INTEGER NOT NULL,
    tipo_regla TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_regla_anterior__presente IN (0,1)),
    CHECK(cod_regla_anterior__presente=1 OR cod_regla_anterior IS NULL),
    CHECK(cod_regla_experta__presente IN (0,1)),
    CHECK(cod_regla_experta__presente=1 OR cod_regla_experta IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_usuario_revision__presente IN (0,1)),
    CHECK(cod_usuario_revision__presente=1 OR cod_usuario_revision IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(clave_regla__presente IN (0,1)),
    CHECK(clave_regla__presente=1 OR clave_regla IS NULL),
    CHECK(descripcion__presente IN (0,1)),
    CHECK(descripcion__presente=1 OR descripcion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(estado_revision__presente IN (0,1)),
    CHECK(estado_revision__presente=1 OR estado_revision IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(fecha_revision__presente IN (0,1)),
    CHECK(fecha_revision__presente=1 OR fecha_revision IS NULL),
    CHECK(justificacion_revision__presente IN (0,1)),
    CHECK(justificacion_revision__presente=1 OR justificacion_revision IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(tipo_regla__presente IN (0,1)),
    CHECK(tipo_regla__presente=1 OR tipo_regla IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_regla_anterior__presente=0 AND cod_regla_experta__presente=0 AND cod_usuario_creacion__presente=0 AND cod_usuario_revision__presente=0 AND cod_version_modelo__presente=0 AND clave_regla__presente=0 AND descripcion__presente=0 AND estado__presente=0 AND estado_revision__presente=0 AND fecha_creacion__presente=0 AND fecha_revision__presente=0 AND justificacion_revision__presente=0 AND nombre__presente=0 AND observacion__presente=0 AND tipo_regla__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_relaciones_semanti_c024 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_nodo_destino__presente INTEGER NOT NULL,
    cod_nodo_destino TEXT,
    cod_nodo_origen__presente INTEGER NOT NULL,
    cod_nodo_origen TEXT,
    cod_relacion_semantica__presente INTEGER NOT NULL,
    cod_relacion_semantica TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    modalidad_relacion__presente INTEGER NOT NULL,
    modalidad_relacion TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    significado__presente INTEGER NOT NULL,
    significado TEXT,
    tipo_relacion__presente INTEGER NOT NULL,
    tipo_relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_nodo_destino__presente IN (0,1)),
    CHECK(cod_nodo_destino__presente=1 OR cod_nodo_destino IS NULL),
    CHECK(cod_nodo_origen__presente IN (0,1)),
    CHECK(cod_nodo_origen__presente=1 OR cod_nodo_origen IS NULL),
    CHECK(cod_relacion_semantica__presente IN (0,1)),
    CHECK(cod_relacion_semantica__presente=1 OR cod_relacion_semantica IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(modalidad_relacion__presente IN (0,1)),
    CHECK(modalidad_relacion__presente=1 OR modalidad_relacion IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(significado__presente IN (0,1)),
    CHECK(significado__presente=1 OR significado IS NULL),
    CHECK(tipo_relacion__presente IN (0,1)),
    CHECK(tipo_relacion__presente=1 OR tipo_relacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_nodo_destino__presente=0 AND cod_nodo_origen__presente=0 AND cod_relacion_semantica__presente=0 AND cod_usuario_creacion__presente=0 AND cod_version_modelo__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND modalidad_relacion__presente=0 AND observacion__presente=0 AND significado__presente=0 AND tipo_relacion__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_valores_semanticos_c025 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente INTEGER NOT NULL,
    cod_dominio_valores TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_valor_semantico__presente INTEGER NOT NULL,
    cod_valor_semantico TEXT,
    clave_valor__presente INTEGER NOT NULL,
    clave_valor TEXT,
    definicion_semantica__presente INTEGER NOT NULL,
    definicion_semantica TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    estado_aprobacion__presente INTEGER NOT NULL,
    estado_aprobacion TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente IN (0,1)),
    CHECK(cod_dominio_valores__presente=1 OR cod_dominio_valores IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_valor_semantico__presente IN (0,1)),
    CHECK(cod_valor_semantico__presente=1 OR cod_valor_semantico IS NULL),
    CHECK(clave_valor__presente IN (0,1)),
    CHECK(clave_valor__presente=1 OR clave_valor IS NULL),
    CHECK(definicion_semantica__presente IN (0,1)),
    CHECK(definicion_semantica__presente=1 OR definicion_semantica IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(estado_aprobacion__presente IN (0,1)),
    CHECK(estado_aprobacion__presente=1 OR estado_aprobacion IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_dominio_valores__presente=0 AND cod_usuario_creacion__presente=0 AND cod_valor_semantico__presente=0 AND clave_valor__presente=0 AND definicion_semantica__presente=0 AND estado__presente=0 AND estado_aprobacion__presente=0 AND fecha_creacion__presente=0 AND nombre__presente=0 AND observacion__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_variables_expertas_c026 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente INTEGER NOT NULL,
    cod_dominio_valores TEXT,
    cod_nodo_propietario_primario__presente INTEGER NOT NULL,
    cod_nodo_propietario_primario TEXT,
    cod_nodo_semantico__presente INTEGER NOT NULL,
    cod_nodo_semantico TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_variable_experta__presente INTEGER NOT NULL,
    cod_variable_experta TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    papel_inferencial__presente INTEGER NOT NULL,
    papel_inferencial TEXT,
    tipo_semantico__presente INTEGER NOT NULL,
    tipo_semantico TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente IN (0,1)),
    CHECK(cod_dominio_valores__presente=1 OR cod_dominio_valores IS NULL),
    CHECK(cod_nodo_propietario_primario__presente IN (0,1)),
    CHECK(cod_nodo_propietario_primario__presente=1 OR cod_nodo_propietario_primario IS NULL),
    CHECK(cod_nodo_semantico__presente IN (0,1)),
    CHECK(cod_nodo_semantico__presente=1 OR cod_nodo_semantico IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_variable_experta__presente IN (0,1)),
    CHECK(cod_variable_experta__presente=1 OR cod_variable_experta IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(papel_inferencial__presente IN (0,1)),
    CHECK(papel_inferencial__presente=1 OR papel_inferencial IS NULL),
    CHECK(tipo_semantico__presente IN (0,1)),
    CHECK(tipo_semantico__presente=1 OR tipo_semantico IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_dominio_valores__presente=0 AND cod_nodo_propietario_primario__presente=0 AND cod_nodo_semantico__presente=0 AND cod_usuario_creacion__presente=0 AND cod_variable_experta__presente=0 AND cod_version_modelo__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND observacion__presente=0 AND papel_inferencial__presente=0 AND tipo_semantico__presente=0))
);

CREATE TABLE ret_contenido_fuente_conceptual_nodos_c027 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    codigo__presente INTEGER NOT NULL,
    codigo TEXT,
    definicion__presente INTEGER NOT NULL,
    definicion TEXT,
    estado_fuente__presente INTEGER NOT NULL,
    estado_fuente TEXT,
    limites__presente INTEGER NOT NULL,
    limites TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    procedencia__estado VARCHAR(8) NOT NULL CHECK(procedencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia__cantidad BIGINT NOT NULL CHECK(procedencia__cantidad>=0),
    tipo__presente INTEGER NOT NULL,
    tipo TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(codigo__presente IN (0,1)),
    CHECK(codigo__presente=1 OR codigo IS NULL),
    CHECK(definicion__presente IN (0,1)),
    CHECK(definicion__presente=1 OR definicion IS NULL),
    CHECK(estado_fuente__presente IN (0,1)),
    CHECK(estado_fuente__presente=1 OR estado_fuente IS NULL),
    CHECK(limites__presente IN (0,1)),
    CHECK(limites__presente=1 OR limites IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(procedencia__estado='PRESENTE' OR procedencia__cantidad=0),
    CHECK(tipo__presente IN (0,1)),
    CHECK(tipo__presente=1 OR tipo IS NULL),
    CHECK(dto__estado='PRESENTE' OR (codigo__presente=0 AND definicion__presente=0 AND estado_fuente__presente=0 AND limites__presente=0 AND nombre__presente=0 AND procedencia__estado='AUSENTE' AND tipo__presente=0))
);

CREATE TABLE ret_fuente_conceptual_nodos_procedencia_c028 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_fuente_conceptual_nodos_c027(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_contenido_fuente_conceptual_relaciones_c029 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    codigo__presente INTEGER NOT NULL,
    codigo TEXT,
    destino__presente INTEGER NOT NULL,
    destino TEXT,
    estado_fuente__presente INTEGER NOT NULL,
    estado_fuente TEXT,
    fila__presente INTEGER NOT NULL,
    fila BIGINT,
    limites__presente INTEGER NOT NULL,
    limites TEXT,
    origen__presente INTEGER NOT NULL,
    origen TEXT,
    relacion__presente INTEGER NOT NULL,
    relacion TEXT,
    significado__presente INTEGER NOT NULL,
    significado TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(codigo__presente IN (0,1)),
    CHECK(codigo__presente=1 OR codigo IS NULL),
    CHECK(destino__presente IN (0,1)),
    CHECK(destino__presente=1 OR destino IS NULL),
    CHECK(estado_fuente__presente IN (0,1)),
    CHECK(estado_fuente__presente=1 OR estado_fuente IS NULL),
    CHECK(fila__presente IN (0,1)),
    CHECK(fila__presente=1 OR fila IS NULL),
    CHECK(limites__presente IN (0,1)),
    CHECK(limites__presente=1 OR limites IS NULL),
    CHECK(origen__presente IN (0,1)),
    CHECK(origen__presente=1 OR origen IS NULL),
    CHECK(relacion__presente IN (0,1)),
    CHECK(relacion__presente=1 OR relacion IS NULL),
    CHECK(significado__presente IN (0,1)),
    CHECK(significado__presente=1 OR significado IS NULL),
    CHECK(dto__estado='PRESENTE' OR (codigo__presente=0 AND destino__presente=0 AND estado_fuente__presente=0 AND fila__presente=0 AND limites__presente=0 AND origen__presente=0 AND relacion__presente=0 AND significado__presente=0))
);

CREATE TABLE ret_perfil_instrumental_comprobacion_adqui_c030 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicabilidad__estado VARCHAR(8) NOT NULL CHECK(aplicabilidad__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicabilidad_alcance_etario_compatible__presente INTEGER NOT NULL,
    aplicabilidad_alcance_etario_compatible INTEGER,
    aplicabilidad_autoriza_interpretacion_clinica__presente INTEGER NOT NULL,
    aplicabilidad_autoriza_interpretacion_clinica INTEGER,
    aplicabilidad_edad_en_aplicacion__presente INTEGER NOT NULL,
    aplicabilidad_edad_en_aplicacion BIGINT,
    aplicabilidad_escolaridad_original__presente INTEGER NOT NULL,
    aplicabilidad_escolaridad_original BIGINT,
    aplicabilidad_fuente__presente INTEGER NOT NULL,
    aplicabilidad_fuente TEXT,
    aplicabilidad_idioma_declarado__presente INTEGER NOT NULL,
    aplicabilidad_idioma_declarado TEXT,
    aplicabilidad_motivos__estado VARCHAR(8) NOT NULL CHECK(aplicabilidad_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicabilidad_pertenece_a_poblacion_objetivo__presente INTEGER NOT NULL,
    aplicabilidad_pertenece_a_poblacion_objetivo INTEGER,
    aplicabilidad_poblacion_objetivo__presente INTEGER NOT NULL,
    aplicabilidad_poblacion_objetivo TEXT,
    aplicabilidad_rango_edad_referencia__estado VARCHAR(8) NOT NULL CHECK(aplicabilidad_rango_edad_referencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicabilidad_rango_edad_referencia_maxima__presente INTEGER NOT NULL,
    aplicabilidad_rango_edad_referencia_maxima BIGINT,
    aplicabilidad_rango_edad_referencia_minima__presente INTEGER NOT NULL,
    aplicabilidad_rango_edad_referencia_minima BIGINT,
    aplicabilidad_requiere_verificar__estado VARCHAR(8) NOT NULL CHECK(aplicabilidad_requiere_verificar__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicabilidad_requiere_verificar__cantidad BIGINT NOT NULL CHECK(aplicabilidad_requiere_verificar__cantidad>=0),
    calculos__estado VARCHAR(8) NOT NULL CHECK(calculos__estado IN ('AUSENTE','NULL','PRESENTE')),
    calculos__cantidad BIGINT NOT NULL CHECK(calculos__cantidad>=0),
    cod_aplicacion__presente INTEGER NOT NULL,
    cod_aplicacion TEXT,
    cod_instrumento__presente INTEGER NOT NULL,
    cod_instrumento TEXT,
    edad_en_aplicacion__presente INTEGER NOT NULL,
    edad_en_aplicacion BIGINT,
    entradas__estado VARCHAR(8) NOT NULL CHECK(entradas__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_aciertos_clave_semantica__estado VARCHAR(8) NOT NULL CHECK(entradas_aciertos_clave_semantica__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_aciertos_clave_semantica_campo__presente INTEGER NOT NULL,
    entradas_aciertos_clave_semantica_campo TEXT,
    entradas_aciertos_clave_semantica_cod_pregunta__presente INTEGER NOT NULL,
    entradas_aciertos_clave_semantica_cod_pregunta TEXT,
    entradas_aciertos_clave_semantica_cod_respuesta__presente INTEGER NOT NULL,
    entradas_aciertos_clave_semantica_cod_respuesta TEXT,
    entradas_aciertos_clave_semantica_estado__presente INTEGER NOT NULL,
    entradas_aciertos_clave_semantica_estado TEXT,
    entradas_aciertos_clave_semantica_tipo__presente INTEGER NOT NULL,
    entradas_aciertos_clave_semantica_tipo TEXT,
    entradas_aciertos_espontaneos__estado VARCHAR(8) NOT NULL CHECK(entradas_aciertos_espontaneos__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_aciertos_espontaneos_campo__presente INTEGER NOT NULL,
    entradas_aciertos_espontaneos_campo TEXT,
    entradas_aciertos_espontaneos_cod_pregunta__presente INTEGER NOT NULL,
    entradas_aciertos_espontaneos_cod_pregunta TEXT,
    entradas_aciertos_espontaneos_cod_respuesta__presente INTEGER NOT NULL,
    entradas_aciertos_espontaneos_cod_respuesta TEXT,
    entradas_aciertos_espontaneos_estado__presente INTEGER NOT NULL,
    entradas_aciertos_espontaneos_estado TEXT,
    entradas_aciertos_espontaneos_tipo__presente INTEGER NOT NULL,
    entradas_aciertos_espontaneos_tipo TEXT,
    entradas_administracion_estandarizada__estado VARCHAR(8) NOT NULL CHECK(entradas_administracion_estandarizada__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_administracion_estandarizada_campo__presente INTEGER NOT NULL,
    entradas_administracion_estandarizada_campo TEXT,
    ent_adm_est_cod_pre__presente INTEGER NOT NULL,
    ent_adm_est_cod_pre TEXT,
    ent_adm_est_cod_res__presente INTEGER NOT NULL,
    ent_adm_est_cod_res TEXT,
    entradas_administracion_estandarizada_estado__presente INTEGER NOT NULL,
    entradas_administracion_estandarizada_estado TEXT,
    entradas_administracion_estandarizada_tipo__presente INTEGER NOT NULL,
    entradas_administracion_estandarizada_tipo TEXT,
    entradas_audicion_compatible__estado VARCHAR(8) NOT NULL CHECK(entradas_audicion_compatible__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_audicion_compatible_campo__presente INTEGER NOT NULL,
    entradas_audicion_compatible_campo TEXT,
    entradas_audicion_compatible_cod_pregunta__presente INTEGER NOT NULL,
    entradas_audicion_compatible_cod_pregunta TEXT,
    entradas_audicion_compatible_cod_respuesta__presente INTEGER NOT NULL,
    entradas_audicion_compatible_cod_respuesta TEXT,
    entradas_audicion_compatible_estado__presente INTEGER NOT NULL,
    entradas_audicion_compatible_estado TEXT,
    entradas_audicion_compatible_tipo__presente INTEGER NOT NULL,
    entradas_audicion_compatible_tipo TEXT,
    entradas_condiciones_sensoriales__estado VARCHAR(8) NOT NULL CHECK(entradas_condiciones_sensoriales__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_condiciones_sensoriales_campo__presente INTEGER NOT NULL,
    entradas_condiciones_sensoriales_campo TEXT,
    entradas_condiciones_sensoriales_cod_pregunta__presente INTEGER NOT NULL,
    entradas_condiciones_sensoriales_cod_pregunta TEXT,
    entradas_condiciones_sensoriales_cod_respuesta__presente INTEGER NOT NULL,
    entradas_condiciones_sensoriales_cod_respuesta TEXT,
    entradas_condiciones_sensoriales_estado__presente INTEGER NOT NULL,
    entradas_condiciones_sensoriales_estado TEXT,
    entradas_condiciones_sensoriales_tipo__presente INTEGER NOT NULL,
    entradas_condiciones_sensoriales_tipo TEXT,
    entradas_demora_minutos__estado VARCHAR(8) NOT NULL CHECK(entradas_demora_minutos__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_demora_minutos_campo__presente INTEGER NOT NULL,
    entradas_demora_minutos_campo TEXT,
    entradas_demora_minutos_cod_pregunta__presente INTEGER NOT NULL,
    entradas_demora_minutos_cod_pregunta TEXT,
    entradas_demora_minutos_cod_respuesta__presente INTEGER NOT NULL,
    entradas_demora_minutos_cod_respuesta TEXT,
    entradas_demora_minutos_estado__presente INTEGER NOT NULL,
    entradas_demora_minutos_estado TEXT,
    entradas_demora_minutos_tipo__presente INTEGER NOT NULL,
    entradas_demora_minutos_tipo TEXT,
    entradas_elementos_revisados__estado VARCHAR(8) NOT NULL CHECK(entradas_elementos_revisados__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_elementos_revisados_campo__presente INTEGER NOT NULL,
    entradas_elementos_revisados_campo TEXT,
    entradas_elementos_revisados_cod_pregunta__presente INTEGER NOT NULL,
    entradas_elementos_revisados_cod_pregunta TEXT,
    entradas_elementos_revisados_cod_respuesta__presente INTEGER NOT NULL,
    entradas_elementos_revisados_cod_respuesta TEXT,
    entradas_elementos_revisados_estado__presente INTEGER NOT NULL,
    entradas_elementos_revisados_estado TEXT,
    entradas_elementos_revisados_tipo__presente INTEGER NOT NULL,
    entradas_elementos_revisados_tipo TEXT,
    entradas_ensayo_1__estado VARCHAR(8) NOT NULL CHECK(entradas_ensayo_1__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_ensayo_1_campo__presente INTEGER NOT NULL,
    entradas_ensayo_1_campo TEXT,
    entradas_ensayo_1_cod_pregunta__presente INTEGER NOT NULL,
    entradas_ensayo_1_cod_pregunta TEXT,
    entradas_ensayo_1_cod_respuesta__presente INTEGER NOT NULL,
    entradas_ensayo_1_cod_respuesta TEXT,
    entradas_ensayo_1_estado__presente INTEGER NOT NULL,
    entradas_ensayo_1_estado TEXT,
    entradas_ensayo_1_tipo__presente INTEGER NOT NULL,
    entradas_ensayo_1_tipo TEXT,
    entradas_ensayo_2__estado VARCHAR(8) NOT NULL CHECK(entradas_ensayo_2__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_ensayo_2_campo__presente INTEGER NOT NULL,
    entradas_ensayo_2_campo TEXT,
    entradas_ensayo_2_cod_pregunta__presente INTEGER NOT NULL,
    entradas_ensayo_2_cod_pregunta TEXT,
    entradas_ensayo_2_cod_respuesta__presente INTEGER NOT NULL,
    entradas_ensayo_2_cod_respuesta TEXT,
    entradas_ensayo_2_estado__presente INTEGER NOT NULL,
    entradas_ensayo_2_estado TEXT,
    entradas_ensayo_2_tipo__presente INTEGER NOT NULL,
    entradas_ensayo_2_tipo TEXT,
    entradas_ensayo_3__estado VARCHAR(8) NOT NULL CHECK(entradas_ensayo_3__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_ensayo_3_campo__presente INTEGER NOT NULL,
    entradas_ensayo_3_campo TEXT,
    entradas_ensayo_3_cod_pregunta__presente INTEGER NOT NULL,
    entradas_ensayo_3_cod_pregunta TEXT,
    entradas_ensayo_3_cod_respuesta__presente INTEGER NOT NULL,
    entradas_ensayo_3_cod_respuesta TEXT,
    entradas_ensayo_3_estado__presente INTEGER NOT NULL,
    entradas_ensayo_3_estado TEXT,
    entradas_ensayo_3_tipo__presente INTEGER NOT NULL,
    entradas_ensayo_3_tipo TEXT,
    entradas_errores_observados__estado VARCHAR(8) NOT NULL CHECK(entradas_errores_observados__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_errores_observados_campo__presente INTEGER NOT NULL,
    entradas_errores_observados_campo TEXT,
    entradas_errores_observados_cod_pregunta__presente INTEGER NOT NULL,
    entradas_errores_observados_cod_pregunta TEXT,
    entradas_errores_observados_cod_respuesta__presente INTEGER NOT NULL,
    entradas_errores_observados_cod_respuesta TEXT,
    entradas_errores_observados_estado__presente INTEGER NOT NULL,
    entradas_errores_observados_estado TEXT,
    entradas_errores_observados_tipo__presente INTEGER NOT NULL,
    entradas_errores_observados_tipo TEXT,
    entradas_escolaridad_anios__estado VARCHAR(8) NOT NULL CHECK(entradas_escolaridad_anios__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_escolaridad_anios_campo__presente INTEGER NOT NULL,
    entradas_escolaridad_anios_campo TEXT,
    entradas_escolaridad_anios_cod_pregunta__presente INTEGER NOT NULL,
    entradas_escolaridad_anios_cod_pregunta TEXT,
    entradas_escolaridad_anios_cod_respuesta__presente INTEGER NOT NULL,
    entradas_escolaridad_anios_cod_respuesta TEXT,
    entradas_escolaridad_anios_estado__presente INTEGER NOT NULL,
    entradas_escolaridad_anios_estado TEXT,
    entradas_escolaridad_anios_tipo__presente INTEGER NOT NULL,
    entradas_escolaridad_anios_tipo TEXT,
    entradas_espanol_nativo__estado VARCHAR(8) NOT NULL CHECK(entradas_espanol_nativo__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_espanol_nativo_campo__presente INTEGER NOT NULL,
    entradas_espanol_nativo_campo TEXT,
    entradas_espanol_nativo_cod_pregunta__presente INTEGER NOT NULL,
    entradas_espanol_nativo_cod_pregunta TEXT,
    entradas_espanol_nativo_cod_respuesta__presente INTEGER NOT NULL,
    entradas_espanol_nativo_cod_respuesta TEXT,
    entradas_espanol_nativo_estado__presente INTEGER NOT NULL,
    entradas_espanol_nativo_estado TEXT,
    entradas_espanol_nativo_tipo__presente INTEGER NOT NULL,
    entradas_espanol_nativo_tipo TEXT,
    entradas_finalizada__estado VARCHAR(8) NOT NULL CHECK(entradas_finalizada__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_finalizada_campo__presente INTEGER NOT NULL,
    entradas_finalizada_campo TEXT,
    entradas_finalizada_cod_pregunta__presente INTEGER NOT NULL,
    entradas_finalizada_cod_pregunta TEXT,
    entradas_finalizada_cod_respuesta__presente INTEGER NOT NULL,
    entradas_finalizada_cod_respuesta TEXT,
    entradas_finalizada_estado__presente INTEGER NOT NULL,
    entradas_finalizada_estado TEXT,
    entradas_finalizada_tipo__presente INTEGER NOT NULL,
    entradas_finalizada_tipo TEXT,
    entradas_forma_l_aciertos__estado VARCHAR(8) NOT NULL CHECK(entradas_forma_l_aciertos__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_forma_l_aciertos_campo__presente INTEGER NOT NULL,
    entradas_forma_l_aciertos_campo TEXT,
    entradas_forma_l_aciertos_cod_pregunta__presente INTEGER NOT NULL,
    entradas_forma_l_aciertos_cod_pregunta TEXT,
    entradas_forma_l_aciertos_cod_respuesta__presente INTEGER NOT NULL,
    entradas_forma_l_aciertos_cod_respuesta TEXT,
    entradas_forma_l_aciertos_estado__presente INTEGER NOT NULL,
    entradas_forma_l_aciertos_estado TEXT,
    entradas_forma_l_aciertos_tipo__presente INTEGER NOT NULL,
    entradas_forma_l_aciertos_tipo TEXT,
    entradas_forma_n_aciertos__estado VARCHAR(8) NOT NULL CHECK(entradas_forma_n_aciertos__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_forma_n_aciertos_campo__presente INTEGER NOT NULL,
    entradas_forma_n_aciertos_campo TEXT,
    entradas_forma_n_aciertos_cod_pregunta__presente INTEGER NOT NULL,
    entradas_forma_n_aciertos_cod_pregunta TEXT,
    entradas_forma_n_aciertos_cod_respuesta__presente INTEGER NOT NULL,
    entradas_forma_n_aciertos_cod_respuesta TEXT,
    entradas_forma_n_aciertos_estado__presente INTEGER NOT NULL,
    entradas_forma_n_aciertos_estado TEXT,
    entradas_forma_n_aciertos_tipo__presente INTEGER NOT NULL,
    entradas_forma_n_aciertos_tipo TEXT,
    entradas_lectoescritura__estado VARCHAR(8) NOT NULL CHECK(entradas_lectoescritura__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_lectoescritura_campo__presente INTEGER NOT NULL,
    entradas_lectoescritura_campo TEXT,
    entradas_lectoescritura_cod_pregunta__presente INTEGER NOT NULL,
    entradas_lectoescritura_cod_pregunta TEXT,
    entradas_lectoescritura_cod_respuesta__presente INTEGER NOT NULL,
    entradas_lectoescritura_cod_respuesta TEXT,
    entradas_lectoescritura_estado__presente INTEGER NOT NULL,
    entradas_lectoescritura_estado TEXT,
    entradas_lectoescritura_tipo__presente INTEGER NOT NULL,
    entradas_lectoescritura_tipo TEXT,
    entradas_nacimiento_bolivia__estado VARCHAR(8) NOT NULL CHECK(entradas_nacimiento_bolivia__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_nacimiento_bolivia_campo__presente INTEGER NOT NULL,
    entradas_nacimiento_bolivia_campo TEXT,
    entradas_nacimiento_bolivia_cod_pregunta__presente INTEGER NOT NULL,
    entradas_nacimiento_bolivia_cod_pregunta TEXT,
    entradas_nacimiento_bolivia_cod_respuesta__presente INTEGER NOT NULL,
    entradas_nacimiento_bolivia_cod_respuesta TEXT,
    entradas_nacimiento_bolivia_estado__presente INTEGER NOT NULL,
    entradas_nacimiento_bolivia_estado TEXT,
    entradas_nacimiento_bolivia_tipo__presente INTEGER NOT NULL,
    entradas_nacimiento_bolivia_tipo TEXT,
    entradas_puntaje_copia__estado VARCHAR(8) NOT NULL CHECK(entradas_puntaje_copia__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_puntaje_copia_campo__presente INTEGER NOT NULL,
    entradas_puntaje_copia_campo TEXT,
    entradas_puntaje_copia_cod_pregunta__presente INTEGER NOT NULL,
    entradas_puntaje_copia_cod_pregunta TEXT,
    entradas_puntaje_copia_cod_respuesta__presente INTEGER NOT NULL,
    entradas_puntaje_copia_cod_respuesta TEXT,
    entradas_puntaje_copia_estado__presente INTEGER NOT NULL,
    entradas_puntaje_copia_estado TEXT,
    entradas_puntaje_copia_tipo__presente INTEGER NOT NULL,
    entradas_puntaje_copia_tipo TEXT,
    entradas_recuerdo_diferido__estado VARCHAR(8) NOT NULL CHECK(entradas_recuerdo_diferido__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_recuerdo_diferido_campo__presente INTEGER NOT NULL,
    entradas_recuerdo_diferido_campo TEXT,
    entradas_recuerdo_diferido_cod_pregunta__presente INTEGER NOT NULL,
    entradas_recuerdo_diferido_cod_pregunta TEXT,
    entradas_recuerdo_diferido_cod_respuesta__presente INTEGER NOT NULL,
    entradas_recuerdo_diferido_cod_respuesta TEXT,
    entradas_recuerdo_diferido_estado__presente INTEGER NOT NULL,
    entradas_recuerdo_diferido_estado TEXT,
    entradas_recuerdo_diferido_tipo__presente INTEGER NOT NULL,
    entradas_recuerdo_diferido_tipo TEXT,
    entradas_residencia_bolivia__estado VARCHAR(8) NOT NULL CHECK(entradas_residencia_bolivia__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_residencia_bolivia_campo__presente INTEGER NOT NULL,
    entradas_residencia_bolivia_campo TEXT,
    entradas_residencia_bolivia_cod_pregunta__presente INTEGER NOT NULL,
    entradas_residencia_bolivia_cod_pregunta TEXT,
    entradas_residencia_bolivia_cod_respuesta__presente INTEGER NOT NULL,
    entradas_residencia_bolivia_cod_respuesta TEXT,
    entradas_residencia_bolivia_estado__presente INTEGER NOT NULL,
    entradas_residencia_bolivia_estado TEXT,
    entradas_residencia_bolivia_tipo__presente INTEGER NOT NULL,
    entradas_residencia_bolivia_tipo TEXT,
    entradas_segundos_parte_b__estado VARCHAR(8) NOT NULL CHECK(entradas_segundos_parte_b__estado IN ('AUSENTE','NULL','PRESENTE')),
    entradas_segundos_parte_b_campo__presente INTEGER NOT NULL,
    entradas_segundos_parte_b_campo TEXT,
    entradas_segundos_parte_b_cod_pregunta__presente INTEGER NOT NULL,
    entradas_segundos_parte_b_cod_pregunta TEXT,
    entradas_segundos_parte_b_cod_respuesta__presente INTEGER NOT NULL,
    entradas_segundos_parte_b_cod_respuesta TEXT,
    entradas_segundos_parte_b_estado__presente INTEGER NOT NULL,
    entradas_segundos_parte_b_estado TEXT,
    entradas_segundos_parte_b_tipo__presente INTEGER NOT NULL,
    entradas_segundos_parte_b_tipo TEXT,
    estado_normalizacion__presente INTEGER NOT NULL,
    estado_normalizacion TEXT,
    fuente_metodo__presente INTEGER NOT NULL,
    fuente_metodo TEXT,
    identidad__estado VARCHAR(8) NOT NULL CHECK(identidad__estado IN ('AUSENTE','NULL','PRESENTE')),
    identidad_edicion_protocolo__estado VARCHAR(8) NOT NULL CHECK(identidad_edicion_protocolo__estado IN ('AUSENTE','NULL','PRESENTE')),
    identidad_edicion_protocolo_declarado__presente INTEGER NOT NULL,
    identidad_edicion_protocolo_declarado TEXT,
    identidad_edicion_protocolo_estado__presente INTEGER NOT NULL,
    identidad_edicion_protocolo_estado TEXT,
    identidad_edicion_protocolo_requerido_por_metodo__presente INTEGER NOT NULL,
    identidad_edicion_protocolo_requerido_por_metodo TEXT,
    identidad_forma__estado VARCHAR(8) NOT NULL CHECK(identidad_forma__estado IN ('AUSENTE','NULL','PRESENTE')),
    identidad_forma_declarado__presente INTEGER NOT NULL,
    identidad_forma_declarado BIGINT,
    identidad_forma_estado__presente INTEGER NOT NULL,
    identidad_forma_estado TEXT,
    identidad_forma_requerido_por_metodo__presente INTEGER NOT NULL,
    identidad_forma_requerido_por_metodo BIGINT,
    identidad_idioma__estado VARCHAR(8) NOT NULL CHECK(identidad_idioma__estado IN ('AUSENTE','NULL','PRESENTE')),
    identidad_idioma_declarado__presente INTEGER NOT NULL,
    identidad_idioma_declarado TEXT,
    identidad_idioma_estado__presente INTEGER NOT NULL,
    identidad_idioma_estado TEXT,
    identidad_idioma_requerido_por_metodo__presente INTEGER NOT NULL,
    identidad_idioma_requerido_por_metodo TEXT,
    identidad_instrumento__estado VARCHAR(8) NOT NULL CHECK(identidad_instrumento__estado IN ('AUSENTE','NULL','PRESENTE')),
    identidad_instrumento_declarado__presente INTEGER NOT NULL,
    identidad_instrumento_declarado TEXT,
    identidad_instrumento_estado__presente INTEGER NOT NULL,
    identidad_instrumento_estado TEXT,
    identidad_instrumento_requerido_por_metodo__presente INTEGER NOT NULL,
    identidad_instrumento_requerido_por_metodo TEXT,
    metodo__presente INTEGER NOT NULL,
    metodo TEXT,
    motivos_captura__estado VARCHAR(8) NOT NULL CHECK(motivos_captura__estado IN ('AUSENTE','NULL','PRESENTE')),
    motivos_lectura__estado VARCHAR(8) NOT NULL CHECK(motivos_lectura__estado IN ('AUSENTE','NULL','PRESENTE')),
    motivos_lectura__cantidad BIGINT NOT NULL CHECK(motivos_lectura__cantidad>=0),
    naturaleza_fuente__presente INTEGER NOT NULL,
    naturaleza_fuente TEXT,
    perfil__presente INTEGER NOT NULL,
    perfil TEXT,
    pertenece_a_poblacion_60_o_mas__presente INTEGER NOT NULL,
    pertenece_a_poblacion_60_o_mas INTEGER,
    procedencia__estado VARCHAR(8) NOT NULL CHECK(procedencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia_cod_documento_clinico__presente INTEGER NOT NULL,
    procedencia_cod_documento_clinico TEXT,
    procedencia_cod_personal_aplicador__presente INTEGER NOT NULL,
    procedencia_cod_personal_aplicador TEXT,
    procedencia_cod_usuario_registro__presente INTEGER NOT NULL,
    procedencia_cod_usuario_registro TEXT,
    procedencia_estado_documento__presente INTEGER NOT NULL,
    procedencia_estado_documento TEXT,
    procedencia_fecha_clinica__presente INTEGER NOT NULL,
    procedencia_fecha_clinica TEXT,
    procedencia_registrado_en__presente INTEGER NOT NULL,
    procedencia_registrado_en TEXT,
    procedencia_tiene_localizador__presente INTEGER NOT NULL,
    procedencia_tiene_localizador INTEGER,
    version_instrumento__presente INTEGER NOT NULL,
    version_instrumento TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(aplicabilidad_alcance_etario_compatible__presente IN (0,1)),
    CHECK(aplicabilidad_alcance_etario_compatible__presente=1 OR aplicabilidad_alcance_etario_compatible IS NULL),
    CHECK(aplicabilidad_alcance_etario_compatible IS NULL OR aplicabilidad_alcance_etario_compatible IN (0,1)),
    CHECK(aplicabilidad_autoriza_interpretacion_clinica__presente IN (0,1)),
    CHECK(aplicabilidad_autoriza_interpretacion_clinica__presente=1 OR aplicabilidad_autoriza_interpretacion_clinica IS NULL),
    CHECK(aplicabilidad_autoriza_interpretacion_clinica IS NULL OR aplicabilidad_autoriza_interpretacion_clinica IN (0,1)),
    CHECK(aplicabilidad_edad_en_aplicacion__presente IN (0,1)),
    CHECK(aplicabilidad_edad_en_aplicacion__presente=1 OR aplicabilidad_edad_en_aplicacion IS NULL),
    CHECK(aplicabilidad_escolaridad_original__presente IN (0,1)),
    CHECK(aplicabilidad_escolaridad_original__presente=1 OR aplicabilidad_escolaridad_original IS NULL),
    CHECK(aplicabilidad_fuente__presente IN (0,1)),
    CHECK(aplicabilidad_fuente__presente=1 OR aplicabilidad_fuente IS NULL),
    CHECK(aplicabilidad_idioma_declarado__presente IN (0,1)),
    CHECK(aplicabilidad_idioma_declarado__presente=1 OR aplicabilidad_idioma_declarado IS NULL),
    CHECK(aplicabilidad_pertenece_a_poblacion_objetivo__presente IN (0,1)),
    CHECK(aplicabilidad_pertenece_a_poblacion_objetivo__presente=1 OR aplicabilidad_pertenece_a_poblacion_objetivo IS NULL),
    CHECK(aplicabilidad_pertenece_a_poblacion_objetivo IS NULL OR aplicabilidad_pertenece_a_poblacion_objetivo IN (0,1)),
    CHECK(aplicabilidad_poblacion_objetivo__presente IN (0,1)),
    CHECK(aplicabilidad_poblacion_objetivo__presente=1 OR aplicabilidad_poblacion_objetivo IS NULL),
    CHECK(aplicabilidad_rango_edad_referencia_maxima__presente IN (0,1)),
    CHECK(aplicabilidad_rango_edad_referencia_maxima__presente=1 OR aplicabilidad_rango_edad_referencia_maxima IS NULL),
    CHECK(aplicabilidad_rango_edad_referencia_minima__presente IN (0,1)),
    CHECK(aplicabilidad_rango_edad_referencia_minima__presente=1 OR aplicabilidad_rango_edad_referencia_minima IS NULL),
    CHECK(aplicabilidad_requiere_verificar__estado='PRESENTE' OR aplicabilidad_requiere_verificar__cantidad=0),
    CHECK(calculos__estado='PRESENTE' OR calculos__cantidad=0),
    CHECK(cod_aplicacion__presente IN (0,1)),
    CHECK(cod_aplicacion__presente=1 OR cod_aplicacion IS NULL),
    CHECK(cod_instrumento__presente IN (0,1)),
    CHECK(cod_instrumento__presente=1 OR cod_instrumento IS NULL),
    CHECK(edad_en_aplicacion__presente IN (0,1)),
    CHECK(edad_en_aplicacion__presente=1 OR edad_en_aplicacion IS NULL),
    CHECK(entradas_aciertos_clave_semantica_campo__presente IN (0,1)),
    CHECK(entradas_aciertos_clave_semantica_campo__presente=1 OR entradas_aciertos_clave_semantica_campo IS NULL),
    CHECK(entradas_aciertos_clave_semantica_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_aciertos_clave_semantica_cod_pregunta__presente=1 OR entradas_aciertos_clave_semantica_cod_pregunta IS NULL),
    CHECK(entradas_aciertos_clave_semantica_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_aciertos_clave_semantica_cod_respuesta__presente=1 OR entradas_aciertos_clave_semantica_cod_respuesta IS NULL),
    CHECK(entradas_aciertos_clave_semantica_estado__presente IN (0,1)),
    CHECK(entradas_aciertos_clave_semantica_estado__presente=1 OR entradas_aciertos_clave_semantica_estado IS NULL),
    CHECK(entradas_aciertos_clave_semantica_tipo__presente IN (0,1)),
    CHECK(entradas_aciertos_clave_semantica_tipo__presente=1 OR entradas_aciertos_clave_semantica_tipo IS NULL),
    CHECK(entradas_aciertos_espontaneos_campo__presente IN (0,1)),
    CHECK(entradas_aciertos_espontaneos_campo__presente=1 OR entradas_aciertos_espontaneos_campo IS NULL),
    CHECK(entradas_aciertos_espontaneos_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_aciertos_espontaneos_cod_pregunta__presente=1 OR entradas_aciertos_espontaneos_cod_pregunta IS NULL),
    CHECK(entradas_aciertos_espontaneos_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_aciertos_espontaneos_cod_respuesta__presente=1 OR entradas_aciertos_espontaneos_cod_respuesta IS NULL),
    CHECK(entradas_aciertos_espontaneos_estado__presente IN (0,1)),
    CHECK(entradas_aciertos_espontaneos_estado__presente=1 OR entradas_aciertos_espontaneos_estado IS NULL),
    CHECK(entradas_aciertos_espontaneos_tipo__presente IN (0,1)),
    CHECK(entradas_aciertos_espontaneos_tipo__presente=1 OR entradas_aciertos_espontaneos_tipo IS NULL),
    CHECK(entradas_administracion_estandarizada_campo__presente IN (0,1)),
    CHECK(entradas_administracion_estandarizada_campo__presente=1 OR entradas_administracion_estandarizada_campo IS NULL),
    CHECK(ent_adm_est_cod_pre__presente IN (0,1)),
    CHECK(ent_adm_est_cod_pre__presente=1 OR ent_adm_est_cod_pre IS NULL),
    CHECK(ent_adm_est_cod_res__presente IN (0,1)),
    CHECK(ent_adm_est_cod_res__presente=1 OR ent_adm_est_cod_res IS NULL),
    CHECK(entradas_administracion_estandarizada_estado__presente IN (0,1)),
    CHECK(entradas_administracion_estandarizada_estado__presente=1 OR entradas_administracion_estandarizada_estado IS NULL),
    CHECK(entradas_administracion_estandarizada_tipo__presente IN (0,1)),
    CHECK(entradas_administracion_estandarizada_tipo__presente=1 OR entradas_administracion_estandarizada_tipo IS NULL),
    CHECK(entradas_audicion_compatible_campo__presente IN (0,1)),
    CHECK(entradas_audicion_compatible_campo__presente=1 OR entradas_audicion_compatible_campo IS NULL),
    CHECK(entradas_audicion_compatible_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_audicion_compatible_cod_pregunta__presente=1 OR entradas_audicion_compatible_cod_pregunta IS NULL),
    CHECK(entradas_audicion_compatible_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_audicion_compatible_cod_respuesta__presente=1 OR entradas_audicion_compatible_cod_respuesta IS NULL),
    CHECK(entradas_audicion_compatible_estado__presente IN (0,1)),
    CHECK(entradas_audicion_compatible_estado__presente=1 OR entradas_audicion_compatible_estado IS NULL),
    CHECK(entradas_audicion_compatible_tipo__presente IN (0,1)),
    CHECK(entradas_audicion_compatible_tipo__presente=1 OR entradas_audicion_compatible_tipo IS NULL),
    CHECK(entradas_condiciones_sensoriales_campo__presente IN (0,1)),
    CHECK(entradas_condiciones_sensoriales_campo__presente=1 OR entradas_condiciones_sensoriales_campo IS NULL),
    CHECK(entradas_condiciones_sensoriales_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_condiciones_sensoriales_cod_pregunta__presente=1 OR entradas_condiciones_sensoriales_cod_pregunta IS NULL),
    CHECK(entradas_condiciones_sensoriales_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_condiciones_sensoriales_cod_respuesta__presente=1 OR entradas_condiciones_sensoriales_cod_respuesta IS NULL),
    CHECK(entradas_condiciones_sensoriales_estado__presente IN (0,1)),
    CHECK(entradas_condiciones_sensoriales_estado__presente=1 OR entradas_condiciones_sensoriales_estado IS NULL),
    CHECK(entradas_condiciones_sensoriales_tipo__presente IN (0,1)),
    CHECK(entradas_condiciones_sensoriales_tipo__presente=1 OR entradas_condiciones_sensoriales_tipo IS NULL),
    CHECK(entradas_demora_minutos_campo__presente IN (0,1)),
    CHECK(entradas_demora_minutos_campo__presente=1 OR entradas_demora_minutos_campo IS NULL),
    CHECK(entradas_demora_minutos_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_demora_minutos_cod_pregunta__presente=1 OR entradas_demora_minutos_cod_pregunta IS NULL),
    CHECK(entradas_demora_minutos_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_demora_minutos_cod_respuesta__presente=1 OR entradas_demora_minutos_cod_respuesta IS NULL),
    CHECK(entradas_demora_minutos_estado__presente IN (0,1)),
    CHECK(entradas_demora_minutos_estado__presente=1 OR entradas_demora_minutos_estado IS NULL),
    CHECK(entradas_demora_minutos_tipo__presente IN (0,1)),
    CHECK(entradas_demora_minutos_tipo__presente=1 OR entradas_demora_minutos_tipo IS NULL),
    CHECK(entradas_elementos_revisados_campo__presente IN (0,1)),
    CHECK(entradas_elementos_revisados_campo__presente=1 OR entradas_elementos_revisados_campo IS NULL),
    CHECK(entradas_elementos_revisados_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_elementos_revisados_cod_pregunta__presente=1 OR entradas_elementos_revisados_cod_pregunta IS NULL),
    CHECK(entradas_elementos_revisados_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_elementos_revisados_cod_respuesta__presente=1 OR entradas_elementos_revisados_cod_respuesta IS NULL),
    CHECK(entradas_elementos_revisados_estado__presente IN (0,1)),
    CHECK(entradas_elementos_revisados_estado__presente=1 OR entradas_elementos_revisados_estado IS NULL),
    CHECK(entradas_elementos_revisados_tipo__presente IN (0,1)),
    CHECK(entradas_elementos_revisados_tipo__presente=1 OR entradas_elementos_revisados_tipo IS NULL),
    CHECK(entradas_ensayo_1_campo__presente IN (0,1)),
    CHECK(entradas_ensayo_1_campo__presente=1 OR entradas_ensayo_1_campo IS NULL),
    CHECK(entradas_ensayo_1_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_ensayo_1_cod_pregunta__presente=1 OR entradas_ensayo_1_cod_pregunta IS NULL),
    CHECK(entradas_ensayo_1_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_ensayo_1_cod_respuesta__presente=1 OR entradas_ensayo_1_cod_respuesta IS NULL),
    CHECK(entradas_ensayo_1_estado__presente IN (0,1)),
    CHECK(entradas_ensayo_1_estado__presente=1 OR entradas_ensayo_1_estado IS NULL),
    CHECK(entradas_ensayo_1_tipo__presente IN (0,1)),
    CHECK(entradas_ensayo_1_tipo__presente=1 OR entradas_ensayo_1_tipo IS NULL),
    CHECK(entradas_ensayo_2_campo__presente IN (0,1)),
    CHECK(entradas_ensayo_2_campo__presente=1 OR entradas_ensayo_2_campo IS NULL),
    CHECK(entradas_ensayo_2_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_ensayo_2_cod_pregunta__presente=1 OR entradas_ensayo_2_cod_pregunta IS NULL),
    CHECK(entradas_ensayo_2_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_ensayo_2_cod_respuesta__presente=1 OR entradas_ensayo_2_cod_respuesta IS NULL),
    CHECK(entradas_ensayo_2_estado__presente IN (0,1)),
    CHECK(entradas_ensayo_2_estado__presente=1 OR entradas_ensayo_2_estado IS NULL),
    CHECK(entradas_ensayo_2_tipo__presente IN (0,1)),
    CHECK(entradas_ensayo_2_tipo__presente=1 OR entradas_ensayo_2_tipo IS NULL),
    CHECK(entradas_ensayo_3_campo__presente IN (0,1)),
    CHECK(entradas_ensayo_3_campo__presente=1 OR entradas_ensayo_3_campo IS NULL),
    CHECK(entradas_ensayo_3_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_ensayo_3_cod_pregunta__presente=1 OR entradas_ensayo_3_cod_pregunta IS NULL),
    CHECK(entradas_ensayo_3_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_ensayo_3_cod_respuesta__presente=1 OR entradas_ensayo_3_cod_respuesta IS NULL),
    CHECK(entradas_ensayo_3_estado__presente IN (0,1)),
    CHECK(entradas_ensayo_3_estado__presente=1 OR entradas_ensayo_3_estado IS NULL),
    CHECK(entradas_ensayo_3_tipo__presente IN (0,1)),
    CHECK(entradas_ensayo_3_tipo__presente=1 OR entradas_ensayo_3_tipo IS NULL),
    CHECK(entradas_errores_observados_campo__presente IN (0,1)),
    CHECK(entradas_errores_observados_campo__presente=1 OR entradas_errores_observados_campo IS NULL),
    CHECK(entradas_errores_observados_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_errores_observados_cod_pregunta__presente=1 OR entradas_errores_observados_cod_pregunta IS NULL),
    CHECK(entradas_errores_observados_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_errores_observados_cod_respuesta__presente=1 OR entradas_errores_observados_cod_respuesta IS NULL),
    CHECK(entradas_errores_observados_estado__presente IN (0,1)),
    CHECK(entradas_errores_observados_estado__presente=1 OR entradas_errores_observados_estado IS NULL),
    CHECK(entradas_errores_observados_tipo__presente IN (0,1)),
    CHECK(entradas_errores_observados_tipo__presente=1 OR entradas_errores_observados_tipo IS NULL),
    CHECK(entradas_escolaridad_anios_campo__presente IN (0,1)),
    CHECK(entradas_escolaridad_anios_campo__presente=1 OR entradas_escolaridad_anios_campo IS NULL),
    CHECK(entradas_escolaridad_anios_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_escolaridad_anios_cod_pregunta__presente=1 OR entradas_escolaridad_anios_cod_pregunta IS NULL),
    CHECK(entradas_escolaridad_anios_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_escolaridad_anios_cod_respuesta__presente=1 OR entradas_escolaridad_anios_cod_respuesta IS NULL),
    CHECK(entradas_escolaridad_anios_estado__presente IN (0,1)),
    CHECK(entradas_escolaridad_anios_estado__presente=1 OR entradas_escolaridad_anios_estado IS NULL),
    CHECK(entradas_escolaridad_anios_tipo__presente IN (0,1)),
    CHECK(entradas_escolaridad_anios_tipo__presente=1 OR entradas_escolaridad_anios_tipo IS NULL),
    CHECK(entradas_espanol_nativo_campo__presente IN (0,1)),
    CHECK(entradas_espanol_nativo_campo__presente=1 OR entradas_espanol_nativo_campo IS NULL),
    CHECK(entradas_espanol_nativo_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_espanol_nativo_cod_pregunta__presente=1 OR entradas_espanol_nativo_cod_pregunta IS NULL),
    CHECK(entradas_espanol_nativo_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_espanol_nativo_cod_respuesta__presente=1 OR entradas_espanol_nativo_cod_respuesta IS NULL),
    CHECK(entradas_espanol_nativo_estado__presente IN (0,1)),
    CHECK(entradas_espanol_nativo_estado__presente=1 OR entradas_espanol_nativo_estado IS NULL),
    CHECK(entradas_espanol_nativo_tipo__presente IN (0,1)),
    CHECK(entradas_espanol_nativo_tipo__presente=1 OR entradas_espanol_nativo_tipo IS NULL),
    CHECK(entradas_finalizada_campo__presente IN (0,1)),
    CHECK(entradas_finalizada_campo__presente=1 OR entradas_finalizada_campo IS NULL),
    CHECK(entradas_finalizada_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_finalizada_cod_pregunta__presente=1 OR entradas_finalizada_cod_pregunta IS NULL),
    CHECK(entradas_finalizada_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_finalizada_cod_respuesta__presente=1 OR entradas_finalizada_cod_respuesta IS NULL),
    CHECK(entradas_finalizada_estado__presente IN (0,1)),
    CHECK(entradas_finalizada_estado__presente=1 OR entradas_finalizada_estado IS NULL),
    CHECK(entradas_finalizada_tipo__presente IN (0,1)),
    CHECK(entradas_finalizada_tipo__presente=1 OR entradas_finalizada_tipo IS NULL),
    CHECK(entradas_forma_l_aciertos_campo__presente IN (0,1)),
    CHECK(entradas_forma_l_aciertos_campo__presente=1 OR entradas_forma_l_aciertos_campo IS NULL),
    CHECK(entradas_forma_l_aciertos_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_forma_l_aciertos_cod_pregunta__presente=1 OR entradas_forma_l_aciertos_cod_pregunta IS NULL),
    CHECK(entradas_forma_l_aciertos_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_forma_l_aciertos_cod_respuesta__presente=1 OR entradas_forma_l_aciertos_cod_respuesta IS NULL),
    CHECK(entradas_forma_l_aciertos_estado__presente IN (0,1)),
    CHECK(entradas_forma_l_aciertos_estado__presente=1 OR entradas_forma_l_aciertos_estado IS NULL),
    CHECK(entradas_forma_l_aciertos_tipo__presente IN (0,1)),
    CHECK(entradas_forma_l_aciertos_tipo__presente=1 OR entradas_forma_l_aciertos_tipo IS NULL),
    CHECK(entradas_forma_n_aciertos_campo__presente IN (0,1)),
    CHECK(entradas_forma_n_aciertos_campo__presente=1 OR entradas_forma_n_aciertos_campo IS NULL),
    CHECK(entradas_forma_n_aciertos_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_forma_n_aciertos_cod_pregunta__presente=1 OR entradas_forma_n_aciertos_cod_pregunta IS NULL),
    CHECK(entradas_forma_n_aciertos_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_forma_n_aciertos_cod_respuesta__presente=1 OR entradas_forma_n_aciertos_cod_respuesta IS NULL),
    CHECK(entradas_forma_n_aciertos_estado__presente IN (0,1)),
    CHECK(entradas_forma_n_aciertos_estado__presente=1 OR entradas_forma_n_aciertos_estado IS NULL),
    CHECK(entradas_forma_n_aciertos_tipo__presente IN (0,1)),
    CHECK(entradas_forma_n_aciertos_tipo__presente=1 OR entradas_forma_n_aciertos_tipo IS NULL),
    CHECK(entradas_lectoescritura_campo__presente IN (0,1)),
    CHECK(entradas_lectoescritura_campo__presente=1 OR entradas_lectoescritura_campo IS NULL),
    CHECK(entradas_lectoescritura_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_lectoescritura_cod_pregunta__presente=1 OR entradas_lectoescritura_cod_pregunta IS NULL),
    CHECK(entradas_lectoescritura_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_lectoescritura_cod_respuesta__presente=1 OR entradas_lectoescritura_cod_respuesta IS NULL),
    CHECK(entradas_lectoescritura_estado__presente IN (0,1)),
    CHECK(entradas_lectoescritura_estado__presente=1 OR entradas_lectoescritura_estado IS NULL),
    CHECK(entradas_lectoescritura_tipo__presente IN (0,1)),
    CHECK(entradas_lectoescritura_tipo__presente=1 OR entradas_lectoescritura_tipo IS NULL),
    CHECK(entradas_nacimiento_bolivia_campo__presente IN (0,1)),
    CHECK(entradas_nacimiento_bolivia_campo__presente=1 OR entradas_nacimiento_bolivia_campo IS NULL),
    CHECK(entradas_nacimiento_bolivia_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_nacimiento_bolivia_cod_pregunta__presente=1 OR entradas_nacimiento_bolivia_cod_pregunta IS NULL),
    CHECK(entradas_nacimiento_bolivia_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_nacimiento_bolivia_cod_respuesta__presente=1 OR entradas_nacimiento_bolivia_cod_respuesta IS NULL),
    CHECK(entradas_nacimiento_bolivia_estado__presente IN (0,1)),
    CHECK(entradas_nacimiento_bolivia_estado__presente=1 OR entradas_nacimiento_bolivia_estado IS NULL),
    CHECK(entradas_nacimiento_bolivia_tipo__presente IN (0,1)),
    CHECK(entradas_nacimiento_bolivia_tipo__presente=1 OR entradas_nacimiento_bolivia_tipo IS NULL),
    CHECK(entradas_puntaje_copia_campo__presente IN (0,1)),
    CHECK(entradas_puntaje_copia_campo__presente=1 OR entradas_puntaje_copia_campo IS NULL),
    CHECK(entradas_puntaje_copia_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_puntaje_copia_cod_pregunta__presente=1 OR entradas_puntaje_copia_cod_pregunta IS NULL),
    CHECK(entradas_puntaje_copia_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_puntaje_copia_cod_respuesta__presente=1 OR entradas_puntaje_copia_cod_respuesta IS NULL),
    CHECK(entradas_puntaje_copia_estado__presente IN (0,1)),
    CHECK(entradas_puntaje_copia_estado__presente=1 OR entradas_puntaje_copia_estado IS NULL),
    CHECK(entradas_puntaje_copia_tipo__presente IN (0,1)),
    CHECK(entradas_puntaje_copia_tipo__presente=1 OR entradas_puntaje_copia_tipo IS NULL),
    CHECK(entradas_recuerdo_diferido_campo__presente IN (0,1)),
    CHECK(entradas_recuerdo_diferido_campo__presente=1 OR entradas_recuerdo_diferido_campo IS NULL),
    CHECK(entradas_recuerdo_diferido_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_recuerdo_diferido_cod_pregunta__presente=1 OR entradas_recuerdo_diferido_cod_pregunta IS NULL),
    CHECK(entradas_recuerdo_diferido_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_recuerdo_diferido_cod_respuesta__presente=1 OR entradas_recuerdo_diferido_cod_respuesta IS NULL),
    CHECK(entradas_recuerdo_diferido_estado__presente IN (0,1)),
    CHECK(entradas_recuerdo_diferido_estado__presente=1 OR entradas_recuerdo_diferido_estado IS NULL),
    CHECK(entradas_recuerdo_diferido_tipo__presente IN (0,1)),
    CHECK(entradas_recuerdo_diferido_tipo__presente=1 OR entradas_recuerdo_diferido_tipo IS NULL),
    CHECK(entradas_residencia_bolivia_campo__presente IN (0,1)),
    CHECK(entradas_residencia_bolivia_campo__presente=1 OR entradas_residencia_bolivia_campo IS NULL),
    CHECK(entradas_residencia_bolivia_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_residencia_bolivia_cod_pregunta__presente=1 OR entradas_residencia_bolivia_cod_pregunta IS NULL),
    CHECK(entradas_residencia_bolivia_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_residencia_bolivia_cod_respuesta__presente=1 OR entradas_residencia_bolivia_cod_respuesta IS NULL),
    CHECK(entradas_residencia_bolivia_estado__presente IN (0,1)),
    CHECK(entradas_residencia_bolivia_estado__presente=1 OR entradas_residencia_bolivia_estado IS NULL),
    CHECK(entradas_residencia_bolivia_tipo__presente IN (0,1)),
    CHECK(entradas_residencia_bolivia_tipo__presente=1 OR entradas_residencia_bolivia_tipo IS NULL),
    CHECK(entradas_segundos_parte_b_campo__presente IN (0,1)),
    CHECK(entradas_segundos_parte_b_campo__presente=1 OR entradas_segundos_parte_b_campo IS NULL),
    CHECK(entradas_segundos_parte_b_cod_pregunta__presente IN (0,1)),
    CHECK(entradas_segundos_parte_b_cod_pregunta__presente=1 OR entradas_segundos_parte_b_cod_pregunta IS NULL),
    CHECK(entradas_segundos_parte_b_cod_respuesta__presente IN (0,1)),
    CHECK(entradas_segundos_parte_b_cod_respuesta__presente=1 OR entradas_segundos_parte_b_cod_respuesta IS NULL),
    CHECK(entradas_segundos_parte_b_estado__presente IN (0,1)),
    CHECK(entradas_segundos_parte_b_estado__presente=1 OR entradas_segundos_parte_b_estado IS NULL),
    CHECK(entradas_segundos_parte_b_tipo__presente IN (0,1)),
    CHECK(entradas_segundos_parte_b_tipo__presente=1 OR entradas_segundos_parte_b_tipo IS NULL),
    CHECK(estado_normalizacion__presente IN (0,1)),
    CHECK(estado_normalizacion__presente=1 OR estado_normalizacion IS NULL),
    CHECK(fuente_metodo__presente IN (0,1)),
    CHECK(fuente_metodo__presente=1 OR fuente_metodo IS NULL),
    CHECK(identidad_edicion_protocolo_declarado__presente IN (0,1)),
    CHECK(identidad_edicion_protocolo_declarado__presente=1 OR identidad_edicion_protocolo_declarado IS NULL),
    CHECK(identidad_edicion_protocolo_estado__presente IN (0,1)),
    CHECK(identidad_edicion_protocolo_estado__presente=1 OR identidad_edicion_protocolo_estado IS NULL),
    CHECK(identidad_edicion_protocolo_requerido_por_metodo__presente IN (0,1)),
    CHECK(identidad_edicion_protocolo_requerido_por_metodo__presente=1 OR identidad_edicion_protocolo_requerido_por_metodo IS NULL),
    CHECK(identidad_forma_declarado__presente IN (0,1)),
    CHECK(identidad_forma_declarado__presente=1 OR identidad_forma_declarado IS NULL),
    CHECK(identidad_forma_estado__presente IN (0,1)),
    CHECK(identidad_forma_estado__presente=1 OR identidad_forma_estado IS NULL),
    CHECK(identidad_forma_requerido_por_metodo__presente IN (0,1)),
    CHECK(identidad_forma_requerido_por_metodo__presente=1 OR identidad_forma_requerido_por_metodo IS NULL),
    CHECK(identidad_idioma_declarado__presente IN (0,1)),
    CHECK(identidad_idioma_declarado__presente=1 OR identidad_idioma_declarado IS NULL),
    CHECK(identidad_idioma_estado__presente IN (0,1)),
    CHECK(identidad_idioma_estado__presente=1 OR identidad_idioma_estado IS NULL),
    CHECK(identidad_idioma_requerido_por_metodo__presente IN (0,1)),
    CHECK(identidad_idioma_requerido_por_metodo__presente=1 OR identidad_idioma_requerido_por_metodo IS NULL),
    CHECK(identidad_instrumento_declarado__presente IN (0,1)),
    CHECK(identidad_instrumento_declarado__presente=1 OR identidad_instrumento_declarado IS NULL),
    CHECK(identidad_instrumento_estado__presente IN (0,1)),
    CHECK(identidad_instrumento_estado__presente=1 OR identidad_instrumento_estado IS NULL),
    CHECK(identidad_instrumento_requerido_por_metodo__presente IN (0,1)),
    CHECK(identidad_instrumento_requerido_por_metodo__presente=1 OR identidad_instrumento_requerido_por_metodo IS NULL),
    CHECK(metodo__presente IN (0,1)),
    CHECK(metodo__presente=1 OR metodo IS NULL),
    CHECK(motivos_lectura__estado='PRESENTE' OR motivos_lectura__cantidad=0),
    CHECK(naturaleza_fuente__presente IN (0,1)),
    CHECK(naturaleza_fuente__presente=1 OR naturaleza_fuente IS NULL),
    CHECK(perfil__presente IN (0,1)),
    CHECK(perfil__presente=1 OR perfil IS NULL),
    CHECK(pertenece_a_poblacion_60_o_mas__presente IN (0,1)),
    CHECK(pertenece_a_poblacion_60_o_mas__presente=1 OR pertenece_a_poblacion_60_o_mas IS NULL),
    CHECK(pertenece_a_poblacion_60_o_mas IS NULL OR pertenece_a_poblacion_60_o_mas IN (0,1)),
    CHECK(procedencia_cod_documento_clinico__presente IN (0,1)),
    CHECK(procedencia_cod_documento_clinico__presente=1 OR procedencia_cod_documento_clinico IS NULL),
    CHECK(procedencia_cod_personal_aplicador__presente IN (0,1)),
    CHECK(procedencia_cod_personal_aplicador__presente=1 OR procedencia_cod_personal_aplicador IS NULL),
    CHECK(procedencia_cod_usuario_registro__presente IN (0,1)),
    CHECK(procedencia_cod_usuario_registro__presente=1 OR procedencia_cod_usuario_registro IS NULL),
    CHECK(procedencia_estado_documento__presente IN (0,1)),
    CHECK(procedencia_estado_documento__presente=1 OR procedencia_estado_documento IS NULL),
    CHECK(procedencia_fecha_clinica__presente IN (0,1)),
    CHECK(procedencia_fecha_clinica__presente=1 OR procedencia_fecha_clinica IS NULL),
    CHECK(procedencia_registrado_en__presente IN (0,1)),
    CHECK(procedencia_registrado_en__presente=1 OR procedencia_registrado_en IS NULL),
    CHECK(procedencia_tiene_localizador__presente IN (0,1)),
    CHECK(procedencia_tiene_localizador__presente=1 OR procedencia_tiene_localizador IS NULL),
    CHECK(procedencia_tiene_localizador IS NULL OR procedencia_tiene_localizador IN (0,1)),
    CHECK(version_instrumento__presente IN (0,1)),
    CHECK(version_instrumento__presente=1 OR version_instrumento IS NULL),
    CHECK(dto__estado='PRESENTE' OR (aplicabilidad__estado='AUSENTE' AND calculos__estado='AUSENTE' AND cod_aplicacion__presente=0 AND cod_instrumento__presente=0 AND edad_en_aplicacion__presente=0 AND entradas__estado='AUSENTE' AND estado_normalizacion__presente=0 AND fuente_metodo__presente=0 AND identidad__estado='AUSENTE' AND metodo__presente=0 AND motivos_captura__estado='AUSENTE' AND motivos_lectura__estado='AUSENTE' AND naturaleza_fuente__presente=0 AND perfil__presente=0 AND pertenece_a_poblacion_60_o_mas__presente=0 AND procedencia__estado='AUSENTE' AND version_instrumento__presente=0)),
    CHECK(aplicabilidad__estado='PRESENTE' OR (aplicabilidad_alcance_etario_compatible__presente=0 AND aplicabilidad_autoriza_interpretacion_clinica__presente=0 AND aplicabilidad_edad_en_aplicacion__presente=0 AND aplicabilidad_escolaridad_original__presente=0 AND aplicabilidad_fuente__presente=0 AND aplicabilidad_idioma_declarado__presente=0 AND aplicabilidad_motivos__estado='AUSENTE' AND aplicabilidad_pertenece_a_poblacion_objetivo__presente=0 AND aplicabilidad_poblacion_objetivo__presente=0 AND aplicabilidad_rango_edad_referencia__estado='AUSENTE' AND aplicabilidad_requiere_verificar__estado='AUSENTE')),
    CHECK(aplicabilidad_rango_edad_referencia__estado='PRESENTE' OR (aplicabilidad_rango_edad_referencia_maxima__presente=0 AND aplicabilidad_rango_edad_referencia_minima__presente=0)),
    CHECK(entradas__estado='PRESENTE' OR (entradas_aciertos_clave_semantica__estado='AUSENTE' AND entradas_aciertos_espontaneos__estado='AUSENTE' AND entradas_administracion_estandarizada__estado='AUSENTE' AND entradas_audicion_compatible__estado='AUSENTE' AND entradas_condiciones_sensoriales__estado='AUSENTE' AND entradas_demora_minutos__estado='AUSENTE' AND entradas_elementos_revisados__estado='AUSENTE' AND entradas_ensayo_1__estado='AUSENTE' AND entradas_ensayo_2__estado='AUSENTE' AND entradas_ensayo_3__estado='AUSENTE' AND entradas_errores_observados__estado='AUSENTE' AND entradas_escolaridad_anios__estado='AUSENTE' AND entradas_espanol_nativo__estado='AUSENTE' AND entradas_finalizada__estado='AUSENTE' AND entradas_forma_l_aciertos__estado='AUSENTE' AND entradas_forma_n_aciertos__estado='AUSENTE' AND entradas_lectoescritura__estado='AUSENTE' AND entradas_nacimiento_bolivia__estado='AUSENTE' AND entradas_puntaje_copia__estado='AUSENTE' AND entradas_recuerdo_diferido__estado='AUSENTE' AND entradas_residencia_bolivia__estado='AUSENTE' AND entradas_segundos_parte_b__estado='AUSENTE')),
    CHECK(entradas_aciertos_clave_semantica__estado='PRESENTE' OR (entradas_aciertos_clave_semantica_campo__presente=0 AND entradas_aciertos_clave_semantica_cod_pregunta__presente=0 AND entradas_aciertos_clave_semantica_cod_respuesta__presente=0 AND entradas_aciertos_clave_semantica_estado__presente=0 AND entradas_aciertos_clave_semantica_tipo__presente=0)),
    CHECK(entradas_aciertos_espontaneos__estado='PRESENTE' OR (entradas_aciertos_espontaneos_campo__presente=0 AND entradas_aciertos_espontaneos_cod_pregunta__presente=0 AND entradas_aciertos_espontaneos_cod_respuesta__presente=0 AND entradas_aciertos_espontaneos_estado__presente=0 AND entradas_aciertos_espontaneos_tipo__presente=0)),
    CHECK(entradas_administracion_estandarizada__estado='PRESENTE' OR (entradas_administracion_estandarizada_campo__presente=0 AND ent_adm_est_cod_pre__presente=0 AND ent_adm_est_cod_res__presente=0 AND entradas_administracion_estandarizada_estado__presente=0 AND entradas_administracion_estandarizada_tipo__presente=0)),
    CHECK(entradas_audicion_compatible__estado='PRESENTE' OR (entradas_audicion_compatible_campo__presente=0 AND entradas_audicion_compatible_cod_pregunta__presente=0 AND entradas_audicion_compatible_cod_respuesta__presente=0 AND entradas_audicion_compatible_estado__presente=0 AND entradas_audicion_compatible_tipo__presente=0)),
    CHECK(entradas_condiciones_sensoriales__estado='PRESENTE' OR (entradas_condiciones_sensoriales_campo__presente=0 AND entradas_condiciones_sensoriales_cod_pregunta__presente=0 AND entradas_condiciones_sensoriales_cod_respuesta__presente=0 AND entradas_condiciones_sensoriales_estado__presente=0 AND entradas_condiciones_sensoriales_tipo__presente=0)),
    CHECK(entradas_demora_minutos__estado='PRESENTE' OR (entradas_demora_minutos_campo__presente=0 AND entradas_demora_minutos_cod_pregunta__presente=0 AND entradas_demora_minutos_cod_respuesta__presente=0 AND entradas_demora_minutos_estado__presente=0 AND entradas_demora_minutos_tipo__presente=0)),
    CHECK(entradas_elementos_revisados__estado='PRESENTE' OR (entradas_elementos_revisados_campo__presente=0 AND entradas_elementos_revisados_cod_pregunta__presente=0 AND entradas_elementos_revisados_cod_respuesta__presente=0 AND entradas_elementos_revisados_estado__presente=0 AND entradas_elementos_revisados_tipo__presente=0)),
    CHECK(entradas_ensayo_1__estado='PRESENTE' OR (entradas_ensayo_1_campo__presente=0 AND entradas_ensayo_1_cod_pregunta__presente=0 AND entradas_ensayo_1_cod_respuesta__presente=0 AND entradas_ensayo_1_estado__presente=0 AND entradas_ensayo_1_tipo__presente=0)),
    CHECK(entradas_ensayo_2__estado='PRESENTE' OR (entradas_ensayo_2_campo__presente=0 AND entradas_ensayo_2_cod_pregunta__presente=0 AND entradas_ensayo_2_cod_respuesta__presente=0 AND entradas_ensayo_2_estado__presente=0 AND entradas_ensayo_2_tipo__presente=0)),
    CHECK(entradas_ensayo_3__estado='PRESENTE' OR (entradas_ensayo_3_campo__presente=0 AND entradas_ensayo_3_cod_pregunta__presente=0 AND entradas_ensayo_3_cod_respuesta__presente=0 AND entradas_ensayo_3_estado__presente=0 AND entradas_ensayo_3_tipo__presente=0)),
    CHECK(entradas_errores_observados__estado='PRESENTE' OR (entradas_errores_observados_campo__presente=0 AND entradas_errores_observados_cod_pregunta__presente=0 AND entradas_errores_observados_cod_respuesta__presente=0 AND entradas_errores_observados_estado__presente=0 AND entradas_errores_observados_tipo__presente=0)),
    CHECK(entradas_escolaridad_anios__estado='PRESENTE' OR (entradas_escolaridad_anios_campo__presente=0 AND entradas_escolaridad_anios_cod_pregunta__presente=0 AND entradas_escolaridad_anios_cod_respuesta__presente=0 AND entradas_escolaridad_anios_estado__presente=0 AND entradas_escolaridad_anios_tipo__presente=0)),
    CHECK(entradas_espanol_nativo__estado='PRESENTE' OR (entradas_espanol_nativo_campo__presente=0 AND entradas_espanol_nativo_cod_pregunta__presente=0 AND entradas_espanol_nativo_cod_respuesta__presente=0 AND entradas_espanol_nativo_estado__presente=0 AND entradas_espanol_nativo_tipo__presente=0)),
    CHECK(entradas_finalizada__estado='PRESENTE' OR (entradas_finalizada_campo__presente=0 AND entradas_finalizada_cod_pregunta__presente=0 AND entradas_finalizada_cod_respuesta__presente=0 AND entradas_finalizada_estado__presente=0 AND entradas_finalizada_tipo__presente=0)),
    CHECK(entradas_forma_l_aciertos__estado='PRESENTE' OR (entradas_forma_l_aciertos_campo__presente=0 AND entradas_forma_l_aciertos_cod_pregunta__presente=0 AND entradas_forma_l_aciertos_cod_respuesta__presente=0 AND entradas_forma_l_aciertos_estado__presente=0 AND entradas_forma_l_aciertos_tipo__presente=0)),
    CHECK(entradas_forma_n_aciertos__estado='PRESENTE' OR (entradas_forma_n_aciertos_campo__presente=0 AND entradas_forma_n_aciertos_cod_pregunta__presente=0 AND entradas_forma_n_aciertos_cod_respuesta__presente=0 AND entradas_forma_n_aciertos_estado__presente=0 AND entradas_forma_n_aciertos_tipo__presente=0)),
    CHECK(entradas_lectoescritura__estado='PRESENTE' OR (entradas_lectoescritura_campo__presente=0 AND entradas_lectoescritura_cod_pregunta__presente=0 AND entradas_lectoescritura_cod_respuesta__presente=0 AND entradas_lectoescritura_estado__presente=0 AND entradas_lectoescritura_tipo__presente=0)),
    CHECK(entradas_nacimiento_bolivia__estado='PRESENTE' OR (entradas_nacimiento_bolivia_campo__presente=0 AND entradas_nacimiento_bolivia_cod_pregunta__presente=0 AND entradas_nacimiento_bolivia_cod_respuesta__presente=0 AND entradas_nacimiento_bolivia_estado__presente=0 AND entradas_nacimiento_bolivia_tipo__presente=0)),
    CHECK(entradas_puntaje_copia__estado='PRESENTE' OR (entradas_puntaje_copia_campo__presente=0 AND entradas_puntaje_copia_cod_pregunta__presente=0 AND entradas_puntaje_copia_cod_respuesta__presente=0 AND entradas_puntaje_copia_estado__presente=0 AND entradas_puntaje_copia_tipo__presente=0)),
    CHECK(entradas_recuerdo_diferido__estado='PRESENTE' OR (entradas_recuerdo_diferido_campo__presente=0 AND entradas_recuerdo_diferido_cod_pregunta__presente=0 AND entradas_recuerdo_diferido_cod_respuesta__presente=0 AND entradas_recuerdo_diferido_estado__presente=0 AND entradas_recuerdo_diferido_tipo__presente=0)),
    CHECK(entradas_residencia_bolivia__estado='PRESENTE' OR (entradas_residencia_bolivia_campo__presente=0 AND entradas_residencia_bolivia_cod_pregunta__presente=0 AND entradas_residencia_bolivia_cod_respuesta__presente=0 AND entradas_residencia_bolivia_estado__presente=0 AND entradas_residencia_bolivia_tipo__presente=0)),
    CHECK(entradas_segundos_parte_b__estado='PRESENTE' OR (entradas_segundos_parte_b_campo__presente=0 AND entradas_segundos_parte_b_cod_pregunta__presente=0 AND entradas_segundos_parte_b_cod_respuesta__presente=0 AND entradas_segundos_parte_b_estado__presente=0 AND entradas_segundos_parte_b_tipo__presente=0)),
    CHECK(identidad__estado='PRESENTE' OR (identidad_edicion_protocolo__estado='AUSENTE' AND identidad_forma__estado='AUSENTE' AND identidad_idioma__estado='AUSENTE' AND identidad_instrumento__estado='AUSENTE')),
    CHECK(identidad_edicion_protocolo__estado='PRESENTE' OR (identidad_edicion_protocolo_declarado__presente=0 AND identidad_edicion_protocolo_estado__presente=0 AND identidad_edicion_protocolo_requerido_por_metodo__presente=0)),
    CHECK(identidad_forma__estado='PRESENTE' OR (identidad_forma_declarado__presente=0 AND identidad_forma_estado__presente=0 AND identidad_forma_requerido_por_metodo__presente=0)),
    CHECK(identidad_idioma__estado='PRESENTE' OR (identidad_idioma_declarado__presente=0 AND identidad_idioma_estado__presente=0 AND identidad_idioma_requerido_por_metodo__presente=0)),
    CHECK(identidad_instrumento__estado='PRESENTE' OR (identidad_instrumento_declarado__presente=0 AND identidad_instrumento_estado__presente=0 AND identidad_instrumento_requerido_por_metodo__presente=0)),
    CHECK(procedencia__estado='PRESENTE' OR (procedencia_cod_documento_clinico__presente=0 AND procedencia_cod_personal_aplicador__presente=0 AND procedencia_cod_usuario_registro__presente=0 AND procedencia_estado_documento__presente=0 AND procedencia_fecha_clinica__presente=0 AND procedencia_registrado_en__presente=0 AND procedencia_tiene_localizador__presente=0))
);

CREATE TABLE ret_aplicaciones_aplicabilidad_requiere_ve_c031 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_comprobacion_adqui_c030(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_comprobacion_adquisicion_aplicaciones__c032 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    dto__cantidad BIGINT NOT NULL CHECK(dto__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_comprobacion_adqui_c030(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(dto__estado='PRESENTE' OR dto__cantidad=0)
);

CREATE TABLE ret_comprobacion_adquisicion_aplicaciones__c033 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_comprobacion_adquisicion_aplicaciones__c032(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(dto__estado='PRESENTE' OR (estado__presente=0 AND motivos__estado='AUSENTE'))
);

CREATE TABLE ret_comprobacion_adquisicion_aplicaciones__c034 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_comprobacion_adqui_c030(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_salida_perfil_instrumental_dominios_c035 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    componentes__estado VARCHAR(8) NOT NULL CHECK(componentes__estado IN ('AUSENTE','NULL','PRESENTE')),
    componentes__cantidad BIGINT NOT NULL CHECK(componentes__cantidad>=0),
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    motivos__cantidad BIGINT NOT NULL CHECK(motivos__cantidad>=0),
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    r_clinico__presente INTEGER NOT NULL,
    r_clinico TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(componentes__estado='PRESENTE' OR componentes__cantidad=0),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(motivos__estado='PRESENTE' OR motivos__cantidad=0),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(r_clinico__presente IN (0,1)),
    CHECK(r_clinico__presente=1 OR r_clinico IS NULL),
    CHECK(dto__estado='PRESENTE' OR (componentes__estado='AUSENTE' AND estado__presente=0 AND motivos__estado='AUSENTE' AND nombre__presente=0 AND r_clinico__presente=0))
);

CREATE TABLE ret_perfil_instrumental_dominios_component_c036 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    captura_requerida__estado VARCHAR(8) NOT NULL CHECK(captura_requerida__estado IN ('AUSENTE','NULL','PRESENTE')),
    captura_requerida_calculos__estado VARCHAR(8) NOT NULL CHECK(captura_requerida_calculos__estado IN ('AUSENTE','NULL','PRESENTE')),
    captura_requerida_calculos__cantidad BIGINT NOT NULL CHECK(captura_requerida_calculos__cantidad>=0),
    captura_requerida_campos__estado VARCHAR(8) NOT NULL CHECK(captura_requerida_campos__estado IN ('AUSENTE','NULL','PRESENTE')),
    captura_requerida_campos_a_segundos__presente INTEGER NOT NULL,
    captura_requerida_campos_a_segundos TEXT,
    captura_requerida_campos_a_validas__presente INTEGER NOT NULL,
    captura_requerida_campos_a_validas TEXT,
    captura_requerida_campos_aciertos_clave_semantica__presente INTEGER NOT NULL,
    captura_requerida_campos_aciertos_clave_semantica TEXT,
    captura_requerida_campos_aciertos_espontaneos__presente INTEGER NOT NULL,
    captura_requerida_campos_aciertos_espontaneos TEXT,
    captura_requerida_campos_aciertos_rek__presente INTEGER NOT NULL,
    captura_requerida_campos_aciertos_rek TEXT,
    captura_requerida_campos_adaptacion_nacional__presente INTEGER NOT NULL,
    captura_requerida_campos_adaptacion_nacional TEXT,
    captura_requerida_campos_adicional_claves_1__presente INTEGER NOT NULL,
    captura_requerida_campos_adicional_claves_1 TEXT,
    captura_requerida_campos_adicional_claves_2__presente INTEGER NOT NULL,
    captura_requerida_campos_adicional_claves_2 TEXT,
    captura_requerida_campos_adicional_claves_3__presente INTEGER NOT NULL,
    captura_requerida_campos_adicional_claves_3 TEXT,
    cap_req_cam_adi_cla_dif__presente INTEGER NOT NULL,
    cap_req_cam_adi_cla_dif TEXT,
    cap_req_cam_adm_est__presente INTEGER NOT NULL,
    cap_req_cam_adm_est TEXT,
    captura_requerida_campos_administrados__presente INTEGER NOT NULL,
    captura_requerida_campos_administrados TEXT,
    captura_requerida_campos_animales_segundos__presente INTEGER NOT NULL,
    captura_requerida_campos_animales_segundos TEXT,
    captura_requerida_campos_animales_validas__presente INTEGER NOT NULL,
    captura_requerida_campos_animales_validas TEXT,
    captura_requerida_campos_audicion_compatible__presente INTEGER NOT NULL,
    captura_requerida_campos_audicion_compatible TEXT,
    captura_requerida_campos_audicion_vision__presente INTEGER NOT NULL,
    captura_requerida_campos_audicion_vision TEXT,
    captura_requerida_campos_ayudas_fonemicas__presente INTEGER NOT NULL,
    captura_requerida_campos_ayudas_fonemicas TEXT,
    captura_requerida_campos_bruto_subprueba__presente INTEGER NOT NULL,
    captura_requerida_campos_bruto_subprueba TEXT,
    captura_requerida_campos_codificacion_verificada__presente INTEGER NOT NULL,
    captura_requerida_campos_codificacion_verificada TEXT,
    captura_requerida_campos_colores_correctos__presente INTEGER NOT NULL,
    captura_requerida_campos_colores_correctos TEXT,
    captura_requerida_campos_condicion__presente INTEGER NOT NULL,
    captura_requerida_campos_condicion TEXT,
    captura_requerida_campos_dto_informacion__presente INTEGER NOT NULL,
    captura_requerida_campos_dto_informacion TEXT,
    captura_requerida_campos_control_visual__presente INTEGER NOT NULL,
    captura_requerida_campos_control_visual TEXT,
    captura_requerida_campos_correctos__presente INTEGER NOT NULL,
    captura_requerida_campos_correctos TEXT,
    captura_requerida_campos_demora_minutos__presente INTEGER NOT NULL,
    captura_requerida_campos_demora_minutos TEXT,
    captura_requerida_campos_elementos_revisados__presente INTEGER NOT NULL,
    captura_requerida_campos_elementos_revisados TEXT,
    captura_requerida_campos_ensayo_1__presente INTEGER NOT NULL,
    captura_requerida_campos_ensayo_1 TEXT,
    captura_requerida_campos_ensayo_2__presente INTEGER NOT NULL,
    captura_requerida_campos_ensayo_2 TEXT,
    captura_requerida_campos_ensayo_3__presente INTEGER NOT NULL,
    captura_requerida_campos_ensayo_3 TEXT,
    captura_requerida_campos_ensayos__presente INTEGER NOT NULL,
    captura_requerida_campos_ensayos TEXT,
    captura_requerida_campos_errores_observados__presente INTEGER NOT NULL,
    captura_requerida_campos_errores_observados TEXT,
    captura_requerida_campos_f_segundos__presente INTEGER NOT NULL,
    captura_requerida_campos_f_segundos TEXT,
    captura_requerida_campos_f_validas__presente INTEGER NOT NULL,
    captura_requerida_campos_f_validas TEXT,
    cap_req_cam_fal_ala_no_rel__presente INTEGER NOT NULL,
    cap_req_cam_fal_ala_no_rel TEXT,
    cap_req_cam_fal_ala_rel__presente INTEGER NOT NULL,
    cap_req_cam_fal_ala_rel TEXT,
    captura_requerida_campos_finalizada__presente INTEGER NOT NULL,
    captura_requerida_campos_finalizada TEXT,
    captura_requerida_campos_fluidez__presente INTEGER NOT NULL,
    captura_requerida_campos_fluidez TEXT,
    captura_requerida_campos_forma__presente INTEGER NOT NULL,
    captura_requerida_campos_forma TEXT,
    captura_requerida_campos_forma_l_aciertos__presente INTEGER NOT NULL,
    captura_requerida_campos_forma_l_aciertos TEXT,
    captura_requerida_campos_forma_n_aciertos__presente INTEGER NOT NULL,
    captura_requerida_campos_forma_n_aciertos TEXT,
    captura_requerida_campos_libre_1__presente INTEGER NOT NULL,
    captura_requerida_campos_libre_1 TEXT,
    captura_requerida_campos_libre_2__presente INTEGER NOT NULL,
    captura_requerida_campos_libre_2 TEXT,
    captura_requerida_campos_libre_3__presente INTEGER NOT NULL,
    captura_requerida_campos_libre_3 TEXT,
    captura_requerida_campos_libre_diferido__presente INTEGER NOT NULL,
    captura_requerida_campos_libre_diferido TEXT,
    captura_requerida_campos_longitud_maxima__presente INTEGER NOT NULL,
    captura_requerida_campos_longitud_maxima TEXT,
    captura_requerida_campos_movimientos_por_problema__presente INTEGER NOT NULL,
    captura_requerida_campos_movimientos_por_problema TEXT,
    cap_req_cam_mue_dis_doc__presente INTEGER NOT NULL,
    cap_req_cam_mue_dis_doc TEXT,
    captura_requerida_campos_palabra_color_correctas__presente INTEGER NOT NULL,
    captura_requerida_campos_palabra_color_correctas TEXT,
    captura_requerida_campos_palabras_correctas__presente INTEGER NOT NULL,
    captura_requerida_campos_palabras_correctas TEXT,
    cap_req_cam_pre_por_ele__presente INTEGER NOT NULL,
    cap_req_cam_pre_por_ele TEXT,
    captura_requerida_campos_puntaje_copia__presente INTEGER NOT NULL,
    captura_requerida_campos_puntaje_copia TEXT,
    captura_requerida_campos_puntaje_total__presente INTEGER NOT NULL,
    captura_requerida_campos_puntaje_total TEXT,
    captura_requerida_campos_puntajes_por_elemento__presente INTEGER NOT NULL,
    captura_requerida_campos_puntajes_por_elemento TEXT,
    captura_requerida_campos_puntajes_por_ensayo__presente INTEGER NOT NULL,
    captura_requerida_campos_puntajes_por_ensayo TEXT,
    captura_requerida_campos_recuerdo_diferido__presente INTEGER NOT NULL,
    captura_requerida_campos_recuerdo_diferido TEXT,
    captura_requerida_campos_referencia_muestra__presente INTEGER NOT NULL,
    captura_requerida_campos_referencia_muestra TEXT,
    captura_requerida_campos_referencia_produccion__presente INTEGER NOT NULL,
    captura_requerida_campos_referencia_produccion TEXT,
    captura_requerida_campos_repeticion_total__presente INTEGER NOT NULL,
    captura_requerida_campos_repeticion_total TEXT,
    captura_requerida_campos_repeticiones_intrusiones__presente INTEGER NOT NULL,
    captura_requerida_campos_repeticiones_intrusiones TEXT,
    captura_requerida_campos_resueltos__presente INTEGER NOT NULL,
    captura_requerida_campos_resueltos TEXT,
    captura_requerida_campos_s_segundos__presente INTEGER NOT NULL,
    captura_requerida_campos_s_segundos TEXT,
    captura_requerida_campos_s_validas__presente INTEGER NOT NULL,
    captura_requerida_campos_s_validas TEXT,
    captura_requerida_campos_segundos_colores__presente INTEGER NOT NULL,
    captura_requerida_campos_segundos_colores TEXT,
    captura_requerida_campos_segundos_palabra_color__presente INTEGER NOT NULL,
    captura_requerida_campos_segundos_palabra_color TEXT,
    captura_requerida_campos_segundos_palabras__presente INTEGER NOT NULL,
    captura_requerida_campos_segundos_palabras TEXT,
    captura_requerida_campos_segundos_parte_b__presente INTEGER NOT NULL,
    captura_requerida_campos_segundos_parte_b TEXT,
    captura_requerida_campos_subprueba__presente INTEGER NOT NULL,
    captura_requerida_campos_subprueba TEXT,
    captura_requerida_campos_subprueba_forma__presente INTEGER NOT NULL,
    captura_requerida_campos_subprueba_forma TEXT,
    captura_requerida_campos_tarea__presente INTEGER NOT NULL,
    captura_requerida_campos_tarea TEXT,
    captura_requerida_campos_tiempos_por_problema__presente INTEGER NOT NULL,
    captura_requerida_campos_tiempos_por_problema TEXT,
    captura_requerida_campos_violaciones_regla__presente INTEGER NOT NULL,
    captura_requerida_campos_violaciones_regla TEXT,
    captura_requerida_campos_violaciones_tiempo__presente INTEGER NOT NULL,
    captura_requerida_campos_violaciones_tiempo TEXT,
    captura_requerida_estado_metodo__presente INTEGER NOT NULL,
    captura_requerida_estado_metodo TEXT,
    captura_requerida_fuente__presente INTEGER NOT NULL,
    captura_requerida_fuente TEXT,
    captura_requerida_instrumento_administrado__presente INTEGER NOT NULL,
    captura_requerida_instrumento_administrado TEXT,
    captura_requerida_instrumento_referencia__presente INTEGER NOT NULL,
    captura_requerida_instrumento_referencia TEXT,
    captura_requerida_limite__presente INTEGER NOT NULL,
    captura_requerida_limite TEXT,
    captura_requerida_mediciones_residente__presente INTEGER NOT NULL,
    captura_requerida_mediciones_residente TEXT,
    captura_requerida_metodos_disponibles__estado VARCHAR(8) NOT NULL CHECK(captura_requerida_metodos_disponibles__estado IN ('AUSENTE','NULL','PRESENTE')),
    captura_requerida_metodos_disponibles__cantidad BIGINT NOT NULL CHECK(captura_requerida_metodos_disponibles__cantidad>=0),
    captura_requerida_normalizadores__estado VARCHAR(8) NOT NULL CHECK(captura_requerida_normalizadores__estado IN ('AUSENTE','NULL','PRESENTE')),
    captura_requerida_normalizadores__cantidad BIGINT NOT NULL CHECK(captura_requerida_normalizadores__cantidad>=0),
    captura_requerida_version__presente INTEGER NOT NULL,
    captura_requerida_version TEXT,
    concepto__estado VARCHAR(8) NOT NULL CHECK(concepto__estado IN ('AUSENTE','NULL','PRESENTE')),
    concepto_codigo__presente INTEGER NOT NULL,
    concepto_codigo TEXT,
    concepto_definicion__presente INTEGER NOT NULL,
    concepto_definicion TEXT,
    concepto_estado_fuente__presente INTEGER NOT NULL,
    concepto_estado_fuente TEXT,
    concepto_limites__presente INTEGER NOT NULL,
    concepto_limites TEXT,
    concepto_nombre__presente INTEGER NOT NULL,
    concepto_nombre TEXT,
    concepto_procedencia__estado VARCHAR(8) NOT NULL CHECK(concepto_procedencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    concepto_procedencia__cantidad BIGINT NOT NULL CHECK(concepto_procedencia__cantidad>=0),
    concepto_tipo__presente INTEGER NOT NULL,
    concepto_tipo TEXT,
    dominio__presente INTEGER NOT NULL,
    dominio TEXT,
    estado_medicion__presente INTEGER NOT NULL,
    estado_medicion TEXT,
    mediciones__estado VARCHAR(8) NOT NULL CHECK(mediciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    mediciones__cantidad BIGINT NOT NULL CHECK(mediciones__cantidad>=0),
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    motivos__cantidad BIGINT NOT NULL CHECK(motivos__cantidad>=0),
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    relacion_original__estado VARCHAR(8) NOT NULL CHECK(relacion_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    relacion_original_codigo__presente INTEGER NOT NULL,
    relacion_original_codigo TEXT,
    relacion_original_destino__presente INTEGER NOT NULL,
    relacion_original_destino TEXT,
    relacion_original_estado_fuente__presente INTEGER NOT NULL,
    relacion_original_estado_fuente TEXT,
    relacion_original_fila__presente INTEGER NOT NULL,
    relacion_original_fila BIGINT,
    relacion_original_limites__presente INTEGER NOT NULL,
    relacion_original_limites TEXT,
    relacion_original_origen__presente INTEGER NOT NULL,
    relacion_original_origen TEXT,
    relacion_original_relacion__presente INTEGER NOT NULL,
    relacion_original_relacion TEXT,
    relacion_original_significado__presente INTEGER NOT NULL,
    relacion_original_significado TEXT,
    r_clinico__presente INTEGER NOT NULL,
    r_clinico TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_salida_perfil_instrumental_dominios_c035(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(captura_requerida_calculos__estado='PRESENTE' OR captura_requerida_calculos__cantidad=0),
    CHECK(captura_requerida_campos_a_segundos__presente IN (0,1)),
    CHECK(captura_requerida_campos_a_segundos__presente=1 OR captura_requerida_campos_a_segundos IS NULL),
    CHECK(captura_requerida_campos_a_validas__presente IN (0,1)),
    CHECK(captura_requerida_campos_a_validas__presente=1 OR captura_requerida_campos_a_validas IS NULL),
    CHECK(captura_requerida_campos_aciertos_clave_semantica__presente IN (0,1)),
    CHECK(captura_requerida_campos_aciertos_clave_semantica__presente=1 OR captura_requerida_campos_aciertos_clave_semantica IS NULL),
    CHECK(captura_requerida_campos_aciertos_espontaneos__presente IN (0,1)),
    CHECK(captura_requerida_campos_aciertos_espontaneos__presente=1 OR captura_requerida_campos_aciertos_espontaneos IS NULL),
    CHECK(captura_requerida_campos_aciertos_rek__presente IN (0,1)),
    CHECK(captura_requerida_campos_aciertos_rek__presente=1 OR captura_requerida_campos_aciertos_rek IS NULL),
    CHECK(captura_requerida_campos_adaptacion_nacional__presente IN (0,1)),
    CHECK(captura_requerida_campos_adaptacion_nacional__presente=1 OR captura_requerida_campos_adaptacion_nacional IS NULL),
    CHECK(captura_requerida_campos_adicional_claves_1__presente IN (0,1)),
    CHECK(captura_requerida_campos_adicional_claves_1__presente=1 OR captura_requerida_campos_adicional_claves_1 IS NULL),
    CHECK(captura_requerida_campos_adicional_claves_2__presente IN (0,1)),
    CHECK(captura_requerida_campos_adicional_claves_2__presente=1 OR captura_requerida_campos_adicional_claves_2 IS NULL),
    CHECK(captura_requerida_campos_adicional_claves_3__presente IN (0,1)),
    CHECK(captura_requerida_campos_adicional_claves_3__presente=1 OR captura_requerida_campos_adicional_claves_3 IS NULL),
    CHECK(cap_req_cam_adi_cla_dif__presente IN (0,1)),
    CHECK(cap_req_cam_adi_cla_dif__presente=1 OR cap_req_cam_adi_cla_dif IS NULL),
    CHECK(cap_req_cam_adm_est__presente IN (0,1)),
    CHECK(cap_req_cam_adm_est__presente=1 OR cap_req_cam_adm_est IS NULL),
    CHECK(captura_requerida_campos_administrados__presente IN (0,1)),
    CHECK(captura_requerida_campos_administrados__presente=1 OR captura_requerida_campos_administrados IS NULL),
    CHECK(captura_requerida_campos_animales_segundos__presente IN (0,1)),
    CHECK(captura_requerida_campos_animales_segundos__presente=1 OR captura_requerida_campos_animales_segundos IS NULL),
    CHECK(captura_requerida_campos_animales_validas__presente IN (0,1)),
    CHECK(captura_requerida_campos_animales_validas__presente=1 OR captura_requerida_campos_animales_validas IS NULL),
    CHECK(captura_requerida_campos_audicion_compatible__presente IN (0,1)),
    CHECK(captura_requerida_campos_audicion_compatible__presente=1 OR captura_requerida_campos_audicion_compatible IS NULL),
    CHECK(captura_requerida_campos_audicion_vision__presente IN (0,1)),
    CHECK(captura_requerida_campos_audicion_vision__presente=1 OR captura_requerida_campos_audicion_vision IS NULL),
    CHECK(captura_requerida_campos_ayudas_fonemicas__presente IN (0,1)),
    CHECK(captura_requerida_campos_ayudas_fonemicas__presente=1 OR captura_requerida_campos_ayudas_fonemicas IS NULL),
    CHECK(captura_requerida_campos_bruto_subprueba__presente IN (0,1)),
    CHECK(captura_requerida_campos_bruto_subprueba__presente=1 OR captura_requerida_campos_bruto_subprueba IS NULL),
    CHECK(captura_requerida_campos_codificacion_verificada__presente IN (0,1)),
    CHECK(captura_requerida_campos_codificacion_verificada__presente=1 OR captura_requerida_campos_codificacion_verificada IS NULL),
    CHECK(captura_requerida_campos_colores_correctos__presente IN (0,1)),
    CHECK(captura_requerida_campos_colores_correctos__presente=1 OR captura_requerida_campos_colores_correctos IS NULL),
    CHECK(captura_requerida_campos_condicion__presente IN (0,1)),
    CHECK(captura_requerida_campos_condicion__presente=1 OR captura_requerida_campos_condicion IS NULL),
    CHECK(captura_requerida_campos_dto_informacion__presente IN (0,1)),
    CHECK(captura_requerida_campos_dto_informacion__presente=1 OR captura_requerida_campos_dto_informacion IS NULL),
    CHECK(captura_requerida_campos_control_visual__presente IN (0,1)),
    CHECK(captura_requerida_campos_control_visual__presente=1 OR captura_requerida_campos_control_visual IS NULL),
    CHECK(captura_requerida_campos_correctos__presente IN (0,1)),
    CHECK(captura_requerida_campos_correctos__presente=1 OR captura_requerida_campos_correctos IS NULL),
    CHECK(captura_requerida_campos_demora_minutos__presente IN (0,1)),
    CHECK(captura_requerida_campos_demora_minutos__presente=1 OR captura_requerida_campos_demora_minutos IS NULL),
    CHECK(captura_requerida_campos_elementos_revisados__presente IN (0,1)),
    CHECK(captura_requerida_campos_elementos_revisados__presente=1 OR captura_requerida_campos_elementos_revisados IS NULL),
    CHECK(captura_requerida_campos_ensayo_1__presente IN (0,1)),
    CHECK(captura_requerida_campos_ensayo_1__presente=1 OR captura_requerida_campos_ensayo_1 IS NULL),
    CHECK(captura_requerida_campos_ensayo_2__presente IN (0,1)),
    CHECK(captura_requerida_campos_ensayo_2__presente=1 OR captura_requerida_campos_ensayo_2 IS NULL),
    CHECK(captura_requerida_campos_ensayo_3__presente IN (0,1)),
    CHECK(captura_requerida_campos_ensayo_3__presente=1 OR captura_requerida_campos_ensayo_3 IS NULL),
    CHECK(captura_requerida_campos_ensayos__presente IN (0,1)),
    CHECK(captura_requerida_campos_ensayos__presente=1 OR captura_requerida_campos_ensayos IS NULL),
    CHECK(captura_requerida_campos_errores_observados__presente IN (0,1)),
    CHECK(captura_requerida_campos_errores_observados__presente=1 OR captura_requerida_campos_errores_observados IS NULL),
    CHECK(captura_requerida_campos_f_segundos__presente IN (0,1)),
    CHECK(captura_requerida_campos_f_segundos__presente=1 OR captura_requerida_campos_f_segundos IS NULL),
    CHECK(captura_requerida_campos_f_validas__presente IN (0,1)),
    CHECK(captura_requerida_campos_f_validas__presente=1 OR captura_requerida_campos_f_validas IS NULL),
    CHECK(cap_req_cam_fal_ala_no_rel__presente IN (0,1)),
    CHECK(cap_req_cam_fal_ala_no_rel__presente=1 OR cap_req_cam_fal_ala_no_rel IS NULL),
    CHECK(cap_req_cam_fal_ala_rel__presente IN (0,1)),
    CHECK(cap_req_cam_fal_ala_rel__presente=1 OR cap_req_cam_fal_ala_rel IS NULL),
    CHECK(captura_requerida_campos_finalizada__presente IN (0,1)),
    CHECK(captura_requerida_campos_finalizada__presente=1 OR captura_requerida_campos_finalizada IS NULL),
    CHECK(captura_requerida_campos_fluidez__presente IN (0,1)),
    CHECK(captura_requerida_campos_fluidez__presente=1 OR captura_requerida_campos_fluidez IS NULL),
    CHECK(captura_requerida_campos_forma__presente IN (0,1)),
    CHECK(captura_requerida_campos_forma__presente=1 OR captura_requerida_campos_forma IS NULL),
    CHECK(captura_requerida_campos_forma_l_aciertos__presente IN (0,1)),
    CHECK(captura_requerida_campos_forma_l_aciertos__presente=1 OR captura_requerida_campos_forma_l_aciertos IS NULL),
    CHECK(captura_requerida_campos_forma_n_aciertos__presente IN (0,1)),
    CHECK(captura_requerida_campos_forma_n_aciertos__presente=1 OR captura_requerida_campos_forma_n_aciertos IS NULL),
    CHECK(captura_requerida_campos_libre_1__presente IN (0,1)),
    CHECK(captura_requerida_campos_libre_1__presente=1 OR captura_requerida_campos_libre_1 IS NULL),
    CHECK(captura_requerida_campos_libre_2__presente IN (0,1)),
    CHECK(captura_requerida_campos_libre_2__presente=1 OR captura_requerida_campos_libre_2 IS NULL),
    CHECK(captura_requerida_campos_libre_3__presente IN (0,1)),
    CHECK(captura_requerida_campos_libre_3__presente=1 OR captura_requerida_campos_libre_3 IS NULL),
    CHECK(captura_requerida_campos_libre_diferido__presente IN (0,1)),
    CHECK(captura_requerida_campos_libre_diferido__presente=1 OR captura_requerida_campos_libre_diferido IS NULL),
    CHECK(captura_requerida_campos_longitud_maxima__presente IN (0,1)),
    CHECK(captura_requerida_campos_longitud_maxima__presente=1 OR captura_requerida_campos_longitud_maxima IS NULL),
    CHECK(captura_requerida_campos_movimientos_por_problema__presente IN (0,1)),
    CHECK(captura_requerida_campos_movimientos_por_problema__presente=1 OR captura_requerida_campos_movimientos_por_problema IS NULL),
    CHECK(cap_req_cam_mue_dis_doc__presente IN (0,1)),
    CHECK(cap_req_cam_mue_dis_doc__presente=1 OR cap_req_cam_mue_dis_doc IS NULL),
    CHECK(captura_requerida_campos_palabra_color_correctas__presente IN (0,1)),
    CHECK(captura_requerida_campos_palabra_color_correctas__presente=1 OR captura_requerida_campos_palabra_color_correctas IS NULL),
    CHECK(captura_requerida_campos_palabras_correctas__presente IN (0,1)),
    CHECK(captura_requerida_campos_palabras_correctas__presente=1 OR captura_requerida_campos_palabras_correctas IS NULL),
    CHECK(cap_req_cam_pre_por_ele__presente IN (0,1)),
    CHECK(cap_req_cam_pre_por_ele__presente=1 OR cap_req_cam_pre_por_ele IS NULL),
    CHECK(captura_requerida_campos_puntaje_copia__presente IN (0,1)),
    CHECK(captura_requerida_campos_puntaje_copia__presente=1 OR captura_requerida_campos_puntaje_copia IS NULL),
    CHECK(captura_requerida_campos_puntaje_total__presente IN (0,1)),
    CHECK(captura_requerida_campos_puntaje_total__presente=1 OR captura_requerida_campos_puntaje_total IS NULL),
    CHECK(captura_requerida_campos_puntajes_por_elemento__presente IN (0,1)),
    CHECK(captura_requerida_campos_puntajes_por_elemento__presente=1 OR captura_requerida_campos_puntajes_por_elemento IS NULL),
    CHECK(captura_requerida_campos_puntajes_por_ensayo__presente IN (0,1)),
    CHECK(captura_requerida_campos_puntajes_por_ensayo__presente=1 OR captura_requerida_campos_puntajes_por_ensayo IS NULL),
    CHECK(captura_requerida_campos_recuerdo_diferido__presente IN (0,1)),
    CHECK(captura_requerida_campos_recuerdo_diferido__presente=1 OR captura_requerida_campos_recuerdo_diferido IS NULL),
    CHECK(captura_requerida_campos_referencia_muestra__presente IN (0,1)),
    CHECK(captura_requerida_campos_referencia_muestra__presente=1 OR captura_requerida_campos_referencia_muestra IS NULL),
    CHECK(captura_requerida_campos_referencia_produccion__presente IN (0,1)),
    CHECK(captura_requerida_campos_referencia_produccion__presente=1 OR captura_requerida_campos_referencia_produccion IS NULL),
    CHECK(captura_requerida_campos_repeticion_total__presente IN (0,1)),
    CHECK(captura_requerida_campos_repeticion_total__presente=1 OR captura_requerida_campos_repeticion_total IS NULL),
    CHECK(captura_requerida_campos_repeticiones_intrusiones__presente IN (0,1)),
    CHECK(captura_requerida_campos_repeticiones_intrusiones__presente=1 OR captura_requerida_campos_repeticiones_intrusiones IS NULL),
    CHECK(captura_requerida_campos_resueltos__presente IN (0,1)),
    CHECK(captura_requerida_campos_resueltos__presente=1 OR captura_requerida_campos_resueltos IS NULL),
    CHECK(captura_requerida_campos_s_segundos__presente IN (0,1)),
    CHECK(captura_requerida_campos_s_segundos__presente=1 OR captura_requerida_campos_s_segundos IS NULL),
    CHECK(captura_requerida_campos_s_validas__presente IN (0,1)),
    CHECK(captura_requerida_campos_s_validas__presente=1 OR captura_requerida_campos_s_validas IS NULL),
    CHECK(captura_requerida_campos_segundos_colores__presente IN (0,1)),
    CHECK(captura_requerida_campos_segundos_colores__presente=1 OR captura_requerida_campos_segundos_colores IS NULL),
    CHECK(captura_requerida_campos_segundos_palabra_color__presente IN (0,1)),
    CHECK(captura_requerida_campos_segundos_palabra_color__presente=1 OR captura_requerida_campos_segundos_palabra_color IS NULL),
    CHECK(captura_requerida_campos_segundos_palabras__presente IN (0,1)),
    CHECK(captura_requerida_campos_segundos_palabras__presente=1 OR captura_requerida_campos_segundos_palabras IS NULL),
    CHECK(captura_requerida_campos_segundos_parte_b__presente IN (0,1)),
    CHECK(captura_requerida_campos_segundos_parte_b__presente=1 OR captura_requerida_campos_segundos_parte_b IS NULL),
    CHECK(captura_requerida_campos_subprueba__presente IN (0,1)),
    CHECK(captura_requerida_campos_subprueba__presente=1 OR captura_requerida_campos_subprueba IS NULL),
    CHECK(captura_requerida_campos_subprueba_forma__presente IN (0,1)),
    CHECK(captura_requerida_campos_subprueba_forma__presente=1 OR captura_requerida_campos_subprueba_forma IS NULL),
    CHECK(captura_requerida_campos_tarea__presente IN (0,1)),
    CHECK(captura_requerida_campos_tarea__presente=1 OR captura_requerida_campos_tarea IS NULL),
    CHECK(captura_requerida_campos_tiempos_por_problema__presente IN (0,1)),
    CHECK(captura_requerida_campos_tiempos_por_problema__presente=1 OR captura_requerida_campos_tiempos_por_problema IS NULL),
    CHECK(captura_requerida_campos_violaciones_regla__presente IN (0,1)),
    CHECK(captura_requerida_campos_violaciones_regla__presente=1 OR captura_requerida_campos_violaciones_regla IS NULL),
    CHECK(captura_requerida_campos_violaciones_tiempo__presente IN (0,1)),
    CHECK(captura_requerida_campos_violaciones_tiempo__presente=1 OR captura_requerida_campos_violaciones_tiempo IS NULL),
    CHECK(captura_requerida_estado_metodo__presente IN (0,1)),
    CHECK(captura_requerida_estado_metodo__presente=1 OR captura_requerida_estado_metodo IS NULL),
    CHECK(captura_requerida_fuente__presente IN (0,1)),
    CHECK(captura_requerida_fuente__presente=1 OR captura_requerida_fuente IS NULL),
    CHECK(captura_requerida_instrumento_administrado__presente IN (0,1)),
    CHECK(captura_requerida_instrumento_administrado__presente=1 OR captura_requerida_instrumento_administrado IS NULL),
    CHECK(captura_requerida_instrumento_referencia__presente IN (0,1)),
    CHECK(captura_requerida_instrumento_referencia__presente=1 OR captura_requerida_instrumento_referencia IS NULL),
    CHECK(captura_requerida_limite__presente IN (0,1)),
    CHECK(captura_requerida_limite__presente=1 OR captura_requerida_limite IS NULL),
    CHECK(captura_requerida_mediciones_residente__presente IN (0,1)),
    CHECK(captura_requerida_mediciones_residente__presente=1 OR captura_requerida_mediciones_residente IS NULL),
    CHECK(captura_requerida_metodos_disponibles__estado='PRESENTE' OR captura_requerida_metodos_disponibles__cantidad=0),
    CHECK(captura_requerida_normalizadores__estado='PRESENTE' OR captura_requerida_normalizadores__cantidad=0),
    CHECK(captura_requerida_version__presente IN (0,1)),
    CHECK(captura_requerida_version__presente=1 OR captura_requerida_version IS NULL),
    CHECK(concepto_codigo__presente IN (0,1)),
    CHECK(concepto_codigo__presente=1 OR concepto_codigo IS NULL),
    CHECK(concepto_definicion__presente IN (0,1)),
    CHECK(concepto_definicion__presente=1 OR concepto_definicion IS NULL),
    CHECK(concepto_estado_fuente__presente IN (0,1)),
    CHECK(concepto_estado_fuente__presente=1 OR concepto_estado_fuente IS NULL),
    CHECK(concepto_limites__presente IN (0,1)),
    CHECK(concepto_limites__presente=1 OR concepto_limites IS NULL),
    CHECK(concepto_nombre__presente IN (0,1)),
    CHECK(concepto_nombre__presente=1 OR concepto_nombre IS NULL),
    CHECK(concepto_procedencia__estado='PRESENTE' OR concepto_procedencia__cantidad=0),
    CHECK(concepto_tipo__presente IN (0,1)),
    CHECK(concepto_tipo__presente=1 OR concepto_tipo IS NULL),
    CHECK(dominio__presente IN (0,1)),
    CHECK(dominio__presente=1 OR dominio IS NULL),
    CHECK(estado_medicion__presente IN (0,1)),
    CHECK(estado_medicion__presente=1 OR estado_medicion IS NULL),
    CHECK(mediciones__estado='PRESENTE' OR mediciones__cantidad=0),
    CHECK(motivos__estado='PRESENTE' OR motivos__cantidad=0),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(relacion_original_codigo__presente IN (0,1)),
    CHECK(relacion_original_codigo__presente=1 OR relacion_original_codigo IS NULL),
    CHECK(relacion_original_destino__presente IN (0,1)),
    CHECK(relacion_original_destino__presente=1 OR relacion_original_destino IS NULL),
    CHECK(relacion_original_estado_fuente__presente IN (0,1)),
    CHECK(relacion_original_estado_fuente__presente=1 OR relacion_original_estado_fuente IS NULL),
    CHECK(relacion_original_fila__presente IN (0,1)),
    CHECK(relacion_original_fila__presente=1 OR relacion_original_fila IS NULL),
    CHECK(relacion_original_limites__presente IN (0,1)),
    CHECK(relacion_original_limites__presente=1 OR relacion_original_limites IS NULL),
    CHECK(relacion_original_origen__presente IN (0,1)),
    CHECK(relacion_original_origen__presente=1 OR relacion_original_origen IS NULL),
    CHECK(relacion_original_relacion__presente IN (0,1)),
    CHECK(relacion_original_relacion__presente=1 OR relacion_original_relacion IS NULL),
    CHECK(relacion_original_significado__presente IN (0,1)),
    CHECK(relacion_original_significado__presente=1 OR relacion_original_significado IS NULL),
    CHECK(r_clinico__presente IN (0,1)),
    CHECK(r_clinico__presente=1 OR r_clinico IS NULL),
    CHECK(dto__estado='PRESENTE' OR (captura_requerida__estado='AUSENTE' AND concepto__estado='AUSENTE' AND dominio__presente=0 AND estado_medicion__presente=0 AND mediciones__estado='AUSENTE' AND motivos__estado='AUSENTE' AND nombre__presente=0 AND relacion_original__estado='AUSENTE' AND r_clinico__presente=0)),
    CHECK(captura_requerida__estado='PRESENTE' OR (captura_requerida_calculos__estado='AUSENTE' AND captura_requerida_campos__estado='AUSENTE' AND captura_requerida_estado_metodo__presente=0 AND captura_requerida_fuente__presente=0 AND captura_requerida_instrumento_administrado__presente=0 AND captura_requerida_instrumento_referencia__presente=0 AND captura_requerida_limite__presente=0 AND captura_requerida_mediciones_residente__presente=0 AND captura_requerida_metodos_disponibles__estado='AUSENTE' AND captura_requerida_normalizadores__estado='AUSENTE' AND captura_requerida_version__presente=0)),
    CHECK(captura_requerida_campos__estado='PRESENTE' OR (captura_requerida_campos_a_segundos__presente=0 AND captura_requerida_campos_a_validas__presente=0 AND captura_requerida_campos_aciertos_clave_semantica__presente=0 AND captura_requerida_campos_aciertos_espontaneos__presente=0 AND captura_requerida_campos_aciertos_rek__presente=0 AND captura_requerida_campos_adaptacion_nacional__presente=0 AND captura_requerida_campos_adicional_claves_1__presente=0 AND captura_requerida_campos_adicional_claves_2__presente=0 AND captura_requerida_campos_adicional_claves_3__presente=0 AND cap_req_cam_adi_cla_dif__presente=0 AND cap_req_cam_adm_est__presente=0 AND captura_requerida_campos_administrados__presente=0 AND captura_requerida_campos_animales_segundos__presente=0 AND captura_requerida_campos_animales_validas__presente=0 AND captura_requerida_campos_audicion_compatible__presente=0 AND captura_requerida_campos_audicion_vision__presente=0 AND captura_requerida_campos_ayudas_fonemicas__presente=0 AND captura_requerida_campos_bruto_subprueba__presente=0 AND captura_requerida_campos_codificacion_verificada__presente=0 AND captura_requerida_campos_colores_correctos__presente=0 AND captura_requerida_campos_condicion__presente=0 AND captura_requerida_campos_dto_informacion__presente=0 AND captura_requerida_campos_control_visual__presente=0 AND captura_requerida_campos_correctos__presente=0 AND captura_requerida_campos_demora_minutos__presente=0 AND captura_requerida_campos_elementos_revisados__presente=0 AND captura_requerida_campos_ensayo_1__presente=0 AND captura_requerida_campos_ensayo_2__presente=0 AND captura_requerida_campos_ensayo_3__presente=0 AND captura_requerida_campos_ensayos__presente=0 AND captura_requerida_campos_errores_observados__presente=0 AND captura_requerida_campos_f_segundos__presente=0 AND captura_requerida_campos_f_validas__presente=0 AND cap_req_cam_fal_ala_no_rel__presente=0 AND cap_req_cam_fal_ala_rel__presente=0 AND captura_requerida_campos_finalizada__presente=0 AND captura_requerida_campos_fluidez__presente=0 AND captura_requerida_campos_forma__presente=0 AND captura_requerida_campos_forma_l_aciertos__presente=0 AND captura_requerida_campos_forma_n_aciertos__presente=0 AND captura_requerida_campos_libre_1__presente=0 AND captura_requerida_campos_libre_2__presente=0 AND captura_requerida_campos_libre_3__presente=0 AND captura_requerida_campos_libre_diferido__presente=0 AND captura_requerida_campos_longitud_maxima__presente=0 AND captura_requerida_campos_movimientos_por_problema__presente=0 AND cap_req_cam_mue_dis_doc__presente=0 AND captura_requerida_campos_palabra_color_correctas__presente=0 AND captura_requerida_campos_palabras_correctas__presente=0 AND cap_req_cam_pre_por_ele__presente=0 AND captura_requerida_campos_puntaje_copia__presente=0 AND captura_requerida_campos_puntaje_total__presente=0 AND captura_requerida_campos_puntajes_por_elemento__presente=0 AND captura_requerida_campos_puntajes_por_ensayo__presente=0 AND captura_requerida_campos_recuerdo_diferido__presente=0 AND captura_requerida_campos_referencia_muestra__presente=0 AND captura_requerida_campos_referencia_produccion__presente=0 AND captura_requerida_campos_repeticion_total__presente=0 AND captura_requerida_campos_repeticiones_intrusiones__presente=0 AND captura_requerida_campos_resueltos__presente=0 AND captura_requerida_campos_s_segundos__presente=0 AND captura_requerida_campos_s_validas__presente=0 AND captura_requerida_campos_segundos_colores__presente=0 AND captura_requerida_campos_segundos_palabra_color__presente=0 AND captura_requerida_campos_segundos_palabras__presente=0 AND captura_requerida_campos_segundos_parte_b__presente=0 AND captura_requerida_campos_subprueba__presente=0 AND captura_requerida_campos_subprueba_forma__presente=0 AND captura_requerida_campos_tarea__presente=0 AND captura_requerida_campos_tiempos_por_problema__presente=0 AND captura_requerida_campos_violaciones_regla__presente=0 AND captura_requerida_campos_violaciones_tiempo__presente=0)),
    CHECK(concepto__estado='PRESENTE' OR (concepto_codigo__presente=0 AND concepto_definicion__presente=0 AND concepto_estado_fuente__presente=0 AND concepto_limites__presente=0 AND concepto_nombre__presente=0 AND concepto_procedencia__estado='AUSENTE' AND concepto_tipo__presente=0)),
    CHECK(relacion_original__estado='PRESENTE' OR (relacion_original_codigo__presente=0 AND relacion_original_destino__presente=0 AND relacion_original_estado_fuente__presente=0 AND relacion_original_fila__presente=0 AND relacion_original_limites__presente=0 AND relacion_original_origen__presente=0 AND relacion_original_relacion__presente=0 AND relacion_original_significado__presente=0))
);

CREATE TABLE ret_componentes_captura_requerida_calculos_c037 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_dominios_component_c036(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_componentes_captura_requerida_metodos__c038 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_dominios_component_c036(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_componentes_captura_requerida_normaliz_c039 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_dominios_component_c036(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_componentes_concepto_procedencia_c040 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_dominios_component_c036(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_dominios_componentes_mediciones_c041 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicacion__presente INTEGER NOT NULL,
    aplicacion TEXT,
    autor__presente INTEGER NOT NULL,
    autor TEXT,
    captura__estado VARCHAR(8) NOT NULL CHECK(captura__estado IN ('AUSENTE','NULL','PRESENTE')),
    captura_aciertos_clave_semantica__presente INTEGER NOT NULL,
    captura_aciertos_clave_semantica BIGINT,
    captura_aciertos_espontaneos__presente INTEGER NOT NULL,
    captura_aciertos_espontaneos BIGINT,
    captura_administracion_estandarizada__presente INTEGER NOT NULL,
    captura_administracion_estandarizada INTEGER,
    captura_audicion_compatible__presente INTEGER NOT NULL,
    captura_audicion_compatible INTEGER,
    captura_condiciones_sensoriales__presente INTEGER NOT NULL,
    captura_condiciones_sensoriales INTEGER,
    captura_demora_minutos__presente INTEGER NOT NULL,
    captura_demora_minutos REAL,
    captura_demora_minutos__cero_negativo INTEGER NOT NULL DEFAULT 0,
    captura_edad_anios__presente INTEGER NOT NULL,
    captura_edad_anios BIGINT,
    captura_edicion_protocolo__presente INTEGER NOT NULL,
    captura_edicion_protocolo TEXT,
    captura_elementos_revisados__presente INTEGER NOT NULL,
    captura_elementos_revisados BIGINT,
    captura_ensayo_1__presente INTEGER NOT NULL,
    captura_ensayo_1 BIGINT,
    captura_ensayo_2__presente INTEGER NOT NULL,
    captura_ensayo_2 BIGINT,
    captura_ensayo_3__presente INTEGER NOT NULL,
    captura_ensayo_3 BIGINT,
    captura_ensayos__estado VARCHAR(8) NOT NULL CHECK(captura_ensayos__estado IN ('AUSENTE','NULL','PRESENTE')),
    captura_ensayos__cantidad BIGINT NOT NULL CHECK(captura_ensayos__cantidad>=0),
    captura_errores_observados__presente INTEGER NOT NULL,
    captura_errores_observados BIGINT,
    captura_escolaridad_anios__presente INTEGER NOT NULL,
    captura_escolaridad_anios BIGINT,
    captura_espanol_nativo__presente INTEGER NOT NULL,
    captura_espanol_nativo INTEGER,
    captura_finalizada__presente INTEGER NOT NULL,
    captura_finalizada INTEGER,
    captura_forma__presente INTEGER NOT NULL,
    captura_forma BIGINT,
    captura_forma_l_aciertos__presente INTEGER NOT NULL,
    captura_forma_l_aciertos BIGINT,
    captura_forma_n_aciertos__presente INTEGER NOT NULL,
    captura_forma_n_aciertos BIGINT,
    captura_idioma__presente INTEGER NOT NULL,
    captura_idioma TEXT,
    captura_instrumento__presente INTEGER NOT NULL,
    captura_instrumento TEXT,
    captura_lectoescritura__presente INTEGER NOT NULL,
    captura_lectoescritura INTEGER,
    captura_nacimiento_bolivia__presente INTEGER NOT NULL,
    captura_nacimiento_bolivia INTEGER,
    captura_protocolo__presente INTEGER NOT NULL,
    captura_protocolo TEXT,
    captura_puntaje_copia__presente INTEGER NOT NULL,
    captura_puntaje_copia REAL,
    captura_puntaje_copia__cero_negativo INTEGER NOT NULL DEFAULT 0,
    captura_recuerdo_diferido__presente INTEGER NOT NULL,
    captura_recuerdo_diferido BIGINT,
    captura_residencia_bolivia__presente INTEGER NOT NULL,
    captura_residencia_bolivia INTEGER,
    captura_segundos_parte_b__presente INTEGER NOT NULL,
    captura_segundos_parte_b REAL,
    captura_segundos_parte_b__cero_negativo INTEGER NOT NULL DEFAULT 0,
    fecha__presente INTEGER NOT NULL,
    fecha TEXT,
    fuentes_entradas__estado VARCHAR(8) NOT NULL CHECK(fuentes_entradas__estado IN ('AUSENTE','NULL','PRESENTE')),
    fuentes_entradas__cantidad BIGINT NOT NULL CHECK(fuentes_entradas__cantidad>=0),
    metodo__presente INTEGER NOT NULL,
    metodo TEXT,
    perfil__presente INTEGER NOT NULL,
    perfil TEXT,
    referencia__presente INTEGER NOT NULL,
    referencia TEXT,
    r__estado VARCHAR(8) NOT NULL CHECK(r__estado IN ('AUSENTE','NULL','PRESENTE')),
    r_clasificacion_clinica__presente INTEGER NOT NULL,
    r_clasificacion_clinica TEXT,
    r_de_residual__presente INTEGER NOT NULL,
    r_de_residual REAL,
    r_de_residual__cero_negativo INTEGER NOT NULL DEFAULT 0,
    r_direccion__presente INTEGER NOT NULL,
    r_direccion TEXT,
    r_esperado__presente INTEGER NOT NULL,
    r_esperado REAL,
    r_esperado__cero_negativo INTEGER NOT NULL DEFAULT 0,
    r_estado__presente INTEGER NOT NULL,
    r_estado TEXT,
    r_interpretacion_matematica__estado VARCHAR(8) NOT NULL CHECK(r_interpretacion_matematica__estado IN ('AUSENTE','NULL','PRESENTE')),
    r_interpretacion_matematica_clasificacion_clinica__presente INTEGER NOT NULL,
    r_interpretacion_matematica_clasificacion_clinica TEXT,
    r_interpretacion_matematica_contrato__presente INTEGER NOT NULL,
    r_interpretacion_matematica_contrato TEXT,
    r_interpretacion_matematica_estado__presente INTEGER NOT NULL,
    r_interpretacion_matematica_estado TEXT,
    r_interpretacion_matematica_limite__presente INTEGER NOT NULL,
    r_interpretacion_matematica_limite TEXT,
    r_interpretacion_matematica_posicion__presente INTEGER NOT NULL,
    r_interpretacion_matematica_posicion TEXT,
    r_interpretacion_matematica_texto__presente INTEGER NOT NULL,
    r_interpretacion_matematica_texto TEXT,
    r_medida__presente INTEGER NOT NULL,
    r_medida TEXT,
    r_motivos__estado VARCHAR(8) NOT NULL CHECK(r_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    r_puntuacion_bruta__presente INTEGER NOT NULL,
    r_puntuacion_bruta__tipo VARCHAR(6) NOT NULL CHECK(r_puntuacion_bruta__tipo IN ('NULL','FLOAT','INT')),
    r_puntuacion_bruta_real REAL,
    r_puntuacion_bruta_entero BIGINT,
    r_puntuacion_bruta__cero_negativo INTEGER NOT NULL DEFAULT 0,
    r_z__presente INTEGER NOT NULL,
    r_z REAL,
    r_z__cero_negativo INTEGER NOT NULL DEFAULT 0,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_dominios_component_c036(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(aplicacion__presente IN (0,1)),
    CHECK(aplicacion__presente=1 OR aplicacion IS NULL),
    CHECK(autor__presente IN (0,1)),
    CHECK(autor__presente=1 OR autor IS NULL),
    CHECK(captura_aciertos_clave_semantica__presente IN (0,1)),
    CHECK(captura_aciertos_clave_semantica__presente=1 OR captura_aciertos_clave_semantica IS NULL),
    CHECK(captura_aciertos_espontaneos__presente IN (0,1)),
    CHECK(captura_aciertos_espontaneos__presente=1 OR captura_aciertos_espontaneos IS NULL),
    CHECK(captura_administracion_estandarizada__presente IN (0,1)),
    CHECK(captura_administracion_estandarizada__presente=1 OR captura_administracion_estandarizada IS NULL),
    CHECK(captura_administracion_estandarizada IS NULL OR captura_administracion_estandarizada IN (0,1)),
    CHECK(captura_audicion_compatible__presente IN (0,1)),
    CHECK(captura_audicion_compatible__presente=1 OR captura_audicion_compatible IS NULL),
    CHECK(captura_audicion_compatible IS NULL OR captura_audicion_compatible IN (0,1)),
    CHECK(captura_condiciones_sensoriales__presente IN (0,1)),
    CHECK(captura_condiciones_sensoriales__presente=1 OR captura_condiciones_sensoriales IS NULL),
    CHECK(captura_condiciones_sensoriales IS NULL OR captura_condiciones_sensoriales IN (0,1)),
    CHECK(captura_demora_minutos__presente IN (0,1)),
    CHECK(captura_demora_minutos__presente=1 OR captura_demora_minutos IS NULL),
    CHECK(captura_demora_minutos__cero_negativo=0 OR captura_demora_minutos=0),
    CHECK(captura_edad_anios__presente IN (0,1)),
    CHECK(captura_edad_anios__presente=1 OR captura_edad_anios IS NULL),
    CHECK(captura_edicion_protocolo__presente IN (0,1)),
    CHECK(captura_edicion_protocolo__presente=1 OR captura_edicion_protocolo IS NULL),
    CHECK(captura_elementos_revisados__presente IN (0,1)),
    CHECK(captura_elementos_revisados__presente=1 OR captura_elementos_revisados IS NULL),
    CHECK(captura_ensayo_1__presente IN (0,1)),
    CHECK(captura_ensayo_1__presente=1 OR captura_ensayo_1 IS NULL),
    CHECK(captura_ensayo_2__presente IN (0,1)),
    CHECK(captura_ensayo_2__presente=1 OR captura_ensayo_2 IS NULL),
    CHECK(captura_ensayo_3__presente IN (0,1)),
    CHECK(captura_ensayo_3__presente=1 OR captura_ensayo_3 IS NULL),
    CHECK(captura_ensayos__estado='PRESENTE' OR captura_ensayos__cantidad=0),
    CHECK(captura_errores_observados__presente IN (0,1)),
    CHECK(captura_errores_observados__presente=1 OR captura_errores_observados IS NULL),
    CHECK(captura_escolaridad_anios__presente IN (0,1)),
    CHECK(captura_escolaridad_anios__presente=1 OR captura_escolaridad_anios IS NULL),
    CHECK(captura_espanol_nativo__presente IN (0,1)),
    CHECK(captura_espanol_nativo__presente=1 OR captura_espanol_nativo IS NULL),
    CHECK(captura_espanol_nativo IS NULL OR captura_espanol_nativo IN (0,1)),
    CHECK(captura_finalizada__presente IN (0,1)),
    CHECK(captura_finalizada__presente=1 OR captura_finalizada IS NULL),
    CHECK(captura_finalizada IS NULL OR captura_finalizada IN (0,1)),
    CHECK(captura_forma__presente IN (0,1)),
    CHECK(captura_forma__presente=1 OR captura_forma IS NULL),
    CHECK(captura_forma_l_aciertos__presente IN (0,1)),
    CHECK(captura_forma_l_aciertos__presente=1 OR captura_forma_l_aciertos IS NULL),
    CHECK(captura_forma_n_aciertos__presente IN (0,1)),
    CHECK(captura_forma_n_aciertos__presente=1 OR captura_forma_n_aciertos IS NULL),
    CHECK(captura_idioma__presente IN (0,1)),
    CHECK(captura_idioma__presente=1 OR captura_idioma IS NULL),
    CHECK(captura_instrumento__presente IN (0,1)),
    CHECK(captura_instrumento__presente=1 OR captura_instrumento IS NULL),
    CHECK(captura_lectoescritura__presente IN (0,1)),
    CHECK(captura_lectoescritura__presente=1 OR captura_lectoescritura IS NULL),
    CHECK(captura_lectoescritura IS NULL OR captura_lectoescritura IN (0,1)),
    CHECK(captura_nacimiento_bolivia__presente IN (0,1)),
    CHECK(captura_nacimiento_bolivia__presente=1 OR captura_nacimiento_bolivia IS NULL),
    CHECK(captura_nacimiento_bolivia IS NULL OR captura_nacimiento_bolivia IN (0,1)),
    CHECK(captura_protocolo__presente IN (0,1)),
    CHECK(captura_protocolo__presente=1 OR captura_protocolo IS NULL),
    CHECK(captura_puntaje_copia__presente IN (0,1)),
    CHECK(captura_puntaje_copia__presente=1 OR captura_puntaje_copia IS NULL),
    CHECK(captura_puntaje_copia__cero_negativo=0 OR captura_puntaje_copia=0),
    CHECK(captura_recuerdo_diferido__presente IN (0,1)),
    CHECK(captura_recuerdo_diferido__presente=1 OR captura_recuerdo_diferido IS NULL),
    CHECK(captura_residencia_bolivia__presente IN (0,1)),
    CHECK(captura_residencia_bolivia__presente=1 OR captura_residencia_bolivia IS NULL),
    CHECK(captura_residencia_bolivia IS NULL OR captura_residencia_bolivia IN (0,1)),
    CHECK(captura_segundos_parte_b__presente IN (0,1)),
    CHECK(captura_segundos_parte_b__presente=1 OR captura_segundos_parte_b IS NULL),
    CHECK(captura_segundos_parte_b__cero_negativo=0 OR captura_segundos_parte_b=0),
    CHECK(fecha__presente IN (0,1)),
    CHECK(fecha__presente=1 OR fecha IS NULL),
    CHECK(fuentes_entradas__estado='PRESENTE' OR fuentes_entradas__cantidad=0),
    CHECK(metodo__presente IN (0,1)),
    CHECK(metodo__presente=1 OR metodo IS NULL),
    CHECK(perfil__presente IN (0,1)),
    CHECK(perfil__presente=1 OR perfil IS NULL),
    CHECK(referencia__presente IN (0,1)),
    CHECK(referencia__presente=1 OR referencia IS NULL),
    CHECK(r_clasificacion_clinica__presente IN (0,1)),
    CHECK(r_clasificacion_clinica__presente=1 OR r_clasificacion_clinica IS NULL),
    CHECK(r_de_residual__presente IN (0,1)),
    CHECK(r_de_residual__presente=1 OR r_de_residual IS NULL),
    CHECK(r_de_residual__cero_negativo=0 OR r_de_residual=0),
    CHECK(r_direccion__presente IN (0,1)),
    CHECK(r_direccion__presente=1 OR r_direccion IS NULL),
    CHECK(r_esperado__presente IN (0,1)),
    CHECK(r_esperado__presente=1 OR r_esperado IS NULL),
    CHECK(r_esperado__cero_negativo=0 OR r_esperado=0),
    CHECK(r_estado__presente IN (0,1)),
    CHECK(r_estado__presente=1 OR r_estado IS NULL),
    CHECK(r_interpretacion_matematica_clasificacion_clinica__presente IN (0,1)),
    CHECK(r_interpretacion_matematica_clasificacion_clinica__presente=1 OR r_interpretacion_matematica_clasificacion_clinica IS NULL),
    CHECK(r_interpretacion_matematica_contrato__presente IN (0,1)),
    CHECK(r_interpretacion_matematica_contrato__presente=1 OR r_interpretacion_matematica_contrato IS NULL),
    CHECK(r_interpretacion_matematica_estado__presente IN (0,1)),
    CHECK(r_interpretacion_matematica_estado__presente=1 OR r_interpretacion_matematica_estado IS NULL),
    CHECK(r_interpretacion_matematica_limite__presente IN (0,1)),
    CHECK(r_interpretacion_matematica_limite__presente=1 OR r_interpretacion_matematica_limite IS NULL),
    CHECK(r_interpretacion_matematica_posicion__presente IN (0,1)),
    CHECK(r_interpretacion_matematica_posicion__presente=1 OR r_interpretacion_matematica_posicion IS NULL),
    CHECK(r_interpretacion_matematica_texto__presente IN (0,1)),
    CHECK(r_interpretacion_matematica_texto__presente=1 OR r_interpretacion_matematica_texto IS NULL),
    CHECK(r_medida__presente IN (0,1)),
    CHECK(r_medida__presente=1 OR r_medida IS NULL),
    CHECK(r_puntuacion_bruta__presente IN (0,1)),
    CHECK(r_puntuacion_bruta__presente=1 OR r_puntuacion_bruta__tipo='NULL'),
    CHECK((r_puntuacion_bruta__tipo='NULL' AND r_puntuacion_bruta_real IS NULL AND r_puntuacion_bruta_entero IS NULL) OR (r_puntuacion_bruta__tipo='FLOAT' AND r_puntuacion_bruta_real IS NOT NULL AND r_puntuacion_bruta_entero IS NULL) OR (r_puntuacion_bruta__tipo='INT' AND r_puntuacion_bruta_entero IS NOT NULL AND r_puntuacion_bruta_real IS NULL)),
    CHECK(r_puntuacion_bruta__cero_negativo=0 OR (r_puntuacion_bruta__tipo='FLOAT' AND r_puntuacion_bruta_real=0)),
    CHECK(r_z__presente IN (0,1)),
    CHECK(r_z__presente=1 OR r_z IS NULL),
    CHECK(r_z__cero_negativo=0 OR r_z=0),
    CHECK(dto__estado='PRESENTE' OR (aplicacion__presente=0 AND autor__presente=0 AND captura__estado='AUSENTE' AND fecha__presente=0 AND fuentes_entradas__estado='AUSENTE' AND metodo__presente=0 AND perfil__presente=0 AND referencia__presente=0 AND r__estado='AUSENTE')),
    CHECK(captura__estado='PRESENTE' OR (captura_aciertos_clave_semantica__presente=0 AND captura_aciertos_espontaneos__presente=0 AND captura_administracion_estandarizada__presente=0 AND captura_audicion_compatible__presente=0 AND captura_condiciones_sensoriales__presente=0 AND captura_demora_minutos__presente=0 AND captura_edad_anios__presente=0 AND captura_edicion_protocolo__presente=0 AND captura_elementos_revisados__presente=0 AND captura_ensayo_1__presente=0 AND captura_ensayo_2__presente=0 AND captura_ensayo_3__presente=0 AND captura_ensayos__estado='AUSENTE' AND captura_errores_observados__presente=0 AND captura_escolaridad_anios__presente=0 AND captura_espanol_nativo__presente=0 AND captura_finalizada__presente=0 AND captura_forma__presente=0 AND captura_forma_l_aciertos__presente=0 AND captura_forma_n_aciertos__presente=0 AND captura_idioma__presente=0 AND captura_instrumento__presente=0 AND captura_lectoescritura__presente=0 AND captura_nacimiento_bolivia__presente=0 AND captura_protocolo__presente=0 AND captura_puntaje_copia__presente=0 AND captura_recuerdo_diferido__presente=0 AND captura_residencia_bolivia__presente=0 AND captura_segundos_parte_b__presente=0)),
    CHECK(r__estado='PRESENTE' OR (r_clasificacion_clinica__presente=0 AND r_de_residual__presente=0 AND r_direccion__presente=0 AND r_esperado__presente=0 AND r_estado__presente=0 AND r_interpretacion_matematica__estado='AUSENTE' AND r_medida__presente=0 AND r_motivos__estado='AUSENTE' AND r_puntuacion_bruta__presente=0 AND r_z__presente=0)),
    CHECK(r_interpretacion_matematica__estado='PRESENTE' OR (r_interpretacion_matematica_clasificacion_clinica__presente=0 AND r_interpretacion_matematica_contrato__presente=0 AND r_interpretacion_matematica_estado__presente=0 AND r_interpretacion_matematica_limite__presente=0 AND r_interpretacion_matematica_posicion__presente=0 AND r_interpretacion_matematica_texto__presente=0))
);

CREATE TABLE ret_mediciones_captura_ensayos_c042 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_dominios_componentes_mediciones_c041(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_componentes_mediciones_fuentes_entrada_c043 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicacion__presente INTEGER NOT NULL,
    aplicacion TEXT,
    bruto__presente INTEGER NOT NULL,
    bruto__tipo VARCHAR(6) NOT NULL CHECK(bruto__tipo IN ('NULL','BOOL','STRING')),
    bruto_logico INTEGER,
    bruto_texto TEXT,
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    pregunta__presente INTEGER NOT NULL,
    pregunta TEXT,
    respuesta__presente INTEGER NOT NULL,
    respuesta TEXT,
    valor__presente INTEGER NOT NULL,
    valor__tipo VARCHAR(6) NOT NULL CHECK(valor__tipo IN ('NULL','BOOL','FLOAT','INT')),
    valor_logico INTEGER,
    valor_real REAL,
    valor_entero BIGINT,
    valor__cero_negativo INTEGER NOT NULL DEFAULT 0,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_dominios_componentes_mediciones_c041(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(aplicacion__presente IN (0,1)),
    CHECK(aplicacion__presente=1 OR aplicacion IS NULL),
    CHECK(bruto__presente IN (0,1)),
    CHECK(bruto__presente=1 OR bruto__tipo='NULL'),
    CHECK((bruto__tipo='NULL' AND bruto_logico IS NULL AND bruto_texto IS NULL) OR (bruto__tipo='BOOL' AND bruto_logico IS NOT NULL AND bruto_texto IS NULL) OR (bruto__tipo='STRING' AND bruto_texto IS NOT NULL AND bruto_logico IS NULL)),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(pregunta__presente IN (0,1)),
    CHECK(pregunta__presente=1 OR pregunta IS NULL),
    CHECK(respuesta__presente IN (0,1)),
    CHECK(respuesta__presente=1 OR respuesta IS NULL),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor__tipo='NULL'),
    CHECK((valor__tipo='NULL' AND valor_logico IS NULL AND valor_real IS NULL AND valor_entero IS NULL) OR (valor__tipo='BOOL' AND valor_logico IS NOT NULL AND valor_real IS NULL AND valor_entero IS NULL) OR (valor__tipo='FLOAT' AND valor_real IS NOT NULL AND valor_logico IS NULL AND valor_entero IS NULL) OR (valor__tipo='INT' AND valor_entero IS NOT NULL AND valor_logico IS NULL AND valor_real IS NULL)),
    CHECK(valor__cero_negativo=0 OR (valor__tipo='FLOAT' AND valor_real=0)),
    CHECK(dto__estado='PRESENTE' OR (aplicacion__presente=0 AND bruto__presente=0 AND campo__presente=0 AND pregunta__presente=0 AND respuesta__presente=0 AND valor__presente=0))
);

CREATE TABLE ret_dominios_componentes_motivos_c044 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_dominios_component_c036(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_perfil_instrumental_dominios_motivos_c045 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_salida_perfil_instrumental_dominios_c035(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_perfil_instrumental_red_contratos_c046 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    modalidades__estado VARCHAR(8) NOT NULL CHECK(modalidades__estado IN ('AUSENTE','NULL','PRESENTE')),
    modalidades__cantidad BIGINT NOT NULL CHECK(modalidades__cantidad>=0),
    tipo_destino__presente INTEGER NOT NULL,
    tipo_destino TEXT,
    tipo_origen__presente INTEGER NOT NULL,
    tipo_origen TEXT,
    tipo_relacion__presente INTEGER NOT NULL,
    tipo_relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(modalidades__estado='PRESENTE' OR modalidades__cantidad=0),
    CHECK(tipo_destino__presente IN (0,1)),
    CHECK(tipo_destino__presente=1 OR tipo_destino IS NULL),
    CHECK(tipo_origen__presente IN (0,1)),
    CHECK(tipo_origen__presente=1 OR tipo_origen IS NULL),
    CHECK(tipo_relacion__presente IN (0,1)),
    CHECK(tipo_relacion__presente=1 OR tipo_relacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (modalidades__estado='AUSENTE' AND tipo_destino__presente=0 AND tipo_origen__presente=0 AND tipo_relacion__presente=0))
);

CREATE TABLE ret_red_contratos_modalidades_c047 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_red_contratos_c046(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_perfil_instrumental_red_dominios_c048 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    componentes__estado VARCHAR(8) NOT NULL CHECK(componentes__estado IN ('AUSENTE','NULL','PRESENTE')),
    componentes__cantidad BIGINT NOT NULL CHECK(componentes__cantidad>=0),
    concepto__estado VARCHAR(8) NOT NULL CHECK(concepto__estado IN ('AUSENTE','NULL','PRESENTE')),
    concepto_codigo__presente INTEGER NOT NULL,
    concepto_codigo TEXT,
    concepto_definicion__presente INTEGER NOT NULL,
    concepto_definicion TEXT,
    concepto_estado_fuente__presente INTEGER NOT NULL,
    concepto_estado_fuente TEXT,
    concepto_limites__presente INTEGER NOT NULL,
    concepto_limites TEXT,
    concepto_nombre__presente INTEGER NOT NULL,
    concepto_nombre TEXT,
    concepto_procedencia__estado VARCHAR(8) NOT NULL CHECK(concepto_procedencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    concepto_procedencia__cantidad BIGINT NOT NULL CHECK(concepto_procedencia__cantidad>=0),
    concepto_tipo__presente INTEGER NOT NULL,
    concepto_tipo TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(componentes__estado='PRESENTE' OR componentes__cantidad=0),
    CHECK(concepto_codigo__presente IN (0,1)),
    CHECK(concepto_codigo__presente=1 OR concepto_codigo IS NULL),
    CHECK(concepto_definicion__presente IN (0,1)),
    CHECK(concepto_definicion__presente=1 OR concepto_definicion IS NULL),
    CHECK(concepto_estado_fuente__presente IN (0,1)),
    CHECK(concepto_estado_fuente__presente=1 OR concepto_estado_fuente IS NULL),
    CHECK(concepto_limites__presente IN (0,1)),
    CHECK(concepto_limites__presente=1 OR concepto_limites IS NULL),
    CHECK(concepto_nombre__presente IN (0,1)),
    CHECK(concepto_nombre__presente=1 OR concepto_nombre IS NULL),
    CHECK(concepto_procedencia__estado='PRESENTE' OR concepto_procedencia__cantidad=0),
    CHECK(concepto_tipo__presente IN (0,1)),
    CHECK(concepto_tipo__presente=1 OR concepto_tipo IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(dto__estado='PRESENTE' OR (componentes__estado='AUSENTE' AND concepto__estado='AUSENTE' AND nombre__presente=0)),
    CHECK(concepto__estado='PRESENTE' OR (concepto_codigo__presente=0 AND concepto_definicion__presente=0 AND concepto_estado_fuente__presente=0 AND concepto_limites__presente=0 AND concepto_nombre__presente=0 AND concepto_procedencia__estado='AUSENTE' AND concepto_tipo__presente=0))
);

CREATE TABLE ret_red_dominios_componentes_c049 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    concepto__estado VARCHAR(8) NOT NULL CHECK(concepto__estado IN ('AUSENTE','NULL','PRESENTE')),
    concepto_codigo__presente INTEGER NOT NULL,
    concepto_codigo TEXT,
    concepto_definicion__presente INTEGER NOT NULL,
    concepto_definicion TEXT,
    concepto_estado_fuente__presente INTEGER NOT NULL,
    concepto_estado_fuente TEXT,
    concepto_limites__presente INTEGER NOT NULL,
    concepto_limites TEXT,
    concepto_nombre__presente INTEGER NOT NULL,
    concepto_nombre TEXT,
    concepto_procedencia__estado VARCHAR(8) NOT NULL CHECK(concepto_procedencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    concepto_procedencia__cantidad BIGINT NOT NULL CHECK(concepto_procedencia__cantidad>=0),
    concepto_tipo__presente INTEGER NOT NULL,
    concepto_tipo TEXT,
    dominio__presente INTEGER NOT NULL,
    dominio TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    relacion_original__estado VARCHAR(8) NOT NULL CHECK(relacion_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    relacion_original_codigo__presente INTEGER NOT NULL,
    relacion_original_codigo TEXT,
    relacion_original_destino__presente INTEGER NOT NULL,
    relacion_original_destino TEXT,
    relacion_original_estado_fuente__presente INTEGER NOT NULL,
    relacion_original_estado_fuente TEXT,
    relacion_original_fila__presente INTEGER NOT NULL,
    relacion_original_fila BIGINT,
    relacion_original_limites__presente INTEGER NOT NULL,
    relacion_original_limites TEXT,
    relacion_original_origen__presente INTEGER NOT NULL,
    relacion_original_origen TEXT,
    relacion_original_relacion__presente INTEGER NOT NULL,
    relacion_original_relacion TEXT,
    relacion_original_significado__presente INTEGER NOT NULL,
    relacion_original_significado TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_red_dominios_c048(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(concepto_codigo__presente IN (0,1)),
    CHECK(concepto_codigo__presente=1 OR concepto_codigo IS NULL),
    CHECK(concepto_definicion__presente IN (0,1)),
    CHECK(concepto_definicion__presente=1 OR concepto_definicion IS NULL),
    CHECK(concepto_estado_fuente__presente IN (0,1)),
    CHECK(concepto_estado_fuente__presente=1 OR concepto_estado_fuente IS NULL),
    CHECK(concepto_limites__presente IN (0,1)),
    CHECK(concepto_limites__presente=1 OR concepto_limites IS NULL),
    CHECK(concepto_nombre__presente IN (0,1)),
    CHECK(concepto_nombre__presente=1 OR concepto_nombre IS NULL),
    CHECK(concepto_procedencia__estado='PRESENTE' OR concepto_procedencia__cantidad=0),
    CHECK(concepto_tipo__presente IN (0,1)),
    CHECK(concepto_tipo__presente=1 OR concepto_tipo IS NULL),
    CHECK(dominio__presente IN (0,1)),
    CHECK(dominio__presente=1 OR dominio IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(relacion_original_codigo__presente IN (0,1)),
    CHECK(relacion_original_codigo__presente=1 OR relacion_original_codigo IS NULL),
    CHECK(relacion_original_destino__presente IN (0,1)),
    CHECK(relacion_original_destino__presente=1 OR relacion_original_destino IS NULL),
    CHECK(relacion_original_estado_fuente__presente IN (0,1)),
    CHECK(relacion_original_estado_fuente__presente=1 OR relacion_original_estado_fuente IS NULL),
    CHECK(relacion_original_fila__presente IN (0,1)),
    CHECK(relacion_original_fila__presente=1 OR relacion_original_fila IS NULL),
    CHECK(relacion_original_limites__presente IN (0,1)),
    CHECK(relacion_original_limites__presente=1 OR relacion_original_limites IS NULL),
    CHECK(relacion_original_origen__presente IN (0,1)),
    CHECK(relacion_original_origen__presente=1 OR relacion_original_origen IS NULL),
    CHECK(relacion_original_relacion__presente IN (0,1)),
    CHECK(relacion_original_relacion__presente=1 OR relacion_original_relacion IS NULL),
    CHECK(relacion_original_significado__presente IN (0,1)),
    CHECK(relacion_original_significado__presente=1 OR relacion_original_significado IS NULL),
    CHECK(dto__estado='PRESENTE' OR (concepto__estado='AUSENTE' AND dominio__presente=0 AND nombre__presente=0 AND relacion_original__estado='AUSENTE')),
    CHECK(concepto__estado='PRESENTE' OR (concepto_codigo__presente=0 AND concepto_definicion__presente=0 AND concepto_estado_fuente__presente=0 AND concepto_limites__presente=0 AND concepto_nombre__presente=0 AND concepto_procedencia__estado='AUSENTE' AND concepto_tipo__presente=0)),
    CHECK(relacion_original__estado='PRESENTE' OR (relacion_original_codigo__presente=0 AND relacion_original_destino__presente=0 AND relacion_original_estado_fuente__presente=0 AND relacion_original_fila__presente=0 AND relacion_original_limites__presente=0 AND relacion_original_origen__presente=0 AND relacion_original_relacion__presente=0 AND relacion_original_significado__presente=0))
);

CREATE TABLE ret_componentes_concepto_procedencia_c050 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_red_dominios_componentes_c049(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_dominios_concepto_procedencia_c051 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_instrumental_red_dominios_c048(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_perfil_instrumental_red_nodos_c052 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_nodo_semantico__presente INTEGER NOT NULL,
    cod_nodo_semantico TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    clave_semantico__presente INTEGER NOT NULL,
    clave_semantico TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    tipo_nodo__presente INTEGER NOT NULL,
    tipo_nodo TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_nodo_semantico__presente IN (0,1)),
    CHECK(cod_nodo_semantico__presente=1 OR cod_nodo_semantico IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(clave_semantico__presente IN (0,1)),
    CHECK(clave_semantico__presente=1 OR clave_semantico IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(tipo_nodo__presente IN (0,1)),
    CHECK(tipo_nodo__presente=1 OR tipo_nodo IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_nodo_semantico__presente=0 AND cod_version_modelo__presente=0 AND clave_semantico__presente=0 AND estado__presente=0 AND nombre__presente=0 AND tipo_nodo__presente=0))
);

CREATE TABLE ret_perfil_instrumental_red_relaciones_c053 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_nodo_destino__presente INTEGER NOT NULL,
    cod_nodo_destino TEXT,
    cod_nodo_origen__presente INTEGER NOT NULL,
    cod_nodo_origen TEXT,
    cod_relacion_semantica__presente INTEGER NOT NULL,
    cod_relacion_semantica TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    modalidad_relacion__presente INTEGER NOT NULL,
    modalidad_relacion TEXT,
    tipo_relacion__presente INTEGER NOT NULL,
    tipo_relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_nodo_destino__presente IN (0,1)),
    CHECK(cod_nodo_destino__presente=1 OR cod_nodo_destino IS NULL),
    CHECK(cod_nodo_origen__presente IN (0,1)),
    CHECK(cod_nodo_origen__presente=1 OR cod_nodo_origen IS NULL),
    CHECK(cod_relacion_semantica__presente IN (0,1)),
    CHECK(cod_relacion_semantica__presente=1 OR cod_relacion_semantica IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(modalidad_relacion__presente IN (0,1)),
    CHECK(modalidad_relacion__presente=1 OR modalidad_relacion IS NULL),
    CHECK(tipo_relacion__presente IN (0,1)),
    CHECK(tipo_relacion__presente=1 OR tipo_relacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_nodo_destino__presente=0 AND cod_nodo_origen__presente=0 AND cod_relacion_semantica__presente=0 AND cod_version_modelo__presente=0 AND estado__presente=0 AND modalidad_relacion__presente=0 AND tipo_relacion__presente=0))
);

CREATE TABLE ret_salida_resultado_perfil_c054 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    antecedentes_descartados_por_contexto__estado VARCHAR(8) NOT NULL CHECK(antecedentes_descartados_por_contexto__estado IN ('AUSENTE','NULL','PRESENTE')),
    compuerta_institucional__estado VARCHAR(8) NOT NULL CHECK(compuerta_institucional__estado IN ('AUSENTE','NULL','PRESENTE')),
    compuerta_institucional_contexto__estado VARCHAR(8) NOT NULL CHECK(compuerta_institucional_contexto__estado IN ('AUSENTE','NULL','PRESENTE')),
    compuerta_institucional_contexto_basal_disponible__presente INTEGER NOT NULL,
    compuerta_institucional_contexto_basal_disponible INTEGER,
    compuerta_institucional_contexto_faltantes__estado VARCHAR(8) NOT NULL CHECK(compuerta_institucional_contexto_faltantes__estado IN ('AUSENTE','NULL','PRESENTE')),
    compuerta_institucional_contexto_faltantes__cantidad BIGINT NOT NULL CHECK(compuerta_institucional_contexto_faltantes__cantidad>=0),
    com_ins_con_inv_nec__presente INTEGER NOT NULL,
    com_ins_con_inv_nec INTEGER,
    compuerta_institucional_contexto_modificador__presente INTEGER NOT NULL,
    compuerta_institucional_contexto_modificador TEXT,
    compuerta_institucional_contexto_suficiente__presente INTEGER NOT NULL,
    compuerta_institucional_contexto_suficiente INTEGER,
    compuerta_institucional_estado__presente INTEGER NOT NULL,
    compuerta_institucional_estado TEXT,
    compuerta_institucional_habilita_r__presente INTEGER NOT NULL,
    compuerta_institucional_habilita_r INTEGER,
    compuerta_institucional_modificador__presente INTEGER NOT NULL,
    compuerta_institucional_modificador TEXT,
    conclusiones_candidatas__estado VARCHAR(8) NOT NULL CHECK(conclusiones_candidatas__estado IN ('AUSENTE','NULL','PRESENTE')),
    condiciones_pendientes__estado VARCHAR(8) NOT NULL CHECK(condiciones_pendientes__estado IN ('AUSENTE','NULL','PRESENTE')),
    condiciones_pendientes__cantidad BIGINT NOT NULL CHECK(condiciones_pendientes__cantidad>=0),
    contradicciones__estado VARCHAR(8) NOT NULL CHECK(contradicciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    criterio__presente INTEGER NOT NULL,
    criterio TEXT,
    estado_institucional__presente INTEGER NOT NULL,
    estado_institucional TEXT,
    motivos_no_emision__estado VARCHAR(8) NOT NULL CHECK(motivos_no_emision__estado IN ('AUSENTE','NULL','PRESENTE')),
    r_institucional__presente INTEGER NOT NULL,
    r_institucional TEXT,
    traza__estado VARCHAR(8) NOT NULL CHECK(traza__estado IN ('AUSENTE','NULL','PRESENTE')),
    traza_compuerta__estado VARCHAR(8) NOT NULL CHECK(traza_compuerta__estado IN ('AUSENTE','NULL','PRESENTE')),
    traza_compuerta_alcance__presente INTEGER NOT NULL,
    traza_compuerta_alcance TEXT,
    traza_compuerta_sin_interpretacion_a_p__presente INTEGER NOT NULL,
    traza_compuerta_sin_interpretacion_a_p INTEGER,
    traza_criterio__presente INTEGER NOT NULL,
    traza_criterio TEXT,
    traza_evaluacion__presente INTEGER NOT NULL,
    traza_evaluacion TEXT,
    traza_evidencias__estado VARCHAR(8) NOT NULL CHECK(traza_evidencias__estado IN ('AUSENTE','NULL','PRESENTE')),
    traza_evidencias__cantidad BIGINT NOT NULL CHECK(traza_evidencias__cantidad>=0),
    traza_fecha_corte__presente INTEGER NOT NULL,
    traza_fecha_corte TEXT,
    traza_reglas__estado VARCHAR(8) NOT NULL CHECK(traza_reglas__estado IN ('AUSENTE','NULL','PRESENTE')),
    traza_reglas__cantidad BIGINT NOT NULL CHECK(traza_reglas__cantidad>=0),
    traza_relaciones_evidencias__estado VARCHAR(8) NOT NULL CHECK(traza_relaciones_evidencias__estado IN ('AUSENTE','NULL','PRESENTE')),
    traza_residente__presente INTEGER NOT NULL,
    traza_residente TEXT,
    traza_r__presente INTEGER NOT NULL,
    traza_r TEXT,
    traza_rutas__estado VARCHAR(8) NOT NULL CHECK(traza_rutas__estado IN ('AUSENTE','NULL','PRESENTE')),
    traza_rutas__cantidad BIGINT NOT NULL CHECK(traza_rutas__cantidad>=0),
    traza_rutas_hallazgos__estado VARCHAR(8) NOT NULL CHECK(traza_rutas_hallazgos__estado IN ('AUSENTE','NULL','PRESENTE')),
    traza_sin_emision__estado VARCHAR(8) NOT NULL CHECK(traza_sin_emision__estado IN ('AUSENTE','NULL','PRESENTE')),
    traza_version__presente INTEGER NOT NULL,
    traza_version TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(compuerta_institucional_contexto_basal_disponible__presente IN (0,1)),
    CHECK(compuerta_institucional_contexto_basal_disponible__presente=1 OR compuerta_institucional_contexto_basal_disponible IS NULL),
    CHECK(compuerta_institucional_contexto_basal_disponible IS NULL OR compuerta_institucional_contexto_basal_disponible IN (0,1)),
    CHECK(compuerta_institucional_contexto_faltantes__estado='PRESENTE' OR compuerta_institucional_contexto_faltantes__cantidad=0),
    CHECK(com_ins_con_inv_nec__presente IN (0,1)),
    CHECK(com_ins_con_inv_nec__presente=1 OR com_ins_con_inv_nec IS NULL),
    CHECK(com_ins_con_inv_nec IS NULL OR com_ins_con_inv_nec IN (0,1)),
    CHECK(compuerta_institucional_contexto_modificador__presente IN (0,1)),
    CHECK(compuerta_institucional_contexto_modificador__presente=1 OR compuerta_institucional_contexto_modificador IS NULL),
    CHECK(compuerta_institucional_contexto_suficiente__presente IN (0,1)),
    CHECK(compuerta_institucional_contexto_suficiente__presente=1 OR compuerta_institucional_contexto_suficiente IS NULL),
    CHECK(compuerta_institucional_contexto_suficiente IS NULL OR compuerta_institucional_contexto_suficiente IN (0,1)),
    CHECK(compuerta_institucional_estado__presente IN (0,1)),
    CHECK(compuerta_institucional_estado__presente=1 OR compuerta_institucional_estado IS NULL),
    CHECK(compuerta_institucional_habilita_r__presente IN (0,1)),
    CHECK(compuerta_institucional_habilita_r__presente=1 OR compuerta_institucional_habilita_r IS NULL),
    CHECK(compuerta_institucional_habilita_r IS NULL OR compuerta_institucional_habilita_r IN (0,1)),
    CHECK(compuerta_institucional_modificador__presente IN (0,1)),
    CHECK(compuerta_institucional_modificador__presente=1 OR compuerta_institucional_modificador IS NULL),
    CHECK(condiciones_pendientes__estado='PRESENTE' OR condiciones_pendientes__cantidad=0),
    CHECK(criterio__presente IN (0,1)),
    CHECK(criterio__presente=1 OR criterio IS NULL),
    CHECK(estado_institucional__presente IN (0,1)),
    CHECK(estado_institucional__presente=1 OR estado_institucional IS NULL),
    CHECK(r_institucional__presente IN (0,1)),
    CHECK(r_institucional__presente=1 OR r_institucional IS NULL),
    CHECK(traza_compuerta_alcance__presente IN (0,1)),
    CHECK(traza_compuerta_alcance__presente=1 OR traza_compuerta_alcance IS NULL),
    CHECK(traza_compuerta_sin_interpretacion_a_p__presente IN (0,1)),
    CHECK(traza_compuerta_sin_interpretacion_a_p__presente=1 OR traza_compuerta_sin_interpretacion_a_p IS NULL),
    CHECK(traza_compuerta_sin_interpretacion_a_p IS NULL OR traza_compuerta_sin_interpretacion_a_p IN (0,1)),
    CHECK(traza_criterio__presente IN (0,1)),
    CHECK(traza_criterio__presente=1 OR traza_criterio IS NULL),
    CHECK(traza_evaluacion__presente IN (0,1)),
    CHECK(traza_evaluacion__presente=1 OR traza_evaluacion IS NULL),
    CHECK(traza_evidencias__estado='PRESENTE' OR traza_evidencias__cantidad=0),
    CHECK(traza_fecha_corte__presente IN (0,1)),
    CHECK(traza_fecha_corte__presente=1 OR traza_fecha_corte IS NULL),
    CHECK(traza_reglas__estado='PRESENTE' OR traza_reglas__cantidad=0),
    CHECK(traza_residente__presente IN (0,1)),
    CHECK(traza_residente__presente=1 OR traza_residente IS NULL),
    CHECK(traza_r__presente IN (0,1)),
    CHECK(traza_r__presente=1 OR traza_r IS NULL),
    CHECK(traza_rutas__estado='PRESENTE' OR traza_rutas__cantidad=0),
    CHECK(traza_version__presente IN (0,1)),
    CHECK(traza_version__presente=1 OR traza_version IS NULL),
    CHECK(dto__estado='PRESENTE' OR (antecedentes_descartados_por_contexto__estado='AUSENTE' AND compuerta_institucional__estado='AUSENTE' AND conclusiones_candidatas__estado='AUSENTE' AND condiciones_pendientes__estado='AUSENTE' AND contradicciones__estado='AUSENTE' AND criterio__presente=0 AND estado_institucional__presente=0 AND motivos_no_emision__estado='AUSENTE' AND r_institucional__presente=0 AND traza__estado='AUSENTE')),
    CHECK(compuerta_institucional__estado='PRESENTE' OR (compuerta_institucional_contexto__estado='AUSENTE' AND compuerta_institucional_estado__presente=0 AND compuerta_institucional_habilita_r__presente=0 AND compuerta_institucional_modificador__presente=0)),
    CHECK(compuerta_institucional_contexto__estado='PRESENTE' OR (compuerta_institucional_contexto_basal_disponible__presente=0 AND compuerta_institucional_contexto_faltantes__estado='AUSENTE' AND com_ins_con_inv_nec__presente=0 AND compuerta_institucional_contexto_modificador__presente=0 AND compuerta_institucional_contexto_suficiente__presente=0)),
    CHECK(traza__estado='PRESENTE' OR (traza_compuerta__estado='AUSENTE' AND traza_criterio__presente=0 AND traza_evaluacion__presente=0 AND traza_evidencias__estado='AUSENTE' AND traza_fecha_corte__presente=0 AND traza_reglas__estado='AUSENTE' AND traza_relaciones_evidencias__estado='AUSENTE' AND traza_residente__presente=0 AND traza_r__presente=0 AND traza_rutas__estado='AUSENTE' AND traza_rutas_hallazgos__estado='AUSENTE' AND traza_sin_emision__estado='AUSENTE' AND traza_version__presente=0)),
    CHECK(traza_compuerta__estado='PRESENTE' OR (traza_compuerta_alcance__presente=0 AND traza_compuerta_sin_interpretacion_a_p__presente=0))
);

CREATE TABLE ret_compuerta_institucional_contexto_falta_c055 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_salida_resultado_perfil_c054(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_resultado_perfil_condiciones_pendiente_c056 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    clave_regla__presente INTEGER NOT NULL,
    clave_regla TEXT,
    condicion__presente INTEGER NOT NULL,
    condicion TEXT,
    regla__presente INTEGER NOT NULL,
    regla TEXT,
    valor__presente INTEGER NOT NULL,
    valor TEXT,
    valor_codigo__presente INTEGER NOT NULL,
    valor_codigo TEXT,
    variable__presente INTEGER NOT NULL,
    variable TEXT,
    variable_codigo__presente INTEGER NOT NULL,
    variable_codigo TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_salida_resultado_perfil_c054(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(clave_regla__presente IN (0,1)),
    CHECK(clave_regla__presente=1 OR clave_regla IS NULL),
    CHECK(condicion__presente IN (0,1)),
    CHECK(condicion__presente=1 OR condicion IS NULL),
    CHECK(regla__presente IN (0,1)),
    CHECK(regla__presente=1 OR regla IS NULL),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor IS NULL),
    CHECK(valor_codigo__presente IN (0,1)),
    CHECK(valor_codigo__presente=1 OR valor_codigo IS NULL),
    CHECK(variable__presente IN (0,1)),
    CHECK(variable__presente=1 OR variable IS NULL),
    CHECK(variable_codigo__presente IN (0,1)),
    CHECK(variable_codigo__presente=1 OR variable_codigo IS NULL),
    CHECK(dto__estado='PRESENTE' OR (clave_regla__presente=0 AND condicion__presente=0 AND regla__presente=0 AND valor__presente=0 AND valor_codigo__presente=0 AND variable__presente=0 AND variable_codigo__presente=0))
);

CREATE TABLE ret_perfil_traza_evidencias_c057 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    admisibilidad__presente INTEGER NOT NULL,
    admisibilidad TEXT,
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    fecha_fuente__presente INTEGER NOT NULL,
    fecha_fuente TEXT,
    fuente__presente INTEGER NOT NULL,
    fuente TEXT,
    id__presente INTEGER NOT NULL,
    id TEXT,
    mapeo_valor__presente INTEGER NOT NULL,
    mapeo_valor TEXT,
    mapeo_variable__presente INTEGER NOT NULL,
    mapeo_variable TEXT,
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia_instrumental__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia_instrumental_campo_original__presente INTEGER NOT NULL,
    procedencia_instrumental_campo_original TEXT,
    procedencia_instrumental_cod_aplicacion__presente INTEGER NOT NULL,
    procedencia_instrumental_cod_aplicacion TEXT,
    procedencia_instrumental_cod_personal_original__presente INTEGER NOT NULL,
    procedencia_instrumental_cod_personal_original TEXT,
    procedencia_instrumental_cod_pregunta__presente INTEGER NOT NULL,
    procedencia_instrumental_cod_pregunta TEXT,
    procedencia_instrumental_cod_respuesta__presente INTEGER NOT NULL,
    procedencia_instrumental_cod_respuesta TEXT,
    procedencia_instrumental_episodio_original__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_episodio_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_epi_ori_cod_ate__presente INTEGER NOT NULL,
    pro_ins_epi_ori_cod_ate TEXT,
    pro_ins_epi_ori_gru_sop__presente INTEGER NOT NULL,
    pro_ins_epi_ori_gru_sop TEXT,
    pro_ins_epi_ori_ind_cli__presente INTEGER NOT NULL,
    pro_ins_epi_ori_ind_cli TEXT,
    pro_ins_epi_ori_uni_con__presente INTEGER NOT NULL,
    pro_ins_epi_ori_uni_con TEXT,
    procedencia_instrumental_fuente_edad__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_fuente_edad__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia_instrumental_fuente_edad_campo__presente INTEGER NOT NULL,
    procedencia_instrumental_fuente_edad_campo TEXT,
    pro_ins_fue_eda_fec_apl__presente INTEGER NOT NULL,
    pro_ins_fue_eda_fec_apl TEXT,
    pro_ins_fue_eda_fec_nac__presente INTEGER NOT NULL,
    pro_ins_fue_eda_fec_nac TEXT,
    procedencia_instrumental_fuente_edad_registro__presente INTEGER NOT NULL,
    procedencia_instrumental_fuente_edad_registro TEXT,
    procedencia_instrumental_fuente_edad_tabla__presente INTEGER NOT NULL,
    procedencia_instrumental_fuente_edad_tabla TEXT,
    procedencia_instrumental_instrumento__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_instrumento__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_ins_cod_ins__presente INTEGER NOT NULL,
    pro_ins_ins_cod_ins TEXT,
    procedencia_instrumental_instrumento_codigo__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_codigo TEXT,
    procedencia_instrumental_instrumento_estado__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_estado TEXT,
    procedencia_instrumental_instrumento_nombre__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_nombre TEXT,
    procedencia_instrumental_instrumento_tipo__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_tipo TEXT,
    procedencia_instrumental_instrumento_version__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_version TEXT,
    pro_ins_med_int__estado VARCHAR(8) NOT NULL CHECK(pro_ins_med_int__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_med_int_aci__estado VARCHAR(8) NOT NULL CHECK(pro_ins_med_int_aci__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_med_int_aci_cam__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_cam TEXT,
    pro_ins_med_int_aci_cod_apl__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_cod_apl TEXT,
    pro_ins_med_int_aci_cod_pre__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_cod_pre TEXT,
    pro_ins_med_int_aci_cod_res__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_cod_res TEXT,
    pro_ins_med_int_aci_val_ori__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_val_ori TEXT,
    procedencia_instrumental_perfil_interpretativo__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_perfil_interpretativo__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_per_int_cod__presente INTEGER NOT NULL,
    pro_ins_per_int_cod TEXT,
    pro_ins_per_int_com__presente INTEGER NOT NULL,
    pro_ins_per_int_com TEXT,
    pro_ins_per_int_fue__presente INTEGER NOT NULL,
    pro_ins_per_int_fue TEXT,
    pro_ins_per_int_ins__estado VARCHAR(8) NOT NULL CHECK(pro_ins_per_int_ins__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_per_int_ins_cod__presente INTEGER NOT NULL,
    pro_ins_per_int_ins_cod TEXT,
    pro_ins_per_int_ins_ver__presente INTEGER NOT NULL,
    pro_ins_per_int_ins_ver TEXT,
    pro_ins_per_int_med_req__estado VARCHAR(8) NOT NULL CHECK(pro_ins_per_int_med_req__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_per_int_med_req__cantidad BIGINT NOT NULL CHECK(pro_ins_per_int_med_req__cantidad>=0),
    pro_ins_per_int_met__presente INTEGER NOT NULL,
    pro_ins_per_int_met TEXT,
    pro_ins_per_int_pre__estado VARCHAR(8) NOT NULL CHECK(pro_ins_per_int_pre__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_per_int_pre__cantidad BIGINT NOT NULL CHECK(pro_ins_per_int_pre__cantidad>=0),
    pro_ins_per_int_ver__presente INTEGER NOT NULL,
    pro_ins_per_int_ver TEXT,
    procedencia_instrumental_pregunta_original__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_pregunta_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_pre_ori_cod_ins__presente INTEGER NOT NULL,
    pro_ins_pre_ori_cod_ins TEXT,
    pro_ins_pre_ori_cod_pre__presente INTEGER NOT NULL,
    pro_ins_pre_ori_cod_pre TEXT,
    pro_ins_pre_ori_tip_res__presente INTEGER NOT NULL,
    pro_ins_pre_ori_tip_res TEXT,
    procedencia_instrumental_registro_original__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_registro_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_reg_ori_cod_apl_ant__presente INTEGER NOT NULL,
    pro_ins_reg_ori_cod_apl_ant TEXT,
    pro_ins_reg_ori_cod_doc_cli__presente INTEGER NOT NULL,
    pro_ins_reg_ori_cod_doc_cli TEXT,
    pro_ins_reg_ori_cod_usu_reg__presente INTEGER NOT NULL,
    pro_ins_reg_ori_cod_usu_reg TEXT,
    pro_ins_reg_ori_loc_fue__presente INTEGER NOT NULL,
    pro_ins_reg_ori_loc_fue TEXT,
    pro_ins_reg_ori_reg_en__presente INTEGER NOT NULL,
    pro_ins_reg_ori_reg_en TEXT,
    registro__presente INTEGER NOT NULL,
    registro TEXT,
    representacion__presente INTEGER NOT NULL,
    representacion TEXT,
    tabla__presente INTEGER NOT NULL,
    tabla TEXT,
    valor__presente INTEGER NOT NULL,
    valor TEXT,
    valor_original__presente INTEGER NOT NULL,
    valor_original INTEGER,
    variable__presente INTEGER NOT NULL,
    variable TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_salida_resultado_perfil_c054(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(admisibilidad__presente IN (0,1)),
    CHECK(admisibilidad__presente=1 OR admisibilidad IS NULL),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(fecha_fuente__presente IN (0,1)),
    CHECK(fecha_fuente__presente=1 OR fecha_fuente IS NULL),
    CHECK(fuente__presente IN (0,1)),
    CHECK(fuente__presente=1 OR fuente IS NULL),
    CHECK(id__presente IN (0,1)),
    CHECK(id__presente=1 OR id IS NULL),
    CHECK(mapeo_valor__presente IN (0,1)),
    CHECK(mapeo_valor__presente=1 OR mapeo_valor IS NULL),
    CHECK(mapeo_variable__presente IN (0,1)),
    CHECK(mapeo_variable__presente=1 OR mapeo_variable IS NULL),
    CHECK(procedencia_instrumental_campo_original__presente IN (0,1)),
    CHECK(procedencia_instrumental_campo_original__presente=1 OR procedencia_instrumental_campo_original IS NULL),
    CHECK(procedencia_instrumental_cod_aplicacion__presente IN (0,1)),
    CHECK(procedencia_instrumental_cod_aplicacion__presente=1 OR procedencia_instrumental_cod_aplicacion IS NULL),
    CHECK(procedencia_instrumental_cod_personal_original__presente IN (0,1)),
    CHECK(procedencia_instrumental_cod_personal_original__presente=1 OR procedencia_instrumental_cod_personal_original IS NULL),
    CHECK(procedencia_instrumental_cod_pregunta__presente IN (0,1)),
    CHECK(procedencia_instrumental_cod_pregunta__presente=1 OR procedencia_instrumental_cod_pregunta IS NULL),
    CHECK(procedencia_instrumental_cod_respuesta__presente IN (0,1)),
    CHECK(procedencia_instrumental_cod_respuesta__presente=1 OR procedencia_instrumental_cod_respuesta IS NULL),
    CHECK(pro_ins_epi_ori_cod_ate__presente IN (0,1)),
    CHECK(pro_ins_epi_ori_cod_ate__presente=1 OR pro_ins_epi_ori_cod_ate IS NULL),
    CHECK(pro_ins_epi_ori_gru_sop__presente IN (0,1)),
    CHECK(pro_ins_epi_ori_gru_sop__presente=1 OR pro_ins_epi_ori_gru_sop IS NULL),
    CHECK(pro_ins_epi_ori_ind_cli__presente IN (0,1)),
    CHECK(pro_ins_epi_ori_ind_cli__presente=1 OR pro_ins_epi_ori_ind_cli IS NULL),
    CHECK(pro_ins_epi_ori_uni_con__presente IN (0,1)),
    CHECK(pro_ins_epi_ori_uni_con__presente=1 OR pro_ins_epi_ori_uni_con IS NULL),
    CHECK(procedencia_instrumental_fuente_edad_campo__presente IN (0,1)),
    CHECK(procedencia_instrumental_fuente_edad_campo__presente=1 OR procedencia_instrumental_fuente_edad_campo IS NULL),
    CHECK(pro_ins_fue_eda_fec_apl__presente IN (0,1)),
    CHECK(pro_ins_fue_eda_fec_apl__presente=1 OR pro_ins_fue_eda_fec_apl IS NULL),
    CHECK(pro_ins_fue_eda_fec_nac__presente IN (0,1)),
    CHECK(pro_ins_fue_eda_fec_nac__presente=1 OR pro_ins_fue_eda_fec_nac IS NULL),
    CHECK(procedencia_instrumental_fuente_edad_registro__presente IN (0,1)),
    CHECK(procedencia_instrumental_fuente_edad_registro__presente=1 OR procedencia_instrumental_fuente_edad_registro IS NULL),
    CHECK(procedencia_instrumental_fuente_edad_tabla__presente IN (0,1)),
    CHECK(procedencia_instrumental_fuente_edad_tabla__presente=1 OR procedencia_instrumental_fuente_edad_tabla IS NULL),
    CHECK(pro_ins_ins_cod_ins__presente IN (0,1)),
    CHECK(pro_ins_ins_cod_ins__presente=1 OR pro_ins_ins_cod_ins IS NULL),
    CHECK(procedencia_instrumental_instrumento_codigo__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_codigo__presente=1 OR procedencia_instrumental_instrumento_codigo IS NULL),
    CHECK(procedencia_instrumental_instrumento_estado__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_estado__presente=1 OR procedencia_instrumental_instrumento_estado IS NULL),
    CHECK(procedencia_instrumental_instrumento_nombre__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_nombre__presente=1 OR procedencia_instrumental_instrumento_nombre IS NULL),
    CHECK(procedencia_instrumental_instrumento_tipo__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_tipo__presente=1 OR procedencia_instrumental_instrumento_tipo IS NULL),
    CHECK(procedencia_instrumental_instrumento_version__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_version__presente=1 OR procedencia_instrumental_instrumento_version IS NULL),
    CHECK(pro_ins_med_int_aci_cam__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_cam__presente=1 OR pro_ins_med_int_aci_cam IS NULL),
    CHECK(pro_ins_med_int_aci_cod_apl__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_cod_apl__presente=1 OR pro_ins_med_int_aci_cod_apl IS NULL),
    CHECK(pro_ins_med_int_aci_cod_pre__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_cod_pre__presente=1 OR pro_ins_med_int_aci_cod_pre IS NULL),
    CHECK(pro_ins_med_int_aci_cod_res__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_cod_res__presente=1 OR pro_ins_med_int_aci_cod_res IS NULL),
    CHECK(pro_ins_med_int_aci_val_ori__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_val_ori__presente=1 OR pro_ins_med_int_aci_val_ori IS NULL),
    CHECK(pro_ins_per_int_cod__presente IN (0,1)),
    CHECK(pro_ins_per_int_cod__presente=1 OR pro_ins_per_int_cod IS NULL),
    CHECK(pro_ins_per_int_com__presente IN (0,1)),
    CHECK(pro_ins_per_int_com__presente=1 OR pro_ins_per_int_com IS NULL),
    CHECK(pro_ins_per_int_fue__presente IN (0,1)),
    CHECK(pro_ins_per_int_fue__presente=1 OR pro_ins_per_int_fue IS NULL),
    CHECK(pro_ins_per_int_ins_cod__presente IN (0,1)),
    CHECK(pro_ins_per_int_ins_cod__presente=1 OR pro_ins_per_int_ins_cod IS NULL),
    CHECK(pro_ins_per_int_ins_ver__presente IN (0,1)),
    CHECK(pro_ins_per_int_ins_ver__presente=1 OR pro_ins_per_int_ins_ver IS NULL),
    CHECK(pro_ins_per_int_med_req__estado='PRESENTE' OR pro_ins_per_int_med_req__cantidad=0),
    CHECK(pro_ins_per_int_met__presente IN (0,1)),
    CHECK(pro_ins_per_int_met__presente=1 OR pro_ins_per_int_met IS NULL),
    CHECK(pro_ins_per_int_pre__estado='PRESENTE' OR pro_ins_per_int_pre__cantidad=0),
    CHECK(pro_ins_per_int_ver__presente IN (0,1)),
    CHECK(pro_ins_per_int_ver__presente=1 OR pro_ins_per_int_ver IS NULL),
    CHECK(pro_ins_pre_ori_cod_ins__presente IN (0,1)),
    CHECK(pro_ins_pre_ori_cod_ins__presente=1 OR pro_ins_pre_ori_cod_ins IS NULL),
    CHECK(pro_ins_pre_ori_cod_pre__presente IN (0,1)),
    CHECK(pro_ins_pre_ori_cod_pre__presente=1 OR pro_ins_pre_ori_cod_pre IS NULL),
    CHECK(pro_ins_pre_ori_tip_res__presente IN (0,1)),
    CHECK(pro_ins_pre_ori_tip_res__presente=1 OR pro_ins_pre_ori_tip_res IS NULL),
    CHECK(pro_ins_reg_ori_cod_apl_ant__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_cod_apl_ant__presente=1 OR pro_ins_reg_ori_cod_apl_ant IS NULL),
    CHECK(pro_ins_reg_ori_cod_doc_cli__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_cod_doc_cli__presente=1 OR pro_ins_reg_ori_cod_doc_cli IS NULL),
    CHECK(pro_ins_reg_ori_cod_usu_reg__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_cod_usu_reg__presente=1 OR pro_ins_reg_ori_cod_usu_reg IS NULL),
    CHECK(pro_ins_reg_ori_loc_fue__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_loc_fue__presente=1 OR pro_ins_reg_ori_loc_fue IS NULL),
    CHECK(pro_ins_reg_ori_reg_en__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_reg_en__presente=1 OR pro_ins_reg_ori_reg_en IS NULL),
    CHECK(registro__presente IN (0,1)),
    CHECK(registro__presente=1 OR registro IS NULL),
    CHECK(representacion__presente IN (0,1)),
    CHECK(representacion__presente=1 OR representacion IS NULL),
    CHECK(tabla__presente IN (0,1)),
    CHECK(tabla__presente=1 OR tabla IS NULL),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor IS NULL),
    CHECK(valor_original__presente IN (0,1)),
    CHECK(valor_original__presente=1 OR valor_original IS NULL),
    CHECK(valor_original IS NULL OR valor_original IN (0,1)),
    CHECK(variable__presente IN (0,1)),
    CHECK(variable__presente=1 OR variable IS NULL),
    CHECK(dto__estado='PRESENTE' OR (admisibilidad__presente=0 AND campo__presente=0 AND fecha_fuente__presente=0 AND fuente__presente=0 AND id__presente=0 AND mapeo_valor__presente=0 AND mapeo_variable__presente=0 AND motivos__estado='AUSENTE' AND procedencia_instrumental__estado='AUSENTE' AND registro__presente=0 AND representacion__presente=0 AND tabla__presente=0 AND valor__presente=0 AND valor_original__presente=0 AND variable__presente=0)),
    CHECK(procedencia_instrumental__estado='PRESENTE' OR (procedencia_instrumental_campo_original__presente=0 AND procedencia_instrumental_cod_aplicacion__presente=0 AND procedencia_instrumental_cod_personal_original__presente=0 AND procedencia_instrumental_cod_pregunta__presente=0 AND procedencia_instrumental_cod_respuesta__presente=0 AND procedencia_instrumental_episodio_original__estado='AUSENTE' AND procedencia_instrumental_fuente_edad__estado='AUSENTE' AND procedencia_instrumental_instrumento__estado='AUSENTE' AND pro_ins_med_int__estado='AUSENTE' AND procedencia_instrumental_perfil_interpretativo__estado='AUSENTE' AND procedencia_instrumental_pregunta_original__estado='AUSENTE' AND procedencia_instrumental_registro_original__estado='AUSENTE')),
    CHECK(procedencia_instrumental_episodio_original__estado='PRESENTE' OR (pro_ins_epi_ori_cod_ate__presente=0 AND pro_ins_epi_ori_gru_sop__presente=0 AND pro_ins_epi_ori_ind_cli__presente=0 AND pro_ins_epi_ori_uni_con__presente=0)),
    CHECK(procedencia_instrumental_fuente_edad__estado='PRESENTE' OR (procedencia_instrumental_fuente_edad_campo__presente=0 AND pro_ins_fue_eda_fec_apl__presente=0 AND pro_ins_fue_eda_fec_nac__presente=0 AND procedencia_instrumental_fuente_edad_registro__presente=0 AND procedencia_instrumental_fuente_edad_tabla__presente=0)),
    CHECK(procedencia_instrumental_instrumento__estado='PRESENTE' OR (pro_ins_ins_cod_ins__presente=0 AND procedencia_instrumental_instrumento_codigo__presente=0 AND procedencia_instrumental_instrumento_estado__presente=0 AND procedencia_instrumental_instrumento_nombre__presente=0 AND procedencia_instrumental_instrumento_tipo__presente=0 AND procedencia_instrumental_instrumento_version__presente=0)),
    CHECK(pro_ins_med_int__estado='PRESENTE' OR (pro_ins_med_int_aci__estado='AUSENTE')),
    CHECK(pro_ins_med_int_aci__estado='PRESENTE' OR (pro_ins_med_int_aci_cam__presente=0 AND pro_ins_med_int_aci_cod_apl__presente=0 AND pro_ins_med_int_aci_cod_pre__presente=0 AND pro_ins_med_int_aci_cod_res__presente=0 AND pro_ins_med_int_aci_val_ori__presente=0)),
    CHECK(procedencia_instrumental_perfil_interpretativo__estado='PRESENTE' OR (pro_ins_per_int_cod__presente=0 AND pro_ins_per_int_com__presente=0 AND pro_ins_per_int_fue__presente=0 AND pro_ins_per_int_ins__estado='AUSENTE' AND pro_ins_per_int_med_req__estado='AUSENTE' AND pro_ins_per_int_met__presente=0 AND pro_ins_per_int_pre__estado='AUSENTE' AND pro_ins_per_int_ver__presente=0)),
    CHECK(pro_ins_per_int_ins__estado='PRESENTE' OR (pro_ins_per_int_ins_cod__presente=0 AND pro_ins_per_int_ins_ver__presente=0)),
    CHECK(procedencia_instrumental_pregunta_original__estado='PRESENTE' OR (pro_ins_pre_ori_cod_ins__presente=0 AND pro_ins_pre_ori_cod_pre__presente=0 AND pro_ins_pre_ori_tip_res__presente=0)),
    CHECK(procedencia_instrumental_registro_original__estado='PRESENTE' OR (pro_ins_reg_ori_cod_apl_ant__presente=0 AND pro_ins_reg_ori_cod_doc_cli__presente=0 AND pro_ins_reg_ori_cod_usu_reg__presente=0 AND pro_ins_reg_ori_loc_fue__presente=0 AND pro_ins_reg_ori_reg_en__presente=0))
);

CREATE TABLE ret_procedencia_instrumental_perfil_interp_c058 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_traza_evidencias_c057(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo__presente=0 AND cod_pregunta__presente=0))
);

CREATE TABLE ret_procedencia_instrumental_perfil_interp_c059 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_traza_evidencias_c057(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_perfil_traza_reglas_c060 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    alcance__presente INTEGER NOT NULL,
    alcance TEXT,
    clave_regla__presente INTEGER NOT NULL,
    clave_regla TEXT,
    condiciones__estado VARCHAR(8) NOT NULL CHECK(condiciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    condiciones__cantidad BIGINT NOT NULL CHECK(condiciones__cantidad>=0),
    consecuencia__estado VARCHAR(8) NOT NULL CHECK(consecuencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    consecuencia_cod_consecuencia_regla__presente INTEGER NOT NULL,
    consecuencia_cod_consecuencia_regla TEXT,
    consecuencia_cod_criterio_dominio_r__presente INTEGER NOT NULL,
    consecuencia_cod_criterio_dominio_r TEXT,
    consecuencia_cod_regla_experta__presente INTEGER NOT NULL,
    consecuencia_cod_regla_experta TEXT,
    consecuencia_cod_valor_semantico__presente INTEGER NOT NULL,
    consecuencia_cod_valor_semantico TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    evaluaciones_unidad__estado VARCHAR(8) NOT NULL CHECK(evaluaciones_unidad__estado IN ('AUSENTE','NULL','PRESENTE')),
    evaluaciones_unidad__cantidad BIGINT NOT NULL CHECK(evaluaciones_unidad__cantidad>=0),
    regla__presente INTEGER NOT NULL,
    regla TEXT,
    unidades_por_componente__estado VARCHAR(8) NOT NULL CHECK(unidades_por_componente__estado IN ('AUSENTE','NULL','PRESENTE')),
    unidades_por_componente__cantidad BIGINT NOT NULL CHECK(unidades_por_componente__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_salida_resultado_perfil_c054(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(alcance__presente IN (0,1)),
    CHECK(alcance__presente=1 OR alcance IS NULL),
    CHECK(clave_regla__presente IN (0,1)),
    CHECK(clave_regla__presente=1 OR clave_regla IS NULL),
    CHECK(condiciones__estado='PRESENTE' OR condiciones__cantidad=0),
    CHECK(consecuencia_cod_consecuencia_regla__presente IN (0,1)),
    CHECK(consecuencia_cod_consecuencia_regla__presente=1 OR consecuencia_cod_consecuencia_regla IS NULL),
    CHECK(consecuencia_cod_criterio_dominio_r__presente IN (0,1)),
    CHECK(consecuencia_cod_criterio_dominio_r__presente=1 OR consecuencia_cod_criterio_dominio_r IS NULL),
    CHECK(consecuencia_cod_regla_experta__presente IN (0,1)),
    CHECK(consecuencia_cod_regla_experta__presente=1 OR consecuencia_cod_regla_experta IS NULL),
    CHECK(consecuencia_cod_valor_semantico__presente IN (0,1)),
    CHECK(consecuencia_cod_valor_semantico__presente=1 OR consecuencia_cod_valor_semantico IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(evaluaciones_unidad__estado='PRESENTE' OR evaluaciones_unidad__cantidad=0),
    CHECK(regla__presente IN (0,1)),
    CHECK(regla__presente=1 OR regla IS NULL),
    CHECK(unidades_por_componente__estado='PRESENTE' OR unidades_por_componente__cantidad=0),
    CHECK(dto__estado='PRESENTE' OR (alcance__presente=0 AND clave_regla__presente=0 AND condiciones__estado='AUSENTE' AND consecuencia__estado='AUSENTE' AND estado__presente=0 AND evaluaciones_unidad__estado='AUSENTE' AND regla__presente=0 AND unidades_por_componente__estado='AUSENTE')),
    CHECK(consecuencia__estado='PRESENTE' OR (consecuencia_cod_consecuencia_regla__presente=0 AND consecuencia_cod_criterio_dominio_r__presente=0 AND consecuencia_cod_regla_experta__presente=0 AND consecuencia_cod_valor_semantico__presente=0))
);

CREATE TABLE ret_traza_reglas_condiciones_c061 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    condicion__presente INTEGER NOT NULL,
    condicion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    soportes__estado VARCHAR(8) NOT NULL CHECK(soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__cantidad BIGINT NOT NULL CHECK(soportes__cantidad>=0),
    valor__presente INTEGER NOT NULL,
    valor TEXT,
    valor_codigo__presente INTEGER NOT NULL,
    valor_codigo TEXT,
    variable__presente INTEGER NOT NULL,
    variable TEXT,
    variable_codigo__presente INTEGER NOT NULL,
    variable_codigo TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_traza_reglas_c060(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(condicion__presente IN (0,1)),
    CHECK(condicion__presente=1 OR condicion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(soportes__estado='PRESENTE' OR soportes__cantidad=0),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor IS NULL),
    CHECK(valor_codigo__presente IN (0,1)),
    CHECK(valor_codigo__presente=1 OR valor_codigo IS NULL),
    CHECK(variable__presente IN (0,1)),
    CHECK(variable__presente=1 OR variable IS NULL),
    CHECK(variable_codigo__presente IN (0,1)),
    CHECK(variable_codigo__presente=1 OR variable_codigo IS NULL),
    CHECK(dto__estado='PRESENTE' OR (condicion__presente=0 AND estado__presente=0 AND soportes__estado='AUSENTE' AND valor__presente=0 AND valor_codigo__presente=0 AND variable__presente=0 AND variable_codigo__presente=0))
);

CREATE TABLE ret_reglas_condiciones_soportes_c062 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_traza_reglas_condiciones_c061(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_traza_reglas_evaluaciones_unidad_c063 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    condiciones__estado VARCHAR(8) NOT NULL CHECK(condiciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    condiciones__cantidad BIGINT NOT NULL CHECK(condiciones__cantidad>=0),
    consecuencia__estado VARCHAR(8) NOT NULL CHECK(consecuencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    consecuencia_cod_consecuencia_regla__presente INTEGER NOT NULL,
    consecuencia_cod_consecuencia_regla TEXT,
    consecuencia_cod_criterio_dominio_r__presente INTEGER NOT NULL,
    consecuencia_cod_criterio_dominio_r TEXT,
    consecuencia_cod_regla_experta__presente INTEGER NOT NULL,
    consecuencia_cod_regla_experta TEXT,
    consecuencia_cod_valor_semantico__presente INTEGER NOT NULL,
    consecuencia_cod_valor_semantico TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    regla__presente INTEGER NOT NULL,
    regla TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_traza_reglas_c060(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(condiciones__estado='PRESENTE' OR condiciones__cantidad=0),
    CHECK(consecuencia_cod_consecuencia_regla__presente IN (0,1)),
    CHECK(consecuencia_cod_consecuencia_regla__presente=1 OR consecuencia_cod_consecuencia_regla IS NULL),
    CHECK(consecuencia_cod_criterio_dominio_r__presente IN (0,1)),
    CHECK(consecuencia_cod_criterio_dominio_r__presente=1 OR consecuencia_cod_criterio_dominio_r IS NULL),
    CHECK(consecuencia_cod_regla_experta__presente IN (0,1)),
    CHECK(consecuencia_cod_regla_experta__presente=1 OR consecuencia_cod_regla_experta IS NULL),
    CHECK(consecuencia_cod_valor_semantico__presente IN (0,1)),
    CHECK(consecuencia_cod_valor_semantico__presente=1 OR consecuencia_cod_valor_semantico IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(regla__presente IN (0,1)),
    CHECK(regla__presente=1 OR regla IS NULL),
    CHECK(dto__estado='PRESENTE' OR (condiciones__estado='AUSENTE' AND consecuencia__estado='AUSENTE' AND estado__presente=0 AND regla__presente=0)),
    CHECK(consecuencia__estado='PRESENTE' OR (consecuencia_cod_consecuencia_regla__presente=0 AND consecuencia_cod_criterio_dominio_r__presente=0 AND consecuencia_cod_regla_experta__presente=0 AND consecuencia_cod_valor_semantico__presente=0))
);

CREATE TABLE ret_reglas_evaluaciones_unidad_condiciones_c064 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    condicion__presente INTEGER NOT NULL,
    condicion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    soportes__estado VARCHAR(8) NOT NULL CHECK(soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__cantidad BIGINT NOT NULL CHECK(soportes__cantidad>=0),
    valor__presente INTEGER NOT NULL,
    valor TEXT,
    variable__presente INTEGER NOT NULL,
    variable TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_traza_reglas_evaluaciones_unidad_c063(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(condicion__presente IN (0,1)),
    CHECK(condicion__presente=1 OR condicion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(soportes__estado='PRESENTE' OR soportes__cantidad=0),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor IS NULL),
    CHECK(variable__presente IN (0,1)),
    CHECK(variable__presente=1 OR variable IS NULL),
    CHECK(dto__estado='PRESENTE' OR (condicion__presente=0 AND estado__presente=0 AND soportes__estado='AUSENTE' AND valor__presente=0 AND variable__presente=0))
);

CREATE TABLE ret_evaluaciones_unidad_condiciones_soport_c065 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_reglas_evaluaciones_unidad_condiciones_c064(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_traza_reglas_unidades_por_componente_c066 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_traza_reglas_c060(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING')))
);

CREATE TABLE ret_perfil_traza_rutas_c067 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    condicion__presente INTEGER NOT NULL,
    condicion TEXT,
    criterio__presente INTEGER NOT NULL,
    criterio TEXT,
    estado_regla__presente INTEGER NOT NULL,
    estado_regla TEXT,
    evaluacion__presente INTEGER NOT NULL,
    evaluacion TEXT,
    evidencia__presente INTEGER NOT NULL,
    evidencia TEXT,
    fecha_fuente__presente INTEGER NOT NULL,
    fecha_fuente TEXT,
    fuente__presente INTEGER NOT NULL,
    fuente TEXT,
    mapeo_valor__presente INTEGER NOT NULL,
    mapeo_valor TEXT,
    mapeo_variable__presente INTEGER NOT NULL,
    mapeo_variable TEXT,
    personal__presente INTEGER NOT NULL,
    personal TEXT,
    procedencia_instrumental__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia_instrumental_campo_original__presente INTEGER NOT NULL,
    procedencia_instrumental_campo_original TEXT,
    procedencia_instrumental_cod_aplicacion__presente INTEGER NOT NULL,
    procedencia_instrumental_cod_aplicacion TEXT,
    procedencia_instrumental_cod_personal_original__presente INTEGER NOT NULL,
    procedencia_instrumental_cod_personal_original TEXT,
    procedencia_instrumental_cod_pregunta__presente INTEGER NOT NULL,
    procedencia_instrumental_cod_pregunta TEXT,
    procedencia_instrumental_cod_respuesta__presente INTEGER NOT NULL,
    procedencia_instrumental_cod_respuesta TEXT,
    procedencia_instrumental_episodio_original__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_episodio_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_epi_ori_cod_ate__presente INTEGER NOT NULL,
    pro_ins_epi_ori_cod_ate TEXT,
    pro_ins_epi_ori_gru_sop__presente INTEGER NOT NULL,
    pro_ins_epi_ori_gru_sop TEXT,
    pro_ins_epi_ori_ind_cli__presente INTEGER NOT NULL,
    pro_ins_epi_ori_ind_cli TEXT,
    pro_ins_epi_ori_uni_con__presente INTEGER NOT NULL,
    pro_ins_epi_ori_uni_con TEXT,
    procedencia_instrumental_fuente_edad__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_fuente_edad__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia_instrumental_fuente_edad_campo__presente INTEGER NOT NULL,
    procedencia_instrumental_fuente_edad_campo TEXT,
    pro_ins_fue_eda_fec_apl__presente INTEGER NOT NULL,
    pro_ins_fue_eda_fec_apl TEXT,
    pro_ins_fue_eda_fec_nac__presente INTEGER NOT NULL,
    pro_ins_fue_eda_fec_nac TEXT,
    procedencia_instrumental_fuente_edad_registro__presente INTEGER NOT NULL,
    procedencia_instrumental_fuente_edad_registro TEXT,
    procedencia_instrumental_fuente_edad_tabla__presente INTEGER NOT NULL,
    procedencia_instrumental_fuente_edad_tabla TEXT,
    procedencia_instrumental_instrumento__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_instrumento__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_ins_cod_ins__presente INTEGER NOT NULL,
    pro_ins_ins_cod_ins TEXT,
    procedencia_instrumental_instrumento_codigo__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_codigo TEXT,
    procedencia_instrumental_instrumento_estado__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_estado TEXT,
    procedencia_instrumental_instrumento_nombre__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_nombre TEXT,
    procedencia_instrumental_instrumento_tipo__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_tipo TEXT,
    procedencia_instrumental_instrumento_version__presente INTEGER NOT NULL,
    procedencia_instrumental_instrumento_version TEXT,
    pro_ins_med_int__estado VARCHAR(8) NOT NULL CHECK(pro_ins_med_int__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_med_int_aci__estado VARCHAR(8) NOT NULL CHECK(pro_ins_med_int_aci__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_med_int_aci_cam__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_cam TEXT,
    pro_ins_med_int_aci_cod_apl__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_cod_apl TEXT,
    pro_ins_med_int_aci_cod_pre__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_cod_pre TEXT,
    pro_ins_med_int_aci_cod_res__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_cod_res TEXT,
    pro_ins_med_int_aci_val_ori__presente INTEGER NOT NULL,
    pro_ins_med_int_aci_val_ori TEXT,
    procedencia_instrumental_perfil_interpretativo__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_perfil_interpretativo__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_per_int_cod__presente INTEGER NOT NULL,
    pro_ins_per_int_cod TEXT,
    pro_ins_per_int_com__presente INTEGER NOT NULL,
    pro_ins_per_int_com TEXT,
    pro_ins_per_int_fue__presente INTEGER NOT NULL,
    pro_ins_per_int_fue TEXT,
    pro_ins_per_int_ins__estado VARCHAR(8) NOT NULL CHECK(pro_ins_per_int_ins__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_per_int_ins_cod__presente INTEGER NOT NULL,
    pro_ins_per_int_ins_cod TEXT,
    pro_ins_per_int_ins_ver__presente INTEGER NOT NULL,
    pro_ins_per_int_ins_ver TEXT,
    pro_ins_per_int_med_req__estado VARCHAR(8) NOT NULL CHECK(pro_ins_per_int_med_req__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_per_int_med_req__cantidad BIGINT NOT NULL CHECK(pro_ins_per_int_med_req__cantidad>=0),
    pro_ins_per_int_met__presente INTEGER NOT NULL,
    pro_ins_per_int_met TEXT,
    pro_ins_per_int_pre__estado VARCHAR(8) NOT NULL CHECK(pro_ins_per_int_pre__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_per_int_pre__cantidad BIGINT NOT NULL CHECK(pro_ins_per_int_pre__cantidad>=0),
    pro_ins_per_int_ver__presente INTEGER NOT NULL,
    pro_ins_per_int_ver TEXT,
    procedencia_instrumental_pregunta_original__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_pregunta_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_pre_ori_cod_ins__presente INTEGER NOT NULL,
    pro_ins_pre_ori_cod_ins TEXT,
    pro_ins_pre_ori_cod_pre__presente INTEGER NOT NULL,
    pro_ins_pre_ori_cod_pre TEXT,
    pro_ins_pre_ori_tip_res__presente INTEGER NOT NULL,
    pro_ins_pre_ori_tip_res TEXT,
    procedencia_instrumental_registro_original__estado VARCHAR(8) NOT NULL CHECK(procedencia_instrumental_registro_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    pro_ins_reg_ori_cod_apl_ant__presente INTEGER NOT NULL,
    pro_ins_reg_ori_cod_apl_ant TEXT,
    pro_ins_reg_ori_cod_doc_cli__presente INTEGER NOT NULL,
    pro_ins_reg_ori_cod_doc_cli TEXT,
    pro_ins_reg_ori_cod_usu_reg__presente INTEGER NOT NULL,
    pro_ins_reg_ori_cod_usu_reg TEXT,
    pro_ins_reg_ori_loc_fue__presente INTEGER NOT NULL,
    pro_ins_reg_ori_loc_fue TEXT,
    pro_ins_reg_ori_reg_en__presente INTEGER NOT NULL,
    pro_ins_reg_ori_reg_en TEXT,
    registro__presente INTEGER NOT NULL,
    registro TEXT,
    regla__presente INTEGER NOT NULL,
    regla TEXT,
    residente__presente INTEGER NOT NULL,
    residente TEXT,
    tabla__presente INTEGER NOT NULL,
    tabla TEXT,
    valor__presente INTEGER NOT NULL,
    valor TEXT,
    valor_original__presente INTEGER NOT NULL,
    valor_original INTEGER,
    variable__presente INTEGER NOT NULL,
    variable TEXT,
    version__presente INTEGER NOT NULL,
    version TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_salida_resultado_perfil_c054(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(condicion__presente IN (0,1)),
    CHECK(condicion__presente=1 OR condicion IS NULL),
    CHECK(criterio__presente IN (0,1)),
    CHECK(criterio__presente=1 OR criterio IS NULL),
    CHECK(estado_regla__presente IN (0,1)),
    CHECK(estado_regla__presente=1 OR estado_regla IS NULL),
    CHECK(evaluacion__presente IN (0,1)),
    CHECK(evaluacion__presente=1 OR evaluacion IS NULL),
    CHECK(evidencia__presente IN (0,1)),
    CHECK(evidencia__presente=1 OR evidencia IS NULL),
    CHECK(fecha_fuente__presente IN (0,1)),
    CHECK(fecha_fuente__presente=1 OR fecha_fuente IS NULL),
    CHECK(fuente__presente IN (0,1)),
    CHECK(fuente__presente=1 OR fuente IS NULL),
    CHECK(mapeo_valor__presente IN (0,1)),
    CHECK(mapeo_valor__presente=1 OR mapeo_valor IS NULL),
    CHECK(mapeo_variable__presente IN (0,1)),
    CHECK(mapeo_variable__presente=1 OR mapeo_variable IS NULL),
    CHECK(personal__presente IN (0,1)),
    CHECK(personal__presente=1 OR personal IS NULL),
    CHECK(procedencia_instrumental_campo_original__presente IN (0,1)),
    CHECK(procedencia_instrumental_campo_original__presente=1 OR procedencia_instrumental_campo_original IS NULL),
    CHECK(procedencia_instrumental_cod_aplicacion__presente IN (0,1)),
    CHECK(procedencia_instrumental_cod_aplicacion__presente=1 OR procedencia_instrumental_cod_aplicacion IS NULL),
    CHECK(procedencia_instrumental_cod_personal_original__presente IN (0,1)),
    CHECK(procedencia_instrumental_cod_personal_original__presente=1 OR procedencia_instrumental_cod_personal_original IS NULL),
    CHECK(procedencia_instrumental_cod_pregunta__presente IN (0,1)),
    CHECK(procedencia_instrumental_cod_pregunta__presente=1 OR procedencia_instrumental_cod_pregunta IS NULL),
    CHECK(procedencia_instrumental_cod_respuesta__presente IN (0,1)),
    CHECK(procedencia_instrumental_cod_respuesta__presente=1 OR procedencia_instrumental_cod_respuesta IS NULL),
    CHECK(pro_ins_epi_ori_cod_ate__presente IN (0,1)),
    CHECK(pro_ins_epi_ori_cod_ate__presente=1 OR pro_ins_epi_ori_cod_ate IS NULL),
    CHECK(pro_ins_epi_ori_gru_sop__presente IN (0,1)),
    CHECK(pro_ins_epi_ori_gru_sop__presente=1 OR pro_ins_epi_ori_gru_sop IS NULL),
    CHECK(pro_ins_epi_ori_ind_cli__presente IN (0,1)),
    CHECK(pro_ins_epi_ori_ind_cli__presente=1 OR pro_ins_epi_ori_ind_cli IS NULL),
    CHECK(pro_ins_epi_ori_uni_con__presente IN (0,1)),
    CHECK(pro_ins_epi_ori_uni_con__presente=1 OR pro_ins_epi_ori_uni_con IS NULL),
    CHECK(procedencia_instrumental_fuente_edad_campo__presente IN (0,1)),
    CHECK(procedencia_instrumental_fuente_edad_campo__presente=1 OR procedencia_instrumental_fuente_edad_campo IS NULL),
    CHECK(pro_ins_fue_eda_fec_apl__presente IN (0,1)),
    CHECK(pro_ins_fue_eda_fec_apl__presente=1 OR pro_ins_fue_eda_fec_apl IS NULL),
    CHECK(pro_ins_fue_eda_fec_nac__presente IN (0,1)),
    CHECK(pro_ins_fue_eda_fec_nac__presente=1 OR pro_ins_fue_eda_fec_nac IS NULL),
    CHECK(procedencia_instrumental_fuente_edad_registro__presente IN (0,1)),
    CHECK(procedencia_instrumental_fuente_edad_registro__presente=1 OR procedencia_instrumental_fuente_edad_registro IS NULL),
    CHECK(procedencia_instrumental_fuente_edad_tabla__presente IN (0,1)),
    CHECK(procedencia_instrumental_fuente_edad_tabla__presente=1 OR procedencia_instrumental_fuente_edad_tabla IS NULL),
    CHECK(pro_ins_ins_cod_ins__presente IN (0,1)),
    CHECK(pro_ins_ins_cod_ins__presente=1 OR pro_ins_ins_cod_ins IS NULL),
    CHECK(procedencia_instrumental_instrumento_codigo__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_codigo__presente=1 OR procedencia_instrumental_instrumento_codigo IS NULL),
    CHECK(procedencia_instrumental_instrumento_estado__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_estado__presente=1 OR procedencia_instrumental_instrumento_estado IS NULL),
    CHECK(procedencia_instrumental_instrumento_nombre__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_nombre__presente=1 OR procedencia_instrumental_instrumento_nombre IS NULL),
    CHECK(procedencia_instrumental_instrumento_tipo__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_tipo__presente=1 OR procedencia_instrumental_instrumento_tipo IS NULL),
    CHECK(procedencia_instrumental_instrumento_version__presente IN (0,1)),
    CHECK(procedencia_instrumental_instrumento_version__presente=1 OR procedencia_instrumental_instrumento_version IS NULL),
    CHECK(pro_ins_med_int_aci_cam__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_cam__presente=1 OR pro_ins_med_int_aci_cam IS NULL),
    CHECK(pro_ins_med_int_aci_cod_apl__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_cod_apl__presente=1 OR pro_ins_med_int_aci_cod_apl IS NULL),
    CHECK(pro_ins_med_int_aci_cod_pre__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_cod_pre__presente=1 OR pro_ins_med_int_aci_cod_pre IS NULL),
    CHECK(pro_ins_med_int_aci_cod_res__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_cod_res__presente=1 OR pro_ins_med_int_aci_cod_res IS NULL),
    CHECK(pro_ins_med_int_aci_val_ori__presente IN (0,1)),
    CHECK(pro_ins_med_int_aci_val_ori__presente=1 OR pro_ins_med_int_aci_val_ori IS NULL),
    CHECK(pro_ins_per_int_cod__presente IN (0,1)),
    CHECK(pro_ins_per_int_cod__presente=1 OR pro_ins_per_int_cod IS NULL),
    CHECK(pro_ins_per_int_com__presente IN (0,1)),
    CHECK(pro_ins_per_int_com__presente=1 OR pro_ins_per_int_com IS NULL),
    CHECK(pro_ins_per_int_fue__presente IN (0,1)),
    CHECK(pro_ins_per_int_fue__presente=1 OR pro_ins_per_int_fue IS NULL),
    CHECK(pro_ins_per_int_ins_cod__presente IN (0,1)),
    CHECK(pro_ins_per_int_ins_cod__presente=1 OR pro_ins_per_int_ins_cod IS NULL),
    CHECK(pro_ins_per_int_ins_ver__presente IN (0,1)),
    CHECK(pro_ins_per_int_ins_ver__presente=1 OR pro_ins_per_int_ins_ver IS NULL),
    CHECK(pro_ins_per_int_med_req__estado='PRESENTE' OR pro_ins_per_int_med_req__cantidad=0),
    CHECK(pro_ins_per_int_met__presente IN (0,1)),
    CHECK(pro_ins_per_int_met__presente=1 OR pro_ins_per_int_met IS NULL),
    CHECK(pro_ins_per_int_pre__estado='PRESENTE' OR pro_ins_per_int_pre__cantidad=0),
    CHECK(pro_ins_per_int_ver__presente IN (0,1)),
    CHECK(pro_ins_per_int_ver__presente=1 OR pro_ins_per_int_ver IS NULL),
    CHECK(pro_ins_pre_ori_cod_ins__presente IN (0,1)),
    CHECK(pro_ins_pre_ori_cod_ins__presente=1 OR pro_ins_pre_ori_cod_ins IS NULL),
    CHECK(pro_ins_pre_ori_cod_pre__presente IN (0,1)),
    CHECK(pro_ins_pre_ori_cod_pre__presente=1 OR pro_ins_pre_ori_cod_pre IS NULL),
    CHECK(pro_ins_pre_ori_tip_res__presente IN (0,1)),
    CHECK(pro_ins_pre_ori_tip_res__presente=1 OR pro_ins_pre_ori_tip_res IS NULL),
    CHECK(pro_ins_reg_ori_cod_apl_ant__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_cod_apl_ant__presente=1 OR pro_ins_reg_ori_cod_apl_ant IS NULL),
    CHECK(pro_ins_reg_ori_cod_doc_cli__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_cod_doc_cli__presente=1 OR pro_ins_reg_ori_cod_doc_cli IS NULL),
    CHECK(pro_ins_reg_ori_cod_usu_reg__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_cod_usu_reg__presente=1 OR pro_ins_reg_ori_cod_usu_reg IS NULL),
    CHECK(pro_ins_reg_ori_loc_fue__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_loc_fue__presente=1 OR pro_ins_reg_ori_loc_fue IS NULL),
    CHECK(pro_ins_reg_ori_reg_en__presente IN (0,1)),
    CHECK(pro_ins_reg_ori_reg_en__presente=1 OR pro_ins_reg_ori_reg_en IS NULL),
    CHECK(registro__presente IN (0,1)),
    CHECK(registro__presente=1 OR registro IS NULL),
    CHECK(regla__presente IN (0,1)),
    CHECK(regla__presente=1 OR regla IS NULL),
    CHECK(residente__presente IN (0,1)),
    CHECK(residente__presente=1 OR residente IS NULL),
    CHECK(tabla__presente IN (0,1)),
    CHECK(tabla__presente=1 OR tabla IS NULL),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor IS NULL),
    CHECK(valor_original__presente IN (0,1)),
    CHECK(valor_original__presente=1 OR valor_original IS NULL),
    CHECK(valor_original IS NULL OR valor_original IN (0,1)),
    CHECK(variable__presente IN (0,1)),
    CHECK(variable__presente=1 OR variable IS NULL),
    CHECK(version__presente IN (0,1)),
    CHECK(version__presente=1 OR version IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo__presente=0 AND condicion__presente=0 AND criterio__presente=0 AND estado_regla__presente=0 AND evaluacion__presente=0 AND evidencia__presente=0 AND fecha_fuente__presente=0 AND fuente__presente=0 AND mapeo_valor__presente=0 AND mapeo_variable__presente=0 AND personal__presente=0 AND procedencia_instrumental__estado='AUSENTE' AND registro__presente=0 AND regla__presente=0 AND residente__presente=0 AND tabla__presente=0 AND valor__presente=0 AND valor_original__presente=0 AND variable__presente=0 AND version__presente=0)),
    CHECK(procedencia_instrumental__estado='PRESENTE' OR (procedencia_instrumental_campo_original__presente=0 AND procedencia_instrumental_cod_aplicacion__presente=0 AND procedencia_instrumental_cod_personal_original__presente=0 AND procedencia_instrumental_cod_pregunta__presente=0 AND procedencia_instrumental_cod_respuesta__presente=0 AND procedencia_instrumental_episodio_original__estado='AUSENTE' AND procedencia_instrumental_fuente_edad__estado='AUSENTE' AND procedencia_instrumental_instrumento__estado='AUSENTE' AND pro_ins_med_int__estado='AUSENTE' AND procedencia_instrumental_perfil_interpretativo__estado='AUSENTE' AND procedencia_instrumental_pregunta_original__estado='AUSENTE' AND procedencia_instrumental_registro_original__estado='AUSENTE')),
    CHECK(procedencia_instrumental_episodio_original__estado='PRESENTE' OR (pro_ins_epi_ori_cod_ate__presente=0 AND pro_ins_epi_ori_gru_sop__presente=0 AND pro_ins_epi_ori_ind_cli__presente=0 AND pro_ins_epi_ori_uni_con__presente=0)),
    CHECK(procedencia_instrumental_fuente_edad__estado='PRESENTE' OR (procedencia_instrumental_fuente_edad_campo__presente=0 AND pro_ins_fue_eda_fec_apl__presente=0 AND pro_ins_fue_eda_fec_nac__presente=0 AND procedencia_instrumental_fuente_edad_registro__presente=0 AND procedencia_instrumental_fuente_edad_tabla__presente=0)),
    CHECK(procedencia_instrumental_instrumento__estado='PRESENTE' OR (pro_ins_ins_cod_ins__presente=0 AND procedencia_instrumental_instrumento_codigo__presente=0 AND procedencia_instrumental_instrumento_estado__presente=0 AND procedencia_instrumental_instrumento_nombre__presente=0 AND procedencia_instrumental_instrumento_tipo__presente=0 AND procedencia_instrumental_instrumento_version__presente=0)),
    CHECK(pro_ins_med_int__estado='PRESENTE' OR (pro_ins_med_int_aci__estado='AUSENTE')),
    CHECK(pro_ins_med_int_aci__estado='PRESENTE' OR (pro_ins_med_int_aci_cam__presente=0 AND pro_ins_med_int_aci_cod_apl__presente=0 AND pro_ins_med_int_aci_cod_pre__presente=0 AND pro_ins_med_int_aci_cod_res__presente=0 AND pro_ins_med_int_aci_val_ori__presente=0)),
    CHECK(procedencia_instrumental_perfil_interpretativo__estado='PRESENTE' OR (pro_ins_per_int_cod__presente=0 AND pro_ins_per_int_com__presente=0 AND pro_ins_per_int_fue__presente=0 AND pro_ins_per_int_ins__estado='AUSENTE' AND pro_ins_per_int_med_req__estado='AUSENTE' AND pro_ins_per_int_met__presente=0 AND pro_ins_per_int_pre__estado='AUSENTE' AND pro_ins_per_int_ver__presente=0)),
    CHECK(pro_ins_per_int_ins__estado='PRESENTE' OR (pro_ins_per_int_ins_cod__presente=0 AND pro_ins_per_int_ins_ver__presente=0)),
    CHECK(procedencia_instrumental_pregunta_original__estado='PRESENTE' OR (pro_ins_pre_ori_cod_ins__presente=0 AND pro_ins_pre_ori_cod_pre__presente=0 AND pro_ins_pre_ori_tip_res__presente=0)),
    CHECK(procedencia_instrumental_registro_original__estado='PRESENTE' OR (pro_ins_reg_ori_cod_apl_ant__presente=0 AND pro_ins_reg_ori_cod_doc_cli__presente=0 AND pro_ins_reg_ori_cod_usu_reg__presente=0 AND pro_ins_reg_ori_loc_fue__presente=0 AND pro_ins_reg_ori_reg_en__presente=0))
);

CREATE TABLE ret_procedencia_instrumental_perfil_interp_c068 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_traza_rutas_c067(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo__presente=0 AND cod_pregunta__presente=0))
);

CREATE TABLE ret_procedencia_instrumental_perfil_interp_c069 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_perfil_traza_rutas_c067(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_resultado_red_componentes_c070 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    dominio__presente INTEGER NOT NULL,
    dominio TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    nodo__presente INTEGER NOT NULL,
    nodo TEXT,
    relacion__presente INTEGER NOT NULL,
    relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(dominio__presente IN (0,1)),
    CHECK(dominio__presente=1 OR dominio IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(nodo__presente IN (0,1)),
    CHECK(nodo__presente=1 OR nodo IS NULL),
    CHECK(relacion__presente IN (0,1)),
    CHECK(relacion__presente=1 OR relacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (dominio__presente=0 AND estado__presente=0 AND nodo__presente=0 AND relacion__presente=0))
);

CREATE TABLE ret_resultado_red_dominios_c071 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_resultado_red_rutas_reglas_c072 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    dto__cantidad BIGINT NOT NULL CHECK(dto__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(dto__estado='PRESENTE' OR dto__cantidad=0)
);

CREATE TABLE ret_resultado_red_rutas_reglas_c073 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    condicion__presente INTEGER NOT NULL,
    condicion TEXT,
    consecuencia__presente INTEGER NOT NULL,
    consecuencia TEXT,
    criterio__presente INTEGER NOT NULL,
    criterio TEXT,
    pertenencia__presente INTEGER NOT NULL,
    pertenencia TEXT,
    propietario__presente INTEGER NOT NULL,
    propietario TEXT,
    variable__presente INTEGER NOT NULL,
    variable TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_resultado_red_rutas_reglas_c072(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(condicion__presente IN (0,1)),
    CHECK(condicion__presente=1 OR condicion IS NULL),
    CHECK(consecuencia__presente IN (0,1)),
    CHECK(consecuencia__presente=1 OR consecuencia IS NULL),
    CHECK(criterio__presente IN (0,1)),
    CHECK(criterio__presente=1 OR criterio IS NULL),
    CHECK(pertenencia__presente IN (0,1)),
    CHECK(pertenencia__presente=1 OR pertenencia IS NULL),
    CHECK(propietario__presente IN (0,1)),
    CHECK(propietario__presente=1 OR propietario IS NULL),
    CHECK(variable__presente IN (0,1)),
    CHECK(variable__presente=1 OR variable IS NULL),
    CHECK(dto__estado='PRESENTE' OR (condicion__presente=0 AND consecuencia__presente=0 AND criterio__presente=0 AND pertenencia__presente=0 AND propietario__presente=0 AND variable__presente=0))
);

CREATE TABLE ret_instrumental_contenido_vinculos_c074 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    cod_instrumento__presente INTEGER NOT NULL,
    cod_instrumento TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    clave_instrumento__presente INTEGER NOT NULL,
    clave_instrumento TEXT,
    componente__presente INTEGER NOT NULL,
    componente TEXT,
    perfil_interpretativo__estado VARCHAR(8) NOT NULL CHECK(perfil_interpretativo__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_interpretativo_codigo__presente INTEGER NOT NULL,
    perfil_interpretativo_codigo TEXT,
    perfil_interpretativo_componente__presente INTEGER NOT NULL,
    perfil_interpretativo_componente TEXT,
    perfil_interpretativo_fuente__presente INTEGER NOT NULL,
    perfil_interpretativo_fuente TEXT,
    perfil_interpretativo_instrumento__estado VARCHAR(8) NOT NULL CHECK(perfil_interpretativo_instrumento__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_interpretativo_instrumento_codigo__presente INTEGER NOT NULL,
    perfil_interpretativo_instrumento_codigo TEXT,
    perfil_interpretativo_instrumento_version__presente INTEGER NOT NULL,
    perfil_interpretativo_instrumento_version TEXT,
    perfil_interpretativo_mediciones_requeridas__estado VARCHAR(8) NOT NULL CHECK(perfil_interpretativo_mediciones_requeridas__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_interpretativo_mediciones_requeridas__cantidad BIGINT NOT NULL CHECK(perfil_interpretativo_mediciones_requeridas__cantidad>=0),
    perfil_interpretativo_metodo__presente INTEGER NOT NULL,
    perfil_interpretativo_metodo TEXT,
    perfil_interpretativo_predicados__estado VARCHAR(8) NOT NULL CHECK(perfil_interpretativo_predicados__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_interpretativo_predicados__cantidad BIGINT NOT NULL CHECK(perfil_interpretativo_predicados__cantidad>=0),
    perfil_interpretativo_version__presente INTEGER NOT NULL,
    perfil_interpretativo_version TEXT,
    version_instrumento__presente INTEGER NOT NULL,
    version_instrumento TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(cod_instrumento__presente IN (0,1)),
    CHECK(cod_instrumento__presente=1 OR cod_instrumento IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(clave_instrumento__presente IN (0,1)),
    CHECK(clave_instrumento__presente=1 OR clave_instrumento IS NULL),
    CHECK(componente__presente IN (0,1)),
    CHECK(componente__presente=1 OR componente IS NULL),
    CHECK(perfil_interpretativo_codigo__presente IN (0,1)),
    CHECK(perfil_interpretativo_codigo__presente=1 OR perfil_interpretativo_codigo IS NULL),
    CHECK(perfil_interpretativo_componente__presente IN (0,1)),
    CHECK(perfil_interpretativo_componente__presente=1 OR perfil_interpretativo_componente IS NULL),
    CHECK(perfil_interpretativo_fuente__presente IN (0,1)),
    CHECK(perfil_interpretativo_fuente__presente=1 OR perfil_interpretativo_fuente IS NULL),
    CHECK(perfil_interpretativo_instrumento_codigo__presente IN (0,1)),
    CHECK(perfil_interpretativo_instrumento_codigo__presente=1 OR perfil_interpretativo_instrumento_codigo IS NULL),
    CHECK(perfil_interpretativo_instrumento_version__presente IN (0,1)),
    CHECK(perfil_interpretativo_instrumento_version__presente=1 OR perfil_interpretativo_instrumento_version IS NULL),
    CHECK(perfil_interpretativo_mediciones_requeridas__estado='PRESENTE' OR perfil_interpretativo_mediciones_requeridas__cantidad=0),
    CHECK(perfil_interpretativo_metodo__presente IN (0,1)),
    CHECK(perfil_interpretativo_metodo__presente=1 OR perfil_interpretativo_metodo IS NULL),
    CHECK(perfil_interpretativo_predicados__estado='PRESENTE' OR perfil_interpretativo_predicados__cantidad=0),
    CHECK(perfil_interpretativo_version__presente IN (0,1)),
    CHECK(perfil_interpretativo_version__presente=1 OR perfil_interpretativo_version IS NULL),
    CHECK(version_instrumento__presente IN (0,1)),
    CHECK(version_instrumento__presente=1 OR version_instrumento IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo__presente=0 AND cod_instrumento__presente=0 AND cod_pregunta__presente=0 AND clave_instrumento__presente=0 AND componente__presente=0 AND perfil_interpretativo__estado='AUSENTE' AND version_instrumento__presente=0)),
    CHECK(perfil_interpretativo__estado='PRESENTE' OR (perfil_interpretativo_codigo__presente=0 AND perfil_interpretativo_componente__presente=0 AND perfil_interpretativo_fuente__presente=0 AND perfil_interpretativo_instrumento__estado='AUSENTE' AND perfil_interpretativo_mediciones_requeridas__estado='AUSENTE' AND perfil_interpretativo_metodo__presente=0 AND perfil_interpretativo_predicados__estado='AUSENTE' AND perfil_interpretativo_version__presente=0)),
    CHECK(perfil_interpretativo_instrumento__estado='PRESENTE' OR (perfil_interpretativo_instrumento_codigo__presente=0 AND perfil_interpretativo_instrumento_version__presente=0))
);

CREATE TABLE ret_vinculos_perfil_interpretativo_medicio_c075 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_instrumental_contenido_vinculos_c074(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo__presente=0 AND cod_pregunta__presente=0))
);

CREATE TABLE ret_vinculos_perfil_interpretativo_predica_c076 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_instrumental_contenido_vinculos_c074(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_contenido_adquisicion_exclusiones_c077 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    elemento__presente INTEGER NOT NULL,
    elemento TEXT,
    motivo__presente INTEGER NOT NULL,
    motivo TEXT,
    valoracion__presente INTEGER NOT NULL,
    valoracion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(elemento__presente IN (0,1)),
    CHECK(elemento__presente=1 OR elemento IS NULL),
    CHECK(motivo__presente IN (0,1)),
    CHECK(motivo__presente=1 OR motivo IS NULL),
    CHECK(valoracion__presente IN (0,1)),
    CHECK(valoracion__presente=1 OR valoracion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (elemento__presente=0 AND motivo__presente=0 AND valoracion__presente=0))
);

CREATE TABLE ret_petersen_contenido_constancias_documen_c078 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_documento_clinico__presente INTEGER NOT NULL,
    cod_documento_clinico TEXT,
    cod_personal__presente INTEGER NOT NULL,
    cod_personal TEXT,
    cod_residente__presente INTEGER NOT NULL,
    cod_residente TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha__presente INTEGER NOT NULL,
    fecha TEXT,
    hash_archivo__presente INTEGER NOT NULL,
    hash_archivo TEXT,
    localizador_fuente__presente INTEGER NOT NULL,
    localizador_fuente TEXT,
    papel__presente INTEGER NOT NULL,
    papel TEXT,
    valoracion__presente INTEGER NOT NULL,
    valoracion TEXT,
    verificado__presente INTEGER NOT NULL,
    verificado INTEGER,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_documento_clinico__presente IN (0,1)),
    CHECK(cod_documento_clinico__presente=1 OR cod_documento_clinico IS NULL),
    CHECK(cod_personal__presente IN (0,1)),
    CHECK(cod_personal__presente=1 OR cod_personal IS NULL),
    CHECK(cod_residente__presente IN (0,1)),
    CHECK(cod_residente__presente=1 OR cod_residente IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha__presente IN (0,1)),
    CHECK(fecha__presente=1 OR fecha IS NULL),
    CHECK(hash_archivo__presente IN (0,1)),
    CHECK(hash_archivo__presente=1 OR hash_archivo IS NULL),
    CHECK(localizador_fuente__presente IN (0,1)),
    CHECK(localizador_fuente__presente=1 OR localizador_fuente IS NULL),
    CHECK(papel__presente IN (0,1)),
    CHECK(papel__presente=1 OR papel IS NULL),
    CHECK(valoracion__presente IN (0,1)),
    CHECK(valoracion__presente=1 OR valoracion IS NULL),
    CHECK(verificado__presente IN (0,1)),
    CHECK(verificado__presente=1 OR verificado IS NULL),
    CHECK(verificado IS NULL OR verificado IN (0,1)),
    CHECK(dto__estado='PRESENTE' OR (cod_documento_clinico__presente=0 AND cod_personal__presente=0 AND cod_residente__presente=0 AND estado__presente=0 AND fecha__presente=0 AND hash_archivo__presente=0 AND localizador_fuente__presente=0 AND papel__presente=0 AND valoracion__presente=0 AND verificado__presente=0))
);

CREATE TABLE ret_gobierno_conocimiento_contratos_relaci_c079 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    habilita_participacion__presente INTEGER NOT NULL,
    habilita_participacion INTEGER,
    modalidades__estado VARCHAR(8) NOT NULL CHECK(modalidades__estado IN ('AUSENTE','NULL','PRESENTE')),
    modalidades__cantidad BIGINT NOT NULL CHECK(modalidades__cantidad>=0),
    tipo_destino__presente INTEGER NOT NULL,
    tipo_destino TEXT,
    tipo_origen__presente INTEGER NOT NULL,
    tipo_origen TEXT,
    tipo_relacion__presente INTEGER NOT NULL,
    tipo_relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(habilita_participacion__presente IN (0,1)),
    CHECK(habilita_participacion__presente=1 OR habilita_participacion IS NULL),
    CHECK(habilita_participacion IS NULL OR habilita_participacion IN (0,1)),
    CHECK(modalidades__estado='PRESENTE' OR modalidades__cantidad=0),
    CHECK(tipo_destino__presente IN (0,1)),
    CHECK(tipo_destino__presente=1 OR tipo_destino IS NULL),
    CHECK(tipo_origen__presente IN (0,1)),
    CHECK(tipo_origen__presente=1 OR tipo_origen IS NULL),
    CHECK(tipo_relacion__presente IN (0,1)),
    CHECK(tipo_relacion__presente=1 OR tipo_relacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (habilita_participacion__presente=0 AND modalidades__estado='AUSENTE' AND tipo_destino__presente=0 AND tipo_origen__presente=0 AND tipo_relacion__presente=0))
);

CREATE TABLE ret_conocimiento_contratos_relaciones_moda_c080 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_gobierno_conocimiento_contratos_relaci_c079(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_conocimiento_tablas_condiciones_regla__c081 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_condicion_regla__presente INTEGER NOT NULL,
    cod_condicion_regla TEXT,
    cod_regla_experta__presente INTEGER NOT NULL,
    cod_regla_experta TEXT,
    cod_valor_semantico__presente INTEGER NOT NULL,
    cod_valor_semantico TEXT,
    cod_variable_experta__presente INTEGER NOT NULL,
    cod_variable_experta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_condicion_regla__presente IN (0,1)),
    CHECK(cod_condicion_regla__presente=1 OR cod_condicion_regla IS NULL),
    CHECK(cod_regla_experta__presente IN (0,1)),
    CHECK(cod_regla_experta__presente=1 OR cod_regla_experta IS NULL),
    CHECK(cod_valor_semantico__presente IN (0,1)),
    CHECK(cod_valor_semantico__presente=1 OR cod_valor_semantico IS NULL),
    CHECK(cod_variable_experta__presente IN (0,1)),
    CHECK(cod_variable_experta__presente=1 OR cod_variable_experta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_condicion_regla__presente=0 AND cod_regla_experta__presente=0 AND cod_valor_semantico__presente=0 AND cod_variable_experta__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_consecuencias_regl_c082 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_consecuencia_regla__presente INTEGER NOT NULL,
    cod_consecuencia_regla TEXT,
    cod_criterio_dominio_r__presente INTEGER NOT NULL,
    cod_criterio_dominio_r TEXT,
    cod_regla_experta__presente INTEGER NOT NULL,
    cod_regla_experta TEXT,
    cod_valor_semantico__presente INTEGER NOT NULL,
    cod_valor_semantico TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_consecuencia_regla__presente IN (0,1)),
    CHECK(cod_consecuencia_regla__presente=1 OR cod_consecuencia_regla IS NULL),
    CHECK(cod_criterio_dominio_r__presente IN (0,1)),
    CHECK(cod_criterio_dominio_r__presente=1 OR cod_criterio_dominio_r IS NULL),
    CHECK(cod_regla_experta__presente IN (0,1)),
    CHECK(cod_regla_experta__presente=1 OR cod_regla_experta IS NULL),
    CHECK(cod_valor_semantico__presente IN (0,1)),
    CHECK(cod_valor_semantico__presente=1 OR cod_valor_semantico IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_consecuencia_regla__presente=0 AND cod_criterio_dominio_r__presente=0 AND cod_regla_experta__presente=0 AND cod_valor_semantico__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_criterios_dominios_c083 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_criterio_dominio_r__presente INTEGER NOT NULL,
    cod_criterio_dominio_r TEXT,
    cod_dominio_valores__presente INTEGER NOT NULL,
    cod_dominio_valores TEXT,
    cod_nodo_criterio__presente INTEGER NOT NULL,
    cod_nodo_criterio TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_criterio_dominio_r__presente IN (0,1)),
    CHECK(cod_criterio_dominio_r__presente=1 OR cod_criterio_dominio_r IS NULL),
    CHECK(cod_dominio_valores__presente IN (0,1)),
    CHECK(cod_dominio_valores__presente=1 OR cod_dominio_valores IS NULL),
    CHECK(cod_nodo_criterio__presente IN (0,1)),
    CHECK(cod_nodo_criterio__presente=1 OR cod_nodo_criterio IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_criterio_dominio_r__presente=0 AND cod_dominio_valores__presente=0 AND cod_nodo_criterio__presente=0 AND cod_usuario_creacion__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND observacion__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_dominios_valores_e_c084 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente INTEGER NOT NULL,
    cod_dominio_valores TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    clave_dominio__presente INTEGER NOT NULL,
    clave_dominio TEXT,
    descripcion__presente INTEGER NOT NULL,
    descripcion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    tipo_dominio__presente INTEGER NOT NULL,
    tipo_dominio TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente IN (0,1)),
    CHECK(cod_dominio_valores__presente=1 OR cod_dominio_valores IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(clave_dominio__presente IN (0,1)),
    CHECK(clave_dominio__presente=1 OR clave_dominio IS NULL),
    CHECK(descripcion__presente IN (0,1)),
    CHECK(descripcion__presente=1 OR descripcion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(tipo_dominio__presente IN (0,1)),
    CHECK(tipo_dominio__presente=1 OR tipo_dominio IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_dominio_valores__presente=0 AND cod_usuario_creacion__presente=0 AND cod_version_modelo__presente=0 AND clave_dominio__presente=0 AND descripcion__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND nombre__presente=0 AND observacion__presente=0 AND tipo_dominio__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_nodos_semanticos_c085 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_nodo_semantico__presente INTEGER NOT NULL,
    cod_nodo_semantico TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    clave_semantico__presente INTEGER NOT NULL,
    clave_semantico TEXT,
    definicion__presente INTEGER NOT NULL,
    definicion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    tipo_nodo__presente INTEGER NOT NULL,
    tipo_nodo TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_nodo_semantico__presente IN (0,1)),
    CHECK(cod_nodo_semantico__presente=1 OR cod_nodo_semantico IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(clave_semantico__presente IN (0,1)),
    CHECK(clave_semantico__presente=1 OR clave_semantico IS NULL),
    CHECK(definicion__presente IN (0,1)),
    CHECK(definicion__presente=1 OR definicion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(tipo_nodo__presente IN (0,1)),
    CHECK(tipo_nodo__presente=1 OR tipo_nodo IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_nodo_semantico__presente=0 AND cod_usuario_creacion__presente=0 AND cod_version_modelo__presente=0 AND clave_semantico__presente=0 AND definicion__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND nombre__presente=0 AND observacion__presente=0 AND tipo_nodo__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_reglas_expertas_c086 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_regla_anterior__presente INTEGER NOT NULL,
    cod_regla_anterior TEXT,
    cod_regla_experta__presente INTEGER NOT NULL,
    cod_regla_experta TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_usuario_revision__presente INTEGER NOT NULL,
    cod_usuario_revision TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    clave_regla__presente INTEGER NOT NULL,
    clave_regla TEXT,
    descripcion__presente INTEGER NOT NULL,
    descripcion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    estado_revision__presente INTEGER NOT NULL,
    estado_revision TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    fecha_revision__presente INTEGER NOT NULL,
    fecha_revision TEXT,
    justificacion_revision__presente INTEGER NOT NULL,
    justificacion_revision TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    tipo_regla__presente INTEGER NOT NULL,
    tipo_regla TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_regla_anterior__presente IN (0,1)),
    CHECK(cod_regla_anterior__presente=1 OR cod_regla_anterior IS NULL),
    CHECK(cod_regla_experta__presente IN (0,1)),
    CHECK(cod_regla_experta__presente=1 OR cod_regla_experta IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_usuario_revision__presente IN (0,1)),
    CHECK(cod_usuario_revision__presente=1 OR cod_usuario_revision IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(clave_regla__presente IN (0,1)),
    CHECK(clave_regla__presente=1 OR clave_regla IS NULL),
    CHECK(descripcion__presente IN (0,1)),
    CHECK(descripcion__presente=1 OR descripcion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(estado_revision__presente IN (0,1)),
    CHECK(estado_revision__presente=1 OR estado_revision IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(fecha_revision__presente IN (0,1)),
    CHECK(fecha_revision__presente=1 OR fecha_revision IS NULL),
    CHECK(justificacion_revision__presente IN (0,1)),
    CHECK(justificacion_revision__presente=1 OR justificacion_revision IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(tipo_regla__presente IN (0,1)),
    CHECK(tipo_regla__presente=1 OR tipo_regla IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_regla_anterior__presente=0 AND cod_regla_experta__presente=0 AND cod_usuario_creacion__presente=0 AND cod_usuario_revision__presente=0 AND cod_version_modelo__presente=0 AND clave_regla__presente=0 AND descripcion__presente=0 AND estado__presente=0 AND estado_revision__presente=0 AND fecha_creacion__presente=0 AND fecha_revision__presente=0 AND justificacion_revision__presente=0 AND nombre__presente=0 AND observacion__presente=0 AND tipo_regla__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_relaciones_semanti_c087 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_nodo_destino__presente INTEGER NOT NULL,
    cod_nodo_destino TEXT,
    cod_nodo_origen__presente INTEGER NOT NULL,
    cod_nodo_origen TEXT,
    cod_relacion_semantica__presente INTEGER NOT NULL,
    cod_relacion_semantica TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    modalidad_relacion__presente INTEGER NOT NULL,
    modalidad_relacion TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    significado__presente INTEGER NOT NULL,
    significado TEXT,
    tipo_relacion__presente INTEGER NOT NULL,
    tipo_relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_nodo_destino__presente IN (0,1)),
    CHECK(cod_nodo_destino__presente=1 OR cod_nodo_destino IS NULL),
    CHECK(cod_nodo_origen__presente IN (0,1)),
    CHECK(cod_nodo_origen__presente=1 OR cod_nodo_origen IS NULL),
    CHECK(cod_relacion_semantica__presente IN (0,1)),
    CHECK(cod_relacion_semantica__presente=1 OR cod_relacion_semantica IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(modalidad_relacion__presente IN (0,1)),
    CHECK(modalidad_relacion__presente=1 OR modalidad_relacion IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(significado__presente IN (0,1)),
    CHECK(significado__presente=1 OR significado IS NULL),
    CHECK(tipo_relacion__presente IN (0,1)),
    CHECK(tipo_relacion__presente=1 OR tipo_relacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_nodo_destino__presente=0 AND cod_nodo_origen__presente=0 AND cod_relacion_semantica__presente=0 AND cod_usuario_creacion__presente=0 AND cod_version_modelo__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND modalidad_relacion__presente=0 AND observacion__presente=0 AND significado__presente=0 AND tipo_relacion__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_valores_semanticos_c088 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente INTEGER NOT NULL,
    cod_dominio_valores TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_valor_semantico__presente INTEGER NOT NULL,
    cod_valor_semantico TEXT,
    clave_valor__presente INTEGER NOT NULL,
    clave_valor TEXT,
    definicion_semantica__presente INTEGER NOT NULL,
    definicion_semantica TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    estado_aprobacion__presente INTEGER NOT NULL,
    estado_aprobacion TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    nombre__presente INTEGER NOT NULL,
    nombre TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente IN (0,1)),
    CHECK(cod_dominio_valores__presente=1 OR cod_dominio_valores IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_valor_semantico__presente IN (0,1)),
    CHECK(cod_valor_semantico__presente=1 OR cod_valor_semantico IS NULL),
    CHECK(clave_valor__presente IN (0,1)),
    CHECK(clave_valor__presente=1 OR clave_valor IS NULL),
    CHECK(definicion_semantica__presente IN (0,1)),
    CHECK(definicion_semantica__presente=1 OR definicion_semantica IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(estado_aprobacion__presente IN (0,1)),
    CHECK(estado_aprobacion__presente=1 OR estado_aprobacion IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(nombre__presente IN (0,1)),
    CHECK(nombre__presente=1 OR nombre IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_dominio_valores__presente=0 AND cod_usuario_creacion__presente=0 AND cod_valor_semantico__presente=0 AND clave_valor__presente=0 AND definicion_semantica__presente=0 AND estado__presente=0 AND estado_aprobacion__presente=0 AND fecha_creacion__presente=0 AND nombre__presente=0 AND observacion__presente=0))
);

CREATE TABLE ret_conocimiento_tablas_variables_expertas_c089 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente INTEGER NOT NULL,
    cod_dominio_valores TEXT,
    cod_nodo_propietario_primario__presente INTEGER NOT NULL,
    cod_nodo_propietario_primario TEXT,
    cod_nodo_semantico__presente INTEGER NOT NULL,
    cod_nodo_semantico TEXT,
    cod_usuario_creacion__presente INTEGER NOT NULL,
    cod_usuario_creacion TEXT,
    cod_variable_experta__presente INTEGER NOT NULL,
    cod_variable_experta TEXT,
    cod_version_modelo__presente INTEGER NOT NULL,
    cod_version_modelo TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    fecha_creacion__presente INTEGER NOT NULL,
    fecha_creacion TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    papel_inferencial__presente INTEGER NOT NULL,
    papel_inferencial TEXT,
    tipo_semantico__presente INTEGER NOT NULL,
    tipo_semantico TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente IN (0,1)),
    CHECK(cod_dominio_valores__presente=1 OR cod_dominio_valores IS NULL),
    CHECK(cod_nodo_propietario_primario__presente IN (0,1)),
    CHECK(cod_nodo_propietario_primario__presente=1 OR cod_nodo_propietario_primario IS NULL),
    CHECK(cod_nodo_semantico__presente IN (0,1)),
    CHECK(cod_nodo_semantico__presente=1 OR cod_nodo_semantico IS NULL),
    CHECK(cod_usuario_creacion__presente IN (0,1)),
    CHECK(cod_usuario_creacion__presente=1 OR cod_usuario_creacion IS NULL),
    CHECK(cod_variable_experta__presente IN (0,1)),
    CHECK(cod_variable_experta__presente=1 OR cod_variable_experta IS NULL),
    CHECK(cod_version_modelo__presente IN (0,1)),
    CHECK(cod_version_modelo__presente=1 OR cod_version_modelo IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(fecha_creacion__presente IN (0,1)),
    CHECK(fecha_creacion__presente=1 OR fecha_creacion IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(papel_inferencial__presente IN (0,1)),
    CHECK(papel_inferencial__presente=1 OR papel_inferencial IS NULL),
    CHECK(tipo_semantico__presente IN (0,1)),
    CHECK(tipo_semantico__presente=1 OR tipo_semantico IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_dominio_valores__presente=0 AND cod_nodo_propietario_primario__presente=0 AND cod_nodo_semantico__presente=0 AND cod_usuario_creacion__presente=0 AND cod_variable_experta__presente=0 AND cod_version_modelo__presente=0 AND estado__presente=0 AND fecha_creacion__presente=0 AND observacion__presente=0 AND papel_inferencial__presente=0 AND tipo_semantico__presente=0))
);

CREATE TABLE ret_petersen_contenido_membresias_soportes_c090 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__estado VARCHAR(8) NOT NULL CHECK(soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__cantidad BIGINT NOT NULL CHECK(soportes__cantidad>=0),
    valoracion__presente INTEGER NOT NULL,
    valoracion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(soportes__estado='PRESENTE' OR soportes__cantidad=0),
    CHECK(valoracion__presente IN (0,1)),
    CHECK(valoracion__presente=1 OR valoracion IS NULL),
    CHECK(dto__estado='PRESENTE' OR (soportes__estado='AUSENTE' AND valoracion__presente=0))
);

CREATE TABLE ret_contenido_membresias_soportes_soportes_c091 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_petersen_contenido_membresias_soportes_c090(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_petersen_contenido_procedencia_valorac_c092 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_documento_clinico__presente INTEGER NOT NULL,
    cod_documento_clinico TEXT,
    cod_personal__presente INTEGER NOT NULL,
    cod_personal TEXT,
    cod_residente__presente INTEGER NOT NULL,
    cod_residente TEXT,
    cod_usuario_registro__presente INTEGER NOT NULL,
    cod_usuario_registro TEXT,
    cod_valoracion_anterior__presente INTEGER NOT NULL,
    cod_valoracion_anterior TEXT,
    cod_valoracion_petersen__presente INTEGER NOT NULL,
    cod_valoracion_petersen TEXT,
    elemento__presente INTEGER NOT NULL,
    elemento TEXT,
    fecha__presente INTEGER NOT NULL,
    fecha TEXT,
    localizador_fuente__presente INTEGER NOT NULL,
    localizador_fuente TEXT,
    registrado_en__presente INTEGER NOT NULL,
    registrado_en TEXT,
    tipo_registro__presente INTEGER NOT NULL,
    tipo_registro TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_documento_clinico__presente IN (0,1)),
    CHECK(cod_documento_clinico__presente=1 OR cod_documento_clinico IS NULL),
    CHECK(cod_personal__presente IN (0,1)),
    CHECK(cod_personal__presente=1 OR cod_personal IS NULL),
    CHECK(cod_residente__presente IN (0,1)),
    CHECK(cod_residente__presente=1 OR cod_residente IS NULL),
    CHECK(cod_usuario_registro__presente IN (0,1)),
    CHECK(cod_usuario_registro__presente=1 OR cod_usuario_registro IS NULL),
    CHECK(cod_valoracion_anterior__presente IN (0,1)),
    CHECK(cod_valoracion_anterior__presente=1 OR cod_valoracion_anterior IS NULL),
    CHECK(cod_valoracion_petersen__presente IN (0,1)),
    CHECK(cod_valoracion_petersen__presente=1 OR cod_valoracion_petersen IS NULL),
    CHECK(elemento__presente IN (0,1)),
    CHECK(elemento__presente=1 OR elemento IS NULL),
    CHECK(fecha__presente IN (0,1)),
    CHECK(fecha__presente=1 OR fecha IS NULL),
    CHECK(localizador_fuente__presente IN (0,1)),
    CHECK(localizador_fuente__presente=1 OR localizador_fuente IS NULL),
    CHECK(registrado_en__presente IN (0,1)),
    CHECK(registrado_en__presente=1 OR registrado_en IS NULL),
    CHECK(tipo_registro__presente IN (0,1)),
    CHECK(tipo_registro__presente=1 OR tipo_registro IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_documento_clinico__presente=0 AND cod_personal__presente=0 AND cod_residente__presente=0 AND cod_usuario_registro__presente=0 AND cod_valoracion_anterior__presente=0 AND cod_valoracion_petersen__presente=0 AND elemento__presente=0 AND fecha__presente=0 AND localizador_fuente__presente=0 AND registrado_en__presente=0 AND tipo_registro__presente=0))
);

CREATE TABLE ret_contenido_salida_dominios_c093 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    indices_valoraciones__estado VARCHAR(8) NOT NULL CHECK(indices_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    indices_valoraciones__cantidad BIGINT NOT NULL CHECK(indices_valoraciones__cantidad>=0),
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    motivos__cantidad BIGINT NOT NULL CHECK(motivos__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(indices_valoraciones__estado='PRESENTE' OR indices_valoraciones__cantidad=0),
    CHECK(motivos__estado='PRESENTE' OR motivos__cantidad=0),
    CHECK(dto__estado='PRESENTE' OR (estado__presente=0 AND indices_valoraciones__estado='AUSENTE' AND motivos__estado='AUSENTE'))
);

CREATE TABLE ret_salida_dominios_indices_valoraciones_c094 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_salida_dominios_c093(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_salida_dominios_motivos_c095 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_salida_dominios_c093(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_salida_gobierno_bloqueada_por_c096 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_salida_gobierno_estados_derivados_c097 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_salida_gobierno_reglas_c098 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    codigo__presente INTEGER NOT NULL,
    codigo TEXT,
    condiciones__estado VARCHAR(8) NOT NULL CHECK(condiciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    condiciones__cantidad BIGINT NOT NULL CHECK(condiciones__cantidad>=0),
    cumple__presente INTEGER NOT NULL,
    cumple INTEGER,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    estado_revision__presente INTEGER NOT NULL,
    estado_revision TEXT,
    habilitada__presente INTEGER NOT NULL,
    habilitada INTEGER,
    regla__presente INTEGER NOT NULL,
    regla TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(codigo__presente IN (0,1)),
    CHECK(codigo__presente=1 OR codigo IS NULL),
    CHECK(condiciones__estado='PRESENTE' OR condiciones__cantidad=0),
    CHECK(cumple__presente IN (0,1)),
    CHECK(cumple__presente=1 OR cumple IS NULL),
    CHECK(cumple IS NULL OR cumple IN (0,1)),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(estado_revision__presente IN (0,1)),
    CHECK(estado_revision__presente=1 OR estado_revision IS NULL),
    CHECK(habilitada__presente IN (0,1)),
    CHECK(habilitada__presente=1 OR habilitada IS NULL),
    CHECK(habilitada IS NULL OR habilitada IN (0,1)),
    CHECK(regla__presente IN (0,1)),
    CHECK(regla__presente=1 OR regla IS NULL),
    CHECK(dto__estado='PRESENTE' OR (codigo__presente=0 AND condiciones__estado='AUSENTE' AND cumple__presente=0 AND estado__presente=0 AND estado_revision__presente=0 AND habilitada__presente=0 AND regla__presente=0))
);

CREATE TABLE ret_gobierno_reglas_condiciones_c099 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    condicion__presente INTEGER NOT NULL,
    condicion TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    soportes__estado VARCHAR(8) NOT NULL CHECK(soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__cantidad BIGINT NOT NULL CHECK(soportes__cantidad>=0),
    valor__presente INTEGER NOT NULL,
    valor TEXT,
    variable__presente INTEGER NOT NULL,
    variable TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_salida_gobierno_reglas_c098(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(condicion__presente IN (0,1)),
    CHECK(condicion__presente=1 OR condicion IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(soportes__estado='PRESENTE' OR soportes__cantidad=0),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor IS NULL),
    CHECK(variable__presente IN (0,1)),
    CHECK(variable__presente=1 OR variable IS NULL),
    CHECK(dto__estado='PRESENTE' OR (condicion__presente=0 AND estado__presente=0 AND soportes__estado='AUSENTE' AND valor__presente=0 AND variable__presente=0))
);

CREATE TABLE ret_reglas_condiciones_soportes_c100 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_gobierno_reglas_condiciones_c099(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_contenido_salida_impedimentos_c101 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c01_indices_valoraciones_c102 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c01_motivos_c103 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c02_derivado_de_c104 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c02_motivos_c105 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c03_indices_valoraciones_c106 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c03_motivos_c107 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c04_indices_valoraciones_c108 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c04_motivos_c109 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c05_indices_valoraciones_c110 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_requisitos_c05_motivos_c111 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_contenido_salida_valoraciones_excluida_c112 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    comprobaciones__estado VARCHAR(8) NOT NULL CHECK(comprobaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    comprobaciones_factores_pertinentes_revisados__presente INTEGER NOT NULL,
    comprobaciones_factores_pertinentes_revisados INTEGER,
    comprobaciones_impedimento_no_resuelto__presente INTEGER NOT NULL,
    comprobaciones_impedimento_no_resuelto INTEGER,
    comprobaciones_revision_explicita__presente INTEGER NOT NULL,
    comprobaciones_revision_explicita INTEGER,
    dominios_revisados__estado VARCHAR(8) NOT NULL CHECK(dominios_revisados__estado IN ('AUSENTE','NULL','PRESENTE')),
    elemento__presente INTEGER NOT NULL,
    elemento TEXT,
    estado_original__presente INTEGER NOT NULL,
    estado_original TEXT,
    justificacion__presente INTEGER NOT NULL,
    justificacion TEXT,
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    motivos__cantidad BIGINT NOT NULL CHECK(motivos__cantidad>=0),
    origen__estado VARCHAR(8) NOT NULL CHECK(origen__estado IN ('AUSENTE','NULL','PRESENTE')),
    origen_autor__presente INTEGER NOT NULL,
    origen_autor TEXT,
    origen_datos_originales__estado VARCHAR(8) NOT NULL CHECK(origen_datos_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    origen_episodio__presente INTEGER NOT NULL,
    origen_episodio TEXT,
    origen_fecha__presente INTEGER NOT NULL,
    origen_fecha TEXT,
    origen_registro__presente INTEGER NOT NULL,
    origen_registro TEXT,
    origen_residente__presente INTEGER NOT NULL,
    origen_residente TEXT,
    origen_tabla__presente INTEGER NOT NULL,
    origen_tabla TEXT,
    origen_tipo__presente INTEGER NOT NULL,
    origen_tipo TEXT,
    origen_vigente__presente INTEGER NOT NULL,
    origen_vigente INTEGER,
    soportes__estado VARCHAR(8) NOT NULL CHECK(soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__cantidad BIGINT NOT NULL CHECK(soportes__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(comprobaciones_factores_pertinentes_revisados__presente IN (0,1)),
    CHECK(comprobaciones_factores_pertinentes_revisados__presente=1 OR comprobaciones_factores_pertinentes_revisados IS NULL),
    CHECK(comprobaciones_factores_pertinentes_revisados IS NULL OR comprobaciones_factores_pertinentes_revisados IN (0,1)),
    CHECK(comprobaciones_impedimento_no_resuelto__presente IN (0,1)),
    CHECK(comprobaciones_impedimento_no_resuelto__presente=1 OR comprobaciones_impedimento_no_resuelto IS NULL),
    CHECK(comprobaciones_impedimento_no_resuelto IS NULL OR comprobaciones_impedimento_no_resuelto IN (0,1)),
    CHECK(comprobaciones_revision_explicita__presente IN (0,1)),
    CHECK(comprobaciones_revision_explicita__presente=1 OR comprobaciones_revision_explicita IS NULL),
    CHECK(comprobaciones_revision_explicita IS NULL OR comprobaciones_revision_explicita IN (0,1)),
    CHECK(elemento__presente IN (0,1)),
    CHECK(elemento__presente=1 OR elemento IS NULL),
    CHECK(estado_original__presente IN (0,1)),
    CHECK(estado_original__presente=1 OR estado_original IS NULL),
    CHECK(justificacion__presente IN (0,1)),
    CHECK(justificacion__presente=1 OR justificacion IS NULL),
    CHECK(motivos__estado='PRESENTE' OR motivos__cantidad=0),
    CHECK(origen_autor__presente IN (0,1)),
    CHECK(origen_autor__presente=1 OR origen_autor IS NULL),
    CHECK(origen_episodio__presente IN (0,1)),
    CHECK(origen_episodio__presente=1 OR origen_episodio IS NULL),
    CHECK(origen_fecha__presente IN (0,1)),
    CHECK(origen_fecha__presente=1 OR origen_fecha IS NULL),
    CHECK(origen_registro__presente IN (0,1)),
    CHECK(origen_registro__presente=1 OR origen_registro IS NULL),
    CHECK(origen_residente__presente IN (0,1)),
    CHECK(origen_residente__presente=1 OR origen_residente IS NULL),
    CHECK(origen_tabla__presente IN (0,1)),
    CHECK(origen_tabla__presente=1 OR origen_tabla IS NULL),
    CHECK(origen_tipo__presente IN (0,1)),
    CHECK(origen_tipo__presente=1 OR origen_tipo IS NULL),
    CHECK(origen_vigente__presente IN (0,1)),
    CHECK(origen_vigente__presente=1 OR origen_vigente IS NULL),
    CHECK(origen_vigente IS NULL OR origen_vigente IN (0,1)),
    CHECK(soportes__estado='PRESENTE' OR soportes__cantidad=0),
    CHECK(dto__estado='PRESENTE' OR (comprobaciones__estado='AUSENTE' AND dominios_revisados__estado='AUSENTE' AND elemento__presente=0 AND estado_original__presente=0 AND justificacion__presente=0 AND motivos__estado='AUSENTE' AND origen__estado='AUSENTE' AND soportes__estado='AUSENTE')),
    CHECK(comprobaciones__estado='PRESENTE' OR (comprobaciones_factores_pertinentes_revisados__presente=0 AND comprobaciones_impedimento_no_resuelto__presente=0 AND comprobaciones_revision_explicita__presente=0)),
    CHECK(origen__estado='PRESENTE' OR (origen_autor__presente=0 AND origen_datos_originales__estado='AUSENTE' AND origen_episodio__presente=0 AND origen_fecha__presente=0 AND origen_registro__presente=0 AND origen_residente__presente=0 AND origen_tabla__presente=0 AND origen_tipo__presente=0 AND origen_vigente__presente=0))
);

CREATE TABLE ret_salida_valoraciones_excluidas_motivos_c113 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_salida_valoraciones_excluida_c112(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_salida_valoraciones_excluidas_soportes_c114 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    autor__presente INTEGER NOT NULL,
    autor TEXT,
    datos_originales__estado VARCHAR(8) NOT NULL CHECK(datos_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    datos_originales_descripcion__presente INTEGER NOT NULL,
    datos_originales_descripcion TEXT,
    episodio__presente INTEGER NOT NULL,
    episodio TEXT,
    fecha__presente INTEGER NOT NULL,
    fecha TEXT,
    registro__presente INTEGER NOT NULL,
    registro TEXT,
    residente__presente INTEGER NOT NULL,
    residente TEXT,
    tabla__presente INTEGER NOT NULL,
    tabla TEXT,
    tipo__presente INTEGER NOT NULL,
    tipo TEXT,
    vigente__presente INTEGER NOT NULL,
    vigente INTEGER,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_salida_valoraciones_excluida_c112(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(autor__presente IN (0,1)),
    CHECK(autor__presente=1 OR autor IS NULL),
    CHECK(datos_originales_descripcion__presente IN (0,1)),
    CHECK(datos_originales_descripcion__presente=1 OR datos_originales_descripcion IS NULL),
    CHECK(episodio__presente IN (0,1)),
    CHECK(episodio__presente=1 OR episodio IS NULL),
    CHECK(fecha__presente IN (0,1)),
    CHECK(fecha__presente=1 OR fecha IS NULL),
    CHECK(registro__presente IN (0,1)),
    CHECK(registro__presente=1 OR registro IS NULL),
    CHECK(residente__presente IN (0,1)),
    CHECK(residente__presente=1 OR residente IS NULL),
    CHECK(tabla__presente IN (0,1)),
    CHECK(tabla__presente=1 OR tabla IS NULL),
    CHECK(tipo__presente IN (0,1)),
    CHECK(tipo__presente=1 OR tipo IS NULL),
    CHECK(vigente__presente IN (0,1)),
    CHECK(vigente__presente=1 OR vigente IS NULL),
    CHECK(vigente IS NULL OR vigente IN (0,1)),
    CHECK(dto__estado='PRESENTE' OR (autor__presente=0 AND datos_originales__estado='AUSENTE' AND episodio__presente=0 AND fecha__presente=0 AND registro__presente=0 AND residente__presente=0 AND tabla__presente=0 AND tipo__presente=0 AND vigente__presente=0)),
    CHECK(datos_originales__estado='PRESENTE' OR (datos_originales_descripcion__presente=0))
);

CREATE TABLE ret_petersen_contenido_valoraciones_c115 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    comprobaciones__estado VARCHAR(8) NOT NULL CHECK(comprobaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    comprobaciones_alcance_dominio__presente INTEGER NOT NULL,
    comprobaciones_alcance_dominio INTEGER,
    comprobaciones_atribucion_revisada__presente INTEGER NOT NULL,
    comprobaciones_atribucion_revisada INTEGER,
    comprobaciones_cambio_respecto_previo__presente INTEGER NOT NULL,
    comprobaciones_cambio_respecto_previo INTEGER,
    comprobaciones_condiciones_revisadas__presente INTEGER NOT NULL,
    comprobaciones_condiciones_revisadas INTEGER,
    comprobaciones_contexto_interpretado__presente INTEGER NOT NULL,
    comprobaciones_contexto_interpretado INTEGER,
    comprobaciones_corroboracion_independiente__presente INTEGER NOT NULL,
    comprobaciones_corroboracion_independiente INTEGER,
    comprobaciones_exclusiones_revisadas__presente INTEGER NOT NULL,
    comprobaciones_exclusiones_revisadas INTEGER,
    comprobaciones_factores_pertinentes_revisados__presente INTEGER NOT NULL,
    comprobaciones_factores_pertinentes_revisados INTEGER,
    comprobaciones_impedimento_no_resuelto__presente INTEGER NOT NULL,
    comprobaciones_impedimento_no_resuelto INTEGER,
    comprobaciones_oportunidad_adecuada__presente INTEGER NOT NULL,
    comprobaciones_oportunidad_adecuada INTEGER,
    comprobaciones_revision_explicita__presente INTEGER NOT NULL,
    comprobaciones_revision_explicita INTEGER,
    comprobaciones_revision_integrada__presente INTEGER NOT NULL,
    comprobaciones_revision_integrada INTEGER,
    comprobaciones_suficiencia_conjunto__presente INTEGER NOT NULL,
    comprobaciones_suficiencia_conjunto INTEGER,
    dominios_revisados__estado VARCHAR(8) NOT NULL CHECK(dominios_revisados__estado IN ('AUSENTE','NULL','PRESENTE')),
    dominios_revisados__cantidad BIGINT NOT NULL CHECK(dominios_revisados__cantidad>=0),
    elemento__presente INTEGER NOT NULL,
    elemento TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    justificacion__presente INTEGER NOT NULL,
    justificacion TEXT,
    origen__estado VARCHAR(8) NOT NULL CHECK(origen__estado IN ('AUSENTE','NULL','PRESENTE')),
    origen_autor__presente INTEGER NOT NULL,
    origen_autor TEXT,
    origen_datos_originales__estado VARCHAR(8) NOT NULL CHECK(origen_datos_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    origen_episodio__presente INTEGER NOT NULL,
    origen_episodio TEXT,
    origen_fecha__presente INTEGER NOT NULL,
    origen_fecha TEXT,
    origen_registro__presente INTEGER NOT NULL,
    origen_registro TEXT,
    origen_residente__presente INTEGER NOT NULL,
    origen_residente TEXT,
    origen_tabla__presente INTEGER NOT NULL,
    origen_tabla TEXT,
    origen_tipo__presente INTEGER NOT NULL,
    origen_tipo TEXT,
    origen_vigente__presente INTEGER NOT NULL,
    origen_vigente INTEGER,
    soportes__estado VARCHAR(8) NOT NULL CHECK(soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__cantidad BIGINT NOT NULL CHECK(soportes__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(comprobaciones_alcance_dominio__presente IN (0,1)),
    CHECK(comprobaciones_alcance_dominio__presente=1 OR comprobaciones_alcance_dominio IS NULL),
    CHECK(comprobaciones_alcance_dominio IS NULL OR comprobaciones_alcance_dominio IN (0,1)),
    CHECK(comprobaciones_atribucion_revisada__presente IN (0,1)),
    CHECK(comprobaciones_atribucion_revisada__presente=1 OR comprobaciones_atribucion_revisada IS NULL),
    CHECK(comprobaciones_atribucion_revisada IS NULL OR comprobaciones_atribucion_revisada IN (0,1)),
    CHECK(comprobaciones_cambio_respecto_previo__presente IN (0,1)),
    CHECK(comprobaciones_cambio_respecto_previo__presente=1 OR comprobaciones_cambio_respecto_previo IS NULL),
    CHECK(comprobaciones_cambio_respecto_previo IS NULL OR comprobaciones_cambio_respecto_previo IN (0,1)),
    CHECK(comprobaciones_condiciones_revisadas__presente IN (0,1)),
    CHECK(comprobaciones_condiciones_revisadas__presente=1 OR comprobaciones_condiciones_revisadas IS NULL),
    CHECK(comprobaciones_condiciones_revisadas IS NULL OR comprobaciones_condiciones_revisadas IN (0,1)),
    CHECK(comprobaciones_contexto_interpretado__presente IN (0,1)),
    CHECK(comprobaciones_contexto_interpretado__presente=1 OR comprobaciones_contexto_interpretado IS NULL),
    CHECK(comprobaciones_contexto_interpretado IS NULL OR comprobaciones_contexto_interpretado IN (0,1)),
    CHECK(comprobaciones_corroboracion_independiente__presente IN (0,1)),
    CHECK(comprobaciones_corroboracion_independiente__presente=1 OR comprobaciones_corroboracion_independiente IS NULL),
    CHECK(comprobaciones_corroboracion_independiente IS NULL OR comprobaciones_corroboracion_independiente IN (0,1)),
    CHECK(comprobaciones_exclusiones_revisadas__presente IN (0,1)),
    CHECK(comprobaciones_exclusiones_revisadas__presente=1 OR comprobaciones_exclusiones_revisadas IS NULL),
    CHECK(comprobaciones_exclusiones_revisadas IS NULL OR comprobaciones_exclusiones_revisadas IN (0,1)),
    CHECK(comprobaciones_factores_pertinentes_revisados__presente IN (0,1)),
    CHECK(comprobaciones_factores_pertinentes_revisados__presente=1 OR comprobaciones_factores_pertinentes_revisados IS NULL),
    CHECK(comprobaciones_factores_pertinentes_revisados IS NULL OR comprobaciones_factores_pertinentes_revisados IN (0,1)),
    CHECK(comprobaciones_impedimento_no_resuelto__presente IN (0,1)),
    CHECK(comprobaciones_impedimento_no_resuelto__presente=1 OR comprobaciones_impedimento_no_resuelto IS NULL),
    CHECK(comprobaciones_impedimento_no_resuelto IS NULL OR comprobaciones_impedimento_no_resuelto IN (0,1)),
    CHECK(comprobaciones_oportunidad_adecuada__presente IN (0,1)),
    CHECK(comprobaciones_oportunidad_adecuada__presente=1 OR comprobaciones_oportunidad_adecuada IS NULL),
    CHECK(comprobaciones_oportunidad_adecuada IS NULL OR comprobaciones_oportunidad_adecuada IN (0,1)),
    CHECK(comprobaciones_revision_explicita__presente IN (0,1)),
    CHECK(comprobaciones_revision_explicita__presente=1 OR comprobaciones_revision_explicita IS NULL),
    CHECK(comprobaciones_revision_explicita IS NULL OR comprobaciones_revision_explicita IN (0,1)),
    CHECK(comprobaciones_revision_integrada__presente IN (0,1)),
    CHECK(comprobaciones_revision_integrada__presente=1 OR comprobaciones_revision_integrada IS NULL),
    CHECK(comprobaciones_revision_integrada IS NULL OR comprobaciones_revision_integrada IN (0,1)),
    CHECK(comprobaciones_suficiencia_conjunto__presente IN (0,1)),
    CHECK(comprobaciones_suficiencia_conjunto__presente=1 OR comprobaciones_suficiencia_conjunto IS NULL),
    CHECK(comprobaciones_suficiencia_conjunto IS NULL OR comprobaciones_suficiencia_conjunto IN (0,1)),
    CHECK(dominios_revisados__estado='PRESENTE' OR dominios_revisados__cantidad=0),
    CHECK(elemento__presente IN (0,1)),
    CHECK(elemento__presente=1 OR elemento IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(justificacion__presente IN (0,1)),
    CHECK(justificacion__presente=1 OR justificacion IS NULL),
    CHECK(origen_autor__presente IN (0,1)),
    CHECK(origen_autor__presente=1 OR origen_autor IS NULL),
    CHECK(origen_episodio__presente IN (0,1)),
    CHECK(origen_episodio__presente=1 OR origen_episodio IS NULL),
    CHECK(origen_fecha__presente IN (0,1)),
    CHECK(origen_fecha__presente=1 OR origen_fecha IS NULL),
    CHECK(origen_registro__presente IN (0,1)),
    CHECK(origen_registro__presente=1 OR origen_registro IS NULL),
    CHECK(origen_residente__presente IN (0,1)),
    CHECK(origen_residente__presente=1 OR origen_residente IS NULL),
    CHECK(origen_tabla__presente IN (0,1)),
    CHECK(origen_tabla__presente=1 OR origen_tabla IS NULL),
    CHECK(origen_tipo__presente IN (0,1)),
    CHECK(origen_tipo__presente=1 OR origen_tipo IS NULL),
    CHECK(origen_vigente__presente IN (0,1)),
    CHECK(origen_vigente__presente=1 OR origen_vigente IS NULL),
    CHECK(origen_vigente IS NULL OR origen_vigente IN (0,1)),
    CHECK(soportes__estado='PRESENTE' OR soportes__cantidad=0),
    CHECK(dto__estado='PRESENTE' OR (comprobaciones__estado='AUSENTE' AND dominios_revisados__estado='AUSENTE' AND elemento__presente=0 AND estado__presente=0 AND justificacion__presente=0 AND origen__estado='AUSENTE' AND soportes__estado='AUSENTE')),
    CHECK(comprobaciones__estado='PRESENTE' OR (comprobaciones_alcance_dominio__presente=0 AND comprobaciones_atribucion_revisada__presente=0 AND comprobaciones_cambio_respecto_previo__presente=0 AND comprobaciones_condiciones_revisadas__presente=0 AND comprobaciones_contexto_interpretado__presente=0 AND comprobaciones_corroboracion_independiente__presente=0 AND comprobaciones_exclusiones_revisadas__presente=0 AND comprobaciones_factores_pertinentes_revisados__presente=0 AND comprobaciones_impedimento_no_resuelto__presente=0 AND comprobaciones_oportunidad_adecuada__presente=0 AND comprobaciones_revision_explicita__presente=0 AND comprobaciones_revision_integrada__presente=0 AND comprobaciones_suficiencia_conjunto__presente=0)),
    CHECK(origen__estado='PRESENTE' OR (origen_autor__presente=0 AND origen_datos_originales__estado='AUSENTE' AND origen_episodio__presente=0 AND origen_fecha__presente=0 AND origen_registro__presente=0 AND origen_residente__presente=0 AND origen_tabla__presente=0 AND origen_tipo__presente=0 AND origen_vigente__presente=0))
);

CREATE TABLE ret_contenido_valoraciones_dominios_revisa_c116 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_petersen_contenido_valoraciones_c115(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_contenido_valoraciones_soportes_c117 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    autor__presente INTEGER NOT NULL,
    autor TEXT,
    datos_originales__estado VARCHAR(8) NOT NULL CHECK(datos_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    datos_originales_actividad__presente INTEGER NOT NULL,
    datos_originales_actividad TEXT,
    datos_originales_captura_original__estado VARCHAR(8) NOT NULL CHECK(datos_originales_captura_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_apl_act__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_apl_act__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_apl_act_cla__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cla TEXT,
    dat_ori_cap_ori_apl_act_cod_apl__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cod_apl TEXT,
    dat_ori_cap_ori_apl_act_cod_apl_ant__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cod_apl_ant TEXT,
    dat_ori_cap_ori_apl_act_cod_ate__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cod_ate TEXT,
    dat_ori_cap_ori_apl_act_cod_doc_cli__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cod_doc_cli TEXT,
    dat_ori_cap_ori_apl_act_cod_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cod_ins TEXT,
    dat_ori_cap_ori_apl_act_cod_per__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cod_per TEXT,
    dat_ori_cap_ori_apl_act_cod_res__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cod_res TEXT,
    dat_ori_cap_ori_apl_act_cod_usu_reg__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_cod_usu_reg TEXT,
    dat_ori_cap_ori_apl_act_est__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_est TEXT,
    dat_ori_cap_ori_apl_act_fec__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_fec TEXT,
    dat_ori_cap_ori_apl_act_int__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_int TEXT,
    dat_ori_cap_ori_apl_act_loc_fue__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_loc_fue TEXT,
    dat_ori_cap_ori_apl_act_obs__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_obs TEXT,
    dat_ori_cap_ori_apl_act_pun_max__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_pun_max TEXT,
    dat_ori_cap_ori_apl_act_pun_tot__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_pun_tot TEXT,
    dat_ori_cap_ori_apl_act_reg_en__presente INTEGER NOT NULL,
    dat_ori_cap_ori_apl_act_reg_en TEXT,
    dat_ori_cap_ori_ate_ori__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ate_ori__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ate_ori_cod_ate__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ate_ori_cod_ate TEXT,
    dat_ori_cap_ori_ate_ori_cod_res__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ate_ori_cod_res TEXT,
    datos_originales_captura_original_autor__presente INTEGER NOT NULL,
    datos_originales_captura_original_autor TEXT,
    datos_originales_captura_original_cod_aplicacion__presente INTEGER NOT NULL,
    datos_originales_captura_original_cod_aplicacion TEXT,
    datos_originales_captura_original_cod_instrumento__presente INTEGER NOT NULL,
    datos_originales_captura_original_cod_instrumento TEXT,
    datos_originales_captura_original_cod_personal__presente INTEGER NOT NULL,
    datos_originales_captura_original_cod_personal TEXT,
    datos_originales_captura_original_cod_residente__presente INTEGER NOT NULL,
    datos_originales_captura_original_cod_residente TEXT,
    dat_ori_cap_ori_cla_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_cla_ins TEXT,
    dat_ori_cap_ori_doc_ori__presente INTEGER NOT NULL,
    dat_ori_cap_ori_doc_ori TEXT,
    dat_ori_cap_ori_eda_en_apl__presente INTEGER NOT NULL,
    dat_ori_cap_ori_eda_en_apl BIGINT,
    datos_originales_captura_original_estado_original__presente INTEGER NOT NULL,
    datos_originales_captura_original_estado_original TEXT,
    datos_originales_captura_original_extraccion__estado VARCHAR(8) NOT NULL CHECK(datos_originales_captura_original_extraccion__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_apl_ger__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_apl_ger__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_apl_ger_alc_eta_com__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_alc_eta_com INTEGER,
    dat_ori_cap_ori_ext_apl_ger_aut_int_cli__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_aut_int_cli INTEGER,
    dat_ori_cap_ori_ext_apl_ger_eda_en_apl__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_eda_en_apl BIGINT,
    dat_ori_cap_ori_ext_apl_ger_esc_ori__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_esc_ori BIGINT,
    dat_ori_cap_ori_ext_apl_ger_fue__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_fue TEXT,
    dat_ori_cap_ori_ext_apl_ger_idi_dec__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_idi_dec TEXT,
    dat_ori_cap_ori_ext_apl_ger_mot__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_apl_ger_mot__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj INTEGER,
    dat_ori_cap_ori_ext_apl_ger_pob_obj__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_pob_obj TEXT,
    dat_ori_cap_ori_ext_apl_ger_ran_eda_ref__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_apl_ger_ran_eda_ref__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_max__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_max BIGINT,
    dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_min__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_min BIGINT,
    dat_ori_cap_ori_ext_apl_ger_req_ver__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_apl_ger_req_ver__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_apl_ger_req_ver__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_ext_apl_ger_req_ver__cantidad>=0),
    dat_ori_cap_ori_ext_cap__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_cap__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_cap_aci_cla_sem__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_aci_cla_sem BIGINT,
    dat_ori_cap_ori_ext_cap_aci_esp__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_aci_esp BIGINT,
    dat_ori_cap_ori_ext_cap_adm_est__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_adm_est INTEGER,
    dat_ori_cap_ori_ext_cap_aud_com__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_aud_com INTEGER,
    dat_ori_cap_ori_ext_cap_con_sen__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_con_sen INTEGER,
    dat_ori_cap_ori_ext_cap_dem_min__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_dem_min REAL,
    dat_ori_cap_ori_ext_cap_dem_min__cero_negativo INTEGER NOT NULL DEFAULT 0,
    dat_ori_cap_ori_ext_cap_eda_ani__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_eda_ani BIGINT,
    dat_ori_cap_ori_ext_cap_edi_pro__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_edi_pro TEXT,
    dat_ori_cap_ori_ext_cap_ele_rev__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_ele_rev BIGINT,
    dat_ori_cap_ori_ext_cap_ens_1__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_ens_1 BIGINT,
    dat_ori_cap_ori_ext_cap_ens_2__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_ens_2 BIGINT,
    dat_ori_cap_ori_ext_cap_ens_3__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_ens_3 BIGINT,
    dat_ori_cap_ori_ext_cap_ens__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_cap_ens__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_cap_ens__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_ext_cap_ens__cantidad>=0),
    dat_ori_cap_ori_ext_cap_err_obs__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_err_obs BIGINT,
    dat_ori_cap_ori_ext_cap_esc_ani__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_esc_ani BIGINT,
    dat_ori_cap_ori_ext_cap_esp_nat__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_esp_nat INTEGER,
    dat_ori_cap_ori_ext_cap_fin__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_fin INTEGER,
    dat_ori_cap_ori_ext_cap_for__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_for BIGINT,
    dat_ori_cap_ori_ext_cap_for_l_aci__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_for_l_aci BIGINT,
    dat_ori_cap_ori_ext_cap_for_n_aci__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_for_n_aci BIGINT,
    dat_ori_cap_ori_ext_cap_idi__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_idi TEXT,
    dat_ori_cap_ori_ext_cap_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_ins TEXT,
    dat_ori_cap_ori_ext_cap_lec__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_lec INTEGER,
    dat_ori_cap_ori_ext_cap_nac_bol__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_nac_bol INTEGER,
    dat_ori_cap_ori_ext_cap_pro__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_pro TEXT,
    dat_ori_cap_ori_ext_cap_pun_cop__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_pun_cop REAL,
    dat_ori_cap_ori_ext_cap_pun_cop__cero_negativo INTEGER NOT NULL DEFAULT 0,
    dat_ori_cap_ori_ext_cap_rec_dif__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_rec_dif BIGINT,
    dat_ori_cap_ori_ext_cap_res_bol__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_res_bol INTEGER,
    dat_ori_cap_ori_ext_cap_seg_par_b__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_cap_seg_par_b REAL,
    dat_ori_cap_ori_ext_cap_seg_par_b__cero_negativo INTEGER NOT NULL DEFAULT 0,
    dat_ori_cap_ori_ext_fue_ent__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_fue_ent__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_fue_ent__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_ext_fue_ent__cantidad>=0),
    dat_ori_cap_ori_ext_ide_dec__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_ide_dec__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_ide_dec_edi_pro__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_ide_dec_edi_pro TEXT,
    dat_ori_cap_ori_ext_ide_dec_for__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_ide_dec_for BIGINT,
    dat_ori_cap_ori_ext_ide_dec_idi__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_ide_dec_idi TEXT,
    dat_ori_cap_ori_ext_ide_dec_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_ide_dec_ins TEXT,
    dat_ori_cap_ori_ext_ide_req_met__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_ide_req_met__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_ide_req_met_edi_pro__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_ide_req_met_edi_pro TEXT,
    dat_ori_cap_ori_ext_ide_req_met_for__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_ide_req_met_for BIGINT,
    dat_ori_cap_ori_ext_ide_req_met_idi__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_ide_req_met_idi TEXT,
    dat_ori_cap_ori_ext_ide_req_met_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_ide_req_met_ins TEXT,
    dat_ori_cap_ori_ext_met__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_met TEXT,
    dat_ori_cap_ori_ext_mot_cap__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_mot_cap__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_nor__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_nor__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_nor_bru__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_nor_bru__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_nor_bru_aci_cla_sem__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_aci_cla_sem BIGINT,
    dat_ori_cap_ori_ext_nor_bru_aci_esp__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_aci_esp BIGINT,
    dat_ori_cap_ori_ext_nor_bru_adm_est__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_adm_est INTEGER,
    dat_ori_cap_ori_ext_nor_bru_aud_com__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_aud_com INTEGER,
    dat_ori_cap_ori_ext_nor_bru_con_sen__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_con_sen INTEGER,
    dat_ori_cap_ori_ext_nor_bru_dem_min__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_dem_min REAL,
    dat_ori_cap_ori_ext_nor_bru_dem_min__cero_negativo INTEGER NOT NULL DEFAULT 0,
    dat_ori_cap_ori_ext_nor_bru_ele_rev__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_ele_rev BIGINT,
    dat_ori_cap_ori_ext_nor_bru_ens__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_nor_bru_ens__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_nor_bru_ens__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_ext_nor_bru_ens__cantidad>=0),
    dat_ori_cap_ori_ext_nor_bru_err_obs__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_err_obs BIGINT,
    dat_ori_cap_ori_ext_nor_bru_esc_ani__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_esc_ani BIGINT,
    dat_ori_cap_ori_ext_nor_bru_esp_nat__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_esp_nat INTEGER,
    dat_ori_cap_ori_ext_nor_bru_fin__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_fin INTEGER,
    dat_ori_cap_ori_ext_nor_bru_for_l_aci__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_for_l_aci BIGINT,
    dat_ori_cap_ori_ext_nor_bru_for_n_aci__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_for_n_aci BIGINT,
    dat_ori_cap_ori_ext_nor_bru_lec__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_lec INTEGER,
    dat_ori_cap_ori_ext_nor_bru_nac_bol__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_nac_bol INTEGER,
    dat_ori_cap_ori_ext_nor_bru_pun_cop__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_pun_cop REAL,
    dat_ori_cap_ori_ext_nor_bru_pun_cop__cero_negativo INTEGER NOT NULL DEFAULT 0,
    dat_ori_cap_ori_ext_nor_bru_rec_dif__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_rec_dif BIGINT,
    dat_ori_cap_ori_ext_nor_bru_res_bol__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_res_bol INTEGER,
    dat_ori_cap_ori_ext_nor_bru_seg_par_b__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_bru_seg_par_b REAL,
    dat_ori_cap_ori_ext_nor_bru_seg_par_b__cero_negativo INTEGER NOT NULL DEFAULT 0,
    dat_ori_cap_ori_ext_nor_com__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_nor_com__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_nor_com__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_ext_nor_com__cantidad>=0),
    dat_ori_cap_ori_ext_nor_con_apl__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ext_nor_con_apl__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ext_nor_con_apl_aci_cla_sem__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_aci_cla_sem BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_aci_esp__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_aci_esp BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_adm_est__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_adm_est INTEGER,
    dat_ori_cap_ori_ext_nor_con_apl_aud_com__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_aud_com INTEGER,
    dat_ori_cap_ori_ext_nor_con_apl_con_sen__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_con_sen INTEGER,
    dat_ori_cap_ori_ext_nor_con_apl_eda_ani__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_eda_ani BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_edi_pro__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_edi_pro TEXT,
    dat_ori_cap_ori_ext_nor_con_apl_ele_rev__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_ele_rev BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_err_obs__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_err_obs BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_esc_ani__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_esc_ani BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_esp_nat__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_esp_nat INTEGER,
    dat_ori_cap_ori_ext_nor_con_apl_fin__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_fin INTEGER,
    dat_ori_cap_ori_ext_nor_con_apl_for__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_for BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_for_l_aci__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_for_l_aci BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_for_n_aci__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_for_n_aci BIGINT,
    dat_ori_cap_ori_ext_nor_con_apl_idi__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_idi TEXT,
    dat_ori_cap_ori_ext_nor_con_apl_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_ins TEXT,
    dat_ori_cap_ori_ext_nor_con_apl_lec__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_lec INTEGER,
    dat_ori_cap_ori_ext_nor_con_apl_nac_bol__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_nac_bol INTEGER,
    dat_ori_cap_ori_ext_nor_con_apl_pro__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_pro TEXT,
    dat_ori_cap_ori_ext_nor_con_apl_pun_cop__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_pun_cop REAL,
    dat_ori_cap_ori_ext_nor_con_apl_pun_cop__cero_negativo INTEGER NOT NULL DEFAULT 0,
    dat_ori_cap_ori_ext_nor_con_apl_res_bol__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_res_bol INTEGER,
    dat_ori_cap_ori_ext_nor_con_apl_seg_par_b__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_con_apl_seg_par_b REAL,
    dat_ori_cap_ori_ext_nor_con_apl_seg_par_b__cero_negativo INTEGER NOT NULL DEFAULT 0,
    dat_ori_cap_ori_ext_nor_dia__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_dia TEXT,
    dat_ori_cap_ori_ext_nor_lim__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_lim TEXT,
    dat_ori_cap_ori_ext_nor_loc__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_loc TEXT,
    dat_ori_cap_ori_ext_nor_met__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_met TEXT,
    dat_ori_cap_ori_ext_nor_ref__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_ref TEXT,
    dat_ori_cap_ori_ext_nor_rev_pro__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_nor_rev_pro TEXT,
    dat_ori_cap_ori_ext_per__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ext_per TEXT,
    datos_originales_captura_original_fecha__presente INTEGER NOT NULL,
    datos_originales_captura_original_fecha TEXT,
    datos_originales_captura_original_fuente_edad__estado VARCHAR(8) NOT NULL CHECK(datos_originales_captura_original_fuente_edad__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_fue_eda_cam__presente INTEGER NOT NULL,
    dat_ori_cap_ori_fue_eda_cam TEXT,
    dat_ori_cap_ori_fue_eda_fec_apl__presente INTEGER NOT NULL,
    dat_ori_cap_ori_fue_eda_fec_apl TEXT,
    dat_ori_cap_ori_fue_eda_fec_nac__presente INTEGER NOT NULL,
    dat_ori_cap_ori_fue_eda_fec_nac TEXT,
    dat_ori_cap_ori_fue_eda_reg__presente INTEGER NOT NULL,
    dat_ori_cap_ori_fue_eda_reg TEXT,
    dat_ori_cap_ori_fue_eda_tab__presente INTEGER NOT NULL,
    dat_ori_cap_ori_fue_eda_tab TEXT,
    dat_ori_cap_ori_ins_act__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_ins_act__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_ins_act_cod_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ins_act_cod_ins TEXT,
    dat_ori_cap_ori_ins_act_cod__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ins_act_cod TEXT,
    dat_ori_cap_ori_ins_act_des__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ins_act_des TEXT,
    dat_ori_cap_ori_ins_act_est__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ins_act_est TEXT,
    dat_ori_cap_ori_ins_act_nom__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ins_act_nom TEXT,
    dat_ori_cap_ori_ins_act_pun_max__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ins_act_pun_max TEXT,
    dat_ori_cap_ori_ins_act_tip__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ins_act_tip TEXT,
    dat_ori_cap_ori_ins_act_ver__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ins_act_ver TEXT,
    datos_originales_captura_original_motivos__estado VARCHAR(8) NOT NULL CHECK(datos_originales_captura_original_motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_nom_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_nom_ins TEXT,
    dat_ori_cap_ori_per_cap_ori__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_per_cap_ori__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_per_cap_ori_cod_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_cod_ins TEXT,
    dat_ori_cap_ori_per_cap_ori_cla_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_cla_ins TEXT,
    dat_ori_cap_ori_per_cap_ori_id__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_id TEXT,
    dat_ori_cap_ori_per_cap_ori_ide_ins__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_per_cap_ori_ide_ins_edi_pro__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_ide_ins_edi_pro TEXT,
    dat_ori_cap_ori_per_cap_ori_ide_ins_for__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_ide_ins_for BIGINT,
    dat_ori_cap_ori_per_cap_ori_ide_ins_idi__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_ide_ins_idi TEXT,
    dat_ori_cap_ori_per_cap_ori_ide_ins_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_ide_ins_ins TEXT,
    dat_ori_cap_ori_per_cap_ori_met__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_met TEXT,
    dat_ori_cap_ori_per_cap_ori_sel__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_per_cap_ori_sel__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_per_cap_ori_sel__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_per_cap_ori_sel__cantidad>=0),
    dat_ori_cap_ori_per_cap_ori_ver_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_per_cap_ori_ver_ins TEXT,
    dat_ori_cap_ori_pre_dec_ori__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_pre_dec_ori__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_pre_dec_ori__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_pre_dec_ori__cantidad>=0),
    datos_originales_captura_original_procedencia__estado VARCHAR(8) NOT NULL CHECK(datos_originales_captura_original_procedencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_pro_cod_apl_ant__presente INTEGER NOT NULL,
    dat_ori_cap_ori_pro_cod_apl_ant TEXT,
    dat_ori_cap_ori_pro_cod_doc_cli__presente INTEGER NOT NULL,
    dat_ori_cap_ori_pro_cod_doc_cli TEXT,
    dat_ori_cap_ori_pro_cod_usu_reg__presente INTEGER NOT NULL,
    dat_ori_cap_ori_pro_cod_usu_reg TEXT,
    dat_ori_cap_ori_pro_loc_fue__presente INTEGER NOT NULL,
    dat_ori_cap_ori_pro_loc_fue TEXT,
    dat_ori_cap_ori_pro_reg_en__presente INTEGER NOT NULL,
    dat_ori_cap_ori_pro_reg_en TEXT,
    dat_ori_cap_ori_res_ori__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_res_ori__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_res_ori__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_res_ori__cantidad>=0),
    dat_ori_cap_ori_ver_ins__presente INTEGER NOT NULL,
    dat_ori_cap_ori_ver_ins TEXT,
    dat_ori_cap_ori_vin_pre_act__estado VARCHAR(8) NOT NULL CHECK(dat_ori_cap_ori_vin_pre_act__estado IN ('AUSENTE','NULL','PRESENTE')),
    dat_ori_cap_ori_vin_pre_act__cantidad BIGINT NOT NULL CHECK(dat_ori_cap_ori_vin_pre_act__cantidad>=0),
    datos_originales_causa_ayuda__presente INTEGER NOT NULL,
    datos_originales_causa_ayuda TEXT,
    datos_originales_descripcion__presente INTEGER NOT NULL,
    datos_originales_descripcion TEXT,
    datos_originales_desempeno__presente INTEGER NOT NULL,
    datos_originales_desempeno TEXT,
    datos_originales_dominio__presente INTEGER NOT NULL,
    datos_originales_dominio TEXT,
    datos_originales_fuente__presente INTEGER NOT NULL,
    datos_originales_fuente TEXT,
    datos_originales_nivel_previo__presente INTEGER NOT NULL,
    datos_originales_nivel_previo TEXT,
    datos_originales_oportunidad__presente INTEGER NOT NULL,
    datos_originales_oportunidad TEXT,
    datos_originales_tipo_ayuda__presente INTEGER NOT NULL,
    datos_originales_tipo_ayuda TEXT,
    episodio__presente INTEGER NOT NULL,
    episodio TEXT,
    fecha__presente INTEGER NOT NULL,
    fecha TEXT,
    registro__presente INTEGER NOT NULL,
    registro TEXT,
    residente__presente INTEGER NOT NULL,
    residente TEXT,
    tabla__presente INTEGER NOT NULL,
    tabla TEXT,
    tipo__presente INTEGER NOT NULL,
    tipo TEXT,
    vigente__presente INTEGER NOT NULL,
    vigente INTEGER,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_petersen_contenido_valoraciones_c115(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(autor__presente IN (0,1)),
    CHECK(autor__presente=1 OR autor IS NULL),
    CHECK(datos_originales_actividad__presente IN (0,1)),
    CHECK(datos_originales_actividad__presente=1 OR datos_originales_actividad IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cla__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cla__presente=1 OR dat_ori_cap_ori_apl_act_cla IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cod_apl__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cod_apl__presente=1 OR dat_ori_cap_ori_apl_act_cod_apl IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cod_apl_ant__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cod_apl_ant__presente=1 OR dat_ori_cap_ori_apl_act_cod_apl_ant IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cod_ate__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cod_ate__presente=1 OR dat_ori_cap_ori_apl_act_cod_ate IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cod_doc_cli__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cod_doc_cli__presente=1 OR dat_ori_cap_ori_apl_act_cod_doc_cli IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cod_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cod_ins__presente=1 OR dat_ori_cap_ori_apl_act_cod_ins IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cod_per__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cod_per__presente=1 OR dat_ori_cap_ori_apl_act_cod_per IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cod_res__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cod_res__presente=1 OR dat_ori_cap_ori_apl_act_cod_res IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_cod_usu_reg__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_cod_usu_reg__presente=1 OR dat_ori_cap_ori_apl_act_cod_usu_reg IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_est__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_est__presente=1 OR dat_ori_cap_ori_apl_act_est IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_fec__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_fec__presente=1 OR dat_ori_cap_ori_apl_act_fec IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_int__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_int__presente=1 OR dat_ori_cap_ori_apl_act_int IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_loc_fue__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_loc_fue__presente=1 OR dat_ori_cap_ori_apl_act_loc_fue IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_obs__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_obs__presente=1 OR dat_ori_cap_ori_apl_act_obs IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_pun_max__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_pun_max__presente=1 OR dat_ori_cap_ori_apl_act_pun_max IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_pun_tot__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_pun_tot__presente=1 OR dat_ori_cap_ori_apl_act_pun_tot IS NULL),
    CHECK(dat_ori_cap_ori_apl_act_reg_en__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_apl_act_reg_en__presente=1 OR dat_ori_cap_ori_apl_act_reg_en IS NULL),
    CHECK(dat_ori_cap_ori_ate_ori_cod_ate__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ate_ori_cod_ate__presente=1 OR dat_ori_cap_ori_ate_ori_cod_ate IS NULL),
    CHECK(dat_ori_cap_ori_ate_ori_cod_res__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ate_ori_cod_res__presente=1 OR dat_ori_cap_ori_ate_ori_cod_res IS NULL),
    CHECK(datos_originales_captura_original_autor__presente IN (0,1)),
    CHECK(datos_originales_captura_original_autor__presente=1 OR datos_originales_captura_original_autor IS NULL),
    CHECK(datos_originales_captura_original_cod_aplicacion__presente IN (0,1)),
    CHECK(datos_originales_captura_original_cod_aplicacion__presente=1 OR datos_originales_captura_original_cod_aplicacion IS NULL),
    CHECK(datos_originales_captura_original_cod_instrumento__presente IN (0,1)),
    CHECK(datos_originales_captura_original_cod_instrumento__presente=1 OR datos_originales_captura_original_cod_instrumento IS NULL),
    CHECK(datos_originales_captura_original_cod_personal__presente IN (0,1)),
    CHECK(datos_originales_captura_original_cod_personal__presente=1 OR datos_originales_captura_original_cod_personal IS NULL),
    CHECK(datos_originales_captura_original_cod_residente__presente IN (0,1)),
    CHECK(datos_originales_captura_original_cod_residente__presente=1 OR datos_originales_captura_original_cod_residente IS NULL),
    CHECK(dat_ori_cap_ori_cla_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_cla_ins__presente=1 OR dat_ori_cap_ori_cla_ins IS NULL),
    CHECK(dat_ori_cap_ori_doc_ori__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_doc_ori__presente=1 OR dat_ori_cap_ori_doc_ori IS NULL),
    CHECK(dat_ori_cap_ori_eda_en_apl__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_eda_en_apl__presente=1 OR dat_ori_cap_ori_eda_en_apl IS NULL),
    CHECK(datos_originales_captura_original_estado_original__presente IN (0,1)),
    CHECK(datos_originales_captura_original_estado_original__presente=1 OR datos_originales_captura_original_estado_original IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_alc_eta_com__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_alc_eta_com__presente=1 OR dat_ori_cap_ori_ext_apl_ger_alc_eta_com IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_alc_eta_com IS NULL OR dat_ori_cap_ori_ext_apl_ger_alc_eta_com IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_aut_int_cli__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_aut_int_cli__presente=1 OR dat_ori_cap_ori_ext_apl_ger_aut_int_cli IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_aut_int_cli IS NULL OR dat_ori_cap_ori_ext_apl_ger_aut_int_cli IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_eda_en_apl__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_eda_en_apl__presente=1 OR dat_ori_cap_ori_ext_apl_ger_eda_en_apl IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_esc_ori__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_esc_ori__presente=1 OR dat_ori_cap_ori_ext_apl_ger_esc_ori IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_fue__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_fue__presente=1 OR dat_ori_cap_ori_ext_apl_ger_fue IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_idi_dec__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_idi_dec__presente=1 OR dat_ori_cap_ori_ext_apl_ger_idi_dec IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj__presente=1 OR dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj IS NULL OR dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_pob_obj__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_pob_obj__presente=1 OR dat_ori_cap_ori_ext_apl_ger_pob_obj IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_max__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_max__presente=1 OR dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_max IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_min__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_min__presente=1 OR dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_min IS NULL),
    CHECK(dat_ori_cap_ori_ext_apl_ger_req_ver__estado='PRESENTE' OR dat_ori_cap_ori_ext_apl_ger_req_ver__cantidad=0),
    CHECK(dat_ori_cap_ori_ext_cap_aci_cla_sem__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_aci_cla_sem__presente=1 OR dat_ori_cap_ori_ext_cap_aci_cla_sem IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_aci_esp__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_aci_esp__presente=1 OR dat_ori_cap_ori_ext_cap_aci_esp IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_adm_est__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_adm_est__presente=1 OR dat_ori_cap_ori_ext_cap_adm_est IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_adm_est IS NULL OR dat_ori_cap_ori_ext_cap_adm_est IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_aud_com__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_aud_com__presente=1 OR dat_ori_cap_ori_ext_cap_aud_com IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_aud_com IS NULL OR dat_ori_cap_ori_ext_cap_aud_com IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_con_sen__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_con_sen__presente=1 OR dat_ori_cap_ori_ext_cap_con_sen IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_con_sen IS NULL OR dat_ori_cap_ori_ext_cap_con_sen IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_dem_min__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_dem_min__presente=1 OR dat_ori_cap_ori_ext_cap_dem_min IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_dem_min__cero_negativo=0 OR dat_ori_cap_ori_ext_cap_dem_min=0),
    CHECK(dat_ori_cap_ori_ext_cap_eda_ani__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_eda_ani__presente=1 OR dat_ori_cap_ori_ext_cap_eda_ani IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_edi_pro__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_edi_pro__presente=1 OR dat_ori_cap_ori_ext_cap_edi_pro IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_ele_rev__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_ele_rev__presente=1 OR dat_ori_cap_ori_ext_cap_ele_rev IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_ens_1__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_ens_1__presente=1 OR dat_ori_cap_ori_ext_cap_ens_1 IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_ens_2__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_ens_2__presente=1 OR dat_ori_cap_ori_ext_cap_ens_2 IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_ens_3__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_ens_3__presente=1 OR dat_ori_cap_ori_ext_cap_ens_3 IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_ens__estado='PRESENTE' OR dat_ori_cap_ori_ext_cap_ens__cantidad=0),
    CHECK(dat_ori_cap_ori_ext_cap_err_obs__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_err_obs__presente=1 OR dat_ori_cap_ori_ext_cap_err_obs IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_esc_ani__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_esc_ani__presente=1 OR dat_ori_cap_ori_ext_cap_esc_ani IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_esp_nat__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_esp_nat__presente=1 OR dat_ori_cap_ori_ext_cap_esp_nat IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_esp_nat IS NULL OR dat_ori_cap_ori_ext_cap_esp_nat IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_fin__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_fin__presente=1 OR dat_ori_cap_ori_ext_cap_fin IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_fin IS NULL OR dat_ori_cap_ori_ext_cap_fin IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_for__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_for__presente=1 OR dat_ori_cap_ori_ext_cap_for IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_for_l_aci__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_for_l_aci__presente=1 OR dat_ori_cap_ori_ext_cap_for_l_aci IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_for_n_aci__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_for_n_aci__presente=1 OR dat_ori_cap_ori_ext_cap_for_n_aci IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_idi__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_idi__presente=1 OR dat_ori_cap_ori_ext_cap_idi IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_ins__presente=1 OR dat_ori_cap_ori_ext_cap_ins IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_lec__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_lec__presente=1 OR dat_ori_cap_ori_ext_cap_lec IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_lec IS NULL OR dat_ori_cap_ori_ext_cap_lec IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_nac_bol__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_nac_bol__presente=1 OR dat_ori_cap_ori_ext_cap_nac_bol IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_nac_bol IS NULL OR dat_ori_cap_ori_ext_cap_nac_bol IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_pro__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_pro__presente=1 OR dat_ori_cap_ori_ext_cap_pro IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_pun_cop__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_pun_cop__presente=1 OR dat_ori_cap_ori_ext_cap_pun_cop IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_pun_cop__cero_negativo=0 OR dat_ori_cap_ori_ext_cap_pun_cop=0),
    CHECK(dat_ori_cap_ori_ext_cap_rec_dif__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_rec_dif__presente=1 OR dat_ori_cap_ori_ext_cap_rec_dif IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_res_bol__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_res_bol__presente=1 OR dat_ori_cap_ori_ext_cap_res_bol IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_res_bol IS NULL OR dat_ori_cap_ori_ext_cap_res_bol IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_seg_par_b__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_cap_seg_par_b__presente=1 OR dat_ori_cap_ori_ext_cap_seg_par_b IS NULL),
    CHECK(dat_ori_cap_ori_ext_cap_seg_par_b__cero_negativo=0 OR dat_ori_cap_ori_ext_cap_seg_par_b=0),
    CHECK(dat_ori_cap_ori_ext_fue_ent__estado='PRESENTE' OR dat_ori_cap_ori_ext_fue_ent__cantidad=0),
    CHECK(dat_ori_cap_ori_ext_ide_dec_edi_pro__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_ide_dec_edi_pro__presente=1 OR dat_ori_cap_ori_ext_ide_dec_edi_pro IS NULL),
    CHECK(dat_ori_cap_ori_ext_ide_dec_for__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_ide_dec_for__presente=1 OR dat_ori_cap_ori_ext_ide_dec_for IS NULL),
    CHECK(dat_ori_cap_ori_ext_ide_dec_idi__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_ide_dec_idi__presente=1 OR dat_ori_cap_ori_ext_ide_dec_idi IS NULL),
    CHECK(dat_ori_cap_ori_ext_ide_dec_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_ide_dec_ins__presente=1 OR dat_ori_cap_ori_ext_ide_dec_ins IS NULL),
    CHECK(dat_ori_cap_ori_ext_ide_req_met_edi_pro__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_ide_req_met_edi_pro__presente=1 OR dat_ori_cap_ori_ext_ide_req_met_edi_pro IS NULL),
    CHECK(dat_ori_cap_ori_ext_ide_req_met_for__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_ide_req_met_for__presente=1 OR dat_ori_cap_ori_ext_ide_req_met_for IS NULL),
    CHECK(dat_ori_cap_ori_ext_ide_req_met_idi__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_ide_req_met_idi__presente=1 OR dat_ori_cap_ori_ext_ide_req_met_idi IS NULL),
    CHECK(dat_ori_cap_ori_ext_ide_req_met_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_ide_req_met_ins__presente=1 OR dat_ori_cap_ori_ext_ide_req_met_ins IS NULL),
    CHECK(dat_ori_cap_ori_ext_met__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_met__presente=1 OR dat_ori_cap_ori_ext_met IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_aci_cla_sem__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_aci_cla_sem__presente=1 OR dat_ori_cap_ori_ext_nor_bru_aci_cla_sem IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_aci_esp__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_aci_esp__presente=1 OR dat_ori_cap_ori_ext_nor_bru_aci_esp IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_adm_est__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_adm_est__presente=1 OR dat_ori_cap_ori_ext_nor_bru_adm_est IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_adm_est IS NULL OR dat_ori_cap_ori_ext_nor_bru_adm_est IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_aud_com__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_aud_com__presente=1 OR dat_ori_cap_ori_ext_nor_bru_aud_com IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_aud_com IS NULL OR dat_ori_cap_ori_ext_nor_bru_aud_com IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_con_sen__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_con_sen__presente=1 OR dat_ori_cap_ori_ext_nor_bru_con_sen IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_con_sen IS NULL OR dat_ori_cap_ori_ext_nor_bru_con_sen IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_dem_min__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_dem_min__presente=1 OR dat_ori_cap_ori_ext_nor_bru_dem_min IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_dem_min__cero_negativo=0 OR dat_ori_cap_ori_ext_nor_bru_dem_min=0),
    CHECK(dat_ori_cap_ori_ext_nor_bru_ele_rev__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_ele_rev__presente=1 OR dat_ori_cap_ori_ext_nor_bru_ele_rev IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_ens__estado='PRESENTE' OR dat_ori_cap_ori_ext_nor_bru_ens__cantidad=0),
    CHECK(dat_ori_cap_ori_ext_nor_bru_err_obs__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_err_obs__presente=1 OR dat_ori_cap_ori_ext_nor_bru_err_obs IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_esc_ani__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_esc_ani__presente=1 OR dat_ori_cap_ori_ext_nor_bru_esc_ani IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_esp_nat__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_esp_nat__presente=1 OR dat_ori_cap_ori_ext_nor_bru_esp_nat IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_esp_nat IS NULL OR dat_ori_cap_ori_ext_nor_bru_esp_nat IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_fin__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_fin__presente=1 OR dat_ori_cap_ori_ext_nor_bru_fin IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_fin IS NULL OR dat_ori_cap_ori_ext_nor_bru_fin IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_for_l_aci__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_for_l_aci__presente=1 OR dat_ori_cap_ori_ext_nor_bru_for_l_aci IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_for_n_aci__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_for_n_aci__presente=1 OR dat_ori_cap_ori_ext_nor_bru_for_n_aci IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_lec__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_lec__presente=1 OR dat_ori_cap_ori_ext_nor_bru_lec IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_lec IS NULL OR dat_ori_cap_ori_ext_nor_bru_lec IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_nac_bol__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_nac_bol__presente=1 OR dat_ori_cap_ori_ext_nor_bru_nac_bol IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_nac_bol IS NULL OR dat_ori_cap_ori_ext_nor_bru_nac_bol IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_pun_cop__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_pun_cop__presente=1 OR dat_ori_cap_ori_ext_nor_bru_pun_cop IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_pun_cop__cero_negativo=0 OR dat_ori_cap_ori_ext_nor_bru_pun_cop=0),
    CHECK(dat_ori_cap_ori_ext_nor_bru_rec_dif__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_rec_dif__presente=1 OR dat_ori_cap_ori_ext_nor_bru_rec_dif IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_res_bol__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_res_bol__presente=1 OR dat_ori_cap_ori_ext_nor_bru_res_bol IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_res_bol IS NULL OR dat_ori_cap_ori_ext_nor_bru_res_bol IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_seg_par_b__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_bru_seg_par_b__presente=1 OR dat_ori_cap_ori_ext_nor_bru_seg_par_b IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_bru_seg_par_b__cero_negativo=0 OR dat_ori_cap_ori_ext_nor_bru_seg_par_b=0),
    CHECK(dat_ori_cap_ori_ext_nor_com__estado='PRESENTE' OR dat_ori_cap_ori_ext_nor_com__cantidad=0),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_aci_cla_sem__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_aci_cla_sem__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_aci_cla_sem IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_aci_esp__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_aci_esp__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_aci_esp IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_adm_est__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_adm_est__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_adm_est IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_adm_est IS NULL OR dat_ori_cap_ori_ext_nor_con_apl_adm_est IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_aud_com__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_aud_com__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_aud_com IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_aud_com IS NULL OR dat_ori_cap_ori_ext_nor_con_apl_aud_com IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_con_sen__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_con_sen__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_con_sen IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_con_sen IS NULL OR dat_ori_cap_ori_ext_nor_con_apl_con_sen IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_eda_ani__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_eda_ani__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_eda_ani IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_edi_pro__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_edi_pro__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_edi_pro IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_ele_rev__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_ele_rev__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_ele_rev IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_err_obs__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_err_obs__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_err_obs IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_esc_ani__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_esc_ani__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_esc_ani IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_esp_nat__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_esp_nat__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_esp_nat IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_esp_nat IS NULL OR dat_ori_cap_ori_ext_nor_con_apl_esp_nat IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_fin__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_fin__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_fin IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_fin IS NULL OR dat_ori_cap_ori_ext_nor_con_apl_fin IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_for__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_for__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_for IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_for_l_aci__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_for_l_aci__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_for_l_aci IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_for_n_aci__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_for_n_aci__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_for_n_aci IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_idi__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_idi__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_idi IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_ins__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_ins IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_lec__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_lec__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_lec IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_lec IS NULL OR dat_ori_cap_ori_ext_nor_con_apl_lec IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_nac_bol__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_nac_bol__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_nac_bol IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_nac_bol IS NULL OR dat_ori_cap_ori_ext_nor_con_apl_nac_bol IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_pro__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_pro__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_pro IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_pun_cop__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_pun_cop__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_pun_cop IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_pun_cop__cero_negativo=0 OR dat_ori_cap_ori_ext_nor_con_apl_pun_cop=0),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_res_bol__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_res_bol__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_res_bol IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_res_bol IS NULL OR dat_ori_cap_ori_ext_nor_con_apl_res_bol IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_seg_par_b__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_seg_par_b__presente=1 OR dat_ori_cap_ori_ext_nor_con_apl_seg_par_b IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl_seg_par_b__cero_negativo=0 OR dat_ori_cap_ori_ext_nor_con_apl_seg_par_b=0),
    CHECK(dat_ori_cap_ori_ext_nor_dia__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_dia__presente=1 OR dat_ori_cap_ori_ext_nor_dia IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_lim__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_lim__presente=1 OR dat_ori_cap_ori_ext_nor_lim IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_loc__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_loc__presente=1 OR dat_ori_cap_ori_ext_nor_loc IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_met__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_met__presente=1 OR dat_ori_cap_ori_ext_nor_met IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_ref__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_ref__presente=1 OR dat_ori_cap_ori_ext_nor_ref IS NULL),
    CHECK(dat_ori_cap_ori_ext_nor_rev_pro__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_nor_rev_pro__presente=1 OR dat_ori_cap_ori_ext_nor_rev_pro IS NULL),
    CHECK(dat_ori_cap_ori_ext_per__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ext_per__presente=1 OR dat_ori_cap_ori_ext_per IS NULL),
    CHECK(datos_originales_captura_original_fecha__presente IN (0,1)),
    CHECK(datos_originales_captura_original_fecha__presente=1 OR datos_originales_captura_original_fecha IS NULL),
    CHECK(dat_ori_cap_ori_fue_eda_cam__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_fue_eda_cam__presente=1 OR dat_ori_cap_ori_fue_eda_cam IS NULL),
    CHECK(dat_ori_cap_ori_fue_eda_fec_apl__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_fue_eda_fec_apl__presente=1 OR dat_ori_cap_ori_fue_eda_fec_apl IS NULL),
    CHECK(dat_ori_cap_ori_fue_eda_fec_nac__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_fue_eda_fec_nac__presente=1 OR dat_ori_cap_ori_fue_eda_fec_nac IS NULL),
    CHECK(dat_ori_cap_ori_fue_eda_reg__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_fue_eda_reg__presente=1 OR dat_ori_cap_ori_fue_eda_reg IS NULL),
    CHECK(dat_ori_cap_ori_fue_eda_tab__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_fue_eda_tab__presente=1 OR dat_ori_cap_ori_fue_eda_tab IS NULL),
    CHECK(dat_ori_cap_ori_ins_act_cod_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ins_act_cod_ins__presente=1 OR dat_ori_cap_ori_ins_act_cod_ins IS NULL),
    CHECK(dat_ori_cap_ori_ins_act_cod__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ins_act_cod__presente=1 OR dat_ori_cap_ori_ins_act_cod IS NULL),
    CHECK(dat_ori_cap_ori_ins_act_des__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ins_act_des__presente=1 OR dat_ori_cap_ori_ins_act_des IS NULL),
    CHECK(dat_ori_cap_ori_ins_act_est__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ins_act_est__presente=1 OR dat_ori_cap_ori_ins_act_est IS NULL),
    CHECK(dat_ori_cap_ori_ins_act_nom__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ins_act_nom__presente=1 OR dat_ori_cap_ori_ins_act_nom IS NULL),
    CHECK(dat_ori_cap_ori_ins_act_pun_max__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ins_act_pun_max__presente=1 OR dat_ori_cap_ori_ins_act_pun_max IS NULL),
    CHECK(dat_ori_cap_ori_ins_act_tip__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ins_act_tip__presente=1 OR dat_ori_cap_ori_ins_act_tip IS NULL),
    CHECK(dat_ori_cap_ori_ins_act_ver__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ins_act_ver__presente=1 OR dat_ori_cap_ori_ins_act_ver IS NULL),
    CHECK(dat_ori_cap_ori_nom_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_nom_ins__presente=1 OR dat_ori_cap_ori_nom_ins IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_cod_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_cod_ins__presente=1 OR dat_ori_cap_ori_per_cap_ori_cod_ins IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_cla_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_cla_ins__presente=1 OR dat_ori_cap_ori_per_cap_ori_cla_ins IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_id__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_id__presente=1 OR dat_ori_cap_ori_per_cap_ori_id IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins_edi_pro__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins_edi_pro__presente=1 OR dat_ori_cap_ori_per_cap_ori_ide_ins_edi_pro IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins_for__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins_for__presente=1 OR dat_ori_cap_ori_per_cap_ori_ide_ins_for IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins_idi__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins_idi__presente=1 OR dat_ori_cap_ori_per_cap_ori_ide_ins_idi IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins_ins__presente=1 OR dat_ori_cap_ori_per_cap_ori_ide_ins_ins IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_met__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_met__presente=1 OR dat_ori_cap_ori_per_cap_ori_met IS NULL),
    CHECK(dat_ori_cap_ori_per_cap_ori_sel__estado='PRESENTE' OR dat_ori_cap_ori_per_cap_ori_sel__cantidad=0),
    CHECK(dat_ori_cap_ori_per_cap_ori_ver_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_per_cap_ori_ver_ins__presente=1 OR dat_ori_cap_ori_per_cap_ori_ver_ins IS NULL),
    CHECK(dat_ori_cap_ori_pre_dec_ori__estado='PRESENTE' OR dat_ori_cap_ori_pre_dec_ori__cantidad=0),
    CHECK(dat_ori_cap_ori_pro_cod_apl_ant__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_pro_cod_apl_ant__presente=1 OR dat_ori_cap_ori_pro_cod_apl_ant IS NULL),
    CHECK(dat_ori_cap_ori_pro_cod_doc_cli__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_pro_cod_doc_cli__presente=1 OR dat_ori_cap_ori_pro_cod_doc_cli IS NULL),
    CHECK(dat_ori_cap_ori_pro_cod_usu_reg__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_pro_cod_usu_reg__presente=1 OR dat_ori_cap_ori_pro_cod_usu_reg IS NULL),
    CHECK(dat_ori_cap_ori_pro_loc_fue__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_pro_loc_fue__presente=1 OR dat_ori_cap_ori_pro_loc_fue IS NULL),
    CHECK(dat_ori_cap_ori_pro_reg_en__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_pro_reg_en__presente=1 OR dat_ori_cap_ori_pro_reg_en IS NULL),
    CHECK(dat_ori_cap_ori_res_ori__estado='PRESENTE' OR dat_ori_cap_ori_res_ori__cantidad=0),
    CHECK(dat_ori_cap_ori_ver_ins__presente IN (0,1)),
    CHECK(dat_ori_cap_ori_ver_ins__presente=1 OR dat_ori_cap_ori_ver_ins IS NULL),
    CHECK(dat_ori_cap_ori_vin_pre_act__estado='PRESENTE' OR dat_ori_cap_ori_vin_pre_act__cantidad=0),
    CHECK(datos_originales_causa_ayuda__presente IN (0,1)),
    CHECK(datos_originales_causa_ayuda__presente=1 OR datos_originales_causa_ayuda IS NULL),
    CHECK(datos_originales_descripcion__presente IN (0,1)),
    CHECK(datos_originales_descripcion__presente=1 OR datos_originales_descripcion IS NULL),
    CHECK(datos_originales_desempeno__presente IN (0,1)),
    CHECK(datos_originales_desempeno__presente=1 OR datos_originales_desempeno IS NULL),
    CHECK(datos_originales_dominio__presente IN (0,1)),
    CHECK(datos_originales_dominio__presente=1 OR datos_originales_dominio IS NULL),
    CHECK(datos_originales_fuente__presente IN (0,1)),
    CHECK(datos_originales_fuente__presente=1 OR datos_originales_fuente IS NULL),
    CHECK(datos_originales_nivel_previo__presente IN (0,1)),
    CHECK(datos_originales_nivel_previo__presente=1 OR datos_originales_nivel_previo IS NULL),
    CHECK(datos_originales_oportunidad__presente IN (0,1)),
    CHECK(datos_originales_oportunidad__presente=1 OR datos_originales_oportunidad IS NULL),
    CHECK(datos_originales_tipo_ayuda__presente IN (0,1)),
    CHECK(datos_originales_tipo_ayuda__presente=1 OR datos_originales_tipo_ayuda IS NULL),
    CHECK(episodio__presente IN (0,1)),
    CHECK(episodio__presente=1 OR episodio IS NULL),
    CHECK(fecha__presente IN (0,1)),
    CHECK(fecha__presente=1 OR fecha IS NULL),
    CHECK(registro__presente IN (0,1)),
    CHECK(registro__presente=1 OR registro IS NULL),
    CHECK(residente__presente IN (0,1)),
    CHECK(residente__presente=1 OR residente IS NULL),
    CHECK(tabla__presente IN (0,1)),
    CHECK(tabla__presente=1 OR tabla IS NULL),
    CHECK(tipo__presente IN (0,1)),
    CHECK(tipo__presente=1 OR tipo IS NULL),
    CHECK(vigente__presente IN (0,1)),
    CHECK(vigente__presente=1 OR vigente IS NULL),
    CHECK(vigente IS NULL OR vigente IN (0,1)),
    CHECK(dto__estado='PRESENTE' OR (autor__presente=0 AND datos_originales__estado='AUSENTE' AND episodio__presente=0 AND fecha__presente=0 AND registro__presente=0 AND residente__presente=0 AND tabla__presente=0 AND tipo__presente=0 AND vigente__presente=0)),
    CHECK(datos_originales__estado='PRESENTE' OR (datos_originales_actividad__presente=0 AND datos_originales_captura_original__estado='AUSENTE' AND datos_originales_causa_ayuda__presente=0 AND datos_originales_descripcion__presente=0 AND datos_originales_desempeno__presente=0 AND datos_originales_dominio__presente=0 AND datos_originales_fuente__presente=0 AND datos_originales_nivel_previo__presente=0 AND datos_originales_oportunidad__presente=0 AND datos_originales_tipo_ayuda__presente=0)),
    CHECK(datos_originales_captura_original__estado='PRESENTE' OR (dat_ori_cap_ori_apl_act__estado='AUSENTE' AND dat_ori_cap_ori_ate_ori__estado='AUSENTE' AND datos_originales_captura_original_autor__presente=0 AND datos_originales_captura_original_cod_aplicacion__presente=0 AND datos_originales_captura_original_cod_instrumento__presente=0 AND datos_originales_captura_original_cod_personal__presente=0 AND datos_originales_captura_original_cod_residente__presente=0 AND dat_ori_cap_ori_cla_ins__presente=0 AND dat_ori_cap_ori_doc_ori__presente=0 AND dat_ori_cap_ori_eda_en_apl__presente=0 AND datos_originales_captura_original_estado_original__presente=0 AND datos_originales_captura_original_extraccion__estado='AUSENTE' AND datos_originales_captura_original_fecha__presente=0 AND datos_originales_captura_original_fuente_edad__estado='AUSENTE' AND dat_ori_cap_ori_ins_act__estado='AUSENTE' AND datos_originales_captura_original_motivos__estado='AUSENTE' AND dat_ori_cap_ori_nom_ins__presente=0 AND dat_ori_cap_ori_per_cap_ori__estado='AUSENTE' AND dat_ori_cap_ori_pre_dec_ori__estado='AUSENTE' AND datos_originales_captura_original_procedencia__estado='AUSENTE' AND dat_ori_cap_ori_res_ori__estado='AUSENTE' AND dat_ori_cap_ori_ver_ins__presente=0 AND dat_ori_cap_ori_vin_pre_act__estado='AUSENTE')),
    CHECK(dat_ori_cap_ori_apl_act__estado='PRESENTE' OR (dat_ori_cap_ori_apl_act_cla__presente=0 AND dat_ori_cap_ori_apl_act_cod_apl__presente=0 AND dat_ori_cap_ori_apl_act_cod_apl_ant__presente=0 AND dat_ori_cap_ori_apl_act_cod_ate__presente=0 AND dat_ori_cap_ori_apl_act_cod_doc_cli__presente=0 AND dat_ori_cap_ori_apl_act_cod_ins__presente=0 AND dat_ori_cap_ori_apl_act_cod_per__presente=0 AND dat_ori_cap_ori_apl_act_cod_res__presente=0 AND dat_ori_cap_ori_apl_act_cod_usu_reg__presente=0 AND dat_ori_cap_ori_apl_act_est__presente=0 AND dat_ori_cap_ori_apl_act_fec__presente=0 AND dat_ori_cap_ori_apl_act_int__presente=0 AND dat_ori_cap_ori_apl_act_loc_fue__presente=0 AND dat_ori_cap_ori_apl_act_obs__presente=0 AND dat_ori_cap_ori_apl_act_pun_max__presente=0 AND dat_ori_cap_ori_apl_act_pun_tot__presente=0 AND dat_ori_cap_ori_apl_act_reg_en__presente=0)),
    CHECK(dat_ori_cap_ori_ate_ori__estado='PRESENTE' OR (dat_ori_cap_ori_ate_ori_cod_ate__presente=0 AND dat_ori_cap_ori_ate_ori_cod_res__presente=0)),
    CHECK(datos_originales_captura_original_extraccion__estado='PRESENTE' OR (dat_ori_cap_ori_ext_apl_ger__estado='AUSENTE' AND dat_ori_cap_ori_ext_cap__estado='AUSENTE' AND dat_ori_cap_ori_ext_fue_ent__estado='AUSENTE' AND dat_ori_cap_ori_ext_ide_dec__estado='AUSENTE' AND dat_ori_cap_ori_ext_ide_req_met__estado='AUSENTE' AND dat_ori_cap_ori_ext_met__presente=0 AND dat_ori_cap_ori_ext_mot_cap__estado='AUSENTE' AND dat_ori_cap_ori_ext_nor__estado='AUSENTE' AND dat_ori_cap_ori_ext_per__presente=0)),
    CHECK(dat_ori_cap_ori_ext_apl_ger__estado='PRESENTE' OR (dat_ori_cap_ori_ext_apl_ger_alc_eta_com__presente=0 AND dat_ori_cap_ori_ext_apl_ger_aut_int_cli__presente=0 AND dat_ori_cap_ori_ext_apl_ger_eda_en_apl__presente=0 AND dat_ori_cap_ori_ext_apl_ger_esc_ori__presente=0 AND dat_ori_cap_ori_ext_apl_ger_fue__presente=0 AND dat_ori_cap_ori_ext_apl_ger_idi_dec__presente=0 AND dat_ori_cap_ori_ext_apl_ger_mot__estado='AUSENTE' AND dat_ori_cap_ori_ext_apl_ger_per_a_pob_obj__presente=0 AND dat_ori_cap_ori_ext_apl_ger_pob_obj__presente=0 AND dat_ori_cap_ori_ext_apl_ger_ran_eda_ref__estado='AUSENTE' AND dat_ori_cap_ori_ext_apl_ger_req_ver__estado='AUSENTE')),
    CHECK(dat_ori_cap_ori_ext_apl_ger_ran_eda_ref__estado='PRESENTE' OR (dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_max__presente=0 AND dat_ori_cap_ori_ext_apl_ger_ran_eda_ref_min__presente=0)),
    CHECK(dat_ori_cap_ori_ext_cap__estado='PRESENTE' OR (dat_ori_cap_ori_ext_cap_aci_cla_sem__presente=0 AND dat_ori_cap_ori_ext_cap_aci_esp__presente=0 AND dat_ori_cap_ori_ext_cap_adm_est__presente=0 AND dat_ori_cap_ori_ext_cap_aud_com__presente=0 AND dat_ori_cap_ori_ext_cap_con_sen__presente=0 AND dat_ori_cap_ori_ext_cap_dem_min__presente=0 AND dat_ori_cap_ori_ext_cap_eda_ani__presente=0 AND dat_ori_cap_ori_ext_cap_edi_pro__presente=0 AND dat_ori_cap_ori_ext_cap_ele_rev__presente=0 AND dat_ori_cap_ori_ext_cap_ens_1__presente=0 AND dat_ori_cap_ori_ext_cap_ens_2__presente=0 AND dat_ori_cap_ori_ext_cap_ens_3__presente=0 AND dat_ori_cap_ori_ext_cap_ens__estado='AUSENTE' AND dat_ori_cap_ori_ext_cap_err_obs__presente=0 AND dat_ori_cap_ori_ext_cap_esc_ani__presente=0 AND dat_ori_cap_ori_ext_cap_esp_nat__presente=0 AND dat_ori_cap_ori_ext_cap_fin__presente=0 AND dat_ori_cap_ori_ext_cap_for__presente=0 AND dat_ori_cap_ori_ext_cap_for_l_aci__presente=0 AND dat_ori_cap_ori_ext_cap_for_n_aci__presente=0 AND dat_ori_cap_ori_ext_cap_idi__presente=0 AND dat_ori_cap_ori_ext_cap_ins__presente=0 AND dat_ori_cap_ori_ext_cap_lec__presente=0 AND dat_ori_cap_ori_ext_cap_nac_bol__presente=0 AND dat_ori_cap_ori_ext_cap_pro__presente=0 AND dat_ori_cap_ori_ext_cap_pun_cop__presente=0 AND dat_ori_cap_ori_ext_cap_rec_dif__presente=0 AND dat_ori_cap_ori_ext_cap_res_bol__presente=0 AND dat_ori_cap_ori_ext_cap_seg_par_b__presente=0)),
    CHECK(dat_ori_cap_ori_ext_ide_dec__estado='PRESENTE' OR (dat_ori_cap_ori_ext_ide_dec_edi_pro__presente=0 AND dat_ori_cap_ori_ext_ide_dec_for__presente=0 AND dat_ori_cap_ori_ext_ide_dec_idi__presente=0 AND dat_ori_cap_ori_ext_ide_dec_ins__presente=0)),
    CHECK(dat_ori_cap_ori_ext_ide_req_met__estado='PRESENTE' OR (dat_ori_cap_ori_ext_ide_req_met_edi_pro__presente=0 AND dat_ori_cap_ori_ext_ide_req_met_for__presente=0 AND dat_ori_cap_ori_ext_ide_req_met_idi__presente=0 AND dat_ori_cap_ori_ext_ide_req_met_ins__presente=0)),
    CHECK(dat_ori_cap_ori_ext_nor__estado='PRESENTE' OR (dat_ori_cap_ori_ext_nor_bru__estado='AUSENTE' AND dat_ori_cap_ori_ext_nor_com__estado='AUSENTE' AND dat_ori_cap_ori_ext_nor_con_apl__estado='AUSENTE' AND dat_ori_cap_ori_ext_nor_dia__presente=0 AND dat_ori_cap_ori_ext_nor_lim__presente=0 AND dat_ori_cap_ori_ext_nor_loc__presente=0 AND dat_ori_cap_ori_ext_nor_met__presente=0 AND dat_ori_cap_ori_ext_nor_ref__presente=0 AND dat_ori_cap_ori_ext_nor_rev_pro__presente=0)),
    CHECK(dat_ori_cap_ori_ext_nor_bru__estado='PRESENTE' OR (dat_ori_cap_ori_ext_nor_bru_aci_cla_sem__presente=0 AND dat_ori_cap_ori_ext_nor_bru_aci_esp__presente=0 AND dat_ori_cap_ori_ext_nor_bru_adm_est__presente=0 AND dat_ori_cap_ori_ext_nor_bru_aud_com__presente=0 AND dat_ori_cap_ori_ext_nor_bru_con_sen__presente=0 AND dat_ori_cap_ori_ext_nor_bru_dem_min__presente=0 AND dat_ori_cap_ori_ext_nor_bru_ele_rev__presente=0 AND dat_ori_cap_ori_ext_nor_bru_ens__estado='AUSENTE' AND dat_ori_cap_ori_ext_nor_bru_err_obs__presente=0 AND dat_ori_cap_ori_ext_nor_bru_esc_ani__presente=0 AND dat_ori_cap_ori_ext_nor_bru_esp_nat__presente=0 AND dat_ori_cap_ori_ext_nor_bru_fin__presente=0 AND dat_ori_cap_ori_ext_nor_bru_for_l_aci__presente=0 AND dat_ori_cap_ori_ext_nor_bru_for_n_aci__presente=0 AND dat_ori_cap_ori_ext_nor_bru_lec__presente=0 AND dat_ori_cap_ori_ext_nor_bru_nac_bol__presente=0 AND dat_ori_cap_ori_ext_nor_bru_pun_cop__presente=0 AND dat_ori_cap_ori_ext_nor_bru_rec_dif__presente=0 AND dat_ori_cap_ori_ext_nor_bru_res_bol__presente=0 AND dat_ori_cap_ori_ext_nor_bru_seg_par_b__presente=0)),
    CHECK(dat_ori_cap_ori_ext_nor_con_apl__estado='PRESENTE' OR (dat_ori_cap_ori_ext_nor_con_apl_aci_cla_sem__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_aci_esp__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_adm_est__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_aud_com__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_con_sen__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_eda_ani__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_edi_pro__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_ele_rev__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_err_obs__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_esc_ani__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_esp_nat__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_fin__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_for__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_for_l_aci__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_for_n_aci__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_idi__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_ins__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_lec__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_nac_bol__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_pro__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_pun_cop__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_res_bol__presente=0 AND dat_ori_cap_ori_ext_nor_con_apl_seg_par_b__presente=0)),
    CHECK(datos_originales_captura_original_fuente_edad__estado='PRESENTE' OR (dat_ori_cap_ori_fue_eda_cam__presente=0 AND dat_ori_cap_ori_fue_eda_fec_apl__presente=0 AND dat_ori_cap_ori_fue_eda_fec_nac__presente=0 AND dat_ori_cap_ori_fue_eda_reg__presente=0 AND dat_ori_cap_ori_fue_eda_tab__presente=0)),
    CHECK(dat_ori_cap_ori_ins_act__estado='PRESENTE' OR (dat_ori_cap_ori_ins_act_cod_ins__presente=0 AND dat_ori_cap_ori_ins_act_cod__presente=0 AND dat_ori_cap_ori_ins_act_des__presente=0 AND dat_ori_cap_ori_ins_act_est__presente=0 AND dat_ori_cap_ori_ins_act_nom__presente=0 AND dat_ori_cap_ori_ins_act_pun_max__presente=0 AND dat_ori_cap_ori_ins_act_tip__presente=0 AND dat_ori_cap_ori_ins_act_ver__presente=0)),
    CHECK(dat_ori_cap_ori_per_cap_ori__estado='PRESENTE' OR (dat_ori_cap_ori_per_cap_ori_cod_ins__presente=0 AND dat_ori_cap_ori_per_cap_ori_cla_ins__presente=0 AND dat_ori_cap_ori_per_cap_ori_id__presente=0 AND dat_ori_cap_ori_per_cap_ori_ide_ins__estado='AUSENTE' AND dat_ori_cap_ori_per_cap_ori_met__presente=0 AND dat_ori_cap_ori_per_cap_ori_sel__estado='AUSENTE' AND dat_ori_cap_ori_per_cap_ori_ver_ins__presente=0)),
    CHECK(dat_ori_cap_ori_per_cap_ori_ide_ins__estado='PRESENTE' OR (dat_ori_cap_ori_per_cap_ori_ide_ins_edi_pro__presente=0 AND dat_ori_cap_ori_per_cap_ori_ide_ins_for__presente=0 AND dat_ori_cap_ori_per_cap_ori_ide_ins_idi__presente=0 AND dat_ori_cap_ori_per_cap_ori_ide_ins_ins__presente=0)),
    CHECK(datos_originales_captura_original_procedencia__estado='PRESENTE' OR (dat_ori_cap_ori_pro_cod_apl_ant__presente=0 AND dat_ori_cap_ori_pro_cod_doc_cli__presente=0 AND dat_ori_cap_ori_pro_cod_usu_reg__presente=0 AND dat_ori_cap_ori_pro_loc_fue__presente=0 AND dat_ori_cap_ori_pro_reg_en__presente=0))
);

CREATE TABLE ret_extraccion_aplicabilidad_geriatrica_re_c118 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_extraccion_captura_ensayos_c119 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_captura_original_extraccion_fuentes_en_c120 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicacion__presente INTEGER NOT NULL,
    aplicacion TEXT,
    bruto__presente INTEGER NOT NULL,
    bruto__tipo VARCHAR(6) NOT NULL CHECK(bruto__tipo IN ('NULL','BOOL','FLOAT','INT')),
    bruto_logico INTEGER,
    bruto_real REAL,
    bruto_entero BIGINT,
    bruto__cero_negativo INTEGER NOT NULL DEFAULT 0,
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    pregunta__presente INTEGER NOT NULL,
    pregunta TEXT,
    respuesta__presente INTEGER NOT NULL,
    respuesta TEXT,
    valor__presente INTEGER NOT NULL,
    valor__tipo VARCHAR(6) NOT NULL CHECK(valor__tipo IN ('NULL','BOOL','FLOAT','INT')),
    valor_logico INTEGER,
    valor_real REAL,
    valor_entero BIGINT,
    valor__cero_negativo INTEGER NOT NULL DEFAULT 0,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(aplicacion__presente IN (0,1)),
    CHECK(aplicacion__presente=1 OR aplicacion IS NULL),
    CHECK(bruto__presente IN (0,1)),
    CHECK(bruto__presente=1 OR bruto__tipo='NULL'),
    CHECK((bruto__tipo='NULL' AND bruto_logico IS NULL AND bruto_real IS NULL AND bruto_entero IS NULL) OR (bruto__tipo='BOOL' AND bruto_logico IS NOT NULL AND bruto_real IS NULL AND bruto_entero IS NULL) OR (bruto__tipo='FLOAT' AND bruto_real IS NOT NULL AND bruto_logico IS NULL AND bruto_entero IS NULL) OR (bruto__tipo='INT' AND bruto_entero IS NOT NULL AND bruto_logico IS NULL AND bruto_real IS NULL)),
    CHECK(bruto__cero_negativo=0 OR (bruto__tipo='FLOAT' AND bruto_real=0)),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(pregunta__presente IN (0,1)),
    CHECK(pregunta__presente=1 OR pregunta IS NULL),
    CHECK(respuesta__presente IN (0,1)),
    CHECK(respuesta__presente=1 OR respuesta IS NULL),
    CHECK(valor__presente IN (0,1)),
    CHECK(valor__presente=1 OR valor__tipo='NULL'),
    CHECK((valor__tipo='NULL' AND valor_logico IS NULL AND valor_real IS NULL AND valor_entero IS NULL) OR (valor__tipo='BOOL' AND valor_logico IS NOT NULL AND valor_real IS NULL AND valor_entero IS NULL) OR (valor__tipo='FLOAT' AND valor_real IS NOT NULL AND valor_logico IS NULL AND valor_entero IS NULL) OR (valor__tipo='INT' AND valor_entero IS NOT NULL AND valor_logico IS NULL AND valor_real IS NULL)),
    CHECK(valor__cero_negativo=0 OR (valor__tipo='FLOAT' AND valor_real=0)),
    CHECK(dto__estado='PRESENTE' OR (aplicacion__presente=0 AND bruto__presente=0 AND campo__presente=0 AND pregunta__presente=0 AND respuesta__presente=0 AND valor__presente=0))
);

CREATE TABLE ret_normalizacion_bruto_ensayos_c121 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto BIGINT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE TABLE ret_extraccion_normalizacion_componentes_c122 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    clasificacion_clinica__presente INTEGER NOT NULL,
    clasificacion_clinica TEXT,
    de_residual__presente INTEGER NOT NULL,
    de_residual REAL,
    de_residual__cero_negativo INTEGER NOT NULL DEFAULT 0,
    direccion__presente INTEGER NOT NULL,
    direccion TEXT,
    esperado__presente INTEGER NOT NULL,
    esperado REAL,
    esperado__cero_negativo INTEGER NOT NULL DEFAULT 0,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    interpretacion_matematica__estado VARCHAR(8) NOT NULL CHECK(interpretacion_matematica__estado IN ('AUSENTE','NULL','PRESENTE')),
    interpretacion_matematica_clasificacion_clinica__presente INTEGER NOT NULL,
    interpretacion_matematica_clasificacion_clinica TEXT,
    interpretacion_matematica_contrato__presente INTEGER NOT NULL,
    interpretacion_matematica_contrato TEXT,
    interpretacion_matematica_estado__presente INTEGER NOT NULL,
    interpretacion_matematica_estado TEXT,
    interpretacion_matematica_limite__presente INTEGER NOT NULL,
    interpretacion_matematica_limite TEXT,
    interpretacion_matematica_posicion__presente INTEGER NOT NULL,
    interpretacion_matematica_posicion TEXT,
    interpretacion_matematica_texto__presente INTEGER NOT NULL,
    interpretacion_matematica_texto TEXT,
    medida__presente INTEGER NOT NULL,
    medida TEXT,
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    puntuacion_bruta__presente INTEGER NOT NULL,
    puntuacion_bruta__tipo VARCHAR(6) NOT NULL CHECK(puntuacion_bruta__tipo IN ('NULL','FLOAT','INT')),
    puntuacion_bruta_real REAL,
    puntuacion_bruta_entero BIGINT,
    puntuacion_bruta__cero_negativo INTEGER NOT NULL DEFAULT 0,
    z__presente INTEGER NOT NULL,
    z REAL,
    z__cero_negativo INTEGER NOT NULL DEFAULT 0,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(clasificacion_clinica__presente IN (0,1)),
    CHECK(clasificacion_clinica__presente=1 OR clasificacion_clinica IS NULL),
    CHECK(de_residual__presente IN (0,1)),
    CHECK(de_residual__presente=1 OR de_residual IS NULL),
    CHECK(de_residual__cero_negativo=0 OR de_residual=0),
    CHECK(direccion__presente IN (0,1)),
    CHECK(direccion__presente=1 OR direccion IS NULL),
    CHECK(esperado__presente IN (0,1)),
    CHECK(esperado__presente=1 OR esperado IS NULL),
    CHECK(esperado__cero_negativo=0 OR esperado=0),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(interpretacion_matematica_clasificacion_clinica__presente IN (0,1)),
    CHECK(interpretacion_matematica_clasificacion_clinica__presente=1 OR interpretacion_matematica_clasificacion_clinica IS NULL),
    CHECK(interpretacion_matematica_contrato__presente IN (0,1)),
    CHECK(interpretacion_matematica_contrato__presente=1 OR interpretacion_matematica_contrato IS NULL),
    CHECK(interpretacion_matematica_estado__presente IN (0,1)),
    CHECK(interpretacion_matematica_estado__presente=1 OR interpretacion_matematica_estado IS NULL),
    CHECK(interpretacion_matematica_limite__presente IN (0,1)),
    CHECK(interpretacion_matematica_limite__presente=1 OR interpretacion_matematica_limite IS NULL),
    CHECK(interpretacion_matematica_posicion__presente IN (0,1)),
    CHECK(interpretacion_matematica_posicion__presente=1 OR interpretacion_matematica_posicion IS NULL),
    CHECK(interpretacion_matematica_texto__presente IN (0,1)),
    CHECK(interpretacion_matematica_texto__presente=1 OR interpretacion_matematica_texto IS NULL),
    CHECK(medida__presente IN (0,1)),
    CHECK(medida__presente=1 OR medida IS NULL),
    CHECK(puntuacion_bruta__presente IN (0,1)),
    CHECK(puntuacion_bruta__presente=1 OR puntuacion_bruta__tipo='NULL'),
    CHECK((puntuacion_bruta__tipo='NULL' AND puntuacion_bruta_real IS NULL AND puntuacion_bruta_entero IS NULL) OR (puntuacion_bruta__tipo='FLOAT' AND puntuacion_bruta_real IS NOT NULL AND puntuacion_bruta_entero IS NULL) OR (puntuacion_bruta__tipo='INT' AND puntuacion_bruta_entero IS NOT NULL AND puntuacion_bruta_real IS NULL)),
    CHECK(puntuacion_bruta__cero_negativo=0 OR (puntuacion_bruta__tipo='FLOAT' AND puntuacion_bruta_real=0)),
    CHECK(z__presente IN (0,1)),
    CHECK(z__presente=1 OR z IS NULL),
    CHECK(z__cero_negativo=0 OR z=0),
    CHECK(dto__estado='PRESENTE' OR (clasificacion_clinica__presente=0 AND de_residual__presente=0 AND direccion__presente=0 AND esperado__presente=0 AND estado__presente=0 AND interpretacion_matematica__estado='AUSENTE' AND medida__presente=0 AND motivos__estado='AUSENTE' AND puntuacion_bruta__presente=0 AND z__presente=0)),
    CHECK(interpretacion_matematica__estado='PRESENTE' OR (interpretacion_matematica_clasificacion_clinica__presente=0 AND interpretacion_matematica_contrato__presente=0 AND interpretacion_matematica_estado__presente=0 AND interpretacion_matematica_limite__presente=0 AND interpretacion_matematica_posicion__presente=0 AND interpretacion_matematica_texto__presente=0))
);

CREATE TABLE ret_captura_original_perfil_captura_origin_c123 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente INTEGER NOT NULL,
    campo TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente IN (0,1)),
    CHECK(campo__presente=1 OR campo IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (campo__presente=0 AND cod_pregunta__presente=0))
);

CREATE TABLE ret_datos_originales_captura_original_preg_c124 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_instrumento__presente INTEGER NOT NULL,
    cod_instrumento TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    codigo__presente INTEGER NOT NULL,
    codigo TEXT,
    dominio__presente INTEGER NOT NULL,
    dominio TEXT,
    enunciado__presente INTEGER NOT NULL,
    enunciado TEXT,
    estado__presente INTEGER NOT NULL,
    estado TEXT,
    orden__presente INTEGER NOT NULL,
    orden BIGINT,
    puntaje_maximo__presente INTEGER NOT NULL,
    puntaje_maximo TEXT,
    tipo_respuesta__presente INTEGER NOT NULL,
    tipo_respuesta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(cod_instrumento__presente IN (0,1)),
    CHECK(cod_instrumento__presente=1 OR cod_instrumento IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(codigo__presente IN (0,1)),
    CHECK(codigo__presente=1 OR codigo IS NULL),
    CHECK(dominio__presente IN (0,1)),
    CHECK(dominio__presente=1 OR dominio IS NULL),
    CHECK(enunciado__presente IN (0,1)),
    CHECK(enunciado__presente=1 OR enunciado IS NULL),
    CHECK(estado__presente IN (0,1)),
    CHECK(estado__presente=1 OR estado IS NULL),
    CHECK(orden__presente IN (0,1)),
    CHECK(orden__presente=1 OR orden IS NULL),
    CHECK(puntaje_maximo__presente IN (0,1)),
    CHECK(puntaje_maximo__presente=1 OR puntaje_maximo IS NULL),
    CHECK(tipo_respuesta__presente IN (0,1)),
    CHECK(tipo_respuesta__presente=1 OR tipo_respuesta IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_instrumento__presente=0 AND cod_pregunta__presente=0 AND codigo__presente=0 AND dominio__presente=0 AND enunciado__presente=0 AND estado__presente=0 AND orden__presente=0 AND puntaje_maximo__presente=0 AND tipo_respuesta__presente=0))
);

CREATE TABLE ret_datos_originales_captura_original_resp_c125 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_aplicacion__presente INTEGER NOT NULL,
    cod_aplicacion TEXT,
    cod_opcion__presente INTEGER NOT NULL,
    cod_opcion TEXT,
    cod_pregunta__presente INTEGER NOT NULL,
    cod_pregunta TEXT,
    cod_respuesta__presente INTEGER NOT NULL,
    cod_respuesta TEXT,
    clave_pregunta__presente INTEGER NOT NULL,
    clave_pregunta TEXT,
    coherente__presente INTEGER NOT NULL,
    coherente INTEGER,
    dominio_original__presente INTEGER NOT NULL,
    dominio_original TEXT,
    estado_pregunta_actual__presente INTEGER NOT NULL,
    estado_pregunta_actual TEXT,
    observacion__presente INTEGER NOT NULL,
    observacion TEXT,
    opcion_actual__presente INTEGER NOT NULL,
    opcion_actual TEXT,
    pregunta__presente INTEGER NOT NULL,
    pregunta TEXT,
    pregunta_actual__estado VARCHAR(8) NOT NULL CHECK(pregunta_actual__estado IN ('AUSENTE','NULL','PRESENTE')),
    pregunta_actual_cod_instrumento__presente INTEGER NOT NULL,
    pregunta_actual_cod_instrumento TEXT,
    pregunta_actual_cod_pregunta__presente INTEGER NOT NULL,
    pregunta_actual_cod_pregunta TEXT,
    pregunta_actual_codigo__presente INTEGER NOT NULL,
    pregunta_actual_codigo TEXT,
    pregunta_actual_dominio__presente INTEGER NOT NULL,
    pregunta_actual_dominio TEXT,
    pregunta_actual_enunciado__presente INTEGER NOT NULL,
    pregunta_actual_enunciado TEXT,
    pregunta_actual_estado__presente INTEGER NOT NULL,
    pregunta_actual_estado TEXT,
    pregunta_actual_orden__presente INTEGER NOT NULL,
    pregunta_actual_orden BIGINT,
    pregunta_actual_puntaje_maximo__presente INTEGER NOT NULL,
    pregunta_actual_puntaje_maximo TEXT,
    pregunta_actual_tipo_respuesta__presente INTEGER NOT NULL,
    pregunta_actual_tipo_respuesta TEXT,
    puntaje__presente INTEGER NOT NULL,
    puntaje TEXT,
    tipo_respuesta__presente INTEGER NOT NULL,
    tipo_respuesta TEXT,
    valor_logico__presente INTEGER NOT NULL,
    valor_logico INTEGER,
    valor_numero__presente INTEGER NOT NULL,
    valor_numero__tipo VARCHAR(6) NOT NULL CHECK(valor_numero__tipo IN ('NULL','FLOAT','INT')),
    valor_numero_real REAL,
    valor_numero_entero BIGINT,
    valor_numero__cero_negativo INTEGER NOT NULL DEFAULT 0,
    valor_texto__presente INTEGER NOT NULL,
    valor_texto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo ='NULL')),
    CHECK(cod_aplicacion__presente IN (0,1)),
    CHECK(cod_aplicacion__presente=1 OR cod_aplicacion IS NULL),
    CHECK(cod_opcion__presente IN (0,1)),
    CHECK(cod_opcion__presente=1 OR cod_opcion IS NULL),
    CHECK(cod_pregunta__presente IN (0,1)),
    CHECK(cod_pregunta__presente=1 OR cod_pregunta IS NULL),
    CHECK(cod_respuesta__presente IN (0,1)),
    CHECK(cod_respuesta__presente=1 OR cod_respuesta IS NULL),
    CHECK(clave_pregunta__presente IN (0,1)),
    CHECK(clave_pregunta__presente=1 OR clave_pregunta IS NULL),
    CHECK(coherente__presente IN (0,1)),
    CHECK(coherente__presente=1 OR coherente IS NULL),
    CHECK(coherente IS NULL OR coherente IN (0,1)),
    CHECK(dominio_original__presente IN (0,1)),
    CHECK(dominio_original__presente=1 OR dominio_original IS NULL),
    CHECK(estado_pregunta_actual__presente IN (0,1)),
    CHECK(estado_pregunta_actual__presente=1 OR estado_pregunta_actual IS NULL),
    CHECK(observacion__presente IN (0,1)),
    CHECK(observacion__presente=1 OR observacion IS NULL),
    CHECK(opcion_actual__presente IN (0,1)),
    CHECK(opcion_actual__presente=1 OR opcion_actual IS NULL),
    CHECK(pregunta__presente IN (0,1)),
    CHECK(pregunta__presente=1 OR pregunta IS NULL),
    CHECK(pregunta_actual_cod_instrumento__presente IN (0,1)),
    CHECK(pregunta_actual_cod_instrumento__presente=1 OR pregunta_actual_cod_instrumento IS NULL),
    CHECK(pregunta_actual_cod_pregunta__presente IN (0,1)),
    CHECK(pregunta_actual_cod_pregunta__presente=1 OR pregunta_actual_cod_pregunta IS NULL),
    CHECK(pregunta_actual_codigo__presente IN (0,1)),
    CHECK(pregunta_actual_codigo__presente=1 OR pregunta_actual_codigo IS NULL),
    CHECK(pregunta_actual_dominio__presente IN (0,1)),
    CHECK(pregunta_actual_dominio__presente=1 OR pregunta_actual_dominio IS NULL),
    CHECK(pregunta_actual_enunciado__presente IN (0,1)),
    CHECK(pregunta_actual_enunciado__presente=1 OR pregunta_actual_enunciado IS NULL),
    CHECK(pregunta_actual_estado__presente IN (0,1)),
    CHECK(pregunta_actual_estado__presente=1 OR pregunta_actual_estado IS NULL),
    CHECK(pregunta_actual_orden__presente IN (0,1)),
    CHECK(pregunta_actual_orden__presente=1 OR pregunta_actual_orden IS NULL),
    CHECK(pregunta_actual_puntaje_maximo__presente IN (0,1)),
    CHECK(pregunta_actual_puntaje_maximo__presente=1 OR pregunta_actual_puntaje_maximo IS NULL),
    CHECK(pregunta_actual_tipo_respuesta__presente IN (0,1)),
    CHECK(pregunta_actual_tipo_respuesta__presente=1 OR pregunta_actual_tipo_respuesta IS NULL),
    CHECK(puntaje__presente IN (0,1)),
    CHECK(puntaje__presente=1 OR puntaje IS NULL),
    CHECK(tipo_respuesta__presente IN (0,1)),
    CHECK(tipo_respuesta__presente=1 OR tipo_respuesta IS NULL),
    CHECK(valor_logico__presente IN (0,1)),
    CHECK(valor_logico__presente=1 OR valor_logico IS NULL),
    CHECK(valor_logico IS NULL OR valor_logico IN (0,1)),
    CHECK(valor_numero__presente IN (0,1)),
    CHECK(valor_numero__presente=1 OR valor_numero__tipo='NULL'),
    CHECK((valor_numero__tipo='NULL' AND valor_numero_real IS NULL AND valor_numero_entero IS NULL) OR (valor_numero__tipo='FLOAT' AND valor_numero_real IS NOT NULL AND valor_numero_entero IS NULL) OR (valor_numero__tipo='INT' AND valor_numero_entero IS NOT NULL AND valor_numero_real IS NULL)),
    CHECK(valor_numero__cero_negativo=0 OR (valor_numero__tipo='FLOAT' AND valor_numero_real=0)),
    CHECK(valor_texto__presente IN (0,1)),
    CHECK(valor_texto__presente=1 OR valor_texto IS NULL),
    CHECK(dto__estado='PRESENTE' OR (cod_aplicacion__presente=0 AND cod_opcion__presente=0 AND cod_pregunta__presente=0 AND cod_respuesta__presente=0 AND clave_pregunta__presente=0 AND coherente__presente=0 AND dominio_original__presente=0 AND estado_pregunta_actual__presente=0 AND observacion__presente=0 AND opcion_actual__presente=0 AND pregunta__presente=0 AND pregunta_actual__estado='AUSENTE' AND puntaje__presente=0 AND tipo_respuesta__presente=0 AND valor_logico__presente=0 AND valor_numero__presente=0 AND valor_texto__presente=0)),
    CHECK(pregunta_actual__estado='PRESENTE' OR (pregunta_actual_cod_instrumento__presente=0 AND pregunta_actual_cod_pregunta__presente=0 AND pregunta_actual_codigo__presente=0 AND pregunta_actual_dominio__presente=0 AND pregunta_actual_enunciado__presente=0 AND pregunta_actual_estado__presente=0 AND pregunta_actual_orden__presente=0 AND pregunta_actual_puntaje_maximo__presente=0 AND pregunta_actual_tipo_respuesta__presente=0))
);

CREATE TABLE ret_datos_originales_captura_original_vinc_c126 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__estado VARCHAR(8) NOT NULL CHECK(dto__estado IN ('AUSENTE','NULL','PRESENTE')),
    pertenece_al_instrumento__presente INTEGER NOT NULL,
    pertenece_al_instrumento INTEGER,
    pregunta_actual__estado VARCHAR(8) NOT NULL CHECK(pregunta_actual__estado IN ('AUSENTE','NULL','PRESENTE')),
    pregunta_actual_cod_instrumento__presente INTEGER NOT NULL,
    pregunta_actual_cod_instrumento TEXT,
    pregunta_actual_cod_pregunta__presente INTEGER NOT NULL,
    pregunta_actual_cod_pregunta TEXT,
    pregunta_actual_codigo__presente INTEGER NOT NULL,
    pregunta_actual_codigo TEXT,
    pregunta_actual_dominio__presente INTEGER NOT NULL,
    pregunta_actual_dominio TEXT,
    pregunta_actual_enunciado__presente INTEGER NOT NULL,
    pregunta_actual_enunciado TEXT,
    pregunta_actual_estado__presente INTEGER NOT NULL,
    pregunta_actual_estado TEXT,
    pregunta_actual_orden__presente INTEGER NOT NULL,
    pregunta_actual_orden BIGINT,
    pregunta_actual_puntaje_maximo__presente INTEGER NOT NULL,
    pregunta_actual_puntaje_maximo TEXT,
    pregunta_actual_tipo_respuesta__presente INTEGER NOT NULL,
    pregunta_actual_tipo_respuesta TEXT,
    selector__estado VARCHAR(8) NOT NULL CHECK(selector__estado IN ('AUSENTE','NULL','PRESENTE')),
    selector_campo__presente INTEGER NOT NULL,
    selector_campo TEXT,
    selector_cod_pregunta__presente INTEGER NOT NULL,
    selector_cod_pregunta TEXT,
    tiene_respuesta__presente INTEGER NOT NULL,
    tiene_respuesta INTEGER,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(pertenece_al_instrumento__presente IN (0,1)),
    CHECK(pertenece_al_instrumento__presente=1 OR pertenece_al_instrumento IS NULL),
    CHECK(pertenece_al_instrumento IS NULL OR pertenece_al_instrumento IN (0,1)),
    CHECK(pregunta_actual_cod_instrumento__presente IN (0,1)),
    CHECK(pregunta_actual_cod_instrumento__presente=1 OR pregunta_actual_cod_instrumento IS NULL),
    CHECK(pregunta_actual_cod_pregunta__presente IN (0,1)),
    CHECK(pregunta_actual_cod_pregunta__presente=1 OR pregunta_actual_cod_pregunta IS NULL),
    CHECK(pregunta_actual_codigo__presente IN (0,1)),
    CHECK(pregunta_actual_codigo__presente=1 OR pregunta_actual_codigo IS NULL),
    CHECK(pregunta_actual_dominio__presente IN (0,1)),
    CHECK(pregunta_actual_dominio__presente=1 OR pregunta_actual_dominio IS NULL),
    CHECK(pregunta_actual_enunciado__presente IN (0,1)),
    CHECK(pregunta_actual_enunciado__presente=1 OR pregunta_actual_enunciado IS NULL),
    CHECK(pregunta_actual_estado__presente IN (0,1)),
    CHECK(pregunta_actual_estado__presente=1 OR pregunta_actual_estado IS NULL),
    CHECK(pregunta_actual_orden__presente IN (0,1)),
    CHECK(pregunta_actual_orden__presente=1 OR pregunta_actual_orden IS NULL),
    CHECK(pregunta_actual_puntaje_maximo__presente IN (0,1)),
    CHECK(pregunta_actual_puntaje_maximo__presente=1 OR pregunta_actual_puntaje_maximo IS NULL),
    CHECK(pregunta_actual_tipo_respuesta__presente IN (0,1)),
    CHECK(pregunta_actual_tipo_respuesta__presente=1 OR pregunta_actual_tipo_respuesta IS NULL),
    CHECK(selector_campo__presente IN (0,1)),
    CHECK(selector_campo__presente=1 OR selector_campo IS NULL),
    CHECK(selector_cod_pregunta__presente IN (0,1)),
    CHECK(selector_cod_pregunta__presente=1 OR selector_cod_pregunta IS NULL),
    CHECK(tiene_respuesta__presente IN (0,1)),
    CHECK(tiene_respuesta__presente=1 OR tiene_respuesta IS NULL),
    CHECK(tiene_respuesta IS NULL OR tiene_respuesta IN (0,1)),
    CHECK(dto__estado='PRESENTE' OR (pertenece_al_instrumento__presente=0 AND pregunta_actual__estado='AUSENTE' AND selector__estado='AUSENTE' AND tiene_respuesta__presente=0)),
    CHECK(pregunta_actual__estado='PRESENTE' OR (pregunta_actual_cod_instrumento__presente=0 AND pregunta_actual_cod_pregunta__presente=0 AND pregunta_actual_codigo__presente=0 AND pregunta_actual_dominio__presente=0 AND pregunta_actual_enunciado__presente=0 AND pregunta_actual_estado__presente=0 AND pregunta_actual_orden__presente=0 AND pregunta_actual_puntaje_maximo__presente=0 AND pregunta_actual_tipo_respuesta__presente=0)),
    CHECK(selector__estado='PRESENTE' OR (selector_campo__presente=0 AND selector_cod_pregunta__presente=0))
);

CREATE TABLE ret_release_archivos_c127 (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dto__presente INTEGER NOT NULL,
    dto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT,
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('COLECCION'))),
    CHECK((rol_coleccion='COLECCION' AND clave_tipo IN ('INT','STRING'))),
    CHECK(dto__presente IN (0,1)),
    CHECK(dto__presente=1 OR dto IS NULL)
);

CREATE INDEX evaluaciones_nominales_cod_residente_i ON evaluaciones_nominales(cod_residente);

CREATE INDEX evaluaciones_nominales_cod_usuario_i ON evaluaciones_nominales(cod_usuario);

CREATE INDEX evaluaciones_nominales_huella_release_i ON evaluaciones_nominales(huella_release);

CREATE INDEX entradas_institucionales_evaluacion_i ON entradas_institucionales(cod_evaluacion_experta);

CREATE INDEX ret_contenido_capturas_capturas_c002_evaluacion_i ON ret_contenido_capturas_capturas_c002(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_capturas_captur_bee0a192d2_k ON ret_contenido_capturas_capturas_c002(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_extraccion_aplicabilidad_geriatrica_re_c003_evaluacion_i ON ret_extraccion_aplicabilidad_geriatrica_re_c003(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_extraccion_aplicabilidad__fddf0558a1_k ON ret_extraccion_aplicabilidad_geriatrica_re_c003(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_extraccion_captura_ensayos_c004_evaluacion_i ON ret_extraccion_captura_ensayos_c004(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_extraccion_captura_ensayo_e312c4c4ff_k ON ret_extraccion_captura_ensayos_c004(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_capturas_extraccion_fuentes_entradas_c005_evaluacion_i ON ret_capturas_extraccion_fuentes_entradas_c005(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_capturas_extraccion_fuent_80165d3168_k ON ret_capturas_extraccion_fuentes_entradas_c005(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_normalizacion_bruto_ensayos_c006_evaluacion_i ON ret_normalizacion_bruto_ensayos_c006(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_normalizacion_bruto_ensay_77f749365e_k ON ret_normalizacion_bruto_ensayos_c006(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_extraccion_normalizacion_componentes_c007_evaluacion_i ON ret_extraccion_normalizacion_componentes_c007(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_extraccion_normalizacion__35ffa12407_k ON ret_extraccion_normalizacion_componentes_c007(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_capturas_capturas_motivos_c008_evaluacion_i ON ret_capturas_capturas_motivos_c008(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_capturas_capturas_motivos_4e24adf741_k ON ret_capturas_capturas_motivos_c008(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_capturas_perfil_captura_original_selec_c009_evaluacion_i ON ret_capturas_perfil_captura_original_selec_c009(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_capturas_perfil_captura_o_7da0776458_k ON ret_capturas_perfil_captura_original_selec_c009(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_capturas_capturas_preguntas_declarativ_c010_evaluacion_i ON ret_capturas_capturas_preguntas_declarativ_c010(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_capturas_capturas_pregunt_53d4199c6c_k ON ret_capturas_capturas_preguntas_declarativ_c010(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_capturas_capturas_respuestas_originale_c011_evaluacion_i ON ret_capturas_capturas_respuestas_originale_c011(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_capturas_capturas_respues_b89be90d6c_k ON ret_capturas_capturas_respuestas_originale_c011(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_capturas_capturas_vinculos_preguntas_a_c012_evaluacion_i ON ret_capturas_capturas_vinculos_preguntas_a_c012(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_capturas_capturas_vinculo_73f5b29aa3_k ON ret_capturas_capturas_vinculos_preguntas_a_c012(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_conocimiento_contratos_relac_c013_evaluacion_i ON ret_contenido_conocimiento_contratos_relac_c013(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_conocimiento_co_8249d9a3b8_k ON ret_contenido_conocimiento_contratos_relac_c013(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_contratos_relaciones_moda_c014_evaluacion_i ON ret_conocimiento_contratos_relaciones_moda_c014(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_contratos_re_6647d1c99c_k ON ret_conocimiento_contratos_relaciones_moda_c014(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_condiciones_regla__c015_evaluacion_i ON ret_conocimiento_tablas_condiciones_regla__c015(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_condi_d0bc5a8acb_k ON ret_conocimiento_tablas_condiciones_regla__c015(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_consecuencias_regl_c016_evaluacion_i ON ret_conocimiento_tablas_consecuencias_regl_c016(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_conse_d56bbd1631_k ON ret_conocimiento_tablas_consecuencias_regl_c016(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_criterios_dominios_c017_evaluacion_i ON ret_conocimiento_tablas_criterios_dominios_c017(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_crite_7ad1acec1d_k ON ret_conocimiento_tablas_criterios_dominios_c017(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_dominios_valores_e_c018_evaluacion_i ON ret_conocimiento_tablas_dominios_valores_e_c018(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_domin_49ccafd8d1_k ON ret_conocimiento_tablas_dominios_valores_e_c018(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_fuentes_datos_expe_c019_evaluacion_i ON ret_conocimiento_tablas_fuentes_datos_expe_c019(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_fuent_0b99299a80_k ON ret_conocimiento_tablas_fuentes_datos_expe_c019(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_mapeos_valores_fue_c020_evaluacion_i ON ret_conocimiento_tablas_mapeos_valores_fue_c020(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_mapeo_2a74153a55_k ON ret_conocimiento_tablas_mapeos_valores_fue_c020(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_mapeos_variables_f_c021_evaluacion_i ON ret_conocimiento_tablas_mapeos_variables_f_c021(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_mapeo_f88780f5b4_k ON ret_conocimiento_tablas_mapeos_variables_f_c021(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_nodos_semanticos_c022_evaluacion_i ON ret_conocimiento_tablas_nodos_semanticos_c022(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_nodos_bf62373df2_k ON ret_conocimiento_tablas_nodos_semanticos_c022(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_reglas_expertas_c023_evaluacion_i ON ret_conocimiento_tablas_reglas_expertas_c023(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_regla_78e073aac8_k ON ret_conocimiento_tablas_reglas_expertas_c023(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_relaciones_semanti_c024_evaluacion_i ON ret_conocimiento_tablas_relaciones_semanti_c024(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_relac_246320989b_k ON ret_conocimiento_tablas_relaciones_semanti_c024(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_valores_semanticos_c025_evaluacion_i ON ret_conocimiento_tablas_valores_semanticos_c025(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_valor_fdd32472d8_k ON ret_conocimiento_tablas_valores_semanticos_c025(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_variables_expertas_c026_evaluacion_i ON ret_conocimiento_tablas_variables_expertas_c026(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_varia_c7651c59d1_k ON ret_conocimiento_tablas_variables_expertas_c026(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_fuente_conceptual_nodos_c027_evaluacion_i ON ret_contenido_fuente_conceptual_nodos_c027(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_fuente_conceptu_71ca34bc55_k ON ret_contenido_fuente_conceptual_nodos_c027(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_fuente_conceptual_nodos_procedencia_c028_evaluacion_i ON ret_fuente_conceptual_nodos_procedencia_c028(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_fuente_conceptual_nodos_p_39898632ea_k ON ret_fuente_conceptual_nodos_procedencia_c028(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_fuente_conceptual_relaciones_c029_evaluacion_i ON ret_contenido_fuente_conceptual_relaciones_c029(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_fuente_conceptu_22ad4cad7a_k ON ret_contenido_fuente_conceptual_relaciones_c029(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_instrumental_comprobacion_adqui_c030_evaluacion_i ON ret_perfil_instrumental_comprobacion_adqui_c030(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_instrumental_compr_b1ffaa4743_k ON ret_perfil_instrumental_comprobacion_adqui_c030(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_aplicaciones_aplicabilidad_requiere_ve_c031_evaluacion_i ON ret_aplicaciones_aplicabilidad_requiere_ve_c031(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_aplicaciones_aplicabilida_6036a3ad99_k ON ret_aplicaciones_aplicabilidad_requiere_ve_c031(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_comprobacion_adquisicion_aplicaciones__c032_evaluacion_i ON ret_comprobacion_adquisicion_aplicaciones__c032(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_comprobacion_adquisicion__1fccd61aa7_k ON ret_comprobacion_adquisicion_aplicaciones__c032(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_comprobacion_adquisicion_aplicaciones__c033_evaluacion_i ON ret_comprobacion_adquisicion_aplicaciones__c033(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_comprobacion_adquisicion__d54d37e127_k ON ret_comprobacion_adquisicion_aplicaciones__c033(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_comprobacion_adquisicion_aplicaciones__c034_evaluacion_i ON ret_comprobacion_adquisicion_aplicaciones__c034(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_comprobacion_adquisicion__dbeb8b05cf_k ON ret_comprobacion_adquisicion_aplicaciones__c034(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_perfil_instrumental_dominios_c035_evaluacion_i ON ret_salida_perfil_instrumental_dominios_c035(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_perfil_instrumenta_c9fce1489a_k ON ret_salida_perfil_instrumental_dominios_c035(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_instrumental_dominios_component_c036_evaluacion_i ON ret_perfil_instrumental_dominios_component_c036(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_instrumental_domin_cfee8c111d_k ON ret_perfil_instrumental_dominios_component_c036(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_componentes_captura_requerida_calculos_c037_evaluacion_i ON ret_componentes_captura_requerida_calculos_c037(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_componentes_captura_reque_d15e262f17_k ON ret_componentes_captura_requerida_calculos_c037(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_componentes_captura_requerida_metodos__c038_evaluacion_i ON ret_componentes_captura_requerida_metodos__c038(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_componentes_captura_reque_eb8775b502_k ON ret_componentes_captura_requerida_metodos__c038(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_componentes_captura_requerida_normaliz_c039_evaluacion_i ON ret_componentes_captura_requerida_normaliz_c039(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_componentes_captura_reque_bd2ef94030_k ON ret_componentes_captura_requerida_normaliz_c039(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_componentes_concepto_procedencia_c040_evaluacion_i ON ret_componentes_concepto_procedencia_c040(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_componentes_concepto_proc_9fd92b1535_k ON ret_componentes_concepto_procedencia_c040(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_dominios_componentes_mediciones_c041_evaluacion_i ON ret_dominios_componentes_mediciones_c041(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_dominios_componentes_medi_7eee01053c_k ON ret_dominios_componentes_mediciones_c041(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_mediciones_captura_ensayos_c042_evaluacion_i ON ret_mediciones_captura_ensayos_c042(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_mediciones_captura_ensayo_797845b805_k ON ret_mediciones_captura_ensayos_c042(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_componentes_mediciones_fuentes_entrada_c043_evaluacion_i ON ret_componentes_mediciones_fuentes_entrada_c043(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_componentes_mediciones_fu_6992b845af_k ON ret_componentes_mediciones_fuentes_entrada_c043(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_dominios_componentes_motivos_c044_evaluacion_i ON ret_dominios_componentes_motivos_c044(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_dominios_componentes_moti_57e055d641_k ON ret_dominios_componentes_motivos_c044(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_instrumental_dominios_motivos_c045_evaluacion_i ON ret_perfil_instrumental_dominios_motivos_c045(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_instrumental_domin_3dc3066746_k ON ret_perfil_instrumental_dominios_motivos_c045(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_instrumental_red_contratos_c046_evaluacion_i ON ret_perfil_instrumental_red_contratos_c046(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_instrumental_red_c_47ae8ff9f4_k ON ret_perfil_instrumental_red_contratos_c046(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_red_contratos_modalidades_c047_evaluacion_i ON ret_red_contratos_modalidades_c047(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_red_contratos_modalidades_2e68bf2adb_k ON ret_red_contratos_modalidades_c047(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_instrumental_red_dominios_c048_evaluacion_i ON ret_perfil_instrumental_red_dominios_c048(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_instrumental_red_d_5c5c63f54b_k ON ret_perfil_instrumental_red_dominios_c048(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_red_dominios_componentes_c049_evaluacion_i ON ret_red_dominios_componentes_c049(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_red_dominios_componentes__c029e4b4b4_k ON ret_red_dominios_componentes_c049(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_componentes_concepto_procedencia_c050_evaluacion_i ON ret_componentes_concepto_procedencia_c050(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_componentes_concepto_proc_7f267e66f7_k ON ret_componentes_concepto_procedencia_c050(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_dominios_concepto_procedencia_c051_evaluacion_i ON ret_dominios_concepto_procedencia_c051(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_dominios_concepto_procede_8e34ec20c3_k ON ret_dominios_concepto_procedencia_c051(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_instrumental_red_nodos_c052_evaluacion_i ON ret_perfil_instrumental_red_nodos_c052(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_instrumental_red_n_4ef1f45e96_k ON ret_perfil_instrumental_red_nodos_c052(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_instrumental_red_relaciones_c053_evaluacion_i ON ret_perfil_instrumental_red_relaciones_c053(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_instrumental_red_r_b2aeceda55_k ON ret_perfil_instrumental_red_relaciones_c053(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_resultado_perfil_c054_evaluacion_i ON ret_salida_resultado_perfil_c054(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_resultado_perfil_c_9ad38023a1_k ON ret_salida_resultado_perfil_c054(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_compuerta_institucional_contexto_falta_c055_evaluacion_i ON ret_compuerta_institucional_contexto_falta_c055(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_compuerta_institucional_c_b107f758fe_k ON ret_compuerta_institucional_contexto_falta_c055(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_resultado_perfil_condiciones_pendiente_c056_evaluacion_i ON ret_resultado_perfil_condiciones_pendiente_c056(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_resultado_perfil_condicio_fcdc2f6781_k ON ret_resultado_perfil_condiciones_pendiente_c056(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_traza_evidencias_c057_evaluacion_i ON ret_perfil_traza_evidencias_c057(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_traza_evidencias_c_adaeccba30_k ON ret_perfil_traza_evidencias_c057(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_procedencia_instrumental_perfil_interp_c058_evaluacion_i ON ret_procedencia_instrumental_perfil_interp_c058(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_procedencia_instrumental__27d8bb25c1_k ON ret_procedencia_instrumental_perfil_interp_c058(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_procedencia_instrumental_perfil_interp_c059_evaluacion_i ON ret_procedencia_instrumental_perfil_interp_c059(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_procedencia_instrumental__96fff35014_k ON ret_procedencia_instrumental_perfil_interp_c059(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_traza_reglas_c060_evaluacion_i ON ret_perfil_traza_reglas_c060(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_traza_reglas_c060_02177fb449_k ON ret_perfil_traza_reglas_c060(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_traza_reglas_condiciones_c061_evaluacion_i ON ret_traza_reglas_condiciones_c061(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_traza_reglas_condiciones__1649a6d9f2_k ON ret_traza_reglas_condiciones_c061(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_reglas_condiciones_soportes_c062_evaluacion_i ON ret_reglas_condiciones_soportes_c062(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_reglas_condiciones_soport_46bdb5c4ec_k ON ret_reglas_condiciones_soportes_c062(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_traza_reglas_evaluaciones_unidad_c063_evaluacion_i ON ret_traza_reglas_evaluaciones_unidad_c063(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_traza_reglas_evaluaciones_0d005cf2fa_k ON ret_traza_reglas_evaluaciones_unidad_c063(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_reglas_evaluaciones_unidad_condiciones_c064_evaluacion_i ON ret_reglas_evaluaciones_unidad_condiciones_c064(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_reglas_evaluaciones_unida_a5e114feed_k ON ret_reglas_evaluaciones_unidad_condiciones_c064(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_evaluaciones_unidad_condiciones_soport_c065_evaluacion_i ON ret_evaluaciones_unidad_condiciones_soport_c065(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_evaluaciones_unidad_condi_f050be6ce8_k ON ret_evaluaciones_unidad_condiciones_soport_c065(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_traza_reglas_unidades_por_componente_c066_evaluacion_i ON ret_traza_reglas_unidades_por_componente_c066(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_traza_reglas_unidades_por_255263adbd_k ON ret_traza_reglas_unidades_por_componente_c066(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_perfil_traza_rutas_c067_evaluacion_i ON ret_perfil_traza_rutas_c067(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_perfil_traza_rutas_c067_22bf7726da_k ON ret_perfil_traza_rutas_c067(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_procedencia_instrumental_perfil_interp_c068_evaluacion_i ON ret_procedencia_instrumental_perfil_interp_c068(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_procedencia_instrumental__55e8ca9bb2_k ON ret_procedencia_instrumental_perfil_interp_c068(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_procedencia_instrumental_perfil_interp_c069_evaluacion_i ON ret_procedencia_instrumental_perfil_interp_c069(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_procedencia_instrumental__dc2afc6cdf_k ON ret_procedencia_instrumental_perfil_interp_c069(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_resultado_red_componentes_c070_evaluacion_i ON ret_resultado_red_componentes_c070(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_resultado_red_componentes_e0a7023926_k ON ret_resultado_red_componentes_c070(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_resultado_red_dominios_c071_evaluacion_i ON ret_resultado_red_dominios_c071(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_resultado_red_dominios_c0_fca8f04bcb_k ON ret_resultado_red_dominios_c071(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_resultado_red_rutas_reglas_c072_evaluacion_i ON ret_resultado_red_rutas_reglas_c072(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_resultado_red_rutas_regla_540affdb78_k ON ret_resultado_red_rutas_reglas_c072(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_resultado_red_rutas_reglas_c073_evaluacion_i ON ret_resultado_red_rutas_reglas_c073(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_resultado_red_rutas_regla_3ef773af0e_k ON ret_resultado_red_rutas_reglas_c073(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_instrumental_contenido_vinculos_c074_evaluacion_i ON ret_instrumental_contenido_vinculos_c074(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_instrumental_contenido_vi_59ad4085ba_k ON ret_instrumental_contenido_vinculos_c074(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_vinculos_perfil_interpretativo_medicio_c075_evaluacion_i ON ret_vinculos_perfil_interpretativo_medicio_c075(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_vinculos_perfil_interpret_b40aa7149a_k ON ret_vinculos_perfil_interpretativo_medicio_c075(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_vinculos_perfil_interpretativo_predica_c076_evaluacion_i ON ret_vinculos_perfil_interpretativo_predica_c076(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_vinculos_perfil_interpret_47fd42891e_k ON ret_vinculos_perfil_interpretativo_predica_c076(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_adquisicion_exclusiones_c077_evaluacion_i ON ret_contenido_adquisicion_exclusiones_c077(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_adquisicion_exc_c592f460ab_k ON ret_contenido_adquisicion_exclusiones_c077(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_petersen_contenido_constancias_documen_c078_evaluacion_i ON ret_petersen_contenido_constancias_documen_c078(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_petersen_contenido_consta_57a04fe96c_k ON ret_petersen_contenido_constancias_documen_c078(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_gobierno_conocimiento_contratos_relaci_c079_evaluacion_i ON ret_gobierno_conocimiento_contratos_relaci_c079(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_gobierno_conocimiento_con_481d9681b1_k ON ret_gobierno_conocimiento_contratos_relaci_c079(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_contratos_relaciones_moda_c080_evaluacion_i ON ret_conocimiento_contratos_relaciones_moda_c080(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_contratos_re_bc84f71204_k ON ret_conocimiento_contratos_relaciones_moda_c080(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_condiciones_regla__c081_evaluacion_i ON ret_conocimiento_tablas_condiciones_regla__c081(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_condi_fc1247fc9d_k ON ret_conocimiento_tablas_condiciones_regla__c081(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_consecuencias_regl_c082_evaluacion_i ON ret_conocimiento_tablas_consecuencias_regl_c082(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_conse_3ce6363306_k ON ret_conocimiento_tablas_consecuencias_regl_c082(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_criterios_dominios_c083_evaluacion_i ON ret_conocimiento_tablas_criterios_dominios_c083(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_crite_2d1d60c958_k ON ret_conocimiento_tablas_criterios_dominios_c083(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_dominios_valores_e_c084_evaluacion_i ON ret_conocimiento_tablas_dominios_valores_e_c084(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_domin_8ead9dff9d_k ON ret_conocimiento_tablas_dominios_valores_e_c084(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_nodos_semanticos_c085_evaluacion_i ON ret_conocimiento_tablas_nodos_semanticos_c085(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_nodos_33a848260b_k ON ret_conocimiento_tablas_nodos_semanticos_c085(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_reglas_expertas_c086_evaluacion_i ON ret_conocimiento_tablas_reglas_expertas_c086(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_regla_7f5ba3a8ba_k ON ret_conocimiento_tablas_reglas_expertas_c086(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_relaciones_semanti_c087_evaluacion_i ON ret_conocimiento_tablas_relaciones_semanti_c087(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_relac_ec093e497c_k ON ret_conocimiento_tablas_relaciones_semanti_c087(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_valores_semanticos_c088_evaluacion_i ON ret_conocimiento_tablas_valores_semanticos_c088(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_valor_2038ef0d5a_k ON ret_conocimiento_tablas_valores_semanticos_c088(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_conocimiento_tablas_variables_expertas_c089_evaluacion_i ON ret_conocimiento_tablas_variables_expertas_c089(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_conocimiento_tablas_varia_1ebadfe4ed_k ON ret_conocimiento_tablas_variables_expertas_c089(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_petersen_contenido_membresias_soportes_c090_evaluacion_i ON ret_petersen_contenido_membresias_soportes_c090(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_petersen_contenido_membre_656a78b6cd_k ON ret_petersen_contenido_membresias_soportes_c090(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_membresias_soportes_soportes_c091_evaluacion_i ON ret_contenido_membresias_soportes_soportes_c091(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_membresias_sopo_81f61607a6_k ON ret_contenido_membresias_soportes_soportes_c091(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_petersen_contenido_procedencia_valorac_c092_evaluacion_i ON ret_petersen_contenido_procedencia_valorac_c092(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_petersen_contenido_proced_6a2f3301ec_k ON ret_petersen_contenido_procedencia_valorac_c092(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_salida_dominios_c093_evaluacion_i ON ret_contenido_salida_dominios_c093(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_salida_dominios_9be137e16d_k ON ret_contenido_salida_dominios_c093(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_dominios_indices_valoraciones_c094_evaluacion_i ON ret_salida_dominios_indices_valoraciones_c094(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_dominios_indices_v_d63d4f327c_k ON ret_salida_dominios_indices_valoraciones_c094(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_dominios_motivos_c095_evaluacion_i ON ret_salida_dominios_motivos_c095(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_dominios_motivos_c_60e6002c54_k ON ret_salida_dominios_motivos_c095(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_gobierno_bloqueada_por_c096_evaluacion_i ON ret_salida_gobierno_bloqueada_por_c096(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_gobierno_bloqueada_0e23d727c7_k ON ret_salida_gobierno_bloqueada_por_c096(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_gobierno_estados_derivados_c097_evaluacion_i ON ret_salida_gobierno_estados_derivados_c097(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_gobierno_estados_d_fe50a78c10_k ON ret_salida_gobierno_estados_derivados_c097(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_gobierno_reglas_c098_evaluacion_i ON ret_salida_gobierno_reglas_c098(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_gobierno_reglas_c0_4585822234_k ON ret_salida_gobierno_reglas_c098(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_gobierno_reglas_condiciones_c099_evaluacion_i ON ret_gobierno_reglas_condiciones_c099(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_gobierno_reglas_condicion_2969a75afa_k ON ret_gobierno_reglas_condiciones_c099(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_reglas_condiciones_soportes_c100_evaluacion_i ON ret_reglas_condiciones_soportes_c100(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_reglas_condiciones_soport_f778e70592_k ON ret_reglas_condiciones_soportes_c100(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_salida_impedimentos_c101_evaluacion_i ON ret_contenido_salida_impedimentos_c101(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_salida_impedime_20fd408534_k ON ret_contenido_salida_impedimentos_c101(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c01_indices_valoraciones_c102_evaluacion_i ON ret_requisitos_c01_indices_valoraciones_c102(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c01_indices_va_70e5871d85_k ON ret_requisitos_c01_indices_valoraciones_c102(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c01_motivos_c103_evaluacion_i ON ret_requisitos_c01_motivos_c103(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c01_motivos_c1_4fadd61964_k ON ret_requisitos_c01_motivos_c103(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c02_derivado_de_c104_evaluacion_i ON ret_requisitos_c02_derivado_de_c104(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c02_derivado_d_3072888379_k ON ret_requisitos_c02_derivado_de_c104(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c02_motivos_c105_evaluacion_i ON ret_requisitos_c02_motivos_c105(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c02_motivos_c1_ecebf4c500_k ON ret_requisitos_c02_motivos_c105(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c03_indices_valoraciones_c106_evaluacion_i ON ret_requisitos_c03_indices_valoraciones_c106(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c03_indices_va_1dca034e37_k ON ret_requisitos_c03_indices_valoraciones_c106(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c03_motivos_c107_evaluacion_i ON ret_requisitos_c03_motivos_c107(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c03_motivos_c1_767edc0e96_k ON ret_requisitos_c03_motivos_c107(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c04_indices_valoraciones_c108_evaluacion_i ON ret_requisitos_c04_indices_valoraciones_c108(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c04_indices_va_a459a05e6d_k ON ret_requisitos_c04_indices_valoraciones_c108(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c04_motivos_c109_evaluacion_i ON ret_requisitos_c04_motivos_c109(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c04_motivos_c1_39268dd81b_k ON ret_requisitos_c04_motivos_c109(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c05_indices_valoraciones_c110_evaluacion_i ON ret_requisitos_c05_indices_valoraciones_c110(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c05_indices_va_08015a5242_k ON ret_requisitos_c05_indices_valoraciones_c110(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_requisitos_c05_motivos_c111_evaluacion_i ON ret_requisitos_c05_motivos_c111(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_requisitos_c05_motivos_c1_942bb04074_k ON ret_requisitos_c05_motivos_c111(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_salida_valoraciones_excluida_c112_evaluacion_i ON ret_contenido_salida_valoraciones_excluida_c112(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_salida_valoraci_fb4a1a88c4_k ON ret_contenido_salida_valoraciones_excluida_c112(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_valoraciones_excluidas_motivos_c113_evaluacion_i ON ret_salida_valoraciones_excluidas_motivos_c113(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_valoraciones_exclu_cd737ef513_k ON ret_salida_valoraciones_excluidas_motivos_c113(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_salida_valoraciones_excluidas_soportes_c114_evaluacion_i ON ret_salida_valoraciones_excluidas_soportes_c114(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_salida_valoraciones_exclu_ddd7f4878f_k ON ret_salida_valoraciones_excluidas_soportes_c114(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_petersen_contenido_valoraciones_c115_evaluacion_i ON ret_petersen_contenido_valoraciones_c115(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_petersen_contenido_valora_9f678657cb_k ON ret_petersen_contenido_valoraciones_c115(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_valoraciones_dominios_revisa_c116_evaluacion_i ON ret_contenido_valoraciones_dominios_revisa_c116(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_valoraciones_do_e07e0efb61_k ON ret_contenido_valoraciones_dominios_revisa_c116(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_contenido_valoraciones_soportes_c117_evaluacion_i ON ret_contenido_valoraciones_soportes_c117(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_contenido_valoraciones_so_ff1415723d_k ON ret_contenido_valoraciones_soportes_c117(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_extraccion_aplicabilidad_geriatrica_re_c118_evaluacion_i ON ret_extraccion_aplicabilidad_geriatrica_re_c118(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_extraccion_aplicabilidad__a1007dbbc5_k ON ret_extraccion_aplicabilidad_geriatrica_re_c118(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_extraccion_captura_ensayos_c119_evaluacion_i ON ret_extraccion_captura_ensayos_c119(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_extraccion_captura_ensayo_94f06ebb15_k ON ret_extraccion_captura_ensayos_c119(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_captura_original_extraccion_fuentes_en_c120_evaluacion_i ON ret_captura_original_extraccion_fuentes_en_c120(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_captura_original_extracci_9a10b45234_k ON ret_captura_original_extraccion_fuentes_en_c120(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_normalizacion_bruto_ensayos_c121_evaluacion_i ON ret_normalizacion_bruto_ensayos_c121(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_normalizacion_bruto_ensay_9089f6c6d6_k ON ret_normalizacion_bruto_ensayos_c121(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_extraccion_normalizacion_componentes_c122_evaluacion_i ON ret_extraccion_normalizacion_componentes_c122(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_extraccion_normalizacion__543f49e543_k ON ret_extraccion_normalizacion_componentes_c122(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_captura_original_perfil_captura_origin_c123_evaluacion_i ON ret_captura_original_perfil_captura_origin_c123(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_captura_original_perfil_c_d22ab30862_k ON ret_captura_original_perfil_captura_origin_c123(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_datos_originales_captura_original_preg_c124_evaluacion_i ON ret_datos_originales_captura_original_preg_c124(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_datos_originales_captura__fcc745dfdd_k ON ret_datos_originales_captura_original_preg_c124(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_datos_originales_captura_original_resp_c125_evaluacion_i ON ret_datos_originales_captura_original_resp_c125(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_datos_originales_captura__9624fbf4b6_k ON ret_datos_originales_captura_original_resp_c125(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_datos_originales_captura_original_vinc_c126_evaluacion_i ON ret_datos_originales_captura_original_vinc_c126(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_datos_originales_captura__10e8b8fee8_k ON ret_datos_originales_captura_original_vinc_c126(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX ret_release_archivos_c127_evaluacion_i ON ret_release_archivos_c127(cod_evaluacion_experta);

CREATE UNIQUE INDEX ret_release_archivos_c127_2c39b93ac1_k ON ret_release_archivos_c127(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE TRIGGER nominal_insertar BEFORE INSERT ON evaluaciones_nominales
                WHEN NEW.estado<>'ABIERTA'
                BEGIN SELECT RAISE(ABORT,'Debe conservar antes de cerrar'); END;

CREATE TRIGGER release_nominal_update BEFORE UPDATE ON releases_nominales
                    BEGIN SELECT RAISE(ABORT,'Release inmutable'); END;

CREATE TRIGGER release_nominal_delete BEFORE DELETE ON releases_nominales
                    BEGIN SELECT RAISE(ABORT,'Release inmutable'); END;

CREATE TRIGGER entradas_institucionales_insert BEFORE INSERT ON entradas_institucionales
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER entradas_institucionales_update BEFORE UPDATE ON entradas_institucionales
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER entradas_institucionales_delete BEFORE DELETE ON entradas_institucionales
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_capturas_capturas_c002_insert BEFORE INSERT ON ret_contenido_capturas_capturas_c002
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_capturas_capturas_c002_update BEFORE UPDATE ON ret_contenido_capturas_capturas_c002
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_capturas_capturas_c002_delete BEFORE DELETE ON ret_contenido_capturas_capturas_c002
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_aplicabilidad_geriatrica_re_c003_insert BEFORE INSERT ON ret_extraccion_aplicabilidad_geriatrica_re_c003
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_aplicabilidad_geriatrica_re_c003_update BEFORE UPDATE ON ret_extraccion_aplicabilidad_geriatrica_re_c003
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_aplicabilidad_geriatrica_re_c003_delete BEFORE DELETE ON ret_extraccion_aplicabilidad_geriatrica_re_c003
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_captura_ensayos_c004_insert BEFORE INSERT ON ret_extraccion_captura_ensayos_c004
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_captura_ensayos_c004_update BEFORE UPDATE ON ret_extraccion_captura_ensayos_c004
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_captura_ensayos_c004_delete BEFORE DELETE ON ret_extraccion_captura_ensayos_c004
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_extraccion_fuentes_entradas_c005_insert BEFORE INSERT ON ret_capturas_extraccion_fuentes_entradas_c005
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_extraccion_fuentes_entradas_c005_update BEFORE UPDATE ON ret_capturas_extraccion_fuentes_entradas_c005
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_extraccion_fuentes_entradas_c005_delete BEFORE DELETE ON ret_capturas_extraccion_fuentes_entradas_c005
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_normalizacion_bruto_ensayos_c006_insert BEFORE INSERT ON ret_normalizacion_bruto_ensayos_c006
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_normalizacion_bruto_ensayos_c006_update BEFORE UPDATE ON ret_normalizacion_bruto_ensayos_c006
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_normalizacion_bruto_ensayos_c006_delete BEFORE DELETE ON ret_normalizacion_bruto_ensayos_c006
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_normalizacion_componentes_c007_insert BEFORE INSERT ON ret_extraccion_normalizacion_componentes_c007
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_normalizacion_componentes_c007_update BEFORE UPDATE ON ret_extraccion_normalizacion_componentes_c007
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_normalizacion_componentes_c007_delete BEFORE DELETE ON ret_extraccion_normalizacion_componentes_c007
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_motivos_c008_insert BEFORE INSERT ON ret_capturas_capturas_motivos_c008
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_motivos_c008_update BEFORE UPDATE ON ret_capturas_capturas_motivos_c008
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_motivos_c008_delete BEFORE DELETE ON ret_capturas_capturas_motivos_c008
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_perfil_captura_original_selec_c009_insert BEFORE INSERT ON ret_capturas_perfil_captura_original_selec_c009
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_perfil_captura_original_selec_c009_update BEFORE UPDATE ON ret_capturas_perfil_captura_original_selec_c009
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_perfil_captura_original_selec_c009_delete BEFORE DELETE ON ret_capturas_perfil_captura_original_selec_c009
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_preguntas_declarativ_c010_insert BEFORE INSERT ON ret_capturas_capturas_preguntas_declarativ_c010
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_preguntas_declarativ_c010_update BEFORE UPDATE ON ret_capturas_capturas_preguntas_declarativ_c010
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_preguntas_declarativ_c010_delete BEFORE DELETE ON ret_capturas_capturas_preguntas_declarativ_c010
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_respuestas_originale_c011_insert BEFORE INSERT ON ret_capturas_capturas_respuestas_originale_c011
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_respuestas_originale_c011_update BEFORE UPDATE ON ret_capturas_capturas_respuestas_originale_c011
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_respuestas_originale_c011_delete BEFORE DELETE ON ret_capturas_capturas_respuestas_originale_c011
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_vinculos_preguntas_a_c012_insert BEFORE INSERT ON ret_capturas_capturas_vinculos_preguntas_a_c012
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_vinculos_preguntas_a_c012_update BEFORE UPDATE ON ret_capturas_capturas_vinculos_preguntas_a_c012
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_capturas_capturas_vinculos_preguntas_a_c012_delete BEFORE DELETE ON ret_capturas_capturas_vinculos_preguntas_a_c012
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_conocimiento_contratos_relac_c013_insert BEFORE INSERT ON ret_contenido_conocimiento_contratos_relac_c013
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_conocimiento_contratos_relac_c013_update BEFORE UPDATE ON ret_contenido_conocimiento_contratos_relac_c013
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_conocimiento_contratos_relac_c013_delete BEFORE DELETE ON ret_contenido_conocimiento_contratos_relac_c013
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_contratos_relaciones_moda_c014_insert BEFORE INSERT ON ret_conocimiento_contratos_relaciones_moda_c014
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_contratos_relaciones_moda_c014_update BEFORE UPDATE ON ret_conocimiento_contratos_relaciones_moda_c014
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_contratos_relaciones_moda_c014_delete BEFORE DELETE ON ret_conocimiento_contratos_relaciones_moda_c014
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_condiciones_regla__c015_insert BEFORE INSERT ON ret_conocimiento_tablas_condiciones_regla__c015
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_condiciones_regla__c015_update BEFORE UPDATE ON ret_conocimiento_tablas_condiciones_regla__c015
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_condiciones_regla__c015_delete BEFORE DELETE ON ret_conocimiento_tablas_condiciones_regla__c015
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_consecuencias_regl_c016_insert BEFORE INSERT ON ret_conocimiento_tablas_consecuencias_regl_c016
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_consecuencias_regl_c016_update BEFORE UPDATE ON ret_conocimiento_tablas_consecuencias_regl_c016
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_consecuencias_regl_c016_delete BEFORE DELETE ON ret_conocimiento_tablas_consecuencias_regl_c016
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_criterios_dominios_c017_insert BEFORE INSERT ON ret_conocimiento_tablas_criterios_dominios_c017
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_criterios_dominios_c017_update BEFORE UPDATE ON ret_conocimiento_tablas_criterios_dominios_c017
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_criterios_dominios_c017_delete BEFORE DELETE ON ret_conocimiento_tablas_criterios_dominios_c017
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_dominios_valores_e_c018_insert BEFORE INSERT ON ret_conocimiento_tablas_dominios_valores_e_c018
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_dominios_valores_e_c018_update BEFORE UPDATE ON ret_conocimiento_tablas_dominios_valores_e_c018
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_dominios_valores_e_c018_delete BEFORE DELETE ON ret_conocimiento_tablas_dominios_valores_e_c018
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_fuentes_datos_expe_c019_insert BEFORE INSERT ON ret_conocimiento_tablas_fuentes_datos_expe_c019
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_fuentes_datos_expe_c019_update BEFORE UPDATE ON ret_conocimiento_tablas_fuentes_datos_expe_c019
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_fuentes_datos_expe_c019_delete BEFORE DELETE ON ret_conocimiento_tablas_fuentes_datos_expe_c019
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_mapeos_valores_fue_c020_insert BEFORE INSERT ON ret_conocimiento_tablas_mapeos_valores_fue_c020
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_mapeos_valores_fue_c020_update BEFORE UPDATE ON ret_conocimiento_tablas_mapeos_valores_fue_c020
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_mapeos_valores_fue_c020_delete BEFORE DELETE ON ret_conocimiento_tablas_mapeos_valores_fue_c020
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_mapeos_variables_f_c021_insert BEFORE INSERT ON ret_conocimiento_tablas_mapeos_variables_f_c021
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_mapeos_variables_f_c021_update BEFORE UPDATE ON ret_conocimiento_tablas_mapeos_variables_f_c021
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_mapeos_variables_f_c021_delete BEFORE DELETE ON ret_conocimiento_tablas_mapeos_variables_f_c021
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_nodos_semanticos_c022_insert BEFORE INSERT ON ret_conocimiento_tablas_nodos_semanticos_c022
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_nodos_semanticos_c022_update BEFORE UPDATE ON ret_conocimiento_tablas_nodos_semanticos_c022
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_nodos_semanticos_c022_delete BEFORE DELETE ON ret_conocimiento_tablas_nodos_semanticos_c022
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_reglas_expertas_c023_insert BEFORE INSERT ON ret_conocimiento_tablas_reglas_expertas_c023
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_reglas_expertas_c023_update BEFORE UPDATE ON ret_conocimiento_tablas_reglas_expertas_c023
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_reglas_expertas_c023_delete BEFORE DELETE ON ret_conocimiento_tablas_reglas_expertas_c023
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_relaciones_semanti_c024_insert BEFORE INSERT ON ret_conocimiento_tablas_relaciones_semanti_c024
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_relaciones_semanti_c024_update BEFORE UPDATE ON ret_conocimiento_tablas_relaciones_semanti_c024
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_relaciones_semanti_c024_delete BEFORE DELETE ON ret_conocimiento_tablas_relaciones_semanti_c024
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_valores_semanticos_c025_insert BEFORE INSERT ON ret_conocimiento_tablas_valores_semanticos_c025
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_valores_semanticos_c025_update BEFORE UPDATE ON ret_conocimiento_tablas_valores_semanticos_c025
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_valores_semanticos_c025_delete BEFORE DELETE ON ret_conocimiento_tablas_valores_semanticos_c025
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_variables_expertas_c026_insert BEFORE INSERT ON ret_conocimiento_tablas_variables_expertas_c026
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_variables_expertas_c026_update BEFORE UPDATE ON ret_conocimiento_tablas_variables_expertas_c026
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_variables_expertas_c026_delete BEFORE DELETE ON ret_conocimiento_tablas_variables_expertas_c026
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_fuente_conceptual_nodos_c027_insert BEFORE INSERT ON ret_contenido_fuente_conceptual_nodos_c027
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_fuente_conceptual_nodos_c027_update BEFORE UPDATE ON ret_contenido_fuente_conceptual_nodos_c027
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_fuente_conceptual_nodos_c027_delete BEFORE DELETE ON ret_contenido_fuente_conceptual_nodos_c027
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_fuente_conceptual_nodos_procedencia_c028_insert BEFORE INSERT ON ret_fuente_conceptual_nodos_procedencia_c028
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_fuente_conceptual_nodos_procedencia_c028_update BEFORE UPDATE ON ret_fuente_conceptual_nodos_procedencia_c028
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_fuente_conceptual_nodos_procedencia_c028_delete BEFORE DELETE ON ret_fuente_conceptual_nodos_procedencia_c028
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_fuente_conceptual_relaciones_c029_insert BEFORE INSERT ON ret_contenido_fuente_conceptual_relaciones_c029
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_fuente_conceptual_relaciones_c029_update BEFORE UPDATE ON ret_contenido_fuente_conceptual_relaciones_c029
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_fuente_conceptual_relaciones_c029_delete BEFORE DELETE ON ret_contenido_fuente_conceptual_relaciones_c029
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_comprobacion_adqui_c030_insert BEFORE INSERT ON ret_perfil_instrumental_comprobacion_adqui_c030
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_comprobacion_adqui_c030_update BEFORE UPDATE ON ret_perfil_instrumental_comprobacion_adqui_c030
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_comprobacion_adqui_c030_delete BEFORE DELETE ON ret_perfil_instrumental_comprobacion_adqui_c030
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_aplicaciones_aplicabilidad_requiere_ve_c031_insert BEFORE INSERT ON ret_aplicaciones_aplicabilidad_requiere_ve_c031
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_aplicaciones_aplicabilidad_requiere_ve_c031_update BEFORE UPDATE ON ret_aplicaciones_aplicabilidad_requiere_ve_c031
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_aplicaciones_aplicabilidad_requiere_ve_c031_delete BEFORE DELETE ON ret_aplicaciones_aplicabilidad_requiere_ve_c031
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c032_insert BEFORE INSERT ON ret_comprobacion_adquisicion_aplicaciones__c032
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c032_update BEFORE UPDATE ON ret_comprobacion_adquisicion_aplicaciones__c032
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c032_delete BEFORE DELETE ON ret_comprobacion_adquisicion_aplicaciones__c032
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c033_insert BEFORE INSERT ON ret_comprobacion_adquisicion_aplicaciones__c033
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c033_update BEFORE UPDATE ON ret_comprobacion_adquisicion_aplicaciones__c033
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c033_delete BEFORE DELETE ON ret_comprobacion_adquisicion_aplicaciones__c033
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c034_insert BEFORE INSERT ON ret_comprobacion_adquisicion_aplicaciones__c034
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c034_update BEFORE UPDATE ON ret_comprobacion_adquisicion_aplicaciones__c034
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_comprobacion_adquisicion_aplicaciones__c034_delete BEFORE DELETE ON ret_comprobacion_adquisicion_aplicaciones__c034
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_perfil_instrumental_dominios_c035_insert BEFORE INSERT ON ret_salida_perfil_instrumental_dominios_c035
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_perfil_instrumental_dominios_c035_update BEFORE UPDATE ON ret_salida_perfil_instrumental_dominios_c035
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_perfil_instrumental_dominios_c035_delete BEFORE DELETE ON ret_salida_perfil_instrumental_dominios_c035
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_dominios_component_c036_insert BEFORE INSERT ON ret_perfil_instrumental_dominios_component_c036
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_dominios_component_c036_update BEFORE UPDATE ON ret_perfil_instrumental_dominios_component_c036
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_dominios_component_c036_delete BEFORE DELETE ON ret_perfil_instrumental_dominios_component_c036
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_calculos_c037_insert BEFORE INSERT ON ret_componentes_captura_requerida_calculos_c037
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_calculos_c037_update BEFORE UPDATE ON ret_componentes_captura_requerida_calculos_c037
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_calculos_c037_delete BEFORE DELETE ON ret_componentes_captura_requerida_calculos_c037
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_metodos__c038_insert BEFORE INSERT ON ret_componentes_captura_requerida_metodos__c038
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_metodos__c038_update BEFORE UPDATE ON ret_componentes_captura_requerida_metodos__c038
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_metodos__c038_delete BEFORE DELETE ON ret_componentes_captura_requerida_metodos__c038
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_normaliz_c039_insert BEFORE INSERT ON ret_componentes_captura_requerida_normaliz_c039
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_normaliz_c039_update BEFORE UPDATE ON ret_componentes_captura_requerida_normaliz_c039
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_captura_requerida_normaliz_c039_delete BEFORE DELETE ON ret_componentes_captura_requerida_normaliz_c039
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_concepto_procedencia_c040_insert BEFORE INSERT ON ret_componentes_concepto_procedencia_c040
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_concepto_procedencia_c040_update BEFORE UPDATE ON ret_componentes_concepto_procedencia_c040
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_concepto_procedencia_c040_delete BEFORE DELETE ON ret_componentes_concepto_procedencia_c040
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_componentes_mediciones_c041_insert BEFORE INSERT ON ret_dominios_componentes_mediciones_c041
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_componentes_mediciones_c041_update BEFORE UPDATE ON ret_dominios_componentes_mediciones_c041
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_componentes_mediciones_c041_delete BEFORE DELETE ON ret_dominios_componentes_mediciones_c041
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_mediciones_captura_ensayos_c042_insert BEFORE INSERT ON ret_mediciones_captura_ensayos_c042
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_mediciones_captura_ensayos_c042_update BEFORE UPDATE ON ret_mediciones_captura_ensayos_c042
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_mediciones_captura_ensayos_c042_delete BEFORE DELETE ON ret_mediciones_captura_ensayos_c042
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_mediciones_fuentes_entrada_c043_insert BEFORE INSERT ON ret_componentes_mediciones_fuentes_entrada_c043
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_mediciones_fuentes_entrada_c043_update BEFORE UPDATE ON ret_componentes_mediciones_fuentes_entrada_c043
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_mediciones_fuentes_entrada_c043_delete BEFORE DELETE ON ret_componentes_mediciones_fuentes_entrada_c043
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_componentes_motivos_c044_insert BEFORE INSERT ON ret_dominios_componentes_motivos_c044
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_componentes_motivos_c044_update BEFORE UPDATE ON ret_dominios_componentes_motivos_c044
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_componentes_motivos_c044_delete BEFORE DELETE ON ret_dominios_componentes_motivos_c044
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_dominios_motivos_c045_insert BEFORE INSERT ON ret_perfil_instrumental_dominios_motivos_c045
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_dominios_motivos_c045_update BEFORE UPDATE ON ret_perfil_instrumental_dominios_motivos_c045
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_dominios_motivos_c045_delete BEFORE DELETE ON ret_perfil_instrumental_dominios_motivos_c045
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_contratos_c046_insert BEFORE INSERT ON ret_perfil_instrumental_red_contratos_c046
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_contratos_c046_update BEFORE UPDATE ON ret_perfil_instrumental_red_contratos_c046
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_contratos_c046_delete BEFORE DELETE ON ret_perfil_instrumental_red_contratos_c046
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_red_contratos_modalidades_c047_insert BEFORE INSERT ON ret_red_contratos_modalidades_c047
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_red_contratos_modalidades_c047_update BEFORE UPDATE ON ret_red_contratos_modalidades_c047
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_red_contratos_modalidades_c047_delete BEFORE DELETE ON ret_red_contratos_modalidades_c047
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_dominios_c048_insert BEFORE INSERT ON ret_perfil_instrumental_red_dominios_c048
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_dominios_c048_update BEFORE UPDATE ON ret_perfil_instrumental_red_dominios_c048
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_dominios_c048_delete BEFORE DELETE ON ret_perfil_instrumental_red_dominios_c048
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_red_dominios_componentes_c049_insert BEFORE INSERT ON ret_red_dominios_componentes_c049
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_red_dominios_componentes_c049_update BEFORE UPDATE ON ret_red_dominios_componentes_c049
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_red_dominios_componentes_c049_delete BEFORE DELETE ON ret_red_dominios_componentes_c049
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_concepto_procedencia_c050_insert BEFORE INSERT ON ret_componentes_concepto_procedencia_c050
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_concepto_procedencia_c050_update BEFORE UPDATE ON ret_componentes_concepto_procedencia_c050
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_componentes_concepto_procedencia_c050_delete BEFORE DELETE ON ret_componentes_concepto_procedencia_c050
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_concepto_procedencia_c051_insert BEFORE INSERT ON ret_dominios_concepto_procedencia_c051
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_concepto_procedencia_c051_update BEFORE UPDATE ON ret_dominios_concepto_procedencia_c051
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_dominios_concepto_procedencia_c051_delete BEFORE DELETE ON ret_dominios_concepto_procedencia_c051
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_nodos_c052_insert BEFORE INSERT ON ret_perfil_instrumental_red_nodos_c052
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_nodos_c052_update BEFORE UPDATE ON ret_perfil_instrumental_red_nodos_c052
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_nodos_c052_delete BEFORE DELETE ON ret_perfil_instrumental_red_nodos_c052
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_relaciones_c053_insert BEFORE INSERT ON ret_perfil_instrumental_red_relaciones_c053
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_relaciones_c053_update BEFORE UPDATE ON ret_perfil_instrumental_red_relaciones_c053
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_instrumental_red_relaciones_c053_delete BEFORE DELETE ON ret_perfil_instrumental_red_relaciones_c053
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_resultado_perfil_c054_insert BEFORE INSERT ON ret_salida_resultado_perfil_c054
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_resultado_perfil_c054_update BEFORE UPDATE ON ret_salida_resultado_perfil_c054
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_resultado_perfil_c054_delete BEFORE DELETE ON ret_salida_resultado_perfil_c054
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_compuerta_institucional_contexto_falta_c055_insert BEFORE INSERT ON ret_compuerta_institucional_contexto_falta_c055
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_compuerta_institucional_contexto_falta_c055_update BEFORE UPDATE ON ret_compuerta_institucional_contexto_falta_c055
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_compuerta_institucional_contexto_falta_c055_delete BEFORE DELETE ON ret_compuerta_institucional_contexto_falta_c055
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_perfil_condiciones_pendiente_c056_insert BEFORE INSERT ON ret_resultado_perfil_condiciones_pendiente_c056
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_perfil_condiciones_pendiente_c056_update BEFORE UPDATE ON ret_resultado_perfil_condiciones_pendiente_c056
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_perfil_condiciones_pendiente_c056_delete BEFORE DELETE ON ret_resultado_perfil_condiciones_pendiente_c056
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_evidencias_c057_insert BEFORE INSERT ON ret_perfil_traza_evidencias_c057
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_evidencias_c057_update BEFORE UPDATE ON ret_perfil_traza_evidencias_c057
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_evidencias_c057_delete BEFORE DELETE ON ret_perfil_traza_evidencias_c057
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c058_insert BEFORE INSERT ON ret_procedencia_instrumental_perfil_interp_c058
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c058_update BEFORE UPDATE ON ret_procedencia_instrumental_perfil_interp_c058
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c058_delete BEFORE DELETE ON ret_procedencia_instrumental_perfil_interp_c058
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c059_insert BEFORE INSERT ON ret_procedencia_instrumental_perfil_interp_c059
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c059_update BEFORE UPDATE ON ret_procedencia_instrumental_perfil_interp_c059
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c059_delete BEFORE DELETE ON ret_procedencia_instrumental_perfil_interp_c059
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_reglas_c060_insert BEFORE INSERT ON ret_perfil_traza_reglas_c060
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_reglas_c060_update BEFORE UPDATE ON ret_perfil_traza_reglas_c060
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_reglas_c060_delete BEFORE DELETE ON ret_perfil_traza_reglas_c060
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_condiciones_c061_insert BEFORE INSERT ON ret_traza_reglas_condiciones_c061
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_condiciones_c061_update BEFORE UPDATE ON ret_traza_reglas_condiciones_c061
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_condiciones_c061_delete BEFORE DELETE ON ret_traza_reglas_condiciones_c061
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_condiciones_soportes_c062_insert BEFORE INSERT ON ret_reglas_condiciones_soportes_c062
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_condiciones_soportes_c062_update BEFORE UPDATE ON ret_reglas_condiciones_soportes_c062
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_condiciones_soportes_c062_delete BEFORE DELETE ON ret_reglas_condiciones_soportes_c062
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_evaluaciones_unidad_c063_insert BEFORE INSERT ON ret_traza_reglas_evaluaciones_unidad_c063
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_evaluaciones_unidad_c063_update BEFORE UPDATE ON ret_traza_reglas_evaluaciones_unidad_c063
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_evaluaciones_unidad_c063_delete BEFORE DELETE ON ret_traza_reglas_evaluaciones_unidad_c063
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_evaluaciones_unidad_condiciones_c064_insert BEFORE INSERT ON ret_reglas_evaluaciones_unidad_condiciones_c064
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_evaluaciones_unidad_condiciones_c064_update BEFORE UPDATE ON ret_reglas_evaluaciones_unidad_condiciones_c064
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_evaluaciones_unidad_condiciones_c064_delete BEFORE DELETE ON ret_reglas_evaluaciones_unidad_condiciones_c064
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_evaluaciones_unidad_condiciones_soport_c065_insert BEFORE INSERT ON ret_evaluaciones_unidad_condiciones_soport_c065
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_evaluaciones_unidad_condiciones_soport_c065_update BEFORE UPDATE ON ret_evaluaciones_unidad_condiciones_soport_c065
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_evaluaciones_unidad_condiciones_soport_c065_delete BEFORE DELETE ON ret_evaluaciones_unidad_condiciones_soport_c065
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_unidades_por_componente_c066_insert BEFORE INSERT ON ret_traza_reglas_unidades_por_componente_c066
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_unidades_por_componente_c066_update BEFORE UPDATE ON ret_traza_reglas_unidades_por_componente_c066
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_traza_reglas_unidades_por_componente_c066_delete BEFORE DELETE ON ret_traza_reglas_unidades_por_componente_c066
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_rutas_c067_insert BEFORE INSERT ON ret_perfil_traza_rutas_c067
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_rutas_c067_update BEFORE UPDATE ON ret_perfil_traza_rutas_c067
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_perfil_traza_rutas_c067_delete BEFORE DELETE ON ret_perfil_traza_rutas_c067
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c068_insert BEFORE INSERT ON ret_procedencia_instrumental_perfil_interp_c068
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c068_update BEFORE UPDATE ON ret_procedencia_instrumental_perfil_interp_c068
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c068_delete BEFORE DELETE ON ret_procedencia_instrumental_perfil_interp_c068
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c069_insert BEFORE INSERT ON ret_procedencia_instrumental_perfil_interp_c069
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c069_update BEFORE UPDATE ON ret_procedencia_instrumental_perfil_interp_c069
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_procedencia_instrumental_perfil_interp_c069_delete BEFORE DELETE ON ret_procedencia_instrumental_perfil_interp_c069
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_componentes_c070_insert BEFORE INSERT ON ret_resultado_red_componentes_c070
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_componentes_c070_update BEFORE UPDATE ON ret_resultado_red_componentes_c070
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_componentes_c070_delete BEFORE DELETE ON ret_resultado_red_componentes_c070
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_dominios_c071_insert BEFORE INSERT ON ret_resultado_red_dominios_c071
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_dominios_c071_update BEFORE UPDATE ON ret_resultado_red_dominios_c071
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_dominios_c071_delete BEFORE DELETE ON ret_resultado_red_dominios_c071
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_rutas_reglas_c072_insert BEFORE INSERT ON ret_resultado_red_rutas_reglas_c072
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_rutas_reglas_c072_update BEFORE UPDATE ON ret_resultado_red_rutas_reglas_c072
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_rutas_reglas_c072_delete BEFORE DELETE ON ret_resultado_red_rutas_reglas_c072
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_rutas_reglas_c073_insert BEFORE INSERT ON ret_resultado_red_rutas_reglas_c073
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_rutas_reglas_c073_update BEFORE UPDATE ON ret_resultado_red_rutas_reglas_c073
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_resultado_red_rutas_reglas_c073_delete BEFORE DELETE ON ret_resultado_red_rutas_reglas_c073
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_instrumental_contenido_vinculos_c074_insert BEFORE INSERT ON ret_instrumental_contenido_vinculos_c074
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_instrumental_contenido_vinculos_c074_update BEFORE UPDATE ON ret_instrumental_contenido_vinculos_c074
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_instrumental_contenido_vinculos_c074_delete BEFORE DELETE ON ret_instrumental_contenido_vinculos_c074
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_vinculos_perfil_interpretativo_medicio_c075_insert BEFORE INSERT ON ret_vinculos_perfil_interpretativo_medicio_c075
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_vinculos_perfil_interpretativo_medicio_c075_update BEFORE UPDATE ON ret_vinculos_perfil_interpretativo_medicio_c075
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_vinculos_perfil_interpretativo_medicio_c075_delete BEFORE DELETE ON ret_vinculos_perfil_interpretativo_medicio_c075
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_vinculos_perfil_interpretativo_predica_c076_insert BEFORE INSERT ON ret_vinculos_perfil_interpretativo_predica_c076
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_vinculos_perfil_interpretativo_predica_c076_update BEFORE UPDATE ON ret_vinculos_perfil_interpretativo_predica_c076
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_vinculos_perfil_interpretativo_predica_c076_delete BEFORE DELETE ON ret_vinculos_perfil_interpretativo_predica_c076
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_adquisicion_exclusiones_c077_insert BEFORE INSERT ON ret_contenido_adquisicion_exclusiones_c077
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_adquisicion_exclusiones_c077_update BEFORE UPDATE ON ret_contenido_adquisicion_exclusiones_c077
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_adquisicion_exclusiones_c077_delete BEFORE DELETE ON ret_contenido_adquisicion_exclusiones_c077
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_constancias_documen_c078_insert BEFORE INSERT ON ret_petersen_contenido_constancias_documen_c078
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_constancias_documen_c078_update BEFORE UPDATE ON ret_petersen_contenido_constancias_documen_c078
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_constancias_documen_c078_delete BEFORE DELETE ON ret_petersen_contenido_constancias_documen_c078
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_gobierno_conocimiento_contratos_relaci_c079_insert BEFORE INSERT ON ret_gobierno_conocimiento_contratos_relaci_c079
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_gobierno_conocimiento_contratos_relaci_c079_update BEFORE UPDATE ON ret_gobierno_conocimiento_contratos_relaci_c079
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_gobierno_conocimiento_contratos_relaci_c079_delete BEFORE DELETE ON ret_gobierno_conocimiento_contratos_relaci_c079
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_contratos_relaciones_moda_c080_insert BEFORE INSERT ON ret_conocimiento_contratos_relaciones_moda_c080
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_contratos_relaciones_moda_c080_update BEFORE UPDATE ON ret_conocimiento_contratos_relaciones_moda_c080
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_contratos_relaciones_moda_c080_delete BEFORE DELETE ON ret_conocimiento_contratos_relaciones_moda_c080
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_condiciones_regla__c081_insert BEFORE INSERT ON ret_conocimiento_tablas_condiciones_regla__c081
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_condiciones_regla__c081_update BEFORE UPDATE ON ret_conocimiento_tablas_condiciones_regla__c081
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_condiciones_regla__c081_delete BEFORE DELETE ON ret_conocimiento_tablas_condiciones_regla__c081
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_consecuencias_regl_c082_insert BEFORE INSERT ON ret_conocimiento_tablas_consecuencias_regl_c082
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_consecuencias_regl_c082_update BEFORE UPDATE ON ret_conocimiento_tablas_consecuencias_regl_c082
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_consecuencias_regl_c082_delete BEFORE DELETE ON ret_conocimiento_tablas_consecuencias_regl_c082
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_criterios_dominios_c083_insert BEFORE INSERT ON ret_conocimiento_tablas_criterios_dominios_c083
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_criterios_dominios_c083_update BEFORE UPDATE ON ret_conocimiento_tablas_criterios_dominios_c083
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_criterios_dominios_c083_delete BEFORE DELETE ON ret_conocimiento_tablas_criterios_dominios_c083
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_dominios_valores_e_c084_insert BEFORE INSERT ON ret_conocimiento_tablas_dominios_valores_e_c084
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_dominios_valores_e_c084_update BEFORE UPDATE ON ret_conocimiento_tablas_dominios_valores_e_c084
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_dominios_valores_e_c084_delete BEFORE DELETE ON ret_conocimiento_tablas_dominios_valores_e_c084
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_nodos_semanticos_c085_insert BEFORE INSERT ON ret_conocimiento_tablas_nodos_semanticos_c085
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_nodos_semanticos_c085_update BEFORE UPDATE ON ret_conocimiento_tablas_nodos_semanticos_c085
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_nodos_semanticos_c085_delete BEFORE DELETE ON ret_conocimiento_tablas_nodos_semanticos_c085
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_reglas_expertas_c086_insert BEFORE INSERT ON ret_conocimiento_tablas_reglas_expertas_c086
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_reglas_expertas_c086_update BEFORE UPDATE ON ret_conocimiento_tablas_reglas_expertas_c086
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_reglas_expertas_c086_delete BEFORE DELETE ON ret_conocimiento_tablas_reglas_expertas_c086
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_relaciones_semanti_c087_insert BEFORE INSERT ON ret_conocimiento_tablas_relaciones_semanti_c087
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_relaciones_semanti_c087_update BEFORE UPDATE ON ret_conocimiento_tablas_relaciones_semanti_c087
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_relaciones_semanti_c087_delete BEFORE DELETE ON ret_conocimiento_tablas_relaciones_semanti_c087
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_valores_semanticos_c088_insert BEFORE INSERT ON ret_conocimiento_tablas_valores_semanticos_c088
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_valores_semanticos_c088_update BEFORE UPDATE ON ret_conocimiento_tablas_valores_semanticos_c088
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_valores_semanticos_c088_delete BEFORE DELETE ON ret_conocimiento_tablas_valores_semanticos_c088
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_variables_expertas_c089_insert BEFORE INSERT ON ret_conocimiento_tablas_variables_expertas_c089
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_variables_expertas_c089_update BEFORE UPDATE ON ret_conocimiento_tablas_variables_expertas_c089
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_conocimiento_tablas_variables_expertas_c089_delete BEFORE DELETE ON ret_conocimiento_tablas_variables_expertas_c089
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_membresias_soportes_c090_insert BEFORE INSERT ON ret_petersen_contenido_membresias_soportes_c090
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_membresias_soportes_c090_update BEFORE UPDATE ON ret_petersen_contenido_membresias_soportes_c090
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_membresias_soportes_c090_delete BEFORE DELETE ON ret_petersen_contenido_membresias_soportes_c090
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_membresias_soportes_soportes_c091_insert BEFORE INSERT ON ret_contenido_membresias_soportes_soportes_c091
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_membresias_soportes_soportes_c091_update BEFORE UPDATE ON ret_contenido_membresias_soportes_soportes_c091
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_membresias_soportes_soportes_c091_delete BEFORE DELETE ON ret_contenido_membresias_soportes_soportes_c091
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_procedencia_valorac_c092_insert BEFORE INSERT ON ret_petersen_contenido_procedencia_valorac_c092
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_procedencia_valorac_c092_update BEFORE UPDATE ON ret_petersen_contenido_procedencia_valorac_c092
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_procedencia_valorac_c092_delete BEFORE DELETE ON ret_petersen_contenido_procedencia_valorac_c092
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_dominios_c093_insert BEFORE INSERT ON ret_contenido_salida_dominios_c093
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_dominios_c093_update BEFORE UPDATE ON ret_contenido_salida_dominios_c093
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_dominios_c093_delete BEFORE DELETE ON ret_contenido_salida_dominios_c093
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_dominios_indices_valoraciones_c094_insert BEFORE INSERT ON ret_salida_dominios_indices_valoraciones_c094
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_dominios_indices_valoraciones_c094_update BEFORE UPDATE ON ret_salida_dominios_indices_valoraciones_c094
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_dominios_indices_valoraciones_c094_delete BEFORE DELETE ON ret_salida_dominios_indices_valoraciones_c094
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_dominios_motivos_c095_insert BEFORE INSERT ON ret_salida_dominios_motivos_c095
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_dominios_motivos_c095_update BEFORE UPDATE ON ret_salida_dominios_motivos_c095
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_dominios_motivos_c095_delete BEFORE DELETE ON ret_salida_dominios_motivos_c095
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_bloqueada_por_c096_insert BEFORE INSERT ON ret_salida_gobierno_bloqueada_por_c096
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_bloqueada_por_c096_update BEFORE UPDATE ON ret_salida_gobierno_bloqueada_por_c096
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_bloqueada_por_c096_delete BEFORE DELETE ON ret_salida_gobierno_bloqueada_por_c096
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_estados_derivados_c097_insert BEFORE INSERT ON ret_salida_gobierno_estados_derivados_c097
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_estados_derivados_c097_update BEFORE UPDATE ON ret_salida_gobierno_estados_derivados_c097
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_estados_derivados_c097_delete BEFORE DELETE ON ret_salida_gobierno_estados_derivados_c097
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_reglas_c098_insert BEFORE INSERT ON ret_salida_gobierno_reglas_c098
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_reglas_c098_update BEFORE UPDATE ON ret_salida_gobierno_reglas_c098
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_gobierno_reglas_c098_delete BEFORE DELETE ON ret_salida_gobierno_reglas_c098
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_gobierno_reglas_condiciones_c099_insert BEFORE INSERT ON ret_gobierno_reglas_condiciones_c099
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_gobierno_reglas_condiciones_c099_update BEFORE UPDATE ON ret_gobierno_reglas_condiciones_c099
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_gobierno_reglas_condiciones_c099_delete BEFORE DELETE ON ret_gobierno_reglas_condiciones_c099
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_condiciones_soportes_c100_insert BEFORE INSERT ON ret_reglas_condiciones_soportes_c100
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_condiciones_soportes_c100_update BEFORE UPDATE ON ret_reglas_condiciones_soportes_c100
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_reglas_condiciones_soportes_c100_delete BEFORE DELETE ON ret_reglas_condiciones_soportes_c100
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_impedimentos_c101_insert BEFORE INSERT ON ret_contenido_salida_impedimentos_c101
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_impedimentos_c101_update BEFORE UPDATE ON ret_contenido_salida_impedimentos_c101
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_impedimentos_c101_delete BEFORE DELETE ON ret_contenido_salida_impedimentos_c101
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c01_indices_valoraciones_c102_insert BEFORE INSERT ON ret_requisitos_c01_indices_valoraciones_c102
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c01_indices_valoraciones_c102_update BEFORE UPDATE ON ret_requisitos_c01_indices_valoraciones_c102
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c01_indices_valoraciones_c102_delete BEFORE DELETE ON ret_requisitos_c01_indices_valoraciones_c102
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c01_motivos_c103_insert BEFORE INSERT ON ret_requisitos_c01_motivos_c103
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c01_motivos_c103_update BEFORE UPDATE ON ret_requisitos_c01_motivos_c103
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c01_motivos_c103_delete BEFORE DELETE ON ret_requisitos_c01_motivos_c103
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c02_derivado_de_c104_insert BEFORE INSERT ON ret_requisitos_c02_derivado_de_c104
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c02_derivado_de_c104_update BEFORE UPDATE ON ret_requisitos_c02_derivado_de_c104
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c02_derivado_de_c104_delete BEFORE DELETE ON ret_requisitos_c02_derivado_de_c104
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c02_motivos_c105_insert BEFORE INSERT ON ret_requisitos_c02_motivos_c105
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c02_motivos_c105_update BEFORE UPDATE ON ret_requisitos_c02_motivos_c105
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c02_motivos_c105_delete BEFORE DELETE ON ret_requisitos_c02_motivos_c105
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c03_indices_valoraciones_c106_insert BEFORE INSERT ON ret_requisitos_c03_indices_valoraciones_c106
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c03_indices_valoraciones_c106_update BEFORE UPDATE ON ret_requisitos_c03_indices_valoraciones_c106
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c03_indices_valoraciones_c106_delete BEFORE DELETE ON ret_requisitos_c03_indices_valoraciones_c106
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c03_motivos_c107_insert BEFORE INSERT ON ret_requisitos_c03_motivos_c107
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c03_motivos_c107_update BEFORE UPDATE ON ret_requisitos_c03_motivos_c107
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c03_motivos_c107_delete BEFORE DELETE ON ret_requisitos_c03_motivos_c107
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c04_indices_valoraciones_c108_insert BEFORE INSERT ON ret_requisitos_c04_indices_valoraciones_c108
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c04_indices_valoraciones_c108_update BEFORE UPDATE ON ret_requisitos_c04_indices_valoraciones_c108
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c04_indices_valoraciones_c108_delete BEFORE DELETE ON ret_requisitos_c04_indices_valoraciones_c108
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c04_motivos_c109_insert BEFORE INSERT ON ret_requisitos_c04_motivos_c109
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c04_motivos_c109_update BEFORE UPDATE ON ret_requisitos_c04_motivos_c109
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c04_motivos_c109_delete BEFORE DELETE ON ret_requisitos_c04_motivos_c109
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c05_indices_valoraciones_c110_insert BEFORE INSERT ON ret_requisitos_c05_indices_valoraciones_c110
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c05_indices_valoraciones_c110_update BEFORE UPDATE ON ret_requisitos_c05_indices_valoraciones_c110
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c05_indices_valoraciones_c110_delete BEFORE DELETE ON ret_requisitos_c05_indices_valoraciones_c110
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c05_motivos_c111_insert BEFORE INSERT ON ret_requisitos_c05_motivos_c111
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c05_motivos_c111_update BEFORE UPDATE ON ret_requisitos_c05_motivos_c111
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_requisitos_c05_motivos_c111_delete BEFORE DELETE ON ret_requisitos_c05_motivos_c111
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_valoraciones_excluida_c112_insert BEFORE INSERT ON ret_contenido_salida_valoraciones_excluida_c112
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_valoraciones_excluida_c112_update BEFORE UPDATE ON ret_contenido_salida_valoraciones_excluida_c112
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_salida_valoraciones_excluida_c112_delete BEFORE DELETE ON ret_contenido_salida_valoraciones_excluida_c112
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_valoraciones_excluidas_motivos_c113_insert BEFORE INSERT ON ret_salida_valoraciones_excluidas_motivos_c113
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_valoraciones_excluidas_motivos_c113_update BEFORE UPDATE ON ret_salida_valoraciones_excluidas_motivos_c113
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_valoraciones_excluidas_motivos_c113_delete BEFORE DELETE ON ret_salida_valoraciones_excluidas_motivos_c113
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_valoraciones_excluidas_soportes_c114_insert BEFORE INSERT ON ret_salida_valoraciones_excluidas_soportes_c114
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_valoraciones_excluidas_soportes_c114_update BEFORE UPDATE ON ret_salida_valoraciones_excluidas_soportes_c114
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_salida_valoraciones_excluidas_soportes_c114_delete BEFORE DELETE ON ret_salida_valoraciones_excluidas_soportes_c114
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_valoraciones_c115_insert BEFORE INSERT ON ret_petersen_contenido_valoraciones_c115
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_valoraciones_c115_update BEFORE UPDATE ON ret_petersen_contenido_valoraciones_c115
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_petersen_contenido_valoraciones_c115_delete BEFORE DELETE ON ret_petersen_contenido_valoraciones_c115
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_valoraciones_dominios_revisa_c116_insert BEFORE INSERT ON ret_contenido_valoraciones_dominios_revisa_c116
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_valoraciones_dominios_revisa_c116_update BEFORE UPDATE ON ret_contenido_valoraciones_dominios_revisa_c116
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_valoraciones_dominios_revisa_c116_delete BEFORE DELETE ON ret_contenido_valoraciones_dominios_revisa_c116
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_valoraciones_soportes_c117_insert BEFORE INSERT ON ret_contenido_valoraciones_soportes_c117
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_valoraciones_soportes_c117_update BEFORE UPDATE ON ret_contenido_valoraciones_soportes_c117
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_contenido_valoraciones_soportes_c117_delete BEFORE DELETE ON ret_contenido_valoraciones_soportes_c117
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_aplicabilidad_geriatrica_re_c118_insert BEFORE INSERT ON ret_extraccion_aplicabilidad_geriatrica_re_c118
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_aplicabilidad_geriatrica_re_c118_update BEFORE UPDATE ON ret_extraccion_aplicabilidad_geriatrica_re_c118
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_aplicabilidad_geriatrica_re_c118_delete BEFORE DELETE ON ret_extraccion_aplicabilidad_geriatrica_re_c118
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_captura_ensayos_c119_insert BEFORE INSERT ON ret_extraccion_captura_ensayos_c119
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_captura_ensayos_c119_update BEFORE UPDATE ON ret_extraccion_captura_ensayos_c119
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_captura_ensayos_c119_delete BEFORE DELETE ON ret_extraccion_captura_ensayos_c119
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_captura_original_extraccion_fuentes_en_c120_insert BEFORE INSERT ON ret_captura_original_extraccion_fuentes_en_c120
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_captura_original_extraccion_fuentes_en_c120_update BEFORE UPDATE ON ret_captura_original_extraccion_fuentes_en_c120
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_captura_original_extraccion_fuentes_en_c120_delete BEFORE DELETE ON ret_captura_original_extraccion_fuentes_en_c120
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_normalizacion_bruto_ensayos_c121_insert BEFORE INSERT ON ret_normalizacion_bruto_ensayos_c121
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_normalizacion_bruto_ensayos_c121_update BEFORE UPDATE ON ret_normalizacion_bruto_ensayos_c121
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_normalizacion_bruto_ensayos_c121_delete BEFORE DELETE ON ret_normalizacion_bruto_ensayos_c121
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_normalizacion_componentes_c122_insert BEFORE INSERT ON ret_extraccion_normalizacion_componentes_c122
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_normalizacion_componentes_c122_update BEFORE UPDATE ON ret_extraccion_normalizacion_componentes_c122
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_extraccion_normalizacion_componentes_c122_delete BEFORE DELETE ON ret_extraccion_normalizacion_componentes_c122
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_captura_original_perfil_captura_origin_c123_insert BEFORE INSERT ON ret_captura_original_perfil_captura_origin_c123
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_captura_original_perfil_captura_origin_c123_update BEFORE UPDATE ON ret_captura_original_perfil_captura_origin_c123
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_captura_original_perfil_captura_origin_c123_delete BEFORE DELETE ON ret_captura_original_perfil_captura_origin_c123
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_preg_c124_insert BEFORE INSERT ON ret_datos_originales_captura_original_preg_c124
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_preg_c124_update BEFORE UPDATE ON ret_datos_originales_captura_original_preg_c124
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_preg_c124_delete BEFORE DELETE ON ret_datos_originales_captura_original_preg_c124
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_resp_c125_insert BEFORE INSERT ON ret_datos_originales_captura_original_resp_c125
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_resp_c125_update BEFORE UPDATE ON ret_datos_originales_captura_original_resp_c125
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_resp_c125_delete BEFORE DELETE ON ret_datos_originales_captura_original_resp_c125
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_vinc_c126_insert BEFORE INSERT ON ret_datos_originales_captura_original_vinc_c126
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_vinc_c126_update BEFORE UPDATE ON ret_datos_originales_captura_original_vinc_c126
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_datos_originales_captura_original_vinc_c126_delete BEFORE DELETE ON ret_datos_originales_captura_original_vinc_c126
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_release_archivos_c127_insert BEFORE INSERT ON ret_release_archivos_c127
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_release_archivos_c127_update BEFORE UPDATE ON ret_release_archivos_c127
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA' OR (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER ret_release_archivos_c127_delete BEFORE DELETE ON ret_release_archivos_c127
                        WHEN (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta)='CERRADA'
                        BEGIN SELECT RAISE(ABORT,'Evaluación cerrada'); END;

CREATE TRIGGER nominal_validar_cierre BEFORE UPDATE ON evaluaciones_nominales
                WHEN OLD.estado='CERRADA' OR NEW.estado<>'CERRADA' OR NEW.cod_evaluacion_experta IS NOT OLD.cod_evaluacion_experta OR NEW.solicitud_uuid IS NOT OLD.solicitud_uuid OR NEW.cod_residente IS NOT OLD.cod_residente OR NEW.cod_usuario IS NOT OLD.cod_usuario OR NEW.huella_entrada IS NOT OLD.huella_entrada OR NEW.huella_compuesta IS NOT OLD.huella_compuesta OR NEW.huella_release IS NOT OLD.huella_release OR NEW.contrato IS NOT OLD.contrato
                BEGIN SELECT RAISE(ABORT,'Transición histórica inválida'); END;

CREATE TRIGGER nominal_sin_borrado BEFORE DELETE ON evaluaciones_nominales
                BEGIN SELECT RAISE(ABORT,'No se elimina historia'); END;

CREATE TRIGGER nominal_cierre_completo BEFORE UPDATE ON evaluaciones_nominales
                WHEN NEW.estado='CERRADA' AND ((SELECT count(*) FROM entradas_institucionales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)<>1 OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_contenido_capturas_capturas_c002 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_capturas_capturas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_capturas_capturas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_capturas_capturas__cantidad)-1 FROM ret_contenido_capturas_capturas_c002 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_contenido_conocimiento_contratos_relac_c013 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_contratos_relaciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_contratos_relaciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_contratos_relaciones__cantidad)-1 FROM ret_contenido_conocimiento_contratos_relac_c013 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_condiciones_regla__c015 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_condiciones_regla_experta__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_condiciones_regla_experta__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_condiciones_regla_experta__cantidad)-1 FROM ret_conocimiento_tablas_condiciones_regla__c015 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_consecuencias_regl_c016 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_consecuencias_regla_experta__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_consecuencias_regla_experta__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_consecuencias_regla_experta__cantidad)-1 FROM ret_conocimiento_tablas_consecuencias_regl_c016 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_criterios_dominios_c017 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_criterios_dominios_r__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_criterios_dominios_r__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_criterios_dominios_r__cantidad)-1 FROM ret_conocimiento_tablas_criterios_dominios_c017 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_dominios_valores_e_c018 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_dominios_valores_expertos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_dominios_valores_expertos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_dominios_valores_expertos__cantidad)-1 FROM ret_conocimiento_tablas_dominios_valores_e_c018 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_fuentes_datos_expe_c019 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_fuentes_datos_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_fuentes_datos_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_fuentes_datos_expertas__cantidad)-1 FROM ret_conocimiento_tablas_fuentes_datos_expe_c019 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_mapeos_valores_fue_c020 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_mapeos_valores_fuente__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_mapeos_valores_fuente__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_mapeos_valores_fuente__cantidad)-1 FROM ret_conocimiento_tablas_mapeos_valores_fue_c020 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_mapeos_variables_f_c021 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_mapeos_variables_fuente__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_mapeos_variables_fuente__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_mapeos_variables_fuente__cantidad)-1 FROM ret_conocimiento_tablas_mapeos_variables_f_c021 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_nodos_semanticos_c022 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_nodos_semanticos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_nodos_semanticos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_nodos_semanticos__cantidad)-1 FROM ret_conocimiento_tablas_nodos_semanticos_c022 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_reglas_expertas_c023 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_reglas_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_reglas_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_reglas_expertas__cantidad)-1 FROM ret_conocimiento_tablas_reglas_expertas_c023 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_relaciones_semanti_c024 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_relaciones_semanticas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_relaciones_semanticas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_relaciones_semanticas__cantidad)-1 FROM ret_conocimiento_tablas_relaciones_semanti_c024 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_valores_semanticos_c025 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_valores_semanticos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_valores_semanticos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_valores_semanticos__cantidad)-1 FROM ret_conocimiento_tablas_valores_semanticos_c025 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_variables_expertas_c026 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_k_tablas_variables_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_variables_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_variables_expertas__cantidad)-1 FROM ret_conocimiento_tablas_variables_expertas_c026 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_contenido_fuente_conceptual_nodos_c027 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_fuente_conceptual_nodos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_fuente_conceptual_nodos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_fuente_conceptual_nodos__cantidad)-1 FROM ret_contenido_fuente_conceptual_nodos_c027 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_contenido_fuente_conceptual_relaciones_c029 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_fuente_conceptual_relaciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_fuente_conceptual_relaciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_fuente_conceptual_relaciones__cantidad)-1 FROM ret_contenido_fuente_conceptual_relaciones_c029 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_perfil_instrumental_comprobacion_adqui_c030 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_perfil_adquisicion_aplicaciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_perfil_adquisicion_aplicaciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_perfil_adquisicion_aplicaciones__cantidad)-1 FROM ret_perfil_instrumental_comprobacion_adqui_c030 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_salida_perfil_instrumental_dominios_c035 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_perfil_dominios__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_perfil_dominios__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_perfil_dominios__cantidad)-1 FROM ret_salida_perfil_instrumental_dominios_c035 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_perfil_instrumental_red_contratos_c046 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_perfil_red_contratos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_perfil_red_contratos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_perfil_red_contratos__cantidad)-1 FROM ret_perfil_instrumental_red_contratos_c046 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_perfil_instrumental_red_dominios_c048 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_perfil_red_dominios__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_perfil_red_dominios__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_perfil_red_dominios__cantidad)-1 FROM ret_perfil_instrumental_red_dominios_c048 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_perfil_instrumental_red_nodos_c052 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_perfil_red_nodos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_perfil_red_nodos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_perfil_red_nodos__cantidad)-1 FROM ret_perfil_instrumental_red_nodos_c052 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_perfil_instrumental_red_relaciones_c053 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_perfil_red_relaciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_perfil_red_relaciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_perfil_red_relaciones__cantidad)-1 FROM ret_perfil_instrumental_red_relaciones_c053 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_salida_resultado_perfil_c054 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_r_perfil__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_r_perfil__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_r_perfil__cantidad)-1 FROM ret_salida_resultado_perfil_c054 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_resultado_red_componentes_c070 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_r_red_componentes__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_r_red_componentes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_r_red_componentes__cantidad)-1 FROM ret_resultado_red_componentes_c070 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_resultado_red_dominios_c071 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_r_red_dominios__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_r_red_dominios__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_r_red_dominios__cantidad)-1 FROM ret_resultado_red_dominios_c071 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_resultado_red_rutas_reglas_c072 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_s_r_red_rutas_reglas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_s_r_red_rutas_reglas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_s_r_red_rutas_reglas__cantidad)-1 FROM ret_resultado_red_rutas_reglas_c072 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_instrumental_contenido_vinculos_c074 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.i_vinculos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_vinculos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_vinculos__cantidad)-1 FROM ret_instrumental_contenido_vinculos_c074 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_contenido_adquisicion_exclusiones_c077 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_adquisicion_exclusiones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_adquisicion_exclusiones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_adquisicion_exclusiones__cantidad)-1 FROM ret_contenido_adquisicion_exclusiones_c077 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_petersen_contenido_constancias_documen_c078 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_constancias_documentales__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_constancias_documentales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_constancias_documentales__cantidad)-1 FROM ret_petersen_contenido_constancias_documen_c078 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_gobierno_conocimiento_contratos_relaci_c079 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_contratos_relaciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_contratos_relaciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_contratos_relaciones__cantidad)-1 FROM ret_gobierno_conocimiento_contratos_relaci_c079 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_condiciones_regla__c081 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_condiciones_regla_experta__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_condiciones_regla_experta__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_condiciones_regla_experta__cantidad)-1 FROM ret_conocimiento_tablas_condiciones_regla__c081 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_consecuencias_regl_c082 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_consecuencias_regla_experta__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_consecuencias_regla_experta__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_consecuencias_regla_experta__cantidad)-1 FROM ret_conocimiento_tablas_consecuencias_regl_c082 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_criterios_dominios_c083 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_criterios_dominios_r__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_criterios_dominios_r__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_criterios_dominios_r__cantidad)-1 FROM ret_conocimiento_tablas_criterios_dominios_c083 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_dominios_valores_e_c084 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_dominios_valores_expertos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_dominios_valores_expertos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_dominios_valores_expertos__cantidad)-1 FROM ret_conocimiento_tablas_dominios_valores_e_c084 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_nodos_semanticos_c085 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_nodos_semanticos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_nodos_semanticos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_nodos_semanticos__cantidad)-1 FROM ret_conocimiento_tablas_nodos_semanticos_c085 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_reglas_expertas_c086 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_reglas_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_reglas_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_reglas_expertas__cantidad)-1 FROM ret_conocimiento_tablas_reglas_expertas_c086 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_relaciones_semanti_c087 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_relaciones_semanticas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_relaciones_semanticas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_relaciones_semanticas__cantidad)-1 FROM ret_conocimiento_tablas_relaciones_semanti_c087 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_valores_semanticos_c088 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_valores_semanticos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_valores_semanticos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_valores_semanticos__cantidad)-1 FROM ret_conocimiento_tablas_valores_semanticos_c088 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_conocimiento_tablas_variables_expertas_c089 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_gobierno_k_tablas_variables_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_gobierno_k_tablas_variables_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_gobierno_k_tablas_variables_expertas__cantidad)-1 FROM ret_conocimiento_tablas_variables_expertas_c089 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_petersen_contenido_membresias_soportes_c090 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_membresias_soportes__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_membresias_soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_membresias_soportes__cantidad)-1 FROM ret_petersen_contenido_membresias_soportes_c090 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_petersen_contenido_procedencia_valorac_c092 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_procedencia_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_procedencia_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_procedencia_valoraciones__cantidad)-1 FROM ret_petersen_contenido_procedencia_valorac_c092 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_contenido_salida_dominios_c093 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_dominios__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_dominios__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_dominios__cantidad)-1 FROM ret_contenido_salida_dominios_c093 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_salida_gobierno_bloqueada_por_c096 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_gobierno_bloqueada_por__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_gobierno_bloqueada_por__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_gobierno_bloqueada_por__cantidad)-1 FROM ret_salida_gobierno_bloqueada_por_c096 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_salida_gobierno_estados_derivados_c097 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_gobierno_estados_derivados__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_gobierno_estados_derivados__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_gobierno_estados_derivados__cantidad)-1 FROM ret_salida_gobierno_estados_derivados_c097 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_salida_gobierno_reglas_c098 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_gobierno_reglas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_gobierno_reglas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_gobierno_reglas__cantidad)-1 FROM ret_salida_gobierno_reglas_c098 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_contenido_salida_impedimentos_c101 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_impedimentos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_impedimentos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_impedimentos__cantidad)-1 FROM ret_contenido_salida_impedimentos_c101 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c01_indices_valoraciones_c102 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c01_indices_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c01_indices_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c01_indices_valoraciones__cantidad)-1 FROM ret_requisitos_c01_indices_valoraciones_c102 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c01_motivos_c103 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c01_motivos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c01_motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c01_motivos__cantidad)-1 FROM ret_requisitos_c01_motivos_c103 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c02_derivado_de_c104 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c02_derivado_de__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c02_derivado_de__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c02_derivado_de__cantidad)-1 FROM ret_requisitos_c02_derivado_de_c104 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c02_motivos_c105 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c02_motivos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c02_motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c02_motivos__cantidad)-1 FROM ret_requisitos_c02_motivos_c105 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c03_indices_valoraciones_c106 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c03_indices_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c03_indices_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c03_indices_valoraciones__cantidad)-1 FROM ret_requisitos_c03_indices_valoraciones_c106 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c03_motivos_c107 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c03_motivos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c03_motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c03_motivos__cantidad)-1 FROM ret_requisitos_c03_motivos_c107 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c04_indices_valoraciones_c108 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c04_indices_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c04_indices_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c04_indices_valoraciones__cantidad)-1 FROM ret_requisitos_c04_indices_valoraciones_c108 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c04_motivos_c109 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c04_motivos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c04_motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c04_motivos__cantidad)-1 FROM ret_requisitos_c04_motivos_c109 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c05_indices_valoraciones_c110 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c05_indices_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c05_indices_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c05_indices_valoraciones__cantidad)-1 FROM ret_requisitos_c05_indices_valoraciones_c110 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_requisitos_c05_motivos_c111 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_requisitos_c05_motivos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_requisitos_c05_motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_requisitos_c05_motivos__cantidad)-1 FROM ret_requisitos_c05_motivos_c111 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_contenido_salida_valoraciones_excluida_c112 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_s_valoraciones_excluidas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_s_valoraciones_excluidas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_s_valoraciones_excluidas__cantidad)-1 FROM ret_contenido_salida_valoraciones_excluida_c112 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_petersen_contenido_valoraciones_c115 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.p_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_valoraciones__cantidad)-1 FROM ret_petersen_contenido_valoraciones_c115 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM ret_release_archivos_c127 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.release_archivos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.release_archivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.release_archivos__cantidad)-1 FROM ret_release_archivos_c127 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_extraccion_aplicabilidad_geriatrica_re_c003 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.ext_apl_ger_req_ver__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.ext_apl_ger_req_ver__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.ext_apl_ger_req_ver__cantidad)-1 FROM ret_extraccion_aplicabilidad_geriatrica_re_c003 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_extraccion_captura_ensayos_c004 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.extraccion_captura_ensayos__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.extraccion_captura_ensayos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.extraccion_captura_ensayos__cantidad)-1 FROM ret_extraccion_captura_ensayos_c004 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_capturas_extraccion_fuentes_entradas_c005 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.extraccion_fuentes_entradas__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.extraccion_fuentes_entradas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.extraccion_fuentes_entradas__cantidad)-1 FROM ret_capturas_extraccion_fuentes_entradas_c005 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_normalizacion_bruto_ensayos_c006 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.extraccion_normalizacion_bruto_ensayos__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.extraccion_normalizacion_bruto_ensayos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.extraccion_normalizacion_bruto_ensayos__cantidad)-1 FROM ret_normalizacion_bruto_ensayos_c006 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_extraccion_normalizacion_componentes_c007 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.extraccion_normalizacion_componentes__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.extraccion_normalizacion_componentes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.extraccion_normalizacion_componentes__cantidad)-1 FROM ret_extraccion_normalizacion_componentes_c007 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_capturas_capturas_motivos_c008 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.motivos__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.motivos__cantidad)-1 FROM ret_capturas_capturas_motivos_c008 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_capturas_perfil_captura_original_selec_c009 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.perfil_captura_original_selectores__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.perfil_captura_original_selectores__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.perfil_captura_original_selectores__cantidad)-1 FROM ret_capturas_perfil_captura_original_selec_c009 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_capturas_capturas_preguntas_declarativ_c010 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.preguntas_declarativas_originales__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.preguntas_declarativas_originales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.preguntas_declarativas_originales__cantidad)-1 FROM ret_capturas_capturas_preguntas_declarativ_c010 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_capturas_capturas_respuestas_originale_c011 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.respuestas_originales__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.respuestas_originales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.respuestas_originales__cantidad)-1 FROM ret_capturas_capturas_respuestas_originale_c011 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_capturas_capturas_vinculos_preguntas_a_c012 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.vinculos_preguntas_actuales__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_capturas_capturas_c002 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.vinculos_preguntas_actuales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.vinculos_preguntas_actuales__cantidad)-1 FROM ret_capturas_capturas_vinculos_preguntas_a_c012 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_conocimiento_contratos_relac_c013 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_conocimiento_contratos_relaciones_moda_c014 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.modalidades__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_conocimiento_contratos_relac_c013 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.modalidades__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.modalidades__cantidad)-1 FROM ret_conocimiento_contratos_relaciones_moda_c014 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_fuente_conceptual_nodos_c027 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_fuente_conceptual_nodos_procedencia_c028 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.procedencia__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_fuente_conceptual_nodos_c027 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.procedencia__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.procedencia__cantidad)-1 FROM ret_fuente_conceptual_nodos_procedencia_c028 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_comprobacion_adqui_c030 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_aplicaciones_aplicabilidad_requiere_ve_c031 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.aplicabilidad_requiere_verificar__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_comprobacion_adqui_c030 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.aplicabilidad_requiere_verificar__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.aplicabilidad_requiere_verificar__cantidad)-1 FROM ret_aplicaciones_aplicabilidad_requiere_ve_c031 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_comprobacion_adqui_c030 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_comprobacion_adquisicion_aplicaciones__c032 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.calculos__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_comprobacion_adqui_c030 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.calculos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.calculos__cantidad)-1 FROM ret_comprobacion_adquisicion_aplicaciones__c032 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_comprobacion_adqui_c030 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_comprobacion_adquisicion_aplicaciones__c034 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.motivos_lectura__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_comprobacion_adqui_c030 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.motivos_lectura__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.motivos_lectura__cantidad)-1 FROM ret_comprobacion_adquisicion_aplicaciones__c034 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_comprobacion_adquisicion_aplicaciones__c032 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_comprobacion_adquisicion_aplicaciones__c033 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dto__cantidad)) OR EXISTS(SELECT 1 FROM ret_comprobacion_adquisicion_aplicaciones__c032 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dto__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dto__cantidad)-1 FROM ret_comprobacion_adquisicion_aplicaciones__c033 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_salida_perfil_instrumental_dominios_c035 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_perfil_instrumental_dominios_component_c036 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.componentes__cantidad)) OR EXISTS(SELECT 1 FROM ret_salida_perfil_instrumental_dominios_c035 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.componentes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.componentes__cantidad)-1 FROM ret_perfil_instrumental_dominios_component_c036 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_salida_perfil_instrumental_dominios_c035 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_perfil_instrumental_dominios_motivos_c045 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.motivos__cantidad)) OR EXISTS(SELECT 1 FROM ret_salida_perfil_instrumental_dominios_c035 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.motivos__cantidad)-1 FROM ret_perfil_instrumental_dominios_motivos_c045 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_componentes_captura_requerida_calculos_c037 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.captura_requerida_calculos__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.captura_requerida_calculos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.captura_requerida_calculos__cantidad)-1 FROM ret_componentes_captura_requerida_calculos_c037 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_componentes_captura_requerida_metodos__c038 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.captura_requerida_metodos_disponibles__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.captura_requerida_metodos_disponibles__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.captura_requerida_metodos_disponibles__cantidad)-1 FROM ret_componentes_captura_requerida_metodos__c038 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_componentes_captura_requerida_normaliz_c039 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.captura_requerida_normalizadores__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.captura_requerida_normalizadores__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.captura_requerida_normalizadores__cantidad)-1 FROM ret_componentes_captura_requerida_normaliz_c039 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_componentes_concepto_procedencia_c040 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.concepto_procedencia__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.concepto_procedencia__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.concepto_procedencia__cantidad)-1 FROM ret_componentes_concepto_procedencia_c040 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_dominios_componentes_mediciones_c041 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.mediciones__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.mediciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.mediciones__cantidad)-1 FROM ret_dominios_componentes_mediciones_c041 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_dominios_componentes_motivos_c044 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.motivos__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_dominios_component_c036 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.motivos__cantidad)-1 FROM ret_dominios_componentes_motivos_c044 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_dominios_componentes_mediciones_c041 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_mediciones_captura_ensayos_c042 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.captura_ensayos__cantidad)) OR EXISTS(SELECT 1 FROM ret_dominios_componentes_mediciones_c041 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.captura_ensayos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.captura_ensayos__cantidad)-1 FROM ret_mediciones_captura_ensayos_c042 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_dominios_componentes_mediciones_c041 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_componentes_mediciones_fuentes_entrada_c043 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.fuentes_entradas__cantidad)) OR EXISTS(SELECT 1 FROM ret_dominios_componentes_mediciones_c041 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.fuentes_entradas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.fuentes_entradas__cantidad)-1 FROM ret_componentes_mediciones_fuentes_entrada_c043 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_red_contratos_c046 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_red_contratos_modalidades_c047 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.modalidades__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_red_contratos_c046 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.modalidades__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.modalidades__cantidad)-1 FROM ret_red_contratos_modalidades_c047 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_red_dominios_c048 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_red_dominios_componentes_c049 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.componentes__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_red_dominios_c048 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.componentes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.componentes__cantidad)-1 FROM ret_red_dominios_componentes_c049 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_red_dominios_c048 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_dominios_concepto_procedencia_c051 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.concepto_procedencia__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_instrumental_red_dominios_c048 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.concepto_procedencia__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.concepto_procedencia__cantidad)-1 FROM ret_dominios_concepto_procedencia_c051 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_red_dominios_componentes_c049 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_componentes_concepto_procedencia_c050 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.concepto_procedencia__cantidad)) OR EXISTS(SELECT 1 FROM ret_red_dominios_componentes_c049 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.concepto_procedencia__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.concepto_procedencia__cantidad)-1 FROM ret_componentes_concepto_procedencia_c050 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_compuerta_institucional_contexto_falta_c055 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.compuerta_institucional_contexto_faltantes__cantidad)) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.compuerta_institucional_contexto_faltantes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.compuerta_institucional_contexto_faltantes__cantidad)-1 FROM ret_compuerta_institucional_contexto_falta_c055 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_resultado_perfil_condiciones_pendiente_c056 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.condiciones_pendientes__cantidad)) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.condiciones_pendientes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.condiciones_pendientes__cantidad)-1 FROM ret_resultado_perfil_condiciones_pendiente_c056 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_perfil_traza_evidencias_c057 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.traza_evidencias__cantidad)) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.traza_evidencias__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.traza_evidencias__cantidad)-1 FROM ret_perfil_traza_evidencias_c057 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_perfil_traza_reglas_c060 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.traza_reglas__cantidad)) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.traza_reglas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.traza_reglas__cantidad)-1 FROM ret_perfil_traza_reglas_c060 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_perfil_traza_rutas_c067 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.traza_rutas__cantidad)) OR EXISTS(SELECT 1 FROM ret_salida_resultado_perfil_c054 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.traza_rutas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.traza_rutas__cantidad)-1 FROM ret_perfil_traza_rutas_c067 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_traza_evidencias_c057 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_procedencia_instrumental_perfil_interp_c058 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.pro_ins_per_int_med_req__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_traza_evidencias_c057 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.pro_ins_per_int_med_req__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.pro_ins_per_int_med_req__cantidad)-1 FROM ret_procedencia_instrumental_perfil_interp_c058 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_traza_evidencias_c057 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_procedencia_instrumental_perfil_interp_c059 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.pro_ins_per_int_pre__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_traza_evidencias_c057 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.pro_ins_per_int_pre__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.pro_ins_per_int_pre__cantidad)-1 FROM ret_procedencia_instrumental_perfil_interp_c059 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_traza_reglas_c060 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_traza_reglas_condiciones_c061 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.condiciones__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_traza_reglas_c060 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.condiciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.condiciones__cantidad)-1 FROM ret_traza_reglas_condiciones_c061 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_traza_reglas_c060 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_traza_reglas_evaluaciones_unidad_c063 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.evaluaciones_unidad__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_traza_reglas_c060 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.evaluaciones_unidad__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.evaluaciones_unidad__cantidad)-1 FROM ret_traza_reglas_evaluaciones_unidad_c063 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_traza_reglas_c060 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_traza_reglas_unidades_por_componente_c066 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.unidades_por_componente__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_traza_reglas_c060 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.unidades_por_componente__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.unidades_por_componente__cantidad)-1 FROM ret_traza_reglas_unidades_por_componente_c066 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_traza_reglas_condiciones_c061 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_reglas_condiciones_soportes_c062 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.soportes__cantidad)) OR EXISTS(SELECT 1 FROM ret_traza_reglas_condiciones_c061 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.soportes__cantidad)-1 FROM ret_reglas_condiciones_soportes_c062 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_traza_reglas_evaluaciones_unidad_c063 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_reglas_evaluaciones_unidad_condiciones_c064 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.condiciones__cantidad)) OR EXISTS(SELECT 1 FROM ret_traza_reglas_evaluaciones_unidad_c063 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.condiciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.condiciones__cantidad)-1 FROM ret_reglas_evaluaciones_unidad_condiciones_c064 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_reglas_evaluaciones_unidad_condiciones_c064 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_evaluaciones_unidad_condiciones_soport_c065 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.soportes__cantidad)) OR EXISTS(SELECT 1 FROM ret_reglas_evaluaciones_unidad_condiciones_c064 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.soportes__cantidad)-1 FROM ret_evaluaciones_unidad_condiciones_soport_c065 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_traza_rutas_c067 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_procedencia_instrumental_perfil_interp_c068 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.pro_ins_per_int_med_req__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_traza_rutas_c067 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.pro_ins_per_int_med_req__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.pro_ins_per_int_med_req__cantidad)-1 FROM ret_procedencia_instrumental_perfil_interp_c068 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_perfil_traza_rutas_c067 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_procedencia_instrumental_perfil_interp_c069 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.pro_ins_per_int_pre__cantidad)) OR EXISTS(SELECT 1 FROM ret_perfil_traza_rutas_c067 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.pro_ins_per_int_pre__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.pro_ins_per_int_pre__cantidad)-1 FROM ret_procedencia_instrumental_perfil_interp_c069 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_resultado_red_rutas_reglas_c072 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_resultado_red_rutas_reglas_c073 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dto__cantidad)) OR EXISTS(SELECT 1 FROM ret_resultado_red_rutas_reglas_c072 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dto__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dto__cantidad)-1 FROM ret_resultado_red_rutas_reglas_c073 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_instrumental_contenido_vinculos_c074 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_vinculos_perfil_interpretativo_medicio_c075 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.perfil_interpretativo_mediciones_requeridas__cantidad)) OR EXISTS(SELECT 1 FROM ret_instrumental_contenido_vinculos_c074 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.perfil_interpretativo_mediciones_requeridas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.perfil_interpretativo_mediciones_requeridas__cantidad)-1 FROM ret_vinculos_perfil_interpretativo_medicio_c075 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_instrumental_contenido_vinculos_c074 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_vinculos_perfil_interpretativo_predica_c076 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.perfil_interpretativo_predicados__cantidad)) OR EXISTS(SELECT 1 FROM ret_instrumental_contenido_vinculos_c074 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.perfil_interpretativo_predicados__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.perfil_interpretativo_predicados__cantidad)-1 FROM ret_vinculos_perfil_interpretativo_predica_c076 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_gobierno_conocimiento_contratos_relaci_c079 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_conocimiento_contratos_relaciones_moda_c080 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.modalidades__cantidad)) OR EXISTS(SELECT 1 FROM ret_gobierno_conocimiento_contratos_relaci_c079 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.modalidades__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.modalidades__cantidad)-1 FROM ret_conocimiento_contratos_relaciones_moda_c080 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_petersen_contenido_membresias_soportes_c090 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_contenido_membresias_soportes_soportes_c091 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.soportes__cantidad)) OR EXISTS(SELECT 1 FROM ret_petersen_contenido_membresias_soportes_c090 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.soportes__cantidad)-1 FROM ret_contenido_membresias_soportes_soportes_c091 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_salida_dominios_c093 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_salida_dominios_indices_valoraciones_c094 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.indices_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_salida_dominios_c093 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.indices_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.indices_valoraciones__cantidad)-1 FROM ret_salida_dominios_indices_valoraciones_c094 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_salida_dominios_c093 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_salida_dominios_motivos_c095 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.motivos__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_salida_dominios_c093 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.motivos__cantidad)-1 FROM ret_salida_dominios_motivos_c095 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_salida_gobierno_reglas_c098 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_gobierno_reglas_condiciones_c099 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.condiciones__cantidad)) OR EXISTS(SELECT 1 FROM ret_salida_gobierno_reglas_c098 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.condiciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.condiciones__cantidad)-1 FROM ret_gobierno_reglas_condiciones_c099 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_gobierno_reglas_condiciones_c099 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_reglas_condiciones_soportes_c100 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.soportes__cantidad)) OR EXISTS(SELECT 1 FROM ret_gobierno_reglas_condiciones_c099 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.soportes__cantidad)-1 FROM ret_reglas_condiciones_soportes_c100 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_salida_valoraciones_excluida_c112 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_salida_valoraciones_excluidas_motivos_c113 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.motivos__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_salida_valoraciones_excluida_c112 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.motivos__cantidad)-1 FROM ret_salida_valoraciones_excluidas_motivos_c113 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_salida_valoraciones_excluida_c112 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_salida_valoraciones_excluidas_soportes_c114 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.soportes__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_salida_valoraciones_excluida_c112 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.soportes__cantidad)-1 FROM ret_salida_valoraciones_excluidas_soportes_c114 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_petersen_contenido_valoraciones_c115 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_contenido_valoraciones_dominios_revisa_c116 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dominios_revisados__cantidad)) OR EXISTS(SELECT 1 FROM ret_petersen_contenido_valoraciones_c115 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dominios_revisados__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dominios_revisados__cantidad)-1 FROM ret_contenido_valoraciones_dominios_revisa_c116 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_petersen_contenido_valoraciones_c115 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_contenido_valoraciones_soportes_c117 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.soportes__cantidad)) OR EXISTS(SELECT 1 FROM ret_petersen_contenido_valoraciones_c115 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.soportes__cantidad)-1 FROM ret_contenido_valoraciones_soportes_c117 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_extraccion_aplicabilidad_geriatrica_re_c118 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_ext_apl_ger_req_ver__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_ext_apl_ger_req_ver__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_ext_apl_ger_req_ver__cantidad)-1 FROM ret_extraccion_aplicabilidad_geriatrica_re_c118 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_extraccion_captura_ensayos_c119 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_ext_cap_ens__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_ext_cap_ens__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_ext_cap_ens__cantidad)-1 FROM ret_extraccion_captura_ensayos_c119 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_captura_original_extraccion_fuentes_en_c120 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_ext_fue_ent__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_ext_fue_ent__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_ext_fue_ent__cantidad)-1 FROM ret_captura_original_extraccion_fuentes_en_c120 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_normalizacion_bruto_ensayos_c121 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_ext_nor_bru_ens__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_ext_nor_bru_ens__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_ext_nor_bru_ens__cantidad)-1 FROM ret_normalizacion_bruto_ensayos_c121 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_extraccion_normalizacion_componentes_c122 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_ext_nor_com__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_ext_nor_com__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_ext_nor_com__cantidad)-1 FROM ret_extraccion_normalizacion_componentes_c122 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_captura_original_perfil_captura_origin_c123 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_per_cap_ori_sel__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_per_cap_ori_sel__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_per_cap_ori_sel__cantidad)-1 FROM ret_captura_original_perfil_captura_origin_c123 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_datos_originales_captura_original_preg_c124 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_pre_dec_ori__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_pre_dec_ori__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_pre_dec_ori__cantidad)-1 FROM ret_datos_originales_captura_original_preg_c124 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_datos_originales_captura_original_resp_c125 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_res_ori__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_res_ori__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_res_ori__cantidad)-1 FROM ret_datos_originales_captura_original_resp_c125 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (SELECT count(*) FROM ret_datos_originales_captura_original_vinc_c126 h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')<>(p.dat_ori_cap_ori_vin_pre_act__cantidad)) OR EXISTS(SELECT 1 FROM ret_contenido_valoraciones_soportes_c117 p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('COLECCION') AND (p.dat_ori_cap_ori_vin_pre_act__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dat_ori_cap_ori_vin_pre_act__cantidad)-1 FROM ret_datos_originales_captura_original_vinc_c126 h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='COLECCION')))
                BEGIN SELECT RAISE(ABORT,'Conservación incompleta'); END;
