# Sistema experto — catálogo de fuentes y hechos del incremento

Fecha: 18/10/2026. **Adquisición contextual común y captura instrumental integradas para el prototipo**. No reemplaza las fuentes rectoras ni acredita interpretaciones clínicas de los campos. La entrada de cinco dominios permanece aislada y solo acepta casos artificiales.

## Fuentes permitidas y capacidad localizada

| Fuente | Implementación reutilizada | Contrato y límite actual |
|---|---|---|
| `controles_cognitivos` | `AdaptadorFuentesCOGMEM::extraerControl`, `AdaptadorControlesCognitivos`, `ExtractorVariableMapeada` | Campos explícitos, bruto/tipado, fecha, autor, atención/jornada, estado y mapeo. No convierte observaciones generales en A/P ni atribuye subtipos por el nombre de una columna. |
| `instrumentos` y `aplicaciones_instrumento` | `AdaptadorFuentesCOGMEM::extraerAplicacion`, `AdaptadorInstrumentos`, inspector D-144 y lector G08 | Instrumento/edición exactos, aplicación y autor/procedencia disponibles; un total no equivale a un dominio. La entrada integral nueva usa una proyección descartable de capturas ficticias. |
| `preguntas_instrumento`, `respuestas_instrumento`, `opciones_pregunta` | `InspectorComponenteInstrumental`, `AdaptadorInstrumentos`, `LectorSQLiteG08Experimental` | Pertenencia aplicación/pregunta/instrumento/opción, respuestas originales y patrón exacto TEST_ONLY. No copia reactivos protegidos, deriva un baremo ni convierte una respuesta faltante en NULL observado. |
| `seguimientos_pedagogicos` | `AdaptadorSeguimientosPedagogicos`, extractor por lotes | PK original, actividad como episodio y campos autorizados. Atención general o ejecución de tarea no acredita un subcomponente específico. |
| `valoraciones_funcionales` | `AdaptadorValoracionesFuncionales` | Conserva BADL/contexto, tipos y ausencia. No reemplaza IADL ni acredita DCL-C03. |
| `controles_funcionales_iadl` | `AdaptadorIadlExperimental` y pruebas de la extensión aprobada | Fuente descriptiva diferenciada de BADL. No fabrica episodio ausente ni declaración de autonomía por falta de registros. |
| `valoraciones_psicologicas` | `RecolectorContextoPerfilExperimental`, Model `ValoracionPsicologica` | Captura 11 campos textuales: ánimo, afecto, ansiedad, apatía, percepción, conducta, comunicación, interacción, impresión, conclusión y recomendación. Conserva autor, atención, estado y fecha; no interpreta narrativa ni la convierte en A/P. |
| `diagnosticos` | Mismo recolector, Model `Diagnostico` | `codigo_clinico`, `nombre`, `tipo`, `certeza`, `observacion`; contexto sin inferencia de confusores. |
| `antecedentes_clinicos` | Mismo recolector, Model `AntecedenteClinico` | `tipo_antecedente`, `descripcion`, `fuente_informacion`, `observacion`; fecha de referencia con precisión de día y nulabilidad conservadas. No inventa un episodio. |
| `registros_sueno` | Mismo recolector, Model `RegistroSueno` | Horas, despertares, insomnio, somnolencia, agitación, calidad y observación con tipos explícitos; jornada original nullable. No deduce fatiga invalidante desde esos datos. |
| Comparaciones longitudinales | `ComparadorLongitudinalExperimental` | Compara contexto, oportunidad, versión, fechas y episodio; conserva valores sin interpretar magnitud, progresión o ventana temporal. |
| Referencias independientes DCL | `AdaptadorEstadosPetersenExperimental` | Referencias ficticias explícitas A/P/U/I/X y C01–C05, con auditoría de admitidas/rechazadas. No proceden automáticamente de los hallazgos descriptivos del perfil. |

Las pruebas `FuentesOperativasTest`, `MapeoFuentesTest` y `AdaptadorInstrumentosTest` recorren los contratos existentes. `AdquisicionPerfilExperimentalTest` acredita además ocho Models → captura → incorporación → perfil → replay. El recolector reutiliza listas/tipos de los cuatro adaptadores cognitivo/pedagógico/funcional/IADL; los cuatro contratos de contexto nuevos son explícitos en su método `contratos`.

## Representación de la salida nueva

`PERFIL-EXPERIMENTAL-1` conserva:

- Caso, evaluación, corte y manifiesto Memoria/G09/DCL/puente.
- `fuentes_examinadas`: capturas G09 originales, fuente técnica de Memoria y referencias DCL suministradas. Una captura rechazada sigue consultable con sus valores originales.
- `hechos_instrumentales`: identidad técnica determinista, origen, valores y tipos originales, valor semántico si el inspector lo produjo, fecha, autor nullable, procedencia sintética declarada, estado operacional, perfil/versionado, criterio, regla, rol, episodio y evidencia generada/admisibilidad/motivos.
- Evidencias admitidas, exclusiones G08/G09/Memoria y auditoría DCL. Faltantes de tareas, contexto, requisitos y dominios DCL.
- 19 subcomponentes como proyección conceptual: los cuatro MEM mantienen `evaluado=false`, resultado NULL y `CONTRATO_DE_TAREA_PENDIENTE`. El resultado D-131 del dominio no se copia a esos cuatro subcomponentes.
- Quince reglas G09 con solicitud/evaluación separadas, condiciones y soportes efectivos. Una regla puede cumplir y no emitir consecuencia por contradicción; la traza y la causa se conservan.
- Perfil, requisitos, patrón/abstención, comparaciones, resumen desde resultados y traza técnica.
- `base_contextual`: bruto/tipado por campo, precisión de fecha original, zona, autor identificable aunque hoy esté inactivo, estado original/utilizable triestado, episodio y tipo de episodio. Cada lectura idéntica se deduplica; una relectura incompatible se rechaza. Compartir atención/actividad no acredita independencia.
- `controles_y_confusores_tareas`: atestaciones sintéticas A01–A14, confusores por tarea, causa de exclusión y modificador de advertencia. La estructura del lector/inspector sigue validándose aunque las atestaciones sean true.

`autor=null` en una captura de laboratorio no se reemplaza por un profesional inventado: la procedencia se identifica como `CAPTURA_SINTETICA_DECLARADA`. Eso no acredita autoría clínica. Un contenedor de capturas NULL/mal tipado se rechaza; un valor de respuesta NULL se conserva y no se admite como respuesta interpretable.

## Invariantes ya verificadas

Tipos y mapeos exactos; no NLP; fecha válida y dentro del corte; contexto de Memoria separado de G09; desconocidos preservados; duplicados y reutilización indebida de aplicaciones rechazados; no normalidad por falta de soporte; referencias DCL independientes; replay sin lectura del expediente ni escritura institucional.

La comparación de revisiones usa los mismos originales, incluso cuando se regeneran las PK. Compara contexto, fecha exacta, tipo/bruto, procedencia y variable semántica; una batería alterada entre revisiones se rechaza. No compara una versión con otros datos para aparentar mejora.

Antes de ejecutar la batería también rederiva cada evidencia desde su fuente y mapeo. Se rechaza un valor semántico o una admisibilidad suministrados que no correspondan a esa derivación, aunque el dato bruto sea idéntico. Esta protección tiene regresión específica y revisión independiente posterior al arreglo.

## Alcance de la adquisición común

El recolector solo permite consola/testing/base desechable, actor técnico revalidado y selección explícita de PK sintéticas. Consulta por lotes, identifica autor y atención del caso y verifica metadatos de la segunda lectura. Una fuente futura o sin fecha mantiene identidad/metadatos/motivo pero no aporta valores al perfil. Una fecha imposible, un bruto no finito o un contrato/rol/tabla incorrectos se rechazan.

El contexto añadido se conserva como `CONTEXTO_SIN_INFERENCIA_AUTOMATICA`; sus valores semánticos y mapeos son NULL mientras no haya contrato autorizado. Esto evita que una nota o un false sin oportunidad se convierta en una conclusión. No se sustituyen BADL e IADL.

## Pendientes que no se ocultan

La conservación integral en la BDD institucional sigue pendiente de un contrato aprobado para manifiesto y capturas originales. El prototipo sí conserva y consulta su instantánea completa en el archivo técnico privado cifrado permitido por el Prompt§14; no la reconstruye desde Models actuales. Véanse la [RFC](RFC_CONSERVACION_PERFIL_EXPERIMENTAL_2026_10_18.md) y la [aceptación integral](FASE01_ACEPTACION_INTEGRAL_2026_10_18.md). A01–A14 son declaraciones del laboratorio, no comprobación documental de licencias o aprobación clínica. No se activa ninguna inferencia asistencial por mejorar la extracción.
