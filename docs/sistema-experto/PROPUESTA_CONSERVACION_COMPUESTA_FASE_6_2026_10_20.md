# Propuesta de conservación compuesta — fase 6

Fecha: 20/10/2026. Estado: **PROPUESTA NO APROBADA. Sin DDL, migración,
instalación ni modificación del inventario vigente.** Esta es una propuesta de
diseño; su autor no emite un PASS de auditoría sobre su propio diseño.

## Decisión concreta propuesta y límites

Conservar **entradas completas, conocimiento de definición sellado, estados de
gobierno observados y release recuperable**, y reconstruir las salidas con ese
release. No añadir tablas de normalizaciones, métricas, hallazgos calculados ni
motivos puramente derivados. El checksum de la salida permite detectar una
reconstrucción distinta; no sustituye a las entradas necesarias para producirla.

La alternativa descrita aquí tiene **25 relaciones nuevas**, todas contadas
como expertas para no ocultar coste en una categoría técnica; **7 columnas
nuevas en 2 tablas existentes**, más constraints y protecciones de colección.
Si se aprobara íntegra, el inventario pasaría de **75 operativas + 33 expertas =
108** a **75 operativas + 58 expertas = 133**. No se afirma un mínimo
matemático. Se conservan las nueve relaciones aprobadas el 17/10; ninguna se
elimina, fusiona o reinterpreta. El número elevado resulta de separar conjuntos
independientes, conservar configuración exacta y cerrar las colecciones.

Esta propuesta **no habilita guardar todavía**. El cotejo estático del DTO
compuesto y la revisión independiente del diseño se cerraron el 20/10 con PASS
acotado para presentar la decisión estructural. Las preparaciones
`InstantaneaPetersen` e `InstantaneaEvaluacionInstitucional` están implementadas
en memoria; su código no es aprobación institucional ni prueba de persistencia.

La lectura posterior y la revisión MED/PSI usan la evaluación reconstruida. La
salida completa se obtiene ejecutando el release retenido; esta alternativa no
promete consultar cada z, intervalo o condición mediante SQL sin ejecutar ese
release. Si se requiere esa consulta analítica adicional, debe diseñarse y
aprobarse aparte, sin convertir un cache en fuente histórica.

## Autoridad y cotejo realizado

- [Baseline rector](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md),
  incluyendo sus decisiones posteriores: inventario actual 108.
- [Diccionario operativo](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md),
  secciones 19, 21, 22, 34 y 57–62: fuentes originales, tipos y relaciones.
- [Conservación aprobada del 17/10](../base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md):
  `ORION-SNAP-2`, nueve relaciones, tipos escalares y fechas exactas.
- [Revisiones aprobadas del 17/10](../base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md):
  cadenas independientes de Medicina y Psicología.
- [Observaciones, 18/10](PROPUESTA_CONSERVACION_OBSERVACIONAL_V1.md): dos FK
  añadidas a hechos; no autoriza un nuevo origen instrumental universal.
- [Gobierno, 18/10](PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md): cinco campos de
  primera revisión en reglas; esa revisión puede cambiar de pendiente a
  aprobada/rechazada en la misma fila.
- [Originales, 19/10](PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md) y
  [captura Petersen aprobada](PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md):
  procedencia y dos relaciones inmutables, sin conservación completa.
- [Análisis anterior](CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md): sus
  nueve candidatas no son una autorización ni un mínimo demostrado.
- Implementación cotejada: migraciones `2026_10_08_120001..120023`,
  `2026_10_17_200000`, `2026_10_18_001000`, `2026_10_18_002000` y
  `2026_10_19_020000`; `CargadorConocimiento`, `ContratosConocimiento`,
  `GestionarReglasExpertas`, `CapturasInstrumentalesResidente`,
  `ConsultarPetersenInstitucional`, `CapturaInstrumentalCanonica`,
  `InstantaneaInstrumental`, `ReleaseMotorInstrumental` y `CodecInstantanea`.

Las migraciones son evidencia de lo implementado. La estructura aprobada sigue
procediendo de las decisiones anteriores. No se consultaron filas de la base
local ni datos clínicos para redactar esta propuesta; no se ejecutaron pruebas.

## Reutilización exacta y brechas

| Responsabilidad | Reutilización | Brecha y resolución propuesta |
|---|---|---|
| Identidad, residente, solicitante y corte | `evaluaciones_expertas`, incluida `fecha_corte_utc/zona/offset` e identidad Domain única | Añadir ejecutor, nacimiento usado, release y huella compuesta. Solicitante no equivale a ejecutor. |
| Dos familias reales | `versiones_modelo_experto` y sus 12 tablas de definición | Cabecera cognitiva y gobierno Petersen distintos mediante membresías; no crear una versión común ficticia. |
| Definición histórica | PK/FK y contenido existente de esas 12 tablas | Hoy Model/Builder prohíben escritura ordinaria, pero no hay sello SQL general que impida INSERT tardío o SQL directo. Proponer sello opt-in completo y guardar aparte los estados observados. |
| Primera revisión de cada regla | Cinco columnas aprobadas de `reglas_expertas` | Copiar su estado y firma **observados** por evaluación; una firma posterior no debe alterar el replay. |
| Juicios Petersen y soportes originales | `valoraciones_petersen_originales` y `soportes_valoracion_petersen` por FK | No copiar juicio, justificación ni comprobaciones; fijar membresía y soportes exactos de esta ejecución. Prohibir UPDATE/DELETE no cierra por sí solo futuras incorporaciones a la colección. |
| Evidencias y trazas D-137 compatibles | Tablas existentes de criterios, resultados, evaluaciones de reglas, condiciones y soportes | Mantienen sus consumidores. No hacer pasar el DTO compuesto por `ORION-SNAP-2`, ni poblar evaluabilidad ficticia para usar una columna. En esta alternativa las trazas nuevas se regeneran; no se escribe una segunda copia de las antiguas. |
| Revisión de la evaluación | `revisiones_evaluacion_experta` | Reutilizar sus dos cadenas después de guardar y verificar íntegramente el contrato nuevo. No requiere otra tabla de firmas. |
| Hechos/simulación V2 | Nueve relaciones aprobadas y `AlmacenInstantaneas` | Conservan su contrato y guardas. No usarlas como repositorio campo/valor de atributos SQL ni quitar restricciones SIM para aparentar conservación institucional. |
| Instrumentos y respuestas | FK de las tablas operativas | Hace falta conservar atributos observados, incluidos rechazados, NULL, cero, false y preguntas seleccionadas sin respuesta. |
| Perfiles y contratos | Registro de métodos del release y estructura actual de configuración | El método no identifica las PK locales ni la identidad declarada incorrecta. Conservar perfiles/selectores y contratos resueltos en columnas cerradas. |
| Contexto y documentos | FK originales privadas | Copiar solo lo consumido y la constancia de verificación en ese instante; el replay no vuelve a comprobar bytes ni permisos históricos. |
| Software | `ARTEFACTO-MOTOR-2` verificable | Falta registro retenido y FK de evaluación. El ZIP sigue conteniendo solo código, lock y red documental, sin PHI, configuración local ni paquetes SQL como atajo de persistencia. |

## Convenciones físicas completas

Las abreviaturas siguientes son expansiones exactas del diccionario, no campos
dinámicos:

- `K` = `string(20)`. Todas las PK nuevas son K, no incrementales, con el
  generador correlativo existente. Los prefijos candidatos se enumeran abajo;
  incorporarlos al catálogo también requiere aprobación.
- `E` = columna K requerida `cod_evaluacion_experta`, FK a
  `evaluaciones_expertas.cod_evaluacion_experta`.
- Toda referencia `FK tabla.columna` de las tablas siguientes es una columna K,
  indexada, `ON DELETE RESTRICT`, `ON UPDATE NO ACTION`, salvo indicación de
  nulabilidad. No hay cascadas. La PK destino se identifica expresamente.
- `ord` = `orden integer NOT NULL CHECK (orden >= 0)`, desde cero. Una lista
  conserva su orden; un mapa se reconstruye por clave. Para cada propietario y
  lista se exige continuidad al sellar y reconstruir.
- `F(p)` = `p_utc datetime(6)`, `p_zona string(80)`, `p_offset integer`; los tres
  obligatorios o los tres NULL cuando se indique `F(p)?`. Offset en segundos,
  entre -50400 y 50400; zona válida y coherencia de instante/zona/offset
  comprobadas por el codec. No se sustituye una fecha sin hora por medianoche.
- `S(p)` = cinco columnas **fijas**: `p_tipo string(8)` obligatorio,
  `p_texto text?`, `p_entero bigint?`, `p_real_ieee754 char(16)?`,
  `p_logico boolean?`. Tipo cerrado NULL/STRING/INT/FLOAT/BOOL; CHECK activa
  exactamente su columna, o ninguna para NULL. FLOAT conserva bits IEEE-754
  finitos. No es EAV: los únicos prefijos permitidos están enumerados por tabla.
- `D8` = decimal original canónico de `(8,2)` almacenado en `string(12)`;
  `D12` = decimal original canónico de `(12,4)` en `string(16)`. CHECK léxico,
  rango y escala exactos; representación sin signo de cero negativo. Se guardan
  como texto **numérico de columna fija**, no como float, para impedir que
  SQLite pierda escala; se reconstruyen las cadenas canónicas que exige V3.
- `H` = `char(64)` con CHECK SHA-256 hexadecimal minúsculo. Huella es integridad,
  no firma profesional ni autenticación contra quien administra DB y archivos.
- `?` significa NULL permitido; las otras columnas son NOT NULL. No hay
  `created_at`, `updated_at`, `deleted_at`, JSON, arrays SQL, serialización PHP,
  BLOB clínico ni listas concatenadas en texto.
- Los `*_sql_texto` son representaciones **temporales observadas de columnas
  concretas** que el DTO V3 actual conserva como string. Se acompañan de su
  fecha tipada cuando se usa como instante. Se comprueba que ambas representan
  lo mismo; no se normaliza silenciosamente la cadena histórica ni se abre un
  almacén de atributos arbitrarios.
- Las referencias resueltas a snapshots deberán pertenecer a la misma E.
  Cuando un hijo no lleva E porque depende de su padre, un trigger valida la
  igualdad recorriendo ese padre; no se presume que una FK simple la pruebe.
  Evitar repetir E en todos los hijos conserva las dependencias funcionales.

### Ampliaciones: 7 columnas, dos tablas existentes

| Tabla | Columna | Tipo / regla |
|---|---|---|
| `evaluaciones_expertas` | `cod_usuario_ejecucion` | K?, FK `usuarios.cod_usuario`. Requerida para el contrato compuesto; cuenta real de sesión. |
| idem | `cod_release_motor` | K?, FK `releases_motor_experto.cod_release_motor`. Requerida al finalizar el contrato compuesto. |
| idem | `fecha_nacimiento_original` | date?. NULL conserva desconocimiento; no se completa con nacimiento actual. |
| idem | `huella_evaluacion_compuesta` | H?. Requerida al finalizar el contrato nuevo; no reutilizar el significado de `huella_memoria_cognicion` V2. |
| `versiones_modelo_experto` | `huella_definicion_sellada` | H?. Incluye conjunto exacto de las 12 tablas y cabecera estable, excluyendo solo las mutaciones autorizadas indicadas abajo. |
| idem | `fecha_hora_sellado` | datetime(6)?. Instante técnico del primer sello. |
| idem | `cod_usuario_sellado` | K?, FK `usuarios.cod_usuario`. Actor real que solicita la conservación. |

Las tres columnas de sello serán todas NULL o todas no NULL. No se alteran
filas previas para inventar un sello histórico. Un sellado posterior registra su
instante verdadero tras contrastar el contenido, sin afirmar que ese contenido
era igual antes. La cabecera existente conserva `cod_version_modelo` para la
familia cognitiva y `version_conservacion` para el nuevo contrato; dos filas de
la relación 2 identifican las ramas reales. Las filas antiguas mantienen la
nulabilidad y lectores anteriores.

Para la nueva conservación, `id_evaluacion_dominio` existente será la identidad
de solicitud generada por el servidor y única; las identidades de contexto de
cada rama se guardan en la relación 2. No se deduplica por fecha o semejanza de
resultados. El CHECK del contrato exige ejecutor, release, corte completo,
huella y ambas membresías antes de finalizar. La comprobación de colecciones
requiere trigger de cierre, no un CHECK de fila aislado.

## Diccionario de las 25 relaciones propuestas

### 1. `releases_motor_experto` — software retenido

Grano: un artefacto de software verificado, no una evaluación. PK
`cod_release_motor` (prefijo candidato `RME`). Columnas:

| Columna | Tipo / referencia |
|---|---|
| `contrato_artefacto` | string(30), `ARTEFACTO-MOTOR-2` en escrituras nuevas |
| `revision_base` | char(40), SHA Git base hexadecimal; no afirma árbol limpio |
| `huella_manifiesto` | H, UNIQUE |
| `huella_archivo` | H |
| `ruta_privada` | string(255), UNIQUE; ruta relativa al storage privado, sin traversal |
| `tamano_bytes` | bigint, CHECK > 0 |
| `php_requerido` | string(30), requisito del manifiesto |
| `version_php_preparacion` | string(30), PHP real observado |
| `familia_sistema` | string(30), familia de sistema observada |
| `bits_entero` | smallint, CHECK IN (32,64) |
| `fecha_hora_retencion` | datetime(6), instante técnico de registro retenido |
| `cod_usuario_registro` | K, FK `usuarios.cod_usuario` |

El manifiesto por archivo se recupera del ZIP y se verifica contra
`huella_manifiesto`; no se copia a una columna JSON ni se crea otra tabla por
archivo. La red conceptual exacta procede del archivo conservado dentro del
release y se coteja con la huella incluida en las entradas. El lock identifica
dependencias; no significa que se hayan retenido binarios PHP. Un entorno de
replay incompatible falla explícitamente.

### 2. `conocimientos_evaluacion_experta` — componentes reales

Grano: E + rama, UNIQUE `(cod_evaluacion_experta,rama)`. PK
`cod_conocimiento_evaluacion` (`CNE`). E requerida. Columnas:

| Columna | Tipo / referencia |
|---|---|
| `rama` | string(12), COGNITIVA/PETERSEN |
| `id_evaluacion_rama` | text, identidad exacta del contexto de esa rama |
| `contrato_preparacion` | string(40), contrato de rama explícito |
| `disponible` | boolean; COGNITIVA exige true, PETERSEN permite ausencia explícita |
| `cod_version_modelo` | K?, FK `versiones_modelo_experto.cod_version_modelo` |
| `estado_version_observado` | string(20)? |
| `creacion_observada_sql_texto`, `vigencia_observada_sql_texto` | text?, representación exacta de cada fecha de cabecera; NULL solo si era NULL |
| `retiro_observado_sql_texto` | text?, representación del retiro observado |
| `F(creacion_observada)`, `F(vigencia_observada)`, `F(retiro_observado)` | cada tripleta nullable conforme a la fecha observada; sin usar la zona actual para reconstruir el pasado |
| `solo_pruebas_tecnicas` | boolean? |
| `tipo_regla_resultado` | string(30)? |
| `admite_memoria_vacia` | boolean?; NULL conserva ausencia de la clave |
| `huella_definicion` | H?, igual al sello de la versión al capturar |
| `huella_preparacion` | H, entrada y salida de la rama conforme a su codec |
| `huella_salida` | H, salida íntegra reconstruida de esa rama |

Si no hay gobierno Petersen: versión, estado, retiro, flags de paquete y
huella de definición NULL, cero filas hijas de conocimiento y ausencia de
gobierno reconstruida como NULL. La preparación y su abstención sí tienen
huellas. Si está disponible, versión/estado/definición/flags de paquete
obligatorios salvo `tipo_regla_resultado` nullable del DTO original. En el
contrato institucional actual, tipo RESULTADO y `solo_pruebas_tecnicas=false`.
La rama cognitiva debe coincidir con `evaluaciones_expertas.cod_version_modelo`.
Cabecera estable y definición se recuperan de la versión sellada; se reponen
las representaciones temporales y el estado observados, sin volver a escoger
una versión vigente. Petersen usa RFC3339 con microsegundos y offset; V3
conserva los strings SQL originales. No confundir ambos formatos ni regenerar
los offsets desde la configuración futura.

### 3. `estados_reglas_evaluacion_experta` — revisión observada

Grano: conocimiento de la relación 2 + regla original; UNIQUE ambos y UNIQUE
`(cod_conocimiento_evaluacion,orden)`. PK `cod_estado_regla_evaluacion` (`ERE`).
FK `cod_conocimiento_evaluacion` → relación 2; FK `cod_regla_experta` →
`reglas_expertas.cod_regla_experta`; `ord`;
`estado_revision_original string(20)?`;
`cod_usuario_revision_original K?` FK `usuarios.cod_usuario`;
`fecha_revision_sql_texto text?`; `justificacion_revision_original text?`.

El conjunto contiene **todas** las reglas de la declaración, no solo las
activadas. El CHECK replica la forma NULL/PENDIENTE frente a
APROBADA/RECHAZADA y firma completa; la fecha observada debe ser >= creación.
Definición, estado ACTIVO/INACTIVO y antecesora quedan en la regla sellada; no
se copian sus condiciones. La regla debe pertenecer a la versión de la relación
2. Una revisión posterior de esa regla no modifica esta observación.

### 4. `contratos_relacion_instantanea`

Grano: un contrato de predicado/tipos de la declaración de la relación 2. PK
`cod_contrato_relacion_instantanea` (`CRI`). FK `cod_conocimiento_evaluacion`
→ relación 2; `ord`; `tipo_relacion string(50)`;
`tipo_origen string(30)`; `tipo_destino string(30)`;
`habilita_participacion boolean?` (NULL significa clave ausente).
UNIQUE `(cod_conocimiento_evaluacion,orden)` y
`(cod_conocimiento_evaluacion,tipo_relacion,tipo_origen,tipo_destino)`.
No admite predicados nuevos: el validador y el release contrastan los tipos
exactos del contrato institucional.

### 5. `modalidades_relacion_instantanea`

Grano: una modalidad de un contrato de la relación 4, incluido el literal NULL.
PK `cod_modalidad_relacion_instantanea` (`MRI`). FK
`cod_contrato_relacion_instantanea` → relación 4; `ord`;
`modalidad string(30)?`. UNIQUE `(cod_contrato_relacion_instantanea,orden)`;
unicidad del valor por propietario mediante UNIQUE parcial para modalidad no
NULL y otro para modalidad NULL. No se colapsa `[NULL]` con lista vacía.
Se separa esta colección de los tipos y de cualquier extracción.

### 6. `extracciones_conocimiento_instantanea`

Grano: una entrada del mapa de contratos de extracción de la relación 2. PK
`cod_extraccion_conocimiento` (`ECI`). FK `cod_conocimiento_evaluacion` →
relación 2; `tipo_extraccion string(30)`; `contrato_extraccion string(80)`.
UNIQUE `(cod_conocimiento_evaluacion,tipo_extraccion)`. No almacena código
ejecutable, SQL o reglas clínicas en texto; conserva identificadores que
reconoce el release. Mapa vacío = cero filas.

### 7. `autores_instantanea_experta`

Grano: E + personal cuya etiqueta se consumió en la captura. PK
`cod_autor_instantanea` (`AUI`); E; `cod_personal K` FK `personal.cod_personal`;
`nombre_mostrado text` (puede ser cadena vacía si así fue observado).
UNIQUE `(cod_evaluacion_experta,cod_personal)`. Copia exclusivamente la etiqueta
nominal usada por V3, sin documento, teléfono, correo ni datos laborales extra.
Las fuentes Petersen que solo consumen el código no fuerzan esta copia nominal.

### 8. `instrumentos_instantanea_experta`

Grano: E + instrumento cuya metadata se leyó. PK `cod_instrumento_instantanea`
(`INI`); E; `cod_instrumento K` FK `instrumentos.cod_instrumento`;
`codigo string(30)`; `nombre string(160)`; `tipo string(50)`;
`version string(30)?`; `descripcion text?`; `puntaje_maximo D8?`;
`estado string(20)`. UNIQUE `(cod_evaluacion_experta,cod_instrumento)`.
No incluye respuestas ni selectores. No se actualiza al modificar el maestro.

### 9. `preguntas_instantanea_experta`

Grano: E + pregunta leída, incluso seleccionada sin respuesta. PK
`cod_pregunta_instantanea` (`PQI`); E;
`cod_pregunta K` FK `preguntas_instrumento.cod_pregunta`;
`cod_instrumento K` FK `instrumentos.cod_instrumento`;
`codigo string(30)`; `enunciado text`; `dominio string(80)?`;
`tipo_respuesta string(30)`; `puntaje_maximo D8?`; `orden smallint`;
`estado string(20)`. UNIQUE `(cod_evaluacion_experta,cod_pregunta)`.

No se impone que su instrumento coincida con una aplicación: una pregunta
existente pero incoherente debe conservar esa FK y ser rechazada por el motor.
Tampoco se adquiere automáticamente metadata adicional del otro instrumento.
La pregunta declarada que no existe no genera una fila ficticia: queda como
declaración no resuelta en las relaciones 14/17/18.

### 10. `opciones_instantanea_experta`

Grano: E + opción leída. PK `cod_opcion_instantanea` (`OPI`); E;
`cod_opcion K` FK `opciones_pregunta.cod_opcion`;
`cod_pregunta K` FK `preguntas_instrumento.cod_pregunta`;
`nombre string(160)`; `valor string(120)?`; `puntaje D8?`;
`orden smallint`; `estado string(20)`.
UNIQUE `(cod_evaluacion_experta,cod_opcion)`. No se corrige una opción de otra
pregunta para fabricar coherencia ni se cargan sus otras opciones por comodidad.

### 11. `aplicaciones_instantanea_experta`

Grano: E + aplicación original, **compartida por V3 y MEDICION de Petersen**.
PK `cod_aplicacion_instantanea` (`API`); E. UNIQUE
`(cod_evaluacion_experta,cod_aplicacion)`. Columnas:

| Columna | Tipo / referencia |
|---|---|
| `cod_aplicacion` | K, FK `aplicaciones_instrumento.cod_aplicacion` |
| `cod_instrumento` | K, FK `instrumentos.cod_instrumento` |
| `cod_instrumento_instantanea` | K?, FK relación 8; NULL solo si la proyección original de metadata era NULL |
| `cod_personal` | K, FK `personal.cod_personal` |
| `cod_autor_instantanea` | K, FK relación 7 |
| `cod_atencion` | K?, FK `atenciones.cod_atencion` |
| `cod_atencion_instantanea` | K?, FK relación 22; conserva la relación que realmente se leyó |
| `fecha_hora_sql_texto` | text, fecha de la aplicación tal como quedó en `aplicacion_actual` |
| `F(fecha_clinica)` | instante formateado/consumido por la captura |
| `puntaje_total`, `puntaje_maximo` | D8?, cada uno |
| `clasificacion` | string(80)? |
| `interpretacion`, `observacion` | text?, cada uno |
| `estado_original` | string(20) |
| `cod_usuario_registro` | K?, FK `usuarios.cod_usuario` |
| `registrado_en_sql_texto` | text? |
| `F(registro_tecnico)?` | fecha usada por `procedencia`, sin inventar historia anterior |
| `cod_documento_clinico` | K?, FK `documentos_clinicos.cod_documento_clinico` |
| `cod_fuente_documento_instantanea` | K?, FK relación 20, rama DOCUMENTO |
| `localizador_fuente` | string(120)? |
| `cod_aplicacion_anterior` | K?, FK `aplicaciones_instrumento.cod_aplicacion` |
| `estado_documento` | string(40), resultado consumido de la comprobación documental |
| `cod_perfil_captura` | K?, FK relación 13; perfil aplicado o ausencia explícita |

Residente original de la aplicación debe ser E.residente. La fuente de edad se
reconstruye de `fecha_nacimiento_original` y `F(fecha_clinica)`; edad es derivada,
no otra columna. Autor/código/nombre/edición redundantes del DTO se reconstruyen
de las copias únicas. Los pares usuario/registro y documento/localizador siguen
sus formas originales; no se inventa una atención para completar origen.

### 12. `respuestas_instantanea_experta`

Grano: aplicación de la relación 11 + respuesta original. PK
`cod_respuesta_instantanea` (`RSI`). FK `cod_aplicacion_instantanea` → relación
11; `cod_respuesta K` FK `respuestas_instrumento.cod_respuesta`;
`cod_pregunta K` FK `preguntas_instrumento.cod_pregunta`;
`cod_opcion K?` FK `opciones_pregunta.cod_opcion`;
`cod_pregunta_instantanea K?` FK relación 9;
`cod_opcion_instantanea K?` FK relación 10;
`valor_numero D12?`; `valor_texto text?`; `valor_logico boolean?`;
`puntaje D8?`; `observacion text?`; `coherente_original boolean`.
UNIQUE `(cod_aplicacion_instantanea,cod_respuesta)`.

No imponer XOR sobre los valores originales: una respuesta original ambigua con
dos columnas pobladas debe conservarse como tal y rechazarse, no corregirse al
copiar. Se conserva la FK incoherente entre entidades existentes. Si una fuente
está físicamente rota y no puede cumplir su propia FK, el guardado falla como
no representable: no crea una referencia falsa ni convierte la PK en texto sin
relación. Orden canónico por código de respuesta, como V3.

### 13. `perfiles_captura_instantanea`

Grano: E + identificador de perfil de captura aplicado. PK `cod_perfil_captura`
(`PCI`); E; `id_perfil_original text`; `metodo string(80)`;
`cod_instrumento K` FK `instrumentos.cod_instrumento`;
`codigo_instrumento string(30)`; `version_instrumento string(30)`;
`identidad_presente boolean`; cuatro booleanos
`declara_instrumento/declara_forma/declara_edicion_protocolo/declara_idioma`;
`S(identidad_instrumento)`, `S(identidad_forma)`,
`S(identidad_edicion_protocolo)`, `S(identidad_idioma)`.
UNIQUE `(cod_evaluacion_experta,id_perfil_original)`.

Estos cuatro atributos son la unión cerrada de identidades de los 23 métodos
actuales; no se guardan como filas atributo/valor. Presencia distingue clave
ausente y NULL. Campo no declarado exige escalar NULL. Si identidad está
ausente, todos los `declara_*` son false; mapa vacío tiene identidad presente
y cuatro flags false. No se reemplaza una identidad incorrecta por la esperada
del registro, pues se perdería una causa de abstención. Los tipos escalares
permiten conservar una declaración errónea finita; estructuras anidadas o
claves desconocidas producen fallo explícito de representabilidad. La identidad
esperada, bibliografía y fórmulas se recuperan del release, sin copiarlas.

### 14. `selectores_captura_instantanea`

Grano: una clave presente en el mapa `selectores` de un perfil de la relación
13. PK `cod_selector_captura` (`SCI`); FK `cod_perfil_captura` → relación 13;
`entrada string(80)`; `selector_nulo boolean`;
`identificador_pregunta_declarado text?`;
`cod_pregunta K?` FK `preguntas_instrumento.cod_pregunta`;
`cod_pregunta_instantanea K?` FK relación 9;
`campo string(30)?` cerrado a valor_numero/valor_logico.
UNIQUE `(cod_perfil_captura,entrada)`.

Una clave ausente no tiene fila; una clave presente con selector NULL sí tiene
fila, `selector_nulo=true` y el resto NULL. Un selector no nulo exige ID declarado
y campo; si existe la pregunta, sus dos FK son obligatorias y su PK debe ser
igual al ID declarado. Un ID declarado inexistente permanece literal de
configuración con ambas FK NULL: no se está sustituyendo una relación existente
por texto ni atribuyendo una pregunta que nunca existió. No guardar una
respuesta para satisfacer ese selector. No deducir selector desde enunciados.

### 15. `perfiles_interpretativos_instantanea`

Grano: E + código/versión/componente del perfil interpretativo **resuelto**.
PK `cod_perfil_interpretativo` (`PII`); E;
`codigo text`; `version text`; `componente string(30)`;
`instrumento_codigo string(30)`; `instrumento_version string(30)`;
`metodo text`; `fuente text`.
UNIQUE `(cod_evaluacion_experta,codigo,version,componente)`.

El perfil no copia brutos ni predicados en listas. No guarda el alias mutable
de configuración como sustituto de su contenido. El escritor admite únicamente
el objeto resuelto con estas claves y las dos colecciones siguientes; claves
adicionales no representadas provocan error, no pérdida silenciosa.

### 16. `predicados_perfil_instantanea`

Grano: una entrada ordenada de `predicados` de la relación 15. PK
`cod_predicado_perfil` (`PPI`); FK `cod_perfil_interpretativo` → relación 15;
`ord`; `predicado string(30)`. UNIQUE `(cod_perfil_interpretativo,orden)`.
Conservar el contenido y orden observados, incluso repetición; la validación
clínica no se infiere de esa lista. El release solo interpreta sus predicados
reconocidos; no hay permiso para añadir una regla nueva por este diccionario.

### 17. `medidas_perfil_instantanea`

Grano: una entrada del mapa `mediciones_requeridas` de la relación 15. PK
`cod_medida_perfil` (`MPI`); FK `cod_perfil_interpretativo` → relación 15;
`nombre string(80)`; `identificador_pregunta_declarado text`;
`cod_pregunta K?` FK `preguntas_instrumento.cod_pregunta`;
`cod_pregunta_instantanea K?` FK relación 9;
`campo string(30)`, CHECK = valor_numero.
UNIQUE `(cod_perfil_interpretativo,nombre)`.
Resolución/no existencia como en relación 14. El número original se obtiene
de respuestas, no se copia a esta definición ni se guarda un z de entrada.

### 18. `vinculos_declarativos_instantanea`

Grano: una entrada del mapa de vínculos de una declaración cognitiva. PK
`cod_vinculo_declarativo` (`VDI`); FK `cod_conocimiento_evaluacion` → relación 2;
`cod_mapeo_variable_fuente K` FK
`mapeos_variables_fuente.cod_mapeo_variable_fuente`;
`cod_instrumento K` FK `instrumentos.cod_instrumento`;
`codigo_instrumento string(30)`; `version_instrumento string(30)`;
`identificador_pregunta_declarado text`;
`cod_pregunta K?` FK `preguntas_instrumento.cod_pregunta`;
`cod_pregunta_instantanea K?` FK relación 9;
`campo string(30)`, valor_logico/valor_texto;
`declara_componente boolean`; `componente string(30)?`;
`declara_perfil_interpretativo boolean`;
`cod_perfil_interpretativo K?` FK relación 15.
UNIQUE `(cod_conocimiento_evaluacion,cod_mapeo_variable_fuente)`.

Misma versión del mapeo y conocimiento; declaración y pertenencia al instrumento
se validan sin fabricar una FK. Flags distinguen clave ausente y NULL. Perfil
resuelto no NULL requiere su FK; NULL explícito conserva ausencia de contenido.
Las preguntas declarativas originales sin respuesta se recuperan desde este
mapa y la relación 9. La configuración actual y las claves regeneradas de una
sucesora no participan en la recuperación.

### 19. `valoraciones_petersen_evaluacion`

Grano: E + juicio original visto al corte, **incluidos rectificados, anulados o
excluidos**. PK `cod_valoracion_evaluacion` (`VPE`); E;
`cod_valoracion_petersen K` FK
`valoraciones_petersen_originales.cod_valoracion_petersen`;
`orden_adquisicion integer` CHECK >= 0;
`orden_entrada_motor integer?` CHECK >= 0;
`incluida_entrada_motor boolean`.
UNIQUE `(cod_evaluacion_experta,cod_valoracion_petersen)`,
UNIQUE `(cod_evaluacion_experta,orden_adquisicion)` y UNIQUE parcial
`(cod_evaluacion_experta,orden_entrada_motor)` no NULL.

Si incluida, orden de entrada obligatorio; en otro caso NULL. Mismo residente,
fecha clínica y técnica <= corte. No copiar elemento, estado, juicio,
justificación, autor ni comprobaciones: esas filas originales ya son inmutables.
El total al corte es el número de membresías, no una consulta posterior de
cabezas. La inclusión refleja la adquisición original; no se convierte en una
declaración de suficiencia clínica.

### 20. `fuentes_petersen_instantanea` — contexto mutable cerrado

Grano: E + una fuente contextual original cuyo contenido fue efectivamente
adquirido. PK `cod_fuente_petersen_instantanea` (`FPI`); E. Columnas:

| Columna | Tipo / referencia |
|---|---|
| `clase_fuente` | string(20): COGNITIVO, IADL, PSICOLOGIA, ANTECEDENTE, DIAGNOSTICO o DOCUMENTO |
| `cod_control_cognitivo` | K?, FK `controles_cognitivos.cod_control_cognitivo` |
| `cod_control_iadl` | K?, FK `controles_funcionales_iadl.cod_control_iadl` |
| `cod_valoracion_psicologica` | K?, FK `valoraciones_psicologicas.cod_valoracion_psicologica` |
| `cod_antecedente` | K?, FK `antecedentes_clinicos.cod_antecedente` |
| `cod_diagnostico` | K?, FK `diagnosticos.cod_diagnostico` |
| `cod_documento_clinico` | K?, FK `documentos_clinicos.cod_documento_clinico` |
| `cod_personal_original` | K?, FK `personal.cod_personal` |
| `F(fecha_clinica)?` | Instante original cuando existe |
| `fecha_referencia_original` | date?, solo cuando la fuente tiene fecha sin hora |
| `F(registro_tecnico)?` | Instante técnico de fuente cuando fue consumido |
| `cod_atencion` | K?, FK `atenciones.cod_atencion` |
| `cod_atencion_instantanea` | K?, FK relación 22 |
| `estado_original` | string(30) |
| `tiene_sucesora_iadl_al_corte` | boolean?, solo IADL; observación de la consulta, no diagnóstico |
| `descripcion_consumida` | text?, proyección fija descripción/observación/conclusión/título que realmente se consumió |
| `actividad`, `oportunidad`, `desempeno`, `tipo_ayuda`, `causa_ayuda`, `nivel_previo`, `fuente_iadl` | string(40)? cada una; siete columnas explícitas, solo IADL |
| `hash_documento_original` | string(64)?, solo DOCUMENTO; H exigida únicamente si bytes_documento_verificados=true |
| `bytes_documento_verificados` | boolean?, solo DOCUMENTO |

CHECK XOR de las seis FK originales y correspondencia con clase; unicidad
parcial `(cod_evaluacion_experta,FK_fuente)` para cada rama. Solo campos de esa
clase poblados. Fuente con mera fecha no recibe instante clínico inventado.
Una huella documental inválida o NULL observada con verificación false se
conserva sin corregirla; esto también cubre documentos solo instrumentales que
no tienen valoración Petersen ni una fila en relación 25.
La fuente pertenece al residente de E; no se copian expedientes ajenos para
explicar un rechazo. El código de residente se reconstruye desde E.

Aplicaciones y juicios no son ramas de esta tabla: se reutilizan relaciones
11 y 19. Documento consumido tanto por V3 como por Petersen usa **la misma**
copia de contexto, y cada ocurrencia conserva su propia constancia/orden en
relación 25. NULL en un campo no se convierte en texto vacío si cambia el
contrato consumido. Los siete atributos IADL son los vocabularios ya aprobados
de la fuente, sin nuevos umbrales, actividad o atribución cognitiva inventados.

### 21. `soportes_petersen_evaluacion`

Grano: una ocurrencia de soporte original en la membresía de juicio de la
relación 19. PK `cod_soporte_petersen_evaluacion` (`SPE`). FK
`cod_valoracion_evaluacion` → relación 19;
`cod_soporte_petersen K` FK `soportes_valoracion_petersen.cod_soporte_petersen`;
`orden_entrada_motor integer?` CHECK >= 0;
`clase_captura string(20)` cerrada
NO_ADQUIRIDA/CONTEXTO/APLICACION/DOMINIO_REVISADO;
`cod_fuente_petersen_instantanea K?` FK relación 20;
`cod_aplicacion_instantanea K?` FK relación 11;
`cod_valoracion_revisada K?` FK
`valoraciones_petersen_originales.cod_valoracion_petersen`;
`vigente_consumida boolean?`.
UNIQUE `(cod_valoracion_evaluacion,cod_soporte_petersen)` y UNIQUE parcial
`(cod_valoracion_evaluacion,orden_entrada_motor)` no NULL.

El original aporta tipo, orden, método y hash del perfil sin copiarlos.
CONTEXTO/APLICACION exige exactamente su FK de snapshot; DOMINIO_REVISADO exige
la FK original revisada de C05 y no inventa un `Fuente` de medición. NO_ADQUIRIDA
carece de las tres FK de destino y de vigencia consumida. La fuente elegida debe
ser la misma que identifica la FK tipada del soporte original. Las fuentes
referenciadas por varios juicios no se duplican.

La lista de soportes de E se recupera **solo** de estas membresías: un INSERT
posterior en la colección operativa del juicio no ingresa retrospectivamente
en esta ejecución. El cierre SQL de E impide también INSERT tardío de nuevas
membresías. La vigencia consumida preserva exactamente la decisión usada por
`FuentePetersenInstitucional`; no se recalcula desde un estado actual.

Las constancias se separan en relación 25: una cabecera documental no es un
soporte, y el lector también conserva intentos no verificables. No forzar una
fila CONTEXTO ni afirmar bytes verificados para representar esos intentos.

### 22. `atenciones_instantanea_experta`

Grano: E + atención cuya relación fue consumida. PK `cod_atencion_instantanea`
(`ATI`); E; `cod_atencion K` FK `atenciones.cod_atencion`;
`cod_residente_original K` FK `residentes.cod_residente`.
UNIQUE `(cod_evaluacion_experta,cod_atencion)`.

Son exactamente los dos atributos de `atencion_original` ya consumidos por V3;
no se añaden nota, diagnóstico, nombre, profesional ni expediente del otro
residente. Puede conservarse una FK de residente incoherente que ya estaba en
la proyección autorizada descartada. Se prohíbe adquirir más datos ajenos o
mostrarlos en la UI. Si no se puede conservar esa referencia conforme a
autorización, falla la conservación; no se cambia silenciosamente V3 ni se
presenta un dato saneado como el original.

### 23. `exclusiones_adquisicion_petersen`

Grano: una entrada **ordenada** de la lista de exclusiones producida por la
adquisición. PK `cod_exclusion_petersen` (`EXP`); E; `ord`;
`cod_valoracion_petersen K` FK
`valoraciones_petersen_originales.cod_valoracion_petersen`;
`motivo string(100)`. UNIQUE `(cod_evaluacion_experta,orden)`.

La valoración debe estar en relación 19; elemento se reconstruye del original.
Se permiten motivos iguales repetidos: distintos soportes pueden producir
dos entradas iguales en el contrato actual. No aplicar UNIQUE por motivo.
Conservar los códigos de adquisición del release, incluidos original anulado,
rectificado, fuente incompatible, falta de instante, documento no verificable
y contrato instrumental irrecuperable. No es una tabla universal de errores.

Estos motivos son **entrada observada** del replay Petersen, que no reproduce
la adquisición SQL ni la autorización histórica; por eso no se eliminan como
si fueran motivos derivados del clasificador. Los motivos del clasificador,
en cambio, se regeneran desde sus entradas conservadas.

### 24. `motivos_adquisicion_instrumental`

Grano: una entrada ordenada de `captura.motivos` producida por el lector de
fuentes, antes de normalizar. PK `cod_motivo_adquisicion` (`MAI`); FK
`cod_aplicacion_instantanea` → relación 11; `ord`; `motivo string(120)`.
UNIQUE `(cod_aplicacion_instantanea,orden)`.

Conserva exclusivamente los motivos de adquisición que V3 usa para decidir si
reextrae. No almacena todos los motivos de normalización, métricas o resultado.
No se debe borrar esta lista bajo la premisa de que el replay reconstruye la
consulta SQL original: el contrato no hace eso. Orden y códigos provienen de
la captura, sin traducciones persistidas ni motivos clínicos nuevos.

### 25. `constancias_documentales_evaluacion`

Grano: una ocurrencia ordenada de comprobación documental de E. PK
`cod_constancia_documental` (`CDE`); E; `ord`;
`cod_valoracion_evaluacion K` FK relación 19;
`papel string(10)` ORIGINAL/SOPORTE;
`cod_documento_clinico K` FK `documentos_clinicos.cod_documento_clinico`;
`cod_soporte_petersen_evaluacion K?` FK relación 21;
`cod_residente_observado K?` FK `residentes.cod_residente`;
`cod_personal_observado K?` FK `personal.cod_personal`;
`hash_archivo_observado string(64)?`;
`F(fecha_documental)?`; `estado_observado string(30)?`;
`localizador_observado string(120)?`; `bytes_verificados boolean`.
UNIQUE `(cod_evaluacion_experta,orden)`.

ORIGINAL exige soporte NULL y documento/localizador iguales a la cabecera
original de la valoración. SOPORTE exige la membresía de soporte de ese mismo
juicio y documento; el localizador es NULL, pues la fuente operativa no lo
contiene. No se inventa a partir del localizador de la cabecera.

La metadata observada puede ser NULL cuando el documento no estaba disponible
para el actor; se conserva la referencia ya autorizada del juicio, sin adquirir
el expediente ajeno. Con `bytes_verificados=true`, hash debe cumplir H y debe
existir metadata del documento del mismo residente. Con false se conserva la
huella original NULL o inválida; no aplicar H incondicional y convertir una
fuente insuficiente en fallo total. Tener bytes válidos no significa que su
fecha sea válida para el corte: una constancia fuera de corte puede ser true
pero su juicio está excluido de la entrada al motor.

Esta relación expresa el resultado de una comprobación en una ocurrencia;
no es una segunda copia de documentos ni un archivo clínico. No guarda ruta,
contenido, firma profesional ni el resultado clínico. Las proyecciones
normalizadas usadas como `Fuente` siguen en relación 20. Si varias ocurrencias
han observado metadata distinta de un documento durante la misma preparación,
el futuro escritor debe conservar cada constancia y rechazar la combinación
si contradice la copia compartida que se pretende guardar; no elegir una al
azar ni afirmar una adquisición SQL atómica que no se garantizó.

## Sellado de las 12 tablas de conocimiento y cabecera

Esta propuesta depende de una **garantía nueva y explícita**, no implementada:
una vez sellada la versión, su conjunto de definiciones no admite INSERT,
UPDATE ni DELETE. Un Model inmutable o un hash aislado no la sustituye.

| Tabla existente que se protege | Resolución de versión para NEW/OLD |
|---|---|
| `nodos_semanticos` | `cod_version_modelo` directo |
| `relaciones_semanticas` | directo; además ambos nodos de esa versión |
| `dominios_valores_expertos` | directo |
| `valores_semanticos` | FK dominio → `dominios_valores_expertos.cod_version_modelo` |
| `variables_expertas` | directo; comprobar nodo y dominio |
| `fuentes_datos_expertas` | directo |
| `mapeos_variables_fuente` | directo; variable y fuente de esa versión |
| `mapeos_valores_fuente` | FK mapeo → `mapeos_variables_fuente.cod_version_modelo` |
| `criterios_dominios_resultado` | FK criterio → `nodos_semanticos.cod_version_modelo` |
| `reglas_expertas` | `cod_version_modelo` directo |
| `condiciones_regla_experta` | FK regla → `reglas_expertas.cod_version_modelo` |
| `consecuencias_regla_experta` | FK regla → `reglas_expertas.cod_version_modelo` |

Triggers SQL en cada tabla resuelven y bloquean la versión **antes de mutar**.
Para UPDATE se resuelven NEW y OLD y no se permite mover una fila desde/hacia
una versión sellada; para DELETE se usa OLD. En PostgreSQL, una mutación obtiene
bloqueo de fila compartido que entra en conflicto con el bloqueo exclusivo del
sellador; después del lock se relee el sello. El sellador bloquea la cabecera,
valida conjunto/huella, fija las tres columnas una sola vez y confirma en la
misma transacción de evaluación. Así, insertar antes se incluye en el sello o
insertar después se rechaza: no existe una ventana de INSERT tardío.
SQLite usa su transacción de escritura y triggers equivalentes, sin atribuirle
la prueba de locks PostgreSQL.

Excepciones cerradas, con todas las demás columnas idénticas:

1. `versiones_modelo_experto.fecha_hora_retiro`: primera asignación de retiro,
   coherente con creación/vigencia, por el flujo autorizado de sucesora. El
   retiro observado de cada evaluación queda en relación 2.
2. En `reglas_expertas`, primera firma de las cinco columnas aprobadas de
   revisión; se mantienen sus CHECK/triggers existentes y queda excluida del
   hash de definición. El estado observado completo queda en relación 3.
3. Las tres columnas del sello: transición conjunta NULL → valor, una sola
   vez; nunca cambiar un sello ya informado.

No se permite añadir una condición por tener aún revisión pendiente. Tampoco
se permite usar el retiro o la primera firma para cambiar nombre, descripción,
estado ACTIVO/INACTIVO, tipo, autor, condición o consecuencia. La cabecera,
excepto retiro y primer sello, queda protegida frente a UPDATE/DELETE tras el
sello. Las revisiones sucesoras son filas nuevas con PK nuevas, sin heredar el
sello de la antecesora antes de completar su propio contenido.

La carga nueva y `crearRevision` deben insertar el paquete completo antes de
sellarlo. Si una evaluación usa una versión existente no sellada, el primer
sello captura **el estado actual comprobado**, no una reconstrucción histórica
inventada. Si una versión ya sellada no coincide con su huella, falla sin
corregirla ni completarla automáticamente. Las versiones previas sin uso nuevo
permanecen sin sellar; no se migra ni reescribe su historia por anticipación.

## Atomicidad, cierre de colecciones e integridad cruzada

1. Autorizar la operación explícita con cuenta activa, permiso y competencia
   aprobados, residente dentro del alcance y sin preview. Este diccionario no
   concede un permiso nuevo ni aprueba el contrato clínico de revisión.
2. Capturar fuentes autorizadas y metadatos necesarios en una transacción con
   aislamiento coherente. Revalidar las dos versiones bajo bloqueo. No volver a
   elegir el paquete activo a mitad de la captura.
3. Verificar y registrar un release ya retenido en storage privado. El archivo
   se publica con nombre nuevo y verificación previa; filesystem y SQL no
   comparten rollback distribuido. Un fallo de SQL puede dejar un artefacto
   huérfano identificable, pero nunca una evaluación exitosa sin release.
4. Reservar identidad y bloquear reintentos. Misma identidad/mismo contenido
   devuelve lo guardado; contenido distinto falla. El conflicto revierte todas
   las filas nuevas. No crear usuarios, residentes, personal o fuentes fallback.
5. Escribir cabecera nueva con contrato compuesto y estado no finalizado,
   snapshots y membresías. Una referencia entre relaciones de E distinta se
   rechaza por SQL además de por la Action cuando se afirma garantía física.
6. Sellar las definiciones de conocimiento si es la primera utilización;
   reconstruir todas las entradas y ambas salidas desde lo que se va a guardar;
   comparar las tres huellas. Activitylog registra actor y referencias mínimas,
   sin payload clínico.
7. Finalizar cabecera. Triggers de cierre validan: dos ramas, versión cognitiva
   coherente, pertenencias, conteo/membresías de juicios, órdenes continuos,
   perfiles representables, constancias y ambos hashes. Solo después el
   resultado se considera guardado.
8. Para cada una de las relaciones dependientes de E (2–25), INSERT/UPDATE/DELETE se bloquean cuando
   la evaluación propietaria ya está finalizada. Los hijos resuelven la
   evaluación por sus FK. **Bloquear solo UPDATE/DELETE no basta.** Releases
   referenciados tampoco se sustituyen ni borran. Revisión profesional posterior
   agrega filas a su tabla ya aprobada, sin reabrir snapshots.

La misma aplicación instrumental usada por ambas ramas tiene una sola fila de
relación 11. Antes de aceptar la membresía MEDICION se coteja la captura
canónica completa con esa fila y sus hijos; mismo código con contenido distinto
provoca conflicto, no elección silenciosa de una rama. El conjunto de soportes
del juicio queda fijado por relación 21 aunque la fuente tenga nuevas sucesoras
o se añadan otros soportes después.

No repetir E en un hijo no exime de verificarlo: triggers de contexto recorren
propietario y referencias de snapshot, y bloquean la cabecera pertinente. Las
FK originales expresan procedencia; la igualdad de residente de las fuentes
clínicas corresponde también a la adquisición autorizada y a la validación de
cierre. Si una referencia ajena solo se retiene como dato descartado ya
consumido, no se recorren sus datos clínicos ni se publican.

## Retención propuesta: decisión pendiente real

No se encontró en las decisiones inspeccionadas una política aprobada de
retención del release institucional ni una obligación de plazo concreto.
Propuesta para decisión de la propietaria:

- Conservar evaluación, conocimiento sellado, snapshots, referencias originales
  y release mientras exista cualquier evaluación o revisión que dependa de
  ellos. Sin purga automática por antigüedad, sin reemplazar ZIP y sin borrar
  historia ordinaria.
- Incluir artefactos, manifiestos y sus registros en el respaldo institucional;
  verificar restauración y reproducción en entorno compatible. Un hash de un
  archivo perdido no satisface retención.
- Controlar lectura de snapshots por alcance de residente y del software por
  el permiso técnico correspondiente; no entregar el paquete clínico completo
  al navegador ni a un servicio externo para reproducirlo.
- La pérdida de release impide afirmar replay verificable y se reporta como
  incidencia. No recalcular con el checkout actual para fingir reproducción.

Esto es una política técnica candidata alineada con conservación de historia,
**no un plazo legal** ni autorización de infraestructura/servicio nuevo. Las
rutinas de backup y custodia reales deben ser confirmadas antes de declarar
`release_retenido=true`. Aprobar tablas no demuestra esa retención operativa.

## 4FN, alternativas y coste evitado

Cada relación tiene una colección o hecho de grano único: respuestas, opciones,
selectores, medidas requeridas y predicados no forman productos cartesianos.
Tipo/modalidades de contrato se separan. Una pregunta y un autor observados no
se copian en cada respuesta/aplicación. La rama, versión, regla y estado observado
se distinguen sin fabricar una versión común.

La relación 20 es una unión **cerrada** de seis fuentes conocidas con FK reales
y columnas explícitas, no un catálogo de tablas dinámicas ni pares campo/valor.
Los cuatro `S(identidad_*)` son columnas fijas del perfil, no filas arbitrarias.
No se introducen catálogos persistidos por cada método ni una tabla por examen.
Las FK declaradas inexistentes son valores de configuración no resueltos,
distintos de una relación existente; cuando la relación existe, se exige su FK.

Se evitan 12 tablas de copia de conocimiento a cambio de protección SQL real
de las 12 existentes y una observación de revisión por regla. Se evitan tablas
de medidas calculadas, intervalos, métricas y razones del motor mediante replay
del release. Las dos listas de motivos propuestas son entradas de adquisición
que ese replay no recalcula, no caches de resultados clínicos.

Alternativas rechazadas para este alcance: insertar todo el DTO en JSON, añadir
un árbol genérico de nodos/tipos/valores, mover el paquete SQL/configuración al
ZIP, copiar toda la historia clínica por soporte o quitar guardas de
`AlmacenInstantaneas`. Una alternativa con menos tablas puede ser válida si
reduce explícitamente el alcance, pero no debe afirmar igualdad íntegra V3 al
omitir un dato que V3 utiliza o conserva.

## Matriz de representación del contrato preparado

| Ruta cerrada del DTO | Representación propuesta |
|---|---|
| Compuesto: contrato, actor, residente, corte, huella | Cabecera existente + cuatro columnas de evaluación; codecs ya aprobados |
| `ejecutor.cantidad_archivos/huella_src` | Derivación del mapa `archivos` del manifiesto del release de relación 1; al finalizar debe igualar la identidad capturada, sin reemplazarla por el checkout actual |
| Compuesto `release` | Manifiesto del ZIP de relación 1, bytes y conjunto verificados; preparación sin release no se finaliza |
| `persistido/release_retenido` de la preparación | Literales false del contrato de preparación; el estado de guardado de la cabecera se informa aparte, sin reescribir el DTO pasado |
| Ramas: contrato/contexto/huella/salida | Relación 2 + cabecera; salida regenerada y comparada con `huella_salida` |
| V3 `cabecera_conocimiento` / Petersen `gobierno.cabecera` | Cabecera sellada y fechas/estado observados de relación 2; formatos de cada rama preservados |
| Conocimiento `version/tablas/solo_pruebas_tecnicas/tipo_regla_resultado` | Definición SQL sellada + relación 2; revisión de todas las reglas en 3, no solo las activadas |
| Contratos `contratos_relaciones/contratos_extraccion` | 4, 5 y 6; modalidades conservan NULL y lista vacía |
| V3 `fuente_conceptual` | Archivo conceptual del release, cotejado contra el contenido de la preparación antes de finalizar |
| Capturas: corte/disponibilidad/aplicaciones/procedencia/edad | Cabecera + 7–12, 20 y 22; edad y cadenas redundantes reconstruidas exactamente de nacimiento y fecha usada |
| Capturas: perfiles/selectores/motivos | 13, 14 y 24; perfil NULL y selector ausente/NULL separados |
| V3 `vinculos` / perfil interpretativo resuelto | 15–18; predicados y medidas son colecciones separadas |
| V3 extracción, métricas, motivos calculados, salida | Derivación desde brutos + release; no copiar un resultado previo como entrada |
| Petersen `procedencia_valoraciones` | Membresías 19 + columnas originales inmutables del juicio; código/autor/fecha/actor/rectificación/documento/localizador |
| Petersen `membresias_soportes` | IDs y orden de 21 para todos los juicios adquiridos, incluidos los descartados; no volver a enumerar soportes operativos al recuperar |
| Petersen `valoraciones` y sus comprobaciones | 19 + originales inmutables; orden de entrada y pertenencia se fijan por ejecución |
| Petersen `origen/soportes/dominios_revisados` | 19–22 + vínculo original tipado; captura MEDICION compartida con V3, relación 11 |
| Petersen `adquisicion` | Conteo derivado de 19, lista ordenada 23 y literales del contrato; no reejecutar SQL histórico |
| Petersen `constancias_documentales` | 25, incluidas cabecera, soporte, no disponible, bytes alterados y fecha fuera de corte |
| Petersen `limites` | Literales del contrato: no revalida permisos históricos ni bytes, no reproduce SQL, preparación no persistida |

El futuro escritor debe ejecutar una comprobación de representabilidad antes
de insertar: claves desconocidas, valores fuera de longitud/tipo, perfiles no
cerrados o discrepancias entre copias compartidas provocan error explícito.
No proyectar silenciosamente un DTO más amplio sobre estas columnas. Los
contratos V1/V2 y el almacén experimental conservan sus lectores y guardas.

## Madurez, decisión solicitada y aceptación posterior

**Completado antes de presentar:** cotejo estático de las rutas cerradas de V3,
Petersen y compuesto en la matriz anterior; membresías de todos los juicios y
soportes adquiridos; procedencia real del localizador; constancias originales y
de soporte separadas, también para verificación false; conservación de hashes
documentales inválidos sin hacerlos evidencia válida. La revisión independiente
de seguridad/integridad y diseño 4FN emitió PASS acotado de diseño estático el
20/10. Se corrigieron sus hallazgos en una fase separada. No es un PASS físico
de tablas todavía inexistentes.

**Decisión de la propietaria:** aprobar o ajustar este diccionario concreto de
25 relaciones, siete columnas, constraints/sellado y prefijos; la alternativa
de reconstruir salidas mediante release en vez de duplicarlas; y la política
técnica de conservarlo mientras haya evaluaciones/revisiones dependientes.
La aprobación estructural no certifica la custodia y los respaldos operativos.
El inventario candidato 133 no se vuelve vigente hasta aprobar e implementar
la extensión conforme a las reglas de baseline.

**Pendiente de implementación posterior a esa decisión:** escritor/lector
relacional con validador de representabilidad, rechazo de cualquier clave o
valor fuera de las columnas cerradas, pruebas guardar→recuperar→replay, garantías
SQL de contexto y sellado concurrente, y verificación de retención/restauración
en storage privado. Si la implementación descubre una entrada aceptada no
representable, falla y presenta la corrección estructural para decisión; no
trunca, inventa o introduce columnas genéricas. Si se requiere además consulta
analítica SQL sin ejecutar el release, debe diseñarse y aprobarse aparte.

Tras aprobación expresa del diccionario final: ADR y migración forward nuevas,
Models/Actions/lectores por contrato, catálogo correlativo y baseline
sincronizados. `down()` rechaza cualquier fila o sello que implique pérdida de
historia antes de modificar estructura. Ninguna migración previa se edita y
no se resetea `remembermind_dev`.

La aceptación implementada deberá demostrar SQLite y PostgreSQL desechables:
guardar/recuperar/replay exactamente iguales; fuentes/perfiles/estados actuales
modificados sin afectar el histórico; pérdida/corrupción de release; ramas o
residente cruzados; NULL/false/cero/decimales y microsegundos; selector sin
respuesta/pregunta inexistente; contexto IADL y documentos; datos descartados;
reintento idéntico/conflicto; rollback intermedio; sellado concurrente frente a
INSERT/UPDATE/DELETE de cada camino de conocimiento; INSERT tardío de soportes
y membresías; sucesoras y primeras revisiones; lectura contextual y dos
revisiones profesionales sin efectos parciales. La evidencia clínica tiene su
proceso independiente y no se deduce de estas pruebas técnicas.
