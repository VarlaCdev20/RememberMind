# Conservación experta V2: nueve tablas y reutilización de D-137

Estado: **APROBADA POR LA PROPIETARIA**, 17/10/2026.

Instrucción explícita: «REALIZA ESTA INCORPORACION Y LUEGO IREMOS REDUCIENDO LAS
TABLAS», referida al inventario de 23 expertas existentes + 9 nuevas = 32
expertas, junto con 73 operativas = **105 tablas de negocio**. Las tablas de
framework, Spatie y secuencias quedan fuera del contador de negocio.

La instrucción posterior aprueba la alternativa de nueve y sus ampliaciones de
D-137 descritas en `app/SistemaExperto/Infrastructure/PROPUESTA_REUTILIZACION_D137_V2.md`.
La alternativa anterior de dieciséis no se implementa. La reducción futura será
una transición aparte, preservando datos y contratos; esta entrega mantiene las
nueve responsabilidades y no elimina tablas.

## Alcance

Conservar y recuperar `MemoriaTrabajo`, `VectorCognitivo` y
`ResultadoClasificacionDcl`, con soportes originales, subcomponentes, perfil
funcional, C01–C05 y reglas aplicadas. Incluye valores extraídos de fuentes
institucionales autorizadas y referencias sintéticas declaradas. La ejecución
continúa restringida a testing, destinos desechables y casos SIM-/SINTETICO-.
No activa clasificaciones asistenciales, providers, permisos, pantallas o rutas.

Actualización posterior autorizada en esta tarea: la capacidad cerrada de
[simulación local](../sistema-experto/SIMULACION_LOCAL_2026_10_17.md) permite
conservar y consultar cinco casos ficticios en `remembermind_dev`. La extensión
estructural no cambia; la excepción no habilita ejecución o publicación
clínica, y el comportamiento general continúa restringido a testing.

La memoria no duplica registros clínicos completos: conserva exclusivamente el
valor utilizado, su tipo y su procedencia. La fuente operativa IADL sigue siendo
`controles_funcionales_iadl`, ya incluida en las 73 operativas.

## Convenciones físicas

- Nuevas PK `string(20)` con el generador correlativo existente. Cada FK tiene
  índice, tipo coincidente y borrado restrictivo. Sin cascadas, JSON, BLOB,
  timestamps automáticos o borrado ordinario de historia.
- IDs originales Domain en columnas separadas `text`; nunca se truncan para
  hacerlos coincidir con una PK física. Residente y versión referencian registros
  existentes; el escritor no crea usuarios, residentes, versiones o mapeos.
- `orden` y `orden_conservacion`: entero no negativo, empezando por cero.
  El lector exige continuidad y rechaza duplicados o referencias ajenas.
- Fecha original `F(p)`: `p_utc datetime(6)`, `p_zona string(80)`,
  `p_offset integer`. Se conserva instante, microsegundos, zona y offset.
- Escalar `S(p)`: `p_tipo string(8)` cerrado a NULL/STRING/INT/FLOAT/BOOL;
  `p_texto text?`, `p_entero bigint?`, `p_real_ieee754 char(16)?`,
  `p_logico boolean?`. Exactamente una columna activa, o ninguna para NULL.
  FLOAT almacena bits finitos IEEE-754; no altera decimales originales STRING.
  Se rechazan texto no UTF-8, bytes NUL y valores fuera de representación.

## Ampliaciones de tablas existentes

| Tabla | Columnas o contrato añadido |
|---|---|
| `evaluaciones_expertas` | `id_evaluacion_dominio text? UNIQUE`, `version_conservacion string(30)?`, `huella_memoria_cognicion char(64)?`, `F(fecha_corte)` nullable para historia anterior. |
| `evaluacion_criterios` | `tipo_registro string(30)` default D137; `version_conservacion string(30)?`; `cod_criterio_padre string(20)?` self-FK restrictiva; `orden_conservacion integer?`; `estado_evaluabilidad` admite NULL exclusivamente en los tipos SUBCOMPONENTE/DCL del contrato nuevo. |
| `resultados_criterio` | `justificacion_original text?`, exigida por el escritor/lector nuevo; no se inventa para registros anteriores. |
| `evidencias_criterio_evaluacion` | `orden_conservacion integer?`; rol nuevo `SOPORTE_EXPERIMENTAL`, participación `PRUEBA_TECNICA`. Los roles anteriores se conservan. |

`tipo_registro` cerrado: D137, DOMINIO, SUBCOMPONENTE, DCL. D137 mantiene
evaluabilidad obligatoria y carece de contrato/padre nuevos. DOMINIO tiene
evaluabilidad y no padre; SUBCOMPONENTE tiene padre dominio de la misma
evaluación y evaluabilidad NULL; DCL no tiene padre ni evaluabilidad. Los tipos
nuevos exigen `ORION-SNAP-2` y orden explícito. CHECK PostgreSQL / triggers
SQLite rechazan también expresiones indeterminadas por NULL. La pertenencia y
semántica padre/hijo se validan al escribir y reconstruir.

## Diccionario de las nueve tablas nuevas

Todas las columnas son obligatorias salvo las marcadas `?`. Las PK se enumeran
en el catálogo de códigos. Las referencias E/H/F/C/R siguientes son FK reales:
E = `cod_evaluacion_experta`; H = `cod_hecho_instantanea`;
F = `cod_funcionalidad_instantanea`; C = `cod_clasificacion_dcl_instantanea`;
R = `cod_regla_aplicada_dcl`.

| Tabla | PK | Columnas además de la PK |
|---|---|---|
| `hechos_instantanea_experta` | `cod_hecho_instantanea` | E; `id_hecho_dominio`, `fuente_id_dominio`, `origen_id_dominio`, `episodio_id_dominio`, `concepto_dominio` text; `F(fecha_hecho)`; `S(valor)`; orden; `tipo_origen string(40)`; `cod_evidencia_evaluacion?` FK; `cod_atencion?` FK; `cod_actividad?` FK. UNIQUE(E,id_hecho_dominio), UNIQUE(E,orden). |
| `evidencias_cognitivas_instantanea` | `cod_evidencia_cognitiva_instantanea` | H UNIQUE; `dominio string(20)`; `subcomponente string(30)?`; `hallazgo string(50)`; `instrumento_id_dominio text?`; `justificacion text`; orden; siete `referencia_{instrumento,edicion,subprueba,norma,idioma,poblacion,revisor} text?`. |
| `soportes_sinteticos_criterio` | `cod_soporte_sintetico` | `cod_evaluacion_criterio` FK; H; orden. UNIQUE(criterio,H), UNIQUE(criterio,orden). |
| `funcionalidad_instantanea_experta` | `cod_funcionalidad_instantanea` | E UNIQUE; `independencia_relativa string(20)`; `revisor_sintetico_id text?`; `justificacion text`. |
| `evidencias_iadl_instantanea` | `cod_evidencia_iadl_instantanea` | F; H; `actividad_id_dominio text`; `estado string(30)`; `oportunidad string(40)`; `nivel_previo string(30)?`; `causa_ayuda text?`; `atribucion_cognitiva_simulada boolean?`; `revisor_sintetico_id text?`; orden. UNIQUE(F,H), UNIQUE(F,orden). |
| `clasificaciones_dcl_instantanea` | `cod_clasificacion_dcl_instantanea` | E UNIQUE; F UNIQUE; `version_contrato string(80)`; `diferencial string(40)`; `patron_candidato string(10)?`; `justificacion text`; `uso string(50)`; `huella_clasificacion char(64)`. |
| `motivos_dcl_instantanea` | `cod_motivo_dcl_instantanea` | C; `motivo string(80)`; orden. UNIQUE(C,motivo), UNIQUE(C,orden). |
| `reglas_aplicadas_dcl_instantanea` | `cod_regla_aplicada_dcl` | C; `cod_regla_experta?` FK; `id_regla_dominio text`; `fuente text`; `habilitada boolean`; `concepto_consecuencia text`; `S(consecuencia)`; orden. UNIQUE(C,id_regla_dominio), UNIQUE(C,orden). |
| `condiciones_regla_instantanea` | `cod_condicion_instantanea` | R; `cod_condicion_padre?` self-FK; `operador string(20)`; `variable text?`; `S(literal)`; orden. |

Forma de origen cerrada: D137, REFERENCIA_EXTERNA_SINTETICA,
REFERENCIA_CRITERIO_SINTETICA, REFERENCIA_IADL_SINTETICA,
REVISION_TECNICA_SINTETICA. D137 exige evidencia de la misma evaluación,
versión/fuente/registro coherentes y episodio operativo real. La escritura
contrasta el valor con el extractor original. Las formas sintéticas no tienen
FK institucionales de conveniencia. Las revisiones técnicas se limitan a
procedencia de evidencia existente o a los seis CAMPOS_CONTEXTO vigentes de
EvaluadorConfusoresService, con valor boolean/null.

El vocabulario de resultados es el de los enums Domain vigentes, sin valores
clínicos nuevos. La clasificación conserva `SIMULACION_ACADEMICA_NO_CLINICA`.
El AST conserva IGUAL_A/DISTINTO_DE/Y/O/NO, una raíz, orden, ausencia de ciclos
y profundidad máxima 32. Las reglas D-137 se comparan con el mapper original
antes de conservarlas; las otras exigen ID SIM explícito.

## Catálogo experimental y compatibilidad

`CatalogoConservacionExperta::preparar(conexion, version, usuario)` es una carga
técnica explícita, idempotente y segregada. Reutiliza las cuatro tablas de
catálogo existentes con nodos `SNAP:{DOMINIO|SUBCOMPONENTE|DCL}:{codigo}`,
dominios `SNAP:{tipo}` y valores `EXPERIMENTAL`. No altera nodos o catálogos G09
ni crea una versión/usuario como fallback. El escritor exige carga previa y
no la realiza automáticamente. No se añade un seeder de activación global.

Los lectores anteriores continúan por su contrato D137; la consulta de
historial G09 excluye evaluaciones exclusivamente V2 y conserva visibles los
criterios D-137 anteriores cuando se anexa una instantánea a su cabecera.
El repositorio de
evaluaciones selecciona la reconstrucción nueva por contrato; no recurre al
Closure anterior si una instantánea nueva está incompleta.

## Escritura, recuperación y reversión

- Guardar memoria/cognición es una transacción; una clasificación requiere esa
  cognición íntegra previamente conservada y tiene su propia transacción.
- Reintentos idénticos no escriben. Misma identidad con contenido diferente se
  rechaza. La versión se bloquea para serializar la primera creación y la
  cabecera para anexar una clasificación. No se afirma concurrencia por una
  prueba secuencial.
- La cabecera nueva representa la evaluación solicitada, con FK al residente
  y versión existentes. Si ya existe una cabecera técnica D-137 coincidente,
  se adjuntan una sola vez las columnas nuevas, sin modificar sus datos
  anteriores. La transacción revierte también esa incorporación si falla.
- Se reconstruye cada hecho una sola vez y se comparte entre memorias,
  evidencias y soportes. Las huellas verifican contenido, tipos y orden; no
  reemplazan los datos ni constituyen firma criptográfica frente a un actor
  con acceso de escritura completo a la DB.
- La recuperación usa los valores conservados; no reextrae fuentes mutables.
  Las FK originales se contrastan sin interpretar de nuevo el valor clínico.
- `down()` rechaza cualquier instantánea, criterio nuevo o fila suplementaria
  existente antes de mutar. Con la extensión vacía permite retirar y volver a
  aplicar preservando D-137 anterior. SQLite difiere la comprobación de FK
  durante la reconstrucción de tablas y exige `foreign_key_check` antes de
  confirmar. No se elimina historia para conseguir una reversión.

Migración: `database/migrations/2026_10_17_200000_incorporate_conservacion_experta_v2.php`.
Models: los nueve nombres de `App\Models` derivados del diccionario, todos
heredan ModeloExperto y sus guardas de inmutabilidad.

La incorporación es código de migración y persistencia validado en pruebas;
no implica haber aplicado migraciones a una base institucional o producción.
La evidencia de ejecución final se registra en
`app/SistemaExperto/VERIFICACION_FASES.md`.
