# Diccionario de adquisición instrumental O.R.I.O.N.

Corte: 19/10/2026. Catálogo `CAPTURA-COGNITIVA-8-20261019`. Extraído del registro ejecutable: 23 métodos y 19 componentes; una aplicación requiere vinculación explícita, no el conjunto de alternativas de un componente. Este diccionario describe requisitos de cálculo, no instrumentos administrados a residentes ni adopción institucional.

## Lectura y procedencia

Cada entrada numérica utiliza `respuestas_instrumento.valor_numero`; cada lógica, `valor_logico`, mediante la PK real de la pregunta del instrumento. `ENTERO` no admite fracciones; `NUMERO` respeta la precisión del contrato de captura (cuatro decimales); `LOGICO` distingue Sí/No/NULL. Edad: años cumplidos en la fecha de aplicación desde la fecha de nacimiento original; no es una pregunta ni se sustituye por la edad actual.

Los campos de identidad son requisitos de la referencia, que deben cotejarse con el protocolo realmente aplicado antes de configurar un perfil. Las condiciones lógicas se contestan desde el registro original; no se fijan a Sí por seleccionar un método. La edad admisible no acredita por sí sola la compatibilidad cultural, escolar, lingüística ni sensorial. Un cálculo descriptivo no tiene norma por el solo hecho de conservar su bruto.

Para cada aplicación: residente, instrumento/código/versión, fecha clínica y aplicador original. Para una transcripción: documento privado verificable del mismo residente, localizador, transcriptor actual e instante técnico, y antecesora/motivo si rectifica. Se reutiliza la [adquisición y conciliación implementadas](FASE_2_FUENTES_ORIGINALES_2026_10_19.md).

## Mapa de los 19 componentes

| Componente | Cálculos de base | Referencias adicionales | Descriptivos |
| --- | --- | --- | --- |
| `MEM-S01` | `HVLT-R-BO-2015-F5` | `PFCSRT-ES-2025-IMAGENES-A-LIBRE` | — |
| `MEM-S02` | `HVLT-R-BO-2015-F5` | — | — |
| `MEM-S03` | — | `FCSRT-ES-SABIEX-2021-APRENDIZAJE` | `FCSRT-PALABRAS-NEURONORMA-BRUTO-1` |
| `MEM-S04` | — | `CERAD-RECONOCIMIENTO-CO-API-2019` | `HVLT-R-F5-RECONOCIMIENTO-BRUTO-1` |
| `ATE-S01` | — | `CANCELACION-A-CO-API-2019` | — |
| `ATE-S02` | — | `LC-PW-ES-SCAND-2023-EFICIENCIA` | — |
| `ATE-S03` | `BTA-BO-2015-NL` | — | — |
| `EJE-S01` | `STROOP-BO-2015-GOLDEN` | — | — |
| `EJE-S02` | `TMT-B-BO-2015` | — | — |
| `EJE-S03` | — | `DSB-ES-SABIEX-2021-LONGITUD`<br>`LN-ES-SABIEX-2021-LONGITUD`<br>`SPAN-ES-SABIEX-2021-LONGITUDES` | — |
| `EJE-S04` | — | `TOL-F-DE-GHS-2019-24` | — |
| `EJE-S05` | — | `RAVEN-A-CO-API-2019` | — |
| `LEN-S01` | `BNT-BO-2015-60` | — | — |
| `LEN-S02` | `FLUIDEZ-BO-2015-FAS-ANIMALES` | — | — |
| `LEN-S03` | — | `TOKEN-CO-UNAL-2017-TABLAS` | — |
| `LEN-S04` | — | `TB-A-ES-NEURONORMA-2011-LENGUAJE` | `WAB-ES-2000-ORAL-BRUTO-1` |
| `LEN-S05` | — | `TB-A-ES-NEURONORMA-2011-LENGUAJE` | `WAB-ES-2000-ORAL-BRUTO-1` |
| `VIS-S01` | — | `JLO-ES-SABIEX-2021-30` | — |
| `VIS-S02` | `ROCF-BO-2015-COPIA-A` | — | — |

## Requisitos exactos de cada método

### `HVLT-R-BO-2015-F5`

- Componentes: `MEM-S01`<br>`MEM-S02`.
- Tipo: `NORMALIZACION_BASE`.
- Fuente primaria: [referencia del método](https://doi.org/10.3233/NRE-151286).
- Intervalo etario publicado por el contrato: 18–95 años. Intersección con población objetivo: 60–95.
- Identidad a cotejar: `{"instrumento":"HVLT-R","forma":5,"idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `ensayo_1` | `ENTERO` | `valor_numero` |
| `ensayo_2` | `ENTERO` | `valor_numero` |
| `ensayo_3` | `ENTERO` | `valor_numero` |
| `recuerdo_diferido` | `ENTERO` | `valor_numero` |
| `demora_minutos` | `NUMERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_bolivia` | `LOGICO` | `valor_logico` |
| `residencia_bolivia` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `MEM-S01`: Total de tres ensayos; no confundir con reconocimiento o recuerdo con claves.
  - `ensayo_1`: Entero de aciertos, 0–12.
  - `ensayo_2`: Entero de aciertos, 0–12.
  - `ensayo_3`: Entero de aciertos, 0–12.
- `MEM-S02`: Recuerdo libre diferido; no aplicar la regresión del total de aprendizaje.
  - `recuerdo_diferido`: Entero de aciertos, 0–12.
  - `demora_minutos`: Número de minutos efectivos, referencia 20–25.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `BTA-BO-2015-NL`

- Componentes: `ATE-S03`.
- Tipo: `NORMALIZACION_BASE`.
- Fuente primaria: [referencia del método](https://doi.org/10.3233/NRE-151283). Localizador: Tabla 2.
- Intervalo etario publicado por el contrato: 18–95 años. Intersección con población objetivo: 60–95.
- Identidad a cotejar: `{"instrumento":"BTA","edicion_protocolo":"Schretlen-1997-NL","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `forma_n_aciertos` | `ENTERO` | `valor_numero` |
| `forma_l_aciertos` | `ENTERO` | `valor_numero` |
| `audicion_compatible` | `LOGICO` | `valor_logico` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_bolivia` | `LOGICO` | `valor_logico` |
| `residencia_bolivia` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `ATE-S03`: El modelo boliviano compara el total N+L, 0–20. No lo convierte en una medida de atención sostenida.
  - `forma_n_aciertos`: Entero, 0–10.
  - `forma_l_aciertos`: Entero, 0–10.
  - `audicion_compatible`: Lógico documentado.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `STROOP-BO-2015-GOLDEN`

- Componentes: `EJE-S01`.
- Tipo: `NORMALIZACION_BASE`.
- Fuente primaria: [referencia del método](https://doi.org/10.3233/NRE-151281). Localizador: Tabla A35.
- Intervalo etario publicado por el contrato: 18–95 años. Intersección con población objetivo: 60–95.
- Identidad a cotejar: `{"instrumento":"STROOP","edicion_protocolo":"Golden-2007-45s","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `palabras_correctas` | `ENTERO` | `valor_numero` |
| `colores_correctos` | `ENTERO` | `valor_numero` |
| `palabra_color_correctas` | `ENTERO` | `valor_numero` |
| `segundos_palabras` | `NUMERO` | `valor_numero` |
| `segundos_colores` | `NUMERO` | `valor_numero` |
| `segundos_palabra_color` | `NUMERO` | `valor_numero` |
| `condiciones_sensoriales_motoras_compatibles` | `LOGICO` | `valor_logico` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_bolivia` | `LOGICO` | `valor_logico` |
| `residencia_bolivia` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `EJE-S01`: Interferencia = WC − W×C/(W+C), denominador positivo. A35 Bolivia permite intervalos percentilares aproximados; no tiene regresión boliviana ni z.
  - `palabras_correctas`: Entero de respuestas correctas.
  - `colores_correctos`: Entero de respuestas correctas.
  - `palabra_color_correctas`: Entero de respuestas correctas.
  - `segundos_palabras`: Número de segundos, referencia 45.
  - `segundos_colores`: Número de segundos, referencia 45.
  - `segundos_palabra_color`: Número de segundos, referencia 45.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `TMT-B-BO-2015`

- Componentes: `EJE-S02`.
- Tipo: `NORMALIZACION_BASE`.
- Fuente primaria: [referencia del método](https://doi.org/10.3233/NRE-151284). Localizador: Tabla 4.
- Intervalo etario publicado por el contrato: 18–95 años. Intersección con población objetivo: 60–95.
- Identidad a cotejar: `{"instrumento":"TMT","edicion_protocolo":"Parte-B-25-300s","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `segundos_parte_b` | `NUMERO` | `valor_numero` |
| `errores_observados` | `ENTERO` | `valor_numero` |
| `finalizada` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_motoras_compatibles` | `LOGICO` | `valor_logico` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_bolivia` | `LOGICO` | `valor_logico` |
| `residencia_bolivia` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `EJE-S02`: Menor tiempo indica mejor rendimiento. Una tarea interrumpida se conserva censurada; no se presenta como tiempo exacto de finalización.
  - `segundos_parte_b`: Número positivo de segundos, hasta 300.
  - `errores_observados`: Entero no negativo.
  - `finalizada`: Lógico documentado.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `BNT-BO-2015-60`

- Componentes: `LEN-S01`.
- Tipo: `NORMALIZACION_BASE`.
- Fuente primaria: [referencia del método](https://doi.org/10.3233/NRE-151278). Localizador: Tabla 2.
- Intervalo etario publicado por el contrato: 18–95 años. Intersección con población objetivo: 60–95.
- Identidad a cotejar: `{"instrumento":"BNT","edicion_protocolo":"Kaplan-1983-60","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `aciertos_espontaneos` | `ENTERO` | `valor_numero` |
| `aciertos_clave_semantica` | `ENTERO` | `valor_numero` |
| `condiciones_sensoriales_motoras_compatibles` | `LOGICO` | `valor_logico` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_bolivia` | `LOGICO` | `valor_logico` |
| `residencia_bolivia` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `LEN-S01`: No admite formas de 15 elementos o ediciones distintas. El modelo boliviano no ajusta por edad; la corrección sigue el protocolo publicado.
  - `aciertos_espontaneos`: Entero, 0–60.
  - `aciertos_clave_semantica`: Entero adicional; suma hasta 60.
  - `ayudas_fonemicas`: Registro separado; no se incluye en ese total.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `FLUIDEZ-BO-2015-FAS-ANIMALES`

- Componentes: `LEN-S02`.
- Tipo: `NORMALIZACION_BASE`.
- Fuente primaria: [referencia del método](https://doi.org/10.3233/NRE-151279). Localizador: Tablas 2, 4, 6 y 8.
- Intervalo etario publicado por el contrato: 18–95 años. Intersección con población objetivo: 60–95.
- Identidad a cotejar: `{"instrumento":"FLUIDEZ","edicion_protocolo":"FAS-Animales-60s","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `f_validas` | `ENTERO` | `valor_numero` |
| `f_segundos` | `NUMERO` | `valor_numero` |
| `a_validas` | `ENTERO` | `valor_numero` |
| `a_segundos` | `NUMERO` | `valor_numero` |
| `s_validas` | `ENTERO` | `valor_numero` |
| `s_segundos` | `NUMERO` | `valor_numero` |
| `animales_validas` | `ENTERO` | `valor_numero` |
| `animales_segundos` | `NUMERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_bolivia` | `LOGICO` | `valor_logico` |
| `residencia_bolivia` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `LEN-S02`: Cuatro modelos propios; no se calcula un z promedio ni un total FAS con la norma de una letra. Una condición ausente no invalida las otras.
  - `f_validas`: Entero de respuestas válidas F.
  - `a_validas`: Entero de respuestas válidas A.
  - `s_validas`: Entero de respuestas válidas S.
  - `animales_validas`: Entero de respuestas válidas, animales.
  - `f_segundos`: Número de segundos, referencia 60.
  - `a_segundos`: Número de segundos, referencia 60.
  - `s_segundos`: Número de segundos, referencia 60.
  - `animales_segundos`: Número de segundos, referencia 60.
  - `repeticiones_intrusiones`: Recuento original separado, según protocolo.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `ROCF-BO-2015-COPIA-A`

- Componentes: `VIS-S02`.
- Tipo: `NORMALIZACION_BASE`.
- Fuente primaria: [referencia del método](https://doi.org/10.3233/NRE-151285). Localizador: Tabla 2.
- Intervalo etario publicado por el contrato: 18–95 años. Intersección con población objetivo: 60–95.
- Identidad a cotejar: `{"instrumento":"ROCF","edicion_protocolo":"Rey-2009-A-Copia","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `puntaje_copia` | `NUMERO` | `valor_numero` |
| `elementos_revisados` | `ENTERO` | `valor_numero` |
| `condiciones_sensoriales_motoras_compatibles` | `LOGICO` | `valor_logico` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_bolivia` | `LOGICO` | `valor_logico` |
| `residencia_bolivia` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `VIS-S02`: No intercambiar copia con recuerdo. La rúbrica se aplica al original; el software no inventa un dibujo ni lo puntúa desde una observación.
  - `puntaje_copia`: Número, 0–36, en medios puntos.
  - `elementos_revisados`: Entero, 18.
  - `referencia_produccion`: Referencia a la producción original.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `DSB-ES-SABIEX-2021-LONGITUD`

- Componentes: `EJE-S03`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.3390/ijerph18199958). Localizador: §2.3.1, tabla 4; calculadora de autores C9/E9.
- Intervalo etario publicado por el contrato: 55–87 años. Intersección con población objetivo: 60–87.
- Identidad a cotejar: `{"instrumento":"WAIS-III","edicion_protocolo":"WAIS-III-ES-LONGITUD-INVERSA","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `longitud_inversa` | `ENTERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `contexto_normativo_espana_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_crianza_espana` | `LOGICO` | `valor_logico` |
| `actividad_cognitiva_universitaria` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `EJE-S03`: No transferir máximos o normas entre WAIS-III, WAIS-IV, idiomas o adaptaciones. Directo no sustituye manipulación de memoria de trabajo.
  - `adaptacion_nacional`: Texto de identidad de edición.
  - `condicion`: Directo, inverso o secuenciación, separados.
  - `puntajes_por_ensayo`: Mediciones originales según rúbrica.
  - `bruto_subprueba`: Puntuación original.
  - `longitud_maxima`: Entero si esa edición lo define.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `LN-ES-SABIEX-2021-LONGITUD`

- Componentes: `EJE-S03`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.3390/ijerph18199958). Localizador: §2.3.2, tabla 4; calculadora de autores C10/E10.
- Intervalo etario publicado por el contrato: 55–87 años. Intersección con población objetivo: 60–87.
- Identidad a cotejar: `{"instrumento":"WAIS-III","edicion_protocolo":"WAIS-III-ES-LONGITUD-LETRAS-NUMEROS","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `longitud_letras_numeros` | `ENTERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `contexto_normativo_espana_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_crianza_espana` | `LOGICO` | `valor_logico` |
| `actividad_cognitiva_universitaria` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `EJE-S03`: No transferir máximos o normas entre WAIS-III, WAIS-IV, idiomas o adaptaciones. Directo no sustituye manipulación de memoria de trabajo.
  - `adaptacion_nacional`: Texto de identidad de edición.
  - `condicion`: Directo, inverso o secuenciación, separados.
  - `puntajes_por_ensayo`: Mediciones originales según rúbrica.
  - `bruto_subprueba`: Puntuación original.
  - `longitud_maxima`: Entero si esa edición lo define.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `SPAN-ES-SABIEX-2021-LONGITUDES`

- Componentes: `EJE-S03`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.3390/ijerph18199958). Localizador: §2.3.1–2, tabla 4; calculadora C9/E9 y C10/E10.
- Intervalo etario publicado por el contrato: 55–87 años. Intersección con población objetivo: 60–87.
- Identidad a cotejar: `{"instrumento":"WAIS-III","edicion_protocolo":"WAIS-III-ES-LONGITUDES-DSB-LN","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `longitud_inversa` | `ENTERO` | `valor_numero` |
| `longitud_letras_numeros` | `ENTERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `contexto_normativo_espana_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_crianza_espana` | `LOGICO` | `valor_logico` |
| `actividad_cognitiva_universitaria` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `EJE-S03`: No transferir máximos o normas entre WAIS-III, WAIS-IV, idiomas o adaptaciones. Directo no sustituye manipulación de memoria de trabajo.
  - `adaptacion_nacional`: Texto de identidad de edición.
  - `condicion`: Directo, inverso o secuenciación, separados.
  - `puntajes_por_ensayo`: Mediciones originales según rúbrica.
  - `bruto_subprueba`: Puntuación original.
  - `longitud_maxima`: Entero si esa edición lo define.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `JLO-ES-SABIEX-2021-30`

- Componentes: `VIS-S01`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.3390/ijerph182412977). Localizador: §2.2.3, tabla 2; calculadora de autores C23/E23.
- Intervalo etario publicado por el contrato: 55–87 años. Intersección con población objetivo: 60–87.
- Identidad a cotejar: `{"instrumento":"JLO","edicion_protocolo":"JLO-30-NEURONORMA","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `aciertos` | `ENTERO` | `valor_numero` |
| `elementos_puntuados` | `ENTERO` | `valor_numero` |
| `sexo_norma` | `ENTERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `contexto_normativo_espana_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_crianza_espana` | `LOGICO` | `valor_logico` |
| `actividad_cognitiva_universitaria` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `VIS-S01`: JLO estándar: 30 elementos puntuados; no atribuir este máximo a VOSP. Normas NEURONORMA españolas, no bolivianas.
  - `subprueba_forma`: Identidad exacta, sin total VOSP genérico.
  - `administrados`: Entero de elementos puntuados.
  - `correctos`: Entero de aciertos.
  - `control_visual`: Condición y apoyos documentados.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `FCSRT-ES-SABIEX-2021-APRENDIZAJE`

- Componentes: `MEM-S03`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.3390/ijerph182412977). Localizador: §2.2.1, tabla 2 FCSRT-Imm; calculadora de autores C15/E15.
- Intervalo etario publicado por el contrato: 55–87 años. Intersección con población objetivo: 60–87.
- Identidad a cotejar: `{"instrumento":"FCSRT","edicion_protocolo":"NEURONORMA-2009-PALABRAS","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `libre_1` | `ENTERO` | `valor_numero` |
| `adicional_claves_1` | `ENTERO` | `valor_numero` |
| `libre_2` | `ENTERO` | `valor_numero` |
| `adicional_claves_2` | `ENTERO` | `valor_numero` |
| `libre_3` | `ENTERO` | `valor_numero` |
| `adicional_claves_3` | `ENTERO` | `valor_numero` |
| `codificacion_verificada` | `LOGICO` | `valor_logico` |
| `sexo_norma` | `ENTERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `contexto_normativo_espana_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `nacimiento_crianza_espana` | `LOGICO` | `valor_logico` |
| `actividad_cognitiva_universitaria` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `MEM-S03`: Libre más adicional no excede 16 por ensayo. Totales descriptivos; no intercambiar palabras e imágenes ni adoptar baremos españoles por esta suma.
  - `libre_1`: Entero, 0–16.
  - `adicional_claves_1`: Entero adicional, sin repetir los libres.
  - `libre_2`: Entero, 0–16.
  - `adicional_claves_2`: Entero adicional, sin repetir los libres.
  - `libre_3`: Entero, 0–16.
  - `adicional_claves_3`: Entero adicional, sin repetir los libres.
  - `libre_diferido`: Entero, 0–16.
  - `adicional_claves_diferido`: Entero adicional, sin repetir los libres.
  - `demora_minutos`: Número; diferido de referencia a 30 minutos.
  - `codificacion_verificada`: Lógico con soporte de la aplicación.
  - `administracion_estandarizada`: Lógico documentado.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `PFCSRT-ES-2025-IMAGENES-A-LIBRE`

- Componentes: `MEM-S01`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.1177/13872877251376906). Localizador: Página 433, tablas 4 y 5; recuerdo libre de aprendizaje.
- Intervalo etario publicado por el contrato: 50–88 años. Intersección con población objetivo: 60–88.
- Identidad a cotejar: `{"instrumento":"pFCSRT+IR","edicion_protocolo":"AEC-IMAGENES-IR-A","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `libre_1` | `ENTERO` | `valor_numero` |
| `libre_2` | `ENTERO` | `valor_numero` |
| `libre_3` | `ENTERO` | `valor_numero` |
| `codificacion_verificada` | `LOGICO` | `valor_logico` |
| `sexo_norma` | `ENTERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `contexto_normativo_espana_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `MEM-S01`: Total de tres ensayos; no confundir con reconocimiento o recuerdo con claves.
  - `ensayo_1`: Entero de aciertos, 0–12.
  - `ensayo_2`: Entero de aciertos, 0–12.
  - `ensayo_3`: Entero de aciertos, 0–12.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `RAVEN-A-CO-API-2019`

- Componentes: `EJE-S05`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/). Localizador: Métodos y tablas 10–11.
- Intervalo etario publicado por el contrato: 61–86 años. Intersección con población objetivo: 61–86.
- Edades excluidas por ambigüedad de la fuente: 65.
- Identidad a cotejar: `{"instrumento":"Raven","edicion_protocolo":"API-COLOMBIA-2019-PARTE-A-12","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `aciertos` | `ENTERO` | `valor_numero` |
| `elementos_puntuados` | `ENTERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `contexto_normativo_antioquia_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `EJE-S05`: La referencia Raven parte A de 12 elementos compara razonamiento no verbal en su contexto colombiano. No normaliza WAIS, Raven completo ni razonamiento general.
  - `subprueba`: Identidad de una subprueba concreta.
  - `puntajes_por_elemento`: Mediciones originales según rúbrica.
  - `bruto_subprueba`: Puntuación original.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `CERAD-RECONOCIMIENTO-CO-API-2019`

- Componentes: `MEM-S04`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/). Localizador: Métodos y tablas 10–11.
- Intervalo etario publicado por el contrato: 61–86 años. Intersección con población objetivo: 61–86.
- Edades excluidas por ambigüedad de la fuente: 65.
- Identidad a cotejar: `{"instrumento":"CERAD-Col","edicion_protocolo":"API-COLOMBIA-2019-RECONOCIMIENTO-20","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `aciertos_objetivos` | `ENTERO` | `valor_numero` |
| `falsas_alarmas` | `ENTERO` | `valor_numero` |
| `objetivos_presentados` | `ENTERO` | `valor_numero` |
| `distractores_presentados` | `ENTERO` | `valor_numero` |
| `demora_minutos` | `NUMERO` | `valor_numero` |
| `aprendizaje_tres_ensayos_documentado` | `LOGICO` | `valor_logico` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `contexto_normativo_antioquia_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `MEM-S04`: HVLT-R conserva su cálculo sin norma; CERAD-Col tiene una referencia separada con identidad y corrección propias. No intercambiar ambas tareas ni sus truncamientos.
  - `aciertos_reconocimiento`: Entero, 0–12 objetivos.
  - `falsas_alarmas_relacionadas`: Entero, 0–6.
  - `falsas_alarmas_no_relacionadas`: Entero, 0–6.
  - `demora_minutos`: Número de minutos efectivos.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `CANCELACION-A-CO-API-2019`

- Componentes: `ATE-S01`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/). Localizador: Métodos y tablas 10–11.
- Intervalo etario publicado por el contrato: 61–86 años. Intersección con población objetivo: 61–86.
- Edades excluidas por ambigüedad de la fuente: 65.
- Identidad a cotejar: `{"instrumento":"Cancelacion-A","edicion_protocolo":"API-COLOMBIA-2019-16","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `aciertos` | `ENTERO` | `valor_numero` |
| `objetivos_presentados` | `ENTERO` | `valor_numero` |
| `tiempo_segundos` | `NUMERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `contexto_normativo_antioquia_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `ATE-S01`: Ruff: diez ensayos por condición y rango editorial 16–70. La cancelación A colombiana tiene referencia propia, no normaliza Ruff ni mide por sí sola vigilancia prolongada.
  - `ensayos`: Veinte registros: condición, objetivos disponibles, aciertos, errores, omisiones y segundos efectivos.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `TOL-F-DE-GHS-2019-24`

- Componentes: `EJE-S04`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.1017/S1355617718001248). Localizador: Tablas 5–6, pp. 527–528.
- Intervalo etario publicado por el contrato: 60–79 años. Intersección con población objetivo: 60–79.
- Identidad a cotejar: `{"instrumento":"TOL-F","edicion_protocolo":"GHS-2019-TACTIL-24","idioma":"de"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `aciertos_minimos_movimientos` | `ENTERO` | `valor_numero` |
| `problemas_del_protocolo` | `ENTERO` | `valor_numero` |
| `sexo_norma` | `ENTERO` | `valor_numero` |
| `nivel_educativo_ghs` | `ENTERO` | `valor_numero` |
| `contexto_normativo_alemania_documentado` | `LOGICO` | `valor_logico` |
| `modalidad_tactil` | `LOGICO` | `valor_logico` |
| `comprension_instrucciones_verificada` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_motoras_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `EJE-S04`: TOL-F táctil de 24 problemas dispone de tabla alemana de referencia para 60–79 años. No sustituye TOL-DX ni acredita una norma boliviana.
  - `movimientos_por_problema`: Enteros no negativos.
  - `resueltos`: Enteros según criterio de esa edición.
  - `violaciones_regla`: Enteros.
  - `violaciones_tiempo`: Enteros.
  - `tiempos_por_problema`: Segundos de inicio, ejecución y total.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `TOKEN-CO-UNAL-2017-TABLAS`

- Componentes: `LEN-S03`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://repositorio.unal.edu.co/handle/unal/59359). Localizador: Tablas 23–30 y 32, pp. 103–108.
- Intervalo etario publicado por el contrato: 60–90 años. Intersección con población objetivo: 60–90.
- Identidad a cotejar: `{"instrumento":"Token-Test","edicion_protocolo":"NN-CO-2017-DE-RENZI-FAGLIONI-36","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `puntaje_total` | `NUMERO` | `valor_numero` |
| `instrucciones_del_protocolo` | `ENTERO` | `valor_numero` |
| `escolaridad_anios` | `ENTERO` | `valor_numero` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `contexto_normativo_neuronorma_colombia_documentado` | `LOGICO` | `valor_logico` |
| `audicion_vision_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `LEN-S03`: No obligar a enteros: se admiten medios puntos. La referencia tabulada UNAL 2017 es colombiana, con lagunas explícitas y nivel de evidencia de tesis; no sustituye NEURONORMA España.
  - `presentaciones_por_elemento`: Primera o segunda presentación.
  - `puntajes_por_elemento`: Decimales según rúbrica.
  - `puntaje_total`: Decimal; máximo 36 solo en versión abreviada correspondiente.
  - `audicion_vision`: Condiciones y apoyos documentados.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `LC-PW-ES-SCAND-2023-EFICIENCIA`

- Componentes: `ATE-S02`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.1080/13854046.2023.2173304). Localizador: Instrumentos p. 1771 y tablas 7–8 pp. 1778–1779.
- Intervalo etario publicado por el contrato: 60–98 años. Intersección con población objetivo: 60–98.
- Identidad a cotejar: `{"instrumento":"Cancelacion-PW","edicion_protocolo":"ELES-SCAND-2023-26X30-65","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `aciertos` | `ENTERO` | `valor_numero` |
| `omisiones_corregidas` | `ENTERO` | `valor_numero` |
| `comisiones` | `ENTERO` | `valor_numero` |
| `objetivos_del_protocolo` | `ENTERO` | `valor_numero` |
| `filas_del_protocolo` | `ENTERO` | `valor_numero` |
| `columnas_del_protocolo` | `ENTERO` | `valor_numero` |
| `segundos` | `NUMERO` | `valor_numero` |
| `nivel_educativo_scand` | `ENTERO` | `valor_numero` |
| `zona_explorada_cotejada` | `LOGICO` | `valor_logico` |
| `practica_comprendida` | `LOGICO` | `valor_logico` |
| `lectoescritura` | `LOGICO` | `valor_logico` |
| `espanol_nativo` | `LOGICO` | `valor_logico` |
| `contexto_normativo_espana_documentado` | `LOGICO` | `valor_logico` |
| `condiciones_sensoriales_motoras_compatibles` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `ATE-S02`: SCAND P/W dispone de referencia propia para eficiencia selectiva, 60–98 años. No normaliza Ruff ni comparte la aplicación de cancelación A como soporte independiente.
  - `ensayos`: Mismos veinte registros originales de detección automática y búsqueda controlada.
  - `condicion`: Identidad de cada condición; no combinar sus puntuaciones brutas.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `TB-A-ES-NEURONORMA-2011-LENGUAJE`

- Componentes: `LEN-S04`<br>`LEN-S05`.
- Tipo: `NORMALIZACION_REFERENCIA`.
- Fuente primaria: [referencia del método](https://doi.org/10.1093/arclin/acq098). Localizador: Resultados, tabla 5 y apéndice A; PDF de autores pp. 4, 6, 9–10.
- Intervalo etario publicado por el contrato: 50–90 años. Intersección con población objetivo: 60–90.
- Identidad a cotejar: `{"instrumento":"TB-A","edicion_protocolo":"NEURONORMA-2011-ABREVIADO","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `repeticion_palabras_aciertos` | `ENTERO` | `valor_numero` |
| `repeticion_elementos_administrados` | `ENTERO` | `valor_numero` |
| `fluencia_gramatica` | `ENTERO` | `valor_numero` |
| `muestra_discurso_documentada` | `LOGICO` | `valor_logico` |
| `tareas_habla_espontanea_completadas` | `LOGICO` | `valor_logico` |
| `audicion_compatible` | `LOGICO` | `valor_logico` |
| `contexto_normativo_espana_documentado` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `LEN-S04`: WAB castellana conserva su bruto. TB-A NEURONORMA 2011 incorpora repetición de diez palabras con conversión categórica y efecto techo; no normaliza WAB ni determina DCL.
  - `forma`: Forma y edición exactas, sin intercambiar WAB y WAB-R.
  - `puntajes_por_elemento`: Mediciones originales según rúbrica.
  - `repeticion_total`: Entero 0–100 solo para WAB castellana Nau Llibres 2000.
  - `audicion_compatible`: Lógico documentado.
  - `administracion_estandarizada`: Lógico documentado.
- `LEN-S05`: WAB castellana conserva contenido y fluidez por separado. TB-A 2011 aporta fluencia/gramática en tareas de habla espontánea con fuerte efecto techo; no equivale a valorar todo el discurso ni a una nota de cuidados.
  - `tarea`: Identidad de conversación o descripción.
  - `referencia_muestra`: Referencia documental al original.
  - `contenido_informacion`: Entero 0–10 para WAB castellana 2000.
  - `fluidez`: Entero 0–10 separado, misma edición.
  - `muestra_discurso_documentada`: Lógico documentado; no sustituye la referencia al original.
  - `administracion_estandarizada`: Lógico documentado.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `HVLT-R-F5-RECONOCIMIENTO-BRUTO-1`

- Componentes: `MEM-S04`.
- Tipo: `CALCULO_DESCRIPTIVO`.
- Fuente primaria: [referencia del método](https://fitbir.nih.gov/dictionary/publicData/dataElementAction!view.action?dataElementName=HVLTRRecogDiscrimIndexRawScore&publicArea=true&style.key=fitbir-style).
- Intervalo etario publicado por el contrato: no definido–no definido años. Intersección con población objetivo: no acredita rango normativo.
- Identidad a cotejar: `{"instrumento":"HVLT-R","forma":5,"idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `aciertos_reconocimiento` | `ENTERO` | `valor_numero` |
| `falsas_alarmas_relacionadas` | `ENTERO` | `valor_numero` |
| `falsas_alarmas_no_relacionadas` | `ENTERO` | `valor_numero` |
| `demora_minutos` | `NUMERO` | `valor_numero` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `MEM-S04`: HVLT-R conserva su cálculo sin norma; CERAD-Col tiene una referencia separada con identidad y corrección propias. No intercambiar ambas tareas ni sus truncamientos.
  - `aciertos_reconocimiento`: Entero, 0–12 objetivos.
  - `falsas_alarmas_relacionadas`: Entero, 0–6.
  - `falsas_alarmas_no_relacionadas`: Entero, 0–6.
  - `demora_minutos`: Número de minutos efectivos.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `WAB-ES-2000-ORAL-BRUTO-1`

- Componentes: `LEN-S04`<br>`LEN-S05`.
- Tipo: `CALCULO_DESCRIPTIVO`.
- Fuente primaria: [referencia del método](https://talkbank.org/aphasia/protocol/languages/Spanish/WAB.pdf).
- Intervalo etario publicado por el contrato: no definido–no definido años. Intersección con población objetivo: no acredita rango normativo.
- Identidad a cotejar: `{"instrumento":"WAB","edicion_protocolo":"Nau-Llibres-2000-Castellana","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `repeticion_total` | `ENTERO` | `valor_numero` |
| `contenido_informacion` | `ENTERO` | `valor_numero` |
| `fluidez` | `ENTERO` | `valor_numero` |
| `audicion_compatible` | `LOGICO` | `valor_logico` |
| `muestra_discurso_documentada` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `LEN-S04`: WAB castellana conserva su bruto. TB-A NEURONORMA 2011 incorpora repetición de diez palabras con conversión categórica y efecto techo; no normaliza WAB ni determina DCL.
  - `forma`: Forma y edición exactas, sin intercambiar WAB y WAB-R.
  - `puntajes_por_elemento`: Mediciones originales según rúbrica.
  - `repeticion_total`: Entero 0–100 solo para WAB castellana Nau Llibres 2000.
  - `audicion_compatible`: Lógico documentado.
  - `administracion_estandarizada`: Lógico documentado.
- `LEN-S05`: WAB castellana conserva contenido y fluidez por separado. TB-A 2011 aporta fluencia/gramática en tareas de habla espontánea con fuerte efecto techo; no equivale a valorar todo el discurso ni a una nota de cuidados.
  - `tarea`: Identidad de conversación o descripción.
  - `referencia_muestra`: Referencia documental al original.
  - `contenido_informacion`: Entero 0–10 para WAB castellana 2000.
  - `fluidez`: Entero 0–10 separado, misma edición.
  - `muestra_discurso_documentada`: Lógico documentado; no sustituye la referencia al original.
  - `administracion_estandarizada`: Lógico documentado.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

### `FCSRT-PALABRAS-NEURONORMA-BRUTO-1`

- Componentes: `MEM-S03`.
- Tipo: `CALCULO_DESCRIPTIVO`.
- Fuente primaria: [referencia del método](https://doi.org/10.3390/ijerph182412977).
- Intervalo etario publicado por el contrato: no definido–no definido años. Intersección con población objetivo: no acredita rango normativo.
- Identidad a cotejar: `{"instrumento":"FCSRT","edicion_protocolo":"NEURONORMA-2009-PALABRAS","idioma":"es"}`.

| Entrada del método | Tipo exigido | Columna original |
| --- | --- | --- |
| `libre_1` | `ENTERO` | `valor_numero` |
| `adicional_claves_1` | `ENTERO` | `valor_numero` |
| `libre_2` | `ENTERO` | `valor_numero` |
| `adicional_claves_2` | `ENTERO` | `valor_numero` |
| `libre_3` | `ENTERO` | `valor_numero` |
| `adicional_claves_3` | `ENTERO` | `valor_numero` |
| `libre_diferido` | `ENTERO` | `valor_numero` |
| `adicional_claves_diferido` | `ENTERO` | `valor_numero` |
| `demora_minutos` | `NUMERO` | `valor_numero` |
| `codificacion_verificada` | `LOGICO` | `valor_logico` |
| `administracion_estandarizada` | `LOGICO` | `valor_logico` |

**Unidades, rangos y límites por componente:**

- `MEM-S03`: Libre más adicional no excede 16 por ensayo. Totales descriptivos; no intercambiar palabras e imágenes ni adoptar baremos españoles por esta suma.
  - `libre_1`: Entero, 0–16.
  - `adicional_claves_1`: Entero adicional, sin repetir los libres.
  - `libre_2`: Entero, 0–16.
  - `adicional_claves_2`: Entero adicional, sin repetir los libres.
  - `libre_3`: Entero, 0–16.
  - `adicional_claves_3`: Entero adicional, sin repetir los libres.
  - `libre_diferido`: Entero, 0–16.
  - `adicional_claves_diferido`: Entero adicional, sin repetir los libres.
  - `demora_minutos`: Número; diferido de referencia a 30 minutos.
  - `codificacion_verificada`: Lógico con soporte de la aplicación.
  - `administracion_estandarizada`: Lógico documentado.

Las descripciones del componente incluyen alternativas de adquisición. La lista tipada de este método es la entrada ejecutable exacta; no se cambia el nombre de un campo de otra edición para hacerla coincidir. Condiciones de cálculo, ecuaciones, tablas y lagunas de fuentes: [cierre de fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), [manual 60+](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md), [referencias adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md).

## Versión y actualización

Huella SHA-256 de los metadatos ordenados como el registro PHP (JSON técnico de definiciones, sin datos clínicos): `6a3dae610e0eaac5dbd704e53e6c8b8d61c16cdecc803b341736d8778cb17090`. Esta huella documenta este corte, no constituye una firma profesional ni reemplaza la conservación relacional pendiente. Si cambia el registro, actualizar este diccionario y ejecutar sus pruebas de cálculo/aplicabilidad.
