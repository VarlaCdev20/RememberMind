# O.R.I.O.N. — Investigación de los 19 componentes cognitivos

Fecha de corte: 19 de octubre de 2026. Versión documental 1.0.
Destinatarios: Medicina, Psicología y equipo de desarrollo de RememberMind.
Estado: investigación e implementación instrumental parcial; no validación clínica.

## Resultado de esta revisión

Se contrastaron los ocho componentes que ya tenían cálculo con las publicaciones
originales. Se añaden ejemplos independientes para sus once medidas, explicación
matemática y un cálculo descriptivo de reconocimiento HVLT-R. Los **once componentes
pendientes de normalización siguen pendientes**: una resta descriptiva no cierra
una norma, y una referencia española no se convierte en boliviana por programarla.

Este informe responde para cada componente: para qué sirve, cómo se obtiene la
medición, por qué se considera, qué operación corresponde y qué interpretación
puede sostenerse. La descripción de uso es conceptual; las instrucciones exactas,
reactivos, entrenamiento, reglas de interrupción y rúbricas corresponden a la
edición legítima elegida. No se incluyen listas, láminas ni respuestas protegidas.

El [objetivo activo](FASE_3_COMPONENTES_2026_10_19.md) conserva el compromiso de
completar los diecinueve; el [plan integral](PLAN_CIERRE_INTEGRAL_2026_10_19.md)
separa este trabajo de adquisición, inferencia, conservación y revisión profesional.

## Método de investigación y nivel de evidencia

Primero se revisaron el catálogo, registro de métodos, normalizadores, extractor,
perfil y documentación V2 del repositorio. Después se consultaron publicaciones
originales de NeuroRehabilitation, registros de los estudios originales en PubMed
y documentación de los editores PAR, Pearson y MHS. Las búsquedas incluyeron los
nombres de los once componentes, sus instrumentos, «Bolivia», «normative data»,
adultos mayores, edición y DOI. Se buscaron alternativas geriátricas y diferencias
entre ediciones. No es una revisión sistemática exhaustiva ni demuestra que no
existan otras normas.

Se distingue acceso al artículo completo, resumen bibliográfico y ficha editorial.
Los siete PDF de la serie boliviana de 2015 permiten contrastar coeficientes y
localizadores. Para varias referencias NEURONORMA se verificaron resumen/identidad,
pero no se obtuvo aquí una copia íntegra verificable de sus tablas. Los enlaces
antiguos `com_attachments` de Test Barcelona aparecen indexados como artículos,
pero la descarga revisada devolvió HTML del sitio actual: **no se trató ese HTML
como un manual ni como un PDF científico**. Los manuales comerciales no se
consideran consultados por disponer de una ficha o informe de ejemplo.

Las etiquetas de cobertura de este informe son de ingeniería documental, no
estados clínicos nuevos:

| Cobertura | Significado |
| --- | --- |
| Norma implementada con condiciones | Cálculo contrastable con una referencia concreta; no aprobación clínica institucional |
| Cálculo descriptivo sin norma | Operación sobre brutos; no comparación con población ni clasificación |
| Método candidato documentado | Instrumento pertinente identificado; edición, rúbrica o norma aplicable aún sin resolver |

## Condiciones comunes de interpretación

La serie de 2015 incluye participantes comunitarios de 18–95 años, español nativo,
nacidos y residentes en el país de referencia, alfabetizados y con al menos un año
de educación. Describe controles sanos; no exige que una persona con sospecha de
dificultad sea sana para compararla. La validez en una residencia, bilingüismo,
escolaridad cero o condiciones diferentes no queda demostrada por compartir país.
[HVLT-R, §2.1](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151286).

En los modelos implementados `E=0` para 1–12 años escolares y `E=1` para más de 12;
`z=(bruto−predicho)/DE residual`. Escolaridad ausente o cero no se sustituye por E=0.
El signo de z expresa posición respecto del predicho. No se fija un umbral de
alteración, no se promedian componentes y no se transforma una observación en
medición instrumental. Cada aplicación conserva identidad, fecha, autor y fuente.

## Fichas de memoria

### 1. MEM-S01 — Aprendizaje y adquisición

**Para qué / por qué:** examinar adquisición verbal a través de ensayos; permite
separarla del recuerdo posterior. **Uso:** HVLT-R forma 5 en español; conservar
los aciertos de los tres ensayos, cada uno entre 0 y 12, sin reconstruirlos desde
etiquetas de desempeño. **Cálculo:** total 0–36 y modelo de tabla 2. **Interpretación:**
posición del total respecto del predicho, sin afirmar capacidad global de memoria.
**Cobertura:** norma implementada con condiciones.
[Arango-Lasprilla et al., 2015, §2.2 y tabla 2](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151286).

### 2. MEM-S02 — Recuerdo diferido libre

**Para qué / por qué:** examinar recuperación verbal después de una demora, distinta
del aprendizaje inicial. **Uso:** misma aplicación HVLT-R, aciertos 0–12 y demora
efectiva 20–25 minutos. **Cálculo:** modelo propio de tabla 4; Bolivia no incluye
escolaridad en este predicho. **Interpretación:** comparación de esta medida, sin
atribuir automáticamente pérdida de almacenamiento. **Cobertura:** norma
implementada; una demora incompatible impide normalizar el diferido.
[Misma publicación, §2.2 y tabla 4](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151286).

### 3. MEM-S03 — Recuerdo con claves

**Para qué / por qué:** estudiar qué ocurre al facilitar claves después del recuerdo
libre, controlando cómo se adquirió el material. **Uso candidato:** FCSRT, versión
exacta identificada; conservar codificación, libre y recuperación adicional con
claves por ensayo, diferido y demora. Una clave no permite contar nuevamente un
elemento ya recuperado libremente. **Cálculo propuesto:** totales separados de
libre y libre más adicional, solo tras cotejar rúbrica y máximos de la versión.
**Interpretación:** la diferencia entre condiciones no demuestra por sí sola una
etiología ni un déficit de almacenamiento. **Pendiente:** el estudio NEURONORMA
es español; faltan tablas íntegras verificadas y decisión de aplicabilidad a la
población institucional. No se programaron ajustes de memoria de otra prueba.
[Peña-Casanova et al., 2009, DOI acp041](https://pubmed.ncbi.nlm.nih.gov/19661107/).

### 4. MEM-S04 — Reconocimiento

**Para qué / por qué:** examinar identificación del material presentado y falsas
alarmas, separando reconocimiento de evocación. **Uso:** recuentos originales de
aciertos y falsas alarmas relacionadas/no relacionadas, forma e intervalo.
**Cálculo añadido:** `aciertos−(falsas relacionadas+no relacionadas)`; ejemplo
sintético `10−(2+1)=7`. Es una diferencia bruta, no una norma de reconocimiento.
**Interpretación:** sin baremo compatible; no usar las regresiones de MEM-S01/02.
FITBIR publica la fórmula pero declara rango 0–12: la resta puede ser negativa.
Se conserva el signo y el conflicto, sin truncamiento ni aceptación como índice
normativo validado. **Cobertura:** cálculo descriptivo; normalización pendiente.
[NIH/FITBIR, elemento HVLTRRecogDiscrimIndexRawScore, v1.2](https://fitbir.nih.gov/dictionary/publicData/dataElementAction!view.action?dataElementName=HVLTRRecogDiscrimIndexRawScore&publicArea=true&style.key=fitbir-style).

## Fichas de atención

### 5. ATE-S01 — Atención sostenida

**Para qué / por qué:** examinar mantenimiento del desempeño a lo largo de una
tarea, conservando velocidad y precisión. **Uso candidato:** Ruff 2 y 7, veinte
ensayos; registrar condición, duración, aciertos, errores y omisiones por ensayo.
**Cálculo:** índices específicos del manual; no se inventa una pendiente temporal
o un porcentaje de precisión con denominador supuesto. **Interpretación:** evita
atribuir falta de atención a lentitud motora aislada. **Pendiente:** rango editorial
16–70 años; no extrapolar a mayores. Se documentó una ampliación publicada para
varones mayores, que no resuelve automáticamente sexo, idioma ni contexto boliviano.
[PAR, Ruff 2 y 7](https://www.parinc.com/products/RUFF2AND7),
[Caban-Holt et al., 2012](https://pubmed.ncbi.nlm.nih.gov/22651854/).

### 6. ATE-S02 — Atención selectiva

**Para qué / por qué:** examinar selección de objetivos entre distractores.
**Uso candidato:** Ruff distingue diez ensayos de detección automática y diez
de búsqueda controlada. **Cálculo:** comparar condiciones según la edición y
su manual, sin atribuir un índice de procesamiento de otra publicación a la
edición comercial. **Interpretación:** requiere considerar errores y velocidad.
**Pendiente:** misma brecha normativa y de manual que ATE-S01; ambas condiciones
pertenecen a la misma aplicación y no son dos episodios independientes.
[PAR, estructura y puntuación del Ruff](https://www.parinc.com/products/RUFF2AND7).

### 7. ATE-S03 — Atención dividida auditiva

**Para qué / por qué:** valorar procesamiento auditivo con demandas diferentes de
las tareas visuales/motoras. **Uso:** BTA, formas N y L, diez respuestas puntuables
por forma; audición y administración documentadas. **Cálculo:** N+L, 0–20, tabla 2
boliviana. **Interpretación:** comparación de esa tarea auditiva, no sustituto de
todos los tipos de atención. **Cobertura:** norma implementada. El ejemplo de §3.1
no coincide con sus coeficientes redondeados: a 60 años y E=0, estos dan 11.402,
mientras el texto presenta 11.424. Se usan los coeficientes publicados; no se
afirma que repliquen parámetros originales de precisión no publicada.
[Rivera et al., 2015, tabla 2 y §3.1](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151283).

## Fichas de funciones ejecutivas

### 8. EJE-S01 — Control inhibitorio

**Para qué / por qué:** estudiar interferencia, diferenciándola de lectura y
denominación de colores. **Uso:** Stroop Golden, tres condiciones de 45 segundos;
conservar W, C y WC correctos. **Cálculo:** `WC−W×C/(W+C)`, denominador positivo;
comparación por puntos de tabla A35 Bolivia. **Interpretación:** intervalo
percentilar aproximado; no z ni clasificación diagnóstica. **Cobertura:** norma
implementada con límites abiertos fuera de P5–P95. El `P+W` de §2.2 queda señalado
como denominador no definido, contrastado con la formulación publicada de Golden.
[Rivera et al., 2015, A35](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151281),
[Periáñez et al., DOI acaa034](https://academic.oup.com/acn/article-pdf/36/1/99/35858033/acaa034.pdf).

### 9. EJE-S02 — Flexibilidad cognitiva

**Para qué / por qué:** examinar alternancia bajo demandas de búsqueda y trazado.
**Uso:** TMT parte B de la versión identificada, segundos, errores y finalización.
**Cálculo:** tiempo exacto finalizado, modelo boliviano tabla 4; tope de
administración B de 300 segundos. **Interpretación:** menor tiempo es más rápido;
z positivo conserva el sentido de mayor lentitud. **Cobertura:** norma implementada.
Una interrupción a 300 no significa finalización en 300; no se convierte en dato
exacto. Visión y motricidad limitan la especificidad del resultado ejecutivo.
[Arango-Lasprilla et al., 2015, §2.2 y tabla 4](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151284).

### 10. EJE-S03 — Manipulación de memoria de trabajo

**Para qué / por qué:** estudiar mantenimiento y transformación de información,
separados de repetición directa. **Uso candidato:** dígitos inversos/crecientes
de WAIS identificada; puntajes originales por condición. **Cálculo:** bruto de
subprueba y transformación con la tabla de su edición, no un z construido a
partir de un ejemplo de informe. **Interpretación:** span y puntuación de aciertos
son medidas distintas. **Pendiente:** elegir adaptación y referencia compatible.
NEURONORMA estudia span y Letras y Números de WAIS-III en 354 participantes de
50–90 años en España; no es baremo de WAIS-IV boliviano.
[Peña-Casanova et al., 2009, acp038](https://pubmed.ncbi.nlm.nih.gov/19661109/),
[Pearson, ejemplo de procesamiento WAIS-IV](https://www.pearsonclinical.es/portals/0/docproductos/13534059942.pdf).

### 11. EJE-S04 — Planificación

**Para qué / por qué:** examinar resolución planificada conservando exactitud,
movimientos, tiempos y violaciones. **Uso candidato:** Torre de Londres, edición
y forma declaradas, sin mezclar problemas ni reglas entre versiones.
**Cálculo:** índices de movimientos, soluciones, inicio/ejecución y violaciones
según manual; no existe aquí un score universal de «la torre».
**Interpretación:** no se concluye lesión frontal ni DCL a partir de un total.
**Pendiente:** cotejar TOL-DX de NEURONORMA frente a segunda edición y población.
MHS declara adultos 16–80 para la segunda edición: esa ficha no cubre a una
persona de 81 años ni acredita normas bolivianas.
[NEURONORMA, acp043](https://doi.org/10.1093/arclin/acp043),
[MHS, TOL-DX segunda edición](https://storefront.mhs.com/collections/toldx-2nd-ed).

### 12. EJE-S05 — Razonamiento y abstracción

**Para qué / por qué:** estudiar relaciones conceptuales verbales o no verbales,
identificando cuál se midió. **Uso candidato:** Semejanzas o Matrices de una
adaptación WAIS concreta; conservar subprueba y corrección original.
**Cálculo:** bruto y conversión de esa subprueba por edad/edición; no sumar los dos
brutos ni sustituirlos por categorías de M-WCST. **Interpretación:** resultados
contextuales de tareas, no razonamiento general íntegro. **Pendiente:** rúbrica,
baremo y uso institucional. Pearson distingue versiones españolas para España
y México; compartir idioma no acredita equivalencia nacional.
[Pearson, versiones nacionales](https://support.pearson.com/usclinical/s/article/WAIS-IV-Spanish-Version-Availability),
[Pearson España, WAIS-IV](https://www.pearsonclinical.es/es-es/Tienda/Evaluaciones-Profesionales/p/P100200036).

## Fichas de lenguaje

### 13. LEN-S01 — Denominación

**Para qué / por qué:** examinar denominación por confrontación, diferenciando
acierto espontáneo y ayudas. **Uso:** BNT estándar de 60 elementos y protocolo
identificado. **Cálculo:** espontáneos más adicionales con clave semántica;
ayudas fonémicas separadas; modelo boliviano tabla 2. **Interpretación:** posición
de ese total respecto de la referencia, considerando familiaridad cultural y
condición visual. **Cobertura:** norma implementada. No incorpora ajuste etario
boliviano ni aplica la tabla de 60 a una forma de 15 elementos.
[Olabarrieta-Landa et al., 2015, §2.2 y tabla 2](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151278).

### 14. LEN-S02 — Fluidez verbal

**Para qué / por qué:** estudiar acceso léxico bajo consigna fonológica y semántica.
**Uso:** condiciones F, A, S y animales de 60 segundos, válidas y errores separados.
**Cálculo:** cuatro predichos y z propios; no promedio ni norma FAS inventada.
**Interpretación:** comparación específica de cada condición; no convierte cuatro
condiciones de una aplicación en cuatro corroboraciones independientes.
**Cobertura:** norma implementada. Una condición incompleta conserva su motivo
y no borra las válidas.
[Olabarrieta-Landa et al., 2015, tablas 2/4/6/8](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151279).

### 15. LEN-S03 — Comprensión lingüística

**Para qué / por qué:** estudiar comprensión de instrucciones con materiales y
demandas definidos, diferenciándola de una impresión cotidiana.
**Uso candidato:** Token Test, forma abreviada exacta; registrar presentaciones,
puntajes según rúbrica y condiciones auditivas/visuales. **Cálculo:** total de la
versión administrada; no forzar enteros cuando su rúbrica admite medios puntos.
**Interpretación:** depende también de audición, visión, comprensión de consigna
y contexto. **Pendiente:** manual y tablas íntegras; NEURONORMA es referencia
española. Un baremo pediátrico latinoamericano no resuelve esta evaluación geriátrica.
[Peña-Casanova et al., 2009, acp039](https://pubmed.ncbi.nlm.nih.gov/19648582/).

### 16. LEN-S04 — Repetición verbal

**Para qué / por qué:** examinar reproducción del lenguaje escuchado, separada de
la producción espontánea. **Uso candidato:** subprueba WAB-R, forma completa o
bedside explícita, respuesta y puntuación original. **Cálculo:** total conforme
a rúbrica de esa forma; sin máximo o factor importado de otra versión.
**Interpretación:** una clasificación de afasia no equivale a DCL.
**Pendiente:** ficha editorial consultada en inglés; no se verificó aquí edición
española y norma compatible. La ficha comercial no proporciona la rúbrica completa.
[Pearson, WAB-R](https://www.pearsonassessments.com/store/en/usd/p/100000194.html).

### 17. LEN-S05 — Producción verbal y discurso

**Para qué / por qué:** estudiar discurso obtenido en una tarea identificable.
**Uso candidato:** muestra de habla de WAB-R con referencia al original, contenido
y fluidez separados. **Cálculo:** puntuación mediante rúbrica; no análisis
automático de una nota de cuidados. **Interpretación:** describe la muestra y
sus limitaciones; repetición conservada no demuestra discurso conservado.
**Pendiente:** misma identificación de idioma, edición, rúbrica y norma que LEN-S04.
No se duplicó la batería para fabricar evaluaciones independientes.
[Pearson, WAB-R, forma y manuales](https://www.pearsonassessments.com/store/en/usd/p/100000194.html).

## Fichas visuoespaciales

### 18. VIS-S01 — Relaciones espaciales

**Para qué / por qué:** examinar percepción espacial con una tarea identificada;
permite distinguirla de la producción de un dibujo. **Uso candidato:** JLO o
subprueba concreta VOSP, con control visual y aciertos originales.
**Cálculo:** total de esa forma y conversión normativa propia; no total VOSP
genérico ni máximo JLO aplicado a VOSP. **Interpretación:** un resultado no
representa todas las habilidades visuoespaciales. **Pendiente:** manual, forma
y norma aplicable. NEURONORMA aporta normas de edad/escolaridad en 341 personas
cognitivamente normales y comunitarias de España, mayores de 49 años.
[Peña-Casanova et al., 2009, acp040](https://pubmed.ncbi.nlm.nih.gov/19648584/).

### 19. VIS-S02 — Visuoconstrucción

**Para qué / por qué:** examinar organización de una copia, separada de su recuerdo.
**Uso:** figura A de Rey–Osterrieth, fase copia; producción original corregida
en 18 elementos por el profesional. **Cálculo:** puntuación 0–36 en medios puntos;
modelo boliviano tabla 2. **Interpretación:** comparación de copia, considerando
visión y motricidad; no interpretación del dibujo generada por software.
**Cobertura:** norma implementada; no sustituir por la norma de recuerdo inmediato.
[Arango-Lasprilla et al., 2015, §2.2 y tabla 2](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151285).

## Hoja de cálculo contrastada de los ocho componentes

La transcripción de ecuaciones, DE y edición queda en
[Métodos V2](METODOS_Y_CAMPOS_INVESTIGADOS_V2_2026_10_18.md#métodos-ejecutables-y-ecuaciones).
Caso sintético de comprobación: edad 70, escolaridad 4 (E=0), identidad y contexto
compatibles. Los z de esta tabla se obtuvieron por separado con aritmética decimal
a partir de bruto, predicho y DE publicados; las pruebas contienen los literales
esperados, sin llamar al algoritmo para construir su propio resultado esperado.

| Componente / medida | Bruto | Predicho | DE residual | z de referencia |
| --- | ---: | ---: | ---: | ---: |
| MEM-S01, ensayos 6+7+8 | 21 | 16.713 | 4.213 | 1.017564680750 |
| MEM-S02, diferido | 4 | 5.222 | 2.424 | −0.504125412541 |
| ATE-S03, N=6/L=7 | 13 | 10.812 | 3.786 | 0.577918647649 |
| EJE-S02, segundos B finalizado | 200 | 228.979 | 69.661 | −0.416000344526 |
| LEN-S01, 40+5 | 45 | 45.102 | 8.732 | −0.011681172698 |
| LEN-S02, F | 8 | 7.380 | 3.248 | 0.190886699507 |
| LEN-S02, A | 9 | 7.151 | 3.441 | 0.537343795408 |
| LEN-S02, S | 10 | 6.233 | 3.405 | 1.106314243759 |
| LEN-S02, animales | 12 | 11.013 | 4.552 | 0.216827768014 |
| VIS-S02, copia | 22.5 | 22.833 | 6.728 | −0.049494649227 |

EJE-S01 usa otro tipo de salida: W=60, C=30, WC=21 produce interferencia 1,
entre P40 y P50 de A35. No se calcula z. Las pruebas previas añaden otra edad y
escolaridad, incompatibilidades, ceros, interrupciones y extremos percentilares.
Estos son casos aritméticos independientes, no evaluaciones de residentes ni
validación por un segundo profesional.

## Resolución de las brechas: qué falta exactamente

| Pendiente | Investigación aportada | Condición concreta para completar cálculo normativo |
| --- | --- | --- |
| MEM-S03 | FCSRT / acp041; adquisición libre y adicional separadas | Versión y rúbrica legítimas, tablas verificadas, población aplicable decidida |
| MEM-S04 | Fórmula NIH/FITBIR; cálculo bruto implementado y conflicto documentado | Norma específica de reconocimiento y resolución del rango con manual/editor |
| ATE-S01/S02 | Ruff comercial y ampliación para varones mayores | Manual/edición y norma que cubra edad, idioma y población elegidos |
| EJE-S03 | WAIS y alternativa NEURONORMA WAIS-III | Adaptación/condición y tablas compatibles; no cruce III/IV |
| EJE-S04 | TOL-DX / segunda edición, diferencias documentadas | Edición, índices, tablas y cobertura de mayores de 80 si corresponde |
| EJE-S05 | Semejanzas/Matrices, versiones España/México | Subprueba y referencia aplicable; interpretación institucional acordada |
| LEN-S03 | Token / acp039 | Versión/rúbrica, tablas verificadas y aplicabilidad |
| LEN-S04/S05 | WAB-R, forma completa/bedside | Edición española legítima identificada o sustitución profesionalmente decidida |
| VIS-S01 | VOSP/JLO / acp040 | Instrumento/subprueba, tablas íntegras y aplicabilidad |

Investigar no permite decidir silenciosamente que una norma extranjera ya es
aplicable. Las alternativas anteriores son candidatas de trabajo; no se hizo una
selección clínica institucional nueva. Tampoco se comprarán instrumentos ni se
enviarán datos clínicos a plataformas editoriales como efecto de esta investigación.

### Conflicto editorial adicional: edad WAIS

La ficha española actual indica 16–90 años, mientras material editorial previo
del catálogo 2024 indica 16–89:11. Q-global muestra 16:0–90:11 en su documentación
de entrada, pero ese idioma de interfaz no identifica la adaptación nacional.
No se eligió el límite más amplio ni se creó un validador genérico de edad.
Debe prevalecer el manual exacto de la edición seleccionada.
[Ficha actual](https://www.pearsonclinical.es/es-es/Tienda/Evaluaciones-Profesionales/p/P100200036),
[catálogo editorial 2024](https://www.pearsonassessments.com/content/dam/school/global/clinical/us/assets/latam/2024-latam-catalogo.pdf),
[Q-global](https://qglobal.pearsonclinical.com/qg/static/Product/es/WAIS-IV/WAIS-IV_Enter_Scores.htm).

## Contratos implementados y ubicación en O.R.I.O.N.

`RegistroMetodosInstrumentales::ejecutar()` conserva las puntuaciones y añade
`interpretacion_matematica` a cada medida individual, también en la fluidez parcial.
Contrato `EXPLICACION-MATEMATICA-1`: comparación con predicho, intervalo
percentilar, descriptivo sin comparación o información insuficiente. Una dirección
ausente o z no finito no se explica como rendimiento normal. Los normalizadores
directos conservan su salida; el registro común agrega la explicación.

El registro incorpora `HVLT-R-F5-RECONOCIMIENTO-BRUTO-1`, de tipo
`CALCULO_DESCRIPTIVO`, con identidad HVLT-R/forma 5/español. Requiere tres
recuentos originales, demora 20–25 y administración documentada. Devuelve
`CALCULADO_SIN_NORMA` o `NO_CALCULABLE`, originales, motivos, advertencias y
clasificación clínica nula. El rango algebraico de la diferencia es −12 a 12;
no se presenta como rango normativo validado del manual.

El catálogo `CAPTURA-COGNITIVA-3-20261019` separa
`metodos_disponibles` (normativos) de `calculos_descriptivos_disponibles`.
Se mantienen ocho componentes con norma, once pendientes de normalización y un
complemento bruto. `PerfilInstrumentalCognitivo` conserva ese complemento sin
contarlo como medición normalizada ni inferir un resultado de memoria.

El extractor y la consulta siguen exigiendo selección e identidad explícitas de
las aplicaciones originales y acceso autorizado. Este incremento no configura
bindings, no ejecuta inferencia en residentes, no cambia permisos/BDD y no
persiste una evaluación. La explicación queda en el contrato técnico; esta
entrega no añade controles ni textos clínicos nuevos a Blade.

## Verificación y límites de entrega

Los resultados ejecutados y archivos se registran en el
[seguimiento de fase 3](FASE_3_COMPONENTES_2026_10_19.md#verificación-del-incremento).
Las pruebas cubren cálculos de los ocho componentes, dirección del tiempo,
datos ausentes, cero, tipos incompatibles, falsas alarmas, resultados negativos,
intervalos abiertos, parcialidad y preservación de clasificación nula.

No se declara cerrada la fase 3. Falta normalización aplicable de once componentes,
la selección institucional de ediciones/referencias y la evaluación profesional
con datos originales. No hay evidencia de sensibilidad/especificidad, acuerdo
entre profesionales ni validación de DCL aportada por estas pruebas técnicas.
