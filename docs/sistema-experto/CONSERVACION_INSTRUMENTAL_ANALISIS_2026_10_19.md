# Conservación instrumental institucional — análisis de brecha

Fecha: 19/10/2026. Estado: **diseño candidato no aprobado; sin DDL, migraciones ni cambios del inventario**. Complementa el cierre de adquisición. No sustituye decisiones aprobadas de conservación ni habilita interpretación clínica.

## Contrato que falta representar

`CapturasInstrumentalesResidente` obtiene aplicaciones, instrumentos, autor, respuestas, preguntas y opciones originales al corte, incluidos datos rechazados. `PerfilInstrumentalCognitivo` añade método, entradas utilizadas, normalización, motivos y la red de cinco dominios y diecinueve componentes. Ambos declaran `persistido=false` para este contrato.

La RFC anterior trata `PERFIL-EXPERIMENTAL-1` y un manifiesto compuesto de laboratorio. Añadir sus tres FK no conserva este perfil instrumental. Las FK a fuentes operativas mutables tampoco demuestran qué valores se utilizaron en una ejecución anterior.

Inventario aprobado consultado: **73 operativas + 33 expertas = 106 tablas de negocio**. Las nueve tablas aprobadas el 17/10 ya forman parte de esas 33 expertas; las candidatas de este análisis serían otra ampliación, no una aplicación de aquella autorización.

## Reutilización

| Elemento existente | Uso compatible | Límite |
| --- | --- | --- |
| `evaluaciones_expertas` | Identidad, residente, solicitante, corte exacto, contrato, huella y versión | No contiene todas las fuentes originales ni todas las medidas normativas |
| `versiones_modelo_experto`, nodos y relaciones | Una revisión institucional coherente real | No fabricar una versión común a paquetes que son distintos |
| Criterios, resultados, trazas y soportes | Conclusiones categóricas derivadas por reglas identificables | Un valor semántico no representa bruto, z o intervalo percentilar |
| `revisiones_evaluacion_experta` | Cadenas posteriores de Medicina y Psicología | Requiere primero una evaluación íntegra compatible |
| Tablas operativas de instrumentos, personal, residentes y atención | Fuentes originales con sus FK | Su contenido actual no sustituye una copia histórica |
| `hechos_instantanea_experta` | Hechos inferenciales con un origen instrumental definido | No convertir cada atributo de respuesta en una fila genérica campo/valor |

## Diseño explícito candidato para igualdad histórica completa

La revisión de lectura propone nueve relaciones. Es una alternativa para conservar todo el contrato actual, **no una afirmación de mínimo universal ni una recomendación aprobada de añadirlas**.

| Relación candidata | Grano y unicidad | Atributos de contenido fijo |
| --- | --- | --- |
| `instrumentos_instantanea_experta` | Evaluación + instrumento original | FK original, código, nombre, tipo, edición, descripción, máximo y estado observados |
| `autores_instantanea_experta` | Evaluación + personal original | FK original y nombre/apellidos observados, sin repetirlos por aplicación |
| `aplicaciones_instantanea_experta` | Evaluación + aplicación original | FK original, instrumento/autor copiados, atención, fecha, puntuaciones, clasificación, interpretación, estado y observación originales |
| `preguntas_instantanea_experta` | Evaluación + pregunta original | FK original, instrumento copiado, código, enunciado, dominio, tipo, máximo, orden y estado |
| `opciones_instantanea_experta` | Evaluación + opción original | FK original, pregunta copiada, nombre, valor, puntaje, orden y estado |
| `respuestas_instantanea_experta` | Aplicación copiada + respuesta original | FK originales, alternativas escalares fijas de respuesta, puntaje y observación, incluso cuando no se admiten para calcular |
| `selectores_instrumentales_instantanea` | Perfil conservado + entrada del método | Instrumento, método, perfil, pregunta y campo autorizado, aunque no exista respuesta |
| `mediciones_instrumentales_instantanea` | Aplicación + método + componente + medida | Bruto, estado, esperado, DE residual, z, dirección y límites/tipo percentilar |
| `motivos_instrumentales_instantanea` | Propietario explícito + orden | Código de motivo y entrada afectada; exactamente una FK propietaria entre aplicación, selector o medición |

PK `cod_* string(20)`, FK restrictivas e indexadas y claves alternativas anteriores. Sin cascadas, JSON/EAV ni timestamps automáticos. Las colecciones independientes de respuestas, medidas y motivos no se cruzan en una sola fila: evitar ese producto cartesiano preserva la 4FN.

Una respuesta incoherente debe conservar sus referencias incoherentes y el motivo de rechazo. No se corrige al copiar para fingir una fuente válida. Debe resolverse y probarse el cierre de referencias originales de esas fuentes descartadas, sin incorporar datos de otro residente.

Seis columnas candidatas adicionales:

- `evaluaciones_expertas`: `fecha_nacimiento_original date nullable` y `cod_usuario_ejecucion string(20) nullable`, FK restrictiva a `usuarios`.
- `versiones_modelo_experto`: `revision_motor char(40) nullable`, `version_captura string(30) nullable`, `huella_documento_conceptual char(64) nullable` y `huella_matrices_conceptuales char(64) nullable`.

Antes de una propuesta de migración deben fijarse los nombres físicos definitivos, precisión, nulabilidad por contrato, checks, unicidades cruzadas, orden y política de inmutabilidad. Las filas históricas existentes no se completan con valores actuales.

## Alternativas para reducir inventario

- Ocho relaciones si se conserva solo la FK del autor y se diferencia expresamente su identidad nominal actual de la histórica. Cambia el alcance de la igualdad del contrato.
- Ocho relaciones si los selectores completos pertenecen a un release inmutable recuperable, con garantía de retención y replay probado. La configuración mutable actual no satisface esa garantía.
- Ampliar `evidencias_cognitivas_instantanea` para medidas normativas ahorraría una relación, pero mezclaría hallazgo cognitivo con normalización numérica; no se recomienda sin rediseñar y aprobar ese contrato.

La candidata completa llevaría a **115 tablas de negocio**; cualquiera de las variantes de ocho, a **114**. Ningún conteo está autorizado. Se mantiene el inventario de 106 mientras se decide el alcance histórico necesario.

## Trabajo técnico previo sin alterar el esquema

1. Definir un DTO canónico con columnas explícitas: decimales originales como cadenas, booleanos explícitos y NULL distinto de cero. No hashear un `getRawOriginal()` abierto dependiente del motor SQL.
2. Incluir también preguntas seleccionadas sin respuesta. La consulta actual carga preguntas presentes en respuestas; esto no conserva todos los selectores del método.
3. Conservar cálculos finitos con exactitud acordada; para igualdad de floats, reutilizar la representación IEEE-754 del codec existente. Las fechas mantienen UTC, microsegundos, zona y offset.
4. Archivar código, método, red y fuentes documentales de cada release de forma recuperable. Una huella sin conservar el artefacto no permite reproducirlo.
5. Definir un único contrato de lectura/escritura instrumental; no retirar guardas del almacén sintético para aparentar compatibilidad.

## Aceptación antes de declarar conservación

Guardar → recuperar → comparar entrada y salida completas; cambiar todas las fuentes actuales y comprobar que el histórico permanece igual; reintento idéntico y conflicto bajo la misma identidad; rollback intermedio; cruce de residente y versión; concurrencia; retirada de conocimiento conservando lectura y revisión posterior MED/PSI. Verificar en SQLite y PostgreSQL desechables.

Este diseño no resuelve IADL, los soportes C01–C05, la falta de mediciones originales ni los once componentes pendientes. No se pide una aprobación profesional previa para desarrollar; sí se mantiene la decisión explícita de la propietaria antes de una nueva ampliación estructural congelada.
