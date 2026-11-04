-- ENSAYO NO INSTALABLE: supera el máximo institucional de 110 tablas.
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
            contrato VARCHAR(40) NOT NULL CHECK(contrato='ENTRADAS-NOMINALES-1'),
            estado VARCHAR(8) NOT NULL CHECK(estado IN ('ABIERTA','CERRADA'))
        );

CREATE TABLE entradas_institucionales (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    instrumental__estado VARCHAR(8) NOT NULL CHECK(instrumental__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_cabecera__estado VARCHAR(8) NOT NULL CHECK(i_cabecera__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_cabecera_cod_usuario_creacion__presente BOOLEAN NOT NULL,
    i_cabecera_cod_usuario_creacion TEXT,
    i_cabecera_cod_version_anterior__presente BOOLEAN NOT NULL,
    i_cabecera_cod_version_anterior TEXT,
    i_cabecera_cod_version_modelo__presente BOOLEAN NOT NULL,
    i_cabecera_cod_version_modelo TEXT,
    i_cabecera_codigo_version__presente BOOLEAN NOT NULL,
    i_cabecera_codigo_version TEXT,
    i_cabecera_descripcion__presente BOOLEAN NOT NULL,
    i_cabecera_descripcion TEXT,
    i_cabecera_estado__presente BOOLEAN NOT NULL,
    i_cabecera_estado TEXT,
    i_cabecera_fecha_hora_creacion__presente BOOLEAN NOT NULL,
    i_cabecera_fecha_hora_creacion TEXT,
    i_cabecera_fecha_hora_retiro__presente BOOLEAN NOT NULL,
    i_cabecera_fecha_hora_retiro TEXT,
    i_cabecera_fecha_hora_vigencia__presente BOOLEAN NOT NULL,
    i_cabecera_fecha_hora_vigencia TEXT,
    i_cabecera_motivo_cambio__presente BOOLEAN NOT NULL,
    i_cabecera_motivo_cambio TEXT,
    i_cabecera_nombre__presente BOOLEAN NOT NULL,
    i_cabecera_nombre TEXT,
    i_cabecera_observacion__presente BOOLEAN NOT NULL,
    i_cabecera_observacion TEXT,
    instrumental_capturas__estado VARCHAR(8) NOT NULL CHECK(instrumental_capturas__estado IN ('AUSENTE','NULL','PRESENTE')),
    instrumental_capturas_capturas__estado VARCHAR(8) NOT NULL CHECK(instrumental_capturas_capturas__estado IN ('AUSENTE','NULL','PRESENTE')),
    instrumental_capturas_capturas__cantidad BIGINT NOT NULL CHECK(instrumental_capturas_capturas__cantidad>=0),
    instrumental_capturas_disponible__presente BOOLEAN NOT NULL,
    instrumental_capturas_disponible BOOLEAN,
    instrumental_capturas_fecha_corte__presente BOOLEAN NOT NULL,
    instrumental_capturas_fecha_corte TEXT,
    instrumental_capturas_persistido__presente BOOLEAN NOT NULL,
    instrumental_capturas_persistido BOOLEAN,
    i_k__estado VARCHAR(8) NOT NULL CHECK(i_k__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_contratos_extraccion__estado VARCHAR(8) NOT NULL CHECK(i_k_contratos_extraccion__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_contratos_extraccion_respuesta_institucional__presente BOOLEAN NOT NULL,
    i_k_contratos_extraccion_respuesta_institucional TEXT,
    i_k_contratos_extraccion_respuesta_original__presente BOOLEAN NOT NULL,
    i_k_contratos_extraccion_respuesta_original TEXT,
    i_k_contratos_relaciones__estado VARCHAR(8) NOT NULL CHECK(i_k_contratos_relaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_contratos_relaciones__cantidad BIGINT NOT NULL CHECK(i_k_contratos_relaciones__cantidad>=0),
    i_k_solo_pruebas_tecnicas__presente BOOLEAN NOT NULL,
    i_k_solo_pruebas_tecnicas BOOLEAN,
    i_k_tablas__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_condiciones_regla_experta__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_condiciones_regla_experta__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_condiciones_regla_experta__cantidad BIGINT NOT NULL CHECK(i_k_tablas_condiciones_regla_experta__cantidad>=0),
    i_k_tablas_consecuencias_regla_experta__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_consecuencias_regla_experta__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_consecuencias_regla_experta__cantidad BIGINT NOT NULL CHECK(i_k_tablas_consecuencias_regla_experta__cantidad>=0),
    i_k_tablas_criterios_dominios_resultado__estado VARCHAR(8) NOT NULL CHECK(i_k_tablas_criterios_dominios_resultado__estado IN ('AUSENTE','NULL','PRESENTE')),
    i_k_tablas_criterios_dominios_resultado__cantidad BIGINT NOT NULL CHECK(i_k_tablas_criterios_dominios_resultado__cantidad>=0),
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
    i_k_tipo_regla_resultado__presente BOOLEAN NOT NULL,
    i_k_tipo_regla_resultado TEXT,
    i_k_version__presente BOOLEAN NOT NULL,
    i_k_version TEXT,
    instrumental_contexto__estado VARCHAR(8) NOT NULL CHECK(instrumental_contexto__estado IN ('AUSENTE','NULL','PRESENTE')),
    instrumental_contexto_evaluacion__presente BOOLEAN NOT NULL,
    instrumental_contexto_evaluacion TEXT,
    instrumental_contexto_fecha_corte__estado VARCHAR(8) NOT NULL CHECK(instrumental_contexto_fecha_corte__estado IN ('AUSENTE','NULL','PRESENTE')),
    instrumental_contexto_fecha_corte_corte_offset__presente BOOLEAN NOT NULL,
    instrumental_contexto_fecha_corte_corte_offset BIGINT,
    instrumental_contexto_fecha_corte_corte_utc__presente BOOLEAN NOT NULL,
    instrumental_contexto_fecha_corte_corte_utc TEXT,
    instrumental_contexto_fecha_corte_corte_zona__presente BOOLEAN NOT NULL,
    instrumental_contexto_fecha_corte_corte_zona TEXT,
    instrumental_contexto_residente__presente BOOLEAN NOT NULL,
    instrumental_contexto_residente TEXT,
    instrumental_contexto_usuario_ejecucion__presente BOOLEAN NOT NULL,
    instrumental_contexto_usuario_ejecucion TEXT,
    instrumental_contexto_version__presente BOOLEAN NOT NULL,
    instrumental_contexto_version TEXT,
    instrumental_contrato__presente BOOLEAN NOT NULL,
    instrumental_contrato TEXT,
    instrumental_vinculos__estado VARCHAR(8) NOT NULL CHECK(instrumental_vinculos__estado IN ('AUSENTE','NULL','PRESENTE')),
    instrumental_vinculos__cantidad BIGINT NOT NULL CHECK(instrumental_vinculos__cantidad>=0),
    petersen__estado VARCHAR(8) NOT NULL CHECK(petersen__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_adquisicion__estado VARCHAR(8) NOT NULL CHECK(petersen_adquisicion__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_adquisicion_conservacion_completa__presente BOOLEAN NOT NULL,
    petersen_adquisicion_conservacion_completa BOOLEAN,
    petersen_adquisicion_exclusiones__estado VARCHAR(8) NOT NULL CHECK(petersen_adquisicion_exclusiones__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_adquisicion_exclusiones__cantidad BIGINT NOT NULL CHECK(petersen_adquisicion_exclusiones__cantidad>=0),
    petersen_adquisicion_fuentes_originales_mutables__presente BOOLEAN NOT NULL,
    petersen_adquisicion_fuentes_originales_mutables BOOLEAN,
    petersen_adquisicion_valoraciones_al_corte__presente BOOLEAN NOT NULL,
    petersen_adquisicion_valoraciones_al_corte BIGINT,
    petersen_constancias_documentales__estado VARCHAR(8) NOT NULL CHECK(petersen_constancias_documentales__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_constancias_documentales__cantidad BIGINT NOT NULL CHECK(petersen_constancias_documentales__cantidad>=0),
    petersen_contexto__estado VARCHAR(8) NOT NULL CHECK(petersen_contexto__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_contexto_evaluacion__presente BOOLEAN NOT NULL,
    petersen_contexto_evaluacion TEXT,
    petersen_contexto_fecha_corte__estado VARCHAR(8) NOT NULL CHECK(petersen_contexto_fecha_corte__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_contexto_fecha_corte_corte_offset__presente BOOLEAN NOT NULL,
    petersen_contexto_fecha_corte_corte_offset BIGINT,
    petersen_contexto_fecha_corte_corte_utc__presente BOOLEAN NOT NULL,
    petersen_contexto_fecha_corte_corte_utc TEXT,
    petersen_contexto_fecha_corte_corte_zona__presente BOOLEAN NOT NULL,
    petersen_contexto_fecha_corte_corte_zona TEXT,
    petersen_contexto_residente__presente BOOLEAN NOT NULL,
    petersen_contexto_residente TEXT,
    petersen_contexto_usuario_ejecucion__presente BOOLEAN NOT NULL,
    petersen_contexto_usuario_ejecucion TEXT,
    petersen_contexto_version__presente BOOLEAN NOT NULL,
    petersen_contexto_version TEXT,
    petersen_contrato__presente BOOLEAN NOT NULL,
    petersen_contrato TEXT,
    petersen_gobierno__estado VARCHAR(8) NOT NULL CHECK(petersen_gobierno__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_cabecera__estado VARCHAR(8) NOT NULL CHECK(p_cabecera__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_cabecera_cod_usuario_creacion__presente BOOLEAN NOT NULL,
    p_cabecera_cod_usuario_creacion TEXT,
    p_cabecera_cod_version_anterior__presente BOOLEAN NOT NULL,
    p_cabecera_cod_version_anterior TEXT,
    p_cabecera_cod_version_modelo__presente BOOLEAN NOT NULL,
    p_cabecera_cod_version_modelo TEXT,
    p_cabecera_codigo_version__presente BOOLEAN NOT NULL,
    p_cabecera_codigo_version TEXT,
    p_cabecera_descripcion__presente BOOLEAN NOT NULL,
    p_cabecera_descripcion TEXT,
    p_cabecera_estado__presente BOOLEAN NOT NULL,
    p_cabecera_estado TEXT,
    p_cabecera_fecha_hora_creacion__presente BOOLEAN NOT NULL,
    p_cabecera_fecha_hora_creacion TEXT,
    p_cabecera_fecha_hora_retiro__presente BOOLEAN NOT NULL,
    p_cabecera_fecha_hora_retiro TEXT,
    p_cabecera_fecha_hora_vigencia__presente BOOLEAN NOT NULL,
    p_cabecera_fecha_hora_vigencia TEXT,
    p_cabecera_motivo_cambio__presente BOOLEAN NOT NULL,
    p_cabecera_motivo_cambio TEXT,
    p_cabecera_nombre__presente BOOLEAN NOT NULL,
    p_cabecera_nombre TEXT,
    p_cabecera_observacion__presente BOOLEAN NOT NULL,
    p_cabecera_observacion TEXT,
    p_k__estado VARCHAR(8) NOT NULL CHECK(p_k__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_contratos_extraccion__estado VARCHAR(8) NOT NULL CHECK(p_k_contratos_extraccion__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_contratos_extraccion_respuesta_institucional__presente BOOLEAN NOT NULL,
    p_k_contratos_extraccion_respuesta_institucional TEXT,
    p_k_contratos_extraccion_respuesta_original__presente BOOLEAN NOT NULL,
    p_k_contratos_extraccion_respuesta_original TEXT,
    p_k_contratos_relaciones__estado VARCHAR(8) NOT NULL CHECK(p_k_contratos_relaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_contratos_relaciones__cantidad BIGINT NOT NULL CHECK(p_k_contratos_relaciones__cantidad>=0),
    p_k_solo_pruebas_tecnicas__presente BOOLEAN NOT NULL,
    p_k_solo_pruebas_tecnicas BOOLEAN,
    p_k_tablas__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_condiciones_regla_experta__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_condiciones_regla_experta__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_condiciones_regla_experta__cantidad BIGINT NOT NULL CHECK(p_k_tablas_condiciones_regla_experta__cantidad>=0),
    p_k_tablas_consecuencias_regla_experta__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_consecuencias_regla_experta__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_consecuencias_regla_experta__cantidad BIGINT NOT NULL CHECK(p_k_tablas_consecuencias_regla_experta__cantidad>=0),
    p_k_tablas_criterios_dominios_resultado__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_criterios_dominios_resultado__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_criterios_dominios_resultado__cantidad BIGINT NOT NULL CHECK(p_k_tablas_criterios_dominios_resultado__cantidad>=0),
    p_k_tablas_dominios_valores_expertos__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_dominios_valores_expertos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_dominios_valores_expertos__cantidad BIGINT NOT NULL CHECK(p_k_tablas_dominios_valores_expertos__cantidad>=0),
    p_k_tablas_fuentes_datos_expertas__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_fuentes_datos_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_fuentes_datos_expertas__cantidad BIGINT NOT NULL CHECK(p_k_tablas_fuentes_datos_expertas__cantidad>=0),
    p_k_tablas_mapeos_valores_fuente__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_mapeos_valores_fuente__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_mapeos_valores_fuente__cantidad BIGINT NOT NULL CHECK(p_k_tablas_mapeos_valores_fuente__cantidad>=0),
    p_k_tablas_mapeos_variables_fuente__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_mapeos_variables_fuente__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_mapeos_variables_fuente__cantidad BIGINT NOT NULL CHECK(p_k_tablas_mapeos_variables_fuente__cantidad>=0),
    p_k_tablas_nodos_semanticos__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_nodos_semanticos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_nodos_semanticos__cantidad BIGINT NOT NULL CHECK(p_k_tablas_nodos_semanticos__cantidad>=0),
    p_k_tablas_reglas_expertas__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_reglas_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_reglas_expertas__cantidad BIGINT NOT NULL CHECK(p_k_tablas_reglas_expertas__cantidad>=0),
    p_k_tablas_relaciones_semanticas__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_relaciones_semanticas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_relaciones_semanticas__cantidad BIGINT NOT NULL CHECK(p_k_tablas_relaciones_semanticas__cantidad>=0),
    p_k_tablas_valores_semanticos__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_valores_semanticos__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_valores_semanticos__cantidad BIGINT NOT NULL CHECK(p_k_tablas_valores_semanticos__cantidad>=0),
    p_k_tablas_variables_expertas__estado VARCHAR(8) NOT NULL CHECK(p_k_tablas_variables_expertas__estado IN ('AUSENTE','NULL','PRESENTE')),
    p_k_tablas_variables_expertas__cantidad BIGINT NOT NULL CHECK(p_k_tablas_variables_expertas__cantidad>=0),
    p_k_tipo_regla_resultado__presente BOOLEAN NOT NULL,
    p_k_tipo_regla_resultado TEXT,
    p_k_version__presente BOOLEAN NOT NULL,
    p_k_version TEXT,
    petersen_limites__estado VARCHAR(8) NOT NULL CHECK(petersen_limites__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_limites_persistido__presente BOOLEAN NOT NULL,
    petersen_limites_persistido BOOLEAN,
    petersen_limites_reproduce_adquisicion_sql__presente BOOLEAN NOT NULL,
    petersen_limites_reproduce_adquisicion_sql BOOLEAN,
    petersen_limites_revalida_bytes_documentales__presente BOOLEAN NOT NULL,
    petersen_limites_revalida_bytes_documentales BOOLEAN,
    petersen_limites_revalida_permisos_historicos__presente BOOLEAN NOT NULL,
    petersen_limites_revalida_permisos_historicos BOOLEAN,
    petersen_membresias_soportes__estado VARCHAR(8) NOT NULL CHECK(petersen_membresias_soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_membresias_soportes__cantidad BIGINT NOT NULL CHECK(petersen_membresias_soportes__cantidad>=0),
    petersen_procedencia_valoraciones__estado VARCHAR(8) NOT NULL CHECK(petersen_procedencia_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_procedencia_valoraciones__cantidad BIGINT NOT NULL CHECK(petersen_procedencia_valoraciones__cantidad>=0),
    petersen_valoraciones__estado VARCHAR(8) NOT NULL CHECK(petersen_valoraciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    petersen_valoraciones__cantidad BIGINT NOT NULL CHECK(petersen_valoraciones__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    UNIQUE(cod_evaluacion_experta),
    CHECK(i_cabecera_cod_usuario_creacion__presente=TRUE OR i_cabecera_cod_usuario_creacion IS NULL),
    CHECK(i_cabecera_cod_version_anterior__presente=TRUE OR i_cabecera_cod_version_anterior IS NULL),
    CHECK(i_cabecera_cod_version_modelo__presente=TRUE OR i_cabecera_cod_version_modelo IS NULL),
    CHECK(i_cabecera_codigo_version__presente=TRUE OR i_cabecera_codigo_version IS NULL),
    CHECK(i_cabecera_descripcion__presente=TRUE OR i_cabecera_descripcion IS NULL),
    CHECK(i_cabecera_estado__presente=TRUE OR i_cabecera_estado IS NULL),
    CHECK(i_cabecera_fecha_hora_creacion__presente=TRUE OR i_cabecera_fecha_hora_creacion IS NULL),
    CHECK(i_cabecera_fecha_hora_retiro__presente=TRUE OR i_cabecera_fecha_hora_retiro IS NULL),
    CHECK(i_cabecera_fecha_hora_vigencia__presente=TRUE OR i_cabecera_fecha_hora_vigencia IS NULL),
    CHECK(i_cabecera_motivo_cambio__presente=TRUE OR i_cabecera_motivo_cambio IS NULL),
    CHECK(i_cabecera_nombre__presente=TRUE OR i_cabecera_nombre IS NULL),
    CHECK(i_cabecera_observacion__presente=TRUE OR i_cabecera_observacion IS NULL),
    CHECK(instrumental_capturas_capturas__estado='PRESENTE' OR instrumental_capturas_capturas__cantidad=0),
    CHECK(instrumental_capturas_disponible__presente=TRUE OR instrumental_capturas_disponible IS NULL),
    CHECK(instrumental_capturas_fecha_corte__presente=TRUE OR instrumental_capturas_fecha_corte IS NULL),
    CHECK(instrumental_capturas_persistido__presente=TRUE OR instrumental_capturas_persistido IS NULL),
    CHECK(i_k_contratos_extraccion_respuesta_institucional__presente=TRUE OR i_k_contratos_extraccion_respuesta_institucional IS NULL),
    CHECK(i_k_contratos_extraccion_respuesta_original__presente=TRUE OR i_k_contratos_extraccion_respuesta_original IS NULL),
    CHECK(i_k_contratos_relaciones__estado='PRESENTE' OR i_k_contratos_relaciones__cantidad=0),
    CHECK(i_k_solo_pruebas_tecnicas__presente=TRUE OR i_k_solo_pruebas_tecnicas IS NULL),
    CHECK(i_k_tablas_condiciones_regla_experta__estado='PRESENTE' OR i_k_tablas_condiciones_regla_experta__cantidad=0),
    CHECK(i_k_tablas_consecuencias_regla_experta__estado='PRESENTE' OR i_k_tablas_consecuencias_regla_experta__cantidad=0),
    CHECK(i_k_tablas_criterios_dominios_resultado__estado='PRESENTE' OR i_k_tablas_criterios_dominios_resultado__cantidad=0),
    CHECK(i_k_tablas_dominios_valores_expertos__estado='PRESENTE' OR i_k_tablas_dominios_valores_expertos__cantidad=0),
    CHECK(i_k_tablas_fuentes_datos_expertas__estado='PRESENTE' OR i_k_tablas_fuentes_datos_expertas__cantidad=0),
    CHECK(i_k_tablas_mapeos_valores_fuente__estado='PRESENTE' OR i_k_tablas_mapeos_valores_fuente__cantidad=0),
    CHECK(i_k_tablas_mapeos_variables_fuente__estado='PRESENTE' OR i_k_tablas_mapeos_variables_fuente__cantidad=0),
    CHECK(i_k_tablas_nodos_semanticos__estado='PRESENTE' OR i_k_tablas_nodos_semanticos__cantidad=0),
    CHECK(i_k_tablas_reglas_expertas__estado='PRESENTE' OR i_k_tablas_reglas_expertas__cantidad=0),
    CHECK(i_k_tablas_relaciones_semanticas__estado='PRESENTE' OR i_k_tablas_relaciones_semanticas__cantidad=0),
    CHECK(i_k_tablas_valores_semanticos__estado='PRESENTE' OR i_k_tablas_valores_semanticos__cantidad=0),
    CHECK(i_k_tablas_variables_expertas__estado='PRESENTE' OR i_k_tablas_variables_expertas__cantidad=0),
    CHECK(i_k_tipo_regla_resultado__presente=TRUE OR i_k_tipo_regla_resultado IS NULL),
    CHECK(i_k_version__presente=TRUE OR i_k_version IS NULL),
    CHECK(instrumental_contexto_evaluacion__presente=TRUE OR instrumental_contexto_evaluacion IS NULL),
    CHECK(instrumental_contexto_fecha_corte_corte_offset__presente=TRUE OR instrumental_contexto_fecha_corte_corte_offset IS NULL),
    CHECK(instrumental_contexto_fecha_corte_corte_utc__presente=TRUE OR instrumental_contexto_fecha_corte_corte_utc IS NULL),
    CHECK(instrumental_contexto_fecha_corte_corte_zona__presente=TRUE OR instrumental_contexto_fecha_corte_corte_zona IS NULL),
    CHECK(instrumental_contexto_residente__presente=TRUE OR instrumental_contexto_residente IS NULL),
    CHECK(instrumental_contexto_usuario_ejecucion__presente=TRUE OR instrumental_contexto_usuario_ejecucion IS NULL),
    CHECK(instrumental_contexto_version__presente=TRUE OR instrumental_contexto_version IS NULL),
    CHECK(instrumental_contrato__presente=TRUE OR instrumental_contrato IS NULL),
    CHECK(instrumental_vinculos__estado='PRESENTE' OR instrumental_vinculos__cantidad=0),
    CHECK(petersen_adquisicion_conservacion_completa__presente=TRUE OR petersen_adquisicion_conservacion_completa IS NULL),
    CHECK(petersen_adquisicion_exclusiones__estado='PRESENTE' OR petersen_adquisicion_exclusiones__cantidad=0),
    CHECK(petersen_adquisicion_fuentes_originales_mutables__presente=TRUE OR petersen_adquisicion_fuentes_originales_mutables IS NULL),
    CHECK(petersen_adquisicion_valoraciones_al_corte__presente=TRUE OR petersen_adquisicion_valoraciones_al_corte IS NULL),
    CHECK(petersen_constancias_documentales__estado='PRESENTE' OR petersen_constancias_documentales__cantidad=0),
    CHECK(petersen_contexto_evaluacion__presente=TRUE OR petersen_contexto_evaluacion IS NULL),
    CHECK(petersen_contexto_fecha_corte_corte_offset__presente=TRUE OR petersen_contexto_fecha_corte_corte_offset IS NULL),
    CHECK(petersen_contexto_fecha_corte_corte_utc__presente=TRUE OR petersen_contexto_fecha_corte_corte_utc IS NULL),
    CHECK(petersen_contexto_fecha_corte_corte_zona__presente=TRUE OR petersen_contexto_fecha_corte_corte_zona IS NULL),
    CHECK(petersen_contexto_residente__presente=TRUE OR petersen_contexto_residente IS NULL),
    CHECK(petersen_contexto_usuario_ejecucion__presente=TRUE OR petersen_contexto_usuario_ejecucion IS NULL),
    CHECK(petersen_contexto_version__presente=TRUE OR petersen_contexto_version IS NULL),
    CHECK(petersen_contrato__presente=TRUE OR petersen_contrato IS NULL),
    CHECK(p_cabecera_cod_usuario_creacion__presente=TRUE OR p_cabecera_cod_usuario_creacion IS NULL),
    CHECK(p_cabecera_cod_version_anterior__presente=TRUE OR p_cabecera_cod_version_anterior IS NULL),
    CHECK(p_cabecera_cod_version_modelo__presente=TRUE OR p_cabecera_cod_version_modelo IS NULL),
    CHECK(p_cabecera_codigo_version__presente=TRUE OR p_cabecera_codigo_version IS NULL),
    CHECK(p_cabecera_descripcion__presente=TRUE OR p_cabecera_descripcion IS NULL),
    CHECK(p_cabecera_estado__presente=TRUE OR p_cabecera_estado IS NULL),
    CHECK(p_cabecera_fecha_hora_creacion__presente=TRUE OR p_cabecera_fecha_hora_creacion IS NULL),
    CHECK(p_cabecera_fecha_hora_retiro__presente=TRUE OR p_cabecera_fecha_hora_retiro IS NULL),
    CHECK(p_cabecera_fecha_hora_vigencia__presente=TRUE OR p_cabecera_fecha_hora_vigencia IS NULL),
    CHECK(p_cabecera_motivo_cambio__presente=TRUE OR p_cabecera_motivo_cambio IS NULL),
    CHECK(p_cabecera_nombre__presente=TRUE OR p_cabecera_nombre IS NULL),
    CHECK(p_cabecera_observacion__presente=TRUE OR p_cabecera_observacion IS NULL),
    CHECK(p_k_contratos_extraccion_respuesta_institucional__presente=TRUE OR p_k_contratos_extraccion_respuesta_institucional IS NULL),
    CHECK(p_k_contratos_extraccion_respuesta_original__presente=TRUE OR p_k_contratos_extraccion_respuesta_original IS NULL),
    CHECK(p_k_contratos_relaciones__estado='PRESENTE' OR p_k_contratos_relaciones__cantidad=0),
    CHECK(p_k_solo_pruebas_tecnicas__presente=TRUE OR p_k_solo_pruebas_tecnicas IS NULL),
    CHECK(p_k_tablas_condiciones_regla_experta__estado='PRESENTE' OR p_k_tablas_condiciones_regla_experta__cantidad=0),
    CHECK(p_k_tablas_consecuencias_regla_experta__estado='PRESENTE' OR p_k_tablas_consecuencias_regla_experta__cantidad=0),
    CHECK(p_k_tablas_criterios_dominios_resultado__estado='PRESENTE' OR p_k_tablas_criterios_dominios_resultado__cantidad=0),
    CHECK(p_k_tablas_dominios_valores_expertos__estado='PRESENTE' OR p_k_tablas_dominios_valores_expertos__cantidad=0),
    CHECK(p_k_tablas_fuentes_datos_expertas__estado='PRESENTE' OR p_k_tablas_fuentes_datos_expertas__cantidad=0),
    CHECK(p_k_tablas_mapeos_valores_fuente__estado='PRESENTE' OR p_k_tablas_mapeos_valores_fuente__cantidad=0),
    CHECK(p_k_tablas_mapeos_variables_fuente__estado='PRESENTE' OR p_k_tablas_mapeos_variables_fuente__cantidad=0),
    CHECK(p_k_tablas_nodos_semanticos__estado='PRESENTE' OR p_k_tablas_nodos_semanticos__cantidad=0),
    CHECK(p_k_tablas_reglas_expertas__estado='PRESENTE' OR p_k_tablas_reglas_expertas__cantidad=0),
    CHECK(p_k_tablas_relaciones_semanticas__estado='PRESENTE' OR p_k_tablas_relaciones_semanticas__cantidad=0),
    CHECK(p_k_tablas_valores_semanticos__estado='PRESENTE' OR p_k_tablas_valores_semanticos__cantidad=0),
    CHECK(p_k_tablas_variables_expertas__estado='PRESENTE' OR p_k_tablas_variables_expertas__cantidad=0),
    CHECK(p_k_tipo_regla_resultado__presente=TRUE OR p_k_tipo_regla_resultado IS NULL),
    CHECK(p_k_version__presente=TRUE OR p_k_version IS NULL),
    CHECK(petersen_limites_persistido__presente=TRUE OR petersen_limites_persistido IS NULL),
    CHECK(petersen_limites_reproduce_adquisicion_sql__presente=TRUE OR petersen_limites_reproduce_adquisicion_sql IS NULL),
    CHECK(petersen_limites_revalida_bytes_documentales__presente=TRUE OR petersen_limites_revalida_bytes_documentales IS NULL),
    CHECK(petersen_limites_revalida_permisos_historicos__presente=TRUE OR petersen_limites_revalida_permisos_historicos IS NULL),
    CHECK(petersen_membresias_soportes__estado='PRESENTE' OR petersen_membresias_soportes__cantidad=0),
    CHECK(petersen_procedencia_valoraciones__estado='PRESENTE' OR petersen_procedencia_valoraciones__cantidad=0),
    CHECK(petersen_valoraciones__estado='PRESENTE' OR petersen_valoraciones__cantidad=0),
    CHECK(registro__estado='PRESENTE' OR (instrumental__estado='AUSENTE' AND petersen__estado='AUSENTE')),
    CHECK(instrumental__estado='PRESENTE' OR (i_cabecera__estado='AUSENTE' AND instrumental_capturas__estado='AUSENTE' AND i_k__estado='AUSENTE' AND instrumental_contexto__estado='AUSENTE' AND instrumental_contrato__presente=FALSE AND instrumental_vinculos__estado='AUSENTE')),
    CHECK(i_cabecera__estado='PRESENTE' OR (i_cabecera_cod_usuario_creacion__presente=FALSE AND i_cabecera_cod_version_anterior__presente=FALSE AND i_cabecera_cod_version_modelo__presente=FALSE AND i_cabecera_codigo_version__presente=FALSE AND i_cabecera_descripcion__presente=FALSE AND i_cabecera_estado__presente=FALSE AND i_cabecera_fecha_hora_creacion__presente=FALSE AND i_cabecera_fecha_hora_retiro__presente=FALSE AND i_cabecera_fecha_hora_vigencia__presente=FALSE AND i_cabecera_motivo_cambio__presente=FALSE AND i_cabecera_nombre__presente=FALSE AND i_cabecera_observacion__presente=FALSE)),
    CHECK(instrumental_capturas__estado='PRESENTE' OR (instrumental_capturas_capturas__estado='AUSENTE' AND instrumental_capturas_disponible__presente=FALSE AND instrumental_capturas_fecha_corte__presente=FALSE AND instrumental_capturas_persistido__presente=FALSE)),
    CHECK(i_k__estado='PRESENTE' OR (i_k_contratos_extraccion__estado='AUSENTE' AND i_k_contratos_relaciones__estado='AUSENTE' AND i_k_solo_pruebas_tecnicas__presente=FALSE AND i_k_tablas__estado='AUSENTE' AND i_k_tipo_regla_resultado__presente=FALSE AND i_k_version__presente=FALSE)),
    CHECK(i_k_contratos_extraccion__estado='PRESENTE' OR (i_k_contratos_extraccion_respuesta_institucional__presente=FALSE AND i_k_contratos_extraccion_respuesta_original__presente=FALSE)),
    CHECK(i_k_tablas__estado='PRESENTE' OR (i_k_tablas_condiciones_regla_experta__estado='AUSENTE' AND i_k_tablas_consecuencias_regla_experta__estado='AUSENTE' AND i_k_tablas_criterios_dominios_resultado__estado='AUSENTE' AND i_k_tablas_dominios_valores_expertos__estado='AUSENTE' AND i_k_tablas_fuentes_datos_expertas__estado='AUSENTE' AND i_k_tablas_mapeos_valores_fuente__estado='AUSENTE' AND i_k_tablas_mapeos_variables_fuente__estado='AUSENTE' AND i_k_tablas_nodos_semanticos__estado='AUSENTE' AND i_k_tablas_reglas_expertas__estado='AUSENTE' AND i_k_tablas_relaciones_semanticas__estado='AUSENTE' AND i_k_tablas_valores_semanticos__estado='AUSENTE' AND i_k_tablas_variables_expertas__estado='AUSENTE')),
    CHECK(instrumental_contexto__estado='PRESENTE' OR (instrumental_contexto_evaluacion__presente=FALSE AND instrumental_contexto_fecha_corte__estado='AUSENTE' AND instrumental_contexto_residente__presente=FALSE AND instrumental_contexto_usuario_ejecucion__presente=FALSE AND instrumental_contexto_version__presente=FALSE)),
    CHECK(instrumental_contexto_fecha_corte__estado='PRESENTE' OR (instrumental_contexto_fecha_corte_corte_offset__presente=FALSE AND instrumental_contexto_fecha_corte_corte_utc__presente=FALSE AND instrumental_contexto_fecha_corte_corte_zona__presente=FALSE)),
    CHECK(petersen__estado='PRESENTE' OR (petersen_adquisicion__estado='AUSENTE' AND petersen_constancias_documentales__estado='AUSENTE' AND petersen_contexto__estado='AUSENTE' AND petersen_contrato__presente=FALSE AND petersen_gobierno__estado='AUSENTE' AND petersen_limites__estado='AUSENTE' AND petersen_membresias_soportes__estado='AUSENTE' AND petersen_procedencia_valoraciones__estado='AUSENTE' AND petersen_valoraciones__estado='AUSENTE')),
    CHECK(petersen_adquisicion__estado='PRESENTE' OR (petersen_adquisicion_conservacion_completa__presente=FALSE AND petersen_adquisicion_exclusiones__estado='AUSENTE' AND petersen_adquisicion_fuentes_originales_mutables__presente=FALSE AND petersen_adquisicion_valoraciones_al_corte__presente=FALSE)),
    CHECK(petersen_contexto__estado='PRESENTE' OR (petersen_contexto_evaluacion__presente=FALSE AND petersen_contexto_fecha_corte__estado='AUSENTE' AND petersen_contexto_residente__presente=FALSE AND petersen_contexto_usuario_ejecucion__presente=FALSE AND petersen_contexto_version__presente=FALSE)),
    CHECK(petersen_contexto_fecha_corte__estado='PRESENTE' OR (petersen_contexto_fecha_corte_corte_offset__presente=FALSE AND petersen_contexto_fecha_corte_corte_utc__presente=FALSE AND petersen_contexto_fecha_corte_corte_zona__presente=FALSE)),
    CHECK(petersen_gobierno__estado='PRESENTE' OR (p_cabecera__estado='AUSENTE' AND p_k__estado='AUSENTE')),
    CHECK(p_cabecera__estado='PRESENTE' OR (p_cabecera_cod_usuario_creacion__presente=FALSE AND p_cabecera_cod_version_anterior__presente=FALSE AND p_cabecera_cod_version_modelo__presente=FALSE AND p_cabecera_codigo_version__presente=FALSE AND p_cabecera_descripcion__presente=FALSE AND p_cabecera_estado__presente=FALSE AND p_cabecera_fecha_hora_creacion__presente=FALSE AND p_cabecera_fecha_hora_retiro__presente=FALSE AND p_cabecera_fecha_hora_vigencia__presente=FALSE AND p_cabecera_motivo_cambio__presente=FALSE AND p_cabecera_nombre__presente=FALSE AND p_cabecera_observacion__presente=FALSE)),
    CHECK(p_k__estado='PRESENTE' OR (p_k_contratos_extraccion__estado='AUSENTE' AND p_k_contratos_relaciones__estado='AUSENTE' AND p_k_solo_pruebas_tecnicas__presente=FALSE AND p_k_tablas__estado='AUSENTE' AND p_k_tipo_regla_resultado__presente=FALSE AND p_k_version__presente=FALSE)),
    CHECK(p_k_contratos_extraccion__estado='PRESENTE' OR (p_k_contratos_extraccion_respuesta_institucional__presente=FALSE AND p_k_contratos_extraccion_respuesta_original__presente=FALSE)),
    CHECK(p_k_tablas__estado='PRESENTE' OR (p_k_tablas_condiciones_regla_experta__estado='AUSENTE' AND p_k_tablas_consecuencias_regla_experta__estado='AUSENTE' AND p_k_tablas_criterios_dominios_resultado__estado='AUSENTE' AND p_k_tablas_dominios_valores_expertos__estado='AUSENTE' AND p_k_tablas_fuentes_datos_expertas__estado='AUSENTE' AND p_k_tablas_mapeos_valores_fuente__estado='AUSENTE' AND p_k_tablas_mapeos_variables_fuente__estado='AUSENTE' AND p_k_tablas_nodos_semanticos__estado='AUSENTE' AND p_k_tablas_reglas_expertas__estado='AUSENTE' AND p_k_tablas_relaciones_semanticas__estado='AUSENTE' AND p_k_tablas_valores_semanticos__estado='AUSENTE' AND p_k_tablas_variables_expertas__estado='AUSENTE')),
    CHECK(petersen_limites__estado='PRESENTE' OR (petersen_limites_persistido__presente=FALSE AND petersen_limites_reproduce_adquisicion_sql__presente=FALSE AND petersen_limites_revalida_bytes_documentales__presente=FALSE AND petersen_limites_revalida_permisos_historicos__presente=FALSE))
);

CREATE TABLE capturas_adquiridas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_entrada_institucional VARCHAR(20),
    cod_fuente_soporte VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicacion__estado VARCHAR(8) NOT NULL CHECK(aplicacion__estado IN ('AUSENTE','NULL','PRESENTE')),
    aplicacion_clasificacion__presente BOOLEAN NOT NULL,
    aplicacion_clasificacion TEXT,
    aplicacion_cod_aplicacion__presente BOOLEAN NOT NULL,
    aplicacion_cod_aplicacion TEXT,
    aplicacion_cod_aplicacion_anterior__presente BOOLEAN NOT NULL,
    aplicacion_cod_aplicacion_anterior TEXT,
    aplicacion_cod_atencion__presente BOOLEAN NOT NULL,
    aplicacion_cod_atencion TEXT,
    aplicacion_cod_documento_clinico__presente BOOLEAN NOT NULL,
    aplicacion_cod_documento_clinico TEXT,
    aplicacion_cod_instrumento__presente BOOLEAN NOT NULL,
    aplicacion_cod_instrumento TEXT,
    aplicacion_cod_personal__presente BOOLEAN NOT NULL,
    aplicacion_cod_personal TEXT,
    aplicacion_cod_residente__presente BOOLEAN NOT NULL,
    aplicacion_cod_residente TEXT,
    aplicacion_cod_usuario_registro__presente BOOLEAN NOT NULL,
    aplicacion_cod_usuario_registro TEXT,
    aplicacion_estado__presente BOOLEAN NOT NULL,
    aplicacion_estado TEXT,
    aplicacion_fecha_hora__presente BOOLEAN NOT NULL,
    aplicacion_fecha_hora TEXT,
    aplicacion_interpretacion__presente BOOLEAN NOT NULL,
    aplicacion_interpretacion TEXT,
    aplicacion_localizador_fuente__presente BOOLEAN NOT NULL,
    aplicacion_localizador_fuente TEXT,
    aplicacion_observacion__presente BOOLEAN NOT NULL,
    aplicacion_observacion TEXT,
    aplicacion_puntaje_maximo__presente BOOLEAN NOT NULL,
    aplicacion_puntaje_maximo__tipo VARCHAR(6) NOT NULL CHECK(aplicacion_puntaje_maximo__tipo IN ('NULL','STRING','INT','FLOAT')),
    aplicacion_puntaje_maximo_texto TEXT,
    aplicacion_puntaje_maximo_entero BIGINT,
    aplicacion_puntaje_maximo_real DOUBLE PRECISION,
    aplicacion_puntaje_maximo__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    aplicacion_puntaje_total__presente BOOLEAN NOT NULL,
    aplicacion_puntaje_total__tipo VARCHAR(6) NOT NULL CHECK(aplicacion_puntaje_total__tipo IN ('NULL','STRING','INT','FLOAT')),
    aplicacion_puntaje_total_texto TEXT,
    aplicacion_puntaje_total_entero BIGINT,
    aplicacion_puntaje_total_real DOUBLE PRECISION,
    aplicacion_puntaje_total__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    aplicacion_registrado_en__presente BOOLEAN NOT NULL,
    aplicacion_registrado_en TEXT,
    atencion_original__estado VARCHAR(8) NOT NULL CHECK(atencion_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    atencion_original_cod_atencion__presente BOOLEAN NOT NULL,
    atencion_original_cod_atencion TEXT,
    atencion_original_cod_residente__presente BOOLEAN NOT NULL,
    atencion_original_cod_residente TEXT,
    autor__presente BOOLEAN NOT NULL,
    autor TEXT,
    cod_aplicacion__presente BOOLEAN NOT NULL,
    cod_aplicacion TEXT,
    cod_instrumento__presente BOOLEAN NOT NULL,
    cod_instrumento TEXT,
    cod_personal__presente BOOLEAN NOT NULL,
    cod_personal TEXT,
    cod_residente__presente BOOLEAN NOT NULL,
    cod_residente TEXT,
    codigo_instrumento__presente BOOLEAN NOT NULL,
    codigo_instrumento TEXT,
    documento_original__estado VARCHAR(8) NOT NULL CHECK(documento_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    documento_original_cod_documento_clinico__presente BOOLEAN NOT NULL,
    documento_original_cod_documento_clinico TEXT,
    documento_original_cod_personal__presente BOOLEAN NOT NULL,
    documento_original_cod_personal TEXT,
    documento_original_cod_residente__presente BOOLEAN NOT NULL,
    documento_original_cod_residente TEXT,
    documento_original_estado__presente BOOLEAN NOT NULL,
    documento_original_estado TEXT,
    documento_original_fecha_hora__presente BOOLEAN NOT NULL,
    documento_original_fecha_hora TEXT,
    documento_original_hash_archivo__presente BOOLEAN NOT NULL,
    documento_original_hash_archivo TEXT,
    edad_en_aplicacion__presente BOOLEAN NOT NULL,
    edad_en_aplicacion BIGINT,
    estado_original__presente BOOLEAN NOT NULL,
    estado_original TEXT,
    extraccion_adquirida__presente BOOLEAN NOT NULL,
    extraccion_adquirida TEXT,
    fecha_hora__presente BOOLEAN NOT NULL,
    fecha_hora TEXT,
    fuente_edad__estado VARCHAR(8) NOT NULL CHECK(fuente_edad__estado IN ('AUSENTE','NULL','PRESENTE')),
    fuente_edad_campo__presente BOOLEAN NOT NULL,
    fuente_edad_campo TEXT,
    fuente_edad_fecha_aplicacion__presente BOOLEAN NOT NULL,
    fuente_edad_fecha_aplicacion TEXT,
    fuente_edad_fecha_nacimiento__presente BOOLEAN NOT NULL,
    fuente_edad_fecha_nacimiento TEXT,
    fuente_edad_registro__presente BOOLEAN NOT NULL,
    fuente_edad_registro TEXT,
    fuente_edad_tabla__presente BOOLEAN NOT NULL,
    fuente_edad_tabla TEXT,
    instrumento__estado VARCHAR(8) NOT NULL CHECK(instrumento__estado IN ('AUSENTE','NULL','PRESENTE')),
    instrumento_cod_instrumento__presente BOOLEAN NOT NULL,
    instrumento_cod_instrumento TEXT,
    instrumento_codigo__presente BOOLEAN NOT NULL,
    instrumento_codigo TEXT,
    instrumento_descripcion__presente BOOLEAN NOT NULL,
    instrumento_descripcion TEXT,
    instrumento_estado__presente BOOLEAN NOT NULL,
    instrumento_estado TEXT,
    instrumento_nombre__presente BOOLEAN NOT NULL,
    instrumento_nombre TEXT,
    instrumento_puntaje_maximo__presente BOOLEAN NOT NULL,
    instrumento_puntaje_maximo__tipo VARCHAR(6) NOT NULL CHECK(instrumento_puntaje_maximo__tipo IN ('NULL','STRING','INT','FLOAT')),
    instrumento_puntaje_maximo_texto TEXT,
    instrumento_puntaje_maximo_entero BIGINT,
    instrumento_puntaje_maximo_real DOUBLE PRECISION,
    instrumento_puntaje_maximo__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    instrumento_tipo__presente BOOLEAN NOT NULL,
    instrumento_tipo TEXT,
    instrumento_version__presente BOOLEAN NOT NULL,
    instrumento_version TEXT,
    motivos__estado VARCHAR(8) NOT NULL CHECK(motivos__estado IN ('AUSENTE','NULL','PRESENTE')),
    motivos__cantidad BIGINT NOT NULL CHECK(motivos__cantidad>=0),
    nombre_instrumento__presente BOOLEAN NOT NULL,
    nombre_instrumento TEXT,
    perfil__estado VARCHAR(8) NOT NULL CHECK(perfil__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_cod_instrumento__presente BOOLEAN NOT NULL,
    perfil_cod_instrumento TEXT,
    perfil_codigo_instrumento__presente BOOLEAN NOT NULL,
    perfil_codigo_instrumento TEXT,
    perfil_id__presente BOOLEAN NOT NULL,
    perfil_id TEXT,
    perfil_identidad__estado VARCHAR(8) NOT NULL CHECK(perfil_identidad__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_identidad_edicion_protocolo__presente BOOLEAN NOT NULL,
    perfil_identidad_edicion_protocolo TEXT,
    perfil_identidad_forma__presente BOOLEAN NOT NULL,
    perfil_identidad_forma__tipo VARCHAR(6) NOT NULL CHECK(perfil_identidad_forma__tipo IN ('NULL','STRING','INT')),
    perfil_identidad_forma_texto TEXT,
    perfil_identidad_forma_entero BIGINT,
    perfil_identidad_idioma__presente BOOLEAN NOT NULL,
    perfil_identidad_idioma TEXT,
    perfil_identidad_instrumento__presente BOOLEAN NOT NULL,
    perfil_identidad_instrumento TEXT,
    perfil_metodo__presente BOOLEAN NOT NULL,
    perfil_metodo TEXT,
    perfil_selectores__estado VARCHAR(8) NOT NULL CHECK(perfil_selectores__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_selectores__cantidad BIGINT NOT NULL CHECK(perfil_selectores__cantidad>=0),
    perfil_version_instrumento__presente BOOLEAN NOT NULL,
    perfil_version_instrumento TEXT,
    preguntas_declarativas_originales__estado VARCHAR(8) NOT NULL CHECK(preguntas_declarativas_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    preguntas_declarativas_originales__cantidad BIGINT NOT NULL CHECK(preguntas_declarativas_originales__cantidad>=0),
    procedencia__estado VARCHAR(8) NOT NULL CHECK(procedencia__estado IN ('AUSENTE','NULL','PRESENTE')),
    procedencia_cod_aplicacion_anterior__presente BOOLEAN NOT NULL,
    procedencia_cod_aplicacion_anterior TEXT,
    procedencia_cod_documento_clinico__presente BOOLEAN NOT NULL,
    procedencia_cod_documento_clinico TEXT,
    procedencia_cod_personal_aplicador__presente BOOLEAN NOT NULL,
    procedencia_cod_personal_aplicador TEXT,
    procedencia_cod_usuario_registro__presente BOOLEAN NOT NULL,
    procedencia_cod_usuario_registro TEXT,
    procedencia_estado_documento__presente BOOLEAN NOT NULL,
    procedencia_estado_documento TEXT,
    procedencia_localizador_fuente__presente BOOLEAN NOT NULL,
    procedencia_localizador_fuente TEXT,
    procedencia_registrado_en__presente BOOLEAN NOT NULL,
    procedencia_registrado_en TEXT,
    respuestas_originales__estado VARCHAR(8) NOT NULL CHECK(respuestas_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    respuestas_originales__cantidad BIGINT NOT NULL CHECK(respuestas_originales__cantidad>=0),
    version_instrumento__presente BOOLEAN NOT NULL,
    version_instrumento TEXT,
    vinculos_preguntas_actuales__estado VARCHAR(8) NOT NULL CHECK(vinculos_preguntas_actuales__estado IN ('AUSENTE','NULL','PRESENTE')),
    vinculos_preguntas_actuales__cantidad BIGINT NOT NULL CHECK(vinculos_preguntas_actuales__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_entrada_institucional,rol_coleccion,posicion_coleccion),
    UNIQUE(cod_fuente_soporte,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_entrada_institucional IS NULL THEN 0 ELSE 1 END+CASE WHEN cod_fuente_soporte IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_entrada_institucional IS NOT NULL AND rol_coleccion IN ('Ca877034852')) OR (cod_fuente_soporte IS NOT NULL AND rol_coleccion IN ('CAPTURA_SOPORTE'))),
    CHECK((rol_coleccion='Ca877034852' AND clave_tipo ='NULL') OR (rol_coleccion='CAPTURA_SOPORTE' AND clave_tipo ='NULL')),
    CHECK(aplicacion_clasificacion__presente=TRUE OR aplicacion_clasificacion IS NULL),
    CHECK(aplicacion_cod_aplicacion__presente=TRUE OR aplicacion_cod_aplicacion IS NULL),
    CHECK(aplicacion_cod_aplicacion_anterior__presente=TRUE OR aplicacion_cod_aplicacion_anterior IS NULL),
    CHECK(aplicacion_cod_atencion__presente=TRUE OR aplicacion_cod_atencion IS NULL),
    CHECK(aplicacion_cod_documento_clinico__presente=TRUE OR aplicacion_cod_documento_clinico IS NULL),
    CHECK(aplicacion_cod_instrumento__presente=TRUE OR aplicacion_cod_instrumento IS NULL),
    CHECK(aplicacion_cod_personal__presente=TRUE OR aplicacion_cod_personal IS NULL),
    CHECK(aplicacion_cod_residente__presente=TRUE OR aplicacion_cod_residente IS NULL),
    CHECK(aplicacion_cod_usuario_registro__presente=TRUE OR aplicacion_cod_usuario_registro IS NULL),
    CHECK(aplicacion_estado__presente=TRUE OR aplicacion_estado IS NULL),
    CHECK(aplicacion_fecha_hora__presente=TRUE OR aplicacion_fecha_hora IS NULL),
    CHECK(aplicacion_interpretacion__presente=TRUE OR aplicacion_interpretacion IS NULL),
    CHECK(aplicacion_localizador_fuente__presente=TRUE OR aplicacion_localizador_fuente IS NULL),
    CHECK(aplicacion_observacion__presente=TRUE OR aplicacion_observacion IS NULL),
    CHECK(aplicacion_puntaje_maximo__presente=TRUE OR aplicacion_puntaje_maximo__tipo='NULL'),
    CHECK((aplicacion_puntaje_maximo__tipo='NULL' AND aplicacion_puntaje_maximo_texto IS NULL AND aplicacion_puntaje_maximo_entero IS NULL AND aplicacion_puntaje_maximo_real IS NULL) OR (aplicacion_puntaje_maximo__tipo='STRING' AND aplicacion_puntaje_maximo_texto IS NOT NULL AND aplicacion_puntaje_maximo_entero IS NULL AND aplicacion_puntaje_maximo_real IS NULL) OR (aplicacion_puntaje_maximo__tipo='INT' AND aplicacion_puntaje_maximo_entero IS NOT NULL AND aplicacion_puntaje_maximo_texto IS NULL AND aplicacion_puntaje_maximo_real IS NULL) OR (aplicacion_puntaje_maximo__tipo='FLOAT' AND aplicacion_puntaje_maximo_real IS NOT NULL AND aplicacion_puntaje_maximo_texto IS NULL AND aplicacion_puntaje_maximo_entero IS NULL)),
    CHECK(aplicacion_puntaje_maximo__cero_negativo=FALSE OR (aplicacion_puntaje_maximo__tipo='FLOAT' AND aplicacion_puntaje_maximo_real=0)),
    CHECK(aplicacion_puntaje_total__presente=TRUE OR aplicacion_puntaje_total__tipo='NULL'),
    CHECK((aplicacion_puntaje_total__tipo='NULL' AND aplicacion_puntaje_total_texto IS NULL AND aplicacion_puntaje_total_entero IS NULL AND aplicacion_puntaje_total_real IS NULL) OR (aplicacion_puntaje_total__tipo='STRING' AND aplicacion_puntaje_total_texto IS NOT NULL AND aplicacion_puntaje_total_entero IS NULL AND aplicacion_puntaje_total_real IS NULL) OR (aplicacion_puntaje_total__tipo='INT' AND aplicacion_puntaje_total_entero IS NOT NULL AND aplicacion_puntaje_total_texto IS NULL AND aplicacion_puntaje_total_real IS NULL) OR (aplicacion_puntaje_total__tipo='FLOAT' AND aplicacion_puntaje_total_real IS NOT NULL AND aplicacion_puntaje_total_texto IS NULL AND aplicacion_puntaje_total_entero IS NULL)),
    CHECK(aplicacion_puntaje_total__cero_negativo=FALSE OR (aplicacion_puntaje_total__tipo='FLOAT' AND aplicacion_puntaje_total_real=0)),
    CHECK(aplicacion_registrado_en__presente=TRUE OR aplicacion_registrado_en IS NULL),
    CHECK(atencion_original_cod_atencion__presente=TRUE OR atencion_original_cod_atencion IS NULL),
    CHECK(atencion_original_cod_residente__presente=TRUE OR atencion_original_cod_residente IS NULL),
    CHECK(autor__presente=TRUE OR autor IS NULL),
    CHECK(cod_aplicacion__presente=TRUE OR cod_aplicacion IS NULL),
    CHECK(cod_instrumento__presente=TRUE OR cod_instrumento IS NULL),
    CHECK(cod_personal__presente=TRUE OR cod_personal IS NULL),
    CHECK(cod_residente__presente=TRUE OR cod_residente IS NULL),
    CHECK(codigo_instrumento__presente=TRUE OR codigo_instrumento IS NULL),
    CHECK(documento_original_cod_documento_clinico__presente=TRUE OR documento_original_cod_documento_clinico IS NULL),
    CHECK(documento_original_cod_personal__presente=TRUE OR documento_original_cod_personal IS NULL),
    CHECK(documento_original_cod_residente__presente=TRUE OR documento_original_cod_residente IS NULL),
    CHECK(documento_original_estado__presente=TRUE OR documento_original_estado IS NULL),
    CHECK(documento_original_fecha_hora__presente=TRUE OR documento_original_fecha_hora IS NULL),
    CHECK(documento_original_hash_archivo__presente=TRUE OR documento_original_hash_archivo IS NULL),
    CHECK(edad_en_aplicacion__presente=TRUE OR edad_en_aplicacion IS NULL),
    CHECK(estado_original__presente=TRUE OR estado_original IS NULL),
    CHECK(extraccion_adquirida__presente=TRUE OR extraccion_adquirida IS NULL),
    CHECK(fecha_hora__presente=TRUE OR fecha_hora IS NULL),
    CHECK(fuente_edad_campo__presente=TRUE OR fuente_edad_campo IS NULL),
    CHECK(fuente_edad_fecha_aplicacion__presente=TRUE OR fuente_edad_fecha_aplicacion IS NULL),
    CHECK(fuente_edad_fecha_nacimiento__presente=TRUE OR fuente_edad_fecha_nacimiento IS NULL),
    CHECK(fuente_edad_registro__presente=TRUE OR fuente_edad_registro IS NULL),
    CHECK(fuente_edad_tabla__presente=TRUE OR fuente_edad_tabla IS NULL),
    CHECK(instrumento_cod_instrumento__presente=TRUE OR instrumento_cod_instrumento IS NULL),
    CHECK(instrumento_codigo__presente=TRUE OR instrumento_codigo IS NULL),
    CHECK(instrumento_descripcion__presente=TRUE OR instrumento_descripcion IS NULL),
    CHECK(instrumento_estado__presente=TRUE OR instrumento_estado IS NULL),
    CHECK(instrumento_nombre__presente=TRUE OR instrumento_nombre IS NULL),
    CHECK(instrumento_puntaje_maximo__presente=TRUE OR instrumento_puntaje_maximo__tipo='NULL'),
    CHECK((instrumento_puntaje_maximo__tipo='NULL' AND instrumento_puntaje_maximo_texto IS NULL AND instrumento_puntaje_maximo_entero IS NULL AND instrumento_puntaje_maximo_real IS NULL) OR (instrumento_puntaje_maximo__tipo='STRING' AND instrumento_puntaje_maximo_texto IS NOT NULL AND instrumento_puntaje_maximo_entero IS NULL AND instrumento_puntaje_maximo_real IS NULL) OR (instrumento_puntaje_maximo__tipo='INT' AND instrumento_puntaje_maximo_entero IS NOT NULL AND instrumento_puntaje_maximo_texto IS NULL AND instrumento_puntaje_maximo_real IS NULL) OR (instrumento_puntaje_maximo__tipo='FLOAT' AND instrumento_puntaje_maximo_real IS NOT NULL AND instrumento_puntaje_maximo_texto IS NULL AND instrumento_puntaje_maximo_entero IS NULL)),
    CHECK(instrumento_puntaje_maximo__cero_negativo=FALSE OR (instrumento_puntaje_maximo__tipo='FLOAT' AND instrumento_puntaje_maximo_real=0)),
    CHECK(instrumento_tipo__presente=TRUE OR instrumento_tipo IS NULL),
    CHECK(instrumento_version__presente=TRUE OR instrumento_version IS NULL),
    CHECK(motivos__estado='PRESENTE' OR motivos__cantidad=0),
    CHECK(nombre_instrumento__presente=TRUE OR nombre_instrumento IS NULL),
    CHECK(perfil_cod_instrumento__presente=TRUE OR perfil_cod_instrumento IS NULL),
    CHECK(perfil_codigo_instrumento__presente=TRUE OR perfil_codigo_instrumento IS NULL),
    CHECK(perfil_id__presente=TRUE OR perfil_id IS NULL),
    CHECK(perfil_identidad_edicion_protocolo__presente=TRUE OR perfil_identidad_edicion_protocolo IS NULL),
    CHECK(perfil_identidad_forma__presente=TRUE OR perfil_identidad_forma__tipo='NULL'),
    CHECK((perfil_identidad_forma__tipo='NULL' AND perfil_identidad_forma_texto IS NULL AND perfil_identidad_forma_entero IS NULL) OR (perfil_identidad_forma__tipo='STRING' AND perfil_identidad_forma_texto IS NOT NULL AND perfil_identidad_forma_entero IS NULL) OR (perfil_identidad_forma__tipo='INT' AND perfil_identidad_forma_entero IS NOT NULL AND perfil_identidad_forma_texto IS NULL)),
    CHECK(perfil_identidad_idioma__presente=TRUE OR perfil_identidad_idioma IS NULL),
    CHECK(perfil_identidad_instrumento__presente=TRUE OR perfil_identidad_instrumento IS NULL),
    CHECK(perfil_metodo__presente=TRUE OR perfil_metodo IS NULL),
    CHECK(perfil_selectores__estado='PRESENTE' OR perfil_selectores__cantidad=0),
    CHECK(perfil_version_instrumento__presente=TRUE OR perfil_version_instrumento IS NULL),
    CHECK(preguntas_declarativas_originales__estado='PRESENTE' OR preguntas_declarativas_originales__cantidad=0),
    CHECK(procedencia_cod_aplicacion_anterior__presente=TRUE OR procedencia_cod_aplicacion_anterior IS NULL),
    CHECK(procedencia_cod_documento_clinico__presente=TRUE OR procedencia_cod_documento_clinico IS NULL),
    CHECK(procedencia_cod_personal_aplicador__presente=TRUE OR procedencia_cod_personal_aplicador IS NULL),
    CHECK(procedencia_cod_usuario_registro__presente=TRUE OR procedencia_cod_usuario_registro IS NULL),
    CHECK(procedencia_estado_documento__presente=TRUE OR procedencia_estado_documento IS NULL),
    CHECK(procedencia_localizador_fuente__presente=TRUE OR procedencia_localizador_fuente IS NULL),
    CHECK(procedencia_registrado_en__presente=TRUE OR procedencia_registrado_en IS NULL),
    CHECK(respuestas_originales__estado='PRESENTE' OR respuestas_originales__cantidad=0),
    CHECK(version_instrumento__presente=TRUE OR version_instrumento IS NULL),
    CHECK(vinculos_preguntas_actuales__estado='PRESENTE' OR vinculos_preguntas_actuales__cantidad=0),
    CHECK(registro__estado='PRESENTE' OR (aplicacion__estado='AUSENTE' AND atencion_original__estado='AUSENTE' AND autor__presente=FALSE AND cod_aplicacion__presente=FALSE AND cod_instrumento__presente=FALSE AND cod_personal__presente=FALSE AND cod_residente__presente=FALSE AND codigo_instrumento__presente=FALSE AND documento_original__estado='AUSENTE' AND edad_en_aplicacion__presente=FALSE AND estado_original__presente=FALSE AND extraccion_adquirida__presente=FALSE AND fecha_hora__presente=FALSE AND fuente_edad__estado='AUSENTE' AND instrumento__estado='AUSENTE' AND motivos__estado='AUSENTE' AND nombre_instrumento__presente=FALSE AND perfil__estado='AUSENTE' AND preguntas_declarativas_originales__estado='AUSENTE' AND procedencia__estado='AUSENTE' AND respuestas_originales__estado='AUSENTE' AND version_instrumento__presente=FALSE AND vinculos_preguntas_actuales__estado='AUSENTE')),
    CHECK(aplicacion__estado='PRESENTE' OR (aplicacion_clasificacion__presente=FALSE AND aplicacion_cod_aplicacion__presente=FALSE AND aplicacion_cod_aplicacion_anterior__presente=FALSE AND aplicacion_cod_atencion__presente=FALSE AND aplicacion_cod_documento_clinico__presente=FALSE AND aplicacion_cod_instrumento__presente=FALSE AND aplicacion_cod_personal__presente=FALSE AND aplicacion_cod_residente__presente=FALSE AND aplicacion_cod_usuario_registro__presente=FALSE AND aplicacion_estado__presente=FALSE AND aplicacion_fecha_hora__presente=FALSE AND aplicacion_interpretacion__presente=FALSE AND aplicacion_localizador_fuente__presente=FALSE AND aplicacion_observacion__presente=FALSE AND aplicacion_puntaje_maximo__presente=FALSE AND aplicacion_puntaje_total__presente=FALSE AND aplicacion_registrado_en__presente=FALSE)),
    CHECK(atencion_original__estado='PRESENTE' OR (atencion_original_cod_atencion__presente=FALSE AND atencion_original_cod_residente__presente=FALSE)),
    CHECK(documento_original__estado='PRESENTE' OR (documento_original_cod_documento_clinico__presente=FALSE AND documento_original_cod_personal__presente=FALSE AND documento_original_cod_residente__presente=FALSE AND documento_original_estado__presente=FALSE AND documento_original_fecha_hora__presente=FALSE AND documento_original_hash_archivo__presente=FALSE)),
    CHECK(fuente_edad__estado='PRESENTE' OR (fuente_edad_campo__presente=FALSE AND fuente_edad_fecha_aplicacion__presente=FALSE AND fuente_edad_fecha_nacimiento__presente=FALSE AND fuente_edad_registro__presente=FALSE AND fuente_edad_tabla__presente=FALSE)),
    CHECK(instrumento__estado='PRESENTE' OR (instrumento_cod_instrumento__presente=FALSE AND instrumento_codigo__presente=FALSE AND instrumento_descripcion__presente=FALSE AND instrumento_estado__presente=FALSE AND instrumento_nombre__presente=FALSE AND instrumento_puntaje_maximo__presente=FALSE AND instrumento_tipo__presente=FALSE AND instrumento_version__presente=FALSE)),
    CHECK(perfil__estado='PRESENTE' OR (perfil_cod_instrumento__presente=FALSE AND perfil_codigo_instrumento__presente=FALSE AND perfil_id__presente=FALSE AND perfil_identidad__estado='AUSENTE' AND perfil_metodo__presente=FALSE AND perfil_selectores__estado='AUSENTE' AND perfil_version_instrumento__presente=FALSE)),
    CHECK(perfil_identidad__estado='PRESENTE' OR (perfil_identidad_edicion_protocolo__presente=FALSE AND perfil_identidad_forma__presente=FALSE AND perfil_identidad_idioma__presente=FALSE AND perfil_identidad_instrumento__presente=FALSE)),
    CHECK(procedencia__estado='PRESENTE' OR (procedencia_cod_aplicacion_anterior__presente=FALSE AND procedencia_cod_documento_clinico__presente=FALSE AND procedencia_cod_personal_aplicador__presente=FALSE AND procedencia_cod_usuario_registro__presente=FALSE AND procedencia_estado_documento__presente=FALSE AND procedencia_localizador_fuente__presente=FALSE AND procedencia_registrado_en__presente=FALSE))
);

CREATE TABLE motivos_captura (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    motivo__presente BOOLEAN NOT NULL,
    motivo TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C03fac49475')) OR (cod_propietario IS NOT NULL AND rol_coleccion IN ('Ca33175a241'))),
    CHECK((rol_coleccion='C03fac49475' AND clave_tipo ='NULL') OR (rol_coleccion='Ca33175a241' AND clave_tipo ='NULL')),
    CHECK(motivo__presente=TRUE OR motivo IS NULL)
);

CREATE TABLE selectores_captura (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente BOOLEAN NOT NULL,
    campo TEXT,
    cod_pregunta__presente BOOLEAN NOT NULL,
    cod_pregunta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C53b52bbfc6')) OR (cod_propietario IS NOT NULL AND rol_coleccion IN ('Cc9e61d6130'))),
    CHECK((rol_coleccion='C53b52bbfc6' AND clave_tipo IN ('INT','STRING')) OR (rol_coleccion='Cc9e61d6130' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente=TRUE OR campo IS NULL),
    CHECK(cod_pregunta__presente=TRUE OR cod_pregunta IS NULL),
    CHECK(registro__estado='PRESENTE' OR (campo__presente=FALSE AND cod_pregunta__presente=FALSE))
);

CREATE TABLE preguntas_captura (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_instrumento__presente BOOLEAN NOT NULL,
    cod_instrumento TEXT,
    cod_pregunta__presente BOOLEAN NOT NULL,
    cod_pregunta TEXT,
    codigo__presente BOOLEAN NOT NULL,
    codigo TEXT,
    dominio__presente BOOLEAN NOT NULL,
    dominio TEXT,
    enunciado__presente BOOLEAN NOT NULL,
    enunciado TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    orden__presente BOOLEAN NOT NULL,
    orden BIGINT,
    puntaje_maximo__presente BOOLEAN NOT NULL,
    puntaje_maximo__tipo VARCHAR(6) NOT NULL CHECK(puntaje_maximo__tipo IN ('NULL','STRING','INT','FLOAT')),
    puntaje_maximo_texto TEXT,
    puntaje_maximo_entero BIGINT,
    puntaje_maximo_real DOUBLE PRECISION,
    puntaje_maximo__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    tipo_respuesta__presente BOOLEAN NOT NULL,
    tipo_respuesta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('Ca070df23f8')) OR (cod_propietario IS NOT NULL AND rol_coleccion IN ('C52d856d563'))),
    CHECK((rol_coleccion='Ca070df23f8' AND clave_tipo IN ('INT','STRING')) OR (rol_coleccion='C52d856d563' AND clave_tipo IN ('INT','STRING'))),
    CHECK(cod_instrumento__presente=TRUE OR cod_instrumento IS NULL),
    CHECK(cod_pregunta__presente=TRUE OR cod_pregunta IS NULL),
    CHECK(codigo__presente=TRUE OR codigo IS NULL),
    CHECK(dominio__presente=TRUE OR dominio IS NULL),
    CHECK(enunciado__presente=TRUE OR enunciado IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(orden__presente=TRUE OR orden IS NULL),
    CHECK(puntaje_maximo__presente=TRUE OR puntaje_maximo__tipo='NULL'),
    CHECK((puntaje_maximo__tipo='NULL' AND puntaje_maximo_texto IS NULL AND puntaje_maximo_entero IS NULL AND puntaje_maximo_real IS NULL) OR (puntaje_maximo__tipo='STRING' AND puntaje_maximo_texto IS NOT NULL AND puntaje_maximo_entero IS NULL AND puntaje_maximo_real IS NULL) OR (puntaje_maximo__tipo='INT' AND puntaje_maximo_entero IS NOT NULL AND puntaje_maximo_texto IS NULL AND puntaje_maximo_real IS NULL) OR (puntaje_maximo__tipo='FLOAT' AND puntaje_maximo_real IS NOT NULL AND puntaje_maximo_texto IS NULL AND puntaje_maximo_entero IS NULL)),
    CHECK(puntaje_maximo__cero_negativo=FALSE OR (puntaje_maximo__tipo='FLOAT' AND puntaje_maximo_real=0)),
    CHECK(tipo_respuesta__presente=TRUE OR tipo_respuesta IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_instrumento__presente=FALSE AND cod_pregunta__presente=FALSE AND codigo__presente=FALSE AND dominio__presente=FALSE AND enunciado__presente=FALSE AND estado__presente=FALSE AND orden__presente=FALSE AND puntaje_maximo__presente=FALSE AND tipo_respuesta__presente=FALSE))
);

CREATE TABLE respuestas_captura (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_aplicacion__presente BOOLEAN NOT NULL,
    cod_aplicacion TEXT,
    cod_opcion__presente BOOLEAN NOT NULL,
    cod_opcion TEXT,
    cod_pregunta__presente BOOLEAN NOT NULL,
    cod_pregunta TEXT,
    cod_respuesta__presente BOOLEAN NOT NULL,
    cod_respuesta TEXT,
    codigo_pregunta__presente BOOLEAN NOT NULL,
    codigo_pregunta TEXT,
    coherente__presente BOOLEAN NOT NULL,
    coherente BOOLEAN,
    dominio_original__presente BOOLEAN NOT NULL,
    dominio_original TEXT,
    estado_pregunta_actual__presente BOOLEAN NOT NULL,
    estado_pregunta_actual TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    opcion__estado VARCHAR(8) NOT NULL CHECK(opcion__estado IN ('AUSENTE','NULL','PRESENTE')),
    opcion_cod_opcion__presente BOOLEAN NOT NULL,
    opcion_cod_opcion TEXT,
    opcion_cod_pregunta__presente BOOLEAN NOT NULL,
    opcion_cod_pregunta TEXT,
    opcion_estado__presente BOOLEAN NOT NULL,
    opcion_estado TEXT,
    opcion_nombre__presente BOOLEAN NOT NULL,
    opcion_nombre TEXT,
    opcion_orden__presente BOOLEAN NOT NULL,
    opcion_orden BIGINT,
    opcion_puntaje__presente BOOLEAN NOT NULL,
    opcion_puntaje__tipo VARCHAR(6) NOT NULL CHECK(opcion_puntaje__tipo IN ('NULL','STRING','INT','FLOAT')),
    opcion_puntaje_texto TEXT,
    opcion_puntaje_entero BIGINT,
    opcion_puntaje_real DOUBLE PRECISION,
    opcion_puntaje__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    opcion_valor__presente BOOLEAN NOT NULL,
    opcion_valor TEXT,
    pregunta__presente BOOLEAN NOT NULL,
    pregunta TEXT,
    pregunta__estado VARCHAR(8) NOT NULL CHECK(pregunta__estado IN ('AUSENTE','NULL','PRESENTE')),
    pregunta_cod_instrumento__presente BOOLEAN NOT NULL,
    pregunta_cod_instrumento TEXT,
    pregunta_cod_pregunta__presente BOOLEAN NOT NULL,
    pregunta_cod_pregunta TEXT,
    pregunta_codigo__presente BOOLEAN NOT NULL,
    pregunta_codigo TEXT,
    pregunta_dominio__presente BOOLEAN NOT NULL,
    pregunta_dominio TEXT,
    pregunta_enunciado__presente BOOLEAN NOT NULL,
    pregunta_enunciado TEXT,
    pregunta_estado__presente BOOLEAN NOT NULL,
    pregunta_estado TEXT,
    pregunta_orden__presente BOOLEAN NOT NULL,
    pregunta_orden BIGINT,
    pregunta_puntaje_maximo__presente BOOLEAN NOT NULL,
    pregunta_puntaje_maximo__tipo VARCHAR(6) NOT NULL CHECK(pregunta_puntaje_maximo__tipo IN ('NULL','STRING','INT','FLOAT')),
    pregunta_puntaje_maximo_texto TEXT,
    pregunta_puntaje_maximo_entero BIGINT,
    pregunta_puntaje_maximo_real DOUBLE PRECISION,
    pregunta_puntaje_maximo__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    pregunta_tipo_respuesta__presente BOOLEAN NOT NULL,
    pregunta_tipo_respuesta TEXT,
    puntaje__presente BOOLEAN NOT NULL,
    puntaje__tipo VARCHAR(6) NOT NULL CHECK(puntaje__tipo IN ('NULL','STRING','INT','FLOAT')),
    puntaje_texto TEXT,
    puntaje_entero BIGINT,
    puntaje_real DOUBLE PRECISION,
    puntaje__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    tipo_respuesta__presente BOOLEAN NOT NULL,
    tipo_respuesta TEXT,
    valor_logico__presente BOOLEAN NOT NULL,
    valor_logico__tipo VARCHAR(6) NOT NULL CHECK(valor_logico__tipo IN ('NULL','BOOL','INT')),
    valor_logico_logico BOOLEAN,
    valor_logico_entero BIGINT,
    valor_numero__presente BOOLEAN NOT NULL,
    valor_numero__tipo VARCHAR(6) NOT NULL CHECK(valor_numero__tipo IN ('NULL','STRING','INT','FLOAT')),
    valor_numero_texto TEXT,
    valor_numero_entero BIGINT,
    valor_numero_real DOUBLE PRECISION,
    valor_numero__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    valor_texto__presente BOOLEAN NOT NULL,
    valor_texto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C388d96740f')) OR (cod_propietario IS NOT NULL AND rol_coleccion IN ('C014dd1988b'))),
    CHECK((rol_coleccion='C388d96740f' AND clave_tipo ='NULL') OR (rol_coleccion='C014dd1988b' AND clave_tipo ='NULL')),
    CHECK(cod_aplicacion__presente=TRUE OR cod_aplicacion IS NULL),
    CHECK(cod_opcion__presente=TRUE OR cod_opcion IS NULL),
    CHECK(cod_pregunta__presente=TRUE OR cod_pregunta IS NULL),
    CHECK(cod_respuesta__presente=TRUE OR cod_respuesta IS NULL),
    CHECK(codigo_pregunta__presente=TRUE OR codigo_pregunta IS NULL),
    CHECK(coherente__presente=TRUE OR coherente IS NULL),
    CHECK(dominio_original__presente=TRUE OR dominio_original IS NULL),
    CHECK(estado_pregunta_actual__presente=TRUE OR estado_pregunta_actual IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(opcion_cod_opcion__presente=TRUE OR opcion_cod_opcion IS NULL),
    CHECK(opcion_cod_pregunta__presente=TRUE OR opcion_cod_pregunta IS NULL),
    CHECK(opcion_estado__presente=TRUE OR opcion_estado IS NULL),
    CHECK(opcion_nombre__presente=TRUE OR opcion_nombre IS NULL),
    CHECK(opcion_orden__presente=TRUE OR opcion_orden IS NULL),
    CHECK(opcion_puntaje__presente=TRUE OR opcion_puntaje__tipo='NULL'),
    CHECK((opcion_puntaje__tipo='NULL' AND opcion_puntaje_texto IS NULL AND opcion_puntaje_entero IS NULL AND opcion_puntaje_real IS NULL) OR (opcion_puntaje__tipo='STRING' AND opcion_puntaje_texto IS NOT NULL AND opcion_puntaje_entero IS NULL AND opcion_puntaje_real IS NULL) OR (opcion_puntaje__tipo='INT' AND opcion_puntaje_entero IS NOT NULL AND opcion_puntaje_texto IS NULL AND opcion_puntaje_real IS NULL) OR (opcion_puntaje__tipo='FLOAT' AND opcion_puntaje_real IS NOT NULL AND opcion_puntaje_texto IS NULL AND opcion_puntaje_entero IS NULL)),
    CHECK(opcion_puntaje__cero_negativo=FALSE OR (opcion_puntaje__tipo='FLOAT' AND opcion_puntaje_real=0)),
    CHECK(opcion_valor__presente=TRUE OR opcion_valor IS NULL),
    CHECK(pregunta__presente=TRUE OR pregunta IS NULL),
    CHECK(pregunta_cod_instrumento__presente=TRUE OR pregunta_cod_instrumento IS NULL),
    CHECK(pregunta_cod_pregunta__presente=TRUE OR pregunta_cod_pregunta IS NULL),
    CHECK(pregunta_codigo__presente=TRUE OR pregunta_codigo IS NULL),
    CHECK(pregunta_dominio__presente=TRUE OR pregunta_dominio IS NULL),
    CHECK(pregunta_enunciado__presente=TRUE OR pregunta_enunciado IS NULL),
    CHECK(pregunta_estado__presente=TRUE OR pregunta_estado IS NULL),
    CHECK(pregunta_orden__presente=TRUE OR pregunta_orden IS NULL),
    CHECK(pregunta_puntaje_maximo__presente=TRUE OR pregunta_puntaje_maximo__tipo='NULL'),
    CHECK((pregunta_puntaje_maximo__tipo='NULL' AND pregunta_puntaje_maximo_texto IS NULL AND pregunta_puntaje_maximo_entero IS NULL AND pregunta_puntaje_maximo_real IS NULL) OR (pregunta_puntaje_maximo__tipo='STRING' AND pregunta_puntaje_maximo_texto IS NOT NULL AND pregunta_puntaje_maximo_entero IS NULL AND pregunta_puntaje_maximo_real IS NULL) OR (pregunta_puntaje_maximo__tipo='INT' AND pregunta_puntaje_maximo_entero IS NOT NULL AND pregunta_puntaje_maximo_texto IS NULL AND pregunta_puntaje_maximo_real IS NULL) OR (pregunta_puntaje_maximo__tipo='FLOAT' AND pregunta_puntaje_maximo_real IS NOT NULL AND pregunta_puntaje_maximo_texto IS NULL AND pregunta_puntaje_maximo_entero IS NULL)),
    CHECK(pregunta_puntaje_maximo__cero_negativo=FALSE OR (pregunta_puntaje_maximo__tipo='FLOAT' AND pregunta_puntaje_maximo_real=0)),
    CHECK(pregunta_tipo_respuesta__presente=TRUE OR pregunta_tipo_respuesta IS NULL),
    CHECK(puntaje__presente=TRUE OR puntaje__tipo='NULL'),
    CHECK((puntaje__tipo='NULL' AND puntaje_texto IS NULL AND puntaje_entero IS NULL AND puntaje_real IS NULL) OR (puntaje__tipo='STRING' AND puntaje_texto IS NOT NULL AND puntaje_entero IS NULL AND puntaje_real IS NULL) OR (puntaje__tipo='INT' AND puntaje_entero IS NOT NULL AND puntaje_texto IS NULL AND puntaje_real IS NULL) OR (puntaje__tipo='FLOAT' AND puntaje_real IS NOT NULL AND puntaje_texto IS NULL AND puntaje_entero IS NULL)),
    CHECK(puntaje__cero_negativo=FALSE OR (puntaje__tipo='FLOAT' AND puntaje_real=0)),
    CHECK(tipo_respuesta__presente=TRUE OR tipo_respuesta IS NULL),
    CHECK(valor_logico__presente=TRUE OR valor_logico__tipo='NULL'),
    CHECK((valor_logico__tipo='NULL' AND valor_logico_logico IS NULL AND valor_logico_entero IS NULL) OR (valor_logico__tipo='BOOL' AND valor_logico_logico IS NOT NULL AND valor_logico_entero IS NULL) OR (valor_logico__tipo='INT' AND valor_logico_entero IS NOT NULL AND valor_logico_logico IS NULL)),
    CHECK(valor_numero__presente=TRUE OR valor_numero__tipo='NULL'),
    CHECK((valor_numero__tipo='NULL' AND valor_numero_texto IS NULL AND valor_numero_entero IS NULL AND valor_numero_real IS NULL) OR (valor_numero__tipo='STRING' AND valor_numero_texto IS NOT NULL AND valor_numero_entero IS NULL AND valor_numero_real IS NULL) OR (valor_numero__tipo='INT' AND valor_numero_entero IS NOT NULL AND valor_numero_texto IS NULL AND valor_numero_real IS NULL) OR (valor_numero__tipo='FLOAT' AND valor_numero_real IS NOT NULL AND valor_numero_texto IS NULL AND valor_numero_entero IS NULL)),
    CHECK(valor_numero__cero_negativo=FALSE OR (valor_numero__tipo='FLOAT' AND valor_numero_real=0)),
    CHECK(valor_texto__presente=TRUE OR valor_texto IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_aplicacion__presente=FALSE AND cod_opcion__presente=FALSE AND cod_pregunta__presente=FALSE AND cod_respuesta__presente=FALSE AND codigo_pregunta__presente=FALSE AND coherente__presente=FALSE AND dominio_original__presente=FALSE AND estado_pregunta_actual__presente=FALSE AND observacion__presente=FALSE AND opcion__estado='AUSENTE' AND pregunta__presente=FALSE AND pregunta__estado='AUSENTE' AND puntaje__presente=FALSE AND tipo_respuesta__presente=FALSE AND valor_logico__presente=FALSE AND valor_numero__presente=FALSE AND valor_texto__presente=FALSE)),
    CHECK(opcion__estado='PRESENTE' OR (opcion_cod_opcion__presente=FALSE AND opcion_cod_pregunta__presente=FALSE AND opcion_estado__presente=FALSE AND opcion_nombre__presente=FALSE AND opcion_orden__presente=FALSE AND opcion_puntaje__presente=FALSE AND opcion_valor__presente=FALSE)),
    CHECK(pregunta__estado='PRESENTE' OR (pregunta_cod_instrumento__presente=FALSE AND pregunta_cod_pregunta__presente=FALSE AND pregunta_codigo__presente=FALSE AND pregunta_dominio__presente=FALSE AND pregunta_enunciado__presente=FALSE AND pregunta_estado__presente=FALSE AND pregunta_orden__presente=FALSE AND pregunta_puntaje_maximo__presente=FALSE AND pregunta_tipo_respuesta__presente=FALSE))
);

CREATE TABLE vinculos_pregunta_captura (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    pertenece_al_instrumento__presente BOOLEAN NOT NULL,
    pertenece_al_instrumento BOOLEAN,
    pregunta__estado VARCHAR(8) NOT NULL CHECK(pregunta__estado IN ('AUSENTE','NULL','PRESENTE')),
    pregunta_cod_instrumento__presente BOOLEAN NOT NULL,
    pregunta_cod_instrumento TEXT,
    pregunta_cod_pregunta__presente BOOLEAN NOT NULL,
    pregunta_cod_pregunta TEXT,
    pregunta_codigo__presente BOOLEAN NOT NULL,
    pregunta_codigo TEXT,
    pregunta_dominio__presente BOOLEAN NOT NULL,
    pregunta_dominio TEXT,
    pregunta_enunciado__presente BOOLEAN NOT NULL,
    pregunta_enunciado TEXT,
    pregunta_estado__presente BOOLEAN NOT NULL,
    pregunta_estado TEXT,
    pregunta_orden__presente BOOLEAN NOT NULL,
    pregunta_orden BIGINT,
    pregunta_puntaje_maximo__presente BOOLEAN NOT NULL,
    pregunta_puntaje_maximo__tipo VARCHAR(6) NOT NULL CHECK(pregunta_puntaje_maximo__tipo IN ('NULL','STRING','INT','FLOAT')),
    pregunta_puntaje_maximo_texto TEXT,
    pregunta_puntaje_maximo_entero BIGINT,
    pregunta_puntaje_maximo_real DOUBLE PRECISION,
    pregunta_puntaje_maximo__cero_negativo BOOLEAN NOT NULL DEFAULT FALSE,
    pregunta_tipo_respuesta__presente BOOLEAN NOT NULL,
    pregunta_tipo_respuesta TEXT,
    selector__estado VARCHAR(8) NOT NULL CHECK(selector__estado IN ('AUSENTE','NULL','PRESENTE')),
    selector_campo__presente BOOLEAN NOT NULL,
    selector_campo TEXT,
    selector_cod_pregunta__presente BOOLEAN NOT NULL,
    selector_cod_pregunta TEXT,
    tiene_respuesta__presente BOOLEAN NOT NULL,
    tiene_respuesta BOOLEAN,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C55341a554f')) OR (cod_propietario IS NOT NULL AND rol_coleccion IN ('C7b4dbb2388'))),
    CHECK((rol_coleccion='C55341a554f' AND clave_tipo IN ('INT','STRING')) OR (rol_coleccion='C7b4dbb2388' AND clave_tipo IN ('INT','STRING'))),
    CHECK(pertenece_al_instrumento__presente=TRUE OR pertenece_al_instrumento IS NULL),
    CHECK(pregunta_cod_instrumento__presente=TRUE OR pregunta_cod_instrumento IS NULL),
    CHECK(pregunta_cod_pregunta__presente=TRUE OR pregunta_cod_pregunta IS NULL),
    CHECK(pregunta_codigo__presente=TRUE OR pregunta_codigo IS NULL),
    CHECK(pregunta_dominio__presente=TRUE OR pregunta_dominio IS NULL),
    CHECK(pregunta_enunciado__presente=TRUE OR pregunta_enunciado IS NULL),
    CHECK(pregunta_estado__presente=TRUE OR pregunta_estado IS NULL),
    CHECK(pregunta_orden__presente=TRUE OR pregunta_orden IS NULL),
    CHECK(pregunta_puntaje_maximo__presente=TRUE OR pregunta_puntaje_maximo__tipo='NULL'),
    CHECK((pregunta_puntaje_maximo__tipo='NULL' AND pregunta_puntaje_maximo_texto IS NULL AND pregunta_puntaje_maximo_entero IS NULL AND pregunta_puntaje_maximo_real IS NULL) OR (pregunta_puntaje_maximo__tipo='STRING' AND pregunta_puntaje_maximo_texto IS NOT NULL AND pregunta_puntaje_maximo_entero IS NULL AND pregunta_puntaje_maximo_real IS NULL) OR (pregunta_puntaje_maximo__tipo='INT' AND pregunta_puntaje_maximo_entero IS NOT NULL AND pregunta_puntaje_maximo_texto IS NULL AND pregunta_puntaje_maximo_real IS NULL) OR (pregunta_puntaje_maximo__tipo='FLOAT' AND pregunta_puntaje_maximo_real IS NOT NULL AND pregunta_puntaje_maximo_texto IS NULL AND pregunta_puntaje_maximo_entero IS NULL)),
    CHECK(pregunta_puntaje_maximo__cero_negativo=FALSE OR (pregunta_puntaje_maximo__tipo='FLOAT' AND pregunta_puntaje_maximo_real=0)),
    CHECK(pregunta_tipo_respuesta__presente=TRUE OR pregunta_tipo_respuesta IS NULL),
    CHECK(selector_campo__presente=TRUE OR selector_campo IS NULL),
    CHECK(selector_cod_pregunta__presente=TRUE OR selector_cod_pregunta IS NULL),
    CHECK(tiene_respuesta__presente=TRUE OR tiene_respuesta IS NULL),
    CHECK(registro__estado='PRESENTE' OR (pertenece_al_instrumento__presente=FALSE AND pregunta__estado='AUSENTE' AND selector__estado='AUSENTE' AND tiene_respuesta__presente=FALSE)),
    CHECK(pregunta__estado='PRESENTE' OR (pregunta_cod_instrumento__presente=FALSE AND pregunta_cod_pregunta__presente=FALSE AND pregunta_codigo__presente=FALSE AND pregunta_dominio__presente=FALSE AND pregunta_enunciado__presente=FALSE AND pregunta_estado__presente=FALSE AND pregunta_orden__presente=FALSE AND pregunta_puntaje_maximo__presente=FALSE AND pregunta_tipo_respuesta__presente=FALSE)),
    CHECK(selector__estado='PRESENTE' OR (selector_campo__presente=FALSE AND selector_cod_pregunta__presente=FALSE))
);

CREATE TABLE contratos_relacion_adquiridos (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    habilita_participacion__presente BOOLEAN NOT NULL,
    habilita_participacion BOOLEAN,
    modalidades__estado VARCHAR(8) NOT NULL CHECK(modalidades__estado IN ('AUSENTE','NULL','PRESENTE')),
    modalidades__cantidad BIGINT NOT NULL CHECK(modalidades__cantidad>=0),
    tipo_destino__presente BOOLEAN NOT NULL,
    tipo_destino TEXT,
    tipo_origen__presente BOOLEAN NOT NULL,
    tipo_origen TEXT,
    tipo_relacion__presente BOOLEAN NOT NULL,
    tipo_relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C4f168aeb0b')) OR (cod_propietario IS NOT NULL AND rol_coleccion IN ('C91c39626e7'))),
    CHECK((rol_coleccion='C4f168aeb0b' AND clave_tipo ='NULL') OR (rol_coleccion='C91c39626e7' AND clave_tipo ='NULL')),
    CHECK(habilita_participacion__presente=TRUE OR habilita_participacion IS NULL),
    CHECK(modalidades__estado='PRESENTE' OR modalidades__cantidad=0),
    CHECK(tipo_destino__presente=TRUE OR tipo_destino IS NULL),
    CHECK(tipo_origen__presente=TRUE OR tipo_origen IS NULL),
    CHECK(tipo_relacion__presente=TRUE OR tipo_relacion IS NULL),
    CHECK(registro__estado='PRESENTE' OR (habilita_participacion__presente=FALSE AND modalidades__estado='AUSENTE' AND tipo_destino__presente=FALSE AND tipo_origen__presente=FALSE AND tipo_relacion__presente=FALSE))
);

CREATE TABLE modalidades_contrato_adquirido (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    modalidad__presente BOOLEAN NOT NULL,
    modalidad TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C9e85749841')) OR (cod_propietario IS NOT NULL AND rol_coleccion IN ('Cdb8280ab29'))),
    CHECK((rol_coleccion='C9e85749841' AND clave_tipo ='NULL') OR (rol_coleccion='Cdb8280ab29' AND clave_tipo ='NULL')),
    CHECK(modalidad__presente=TRUE OR modalidad IS NULL)
);

CREATE TABLE conocimiento_condiciones_regla_experta (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_condicion_regla__presente BOOLEAN NOT NULL,
    cod_condicion_regla TEXT,
    cod_regla_experta__presente BOOLEAN NOT NULL,
    cod_regla_experta TEXT,
    cod_valor_semantico__presente BOOLEAN NOT NULL,
    cod_valor_semantico TEXT,
    cod_variable_experta__presente BOOLEAN NOT NULL,
    cod_variable_experta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_condicion_regla__presente=TRUE OR cod_condicion_regla IS NULL),
    CHECK(cod_regla_experta__presente=TRUE OR cod_regla_experta IS NULL),
    CHECK(cod_valor_semantico__presente=TRUE OR cod_valor_semantico IS NULL),
    CHECK(cod_variable_experta__presente=TRUE OR cod_variable_experta IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_condicion_regla__presente=FALSE AND cod_regla_experta__presente=FALSE AND cod_valor_semantico__presente=FALSE AND cod_variable_experta__presente=FALSE))
);

CREATE TABLE conocimiento_consecuencias_regla_experta (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_consecuencia_regla__presente BOOLEAN NOT NULL,
    cod_consecuencia_regla TEXT,
    cod_criterio_dominio_resultado__presente BOOLEAN NOT NULL,
    cod_criterio_dominio_resultado TEXT,
    cod_regla_experta__presente BOOLEAN NOT NULL,
    cod_regla_experta TEXT,
    cod_valor_semantico__presente BOOLEAN NOT NULL,
    cod_valor_semantico TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_consecuencia_regla__presente=TRUE OR cod_consecuencia_regla IS NULL),
    CHECK(cod_criterio_dominio_resultado__presente=TRUE OR cod_criterio_dominio_resultado IS NULL),
    CHECK(cod_regla_experta__presente=TRUE OR cod_regla_experta IS NULL),
    CHECK(cod_valor_semantico__presente=TRUE OR cod_valor_semantico IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_consecuencia_regla__presente=FALSE AND cod_criterio_dominio_resultado__presente=FALSE AND cod_regla_experta__presente=FALSE AND cod_valor_semantico__presente=FALSE))
);

CREATE TABLE conocimiento_criterios_dominios_resultado (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_criterio_dominio_resultado__presente BOOLEAN NOT NULL,
    cod_criterio_dominio_resultado TEXT,
    cod_dominio_valores__presente BOOLEAN NOT NULL,
    cod_dominio_valores TEXT,
    cod_nodo_criterio__presente BOOLEAN NOT NULL,
    cod_nodo_criterio TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_criterio_dominio_resultado__presente=TRUE OR cod_criterio_dominio_resultado IS NULL),
    CHECK(cod_dominio_valores__presente=TRUE OR cod_dominio_valores IS NULL),
    CHECK(cod_nodo_criterio__presente=TRUE OR cod_nodo_criterio IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_criterio_dominio_resultado__presente=FALSE AND cod_dominio_valores__presente=FALSE AND cod_nodo_criterio__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND estado__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND observacion__presente=FALSE))
);

CREATE TABLE conocimiento_dominios_valores_expertos (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente BOOLEAN NOT NULL,
    cod_dominio_valores TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente BOOLEAN NOT NULL,
    cod_version_modelo TEXT,
    codigo_dominio__presente BOOLEAN NOT NULL,
    codigo_dominio TEXT,
    descripcion__presente BOOLEAN NOT NULL,
    descripcion TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    nombre__presente BOOLEAN NOT NULL,
    nombre TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    tipo_dominio__presente BOOLEAN NOT NULL,
    tipo_dominio TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente=TRUE OR cod_dominio_valores IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente=TRUE OR cod_version_modelo IS NULL),
    CHECK(codigo_dominio__presente=TRUE OR codigo_dominio IS NULL),
    CHECK(descripcion__presente=TRUE OR descripcion IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(nombre__presente=TRUE OR nombre IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(tipo_dominio__presente=TRUE OR tipo_dominio IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_dominio_valores__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_version_modelo__presente=FALSE AND codigo_dominio__presente=FALSE AND descripcion__presente=FALSE AND estado__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND nombre__presente=FALSE AND observacion__presente=FALSE AND tipo_dominio__presente=FALSE))
);

CREATE TABLE conocimiento_fuentes_datos_expertas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo_estado__presente BOOLEAN NOT NULL,
    campo_estado TEXT,
    campo_personal__presente BOOLEAN NOT NULL,
    campo_personal TEXT,
    campo_pk_raiz__presente BOOLEAN NOT NULL,
    campo_pk_raiz TEXT,
    campo_residente__presente BOOLEAN NOT NULL,
    campo_residente TEXT,
    campo_temporal__presente BOOLEAN NOT NULL,
    campo_temporal TEXT,
    clave_adaptador__presente BOOLEAN NOT NULL,
    clave_adaptador TEXT,
    cod_fuente_dato_experta__presente BOOLEAN NOT NULL,
    cod_fuente_dato_experta TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente BOOLEAN NOT NULL,
    cod_version_modelo TEXT,
    codigo_fuente__presente BOOLEAN NOT NULL,
    codigo_fuente TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    nombre__presente BOOLEAN NOT NULL,
    nombre TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    tabla_raiz__presente BOOLEAN NOT NULL,
    tabla_raiz TEXT,
    tipo_fuente__presente BOOLEAN NOT NULL,
    tipo_fuente TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(campo_estado__presente=TRUE OR campo_estado IS NULL),
    CHECK(campo_personal__presente=TRUE OR campo_personal IS NULL),
    CHECK(campo_pk_raiz__presente=TRUE OR campo_pk_raiz IS NULL),
    CHECK(campo_residente__presente=TRUE OR campo_residente IS NULL),
    CHECK(campo_temporal__presente=TRUE OR campo_temporal IS NULL),
    CHECK(clave_adaptador__presente=TRUE OR clave_adaptador IS NULL),
    CHECK(cod_fuente_dato_experta__presente=TRUE OR cod_fuente_dato_experta IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente=TRUE OR cod_version_modelo IS NULL),
    CHECK(codigo_fuente__presente=TRUE OR codigo_fuente IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(nombre__presente=TRUE OR nombre IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(tabla_raiz__presente=TRUE OR tabla_raiz IS NULL),
    CHECK(tipo_fuente__presente=TRUE OR tipo_fuente IS NULL),
    CHECK(registro__estado='PRESENTE' OR (campo_estado__presente=FALSE AND campo_personal__presente=FALSE AND campo_pk_raiz__presente=FALSE AND campo_residente__presente=FALSE AND campo_temporal__presente=FALSE AND clave_adaptador__presente=FALSE AND cod_fuente_dato_experta__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_version_modelo__presente=FALSE AND codigo_fuente__presente=FALSE AND estado__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND nombre__presente=FALSE AND observacion__presente=FALSE AND tabla_raiz__presente=FALSE AND tipo_fuente__presente=FALSE))
);

CREATE TABLE conocimiento_mapeos_valores_fuente (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_mapeo_valor_fuente__presente BOOLEAN NOT NULL,
    cod_mapeo_valor_fuente TEXT,
    cod_mapeo_variable_fuente__presente BOOLEAN NOT NULL,
    cod_mapeo_variable_fuente TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_valor_semantico__presente BOOLEAN NOT NULL,
    cod_valor_semantico TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    estado_aprobacion__presente BOOLEAN NOT NULL,
    estado_aprobacion TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    fecha_hora_vigencia_desde__presente BOOLEAN NOT NULL,
    fecha_hora_vigencia_desde TEXT,
    fecha_hora_vigencia_hasta__presente BOOLEAN NOT NULL,
    fecha_hora_vigencia_hasta TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    tipo_valor_fuente__presente BOOLEAN NOT NULL,
    tipo_valor_fuente TEXT,
    valor_fuente_exacto__presente BOOLEAN NOT NULL,
    valor_fuente_exacto TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_mapeo_valor_fuente__presente=TRUE OR cod_mapeo_valor_fuente IS NULL),
    CHECK(cod_mapeo_variable_fuente__presente=TRUE OR cod_mapeo_variable_fuente IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_valor_semantico__presente=TRUE OR cod_valor_semantico IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(estado_aprobacion__presente=TRUE OR estado_aprobacion IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(fecha_hora_vigencia_desde__presente=TRUE OR fecha_hora_vigencia_desde IS NULL),
    CHECK(fecha_hora_vigencia_hasta__presente=TRUE OR fecha_hora_vigencia_hasta IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(tipo_valor_fuente__presente=TRUE OR tipo_valor_fuente IS NULL),
    CHECK(valor_fuente_exacto__presente=TRUE OR valor_fuente_exacto IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_mapeo_valor_fuente__presente=FALSE AND cod_mapeo_variable_fuente__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_valor_semantico__presente=FALSE AND estado__presente=FALSE AND estado_aprobacion__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND fecha_hora_vigencia_desde__presente=FALSE AND fecha_hora_vigencia_hasta__presente=FALSE AND observacion__presente=FALSE AND tipo_valor_fuente__presente=FALSE AND valor_fuente_exacto__presente=FALSE))
);

CREATE TABLE conocimiento_mapeos_variables_fuente (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo_valor__presente BOOLEAN NOT NULL,
    campo_valor TEXT,
    clave_selector__presente BOOLEAN NOT NULL,
    clave_selector TEXT,
    cod_fuente_dato_experta__presente BOOLEAN NOT NULL,
    cod_fuente_dato_experta TEXT,
    cod_mapeo_variable_fuente__presente BOOLEAN NOT NULL,
    cod_mapeo_variable_fuente TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_variable_experta__presente BOOLEAN NOT NULL,
    cod_variable_experta TEXT,
    cod_version_modelo__presente BOOLEAN NOT NULL,
    cod_version_modelo TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    tipo_extraccion__presente BOOLEAN NOT NULL,
    tipo_extraccion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(campo_valor__presente=TRUE OR campo_valor IS NULL),
    CHECK(clave_selector__presente=TRUE OR clave_selector IS NULL),
    CHECK(cod_fuente_dato_experta__presente=TRUE OR cod_fuente_dato_experta IS NULL),
    CHECK(cod_mapeo_variable_fuente__presente=TRUE OR cod_mapeo_variable_fuente IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_variable_experta__presente=TRUE OR cod_variable_experta IS NULL),
    CHECK(cod_version_modelo__presente=TRUE OR cod_version_modelo IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(tipo_extraccion__presente=TRUE OR tipo_extraccion IS NULL),
    CHECK(registro__estado='PRESENTE' OR (campo_valor__presente=FALSE AND clave_selector__presente=FALSE AND cod_fuente_dato_experta__presente=FALSE AND cod_mapeo_variable_fuente__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_variable_experta__presente=FALSE AND cod_version_modelo__presente=FALSE AND estado__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND observacion__presente=FALSE AND tipo_extraccion__presente=FALSE))
);

CREATE TABLE conocimiento_nodos_semanticos (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_nodo_semantico__presente BOOLEAN NOT NULL,
    cod_nodo_semantico TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente BOOLEAN NOT NULL,
    cod_version_modelo TEXT,
    codigo_semantico__presente BOOLEAN NOT NULL,
    codigo_semantico TEXT,
    definicion__presente BOOLEAN NOT NULL,
    definicion TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    nombre__presente BOOLEAN NOT NULL,
    nombre TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    tipo_nodo__presente BOOLEAN NOT NULL,
    tipo_nodo TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_nodo_semantico__presente=TRUE OR cod_nodo_semantico IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente=TRUE OR cod_version_modelo IS NULL),
    CHECK(codigo_semantico__presente=TRUE OR codigo_semantico IS NULL),
    CHECK(definicion__presente=TRUE OR definicion IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(nombre__presente=TRUE OR nombre IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(tipo_nodo__presente=TRUE OR tipo_nodo IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_nodo_semantico__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_version_modelo__presente=FALSE AND codigo_semantico__presente=FALSE AND definicion__presente=FALSE AND estado__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND nombre__presente=FALSE AND observacion__presente=FALSE AND tipo_nodo__presente=FALSE))
);

CREATE TABLE conocimiento_reglas_expertas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_regla_anterior__presente BOOLEAN NOT NULL,
    cod_regla_anterior TEXT,
    cod_regla_experta__presente BOOLEAN NOT NULL,
    cod_regla_experta TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_usuario_revision__presente BOOLEAN NOT NULL,
    cod_usuario_revision TEXT,
    cod_version_modelo__presente BOOLEAN NOT NULL,
    cod_version_modelo TEXT,
    codigo_regla__presente BOOLEAN NOT NULL,
    codigo_regla TEXT,
    descripcion__presente BOOLEAN NOT NULL,
    descripcion TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    estado_revision__presente BOOLEAN NOT NULL,
    estado_revision TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    fecha_hora_revision__presente BOOLEAN NOT NULL,
    fecha_hora_revision TEXT,
    justificacion_revision__presente BOOLEAN NOT NULL,
    justificacion_revision TEXT,
    nombre__presente BOOLEAN NOT NULL,
    nombre TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    tipo_regla__presente BOOLEAN NOT NULL,
    tipo_regla TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_regla_anterior__presente=TRUE OR cod_regla_anterior IS NULL),
    CHECK(cod_regla_experta__presente=TRUE OR cod_regla_experta IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_usuario_revision__presente=TRUE OR cod_usuario_revision IS NULL),
    CHECK(cod_version_modelo__presente=TRUE OR cod_version_modelo IS NULL),
    CHECK(codigo_regla__presente=TRUE OR codigo_regla IS NULL),
    CHECK(descripcion__presente=TRUE OR descripcion IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(estado_revision__presente=TRUE OR estado_revision IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(fecha_hora_revision__presente=TRUE OR fecha_hora_revision IS NULL),
    CHECK(justificacion_revision__presente=TRUE OR justificacion_revision IS NULL),
    CHECK(nombre__presente=TRUE OR nombre IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(tipo_regla__presente=TRUE OR tipo_regla IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_regla_anterior__presente=FALSE AND cod_regla_experta__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_usuario_revision__presente=FALSE AND cod_version_modelo__presente=FALSE AND codigo_regla__presente=FALSE AND descripcion__presente=FALSE AND estado__presente=FALSE AND estado_revision__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND fecha_hora_revision__presente=FALSE AND justificacion_revision__presente=FALSE AND nombre__presente=FALSE AND observacion__presente=FALSE AND tipo_regla__presente=FALSE))
);

CREATE TABLE conocimiento_relaciones_semanticas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_nodo_destino__presente BOOLEAN NOT NULL,
    cod_nodo_destino TEXT,
    cod_nodo_origen__presente BOOLEAN NOT NULL,
    cod_nodo_origen TEXT,
    cod_relacion_semantica__presente BOOLEAN NOT NULL,
    cod_relacion_semantica TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_version_modelo__presente BOOLEAN NOT NULL,
    cod_version_modelo TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    modalidad_relacion__presente BOOLEAN NOT NULL,
    modalidad_relacion TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    significado__presente BOOLEAN NOT NULL,
    significado TEXT,
    tipo_relacion__presente BOOLEAN NOT NULL,
    tipo_relacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_nodo_destino__presente=TRUE OR cod_nodo_destino IS NULL),
    CHECK(cod_nodo_origen__presente=TRUE OR cod_nodo_origen IS NULL),
    CHECK(cod_relacion_semantica__presente=TRUE OR cod_relacion_semantica IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_version_modelo__presente=TRUE OR cod_version_modelo IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(modalidad_relacion__presente=TRUE OR modalidad_relacion IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(significado__presente=TRUE OR significado IS NULL),
    CHECK(tipo_relacion__presente=TRUE OR tipo_relacion IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_nodo_destino__presente=FALSE AND cod_nodo_origen__presente=FALSE AND cod_relacion_semantica__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_version_modelo__presente=FALSE AND estado__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND modalidad_relacion__presente=FALSE AND observacion__presente=FALSE AND significado__presente=FALSE AND tipo_relacion__presente=FALSE))
);

CREATE TABLE conocimiento_valores_semanticos (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente BOOLEAN NOT NULL,
    cod_dominio_valores TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_valor_semantico__presente BOOLEAN NOT NULL,
    cod_valor_semantico TEXT,
    codigo_valor__presente BOOLEAN NOT NULL,
    codigo_valor TEXT,
    definicion_semantica__presente BOOLEAN NOT NULL,
    definicion_semantica TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    estado_aprobacion__presente BOOLEAN NOT NULL,
    estado_aprobacion TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    nombre__presente BOOLEAN NOT NULL,
    nombre TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente=TRUE OR cod_dominio_valores IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_valor_semantico__presente=TRUE OR cod_valor_semantico IS NULL),
    CHECK(codigo_valor__presente=TRUE OR codigo_valor IS NULL),
    CHECK(definicion_semantica__presente=TRUE OR definicion_semantica IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(estado_aprobacion__presente=TRUE OR estado_aprobacion IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(nombre__presente=TRUE OR nombre IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_dominio_valores__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_valor_semantico__presente=FALSE AND codigo_valor__presente=FALSE AND definicion_semantica__presente=FALSE AND estado__presente=FALSE AND estado_aprobacion__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND nombre__presente=FALSE AND observacion__presente=FALSE))
);

CREATE TABLE conocimiento_variables_expertas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_dominio_valores__presente BOOLEAN NOT NULL,
    cod_dominio_valores TEXT,
    cod_nodo_propietario_primario__presente BOOLEAN NOT NULL,
    cod_nodo_propietario_primario TEXT,
    cod_nodo_semantico__presente BOOLEAN NOT NULL,
    cod_nodo_semantico TEXT,
    cod_usuario_creacion__presente BOOLEAN NOT NULL,
    cod_usuario_creacion TEXT,
    cod_variable_experta__presente BOOLEAN NOT NULL,
    cod_variable_experta TEXT,
    cod_version_modelo__presente BOOLEAN NOT NULL,
    cod_version_modelo TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    fecha_hora_creacion__presente BOOLEAN NOT NULL,
    fecha_hora_creacion TEXT,
    observacion__presente BOOLEAN NOT NULL,
    observacion TEXT,
    papel_inferencial__presente BOOLEAN NOT NULL,
    papel_inferencial TEXT,
    tipo_semantico__presente BOOLEAN NOT NULL,
    tipo_semantico TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('I','P'))),
    CHECK((rol_coleccion='I' AND clave_tipo ='NULL') OR (rol_coleccion='P' AND clave_tipo ='NULL')),
    CHECK(cod_dominio_valores__presente=TRUE OR cod_dominio_valores IS NULL),
    CHECK(cod_nodo_propietario_primario__presente=TRUE OR cod_nodo_propietario_primario IS NULL),
    CHECK(cod_nodo_semantico__presente=TRUE OR cod_nodo_semantico IS NULL),
    CHECK(cod_usuario_creacion__presente=TRUE OR cod_usuario_creacion IS NULL),
    CHECK(cod_variable_experta__presente=TRUE OR cod_variable_experta IS NULL),
    CHECK(cod_version_modelo__presente=TRUE OR cod_version_modelo IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(fecha_hora_creacion__presente=TRUE OR fecha_hora_creacion IS NULL),
    CHECK(observacion__presente=TRUE OR observacion IS NULL),
    CHECK(papel_inferencial__presente=TRUE OR papel_inferencial IS NULL),
    CHECK(tipo_semantico__presente=TRUE OR tipo_semantico IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_dominio_valores__presente=FALSE AND cod_nodo_propietario_primario__presente=FALSE AND cod_nodo_semantico__presente=FALSE AND cod_usuario_creacion__presente=FALSE AND cod_variable_experta__presente=FALSE AND cod_version_modelo__presente=FALSE AND estado__presente=FALSE AND fecha_hora_creacion__presente=FALSE AND observacion__presente=FALSE AND papel_inferencial__presente=FALSE AND tipo_semantico__presente=FALSE))
);

CREATE TABLE vinculos_instrumentales_adquiridos (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente BOOLEAN NOT NULL,
    campo TEXT,
    cod_instrumento__presente BOOLEAN NOT NULL,
    cod_instrumento TEXT,
    cod_pregunta__presente BOOLEAN NOT NULL,
    cod_pregunta TEXT,
    codigo_instrumento__presente BOOLEAN NOT NULL,
    codigo_instrumento TEXT,
    componente__presente BOOLEAN NOT NULL,
    componente TEXT,
    perfil_interpretativo__estado VARCHAR(8) NOT NULL CHECK(perfil_interpretativo__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_interpretativo_codigo__presente BOOLEAN NOT NULL,
    perfil_interpretativo_codigo TEXT,
    perfil_interpretativo_componente__presente BOOLEAN NOT NULL,
    perfil_interpretativo_componente TEXT,
    perfil_interpretativo_fuente__presente BOOLEAN NOT NULL,
    perfil_interpretativo_fuente TEXT,
    perfil_interpretativo_instrumento__estado VARCHAR(8) NOT NULL CHECK(perfil_interpretativo_instrumento__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_interpretativo_instrumento_codigo__presente BOOLEAN NOT NULL,
    perfil_interpretativo_instrumento_codigo TEXT,
    perfil_interpretativo_instrumento_version__presente BOOLEAN NOT NULL,
    perfil_interpretativo_instrumento_version TEXT,
    perfil_interpretativo_mediciones_requeridas__estado VARCHAR(8) NOT NULL CHECK(perfil_interpretativo_mediciones_requeridas__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_interpretativo_mediciones_requeridas__cantidad BIGINT NOT NULL CHECK(perfil_interpretativo_mediciones_requeridas__cantidad>=0),
    perfil_interpretativo_metodo__presente BOOLEAN NOT NULL,
    perfil_interpretativo_metodo TEXT,
    perfil_interpretativo_predicados__estado VARCHAR(8) NOT NULL CHECK(perfil_interpretativo_predicados__estado IN ('AUSENTE','NULL','PRESENTE')),
    perfil_interpretativo_predicados__cantidad BIGINT NOT NULL CHECK(perfil_interpretativo_predicados__cantidad>=0),
    perfil_interpretativo_version__presente BOOLEAN NOT NULL,
    perfil_interpretativo_version TEXT,
    version_instrumento__presente BOOLEAN NOT NULL,
    version_instrumento TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C7874dd01a9'))),
    CHECK((rol_coleccion='C7874dd01a9' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente=TRUE OR campo IS NULL),
    CHECK(cod_instrumento__presente=TRUE OR cod_instrumento IS NULL),
    CHECK(cod_pregunta__presente=TRUE OR cod_pregunta IS NULL),
    CHECK(codigo_instrumento__presente=TRUE OR codigo_instrumento IS NULL),
    CHECK(componente__presente=TRUE OR componente IS NULL),
    CHECK(perfil_interpretativo_codigo__presente=TRUE OR perfil_interpretativo_codigo IS NULL),
    CHECK(perfil_interpretativo_componente__presente=TRUE OR perfil_interpretativo_componente IS NULL),
    CHECK(perfil_interpretativo_fuente__presente=TRUE OR perfil_interpretativo_fuente IS NULL),
    CHECK(perfil_interpretativo_instrumento_codigo__presente=TRUE OR perfil_interpretativo_instrumento_codigo IS NULL),
    CHECK(perfil_interpretativo_instrumento_version__presente=TRUE OR perfil_interpretativo_instrumento_version IS NULL),
    CHECK(perfil_interpretativo_mediciones_requeridas__estado='PRESENTE' OR perfil_interpretativo_mediciones_requeridas__cantidad=0),
    CHECK(perfil_interpretativo_metodo__presente=TRUE OR perfil_interpretativo_metodo IS NULL),
    CHECK(perfil_interpretativo_predicados__estado='PRESENTE' OR perfil_interpretativo_predicados__cantidad=0),
    CHECK(perfil_interpretativo_version__presente=TRUE OR perfil_interpretativo_version IS NULL),
    CHECK(version_instrumento__presente=TRUE OR version_instrumento IS NULL),
    CHECK(registro__estado='PRESENTE' OR (campo__presente=FALSE AND cod_instrumento__presente=FALSE AND cod_pregunta__presente=FALSE AND codigo_instrumento__presente=FALSE AND componente__presente=FALSE AND perfil_interpretativo__estado='AUSENTE' AND version_instrumento__presente=FALSE)),
    CHECK(perfil_interpretativo__estado='PRESENTE' OR (perfil_interpretativo_codigo__presente=FALSE AND perfil_interpretativo_componente__presente=FALSE AND perfil_interpretativo_fuente__presente=FALSE AND perfil_interpretativo_instrumento__estado='AUSENTE' AND perfil_interpretativo_mediciones_requeridas__estado='AUSENTE' AND perfil_interpretativo_metodo__presente=FALSE AND perfil_interpretativo_predicados__estado='AUSENTE' AND perfil_interpretativo_version__presente=FALSE)),
    CHECK(perfil_interpretativo_instrumento__estado='PRESENTE' OR (perfil_interpretativo_instrumento_codigo__presente=FALSE AND perfil_interpretativo_instrumento_version__presente=FALSE))
);

CREATE TABLE mediciones_requeridas_perfil (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    campo__presente BOOLEAN NOT NULL,
    campo TEXT,
    cod_pregunta__presente BOOLEAN NOT NULL,
    cod_pregunta TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C66a9c79cf7'))),
    CHECK((rol_coleccion='C66a9c79cf7' AND clave_tipo IN ('INT','STRING'))),
    CHECK(campo__presente=TRUE OR campo IS NULL),
    CHECK(cod_pregunta__presente=TRUE OR cod_pregunta IS NULL),
    CHECK(registro__estado='PRESENTE' OR (campo__presente=FALSE AND cod_pregunta__presente=FALSE))
);

CREATE TABLE predicados_perfil_adquirido (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    predicado__presente BOOLEAN NOT NULL,
    predicado TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C94c27667c0'))),
    CHECK((rol_coleccion='C94c27667c0' AND clave_tipo ='NULL')),
    CHECK(predicado__presente=TRUE OR predicado IS NULL)
);

CREATE TABLE exclusiones_adquisicion (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    elemento__presente BOOLEAN NOT NULL,
    elemento TEXT,
    motivo__presente BOOLEAN NOT NULL,
    motivo TEXT,
    valoracion__presente BOOLEAN NOT NULL,
    valoracion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('Cefd2a57b6f'))),
    CHECK((rol_coleccion='Cefd2a57b6f' AND clave_tipo ='NULL')),
    CHECK(elemento__presente=TRUE OR elemento IS NULL),
    CHECK(motivo__presente=TRUE OR motivo IS NULL),
    CHECK(valoracion__presente=TRUE OR valoracion IS NULL),
    CHECK(registro__estado='PRESENTE' OR (elemento__presente=FALSE AND motivo__presente=FALSE AND valoracion__presente=FALSE))
);

CREATE TABLE constancias_documentales_adquiridas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_documento_clinico__presente BOOLEAN NOT NULL,
    cod_documento_clinico TEXT,
    cod_personal__presente BOOLEAN NOT NULL,
    cod_personal TEXT,
    cod_residente__presente BOOLEAN NOT NULL,
    cod_residente TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    fecha_hora__presente BOOLEAN NOT NULL,
    fecha_hora TEXT,
    hash_archivo__presente BOOLEAN NOT NULL,
    hash_archivo TEXT,
    localizador_fuente__presente BOOLEAN NOT NULL,
    localizador_fuente TEXT,
    papel__presente BOOLEAN NOT NULL,
    papel TEXT,
    valoracion__presente BOOLEAN NOT NULL,
    valoracion TEXT,
    verificado__presente BOOLEAN NOT NULL,
    verificado BOOLEAN,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C7eea8071b0'))),
    CHECK((rol_coleccion='C7eea8071b0' AND clave_tipo ='NULL')),
    CHECK(cod_documento_clinico__presente=TRUE OR cod_documento_clinico IS NULL),
    CHECK(cod_personal__presente=TRUE OR cod_personal IS NULL),
    CHECK(cod_residente__presente=TRUE OR cod_residente IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(fecha_hora__presente=TRUE OR fecha_hora IS NULL),
    CHECK(hash_archivo__presente=TRUE OR hash_archivo IS NULL),
    CHECK(localizador_fuente__presente=TRUE OR localizador_fuente IS NULL),
    CHECK(papel__presente=TRUE OR papel IS NULL),
    CHECK(valoracion__presente=TRUE OR valoracion IS NULL),
    CHECK(verificado__presente=TRUE OR verificado IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_documento_clinico__presente=FALSE AND cod_personal__presente=FALSE AND cod_residente__presente=FALSE AND estado__presente=FALSE AND fecha_hora__presente=FALSE AND hash_archivo__presente=FALSE AND localizador_fuente__presente=FALSE AND papel__presente=FALSE AND valoracion__presente=FALSE AND verificado__presente=FALSE))
);

CREATE TABLE membresias_juicio_adquiridas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__estado VARCHAR(8) NOT NULL CHECK(soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__cantidad BIGINT NOT NULL CHECK(soportes__cantidad>=0),
    valoracion__presente BOOLEAN NOT NULL,
    valoracion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('Ce2d972a31d'))),
    CHECK((rol_coleccion='Ce2d972a31d' AND clave_tipo ='NULL')),
    CHECK(soportes__estado='PRESENTE' OR soportes__cantidad=0),
    CHECK(valoracion__presente=TRUE OR valoracion IS NULL),
    CHECK(registro__estado='PRESENTE' OR (soportes__estado='AUSENTE' AND valoracion__presente=FALSE))
);

CREATE TABLE miembros_soporte_adquiridos (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    cod_soporte_valoracion__presente BOOLEAN NOT NULL,
    cod_soporte_valoracion TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C4607c4e418'))),
    CHECK((rol_coleccion='C4607c4e418' AND clave_tipo ='NULL')),
    CHECK(cod_soporte_valoracion__presente=TRUE OR cod_soporte_valoracion IS NULL)
);

CREATE TABLE procedencias_juicio_adquiridas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    cod_documento_clinico__presente BOOLEAN NOT NULL,
    cod_documento_clinico TEXT,
    cod_personal__presente BOOLEAN NOT NULL,
    cod_personal TEXT,
    cod_residente__presente BOOLEAN NOT NULL,
    cod_residente TEXT,
    cod_usuario_registro__presente BOOLEAN NOT NULL,
    cod_usuario_registro TEXT,
    cod_valoracion_anterior__presente BOOLEAN NOT NULL,
    cod_valoracion_anterior TEXT,
    cod_valoracion_petersen__presente BOOLEAN NOT NULL,
    cod_valoracion_petersen TEXT,
    elemento__presente BOOLEAN NOT NULL,
    elemento TEXT,
    fecha_hora__presente BOOLEAN NOT NULL,
    fecha_hora TEXT,
    localizador_fuente__presente BOOLEAN NOT NULL,
    localizador_fuente TEXT,
    registrado_en__presente BOOLEAN NOT NULL,
    registrado_en TEXT,
    tipo_registro__presente BOOLEAN NOT NULL,
    tipo_registro TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('Cd31d2cbcf0'))),
    CHECK((rol_coleccion='Cd31d2cbcf0' AND clave_tipo ='NULL')),
    CHECK(cod_documento_clinico__presente=TRUE OR cod_documento_clinico IS NULL),
    CHECK(cod_personal__presente=TRUE OR cod_personal IS NULL),
    CHECK(cod_residente__presente=TRUE OR cod_residente IS NULL),
    CHECK(cod_usuario_registro__presente=TRUE OR cod_usuario_registro IS NULL),
    CHECK(cod_valoracion_anterior__presente=TRUE OR cod_valoracion_anterior IS NULL),
    CHECK(cod_valoracion_petersen__presente=TRUE OR cod_valoracion_petersen IS NULL),
    CHECK(elemento__presente=TRUE OR elemento IS NULL),
    CHECK(fecha_hora__presente=TRUE OR fecha_hora IS NULL),
    CHECK(localizador_fuente__presente=TRUE OR localizador_fuente IS NULL),
    CHECK(registrado_en__presente=TRUE OR registrado_en IS NULL),
    CHECK(tipo_registro__presente=TRUE OR tipo_registro IS NULL),
    CHECK(registro__estado='PRESENTE' OR (cod_documento_clinico__presente=FALSE AND cod_personal__presente=FALSE AND cod_residente__presente=FALSE AND cod_usuario_registro__presente=FALSE AND cod_valoracion_anterior__presente=FALSE AND cod_valoracion_petersen__presente=FALSE AND elemento__presente=FALSE AND fecha_hora__presente=FALSE AND localizador_fuente__presente=FALSE AND registrado_en__presente=FALSE AND tipo_registro__presente=FALSE))
);

CREATE TABLE juicios_petersen_adquiridos (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    comprobaciones__estado VARCHAR(8) NOT NULL CHECK(comprobaciones__estado IN ('AUSENTE','NULL','PRESENTE')),
    comprobaciones_alcance_dominio__presente BOOLEAN NOT NULL,
    comprobaciones_alcance_dominio BOOLEAN,
    comprobaciones_atribucion_revisada__presente BOOLEAN NOT NULL,
    comprobaciones_atribucion_revisada BOOLEAN,
    comprobaciones_cambio_respecto_previo__presente BOOLEAN NOT NULL,
    comprobaciones_cambio_respecto_previo BOOLEAN,
    comprobaciones_condiciones_revisadas__presente BOOLEAN NOT NULL,
    comprobaciones_condiciones_revisadas BOOLEAN,
    comprobaciones_contexto_interpretado__presente BOOLEAN NOT NULL,
    comprobaciones_contexto_interpretado BOOLEAN,
    comprobaciones_corroboracion_independiente__presente BOOLEAN NOT NULL,
    comprobaciones_corroboracion_independiente BOOLEAN,
    comprobaciones_exclusiones_revisadas__presente BOOLEAN NOT NULL,
    comprobaciones_exclusiones_revisadas BOOLEAN,
    comprobaciones_factores_pertinentes_revisados__presente BOOLEAN NOT NULL,
    comprobaciones_factores_pertinentes_revisados BOOLEAN,
    comprobaciones_impedimento_no_resuelto__presente BOOLEAN NOT NULL,
    comprobaciones_impedimento_no_resuelto BOOLEAN,
    comprobaciones_oportunidad_adecuada__presente BOOLEAN NOT NULL,
    comprobaciones_oportunidad_adecuada BOOLEAN,
    comprobaciones_revision_explicita__presente BOOLEAN NOT NULL,
    comprobaciones_revision_explicita BOOLEAN,
    comprobaciones_revision_integrada__presente BOOLEAN NOT NULL,
    comprobaciones_revision_integrada BOOLEAN,
    comprobaciones_suficiencia_conjunto__presente BOOLEAN NOT NULL,
    comprobaciones_suficiencia_conjunto BOOLEAN,
    dominios_revisados__estado VARCHAR(8) NOT NULL CHECK(dominios_revisados__estado IN ('AUSENTE','NULL','PRESENTE')),
    dominios_revisados__cantidad BIGINT NOT NULL CHECK(dominios_revisados__cantidad>=0),
    elemento__presente BOOLEAN NOT NULL,
    elemento TEXT,
    estado__presente BOOLEAN NOT NULL,
    estado TEXT,
    justificacion__presente BOOLEAN NOT NULL,
    justificacion TEXT,
    origen__estado VARCHAR(8) NOT NULL CHECK(origen__estado IN ('AUSENTE','NULL','PRESENTE')),
    origen_autor__presente BOOLEAN NOT NULL,
    origen_autor TEXT,
    origen_datos_originales__estado VARCHAR(8) NOT NULL CHECK(origen_datos_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    origen_episodio__presente BOOLEAN NOT NULL,
    origen_episodio TEXT,
    origen_fecha__presente BOOLEAN NOT NULL,
    origen_fecha TEXT,
    origen_registro__presente BOOLEAN NOT NULL,
    origen_registro TEXT,
    origen_residente__presente BOOLEAN NOT NULL,
    origen_residente TEXT,
    origen_tabla__presente BOOLEAN NOT NULL,
    origen_tabla TEXT,
    origen_tipo__presente BOOLEAN NOT NULL,
    origen_tipo TEXT,
    origen_vigente__presente BOOLEAN NOT NULL,
    origen_vigente BOOLEAN,
    soportes__estado VARCHAR(8) NOT NULL CHECK(soportes__estado IN ('AUSENTE','NULL','PRESENTE')),
    soportes__cantidad BIGINT NOT NULL CHECK(soportes__cantidad>=0),
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('Cc09aa72640'))),
    CHECK((rol_coleccion='Cc09aa72640' AND clave_tipo ='NULL')),
    CHECK(comprobaciones_alcance_dominio__presente=TRUE OR comprobaciones_alcance_dominio IS NULL),
    CHECK(comprobaciones_atribucion_revisada__presente=TRUE OR comprobaciones_atribucion_revisada IS NULL),
    CHECK(comprobaciones_cambio_respecto_previo__presente=TRUE OR comprobaciones_cambio_respecto_previo IS NULL),
    CHECK(comprobaciones_condiciones_revisadas__presente=TRUE OR comprobaciones_condiciones_revisadas IS NULL),
    CHECK(comprobaciones_contexto_interpretado__presente=TRUE OR comprobaciones_contexto_interpretado IS NULL),
    CHECK(comprobaciones_corroboracion_independiente__presente=TRUE OR comprobaciones_corroboracion_independiente IS NULL),
    CHECK(comprobaciones_exclusiones_revisadas__presente=TRUE OR comprobaciones_exclusiones_revisadas IS NULL),
    CHECK(comprobaciones_factores_pertinentes_revisados__presente=TRUE OR comprobaciones_factores_pertinentes_revisados IS NULL),
    CHECK(comprobaciones_impedimento_no_resuelto__presente=TRUE OR comprobaciones_impedimento_no_resuelto IS NULL),
    CHECK(comprobaciones_oportunidad_adecuada__presente=TRUE OR comprobaciones_oportunidad_adecuada IS NULL),
    CHECK(comprobaciones_revision_explicita__presente=TRUE OR comprobaciones_revision_explicita IS NULL),
    CHECK(comprobaciones_revision_integrada__presente=TRUE OR comprobaciones_revision_integrada IS NULL),
    CHECK(comprobaciones_suficiencia_conjunto__presente=TRUE OR comprobaciones_suficiencia_conjunto IS NULL),
    CHECK(dominios_revisados__estado='PRESENTE' OR dominios_revisados__cantidad=0),
    CHECK(elemento__presente=TRUE OR elemento IS NULL),
    CHECK(estado__presente=TRUE OR estado IS NULL),
    CHECK(justificacion__presente=TRUE OR justificacion IS NULL),
    CHECK(origen_autor__presente=TRUE OR origen_autor IS NULL),
    CHECK(origen_episodio__presente=TRUE OR origen_episodio IS NULL),
    CHECK(origen_fecha__presente=TRUE OR origen_fecha IS NULL),
    CHECK(origen_registro__presente=TRUE OR origen_registro IS NULL),
    CHECK(origen_residente__presente=TRUE OR origen_residente IS NULL),
    CHECK(origen_tabla__presente=TRUE OR origen_tabla IS NULL),
    CHECK(origen_tipo__presente=TRUE OR origen_tipo IS NULL),
    CHECK(origen_vigente__presente=TRUE OR origen_vigente IS NULL),
    CHECK(soportes__estado='PRESENTE' OR soportes__cantidad=0),
    CHECK(registro__estado='PRESENTE' OR (comprobaciones__estado='AUSENTE' AND dominios_revisados__estado='AUSENTE' AND elemento__presente=FALSE AND estado__presente=FALSE AND justificacion__presente=FALSE AND origen__estado='AUSENTE' AND soportes__estado='AUSENTE')),
    CHECK(comprobaciones__estado='PRESENTE' OR (comprobaciones_alcance_dominio__presente=FALSE AND comprobaciones_atribucion_revisada__presente=FALSE AND comprobaciones_cambio_respecto_previo__presente=FALSE AND comprobaciones_condiciones_revisadas__presente=FALSE AND comprobaciones_contexto_interpretado__presente=FALSE AND comprobaciones_corroboracion_independiente__presente=FALSE AND comprobaciones_exclusiones_revisadas__presente=FALSE AND comprobaciones_factores_pertinentes_revisados__presente=FALSE AND comprobaciones_impedimento_no_resuelto__presente=FALSE AND comprobaciones_oportunidad_adecuada__presente=FALSE AND comprobaciones_revision_explicita__presente=FALSE AND comprobaciones_revision_integrada__presente=FALSE AND comprobaciones_suficiencia_conjunto__presente=FALSE)),
    CHECK(origen__estado='PRESENTE' OR (origen_autor__presente=FALSE AND origen_datos_originales__estado='AUSENTE' AND origen_episodio__presente=FALSE AND origen_fecha__presente=FALSE AND origen_registro__presente=FALSE AND origen_residente__presente=FALSE AND origen_tabla__presente=FALSE AND origen_tipo__presente=FALSE AND origen_vigente__presente=FALSE))
);

CREATE TABLE dominios_revisados_adquiridos (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    dominio_revisado__presente BOOLEAN NOT NULL,
    dominio_revisado TEXT,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('Caf7f6dc239'))),
    CHECK((rol_coleccion='Caf7f6dc239' AND clave_tipo IN ('INT','STRING'))),
    CHECK(dominio_revisado__presente=TRUE OR dominio_revisado IS NULL)
);

CREATE TABLE fuentes_soporte_adquiridas (
    cod_fila VARCHAR(20) PRIMARY KEY,
    cod_evaluacion_experta VARCHAR(20) NOT NULL,
    posicion_coleccion BIGINT NOT NULL CHECK(posicion_coleccion>=0),
    rol_coleccion VARCHAR(32) NOT NULL,
    clave_tipo VARCHAR(6) NOT NULL CHECK(clave_tipo IN ('NULL','INT','STRING')),
    clave_texto TEXT,
    cod_propietario VARCHAR(20),
    registro__estado VARCHAR(8) NOT NULL CHECK(registro__estado IN ('AUSENTE','NULL','PRESENTE')),
    autor__presente BOOLEAN NOT NULL,
    autor TEXT,
    datos_originales__estado VARCHAR(8) NOT NULL CHECK(datos_originales__estado IN ('AUSENTE','NULL','PRESENTE')),
    datos_originales_actividad__presente BOOLEAN NOT NULL,
    datos_originales_actividad TEXT,
    datos_originales_captura_original__estado VARCHAR(8) NOT NULL CHECK(datos_originales_captura_original__estado IN ('AUSENTE','NULL','PRESENTE')),
    datos_originales_causa_ayuda__presente BOOLEAN NOT NULL,
    datos_originales_causa_ayuda TEXT,
    datos_originales_descripcion__presente BOOLEAN NOT NULL,
    datos_originales_descripcion TEXT,
    datos_originales_desempeno__presente BOOLEAN NOT NULL,
    datos_originales_desempeno TEXT,
    datos_originales_dominio__presente BOOLEAN NOT NULL,
    datos_originales_dominio TEXT,
    datos_originales_fuente__presente BOOLEAN NOT NULL,
    datos_originales_fuente TEXT,
    datos_originales_nivel_previo__presente BOOLEAN NOT NULL,
    datos_originales_nivel_previo TEXT,
    datos_originales_oportunidad__presente BOOLEAN NOT NULL,
    datos_originales_oportunidad TEXT,
    datos_originales_tipo_ayuda__presente BOOLEAN NOT NULL,
    datos_originales_tipo_ayuda TEXT,
    episodio__presente BOOLEAN NOT NULL,
    episodio TEXT,
    fecha__presente BOOLEAN NOT NULL,
    fecha TEXT,
    registro__presente BOOLEAN NOT NULL,
    registro TEXT,
    residente__presente BOOLEAN NOT NULL,
    residente TEXT,
    tabla__presente BOOLEAN NOT NULL,
    tabla TEXT,
    tipo__presente BOOLEAN NOT NULL,
    tipo TEXT,
    vigente__presente BOOLEAN NOT NULL,
    vigente BOOLEAN,
    UNIQUE(cod_evaluacion_experta,cod_fila),
    FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_nominales(cod_evaluacion_experta) ON DELETE RESTRICT,
    CHECK((clave_tipo='NULL' AND clave_texto IS NULL) OR (clave_tipo<>'NULL' AND clave_texto IS NOT NULL)),
    UNIQUE(cod_propietario,rol_coleccion,posicion_coleccion),
    CHECK((CASE WHEN cod_propietario IS NULL THEN 0 ELSE 1 END)=1),
    CHECK((cod_propietario IS NOT NULL AND rol_coleccion IN ('C027418f6cc'))),
    CHECK((rol_coleccion='C027418f6cc' AND clave_tipo ='NULL')),
    CHECK(autor__presente=TRUE OR autor IS NULL),
    CHECK(datos_originales_actividad__presente=TRUE OR datos_originales_actividad IS NULL),
    CHECK(datos_originales_causa_ayuda__presente=TRUE OR datos_originales_causa_ayuda IS NULL),
    CHECK(datos_originales_descripcion__presente=TRUE OR datos_originales_descripcion IS NULL),
    CHECK(datos_originales_desempeno__presente=TRUE OR datos_originales_desempeno IS NULL),
    CHECK(datos_originales_dominio__presente=TRUE OR datos_originales_dominio IS NULL),
    CHECK(datos_originales_fuente__presente=TRUE OR datos_originales_fuente IS NULL),
    CHECK(datos_originales_nivel_previo__presente=TRUE OR datos_originales_nivel_previo IS NULL),
    CHECK(datos_originales_oportunidad__presente=TRUE OR datos_originales_oportunidad IS NULL),
    CHECK(datos_originales_tipo_ayuda__presente=TRUE OR datos_originales_tipo_ayuda IS NULL),
    CHECK(episodio__presente=TRUE OR episodio IS NULL),
    CHECK(fecha__presente=TRUE OR fecha IS NULL),
    CHECK(registro__presente=TRUE OR registro IS NULL),
    CHECK(residente__presente=TRUE OR residente IS NULL),
    CHECK(tabla__presente=TRUE OR tabla IS NULL),
    CHECK(tipo__presente=TRUE OR tipo IS NULL),
    CHECK(vigente__presente=TRUE OR vigente IS NULL),
    CHECK(registro__estado='PRESENTE' OR (autor__presente=FALSE AND datos_originales__estado='AUSENTE' AND episodio__presente=FALSE AND fecha__presente=FALSE AND registro__presente=FALSE AND residente__presente=FALSE AND tabla__presente=FALSE AND tipo__presente=FALSE AND vigente__presente=FALSE)),
    CHECK(datos_originales__estado='PRESENTE' OR (datos_originales_actividad__presente=FALSE AND datos_originales_captura_original__estado='AUSENTE' AND datos_originales_causa_ayuda__presente=FALSE AND datos_originales_descripcion__presente=FALSE AND datos_originales_desempeno__presente=FALSE AND datos_originales_dominio__presente=FALSE AND datos_originales_fuente__presente=FALSE AND datos_originales_nivel_previo__presente=FALSE AND datos_originales_oportunidad__presente=FALSE AND datos_originales_tipo_ayuda__presente=FALSE))
);

ALTER TABLE capturas_adquiridas ADD FOREIGN KEY(cod_evaluacion_experta,cod_entrada_institucional) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE capturas_adquiridas ADD FOREIGN KEY(cod_evaluacion_experta,cod_fuente_soporte) REFERENCES fuentes_soporte_adquiridas(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE motivos_captura ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES capturas_adquiridas(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE selectores_captura ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES capturas_adquiridas(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE preguntas_captura ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES capturas_adquiridas(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE respuestas_captura ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES capturas_adquiridas(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE vinculos_pregunta_captura ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES capturas_adquiridas(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE contratos_relacion_adquiridos ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE modalidades_contrato_adquirido ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES contratos_relacion_adquiridos(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_condiciones_regla_experta ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_consecuencias_regla_experta ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_criterios_dominios_resultado ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_dominios_valores_expertos ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_fuentes_datos_expertas ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_mapeos_valores_fuente ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_mapeos_variables_fuente ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_nodos_semanticos ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_reglas_expertas ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_relaciones_semanticas ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_valores_semanticos ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE conocimiento_variables_expertas ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE vinculos_instrumentales_adquiridos ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE mediciones_requeridas_perfil ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES vinculos_instrumentales_adquiridos(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE predicados_perfil_adquirido ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES vinculos_instrumentales_adquiridos(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE exclusiones_adquisicion ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE constancias_documentales_adquiridas ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE membresias_juicio_adquiridas ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE miembros_soporte_adquiridos ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES membresias_juicio_adquiridas(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE procedencias_juicio_adquiridas ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE juicios_petersen_adquiridos ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES entradas_institucionales(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE dominios_revisados_adquiridos ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES juicios_petersen_adquiridos(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

ALTER TABLE fuentes_soporte_adquiridas ADD FOREIGN KEY(cod_evaluacion_experta,cod_propietario) REFERENCES juicios_petersen_adquiridos(cod_evaluacion_experta,cod_fila) ON DELETE RESTRICT;

CREATE INDEX evaluaciones_nominales_cod_residente_i ON evaluaciones_nominales(cod_residente);

CREATE INDEX evaluaciones_nominales_cod_usuario_i ON evaluaciones_nominales(cod_usuario);

CREATE INDEX evaluaciones_nominales_huella_release_i ON evaluaciones_nominales(huella_release);

CREATE INDEX entradas_institucionales_evaluacion_i ON entradas_institucionales(cod_evaluacion_experta);

CREATE INDEX capturas_adquiridas_evaluacion_i ON capturas_adquiridas(cod_evaluacion_experta);

CREATE UNIQUE INDEX capturas_adquiridas_0e043bedad_k ON capturas_adquiridas(cod_entrada_institucional,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE UNIQUE INDEX capturas_adquiridas_d4dfc8902b_k ON capturas_adquiridas(cod_fuente_soporte,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX motivos_captura_evaluacion_i ON motivos_captura(cod_evaluacion_experta);

CREATE UNIQUE INDEX motivos_captura_8d93164f8b_k ON motivos_captura(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX selectores_captura_evaluacion_i ON selectores_captura(cod_evaluacion_experta);

CREATE UNIQUE INDEX selectores_captura_50f822135d_k ON selectores_captura(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX preguntas_captura_evaluacion_i ON preguntas_captura(cod_evaluacion_experta);

CREATE UNIQUE INDEX preguntas_captura_09f572e4f1_k ON preguntas_captura(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX respuestas_captura_evaluacion_i ON respuestas_captura(cod_evaluacion_experta);

CREATE UNIQUE INDEX respuestas_captura_48c2f3fec4_k ON respuestas_captura(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX vinculos_pregunta_captura_evaluacion_i ON vinculos_pregunta_captura(cod_evaluacion_experta);

CREATE UNIQUE INDEX vinculos_pregunta_captura_0411d4817b_k ON vinculos_pregunta_captura(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX contratos_relacion_adquiridos_evaluacion_i ON contratos_relacion_adquiridos(cod_evaluacion_experta);

CREATE UNIQUE INDEX contratos_relacion_adquiridos_1ab9b55a43_k ON contratos_relacion_adquiridos(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX modalidades_contrato_adquirido_evaluacion_i ON modalidades_contrato_adquirido(cod_evaluacion_experta);

CREATE UNIQUE INDEX modalidades_contrato_adquirid_4035da1126_k ON modalidades_contrato_adquirido(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_condiciones_regla_experta_evaluacion_i ON conocimiento_condiciones_regla_experta(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_condiciones_regl_5d5a008f22_k ON conocimiento_condiciones_regla_experta(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_consecuencias_regla_experta_evaluacion_i ON conocimiento_consecuencias_regla_experta(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_consecuencias_re_4f264f098b_k ON conocimiento_consecuencias_regla_experta(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_criterios_dominios_resultado_evaluacion_i ON conocimiento_criterios_dominios_resultado(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_criterios_domini_e1c19cb7e6_k ON conocimiento_criterios_dominios_resultado(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_dominios_valores_expertos_evaluacion_i ON conocimiento_dominios_valores_expertos(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_dominios_valores_d31d3e030d_k ON conocimiento_dominios_valores_expertos(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_fuentes_datos_expertas_evaluacion_i ON conocimiento_fuentes_datos_expertas(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_fuentes_datos_ex_549f042695_k ON conocimiento_fuentes_datos_expertas(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_mapeos_valores_fuente_evaluacion_i ON conocimiento_mapeos_valores_fuente(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_mapeos_valores_f_461e463dd9_k ON conocimiento_mapeos_valores_fuente(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_mapeos_variables_fuente_evaluacion_i ON conocimiento_mapeos_variables_fuente(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_mapeos_variables_5be271e995_k ON conocimiento_mapeos_variables_fuente(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_nodos_semanticos_evaluacion_i ON conocimiento_nodos_semanticos(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_nodos_semanticos_cbf05d6d35_k ON conocimiento_nodos_semanticos(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_reglas_expertas_evaluacion_i ON conocimiento_reglas_expertas(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_reglas_expertas_67cebe75c5_k ON conocimiento_reglas_expertas(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_relaciones_semanticas_evaluacion_i ON conocimiento_relaciones_semanticas(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_relaciones_seman_89d63ec080_k ON conocimiento_relaciones_semanticas(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_valores_semanticos_evaluacion_i ON conocimiento_valores_semanticos(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_valores_semantic_c6535e1a4b_k ON conocimiento_valores_semanticos(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX conocimiento_variables_expertas_evaluacion_i ON conocimiento_variables_expertas(cod_evaluacion_experta);

CREATE UNIQUE INDEX conocimiento_variables_expert_55aa8b0f01_k ON conocimiento_variables_expertas(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX vinculos_instrumentales_adquiridos_evaluacion_i ON vinculos_instrumentales_adquiridos(cod_evaluacion_experta);

CREATE UNIQUE INDEX vinculos_instrumentales_adqui_7ccfedc102_k ON vinculos_instrumentales_adquiridos(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX mediciones_requeridas_perfil_evaluacion_i ON mediciones_requeridas_perfil(cod_evaluacion_experta);

CREATE UNIQUE INDEX mediciones_requeridas_perfil_4f4625df10_k ON mediciones_requeridas_perfil(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX predicados_perfil_adquirido_evaluacion_i ON predicados_perfil_adquirido(cod_evaluacion_experta);

CREATE UNIQUE INDEX predicados_perfil_adquirido_438ee6f4f8_k ON predicados_perfil_adquirido(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX exclusiones_adquisicion_evaluacion_i ON exclusiones_adquisicion(cod_evaluacion_experta);

CREATE UNIQUE INDEX exclusiones_adquisicion_22004c15ad_k ON exclusiones_adquisicion(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX constancias_documentales_adquiridas_evaluacion_i ON constancias_documentales_adquiridas(cod_evaluacion_experta);

CREATE UNIQUE INDEX constancias_documentales_adqu_18ac860aee_k ON constancias_documentales_adquiridas(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX membresias_juicio_adquiridas_evaluacion_i ON membresias_juicio_adquiridas(cod_evaluacion_experta);

CREATE UNIQUE INDEX membresias_juicio_adquiridas_d2ca656404_k ON membresias_juicio_adquiridas(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX miembros_soporte_adquiridos_evaluacion_i ON miembros_soporte_adquiridos(cod_evaluacion_experta);

CREATE UNIQUE INDEX miembros_soporte_adquiridos_aad694d27a_k ON miembros_soporte_adquiridos(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX procedencias_juicio_adquiridas_evaluacion_i ON procedencias_juicio_adquiridas(cod_evaluacion_experta);

CREATE UNIQUE INDEX procedencias_juicio_adquirida_29ab5e2fa4_k ON procedencias_juicio_adquiridas(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX juicios_petersen_adquiridos_evaluacion_i ON juicios_petersen_adquiridos(cod_evaluacion_experta);

CREATE UNIQUE INDEX juicios_petersen_adquiridos_92a4375c28_k ON juicios_petersen_adquiridos(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX dominios_revisados_adquiridos_evaluacion_i ON dominios_revisados_adquiridos(cod_evaluacion_experta);

CREATE UNIQUE INDEX dominios_revisados_adquiridos_20781f8f61_k ON dominios_revisados_adquiridos(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE INDEX fuentes_soporte_adquiridas_evaluacion_i ON fuentes_soporte_adquiridas(cod_evaluacion_experta);

CREATE UNIQUE INDEX fuentes_soporte_adquiridas_1ee3511dca_k ON fuentes_soporte_adquiridas(cod_propietario,rol_coleccion,clave_tipo,clave_texto) WHERE clave_tipo<>'NULL';

CREATE OR REPLACE FUNCTION nominal_guardar_hija() RETURNS trigger LANGUAGE plpgsql AS $$
                DECLARE id VARCHAR(20); estado_actual TEXT;
                BEGIN
                    id:=CASE WHEN TG_OP='DELETE' THEN OLD.cod_evaluacion_experta ELSE NEW.cod_evaluacion_experta END;
                    PERFORM pg_advisory_xact_lock(hashtextextended(id,1));
                    SELECT estado INTO estado_actual FROM evaluaciones_nominales WHERE cod_evaluacion_experta=id FOR UPDATE;
                    IF estado_actual='CERRADA' THEN RAISE EXCEPTION 'Evaluación cerrada' USING ERRCODE='55000'; END IF;
                    IF TG_OP='UPDATE' AND OLD.cod_evaluacion_experta<>NEW.cod_evaluacion_experta THEN
                        SELECT estado INTO estado_actual FROM evaluaciones_nominales WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta FOR UPDATE;
                        IF estado_actual='CERRADA' THEN RAISE EXCEPTION 'Evaluación cerrada' USING ERRCODE='55000'; END IF;
                    END IF;
                    IF TG_OP='DELETE' THEN RETURN OLD; END IF; RETURN NEW;
                END $$;

CREATE TRIGGER entradas_institucionales_historia BEFORE INSERT OR UPDATE OR DELETE ON entradas_institucionales FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER capturas_adquiridas_historia BEFORE INSERT OR UPDATE OR DELETE ON capturas_adquiridas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER motivos_captura_historia BEFORE INSERT OR UPDATE OR DELETE ON motivos_captura FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER selectores_captura_historia BEFORE INSERT OR UPDATE OR DELETE ON selectores_captura FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER preguntas_captura_historia BEFORE INSERT OR UPDATE OR DELETE ON preguntas_captura FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER respuestas_captura_historia BEFORE INSERT OR UPDATE OR DELETE ON respuestas_captura FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER vinculos_pregunta_captura_historia BEFORE INSERT OR UPDATE OR DELETE ON vinculos_pregunta_captura FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER contratos_relacion_adquiridos_historia BEFORE INSERT OR UPDATE OR DELETE ON contratos_relacion_adquiridos FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER modalidades_contrato_adquirido_historia BEFORE INSERT OR UPDATE OR DELETE ON modalidades_contrato_adquirido FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_condiciones_regla_experta_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_condiciones_regla_experta FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_consecuencias_regla_experta_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_consecuencias_regla_experta FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_criterios_dominios_resultado_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_criterios_dominios_resultado FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_dominios_valores_expertos_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_dominios_valores_expertos FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_fuentes_datos_expertas_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_fuentes_datos_expertas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_mapeos_valores_fuente_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_mapeos_valores_fuente FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_mapeos_variables_fuente_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_mapeos_variables_fuente FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_nodos_semanticos_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_nodos_semanticos FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_reglas_expertas_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_reglas_expertas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_relaciones_semanticas_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_relaciones_semanticas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_valores_semanticos_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_valores_semanticos FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER conocimiento_variables_expertas_historia BEFORE INSERT OR UPDATE OR DELETE ON conocimiento_variables_expertas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER vinculos_instrumentales_adquiridos_historia BEFORE INSERT OR UPDATE OR DELETE ON vinculos_instrumentales_adquiridos FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER mediciones_requeridas_perfil_historia BEFORE INSERT OR UPDATE OR DELETE ON mediciones_requeridas_perfil FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER predicados_perfil_adquirido_historia BEFORE INSERT OR UPDATE OR DELETE ON predicados_perfil_adquirido FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER exclusiones_adquisicion_historia BEFORE INSERT OR UPDATE OR DELETE ON exclusiones_adquisicion FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER constancias_documentales_adquiridas_historia BEFORE INSERT OR UPDATE OR DELETE ON constancias_documentales_adquiridas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER membresias_juicio_adquiridas_historia BEFORE INSERT OR UPDATE OR DELETE ON membresias_juicio_adquiridas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER miembros_soporte_adquiridos_historia BEFORE INSERT OR UPDATE OR DELETE ON miembros_soporte_adquiridos FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER procedencias_juicio_adquiridas_historia BEFORE INSERT OR UPDATE OR DELETE ON procedencias_juicio_adquiridas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER juicios_petersen_adquiridos_historia BEFORE INSERT OR UPDATE OR DELETE ON juicios_petersen_adquiridos FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER dominios_revisados_adquiridos_historia BEFORE INSERT OR UPDATE OR DELETE ON dominios_revisados_adquiridos FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE TRIGGER fuentes_soporte_adquiridas_historia BEFORE INSERT OR UPDATE OR DELETE ON fuentes_soporte_adquiridas FOR EACH ROW EXECUTE FUNCTION nominal_guardar_hija();

CREATE OR REPLACE FUNCTION nominal_validar_cabecera() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF TG_OP='DELETE' THEN RAISE EXCEPTION 'No se elimina historia' USING ERRCODE='55000'; END IF;
                    IF OLD.estado='CERRADA' OR NEW.estado<>'CERRADA' OR NEW.cod_evaluacion_experta IS DISTINCT FROM OLD.cod_evaluacion_experta OR NEW.solicitud_uuid IS DISTINCT FROM OLD.solicitud_uuid OR NEW.cod_residente IS DISTINCT FROM OLD.cod_residente OR NEW.cod_usuario IS DISTINCT FROM OLD.cod_usuario OR NEW.huella_entrada IS DISTINCT FROM OLD.huella_entrada OR NEW.huella_compuesta IS DISTINCT FROM OLD.huella_compuesta OR NEW.huella_release IS DISTINCT FROM OLD.huella_release OR NEW.contrato IS DISTINCT FROM OLD.contrato THEN
                        RAISE EXCEPTION 'Transición histórica inválida' USING ERRCODE='55000'; END IF;
                    IF (SELECT count(*) FROM entradas_institucionales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)<>1 OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM capturas_adquiridas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_entrada_institucional=p.cod_fila AND h.rol_coleccion='Ca877034852')<>(p.instrumental_capturas_capturas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.instrumental_capturas_capturas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.instrumental_capturas_capturas__cantidad)-1 FROM capturas_adquiridas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_entrada_institucional=p.cod_fila AND h.rol_coleccion='Ca877034852')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM contratos_relacion_adquiridos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C4f168aeb0b')<>(p.i_k_contratos_relaciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_contratos_relaciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_contratos_relaciones__cantidad)-1 FROM contratos_relacion_adquiridos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C4f168aeb0b')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_condiciones_regla_experta h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_condiciones_regla_experta__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_condiciones_regla_experta__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_condiciones_regla_experta__cantidad)-1 FROM conocimiento_condiciones_regla_experta h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_consecuencias_regla_experta h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_consecuencias_regla_experta__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_consecuencias_regla_experta__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_consecuencias_regla_experta__cantidad)-1 FROM conocimiento_consecuencias_regla_experta h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_criterios_dominios_resultado h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_criterios_dominios_resultado__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_criterios_dominios_resultado__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_criterios_dominios_resultado__cantidad)-1 FROM conocimiento_criterios_dominios_resultado h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_dominios_valores_expertos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_dominios_valores_expertos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_dominios_valores_expertos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_dominios_valores_expertos__cantidad)-1 FROM conocimiento_dominios_valores_expertos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_fuentes_datos_expertas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_fuentes_datos_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_fuentes_datos_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_fuentes_datos_expertas__cantidad)-1 FROM conocimiento_fuentes_datos_expertas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_mapeos_valores_fuente h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_mapeos_valores_fuente__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_mapeos_valores_fuente__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_mapeos_valores_fuente__cantidad)-1 FROM conocimiento_mapeos_valores_fuente h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_mapeos_variables_fuente h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_mapeos_variables_fuente__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_mapeos_variables_fuente__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_mapeos_variables_fuente__cantidad)-1 FROM conocimiento_mapeos_variables_fuente h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_nodos_semanticos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_nodos_semanticos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_nodos_semanticos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_nodos_semanticos__cantidad)-1 FROM conocimiento_nodos_semanticos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_reglas_expertas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_reglas_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_reglas_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_reglas_expertas__cantidad)-1 FROM conocimiento_reglas_expertas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_relaciones_semanticas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_relaciones_semanticas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_relaciones_semanticas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_relaciones_semanticas__cantidad)-1 FROM conocimiento_relaciones_semanticas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_valores_semanticos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_valores_semanticos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_valores_semanticos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_valores_semanticos__cantidad)-1 FROM conocimiento_valores_semanticos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_variables_expertas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')<>(p.i_k_tablas_variables_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.i_k_tablas_variables_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.i_k_tablas_variables_expertas__cantidad)-1 FROM conocimiento_variables_expertas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='I')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM vinculos_instrumentales_adquiridos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C7874dd01a9')<>(p.instrumental_vinculos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.instrumental_vinculos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.instrumental_vinculos__cantidad)-1 FROM vinculos_instrumentales_adquiridos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C7874dd01a9')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM exclusiones_adquisicion h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cefd2a57b6f')<>(p.petersen_adquisicion_exclusiones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.petersen_adquisicion_exclusiones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.petersen_adquisicion_exclusiones__cantidad)-1 FROM exclusiones_adquisicion h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cefd2a57b6f')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM constancias_documentales_adquiridas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C7eea8071b0')<>(p.petersen_constancias_documentales__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.petersen_constancias_documentales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.petersen_constancias_documentales__cantidad)-1 FROM constancias_documentales_adquiridas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C7eea8071b0')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM contratos_relacion_adquiridos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C91c39626e7')<>(p.p_k_contratos_relaciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_contratos_relaciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_contratos_relaciones__cantidad)-1 FROM contratos_relacion_adquiridos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C91c39626e7')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_condiciones_regla_experta h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_condiciones_regla_experta__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_condiciones_regla_experta__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_condiciones_regla_experta__cantidad)-1 FROM conocimiento_condiciones_regla_experta h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_consecuencias_regla_experta h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_consecuencias_regla_experta__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_consecuencias_regla_experta__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_consecuencias_regla_experta__cantidad)-1 FROM conocimiento_consecuencias_regla_experta h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_criterios_dominios_resultado h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_criterios_dominios_resultado__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_criterios_dominios_resultado__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_criterios_dominios_resultado__cantidad)-1 FROM conocimiento_criterios_dominios_resultado h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_dominios_valores_expertos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_dominios_valores_expertos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_dominios_valores_expertos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_dominios_valores_expertos__cantidad)-1 FROM conocimiento_dominios_valores_expertos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_fuentes_datos_expertas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_fuentes_datos_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_fuentes_datos_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_fuentes_datos_expertas__cantidad)-1 FROM conocimiento_fuentes_datos_expertas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_mapeos_valores_fuente h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_mapeos_valores_fuente__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_mapeos_valores_fuente__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_mapeos_valores_fuente__cantidad)-1 FROM conocimiento_mapeos_valores_fuente h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_mapeos_variables_fuente h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_mapeos_variables_fuente__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_mapeos_variables_fuente__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_mapeos_variables_fuente__cantidad)-1 FROM conocimiento_mapeos_variables_fuente h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_nodos_semanticos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_nodos_semanticos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_nodos_semanticos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_nodos_semanticos__cantidad)-1 FROM conocimiento_nodos_semanticos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_reglas_expertas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_reglas_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_reglas_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_reglas_expertas__cantidad)-1 FROM conocimiento_reglas_expertas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_relaciones_semanticas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_relaciones_semanticas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_relaciones_semanticas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_relaciones_semanticas__cantidad)-1 FROM conocimiento_relaciones_semanticas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_valores_semanticos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_valores_semanticos__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_valores_semanticos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_valores_semanticos__cantidad)-1 FROM conocimiento_valores_semanticos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM conocimiento_variables_expertas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')<>(p.p_k_tablas_variables_expertas__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.p_k_tablas_variables_expertas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.p_k_tablas_variables_expertas__cantidad)-1 FROM conocimiento_variables_expertas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='P')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM membresias_juicio_adquiridas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Ce2d972a31d')<>(p.petersen_membresias_soportes__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.petersen_membresias_soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.petersen_membresias_soportes__cantidad)-1 FROM membresias_juicio_adquiridas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Ce2d972a31d')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM procedencias_juicio_adquiridas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cd31d2cbcf0')<>(p.petersen_procedencia_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.petersen_procedencia_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.petersen_procedencia_valoraciones__cantidad)-1 FROM procedencias_juicio_adquiridas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cd31d2cbcf0')) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (SELECT count(*) FROM juicios_petersen_adquiridos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cc09aa72640')<>(p.petersen_valoraciones__cantidad)) OR EXISTS(SELECT 1 FROM entradas_institucionales p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (p.petersen_valoraciones__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.petersen_valoraciones__cantidad)-1 FROM juicios_petersen_adquiridos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cc09aa72640')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (SELECT count(*) FROM motivos_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C03fac49475')<>(p.motivos__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (p.motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.motivos__cantidad)-1 FROM motivos_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C03fac49475')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (SELECT count(*) FROM selectores_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C53b52bbfc6')<>(p.perfil_selectores__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (p.perfil_selectores__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.perfil_selectores__cantidad)-1 FROM selectores_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C53b52bbfc6')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (SELECT count(*) FROM preguntas_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Ca070df23f8')<>(p.preguntas_declarativas_originales__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (p.preguntas_declarativas_originales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.preguntas_declarativas_originales__cantidad)-1 FROM preguntas_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Ca070df23f8')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (SELECT count(*) FROM respuestas_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C388d96740f')<>(p.respuestas_originales__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (p.respuestas_originales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.respuestas_originales__cantidad)-1 FROM respuestas_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C388d96740f')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (SELECT count(*) FROM vinculos_pregunta_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C55341a554f')<>(p.vinculos_preguntas_actuales__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ca877034852') AND (p.vinculos_preguntas_actuales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.vinculos_preguntas_actuales__cantidad)-1 FROM vinculos_pregunta_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C55341a554f')) OR EXISTS(SELECT 1 FROM contratos_relacion_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C4f168aeb0b') AND (SELECT count(*) FROM modalidades_contrato_adquirido h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C9e85749841')<>(p.modalidades__cantidad)) OR EXISTS(SELECT 1 FROM contratos_relacion_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C4f168aeb0b') AND (p.modalidades__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.modalidades__cantidad)-1 FROM modalidades_contrato_adquirido h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C9e85749841')) OR EXISTS(SELECT 1 FROM vinculos_instrumentales_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C7874dd01a9') AND (SELECT count(*) FROM mediciones_requeridas_perfil h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C66a9c79cf7')<>(p.perfil_interpretativo_mediciones_requeridas__cantidad)) OR EXISTS(SELECT 1 FROM vinculos_instrumentales_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C7874dd01a9') AND (p.perfil_interpretativo_mediciones_requeridas__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.perfil_interpretativo_mediciones_requeridas__cantidad)-1 FROM mediciones_requeridas_perfil h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C66a9c79cf7')) OR EXISTS(SELECT 1 FROM vinculos_instrumentales_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C7874dd01a9') AND (SELECT count(*) FROM predicados_perfil_adquirido h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C94c27667c0')<>(p.perfil_interpretativo_predicados__cantidad)) OR EXISTS(SELECT 1 FROM vinculos_instrumentales_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C7874dd01a9') AND (p.perfil_interpretativo_predicados__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.perfil_interpretativo_predicados__cantidad)-1 FROM predicados_perfil_adquirido h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C94c27667c0')) OR EXISTS(SELECT 1 FROM contratos_relacion_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C91c39626e7') AND (SELECT count(*) FROM modalidades_contrato_adquirido h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cdb8280ab29')<>(p.modalidades__cantidad)) OR EXISTS(SELECT 1 FROM contratos_relacion_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C91c39626e7') AND (p.modalidades__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.modalidades__cantidad)-1 FROM modalidades_contrato_adquirido h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cdb8280ab29')) OR EXISTS(SELECT 1 FROM membresias_juicio_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ce2d972a31d') AND (SELECT count(*) FROM miembros_soporte_adquiridos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C4607c4e418')<>(p.soportes__cantidad)) OR EXISTS(SELECT 1 FROM membresias_juicio_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Ce2d972a31d') AND (p.soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.soportes__cantidad)-1 FROM miembros_soporte_adquiridos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C4607c4e418')) OR EXISTS(SELECT 1 FROM juicios_petersen_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Cc09aa72640') AND (SELECT count(*) FROM dominios_revisados_adquiridos h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Caf7f6dc239')<>(p.dominios_revisados__cantidad)) OR EXISTS(SELECT 1 FROM juicios_petersen_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Cc09aa72640') AND (p.dominios_revisados__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.dominios_revisados__cantidad)-1 FROM dominios_revisados_adquiridos h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Caf7f6dc239')) OR EXISTS(SELECT 1 FROM juicios_petersen_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Cc09aa72640') AND (SELECT count(*) FROM fuentes_soporte_adquiridas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C027418f6cc')<>(p.soportes__cantidad)) OR EXISTS(SELECT 1 FROM juicios_petersen_adquiridos p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('Cc09aa72640') AND (p.soportes__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.soportes__cantidad)-1 FROM fuentes_soporte_adquiridas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C027418f6cc')) OR EXISTS(SELECT 1 FROM fuentes_soporte_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C027418f6cc') AND (SELECT count(*) FROM capturas_adquiridas h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_fuente_soporte=p.cod_fila AND h.rol_coleccion='CAPTURA_SOPORTE')<>(CASE WHEN p.datos_originales_captura_original__estado='PRESENTE' THEN 1 ELSE 0 END)) OR EXISTS(SELECT 1 FROM fuentes_soporte_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('C027418f6cc') AND (CASE WHEN p.datos_originales_captura_original__estado='PRESENTE' THEN 1 ELSE 0 END)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(CASE WHEN p.datos_originales_captura_original__estado='PRESENTE' THEN 1 ELSE 0 END)-1 FROM capturas_adquiridas h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_fuente_soporte=p.cod_fila AND h.rol_coleccion='CAPTURA_SOPORTE')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (SELECT count(*) FROM motivos_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Ca33175a241')<>(p.motivos__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (p.motivos__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.motivos__cantidad)-1 FROM motivos_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Ca33175a241')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (SELECT count(*) FROM selectores_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cc9e61d6130')<>(p.perfil_selectores__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (p.perfil_selectores__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.perfil_selectores__cantidad)-1 FROM selectores_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='Cc9e61d6130')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (SELECT count(*) FROM preguntas_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C52d856d563')<>(p.preguntas_declarativas_originales__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (p.preguntas_declarativas_originales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.preguntas_declarativas_originales__cantidad)-1 FROM preguntas_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C52d856d563')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (SELECT count(*) FROM respuestas_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C014dd1988b')<>(p.respuestas_originales__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (p.respuestas_originales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.respuestas_originales__cantidad)-1 FROM respuestas_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C014dd1988b')) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (SELECT count(*) FROM vinculos_pregunta_captura h WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C7b4dbb2388')<>(p.vinculos_preguntas_actuales__cantidad)) OR EXISTS(SELECT 1 FROM capturas_adquiridas p WHERE p.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND p.rol_coleccion IN ('CAPTURA_SOPORTE') AND (p.vinculos_preguntas_actuales__cantidad)>0 AND (SELECT min(posicion_coleccion)<>0 OR max(posicion_coleccion)<>(p.vinculos_preguntas_actuales__cantidad)-1 FROM vinculos_pregunta_captura h
                    WHERE h.cod_evaluacion_experta=p.cod_evaluacion_experta AND h.cod_propietario=p.cod_fila AND h.rol_coleccion='C7b4dbb2388')) THEN RAISE EXCEPTION 'Conservación incompleta' USING ERRCODE='23514'; END IF;
                    RETURN NEW;
                END $$;

CREATE TRIGGER nominal_cabecera BEFORE UPDATE OR DELETE ON evaluaciones_nominales FOR EACH ROW EXECUTE FUNCTION nominal_validar_cabecera();

CREATE OR REPLACE FUNCTION nominal_iniciar() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    PERFORM pg_advisory_xact_lock(hashtextextended(NEW.cod_evaluacion_experta,1));
                    IF NEW.estado<>'ABIERTA' THEN RAISE EXCEPTION 'Debe conservar antes de cerrar' USING ERRCODE='23514'; END IF;
                    RETURN NEW;
                END $$;

CREATE TRIGGER nominal_insertar BEFORE INSERT ON evaluaciones_nominales FOR EACH ROW EXECUTE FUNCTION nominal_iniciar();

CREATE OR REPLACE FUNCTION nominal_exigir_cerrada() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF (SELECT estado FROM evaluaciones_nominales WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta)<>'CERRADA'
                        THEN RAISE EXCEPTION 'No se confirma una evaluación parcial' USING ERRCODE='23514'; END IF;
                    RETURN NULL;
                END $$;

CREATE CONSTRAINT TRIGGER nominal_confirmar AFTER INSERT OR UPDATE ON evaluaciones_nominales
                DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION nominal_exigir_cerrada();

CREATE OR REPLACE FUNCTION nominal_release_inmutable() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN RAISE EXCEPTION 'Release inmutable' USING ERRCODE='55000'; END $$;

CREATE TRIGGER nominal_release BEFORE UPDATE OR DELETE ON releases_nominales FOR EACH ROW EXECUTE FUNCTION nominal_release_inmutable();

CREATE OR REPLACE FUNCTION nominal_sin_truncate() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN RAISE EXCEPTION 'No se trunca historia' USING ERRCODE='55000'; END $$;

CREATE TRIGGER entradas_institucionales_sin_truncate BEFORE TRUNCATE ON entradas_institucionales FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER capturas_adquiridas_sin_truncate BEFORE TRUNCATE ON capturas_adquiridas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER motivos_captura_sin_truncate BEFORE TRUNCATE ON motivos_captura FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER selectores_captura_sin_truncate BEFORE TRUNCATE ON selectores_captura FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER preguntas_captura_sin_truncate BEFORE TRUNCATE ON preguntas_captura FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER respuestas_captura_sin_truncate BEFORE TRUNCATE ON respuestas_captura FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER vinculos_pregunta_captura_sin_truncate BEFORE TRUNCATE ON vinculos_pregunta_captura FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER contratos_relacion_adquiridos_sin_truncate BEFORE TRUNCATE ON contratos_relacion_adquiridos FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER modalidades_contrato_adquirido_sin_truncate BEFORE TRUNCATE ON modalidades_contrato_adquirido FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_condiciones_regla_experta_sin_truncate BEFORE TRUNCATE ON conocimiento_condiciones_regla_experta FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_consecuencias_regla_experta_sin_truncate BEFORE TRUNCATE ON conocimiento_consecuencias_regla_experta FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_criterios_dominios_resultado_sin_truncate BEFORE TRUNCATE ON conocimiento_criterios_dominios_resultado FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_dominios_valores_expertos_sin_truncate BEFORE TRUNCATE ON conocimiento_dominios_valores_expertos FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_fuentes_datos_expertas_sin_truncate BEFORE TRUNCATE ON conocimiento_fuentes_datos_expertas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_mapeos_valores_fuente_sin_truncate BEFORE TRUNCATE ON conocimiento_mapeos_valores_fuente FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_mapeos_variables_fuente_sin_truncate BEFORE TRUNCATE ON conocimiento_mapeos_variables_fuente FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_nodos_semanticos_sin_truncate BEFORE TRUNCATE ON conocimiento_nodos_semanticos FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_reglas_expertas_sin_truncate BEFORE TRUNCATE ON conocimiento_reglas_expertas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_relaciones_semanticas_sin_truncate BEFORE TRUNCATE ON conocimiento_relaciones_semanticas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_valores_semanticos_sin_truncate BEFORE TRUNCATE ON conocimiento_valores_semanticos FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER conocimiento_variables_expertas_sin_truncate BEFORE TRUNCATE ON conocimiento_variables_expertas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER vinculos_instrumentales_adquiridos_sin_truncate BEFORE TRUNCATE ON vinculos_instrumentales_adquiridos FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER mediciones_requeridas_perfil_sin_truncate BEFORE TRUNCATE ON mediciones_requeridas_perfil FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER predicados_perfil_adquirido_sin_truncate BEFORE TRUNCATE ON predicados_perfil_adquirido FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER exclusiones_adquisicion_sin_truncate BEFORE TRUNCATE ON exclusiones_adquisicion FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER constancias_documentales_adquiridas_sin_truncate BEFORE TRUNCATE ON constancias_documentales_adquiridas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER membresias_juicio_adquiridas_sin_truncate BEFORE TRUNCATE ON membresias_juicio_adquiridas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER miembros_soporte_adquiridos_sin_truncate BEFORE TRUNCATE ON miembros_soporte_adquiridos FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER procedencias_juicio_adquiridas_sin_truncate BEFORE TRUNCATE ON procedencias_juicio_adquiridas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER juicios_petersen_adquiridos_sin_truncate BEFORE TRUNCATE ON juicios_petersen_adquiridos FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER dominios_revisados_adquiridos_sin_truncate BEFORE TRUNCATE ON dominios_revisados_adquiridos FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER fuentes_soporte_adquiridas_sin_truncate BEFORE TRUNCATE ON fuentes_soporte_adquiridas FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER evaluaciones_nominales_sin_truncate BEFORE TRUNCATE ON evaluaciones_nominales FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();

CREATE TRIGGER releases_nominales_sin_truncate BEFORE TRUNCATE ON releases_nominales FOR EACH STATEMENT EXECUTE FUNCTION nominal_sin_truncate();
