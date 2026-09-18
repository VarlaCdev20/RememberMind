# RememberMind — BDD Operativa V2: 69 tablas

**Estado:** CONGELADO  
**Documento complementario:** `REMEMBERMIND_BDD_BASELINE_CONGELADO.md`  
**Entidad central:** `residentes`

> Este documento define las entidades, atributos, PK, FK y relaciones de la BDD operativa. No modificar sin consulta y aprobación previa.

## Convenciones de tipos

Los tipos son **tipos lógicos orientativos para migraciones Laravel**:

- `string(n)` → `VARCHAR(n)` / `$table->string(..., n)`
- `text` → TEXT
- `date` → DATE
- `datetime` → DATETIME/TIMESTAMP según estrategia Laravel
- `time` → TIME
- `boolean` → BOOLEAN
- `decimal(p,s)` → DECIMAL(p,s)
- `smallint` → SMALLINT
- `unsignedBigInteger` → entero sin signo cuando el motor lo soporte

Todas las PK `cod_*` usan `string(20)` salvo decisión futura explícitamente aprobada.

## Resumen

- 11 maestras
- 45 transaccionales
- 7 intermedias
- 6 auxiliares
- **69 operativas**
- Sistema experto: fuera de este documento
- Tablas Laravel/Spatie: fuera del conteo

---

## 1. `usuarios` — Maestra

**PK:** `cod_usuario string(20)`


**FK:** ninguna.


**Atributos:**

- `correo string(120) UNIQUE NOT NULL`

- `contrasena string(255) NOT NULL`

- `foto string(255) NULL`

- `estado string(20) NOT NULL`


**Relaciones:**

- 1 — 0..1 personal

- 1 — 0..N contactos (solo contactos con cuenta)


**Reglas/observaciones:** Cuenta de acceso; no almacena identidad personal duplicada.


## 2. `personal` — Maestra

**PK:** `cod_personal string(20)`


**FK:**

- `cod_usuario → usuarios.cod_usuario UNIQUE`


**Atributos:**

- `nombres string(100)`

- `apellido_paterno string(80)`

- `apellido_materno string(80) NULL`

- `numero_documento string(30) UNIQUE`

- `expedicion_documento string(20) NULL`

- `fecha_nacimiento date NULL`

- `genero string(20) NULL`

- `telefono string(30) NULL`

- `direccion string(255) NULL`

- `profesion string(80)`

- `especialidad string(120) NULL`

- `matricula_profesional string(50) NULL`

- `fecha_ingreso date NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — M jornadas/areas mediante asignaciones_personal

- 1 — N atenciones y registros clínicos


## 3. `areas` — Maestra

**PK:** `cod_area string(20)`


**FK:** ninguna.


**Atributos:**

- `nombre string(80) UNIQUE`

- `descripcion text NULL`

- `estado string(20)`


**Relaciones:**

- 1 — N asignaciones_personal

- 1 — N atenciones

- 1 — N planes_cuidado

- 1 — N actividades


## 4. `turnos` — Maestra

**PK:** `cod_turno string(20)`


**FK:** ninguna.


**Atributos:**

- `nombre string(50) UNIQUE`

- `hora_inicio time`

- `hora_cierre time`

- `orden smallint`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N jornadas

- 1 — N programaciones_cuidado


## 5. `jornadas` — Transaccional

**PK:** `cod_jornada string(20)`


**FK:**

- `cod_turno → turnos.cod_turno`

- `cod_usuario_apertura → usuarios.cod_usuario NULL`

- `cod_usuario_cierre → usuarios.cod_usuario NULL`


**Atributos:**

- `fecha_jornada date`

- `estado string(20)`


**Relaciones:**

- 1 — N asignaciones_personal

- 1 — N asignaciones_residente_jornada

- 1 — N registros operativos por turno


## 6. `asignaciones_personal` — Intermedia

**PK:** `cod_asignacion_personal string(20)`


**FK:**

- `cod_jornada → jornadas.cod_jornada`

- `cod_personal → personal.cod_personal`

- `cod_area → areas.cod_area`


**Atributos:**

- `funcion string(80) NULL`

- `tipo_asignacion string(30)`

- `fecha_asignacion datetime`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 jornada

- N — 1 personal

- N — 1 área


## 7. `preadmisiones` — Transaccional

**PK:** `cod_preadmision string(20)`


**FK:**

- `cod_contacto → contactos.cod_contacto NULL`

- `cod_usuario_registro → usuarios.cod_usuario`

- `cod_usuario_revision → usuarios.cod_usuario NULL`


**Atributos:**

- `nombres string(100)`

- `apellido_paterno string(80)`

- `apellido_materno string(80) NULL`

- `numero_documento string(30) NULL`

- `expedicion_documento string(20) NULL`

- `fecha_nacimiento date`

- `genero string(20) NULL`

- `estado_civil string(30) NULL`

- `telefono string(30) NULL`

- `direccion string(255) NULL`

- `motivo_ingreso text`

- `procedencia string(120) NULL`

- `tipo_ingreso string(50) NULL`

- `permanencia string(50) NULL`

- `prioridad string(20) NULL`

- `descripcion_caso text NULL`

- `fecha_solicitud datetime`

- `fecha_revision datetime NULL`

- `estado string(20)`

- `motivo_rechazo text NULL`


**Relaciones:**

- 0..1 — 1 admision

- puede vincular contacto inicial


**Reglas/observaciones:** Estados mínimos: PENDIENTE, APROBADA, RECHAZADA.


## 8. `contactos` — Maestra

**PK:** `cod_contacto string(20)`


**FK:**

- `cod_usuario → usuarios.cod_usuario NULL UNIQUE`


**Atributos:**

- `nombres string(100)`

- `apellido_paterno string(80)`

- `apellido_materno string(80) NULL`

- `numero_documento string(30) NULL`

- `telefono string(30) NULL`

- `celular string(30) NULL`

- `correo string(120) NULL`

- `direccion string(255) NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — M residentes mediante residentes_contactos

- 1 — N visitas


## 9. `residentes_contactos` — Intermedia

**PK:** `cod_residente_contacto string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_contacto → contactos.cod_contacto`


**Atributos:**

- `parentesco string(40)`

- `responsable_principal boolean`

- `contacto_emergencia boolean`

- `autoriza_informacion boolean`

- `autoriza_salida boolean`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente

- N — 1 contacto

- 1 — N consentimientos cuando firma el contacto


**Reglas/observaciones:** UNIQUE lógico/DB sobre par residente-contacto activo.


## 10. `admisiones` — Transaccional

**PK:** `cod_admision string(20)`


**FK:**

- `cod_preadmision → preadmisiones.cod_preadmision NULL`

- `cod_residente → residentes.cod_residente`

- `cod_usuario_registro → usuarios.cod_usuario`


**Atributos:**

- `fecha_hora_admision datetime`

- `tipo_ingreso string(50) NULL`

- `procedencia string(120) NULL`

- `motivo_ingreso text`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — 1 residente por evento de admisión vigente

- 1 — N ocupaciones_cama

- 1 — N consentimientos


## 11. `residentes` — Maestra principal

**PK:** `cod_residente string(20)`


**FK:** ninguna.


**Atributos:**

- `nombres string(100)`

- `apellido_paterno string(80)`

- `apellido_materno string(80) NULL`

- `numero_documento string(30) UNIQUE NULL`

- `complemento_documento string(10) NULL`

- `expedicion_documento string(20) NULL`

- `fecha_nacimiento date`

- `genero string(20) NULL`

- `estado_civil string(30) NULL`

- `telefono string(30) NULL`

- `celular string(30) NULL`

- `direccion string(255) NULL`

- `nivel_educativo string(80) NULL`

- `grupo_sanguineo string(5) NULL`

- `factor_rh string(5) NULL`

- `foto string(255) NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- Entidad central: relacionada directa/indirectamente con todo expediente y seguimiento


**Reglas/observaciones:** Solo se crea mediante admisión formal.


## 12. `historial_estados_residente` — Transaccional

**PK:** `cod_historial_estado string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_usuario_registro → usuarios.cod_usuario`


**Atributos:**

- `estado_anterior string(30) NULL`

- `estado_nuevo string(30)`

- `fecha_hora datetime`

- `motivo text NULL`


**Relaciones:**

- N — 1 residente


## 13. `habitaciones` — Maestra

**PK:** `cod_habitacion string(20)`


**FK:** ninguna.


**Atributos:**

- `codigo string(30) UNIQUE`

- `nombre string(80) NULL`

- `tipo string(40) NULL`

- `piso string(30) NULL`

- `capacidad smallint`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N camas


## 14. `camas` — Maestra

**PK:** `cod_cama string(20)`


**FK:**

- `cod_habitacion → habitaciones.cod_habitacion`


**Atributos:**

- `codigo string(30) UNIQUE`

- `tipo string(40) NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N ocupaciones_cama


## 15. `ocupaciones_cama` — Intermedia

**PK:** `cod_ocupacion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_cama → camas.cod_cama`

- `cod_admision → admisiones.cod_admision`

- `cod_usuario_registro → usuarios.cod_usuario`


**Atributos:**

- `fecha_hora_asignacion datetime`

- `fecha_hora_liberacion datetime NULL`

- `motivo_liberacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente

- N — 1 cama

- N — 1 admisión


**Reglas/observaciones:** Máximo una ocupación activa por cama y por residente.


## 16. `documentos` — Transaccional

**PK:** `cod_documento string(20)`


**FK:**

- `cod_preadmision → preadmisiones.cod_preadmision NULL`

- `cod_residente → residentes.cod_residente NULL`

- `cod_usuario → usuarios.cod_usuario NULL`

- `cod_contacto → contactos.cod_contacto NULL`

- `cod_documento_anterior → documentos.cod_documento NULL`

- `cod_usuario_validacion → usuarios.cod_usuario NULL`


**Atributos:**

- `tipo_documento string(60)`

- `nombre string(160)`

- `ruta_archivo string(500)`

- `tipo_archivo string(80)`

- `hash_archivo string(128)`

- `fecha_vencimiento date NULL`

- `fecha_validacion datetime NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- Pertenece a exactamente un contexto principal mediante regla de servicio/check


**Reglas/observaciones:** Sin campo version; sucesión por cod_documento_anterior.


## 17. `consentimientos` — Transaccional

**PK:** `cod_consentimiento string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_admision → admisiones.cod_admision`

- `cod_residente_contacto → residentes_contactos.cod_residente_contacto NULL`

- `cod_documento → documentos.cod_documento NULL`

- `cod_usuario_registro → usuarios.cod_usuario`


**Atributos:**

- `tipo_consentimiento string(80)`

- `firma_residente boolean`

- `fecha_consentimiento datetime`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente

- N — 1 admisión


**Reglas/observaciones:** No tiene aceptado, firmante ni tipo_firmante. Si firma_residente=false, requiere cod_residente_contacto del mismo residente.


## 18. `atenciones` — Transaccional

**PK:** `cod_atencion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_area → areas.cod_area`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `tipo_atencion string(60)`

- `motivo text NULL`

- `fecha_hora datetime`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente

- 1 — N notas/diagnósticos/estudios/indicaciones/prescripciones/valoraciones


## 19. `notas_clinicas` — Transaccional

**PK:** `cod_nota string(20)`


**FK:**

- `cod_atencion → atenciones.cod_atencion`

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_nota_anterior → notas_clinicas.cod_nota NULL`


**Atributos:**

- `tipo_nota string(50)`

- `contenido text`

- `fecha_hora datetime`

- `motivo_correccion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 atención

- cadena de correcciones mediante cod_nota_anterior


## 20. `antecedentes_clinicos` — Transaccional

**PK:** `cod_antecedente string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `tipo_antecedente string(60)`

- `descripcion text`

- `fecha_referencia date NULL`

- `fuente_informacion string(80) NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente


## 21. `diagnosticos` — Transaccional

**PK:** `cod_diagnostico string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_atencion → atenciones.cod_atencion`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `codigo_clinico string(30) NULL`

- `nombre string(160)`

- `tipo string(50) NULL`

- `certeza string(30) NULL`

- `fecha_hora datetime`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente

- N — 1 atención


## 22. `alergias` — Transaccional

**PK:** `cod_alergia string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `tipo string(40) NULL`

- `sustancia string(120)`

- `reaccion text NULL`

- `gravedad string(30) NULL`

- `fecha_hora datetime`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente


## 23. `seguros_residente` — Transaccional

**PK:** `cod_seguro string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`


**Atributos:**

- `entidad string(120)`

- `plan string(100) NULL`

- `numero_afiliacion string(80) NULL`

- `titular string(160) NULL`

- `cobertura text NULL`

- `telefono string(30) NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 24. `dispositivos_clinicos` — Transaccional

**PK:** `cod_dispositivo string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `tipo string(60)`

- `descripcion text NULL`

- `ubicacion string(120) NULL`

- `fecha_colocacion datetime NULL`

- `fecha_retiro datetime NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente


## 25. `signos_vitales` — Transaccional

**PK:** `cod_signo string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada NULL`

- `cod_atencion → atenciones.cod_atencion NULL`


**Atributos:**

- `fecha_hora datetime`

- `presion_sistolica decimal(5,2) NULL`

- `presion_diastolica decimal(5,2) NULL`

- `frecuencia_cardiaca decimal(6,2) NULL`

- `frecuencia_respiratoria decimal(6,2) NULL`

- `temperatura decimal(4,1) NULL`

- `saturacion_oxigeno decimal(5,2) NULL`

- `glucemia decimal(8,2) NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente


## 26. `valoraciones_dolor` — Transaccional

**PK:** `cod_valoracion_dolor string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_atencion → atenciones.cod_atencion NULL`


**Atributos:**

- `fecha_hora datetime`

- `intensidad unsignedTinyInteger NULL`

- `ubicacion string(120) NULL`

- `tipo_dolor string(60) NULL`

- `duracion string(80) NULL`

- `desencadenante text NULL`

- `intervencion text NULL`

- `respuesta text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


**Reglas/observaciones:** Intensidad validada en escala definida por la aplicación.


## 27. `mediciones_antropometricas` — Transaccional

**PK:** `cod_medicion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `fecha_hora datetime`

- `peso decimal(6,2) NULL`

- `talla decimal(5,2) NULL`

- `imc decimal(5,2) NULL`

- `perimetro_braquial decimal(6,2) NULL`

- `perimetro_pantorrilla decimal(6,2) NULL`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente


## 28. `tipos_estudio_clinico` — Maestra

**PK:** `cod_tipo_estudio string(20)`


**FK:** ninguna.


**Atributos:**

- `nombre string(120) UNIQUE`

- `categoria string(50)`

- `descripcion text NULL`

- `requiere_componentes boolean`

- `requiere_informe boolean`

- `estado string(20)`


**Relaciones:**

- 1 — N componentes_estudio

- 1 — N estudios_clinicos


## 29. `componentes_estudio` — Auxiliar

**PK:** `cod_componente string(20)`


**FK:**

- `cod_tipo_estudio → tipos_estudio_clinico.cod_tipo_estudio`


**Atributos:**

- `nombre string(120)`

- `unidad_referencia string(40) NULL`

- `tipo_resultado string(30)`

- `orden smallint`

- `estado string(20)`


**Relaciones:**

- N — 1 tipo_estudio

- 1 — N resultados_estudio


## 30. `estudios_clinicos` — Transaccional

**PK:** `cod_estudio string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_atencion → atenciones.cod_atencion`

- `cod_tipo_estudio → tipos_estudio_clinico.cod_tipo_estudio`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `motivo text NULL`

- `prioridad string(20) NULL`

- `fecha_solicitud datetime`

- `fecha_realizacion datetime NULL`

- `centro_medico string(160) NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N resultados_estudio

- 1 — N informes_estudio

- 1 — N documentos_clinicos


## 31. `resultados_estudio` — Intermedia

**PK:** `cod_resultado_estudio string(20)`


**FK:**

- `cod_estudio → estudios_clinicos.cod_estudio`

- `cod_componente → componentes_estudio.cod_componente`


**Atributos:**

- `valor_numerico decimal(18,6) NULL`

- `valor_texto text NULL`

- `unidad string(40) NULL`

- `rango_referencia string(120) NULL`

- `clasificacion string(30) NULL`

- `observacion text NULL`


**Relaciones:**

- N — 1 estudio

- N — 1 componente


**Reglas/observaciones:** UNIQUE recomendado (cod_estudio,cod_componente) salvo excepción aprobada.


## 32. `informes_estudio` — Transaccional

**PK:** `cod_informe_estudio string(20)`


**FK:**

- `cod_estudio → estudios_clinicos.cod_estudio`

- `cod_personal → personal.cod_personal NULL`


**Atributos:**

- `fecha_hora datetime`

- `hallazgos text NULL`

- `conclusion text NULL`

- `recomendacion text NULL`

- `origen string(20)`

- `profesional_externo string(160) NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 estudio


## 33. `documentos_clinicos` — Transaccional

**PK:** `cod_documento_clinico string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_estudio → estudios_clinicos.cod_estudio NULL`

- `cod_atencion → atenciones.cod_atencion NULL`

- `cod_personal → personal.cod_personal NULL`


**Atributos:**

- `tipo_documento string(60)`

- `titulo string(180)`

- `descripcion text NULL`

- `ruta_archivo string(500)`

- `formato string(30)`

- `tamano_bytes unsignedBigInteger NULL`

- `hash_archivo string(128)`

- `fecha_hora datetime`

- `origen string(20) NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente

- N — 0..1 estudio/atención


**Reglas/observaciones:** Guardar archivo en almacenamiento privado; no BLOB por defecto.


## 34. `derivaciones` — Transaccional

**PK:** `cod_derivacion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_area_solicitante → areas.cod_area`

- `cod_area_receptora → areas.cod_area`

- `cod_personal_solicitante → personal.cod_personal`

- `cod_personal_receptor → personal.cod_personal NULL`

- `cod_atencion → atenciones.cod_atencion NULL`


**Atributos:**

- `motivo text`

- `prioridad string(20) NULL`

- `fecha_hora datetime`

- `respuesta text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 35. `incidentes` — Transaccional

**PK:** `cod_incidente string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada NULL`


**Atributos:**

- `tipo_incidente string(60)`

- `gravedad string(30) NULL`

- `lugar string(120) NULL`

- `fecha_hora datetime`

- `descripcion text`

- `medida_inmediata text NULL`

- `requiere_medico boolean`

- `requiere_derivacion boolean`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente


## 36. `indicaciones_clinicas` — Transaccional

**PK:** `cod_indicacion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_atencion → atenciones.cod_atencion`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `tipo_indicacion string(40)`

- `descripcion text`

- `prioridad string(20) NULL`

- `fecha_hora datetime`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente

- N — 1 atención


**Reglas/observaciones:** Tipos incluyen CUIDADO, RESTRICCION, MONITOREO, DIETA, MOVILIDAD, CONTROL; medicamentos van en prescripciones.


## 37. `asignaciones_residente_jornada` — Intermedia

**PK:** `cod_asignacion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_jornada → jornadas.cod_jornada`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `nivel_supervision string(30) NULL`

- `fecha_hora datetime`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- Relaciona residente-personal-jornada


## 38. `controles_cognitivos` — Transaccional

**PK:** `cod_control_cognitivo string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada NULL`

- `cod_atencion → atenciones.cod_atencion NULL`


**Atributos:**

- `fecha_hora datetime`

- `orientacion_persona string(30) NULL`

- `orientacion_lugar string(30) NULL`

- `orientacion_tiempo string(30) NULL`

- `memoria_reciente string(30) NULL`

- `memoria_remota string(30) NULL`

- `atencion string(30) NULL`

- `comprension string(30) NULL`

- `lenguaje string(30) NULL`

- `sigue_instrucciones boolean NULL`

- `repite_preguntas boolean NULL`

- `olvida_indicaciones boolean NULL`

- `reconoce_personas boolean NULL`

- `reconoce_entorno boolean NULL`

- `confusion boolean NULL`

- `cambio_cognitivo boolean NULL`

- `observacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


**Reglas/observaciones:** Observación longitudinal; no es diagnóstico.


## 39. `registros_conductuales` — Transaccional

**PK:** `cod_registro_conductual string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada NULL`

- `cod_atencion → atenciones.cod_atencion NULL`


**Atributos:**

- `fecha_hora datetime`

- `estado_animo string(40) NULL`

- `apatia boolean NULL`

- `agitacion boolean NULL`

- `agresividad boolean NULL`

- `ansiedad boolean NULL`

- `aislamiento boolean NULL`

- `deambulacion boolean NULL`

- `participacion string(30) NULL`

- `cambio_conducta boolean NULL`

- `descripcion text NULL`

- `intervencion text NULL`

- `respuesta text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 40. `registros_sueno` — Transaccional

**PK:** `cod_registro_sueno string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada NULL`


**Atributos:**

- `fecha date`

- `horas_sueno decimal(4,2) NULL`

- `despertares smallint NULL`

- `insomnio boolean NULL`

- `somnolencia_diurna boolean NULL`

- `agitacion_nocturna boolean NULL`

- `calidad string(30) NULL`

- `observacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 41. `registros_ingesta` — Transaccional

**PK:** `cod_ingesta string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada`


**Atributos:**

- `fecha_hora datetime`

- `tipo_comida string(40)`

- `porcentaje_consumido decimal(5,2) NULL`

- `apetito string(30) NULL`

- `tolerancia string(30) NULL`

- `dificultad_deglucion boolean NULL`

- `observacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 42. `registros_hidratacion` — Transaccional

**PK:** `cod_hidratacion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada`


**Atributos:**

- `fecha_hora datetime`

- `cantidad_ml decimal(8,2)`

- `tipo_liquido string(60) NULL`

- `tolerancia string(30) NULL`

- `observacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 43. `registros_eliminacion` — Transaccional

**PK:** `cod_eliminacion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada`


**Atributos:**

- `fecha_hora datetime`

- `tipo_eliminacion string(30)`

- `cantidad string(40) NULL`

- `caracteristica string(120) NULL`

- `continencia string(30) NULL`

- `observacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 44. `registros_movilidad` — Transaccional

**PK:** `cod_movilidad string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada NULL`

- `cod_atencion → atenciones.cod_atencion NULL`


**Atributos:**

- `fecha_hora datetime`

- `marcha string(40) NULL`

- `equilibrio string(40) NULL`

- `traslado string(40) NULL`

- `tipo_apoyo string(60) NULL`

- `dispositivo string(80) NULL`

- `fatiga string(30) NULL`

- `riesgo_caida string(30) NULL`

- `observacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 45. `heridas` — Transaccional

**PK:** `cod_herida string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `tipo_herida string(60)`

- `ubicacion string(120)`

- `causa string(120) NULL`

- `clasificacion string(60) NULL`

- `fecha_hora_identificacion datetime`

- `fecha_hora_cierre datetime NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N curaciones_herida

- N — 1 residente


## 46. `curaciones_herida` — Transaccional

**PK:** `cod_curacion string(20)`


**FK:**

- `cod_herida → heridas.cod_herida`

- `cod_personal → personal.cod_personal`

- `cod_jornada → jornadas.cod_jornada NULL`


**Atributos:**

- `fecha_hora datetime`

- `longitud decimal(7,2) NULL`

- `ancho decimal(7,2) NULL`

- `profundidad decimal(7,2) NULL`

- `tejido string(80) NULL`

- `exudado string(80) NULL`

- `olor string(80) NULL`

- `dolor string(40) NULL`

- `procedimiento text`

- `materiales text NULL`

- `respuesta text NULL`

- `observacion text NULL`


**Relaciones:**

- N — 1 herida


## 47. `pases_turno` — Transaccional

**PK:** `cod_pase string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_jornada_saliente → jornadas.cod_jornada`

- `cod_jornada_entrante → jornadas.cod_jornada`

- `cod_personal_saliente → personal.cod_personal`

- `cod_personal_entrante → personal.cod_personal NULL`


**Atributos:**

- `fecha_hora datetime`

- `estado_general text NULL`

- `resumen text`

- `pendientes text NULL`

- `vigilancia text NULL`

- `recomendacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 48. `planes_cuidado` — Transaccional

**PK:** `cod_plan string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_area → areas.cod_area`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `tipo_plan string(50)`

- `nombre string(160)`

- `objetivo_general text`

- `prioridad string(20) NULL`

- `fecha_hora_apertura datetime`

- `fecha_hora_cierre datetime NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N intervenciones_cuidado

- N — 1 residente


## 49. `intervenciones_cuidado` — Auxiliar

**PK:** `cod_intervencion string(20)`


**FK:**

- `cod_plan → planes_cuidado.cod_plan`


**Atributos:**

- `nombre string(160)`

- `descripcion text`

- `objetivo_especifico text NULL`

- `prioridad string(20) NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 plan

- 1 — N programaciones

- 1 — N ejecuciones


## 50. `programaciones_cuidado` — Auxiliar

**PK:** `cod_programacion string(20)`


**FK:**

- `cod_intervencion → intervenciones_cuidado.cod_intervencion`

- `cod_turno → turnos.cod_turno NULL`


**Atributos:**

- `frecuencia string(60)`

- `dias_semana string(50) NULL`

- `hora_programada time NULL`

- `fecha_activacion date`

- `fecha_desactivacion date NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 intervención


## 51. `ejecuciones_cuidado` — Transaccional

**PK:** `cod_ejecucion string(20)`


**FK:**

- `cod_intervencion → intervenciones_cuidado.cod_intervencion`

- `cod_residente → residentes.cod_residente`

- `cod_jornada → jornadas.cod_jornada`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `fecha_hora_programada datetime NULL`

- `fecha_hora_ejecucion datetime NULL`

- `resultado string(60) NULL`

- `motivo_omision text NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 intervención

- N — 1 residente


## 52. `medicamentos` — Maestra

**PK:** `cod_medicamento string(20)`


**FK:** ninguna.


**Atributos:**

- `nombre_generico string(120)`

- `nombre_comercial string(120) NULL`

- `concentracion string(60) NULL`

- `forma_farmaceutica string(60) NULL`

- `unidad string(30) NULL`

- `via_predeterminada string(60) NULL`

- `control_especial boolean`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N prescripciones


## 53. `prescripciones` — Transaccional

**PK:** `cod_prescripcion string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_atencion → atenciones.cod_atencion`

- `cod_medicamento → medicamentos.cod_medicamento`

- `cod_personal → personal.cod_personal`

- `cod_personal_suspension → personal.cod_personal NULL`


**Atributos:**

- `dosis decimal(10,3) NULL`

- `unidad_dosis string(30) NULL`

- `via_administracion string(60)`

- `frecuencia string(80) NULL`

- `indicacion text NULL`

- `segun_necesidad boolean`

- `fecha_hora_prescripcion datetime`

- `fecha_hora_suspension datetime NULL`

- `motivo_suspension text NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N horarios_prescripcion

- 1 — N administraciones_medicacion

- N — 1 residente


## 54. `horarios_prescripcion` — Auxiliar

**PK:** `cod_horario_prescripcion string(20)`


**FK:**

- `cod_prescripcion → prescripciones.cod_prescripcion`


**Atributos:**

- `hora_programada time`

- `dosis_programada decimal(10,3) NULL`

- `dias_semana string(50) NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 prescripción


## 55. `administraciones_medicacion` — Transaccional

**PK:** `cod_administracion string(20)`


**FK:**

- `cod_prescripcion → prescripciones.cod_prescripcion`

- `cod_horario_prescripcion → horarios_prescripcion.cod_horario_prescripcion NULL`

- `cod_residente → residentes.cod_residente`

- `cod_jornada → jornadas.cod_jornada`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `fecha_hora_programada datetime NULL`

- `fecha_hora_administracion datetime NULL`

- `resultado string(40)`

- `dosis_administrada decimal(10,3) NULL`

- `motivo_omision text NULL`

- `efecto_observado text NULL`

- `reaccion_adversa text NULL`

- `observacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 prescripción


**Reglas/observaciones:** Validar que residente coincida con la prescripción.


## 56. `instrumentos` — Maestra

**PK:** `cod_instrumento string(20)`


**FK:** ninguna.


**Atributos:**

- `codigo string(30) UNIQUE`

- `nombre string(160)`

- `tipo string(50)`

- `version string(30) NULL`

- `descripcion text NULL`

- `puntaje_maximo decimal(8,2) NULL`

- `estado string(20)`


**Relaciones:**

- 1 — N preguntas_instrumento

- 1 — N aplicaciones_instrumento


**Reglas/observaciones:** El campo version aquí sí corresponde a la versión formal del instrumento.


## 57. `preguntas_instrumento` — Auxiliar

**PK:** `cod_pregunta string(20)`


**FK:**

- `cod_instrumento → instrumentos.cod_instrumento`


**Atributos:**

- `codigo string(30)`

- `enunciado text`

- `dominio string(80) NULL`

- `tipo_respuesta string(30)`

- `puntaje_maximo decimal(8,2) NULL`

- `orden smallint`

- `estado string(20)`


**Relaciones:**

- N — 1 instrumento

- 1 — N opciones_pregunta

- 1 — N respuestas_instrumento


## 58. `opciones_pregunta` — Auxiliar

**PK:** `cod_opcion string(20)`


**FK:**

- `cod_pregunta → preguntas_instrumento.cod_pregunta`


**Atributos:**

- `nombre string(160)`

- `valor string(120) NULL`

- `puntaje decimal(8,2) NULL`

- `orden smallint`

- `estado string(20)`


**Relaciones:**

- N — 1 pregunta


## 59. `aplicaciones_instrumento` — Transaccional

**PK:** `cod_aplicacion string(20)`


**FK:**

- `cod_instrumento → instrumentos.cod_instrumento`

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_atencion → atenciones.cod_atencion NULL`


**Atributos:**

- `fecha_hora datetime`

- `puntaje_total decimal(8,2) NULL`

- `puntaje_maximo decimal(8,2) NULL`

- `clasificacion string(80) NULL`

- `interpretacion text NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N respuestas_instrumento

- N — 1 residente


## 60. `respuestas_instrumento` — Intermedia

**PK:** `cod_respuesta string(20)`


**FK:**

- `cod_aplicacion → aplicaciones_instrumento.cod_aplicacion`

- `cod_pregunta → preguntas_instrumento.cod_pregunta`

- `cod_opcion → opciones_pregunta.cod_opcion NULL`


**Atributos:**

- `valor_numero decimal(12,4) NULL`

- `valor_texto text NULL`

- `valor_logico boolean NULL`

- `puntaje decimal(8,2) NULL`

- `observacion text NULL`


**Relaciones:**

- N — 1 aplicación

- N — 1 pregunta


**Reglas/observaciones:** Validar que la pregunta/opción correspondan al instrumento aplicado.


## 61. `valoraciones_psicologicas` — Transaccional

**PK:** `cod_valoracion_psicologica string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_atencion → atenciones.cod_atencion`


**Atributos:**

- `fecha_hora datetime`

- `estado_animo string(50) NULL`

- `afecto string(50) NULL`

- `ansiedad string(50) NULL`

- `apatia string(50) NULL`

- `percepcion text NULL`

- `conducta text NULL`

- `comunicacion text NULL`

- `interaccion_social text NULL`

- `impresion_cognitiva text NULL`

- `conclusion text NULL`

- `recomendacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente

- N — 1 atención


## 62. `valoraciones_nutricionales` — Transaccional

**PK:** `cod_valoracion_nutricional string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_atencion → atenciones.cod_atencion`

- `cod_medicion → mediciones_antropometricas.cod_medicion NULL`


**Atributos:**

- `fecha_hora datetime`

- `estado_nutricional string(60) NULL`

- `apetito string(40) NULL`

- `deglucion string(40) NULL`

- `riesgo_desnutricion string(40) NULL`

- `necesidad_asistencia string(40) NULL`

- `requerimiento_hidrico decimal(10,2) NULL`

- `restricciones_alimentarias text NULL`

- `conclusion text NULL`

- `recomendacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 63. `valoraciones_funcionales` — Transaccional

**PK:** `cod_valoracion_funcional string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_atencion → atenciones.cod_atencion`


**Atributos:**

- `fecha_hora datetime`

- `marcha string(40) NULL`

- `equilibrio string(40) NULL`

- `traslado string(40) NULL`

- `fuerza_funcional string(40) NULL`

- `resistencia string(40) NULL`

- `alimentacion_autonoma string(30) NULL`

- `bano_autonomo string(30) NULL`

- `vestido_autonomo string(30) NULL`

- `higiene_autonoma string(30) NULL`

- `continencia string(30) NULL`

- `movilidad_autonoma string(30) NULL`

- `necesita_supervision boolean NULL`

- `nivel_dependencia string(40) NULL`

- `conclusion text NULL`

- `recomendacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 64. `seguimientos_pedagogicos` — Transaccional

**PK:** `cod_seguimiento_pedagogico string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal → personal.cod_personal`

- `cod_actividad → actividades.cod_actividad NULL`


**Atributos:**

- `fecha_hora datetime`

- `atencion string(40) NULL`

- `comprension_instrucciones string(40) NULL`

- `ejecucion_tarea string(40) NULL`

- `reconocimiento string(40) NULL`

- `orientacion string(40) NULL`

- `participacion string(40) NULL`

- `interaccion string(40) NULL`

- `cambio_desempeno boolean NULL`

- `observacion text NULL`

- `estado string(20)`


**Relaciones:**

- N — 1 residente


## 65. `actividades` — Transaccional

**PK:** `cod_actividad string(20)`


**FK:**

- `cod_area → areas.cod_area`

- `cod_personal → personal.cod_personal`


**Atributos:**

- `tipo string(60)`

- `nombre string(160)`

- `descripcion text NULL`

- `fecha_hora datetime`

- `duracion_minutos unsignedSmallInteger NULL`

- `lugar string(120) NULL`

- `cupo unsignedSmallInteger NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- 1 — N participantes_actividad


## 66. `participantes_actividad` — Intermedia

**PK:** `cod_participante string(20)`


**FK:**

- `cod_actividad → actividades.cod_actividad`

- `cod_residente → residentes.cod_residente`


**Atributos:**

- `asistencia string(30) NULL`

- `nivel_participacion string(40) NULL`

- `desempeno string(40) NULL`

- `observacion text NULL`


**Relaciones:**

- N — 1 actividad

- N — 1 residente


**Reglas/observaciones:** UNIQUE recomendado (cod_actividad,cod_residente).


## 67. `visitas` — Transaccional

**PK:** `cod_visita string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_contacto → contactos.cod_contacto`

- `cod_usuario_autorizacion → usuarios.cod_usuario`


**Atributos:**

- `fecha_hora_programada datetime NULL`

- `fecha_hora_ingreso datetime NULL`

- `fecha_hora_salida datetime NULL`

- `motivo string(160) NULL`

- `estado string(20)`

- `observacion text NULL`


**Relaciones:**

- N — 1 residente

- N — 1 contacto


## 68. `alertas` — Transaccional

**PK:** `cod_alerta string(20)`


**FK:**

- `cod_residente → residentes.cod_residente`

- `cod_personal_responsable → personal.cod_personal NULL`


**Atributos:**

- `tipo string(60)`

- `prioridad string(20)`

- `modulo string(60) NULL`

- `cod_registro string(20) NULL`

- `titulo string(180)`

- `descripcion text`

- `fecha_hora datetime`

- `fecha_hora_limite datetime NULL`

- `generacion string(30)`

- `estado string(20)`


**Relaciones:**

- 1 — N eventos_alerta

- N — 1 residente


**Reglas/observaciones:** generacion: MANUAL/AUTOMATICA/SISTEMA_EXPERTO. modulo+cod_registro es referencia lógica, no FK polimórfica física.


## 69. `eventos_alerta` — Transaccional

**PK:** `cod_evento_alerta string(20)`


**FK:**

- `cod_alerta → alertas.cod_alerta`

- `cod_usuario → usuarios.cod_usuario`


**Atributos:**

- `tipo_evento string(50)`

- `estado_anterior string(30) NULL`

- `estado_nuevo string(30) NULL`

- `fecha_hora datetime`

- `descripcion text NULL`


**Relaciones:**

- N — 1 alerta

---

# Mapa de relaciones principales

## Flujo institucional

`preadmisiones → admisiones → residentes`

Al formalizar:
- `residentes`;
- `admisiones`;
- `residentes_contactos`;
- `ocupaciones_cama`;
- `historial_estados_residente`;
- `consentimientos`.

## Ubicación

`habitaciones 1:N camas 1:N ocupaciones_cama N:1 residentes`

La cama actual se obtiene de la ocupación activa; nunca desde una columna en `residentes`.

## Profesionales

`usuarios 1:0..1 personal`

`personal + jornadas + areas → asignaciones_personal`

`residentes + jornadas + personal → asignaciones_residente_jornada`

## Expediente clínico

`residentes 1:N atenciones`

Desde `atenciones` pueden originarse:
- `notas_clinicas`;
- `diagnosticos`;
- `estudios_clinicos`;
- `indicaciones_clinicas`;
- `prescripciones`;
- `aplicaciones_instrumento`;
- `valoraciones_psicologicas`;
- `valoraciones_nutricionales`;
- `valoraciones_funcionales`.

## Estudios

`tipos_estudio_clinico 1:N componentes_estudio`

`residentes 1:N estudios_clinicos`

`estudios_clinicos 1:N resultados_estudio`

`componentes_estudio 1:N resultados_estudio`

`estudios_clinicos 1:N informes_estudio`

`estudios_clinicos 1:N documentos_clinicos`

## Seguimiento longitudinal

`residentes 1:N`:
- `signos_vitales`;
- `valoraciones_dolor`;
- `mediciones_antropometricas`;
- `controles_cognitivos`;
- `registros_conductuales`;
- `registros_sueno`;
- `registros_ingesta`;
- `registros_hidratacion`;
- `registros_eliminacion`;
- `registros_movilidad`;
- `heridas`.

`heridas 1:N curaciones_herida`.

## Cuidado

`residentes 1:N planes_cuidado`

`planes_cuidado 1:N intervenciones_cuidado`

`intervenciones_cuidado 1:N programaciones_cuidado`

`intervenciones_cuidado 1:N ejecuciones_cuidado`

## Medicación

`medicamentos 1:N prescripciones`

`residentes 1:N prescripciones`

`prescripciones 1:N horarios_prescripcion`

`prescripciones 1:N administraciones_medicacion`

## Instrumentos

`instrumentos 1:N preguntas_instrumento`

`preguntas_instrumento 1:N opciones_pregunta`

`residentes 1:N aplicaciones_instrumento`

`instrumentos 1:N aplicaciones_instrumento`

`aplicaciones_instrumento 1:N respuestas_instrumento`

`preguntas_instrumento 1:N respuestas_instrumento`

## Familia

`residentes N:M contactos` mediante `residentes_contactos`.

`residentes_contactos 1:N consentimientos` cuando el contacto firma.

`residentes 1:N visitas` y `contactos 1:N visitas`.

## Actividades

`residentes N:M actividades` mediante `participantes_actividad`.

## Alertas

`residentes 1:N alertas`

`alertas 1:N eventos_alerta`

---

# Orden recomendado de migraciones

Para evitar FK hacia tablas aún inexistentes:

1. tablas técnicas requeridas por Laravel/paquetes;
2. `usuarios`;
3. `personal`;
4. `areas`;
5. `turnos`;
6. `contactos`;
7. `residentes`;
8. `habitaciones`;
9. `camas`;
10. `tipos_estudio_clinico`;
11. `medicamentos`;
12. `instrumentos`;
13. `jornadas`;
14. `preadmisiones`;
15. `admisiones`;
16. `residentes_contactos`;
17. `historial_estados_residente`;
18. `ocupaciones_cama`;
19. `documentos`;
20. `consentimientos`;
21. `atenciones`;
22. resto de tablas clínicas dependientes;
23. tablas de seguimiento;
24. planes e intervenciones;
25. medicación;
26. estructura/aplicaciones de instrumentos;
27. valoraciones profesionales;
28. actividades/participantes;
29. visitas;
30. alertas/eventos.

Codex puede agrupar migraciones relacionadas si mantiene exactamente estas entidades, claves y relaciones y no introduce dependencias circulares.

---

# Políticas generales de FK

- PK/FK con el mismo tipo y longitud.
- Indexar todas las FK.
- No usar `cascadeOnDelete()` para historia clínica o transacciones sensibles.
- Preferir `restrictOnDelete()` / comportamiento equivalente.
- Las anulaciones se hacen con estado.
- Los `NULL` de FK solo se permiten donde este documento los declara opcionales.
- No sustituir FK por cadenas de texto para “simplificar”.

---

# Índices prioritarios

Además de PK, UNIQUE y FK:

- `residentes(numero_documento)`;
- `preadmisiones(numero_documento, estado)`;
- `ocupaciones_cama(cod_cama, estado)`;
- `ocupaciones_cama(cod_residente, estado)`;
- `atenciones(cod_residente, fecha_hora)`;
- `signos_vitales(cod_residente, fecha_hora)`;
- `controles_cognitivos(cod_residente, fecha_hora)`;
- `registros_conductuales(cod_residente, fecha_hora)`;
- `registros_ingesta(cod_residente, fecha_hora)`;
- `registros_hidratacion(cod_residente, fecha_hora)`;
- `registros_movilidad(cod_residente, fecha_hora)`;
- `estudios_clinicos(cod_residente, fecha_solicitud)`;
- `prescripciones(cod_residente, estado)`;
- `administraciones_medicacion(cod_residente, fecha_hora_programada)`;
- `aplicaciones_instrumento(cod_residente, fecha_hora)`;
- `alertas(cod_residente, estado, fecha_hora)`.

---

# Invariantes que deben probarse

1. No crear residente sin admisión formal.
2. No asignar una cama ocupada.
3. No dejar dos ocupaciones activas para un residente.
4. No administrar una prescripción de otro residente.
5. No responder una pregunta de otro instrumento.
6. No usar un contacto de otro residente para un consentimiento.
7. No duplicar participante en una actividad.
8. No duplicar un resultado de componente para el mismo estudio sin regla explícita.
9. No permitir borrado físico ordinario de historia clínica.
10. `SUPERADMINISTRADOR` puede leer todo.
11. `ADMINISTRADOR` no adquiere escritura clínica.
12. `ENFERMEROS` no prescribe.
13. `FAMILIAR` solo ve información autorizada del residente vinculado.
14. `VOLUNTARIO` no existe en el alcance actual.

---

# Nota de congelamiento

**No modificar esta estructura sin consulta y aprobación previa.**

Si una necesidad nueva no cabe en el modelo:
- documentar el caso;
- proponer alternativas;
- evaluar 3FN y relaciones;
- solicitar autorización;
- crear una migración nueva solo después de aprobar el cambio.

