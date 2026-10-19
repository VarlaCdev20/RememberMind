# Métodos y campos de evaluación cognitiva — V2

Fecha de revisión: 18/10/2026. Alcance: documentación científica y cálculo instrumental en RememberMind. Solicitud de la propietaria: investigar, documentar y completar los campos pendientes. Esta versión amplía el informe V1; no modifica historias, catálogos, permisos ni estructura BDD.

## Resultado de la investigación aplicada

Se completó el diccionario de captura de los 19 componentes, con tipos, unidades, identidad del instrumento, límites y fuente. El registro ejecutable pasa de un perfil a siete: HVLT-R y seis perfiles adicionales. Son **11 medidas normativas en ocho componentes**, distribuidos en los cinco dominios. El diccionario se consulta en «Perfil de mediciones por dominio → Campos y método de evaluación».

Los campos quedan definidos, pero sus valores originales no pueden obtenerse de publicaciones. No se atribuye a un residente una prueba, edición, duración, resultado o firma por haber localizado un método. Tampoco se identifica una aplicación por similitud de nombre. Los 11 componentes sin normalizador mantienen su causa concreta; no se completan con el promedio de otra prueba.

## Fuentes, población y aplicabilidad

Los seis estudios de 2015 consultados incluyen muestras comunitarias de adultos de 18–95 años, español nativo, nacimiento y residencia en el país de la norma, al menos un año de educación y lectoescritura. La selección de controles sanos mediante MMSE, PHQ-9 y Barthel describe la muestra; no obliga a que una persona evaluada tenga resultados normales antes de compararla. Una residencia geriátrica no queda validada como población por compartir país. [BTA, sección 2.1](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151283), [TMT, sección 2.1](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151284).

El motor verifica identidad y condiciones documentadas. Escolaridad cero queda fuera de estas referencias; no se reclasifica como escolaridad de 1–12 años. Se conserva la falta de dato y el motivo de no comparación. Los controles sensoriales, motores, de completitud y duración son controles de fidelidad de captura; no son nuevos umbrales clínicos.

## Métodos ejecutables y ecuaciones

`E=0` para 1–12 años completos de escolaridad y `E=1` para más de 12. Edad en años completos a la aplicación. Para las regresiones: `z=(bruto−predicho)/DE residual`. Se conserva la dirección; no se promedian pruebas ni se obtiene un score global.

| Perfil / componente | Medida y predicho boliviano | DE residual / localizador |
| --- | --- | --- |
| `HVLT-R-BO-2015-F5` / MEM-S01 | Suma de tres ensayos: `24.553−0.112×edad+3.264×E` | 4.213; tabla 2, [HVLT-R](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151286) |
| Mismo perfil / MEM-S02 | Recuerdo libre diferido: `9.702−0.064×edad`; demora 20–25 min | 2.424; tabla 4, misma fuente |
| `BTA-BO-2015-NL` / ATE-S03 | Total de formas N y L: `14.942−0.059×edad+1.943×E` | 3.786; tabla 2, [BTA](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151283) |
| `TMT-B-BO-2015` / EJE-S02 | Segundos de parte B: `57.619+2.448×edad−54.711×E` | 69.661; tabla 4, [TMT](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151284) |
| `BNT-BO-2015-60` / LEN-S01 | Aciertos espontáneos más adicionales con clave semántica: `45.102+3.746×E` | 8.732; tabla 2, [BNT estándar](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151278) |
| `FLUIDEZ-BO-2015-FAS-ANIMALES` / LEN-S02 | F: `10.600−0.046×edad+2.630×E` | 3.248; tabla 2, [fluidez](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151279) |
| Mismo perfil / LEN-S02 | A: `10.651−0.050×edad+3.435×E` | 3.441; tabla 4, misma fuente |
| Mismo perfil / LEN-S02 | S: `9.453−0.046×edad+3.404×E` | 3.405; tabla 6, misma fuente |
| Mismo perfil / LEN-S02 | Animales: `18.013−0.100×edad+4.398×E` | 4.552; tabla 8, misma fuente |
| `ROCF-BO-2015-COPIA-A` / VIS-S02 | Copia figura A: `33.123−0.147×edad+5.212×E` | 6.728; tabla 2, [ROCF](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151285) |
| `STROOP-BO-2015-GOLDEN` / EJE-S01 | Índice de interferencia `WC−W×C/(W+C)` | Intervalo percentilar de A35, [Stroop](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151281); no regresión ni z bolivianos |

En TMT-B **menor tiempo indica mejor rendimiento**; z positivo indica más lentitud respecto al predicho. El estudio usa un máximo de 300 s para B. El software conserva una interrupción como tal y no publica su tiempo censurado como una finalización exacta. BNT no hereda ajustes por edad de otros países ni admite BNT-15; la ayuda fonémica se conserva separada del total indicado. [TMT, sección 2.2 y tabla 4](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151284), [BNT, secciones 2.2 y 3](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151278).

Fluidez compara cada condición de 60 s por separado. Una condición incompleta deja su medida sin normalizar y no invalida las demás. Las cuatro conservan la misma aplicación y no representan cuatro episodios independientes. ROCF exige fase de copia, figura A y corrección de 18 elementos, máximo 36, en medios puntos; el programa recibe la corrección original, no genera ni interpreta dibujos. [Fluidez, sección 2.2](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151279), [ROCF, sección 2.2](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151285).

### Discrepancia localizada en la fórmula Stroop

La sección 2.2 del artículo de 2015 imprime `P+W`, sin definir P en ese párrafo. La formulación de Golden es `W+C`; se contrastó en el [estudio primario de Periáñez y colaboradores, DOI 10.1093/arclin/acaa034](https://academic.oup.com/acn/article-pdf/36/1/99/35858033/acaa034.pdf). Se registra la discrepancia en lugar de aplicar literalmente un denominador indefinido. El software usa W, C y WC correctos, tres condiciones de 45 s y denominador positivo.

Bolivia A35 publica puntos percentilares, sin ajuste por edad/escolaridad. El software devuelve el intervalo entre puntos publicados; fuera de P5–P95 deja un extremo sin estimar. En coincidencias devuelve el percentil aproximado de la tabla redondeada. **No interpola, extrapola, transforma a z ni importa tablas de otra edición o país.** La actualización normativa comercial de Stroop no sustituye esta referencia.

## Diccionario de los 19 componentes

Cabecera común obligatoria para una captura identificable: residente, aplicación, autor original, fecha/hora/zona, instrumento/edición/forma/idioma, referencia al protocolo, estado, escolaridad y su fuente, condiciones de comprensión/sensoriales/motoras y desviaciones. Los campos ausentes siguen ausentes. Los conteos se registran como enteros y las duraciones en su unidad original. Esta tabla define adquisición; los límites corresponden solo a las versiones indicadas.

| Componente | Campos completados en el diccionario | Estado y límite |
| --- | --- | --- |
| MEM-S01 | Tres ensayos de aciertos 0–12 | HVLT-R forma 5, ejecutable con contexto |
| MEM-S02 | Aciertos 0–12 y demora efectiva en minutos | HVLT-R forma 5, ejecutable con contexto |
| MEM-S03 | Libre y adicional con claves de cada ensayo, codificación, diferido y demora | [FCSRT NEURONORMA](https://doi.org/10.1093/arclin/acp041), 16 elementos en esa versión. No contar de nuevo lo recuperado libremente. Sin normalizador boliviano verificado |
| MEM-S04 | Aciertos de reconocimiento y falsas alarmas relacionadas/no relacionadas; demora | [HVLT-R](https://www.parinc.com/products/HVLT-R). Brutos separados; no usar la norma de aprendizaje/recuerdo para reconocimiento |
| ATE-S01 | Veinte ensayos: condición, objetivos disponibles, aciertos, errores, omisiones y segundos | [Ruff 2 y 7](https://www.parinc.com/products/RUFF2AND7), diez ensayos por condición, rango editorial 16–70; no extrapolar a mayores |
| ATE-S02 | Mismos ensayos, detección automática/búsqueda controlada identificadas | Misma fuente y aplicación Ruff; índices requieren manual. No duplicar soporte |
| ATE-S03 | Aciertos enteros N y L 0–10, audición documentada | BTA, total 0–20; ejecutable |
| EJE-S01 | W, C, WC y duración de cada condición | Stroop Golden 45 s; intervalo normativo ejecutable |
| EJE-S02 | Segundos B, errores y finalización | TMT-B; ejecutable para tiempo exacto compatible |
| EJE-S03 | Adaptación nacional, condición, puntajes por ensayo, bruto y longitud si procede | [WAIS](https://www.pearsonclinical.es/portals/0/docproductos/13534059942.pdf). Directo, inverso y secuenciación separados; no transferir normas entre ediciones |
| EJE-S04 | Movimientos, problemas resueltos, violaciones de regla/tiempo y tiempos por problema | [TOL-DX NEURONORMA](https://doi.org/10.1093/arclin/acp043). No confundir con [segunda edición](https://storefront.mhs.com/collections/toldx-2nd-ed). Sin norma boliviana verificada |
| EJE-S05 | Subprueba exacta, puntajes por elemento y bruto | Semejanzas o Matrices, edición concreta; no sumar sus brutos ni atribuir razonamiento general a M-WCST |
| LEN-S01 | Aciertos espontáneos y adicionales con clave semántica, ayudas separadas | BNT estándar 60; ejecutable |
| LEN-S02 | Válidas F/A/S/animales, duraciones y repeticiones/intrusiones separadas | Cuatro medidas ejecutables, sin promedio ni total normativo inventado |
| LEN-S03 | Versión, presentaciones, puntajes decimales por elemento y total; audición/visión | [Token NEURONORMA](https://doi.org/10.1093/arclin/acp039). No exigir enteros; máximo 36 solo en la forma correspondiente. Sin norma boliviana verificada |
| LEN-S04 | Forma completa/bedside, puntajes por elemento y total de repetición | [WAB-R](https://www.pearsonassessments.com/store/en/usd/p/100000194.html). Falta edición española y referencia compatible; no fijar máximo de otra forma |
| LEN-S05 | Tarea de discurso, referencia a muestra original, contenido y fluidez separados | Misma fuente WAB; una nota de cuidados no constituye la subprueba |
| VIS-S01 | VOSP o JLO, subprueba/forma, elementos administrados/correctos y control visual | [NEURONORMA](https://doi.org/10.1093/arclin/acp040), referencia española. JLO estándar 30; no trasladar ese máximo a VOSP |
| VIS-S02 | Puntaje de copia, 18 elementos revisados y referencia al original | ROCF figura A/copia; ejecutable |

Los manuales legítimos siguen definiendo administración y rúbricas. El código incorpora datos publicados y validación de captura, no reactivos, láminas o listas. La disponibilidad pública de una referencia no identifica qué instrumento recibió un residente ni acredita derechos de digitalización de su material.

## Campos de soporte C01–C05

Se especifican aquí para cerrar la adquisición documental; no son nuevos campos físicos ni se rellenan con valores supuestos. El contrato conserva identidad de soporte, fecha, autor y origen de cada afirmación. La interpretación y revisión deben señalar la evidencia utilizada.

| Requisito del proyecto | Campos y tipos que debe aportar el soporte original | No equivale a soporte |
| --- | --- | --- |
| C01 Cambio corroborado | Fuente/informante identificable; fecha o periodo; funcionamiento previo y cambio descrito; profesional y soporte original | Cantidad de observaciones sin cambio documentado |
| C02 Alteración objetiva | Aplicación y medidas originales; instrumento/edición/método/norma; condiciones; dominio y fundamento interpretativo | z aislado o etiqueta nominal de dificultad |
| C03 Independencia instrumental relativa | Actividad instrumental concreta; oportunidad; nivel previo/actual; ayuda requerida y causa; fecha/autor | Barthel u otra puntuación de actividades básicas |
| C04 Evaluación diferencial | Revisión profesional fechada; causas examinadas, hallazgos y conclusión respecto de demencia/otras explicaciones; soportes | Ausencia de un diagnóstico en una tabla |
| C05 Calidad/interpretabilidad | Aplicación; condiciones, interrupciones, omisiones y desviaciones; compatibilidad de método; motivo por medida descartada | Aplicación marcada COMPLETA sin documentar sus condiciones |

Los cuatro primeros apartados se apoyan en la evaluación cognitiva/funcional descrita por [Albert y colaboradores, NIA-AA 2011, secciones 2.1–2.2](https://www.med.upenn.edu/digitalneuropathologylab/assets/user-content/documents/educational-resources/the-diagnosis-of-mild-cognitive-impairment-due-to-alzheimer%27s.pdf). C05 es un control de calidad del proyecto, no un quinto criterio clínico universal. Las desviaciones orientativas del artículo no se convierten en un corte automático. Esta entrega no establece etiología Alzheimer ni firma decisiones humanas.

## Integración y conservación

El extractor común lee selectores explícitos `cod_pregunta` y columna tipada, con identidad exacta de instrumento y versión, manteniendo el valor SQL y su respuesta de origen. No acepta selectores repetidos, valores nominales como números, respuestas incoherentes ni identidades ambiguas. La edad usa nacimiento y fecha originales. Ningún contexto lógico se completa por defecto.

`config/sistema_experto.php` conserva `perfiles_instrumentales=[]` hasta contar con las PK y versiones originales identificadas; no se introducen catálogos ficticios para rellenarlo. Cada binding requiere `id`, `metodo`, `cod_instrumento`, `codigo_instrumento`, `version_instrumento` y `selectores`. `RegistroMetodosInstrumentales::metodos()` entrega los nombres/tipos exactos de entradas y la identidad del protocolo. Elegir el método requiere cotejar realmente la edición y administración; configurar un nombre no demuestra ese cotejo.

La lectura continúa protegida por Policy y permisos de las cinco fuentes. La consulta no crea evaluaciones, cambia resultados clínicos ni altera las revisiones MED/PSI existentes. El perfil mantiene `persistido=false`. La [brecha de conservación integral](CORRECCIONES_POR_PUNTOS_2026_10_18.md) continúa abierta: se deben conservar también originales, contexto y exclusiones. No se implementa JSON/EAV ni una migración parcial para ocultarla. La investigación no resuelve una representación relacional ausente.

## Checklist de esta entrega

- [x] Investigación de fuentes originales y localizadores de cálculo.
- [x] Diccionario de los 19 componentes, tipos/unidades/límites y motivo de no normalización.
- [x] Seis perfiles nuevos, conservación del método HVLT-R anterior y separación de once medidas.
- [x] Extracción desde respuestas originales, identidad exacta y procedencia compartida de fluidez.
- [x] Documentación de campos de soporte C01–C05 sin generar afirmaciones de residentes.
- [x] Presentación de campos y métodos en el perfil de resultados.
- [ ] Mediciones originales y bindings institucionales compatibles: no aportados por investigación.
- [ ] Normalizadores de los 11 componentes restantes: manual/edición y norma compatible pendientes.
- [ ] Contrato de integración clínica de dominios y conservación integral institucional.
- [ ] Verificación visual/responsive completa y revisión profesional posterior de cada resultado conservado.

La evidencia de pruebas y el commit de este incremento se registran en [Correcciones por puntos](CORRECCIONES_POR_PUNTOS_2026_10_18.md). No se declara un campo clínico completo por haber completado su definición técnica.

Comprobación reproducible, con fixtures sintéticos y conexiones de pruebas aisladas:

```powershell
php vendor/phpunit/phpunit/phpunit --filter 'NormalizacionBateriaBolivia2015Test|NormalizacionInstrumentalInvestigadaTest|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest|ApplicationBoundaryTest|PresentationBoundaryTest|FrontendArchitectureTest'
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro 'NormalizacionBateriaBolivia2015Test|NormalizacionInstrumentalInvestigadaTest|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest'
npm run build
```

El script PostgreSQL crea y retira su propio clúster desechable; no se ejecuta contra la base operativa. Las pruebas de cálculo no requieren conexión externa ni contienen material de administración de pruebas clínicas.
