# O.R.I.O.N. — Cierre técnico de los 19 componentes instrumentales

Fecha: **19/10/2026**, America/La_Paz. Catálogo ejecutable:
**CAPTURA-COGNITIVA-8-20261019**. Alcance solicitado: verificar ocho componentes
con cálculo, resolver once restantes mediante investigación profesional y
documentar utilidad, uso, motivo, interpretación y pruebas.

**Resultado: 19 de 19 componentes tienen al menos un método de comparación
implementado y probado bajo sus condiciones expresas. Se cierra el alcance
técnico de cálculo y documentación de fase 3.** No se declara validación clínica,
adopción institucional de baremos extranjeros ni disponibilidad de mediciones
originales para todos los residentes. La entrega no acredita el cierre integral
de O.R.I.O.N., la fase de fuentes originales ni las etapas de inferencia.

## 1. Fuentes de verdad y significado del contador

Autoridad: solicitud de la propietaria, [objetivo de fase 3](FASE_3_COMPONENTES_2026_10_19.md)
y [plan integral](PLAN_CIERRE_INTEGRAL_2026_10_19.md). Se reutilizan el registro,
captura, extractor, perfil y explicación instrumental existentes en la arquitectura
híbrida; no se construye otro motor. La investigación es dirigida y trazable,
no una revisión sistemática ni un estudio clínico propio.

El contador reúne ocho componentes de la base y doce con referencias adicionales,
con un solapamiento en aprendizaje: `8 + 12 − 1 = 19`. Los **23 selectores** del
registro son siete de base, trece de referencia y tres descriptivos. Un selector
puede producir varias medidas; ninguno equivale a una nueva evidencia independiente.
Los cálculos exclusivamente brutos no se cuentan como normalizadores.

«Disponible» significa que el algoritmo puede comparar una aplicación original
compatible. No significa que un instrumento o baremo sea aplicable a cualquier
persona mayor de 60. Cada método conserva su techo de edad, escolaridad, cultura,
idioma, versión, condiciones y celdas sin soporte. No existe una norma universal
para todas las edades mayores de 60 ni se extienden automáticamente los rangos.

## 2. Matriz completa y documentación de cada componente

Los códigos siguientes son identificadores del contrato técnico, no mensajes
destinados al médico. BO = Bolivia; ES = España; CO = Colombia; DE = Alemania.
Las edades son las implementadas; no bastan por sí solas para admitir una captura.

| Componente | Medición / método de comparación disponible | Edad y límite principal | Fuente y pruebas |
| --- | --- | --- | --- |
| MEM-S01: aprendizaje | `HVLT-R-BO-2015-F5`, total de tres ensayos; alternativa `PFCSRT-ES-2025-IMAGENES-A-LIBRE` | BO 18–95, forma 5; alternativa ES 50–88, imágenes A | [Fichas y ejemplos base](INVESTIGACION_PROFESIONAL_19_COMPONENTES_2026_10_19.md); [alternativa](NORMALIZACIONES_REFERENCIA_2026_10_19.md); `InterpretacionInstrumentalFaseTresTest`, `NormalizacionReferenciasEspanaTest` |
| MEM-S02: recuerdo diferido | `HVLT-R-BO-2015-F5`, diferido original de 20–25 minutos | BO 18–95; no inventar intervalo ni sustituir libre por reconocimiento | Fichas base; `NormalizacionInstrumentalInvestigadaTest`, `InterpretacionInstrumentalFaseTresTest` |
| MEM-S03: recuerdo con claves | `FCSRT-ES-SABIEX-2021-APRENDIZAJE`, total libre + adicional con claves | ES 55–87, escolaridad 3–22; no norma aislada de los adicionales | [Ecuación y uso](NORMALIZACIONES_REFERENCIA_2026_10_19.md); `NormalizacionReferenciasEspanaTest`, `RecuerdoConClavesFcsrtInvestigadoTest` |
| MEM-S04: reconocimiento | `CERAD-RECONOCIMIENTO-CO-API-2019` | CO 61–64 y 66–86, escolaridad 1–25; 65 ambiguo excluido | [Capítulo CERAD](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md); `NormalizacionReferenciasGeriatricasAdicionalesTest` |
| ATE-S01: atención sostenida en la tarea | `CANCELACION-A-CO-API-2019`, aciertos | Misma edad API; grupo mayor con escolaridad baja sin celda interpretable | [Capítulo cancelación A](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md); `NormalizacionReferenciasGeriatricasAdicionalesTest` |
| ATE-S02: atención selectiva en la tarea | `LC-PW-ES-SCAND-2023-EFICIENCIA` | ES 60–98; eficiencia −42..28 y cuatro categorías educativas | §3 de este documento; `CierreComponentesFaseTresTest` |
| ATE-S03: atención dividida | `BTA-BO-2015-NL` | BO 18–95; series N/L originales y suma 0–20 | Fichas base; `NormalizacionBateriaBolivia2015Test`, `InterpretacionInstrumentalFaseTresTest` |
| EJE-S01: inhibición | `STROOP-BO-2015-GOLDEN`, interferencia | BO 18–95; tres tareas de 45 s, intervalo tabulado, no z inventado | Fichas base; `NormalizacionBateriaBolivia2015Test`, `InterpretacionInstrumentalFaseTresTest` |
| EJE-S02: flexibilidad | `TMT-B-BO-2015` | BO 18–95; tiempo final ≤300 s; menor es mejor | Fichas base; mismas dos clases |
| EJE-S03: memoria de trabajo | `SPAN-ES-SABIEX-2021-LONGITUDES`, longitudes inversa y letras/números; selectores separados también disponibles | ES 55–87, escolaridad 3–22; no sustituir longitud máxima por total de aciertos | [Ecuaciones SABIEX](NORMALIZACIONES_REFERENCIA_2026_10_19.md); `NormalizacionReferenciasEspanaTest` |
| EJE-S04: planificación | `TOL-F-DE-GHS-2019-24`, percentil publicado | DE 60–79, categorías educativas 2–5, 24 problemas táctiles; sin fila cero | [Capítulo TOL-F](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md); `NormalizacionReferenciasGeriatricasAdicionalesTest` |
| EJE-S05: razonamiento no verbal | `RAVEN-A-CO-API-2019`, parte A de doce elementos | CO 61–64 y 66–86, escolaridad 1–25; no WAIS ni Raven completo | [Capítulo Raven](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md); misma clase |
| LEN-S01: denominación | `BNT-BO-2015-60`, espontáneos + ayudas semánticas permitidas | BO 18–95; versión 60 elementos | Fichas base; `NormalizacionBateriaBolivia2015Test`, `InterpretacionInstrumentalFaseTresTest` |
| LEN-S02: fluidez verbal | `FLUIDEZ-BO-2015-FAS-ANIMALES`, cuatro resultados separados | BO 18–95; 60 s cada tarea; no promediar los cuatro modelos | Fichas base; mismas dos clases |
| LEN-S03: comprensión | `TOKEN-CO-UNAL-2017-TABLAS`, escala edad y ajuste escolar | CO 60–90, escolaridad 0–18 con lectoescritura; abstención en celdas ambiguas o sin correspondencia | [Capítulo Token, tesis identificada](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md); `NormalizacionTokenColombiaInvestigadaTest` |
| LEN-S04: repetición | `TB-A-ES-NEURONORMA-2011-LENGUAJE`, diez palabras | ES 50–90; conversión categórica con efecto techo, no gradación diagnóstica | §4; `CierreComponentesFaseTresTest` |
| LEN-S05: discurso, alcance fluencia/gramática | Mismo método TB-A; habla espontánea original documentada | ES 50–90; no evaluación integral de coherencia, contenido o pragmática | §4; misma clase |
| VIS-S01: percepción visuoespacial | `JLO-ES-SABIEX-2021-30`, ambos segmentos correctos | ES 55–87, escolaridad 3–22; 30 elementos | [Ecuación JLO](NORMALIZACIONES_REFERENCIA_2026_10_19.md); `NormalizacionReferenciasEspanaTest` |
| VIS-S02: construcción | `ROCF-BO-2015-COPIA-A`, copia de 18 elementos | BO 18–95; medios puntos hasta 36; no usar norma de recuerdo | Fichas base; `NormalizacionBateriaBolivia2015Test`, `InterpretacionInstrumentalFaseTresTest` |

Las fichas base explican para qué, cómo y por qué de los ocho iniciales, con
referencias DOI y ejemplos aritméticos independientes. Los capítulos enlazados
aportan lo mismo para las ocho adiciones anteriores. La escolaridad, lengua y
condiciones de aplicación completas están en esos contratos; esta matriz no
los reemplaza. Se verificaron las once medidas de los ocho componentes iniciales.

## 3. Atención selectiva: cancelación P/W de SCAND

**Fuente primaria:** Lojo-Seoane et al., *Normative scores for attentional tests
used by the Spanish consortium for ageing normative data (SCAND) study: Trail
Making Test, Digit Symbol and Letter Cancellation*, *The Clinical
Neuropsychologist* 37(8), 1766–1786, 2023,
[DOI 10.1080/13854046.2023.2173304](https://doi.org/10.1080/13854046.2023.2173304).
Se consultó el [original depositado por una coautora](https://www.researchgate.net/publication/368454559_Normative_scores_for_attentional_tests_used_by_the_Spanish_consortium_for_ageing_normative_data_SCAND_study_Trail_Making_Test_Digit_Symbol_and_Letter_Cancellation),
no un resumen comercial: instrumentos p. 1771 y tablas 7–8 pp. 1778–1779.
El [registro de la Universidad de Santiago](https://investigacion.usc.gal/documentos/64046e84d5b0fa1e7b2771b0)
corrobora la identidad bibliográfica.

**Para qué sirve:** medir selección visual de objetivos entre distractores,
con control de omisiones y comisiones durante una tarea breve. **Por qué se usa:**
el original describe la administración, el índice de eficiencia y su conversión
con ajuste por edad y educación. Es una alternativa investigada para ATE-S02;
no se transfieren sus normas a Ruff, d2 o cancelación A.

**Cómo se usa:** un profesional aplica la forma original de 26 filas × 30
columnas, 65 objetivos P/W y 60 segundos. Coteja práctica, última zona explorada,
omisiones corregidas y comisiones. El software recibe los conteos documentados;
no reconstruye los objetivos omitidos como `65 − aciertos`, pues incluye zona
no explorada. No reproduce la lámina ni la administra por cuenta propia.

**Contexto:** estudio general n=2597, edad 50–98; datos válidos de eficiencia
n=1241. No se atribuyen 2597 observaciones a este índice. Se implementan solo las
filas de 60–98. Requiere español nativo, lectoescritura, condiciones sensoriales
y motoras compatibles, administración estandarizada y contexto español cotejado.
La categoría escolar publicada debe documentarse: 1 <6 años, 2 =6–11,
3 =12–15, 4 >15; no se deduce del nombre, edad o cargo.

```text
eficiencia = aciertos − (omisiones_corregidas + comisiones)
escalar_base = fila de eficiencia de la tabla 7
escalar_ajustado = escalar_base + ajuste[edad][nivel_educativo] de tabla 8
```

Los recuentos son enteros: aciertos y omisiones 0–65; comisiones 0–715;
aciertos + omisiones ≤65. Los negativos derivados se conservan. No se extrapola
fuera del soporte publicado −42..28 ni se recorta la escala ajustada a 18.
Se cotejaron independientemente **156 ajustes** (39 edades × cuatro categorías),
tomando las últimas cuatro columnas de tabla 8, las de eficiencia.

Ejemplo sintético: 14 aciertos, tres omisiones y una comisión → eficiencia 10;
tabla 7 → escalar 10; edad 78, categoría 1 → ajuste +3; resultado escalar 13.
Otro: 25, cero y cero, edad 98, categoría 4 → base 18 +2 =20. Son cálculos
de ejemplo, no mediciones de residentes ni resultados clínicos.

**Interpretación:** posición escalar en esa tarea y referencia. El rango
percentilar conservado corresponde al bruto antes del ajuste; no se anuncia
un percentil final ni se calcula z gaussiana desde el escalar. En la primera
fila el rango percentilar no se pudo asignar con seguridad desde la presentación
consultada: queda `null`, con advertencia. Esto no impide identificar su escalar.
No se deriva una alteración objetiva, dominio afectado ni DCL del índice aislado.
La misma cancelación no aporta además otra evidencia a ATE-S01.

## 4. Repetición y discurso: Test Barcelona Abreviado

**Fuente primaria:** Quintana et al., *Spanish Multicenter Normative Studies
(Neuronorma Project): Norms for the Abbreviated Barcelona Test*, *Archives of
Clinical Neuropsychology* 26(2), 144–157, 2011,
[DOI 10.1093/arclin/acq098](https://doi.org/10.1093/arclin/acq098).
Se obtuvo el [PDF completo del sitio del instrumento](https://test-barcelona.com/wp-content/uploads/2025/09/2010-Quint-TB-Norms-1.pdf):
Resultados p. 4, tabla 5 p. 6 y apéndice A pp. 9–10 de la paginación PDF,
distinta de la paginación de revista. Se cotejó visualmente tabla 5 y el texto.
Publicación anticipada de diciembre de 2010, volumen de 2011.

**Para qué sirven:** LEN-S04 registra reproducción de diez palabras del TB-A;
LEN-S05 registra fluencia y gramática de una muestra de habla espontánea.
**Por qué se usan:** el original define ambas tareas y una conversión explícita
para participantes de 50–90 años. No se sustituyen por WAB castellana, WAB-R,
TB revisado o anotaciones generales sobre conversación.

**Cómo se usan:** se recibe el número de palabras correctamente repetidas de
los diez elementos originales y, por separado, la puntuación profesional de
fluencia/gramática 0–10 según la rúbrica TB-A. Esta última requiere las tareas
de conversación, descripción y narración completadas y una muestra documentada.
El sistema no inventa una puntuación desde texto libre ni reproduce estímulos.
Requiere audición compatible, administración estandarizada, contexto normativo
español documentado e identidad `TB-A / NEURONORMA-2011-ABREVIADO / es`.

**Contexto y limitación decisiva:** muestra final n=346, edad 50–90, media 65.04;
140 varones y 206 mujeres; escolaridad media 10.56, DE 5.46. En estas dos tareas
el 100% obtuvo el máximo 10. El propio artículo define máximo → escalar 18 y
cualquier error → escalar 2. Es una conversión extrema por **efecto techo**:

| Puntuación original entera | Escalar publicado | Rango percentilar rotulado en la conversión |
| --- | ---: | --- |
| 10 | 18 | >99 |
| 0–9 | 2 | <1 |

La tabla numérica se conserva junto con su limitación; **no se interpreta un
10 como capacidad superior al 99% de la población**, porque toda la muestra
alcanzó el máximo. Tampoco se interpreta cualquier error como gravedad, DCL o
pérdida general del lenguaje. No se interpolan escalas intermedias ni se
construye z a partir de una variable sin variabilidad en esa muestra. Los
autores advierten sobre el uso de brutos ante efectos techo.

Ejemplo sintético: repetición 9/10 → escalar publicado 2; fluencia/gramática 10
→ escalar publicado 18 con advertencia. La salida no contiene un diagnóstico.
Si falta discurso, repetición puede seguir siendo interpretable y viceversa.
Una incompatibilidad del contexto compartido impide ambas comparaciones.

LEN-S05 queda cubierto en el alcance concreto de **fluencia/gramática del habla
espontánea**. No se afirma que esa puntuación evalúe por completo coherencia,
contenido semántico, pragmática o todas las formas de discurso.

## 5. Contratos de entrada, salida e integración

Clases nuevas: `NormalizadorReferenciaCancelacionScand2023` y
`NormalizadorReferenciaBarcelona2011`, dentro del motor PHP independiente.
`RegistroMetodosInstrumentales` publica identidades, tipos y despacha los métodos;
`CatalogoCapturaCognitiva` versión 8 presenta sus alternativas reales.
`ExplicacionMatematicaInstrumental` distingue `ESCALAR_AJUSTADO_TABULADO` de
`CONVERSION_PUBLICADA_CON_EFECTO_TECHO`. Blade no calcula estos resultados.

El contrato común conserva método, referencia, localizador, bruto, contexto,
componentes, motivos de abstención y límite interpretativo. Cada componente
contiene medida, tipo, estado técnico, dirección y puntuación original.

- SCAND: `escalar_base`, `ajuste_demografico`, `escalar_ajustado`,
  `rango_percentil_sin_ajuste`; `z=null`, `percentil=null`.
- TB-A: `tipo_comparacion=TABLA_CON_EFECTO_TECHO`, `escalar_publicado`,
  `rango_percentil_publicado`, advertencias; `z=null`.
- Ambos: `clasificacion_clinica=null`, `diagnostico=null`,
  `revision_profesional=PENDIENTE`, `adopcion_institucional=NO_CONFIGURADA`.

`NORMALIZADO` describe una conversión técnica compatible; no es «normal»,
resultado experto autorizado o aprobación profesional. Datos ausentes, falso,
cero, tipo incorrecto e identidad incompatible no se convierten entre sí.
Fuera del soporte se devuelve `NO_NORMALIZABLE` y un motivo explícito.

La prueba SQL recorre la Action autorizada existente → captura original →
consulta de perfil. Dos aplicaciones sintéticas aportan tres medidas, con
preguntas, autor, fecha y respuestas preservados: dos TB-A en una misma
aplicación y una SCAND en otra. No se produce una segunda medida atencional,
no se escribe una evaluación experta ni se alteran permisos o la historia real.
Los controles de acceso existentes forman parte de la suite de regresión.

## 6. Acceso, cotejo y huellas

| Artefacto realmente obtenido | Evidencia |
| --- | --- |
| PDF Barcelona, 148800 bytes | SHA-256 `072b60d9c3244326046fb46ad5f9de78422ca160e277c8ff3ee87a167bd0f9e5`; tabla 5 inspeccionada visualmente |
| SCAND, texto completo del original subido por coautora | Fórmula, protocolo, tabla 7 y tabla 8 cotejados; no se declara PDF obtenido |
| JSON temporal de cotejo numérico SCAND, 156 ajustes | SHA-256 `898be0ce138c24a353bc36cc707ac548d5ccb7f9cfad06251f589154c96e13e1`; extracción del original comparada con constante PHP, sin discrepancias |

La descarga PDF editorial SCAND devolvió 403. La ruta de depósito UCM devolvió
HTML de acceso, no PDF: no se trató como fuente de tablas ni se eludió el acceso.
Se utilizó el original completo consultable por lector web. Los temporales están
fuera del repositorio. No se suben artículos completos, reactivos, láminas ni
datos de personas. Los capítulos anteriores registran sus propias huellas y
los descartes Ruff, BDAE laboral y d2 de población joven.

## 7. Pruebas y revisión independiente

Suite relacionada, ejecutada el 19/10/2026:

```powershell
$filtro = 'CierreComponentesFaseTresTest|NormalizacionTokenColombiaInvestigadaTest|NormalizacionReferenciasGeriatricasAdicionalesTest|NormalizacionReferenciasEspanaTest|RecuerdoConClavesFcsrtInvestigadoTest|LenguajeWabCastellanoInvestigadoTest|InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest'
php artisan test --compact --filter=$filtro --colors=never
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro $filtro
```

- **SQLite: 373 pruebas / 9280 aserciones**, 100.47 s.
- **PostgreSQL desechable: 373 pruebas / 9279 aserciones**, 120.694 s.
  JUnit/log temporal `remembermind-pg-8ea5a80d907e43b2bcd9e4191d435d51`;
  servidor de prueba detenido. No se utilizó la BDD operativa.
- La condición explícita de español nativo SCAND se añadió después del inicio
  de estas suites. Verificación independiente posterior: **75 pruebas / 1036
  aserciones**, SQLite, 4.03 s, incluido el circuito SQL. Reejecución posterior
  en **PostgreSQL: 75 / 1036**, 4.407 s; JUnit/log temporal
  `remembermind-pg-ed93b851ed154539a1a6b364bc1d657d`, servidor detenido.

Filtro de la comprobación posterior, en ambos motores:

```powershell
$estrecho = 'CierreComponentesFaseTresTest|test_cierre_de_componentes_conserva_dos_aplicaciones_y_tres_medidas_autorizadas'
php artisan test --compact --filter=$estrecho --colors=never
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro $estrecho
```

Verificación final: sintaxis de diez PHP y Pint PASS, 97 enlaces locales en ocho
documentos comprobados sin enlaces rotos; `git diff --check` PASS.

Los nuevos casos cubren límites de las 17 filas escalares SCAND, eficiencia
negativa/cero, ajustes por edad y escolaridad, techo sin recortar, todas las
puntuaciones 0–10 TB-A, edad límite, faltantes por subprueba, contexto inválido,
tipos, ediciones ajenas, recuentos contradictorios y conservación de procedencia.
No se fabrican mediciones ni se usa el resultado esperado como entrada.

La revisión independiente de lectura, mediante `remembermind-expert-validation`,
cotejó las nuevas fuentes y los 156 ajustes: **PASS técnico**, sin hallazgos
bloqueantes. La revisión documental independiente también resultó PASS;
comprobó el conteo ejecutando el registro. Se corrigió su observación menor:
tres componentes completados mediante dos métodos nuevos. La validación clínica
permanece **BLOQUEADA por falta de juicio
experto/adopción documentada**, no por un fallo de cálculo. Esta revisión no
revalida clínicamente los dieciséis componentes anteriores. No hay métricas
de sensibilidad/especificidad, muestra de residentes ni aprobación clínica
inventadas. Las diferencias de una aserción entre motores se conservan.

## 8. Archivos propios de este cierre

- `motor-experto/src/motor_experto/dominio/instrumentos/NormalizadorReferenciaBarcelona2011.php` (nuevo).
- `motor-experto/src/motor_experto/dominio/instrumentos/NormalizadorReferenciaCancelacionScand2023.php` (nuevo).
- `motor-experto/src/motor_experto/dominio/instrumentos/RegistroMetodosInstrumentales.php`.
- `motor-experto/src/motor_experto/dominio/instrumentos/CatalogoCapturaCognitiva.php`.
- `motor-experto/src/motor_experto/dominio/instrumentos/ExplicacionMatematicaInstrumental.php`.
- `tests/Unit/CierreComponentesFaseTresTest.php` (nuevo).
- `tests/Unit/NormalizacionReferenciasEspanaTest.php`.
- `tests/Unit/NormalizacionReferenciasGeriatricasAdicionalesTest.php`.
- `tests/Unit/LenguajeWabCastellanoInvestigadoTest.php`.
- `tests/Feature/CapturaInstrumentalInstitucionalTest.php`.
- Este documento (nuevo), `FASE_3_COMPONENTES_2026_10_19.md`,
  `INVESTIGACION_PROFESIONAL_19_COMPONENTES_2026_10_19.md`,
  `MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md`,
  `NORMALIZACIONES_REFERENCIA_2026_10_19.md`,
  `REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md`,
  `PLAN_CIERRE_INTEGRAL_2026_10_19.md` y `README.md`, todos bajo `docs/sistema-experto`.

No hay cambios frontend: build y QA visual no aplican a este incremento.
No se incorpora dominio V1, SQL al motor, migración, tabla, permiso, regla,
umbral, binding operativo ni datos reales. Los cambios concurrentes de otras
tareas se mantienen fuera de este cierre y de su commit local; no hay push.

## 9. Qué queda y qué se considera cerrado

**Cerrado:** cobertura técnica de los 19 componentes, investigación de sus
métodos, operaciones reproducibles, alcance interpretativo, límites y pruebas.
Las tres brechas de comparación de la versión 7 quedan resueltas con SCAND y
TB-A; las cifras 8/11/16 de los documentos previos son cortes históricos.

**Pendiente institucional:** decidir y documentar pertinencia/adopción de cada
instrumento y referencia para la residencia; disponer de materiales legítimos y
mediciones realmente aplicadas; revisar profesionalmente resultados y utilidad
clínica; resolver personas fuera del soporte sin extrapolar. El TB-A de techo
acusado no permite graduar por sí solo un hallazgo cognitivo. La selección
técnica de una alternativa no impone sustituir el protocolo institucional.

**Pendiente de las fases de integración:** definir y validar la contribución de
medidas a hallazgos, dominios y criterios clínicos, conservación completa y
recorrido operativo. Un z, escalar o percentil aislado no activa DCL/Petersen.
Este incremento no ejecuta G09, Petersen ni TEST_ONLY sobre residentes y no
inicia por sí mismo otra fase del plan.
