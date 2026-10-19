# Fase 2 — Comprobación de adquisición y métricas 60+

Fecha: 19/10/2026. Incremento sobre `40219c13`. Alcance: completar el control técnico de faltantes desde las fuentes autorizadas, contrastar el paquete disponible y entregar el diccionario de adquisición de los 23 métodos. La fase 2 institucional sigue abierta hasta vincular los protocolos y mediciones originales que no constan en las fuentes aportadas.

## Entregado y checklist

- [x] Reexaminar los cuatro CSV instrumentales aportados, sin ejecutar sus scripts ni modificar registros.
- [x] Documentar requisitos exactos de los **23 métodos / 19 componentes**, identidad, entradas, tipos, columnas, edades, referencias y límites.
- [x] Comprobación por aplicación de identidad declarada, selectores, respuesta, tipo, aplicabilidad y causas del cálculo.
- [x] Mantener separados aplicador original, transcriptor, fecha clínica y fecha técnica; mostrar estado documental y conservar las referencias a preguntas sin respuesta.
- [x] Medir completitud de entradas para aplicaciones de 60 o más años; cero y «No» cuentan como datos, NULL permanece ausente.
- [x] Integrar la comprobación en la consulta institucional existente, sobre la misma captura autorizada del perfil y de la inferencia.
- [x] Evitar que la misma aplicación se cuente dos veces; sin denominador se devuelve NULL y se informa cuántas aplicaciones carecen de extracción.
- [ ] Cotejar la identidad y los valores efectivamente administrados que no se encuentran en el paquete.
- [ ] Configurar los vínculos institucionales exactos de instrumento/versión/pregunta; no inferirlos desde un título o una alternativa bibliográfica.

No se cambia el esquema, catálogo operativo, reglas clínicas, permisos ni datos históricos. El inventario continúa en 106 tablas de negocio. Se reutilizan el registro de métodos, el extractor, la captura autorizada y la red existentes; no se crea otro normalizador o importador.

## Resultado del cotejo del paquete

Esta revisión corresponde a los archivos aportados en Temp, **no** a un nuevo inventario de la base instalada. Se conserva la declaración de la propietaria sobre su procedencia real; las marcas editoriales no se usan para descartar observaciones ni para acreditar un instrumento diferente.

| Archivo / comprobación | Resultado |
| --- | ---: |
| `instrumentos.csv` | 19 instrumentos |
| `preguntas_instrumento.csv` | 19 preguntas, todas OPCION |
| `aplicaciones_instrumento.csv` | 286 aplicaciones |
| `respuestas_instrumento.csv` | 286 respuestas |
| Respuestas con `valor_texto` | 286 |
| Respuestas con `valor_numero`, `valor_logico` o `puntaje` | 0 en cada columna |
| Pares aplicación/pregunta repetidos | 0 |
| Respuestas sin aplicación o pregunta referenciada en estos CSV | 0 |

La ausencia de aciertos, tiempos y condiciones tipadas en estos archivos no permite reconstruirlos desde una respuesta nominal. El [diccionario de los 23 métodos](DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md) completa las **definiciones** faltantes mediante el registro investigado; no atribuye a residentes una forma/edición que no figure en sus originales. Los conteos no sustituyen el cotejo de fechas, autoría, protocolos y documentos de cada expediente.

Evidencia privada: `storage/app/private/adquisicion-paquete-auditoria.json`, con tamaños, SHA-256 y conteos agregados de las cuatro fuentes. Ninguna respuesta, nombre ni expediente clínico se incorpora a estos documentos o al commit.

## Investigación y decisiones técnicas

La ficha [OMS, Ageing and health, 1/10/2025](https://www.who.int/news-room/fact-sheets/detail/ageing-and-health) utiliza la cohorte de 60 o más años y explica la heterogeneidad del envejecimiento. Se conserva la población solicitada **edad ≥ 60 en la aplicación**, sin asumir capacidades o un umbral clínico por edad.

Las [directrices ITC de adaptación, 2.ª edición, versión 2.4, 2017](https://www.intestcom.org/files/guideline_test_adaptation_2ed.pdf), A-2, SSI-1/2 y Doc-1/2, requieren documentar condiciones, contexto, evidencia de comparabilidad y límites de interpretación. Por eso los métodos conservan su edición, población y restricciones; no se trasladan normas extranjeras por compartir el idioma o superar 60 años. La selección de un perfil es una declaración técnica que debe cotejarse con el original; el estado `COINCIDE_CON_METODO` no certifica ese cotejo.

La presente revisión reabrió y leyó OMS e ITC. NIA devolvió verificación de JavaScript y NICE un error de acceso; no se atribuyen nuevos parámetros a páginas que no se pudieron leer. Los métodos y tablas primarias ya revisados en fase 3 se reúnen sin modificar sus ecuaciones en el diccionario, con enlaces directos y sus localizadores disponibles. El [manual investigado 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md) conserva la investigación detallada de esas referencias.

Los porcentajes siguientes son **definiciones de ingeniería de datos del proyecto**, no umbrales de una guía médica ni medidas de sensibilidad, especificidad o validez clínica.

## Contrato implementado

`PerfilInstrumentalCognitivo.construir()` devuelve ahora `comprobacion_adquisicion`, contrato `COMPROBACION-ADQUISICION-1`. `ConsultarPerfilInstrumental` ya lo incorpora en la respuesta de la consulta institucional y en el modelo de la pantalla existente. Este incremento no diseña una nueva sección visual ni acredita su QA.

Por aplicación se informa:

1. PK, instrumento y versión, edad histórica, perfil exacto y fuente del método. Si no hay extracción, se mantienen los motivos de lectura; no se adivina un método.
2. Identidad requerida y declarada, con `SIN_DECLARACION`, `INCOMPATIBLE_CON_METODO` o `COINCIDE_CON_METODO` por campo.
3. Entradas del método con tipo/columna, pregunta, respuesta y uno de cuatro estados: `SIN_SELECTOR`, `SIN_RESPUESTA_COMPATIBLE`, `VALOR_AUSENTE_O_TIPO_INVALIDO`, `DATO_TIPADO_DISPONIBLE`. Una pregunta sin respuesta conserva su PK desde el selector.
4. Aplicabilidad etaria y causas de cada cálculo **copiadas del extractor/normalizador existente**. El comprobador no calcula otra norma ni inventa la causa del resultado.
5. Aplicador, fecha clínica completa, transcriptor e instante técnico si constan, FK/estado documental y disponibilidad de localizador. No se entregan rutas de archivos.

`DATO_TIPADO_DISPONIBLE` acredita presencia/tipo en la extracción, no veracidad, rango clínico o aplicabilidad. Por ejemplo, «No» a lectoescritura está presente pero puede impedir el uso de una norma; 13 aciertos es un entero capturado, pero no es admisible en un ensayo cuyo máximo es 12. Ambas causas siguen visibles en el cálculo. El estado documental no se convierte en obligación retrospectiva para una captura propia que no requiera transcripción.

## Métricas y denominadores

| Métrica | Numerador / valor | Denominador y excepciones |
| --- | --- | --- |
| Completitud de entradas 60+ | Entradas con fuente y valor tipado no NULL | Suma de entradas requeridas por las extracciones disponibles de aplicaciones con edad histórica ≥60. No estima entradas de instrumentos sin perfil |
| Porcentaje de completitud | 100 × disponibles / requeridas, dos decimales | Si requeridas=0: NULL, nunca 100% de éxito vacío |
| Entradas faltantes o inválidas | Requeridas − disponibles | Mismo universo; no confundir con todos los faltantes de la historia |
| Aplicaciones 60+ sin extracción | Aplicaciones de esa edad con `extraccion=null` | Se informan aparte para que no desaparezcan del reporte aunque no pueda calcularse su número de entradas |
| Cobertura comparativa 60+ existente | Componentes distintos con alguna comparación normalizada de aplicaciones ≥60 | 19 componentes; repetir una aplicación, medida o alternativa no aumenta componentes |

Completitud cuenta **entradas por aplicación**, cobertura cuenta **componentes distintos**. No se suman ambos porcentajes. Una identidad incompatible puede tener 100% de entradas y ningún cálculo admisible. Una edad desconocida se informa como desconocida, sin incorporarla a la cohorte. Las aplicaciones menores de 60 permanecen consultables y fuera del denominador 60+; cada referencia conserva además su rango particular y edades excluidas.

Ejemplo sintético reproducible: HVLT-R requiere 11 entradas en este contrato. Con cero en un ensayo, «No» documentado y una condición sin respuesta, existen 10/11 entradas: **90,91% de completitud**, aunque no haya componentes normalizables. El caso se comprueba desde SQL y no modifica la historia al consultar.

## Seguridad, integración y conservación

La entrada procede de `ConsultarConocimientoCognitivo` → `CapturasInstrumentalesResidente`: cuenta vigente, Policy del residente y cinco permisos de fuentes. Sin acceso, la consulta no entrega perfil ni comprobación y no consulta las aplicaciones/respuestas. El motor puro rechaza fuentes marcadas como no disponibles.

La comprobación reutiliza la captura única del incremento anterior: no abre SQL, no descarga documentos, no consulta usuarios adicionales y no se ejecuta por una ruta nueva. La respuesta contiene referencias ya disponibles en la captura autorizada. Los tests verifican ausencia de escrituras al consultar y preservación de las fechas y autores originales.

La nueva proyección se reconstruye desde esa captura y el registro versionado. No se presenta como evaluación conservada: la vinculación institucional del release y la representación relacional completa siguen en fase 6, según su [análisis estructural](CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md). Tampoco activa reglas o Petersen.

## Matriz de aceptación y verificación

| Requisito | Componente / archivo | Evidencia ejecutable |
| --- | --- | --- |
| Completitud, faltantes y procedencia | `ComprobacionAdquisicionInstrumental.php` | `ComprobacionAdquisicionInstrumentalTest`: completo; selector/respuesta/NULL; cero/No; identidad y rango; 59/60/desconocida; vacío; duplicado; acceso |
| Integración de cinco dominios y métricas | `PerfilInstrumentalCognitivo.php` | `PerfilInstrumentalInstitucionalTest`, `AplicabilidadGeriatricaTest`, tests de cálculos y referencias conectados al perfil |
| Captura SQL, permisos y cero escrituras | Consulta institucional existente | `CapturaInstrumentalInstitucionalTest::test_comprobacion_desde_sql_detalla_faltante_y_fuentes_sin_escribir_historia`; revocación de permiso extendida |
| Una única captura y reglas originales | Coordinación existente sin cambios | Regresión `ConocimientoCognitivoInstitucionalTest` |
| Contrato detallado por método | Registro + catálogo versión 8 | Diccionario de 23 métodos con huella del registro, 19 pertenencias y bibliografía vinculada |

Verificación de HEAD `40219c13` más los cinco archivos PHP propios en candidato aislado, sin `.env`, dependencias copiadas y almacenamiento de pruebas separado por motor:

| Verificación | Resultado |
| --- | --- |
| SQLite `:memory:`, selección relacionada | **182 pruebas / 6378 aserciones PASS**, 1 min 52 s |
| PostgreSQL desechable, misma selección | **182 pruebas / 6377 aserciones PASS**, 2 min 18 s; parada del servidor confirmada |
| Unitarias iniciales de comprobación, edad, perfil y referencias | **116 pruebas / 1187 aserciones PASS**; incluidas después en la selección relacionada, no se suman como casos nuevos |
| Independencia del motor y dominio O.R.I.O.N. | **PASS** en ambos scripts |
| Pint, cinco archivos PHP del incremento | **PASS** |
| Formato de diff y enlaces locales de los documentos | **PASS**; diccionario con 23 métodos y 19 componentes |

Comando de selección: `php vendor/phpunit/phpunit/phpunit --filter 'ComprobacionAdquisicionInstrumentalTest|AplicabilidadGeriatricaTest|PerfilInstrumentalInstitucionalTest|InterpretacionInstrumentalFaseTresTest|RecuerdoConClavesFcsrtInvestigadoTest|NormalizacionReferenciasEspanaTest|CapturaInstrumentalInstitucionalTest|ConocimientoCognitivoInstitucionalTest|ApplicationBoundaryTest|FolderOrganizationTest'`. PostgreSQL se ejecutó con `scripts/probar-postgres.ps1 -Filtro` y esa misma selección, sin utilizar la base operativa. XML/logs privados: `storage/app/private/adquisicion-{sqlite,postgres}.junit.xml` y `.log`.

Revisión estática del implementador: sin escrituras ni SQL nuevos, rutas/archivos no expuestos, autorización previa preservada, cero/No/NULL y denominadores explícitos. No se atribuye a este incremento una revisión clínica ni un gate independiente nuevo. No se ejecutó la suite global. Build y QA visual no aplican a esta proyección del backend sin cambios en vistas o assets; no se reutiliza un build anterior para afirmar una verificación nueva.

## Continuidad de las fases

Este incremento cierra la **comprobación técnica de adquisición y su diccionario**. No marca completa la fase 2 institucional: aún faltan los protocolos y mediciones originales identificables. La fase 3 conserva su cierre técnico; la fase 4 dispone del conector y necesita sus vínculos reales. Las fases 5/6 necesitan, respectivamente, soportes de requisitos y representación íntegra aprobada. Se continúa con trabajo independiente sin rellenar valores de residentes desde investigación ni introducir una nueva exigencia de firma profesional previa al desarrollo.
