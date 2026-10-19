# Protocolos y fichas de captura cognitiva — V1

Fecha: 18/10/2026. Elaborado por solicitud de la propietaria de crear los protocolos faltantes. Especificación de captura y trabajo; no contiene puntuaciones de residentes, firmas, diagnósticos ni una acreditación profesional. No modifica catálogos ni la estructura de la BDD.

## Uso

Registrar la medición obtenida en una aplicación identificable, o transcribir el protocolo original conservando su fecha y autor. Separar fecha clínica de fecha de digitación. Las condiciones desconocidas se dejan sin dato; una tarea no administrada no se puntúa como cero. Una nota de observación se conserva como observación. El procedimiento de administración y corrección de cada instrumento procede de su manual legítimo; esta ficha no reproduce reactivos, listas, láminas ni rúbricas protegidas.

La evaluación reúne historia, mediciones, funcionalidad y posibles factores que afectan el rendimiento. Una prueba breve o un único resultado no resuelven por sí solos la valoración completa. Fuentes: [NICE NG97](https://www.nice.org.uk/guidance/ng97/chapter/Recommendations) y [matriz de evidencia del proyecto](INFORME_INSTRUMENTAL_Y_NORMALIZACION_2026_10_18.md).

## Cabecera de cada aplicación

| Campo | Registro original |
| --- | --- |
| Código del residente | ____________________ |
| Profesional que realizó la aplicación | ____________________ |
| Fecha y hora originales, con zona | ____________________ |
| Documento/protocolo de origen | ____________________ |
| Instrumento, edición, forma e idioma | ____________________ |
| Tareas administradas y tareas omitidas | ____________________ |
| Escolaridad completada en años; fuente | ____________________ |
| Idioma habitual y condiciones de comprensión | ____________________ |
| Condiciones sensoriales/motoras y apoyos utilizados | ____________________ |
| Interrupciones, desviaciones o motivos de no interpretación | ____________________ |
| Estado de la aplicación y vínculo a rectificación, si existe | ____________________ |

## Ficha ejecutable de captura HVLT-R

Identidad requerida por el método programado: HVLT-R, forma 5, español; referencia boliviana de 2015, contrato `HVLT-R-BO-2015-F5`. Dos componentes: aprendizaje total y recuerdo diferido libre. El cálculo necesita identificar exactamente las preguntas de captura y sus respuestas; se configura por sus PK y versión, no por semejanza de nombres.

| Entrada del método | Tipo y condición | Valor original |
| --- | --- | --- |
| `ensayo_1` | Entero de aciertos, 0–12 | ______ |
| `ensayo_2` | Entero de aciertos, 0–12 | ______ |
| `ensayo_3` | Entero de aciertos, 0–12 | ______ |
| `recuerdo_diferido` | Entero de aciertos, 0–12 | ______ |
| `demora_minutos` | Tiempo documentado de demora; aplicabilidad entre 20 y 25 minutos | ______ |
| `escolaridad_anios` | Años completos documentados; referencia desde un año | ______ |
| `espanol_nativo` | Sí / No / Sin dato | ______ |
| `nacimiento_bolivia` | Sí / No / Sin dato | ______ |
| `residencia_bolivia` | Sí / No / Sin dato | ______ |
| `lectoescritura` | Sí / No / Sin dato | ______ |
| `administracion_estandarizada` | Sí / No / Sin dato, según protocolo legítimo | ______ |
| Edad en la aplicación | Derivada de nacimiento y fecha originales; referencia 18–95 años | ______ |

Si se documentan las condiciones, el motor suma los tres ensayos para aprendizaje y compara ambos resultados con las regresiones publicadas para Bolivia. Devuelve puntuación bruta, referencia, desviación residual y z. Si falta una condición o no se satisface la aplicabilidad, conserva el original y explica por qué no normaliza. No asigna por esa sola comparación una clasificación A/P ni DCL.

Referencia y límites de esa ficha: [Arango-Lasprilla y colaboradores, 2015, DOI 10.3233/NRE-151286](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151286). No contiene una lista de palabras para administrar el examen. Materiales y condiciones del titular: [PAR HVLT-R](https://www.parinc.com/products/HVLT-R).

## Matriz de las diecinueve fichas

Los candidatos siguientes proceden de la investigación del proyecto. La ficha identifica qué dato original hace falta; no declara que esos instrumentos ya se aplicaron. Un candidato no tiene normalización ejecutable hasta identificar su edición, norma y regla de corrección aplicable. La procedencia y los límites de cada candidato están en el [informe instrumental, con sus 25 referencias](INFORME_INSTRUMENTAL_Y_NORMALIZACION_2026_10_18.md).

| Componente | Dato que debe capturarse | Referencia candidata / situación de cálculo |
| --- | --- | --- |
| MEM-S01 Adquisición | Aciertos de cada ensayo y forma | HVLT-R forma 5; cálculo implementado bajo aplicabilidad |
| MEM-S02 Recuerdo diferido libre | Aciertos y demora real | HVLT-R forma 5; cálculo implementado bajo aplicabilidad |
| MEM-S03 Recuerdo con claves | Ensayos, ayuda y respuesta con claves | FCSRT; requiere edición, norma y mediciones propias |
| MEM-S04 Reconocimiento | Aciertos y falsos positivos por condición | Instrumento que mida reconocimiento; no usar regresiones de recuerdo libre |
| ATE-S01 Atención sostenida | Aciertos, errores y duración de la condición | Ruff 2 y 7; confirmar manual y referencia aplicable |
| ATE-S02 Atención selectiva | Desempeño por condición y distractores | Ruff 2 y 7; identificar demanda mixta antes de integrar |
| ATE-S03 Atención dividida | Aciertos/errores y condición dual | BTA; identificar versión y comparación normativa |
| EJE-S01 Inhibición | Desempeño de condiciones de interferencia | Stroop; registrar versión y mediciones de cada condición |
| EJE-S02 Flexibilidad | Tiempo, errores y finalización | TMT B o M-WCST; no intercambiar sus puntuaciones |
| EJE-S03 Memoria de trabajo | Respuestas y puntuación según tarea | Subprueba documentada; pertenencia primaria ejecutiva |
| EJE-S04 Planificación | Movimientos, errores y tiempos definidos por tarea | Torre de Londres; identificar edición y rúbrica |
| EJE-S05 Razonamiento | Respuestas por ítem y corrección legítima | Subprueba de abstracción identificada; método pendiente |
| LEN-S01 Denominación | Respuesta espontánea y ayudas por ítem | BNT; identificar longitud y edición |
| LEN-S02 Fluidez verbal | Palabras válidas, repeticiones, intrusiones y tiempo | FAS/animales; condición y norma propias |
| LEN-S03 Comprensión | Ítems administrados, errores y control auditivo | Token Test; identificar norma aplicable |
| LEN-S04 Repetición | Respuestas literales y corrección de la subprueba | WAB-R; identificar versión e idioma |
| LEN-S05 Discurso | Muestra de una tarea definida y corrección documentada | WAB-R u otra rúbrica identificada; una nota libre no equivale a administrarla |
| VIS-S01 Relaciones espaciales | Aciertos de subprueba, forma y control visual | VOSP/JLO; instrumentos diferentes |
| VIS-S02 Visuoconstrucción | Producción original, fase y corrección por elementos | Rey-Osterrieth, fase de copia; registrar versión |

Para cada fila adjuntar: identidad de aplicación, medición, unidad, instrumento/edición/norma, condiciones, oportunidad, autor, fecha, soporte original y causa de exclusión. Una aplicación que aporte a varios componentes conserva su identidad compartida; no se cuenta varias veces como soporte independiente.

## Ficha de soportes para Petersen

Esta ficha mantiene los cinco requisitos del contrato del proyecto. C05 es un control de calidad e interpretabilidad de ingeniería; no se presenta como un criterio médico universal ni como un baremo. Estado inicial de cada requisito: pendiente de soporte.

| Requisito | Documentación que debe vincularse | Soporte, fecha y autor |
| --- | --- | --- |
| DCL-C01 Cambio corroborado | Historia de cambio, informante/origen y comparación con funcionamiento previo | ____________________ |
| DCL-C02 Alteración objetiva | Mediciones interpretables y norma/método que sustentan el estado de uno o más dominios | ____________________ |
| DCL-C03 Independencia relativa | Actividades instrumentales observadas, nivel previo, oportunidad, ayuda y causas examinadas | ____________________ |
| DCL-C04 Evaluación diferencial | Revisión documentada de posibles explicaciones e impedimentos, incluido el alcance respecto de demencia | ____________________ |
| DCL-C05 Interpretabilidad/calidad | Condiciones y calidad suficientes de las tareas, factores que afectan cada aplicación y exclusiones | ____________________ |

No acreditar un requisito por ausencia de filas, por no dispararse una regla o por reutilizar el resultado esperado del caso. No sustituir IADL por un total de actividades básicas. Conservar discrepancias y soporte compartido; cuando falta fundamento, explicar la abstención.

## Checklist para integrar una aplicación

- [ ] Cabecera y profesional originales identificados.
- [ ] Instrumento, edición, forma e idioma comprobados.
- [ ] Mediciones copiadas literalmente o recogidas en la aplicación actual.
- [ ] Condiciones de aplicabilidad documentadas; ausencia distinta de cero/No.
- [ ] Mapeo explícito por instrumento, versión y pregunta.
- [ ] Cálculo reproducible y causas de no interpretación comprobadas.
- [ ] Conservación completa en contrato institucional representable, antes de confirmar guardado.
- [ ] Revisión posterior de Medicina y Psicología, cada una sobre la evaluación conservada exacta.

Las fichas se han creado vacías deliberadamente: son el soporte para obtener mediciones. Una puntuación propuesta o generada por software no constituye una medición del residente.
