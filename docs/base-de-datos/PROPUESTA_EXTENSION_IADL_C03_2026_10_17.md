---
title: "Extensión IADL-C03 — captura longitudinal de funcionalidad instrumental"
status: PROPOSED_IMPLEMENTED_IN_DRAFT_PR
version: "V2.3-propuesta"
last_reviewed: 2026-10-17
owner: RememberMind
source_of_truth: false
runtime_verified: false
verification_scope: "IMPLEMENTACION_EN_RAMA_SEPARADA_PENDIENTE_REVISION"
---

# Solicitud y decisión de cambio: IADL-C03 (17/10/2026)

La solicitud expresa del responsable del proyecto es **implementar una captura de funcionalidad instrumental** mediante una nueva tabla operativa `controles_funcionales_iadl`. Esta petición autoriza desarrollar y presentar el cambio para revisión, **no equivale a aprobar una migración en producción, ni el algoritmo C03 o diagnóstico de DCL**.

## Baseline rector y problema

- `REMEMBERMIND_BDD_BASELINE_CONGELADO.md` (V2.1, 70 tablas) y `docs/base-de-datos/README.md` (71 incluyendo extensión V2.2 ya aprobada) requieren **control de cambios explícito**.
- `valoraciones_funcionales` registra principalmente marcha, movilidad, alimentación, higiene y otras actividades básicas (BADL); `necesita_supervision` no identifica la tarea IADL ni el motivo de ayuda.
- El [Documento 01 DCL](https://docs.google.com/document/d/1HUoV_3sWPO9F07zpD3UUkyamxBKM-1CuazZOgIdp9VU/edit) y el [Contrato 03 Petersen](https://docs.google.com/document/d/108-74YgGmw3JmA3Lba_bmwMcDeR39oVKIkamtyqJLgw/edit) exigen para **DCL-C03** valorar la **independencia funcional relativamente conservada**, el nivel previo, las IADL, las rutinas institucionales y las causas físicas/sensoriales de ayuda; no se infiere desde Barthel/Katz ni de tareas que no se ofrecieron.
- **La independencia funcional no es una regla binaria universal:** un residente puede recibir asistencia habitual por logística institucional sin haber perdido la capacidad; una dificultad observada tampoco diagnostica demencia.

## Alternativas contrastadas

1. Ampliar `valoraciones_funcionales`: rechazado como primera solución por alterar la tabla congelada e introducir campos de tareas heterogéneas BADL/IADL.
2. Usar texto libre en `conclusion` o `observacion`: rechazado por no permitir atribuir tarea, oportunidad, ayuda, causa y fuente con integridad y trazabilidad.
3. Reutilizar exclusivamente `aplicaciones_instrumento`: válido **si existe** una escala administrada, versión autorizada y preguntas codificadas, pero no documenta por sí solo las oportunidades reales del régimen institucional. Mantener el enlace `cod_atencion` opcional; no crear respuestas duplicadas o escala «Lawton» ficticia.
4. **Extensión tipada y longitudinal `controles_funcionales_iadl` (propuesta):** una fila por residente, tarea y momento, sin EAV/JSON, correcciones append-only y catálogos cerrados. La nueva tabla sería la **número 72** en la rama tras la migración, mientras las 71 existentes permanecen intactas.

## Diccionario físico propuesto

| Campo | Tipo | Regla |
|---|---|---|
| `cod_control_iadl` | `string(20)` PK | Identificador de observación, autogenerado |
| `cod_residente` | `string(20)` FK no nula | `residentes.cod_residente`; residente admitido |
| `cod_personal` | `string(20)` FK no nula | Autor clínico en `personal` |
| `cod_usuario_registro` | `string(20)` FK no nula | Cuenta técnica/autorizada en `usuarios` |
| `cod_atencion` | `string(20)` FK opcional | Atención del mismo residente (validación de aplicación) |
| `cod_control_anterior` | `string(20)` self-FK opcional | Corrección sin borrar/sobrescribir fila previa |
| `fecha_hora` | datetime no nulo | Momento de tarea observada o reportada |
| `actividad` | `string(30)` cerrado | MEDICACION, PERTENENCIAS, RUTINAS, CITAS, COMUNICACION, FINANZAS, COMPRAS, TRANSPORTE |
| `oportunidad` | `string(25)` cerrado | OBSERVADA, REFERIDA, SIN_OPORTUNIDAD, NO_HABITUAL |
| `desempeno` | `string(25)` cerrado | INDEPENDIENTE, CON_APOYO, NO_EVALUABLE |
| `tipo_ayuda` | `string(30)` cerrado | NINGUNA, RECORDATORIO, SUPERVISION, SUSTITUCION, NO_APLICA |
| `causa_ayuda` | `string(30)` cerrado | NINGUNA, COGNITIVA, FISICA, SENSORIAL, INSTITUCIONAL, MIXTA, NO_DETERMINADA, NO_APLICA |
| `nivel_previo` | `string(25)` cerrado | INDEPENDIENTE, CON_APOYO, NO_HABITUAL, DESCONOCIDO |
| `fuente` | `string(25)` cerrado | OBSERVACION_DIRECTA, RESIDENTE, FAMILIAR, EQUIPO_CUIDADOS |
| `estado` | `string(20)` | REGISTRADO o RECTIFICACION (append-only) |
| `observacion` | TEXT opcional | Contexto narrativo; no sirve para derivar categorías sin captura estructurada |
| `registrado_en` | datetime no nulo | Momento técnico de persistencia, separado de `fecha_hora` |

FK restrictivas, índices específicos por residente/fecha, residente/tarea/fecha y FKs; ningún borrado en cascada. **No guardar nombres de familiares ni otra identidad duplicada en texto estructurado.** La observación libre está sujeta a controles de acceso institucional y no debe enviarse a un servicio externo.

## Invariantes de captura y explicación

- `SIN_OPORTUNIDAD`/`NO_HABITUAL` ⇒ `NO_EVALUABLE` + ayuda/causa `NO_APLICA`; **no implica dependencia**.
- `INDEPENDIENTE` ⇒ ayuda y causa `NINGUNA`.
- `CON_APOYO` ⇒ tipo y causa específicos + descripción contextual. La causa puede ser física, institucional o no determinada; no se supone automáticamente cognitiva.
- `OBSERVADA` exige fuente directa; `REFERIDA` conserva quién proporcionó el relato.
- Toda fila tiene autoría personal y usuario, residente admitido y fecha no futura; `cod_atencion` opcional debe pertenecer al mismo residente.
- Correcciones son filas nuevas con self-FK; el historial queda disponible y el resumen excluye versiones reemplazadas.
- `AnalizadorIadlC03` presenta recuentos de oportunidades, ayuda e independencia por tarea, **pero siempre devuelve `criterio_dcl_c03=U`**. Un profesional debe valorar preservación funcional relativa, comparación con el nivel previo y causas no cognitivas antes de una futura decisión clínica C03.

## Acceso y seguridad

- Módulo responsable **Valoraciones** (Dominio, Aplicación, Presentación) y modelo Eloquent de compatibilidad.
- Permisos específicos `controles_funcionales_iadl.ver`, `.crear` y `.rectificar`; acceso desde expediente con política `ResidentePolicy` y middleware `AutorizarResidenteVinculado`.
- Médico, Enfermería, Psicología y Fisioterapia pueden documentar/consultar según su asignación de permisos; Superadmin tiene lectura pero **la escritura exige `personal` activo**. Familiar/administración no obtienen lectura clínica IADL por defecto.
- No exportar controles IADL a vistas familiares, informes administrativos o el clasificador Petersen clínico sin nuevas decisiones.

## Entrega y validación

- Migración `2026_10_17_190000_create_controles_funcionales_iadl_table.php`.
- Modelo `ControlFuncionalIadl`, acción `RegistrarControlIadl`, analizador `AnalizadorIadlC03`, pantalla y rutas protegidas.
- Test `ControlesFuncionalesIadlTest` + inventario ampliado a 72 exclusivamente en la rama de propuesta.
- Ejecutar en **SQLite in-memory** y CI PostgreSQL desechable; nunca ejecutar `migrate:fresh` contra entornos con datos institucionales.
- Riesgo abierto: validación de autorización técnica/científica de C03, procedencia de informantes y asociación a escalas tipo Lawton–Brody **requiere edición y normas identificadas, sin inventar puntuaciones**.

**Estado actual de la decisión:** cambio implementado para revisión en PR independiente de [PR #15 (O.R.I.O.N. sintético)](https://github.com/VarlaCdev20/RememberMind/pull/15). **No fusionar o desplegar sin revisar el esquema, permisos, pruebas y autorización de operación.**


## Contrato de lectura con O.R.I.O.N. (ApoyoDecision)

`ConsultarEvidenciaIadlC03` ofrece consulta autenticada del historial del residente hasta una fecha de corte, retorna IDs de los registros como procedencia y ejecuta la síntesis descriptiva `AnalizadorIadlC03`. La interfaz de salida exige `criterio_dcl_c03=U`, `puede_emitir_clasificacion_dcl=false` y registra de forma explícita los límites de la evidencia. Verifica `ResidentePolicy` más `controles_funcionales_iadl.ver` antes de leer registros sensibles.

Esta capa **sí conecta la tabla a la consulta de ApoyoDecision**, pero **no** conecta automáticamente con el clasificador clínico de Petersen del PR #15 ni transforma observaciones, supuestos de causalidad o ausencia de datos en `S`. La revisión C03 será objeto de una decisión técnica/científica posterior. Los ensayos de corte temporal, procedencia y privacidad forman parte de `ControlesFuncionalesIadlTest`.
