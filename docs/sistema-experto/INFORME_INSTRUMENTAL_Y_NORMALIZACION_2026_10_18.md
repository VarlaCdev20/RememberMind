# Evaluación cognitiva y métodos instrumentales para RememberMind

Informe técnico de evidencia para Medicina y Psicología. Versión 1.0, 18 de octubre de 2026.

## Decisión y aplicación

La investigación identifica instrumentos y referencias para estudiar los cinco dominios y los 19 componentes del sistema. La aplicación inmediata incorpora al motor dos modelos de normalización bolivianos publicados para HVLT-R forma 5. Conserva separados el dato registrado, la puntuación, la referencia normativa y la interpretación profesional. No añade tablas ni modifica las transcripciones originales.

Los 19 registros nominales del paquete describen desempeño por componente. Sus opciones no contienen aciertos por ensayo, tiempos, falsas alarmas o rúbricas originales. Aunque sean transcripciones reales, esas categorías no permiten reconstruir una prueba estandarizada. El sistema conserva esas observaciones y solicita las mediciones concretas que corresponden a cada método, sin cambiarles la identidad del instrumento.

El cálculo no requiere una firma profesional previa para estar programado. La confirmación o rechazo del resultado se registra al final, por Medicina y Psicología en sus cadenas separadas. La revisión posterior no sustituye mediciones ausentes ni otorga una licencia de uso de pruebas.

## Fundamento clínico

La guía DETeCD-ADRD propone integrar historia, información de personas cercanas, funcionamiento cotidiano y exploración de varios dominios, considerando educación, idioma, cultura y comorbilidad. Un resultado de cribado no se distribuye entre los 19 componentes ni determina automáticamente un patrón cognitivo. La matriz siguiente selecciona candidatos por constructo; no afirma que se hayan administrado a los residentes. [1]

El NIA recomienda evaluar las preocupaciones cognitivas y buscar causas que puedan explicar o agravar los síntomas. Las observaciones longitudinales son útiles para orientar esa evaluación y documentar el cambio. [2] NICE indica que una puntuación aparentemente normal no excluye por sí sola demencia y recomienda estudiar causas reversibles, estado sensorial y diagnóstico diferencial. [3]

## Memoria de aprendizaje y recuperación

| Componente | Medición que debe conservarse | Instrumento o referencia y límite |
| --- | --- | --- |
| MEM-S01 Aprendizaje y adquisición | Aciertos separados en tres ensayos y total | HVLT-R forma 5, norma boliviana de 2015. Método implementado. [4] |
| MEM-S02 Recuerdo diferido libre | Aciertos y demora efectiva | HVLT-R forma 5, modelo diferido propio. Método implementado. [4] |
| MEM-S03 Recuerdo con claves | Recuperación libre y con claves por ensayo, codificación y versión | FCSRT, referencia NEURONORMA española. HVLT-R no sustituye esta fase. No se transfiere esa norma española a Bolivia. [5] |
| MEM-S04 Reconocimiento | Aciertos, falsas alarmas, forma y condición | HVLT-R dispone de reconocimiento; su norma debe identificarse específicamente. El artículo de 2015 usado aquí no normaliza esta medida. [6] |

### Cálculo aplicado al motor

Para HVLT-R forma 5, la referencia incluye personas de 18 a 95 años, español nativo, nacidas y residentes en el país, con al menos un año escolar y lectoescritura. La administración del recuerdo libre diferido utiliza 20 a 25 minutos. [4, secciones 2.1 y 2.2]

```text
E = 0 para escolaridad de 1 a 12 años; E = 1 para más de 12 años
Predicho total = 24.553 - 0.112 × edad + 3.264 × E
z total = (total observado - predicho total) / 4.213
Predicho diferido = 9.702 - 0.064 × edad
z diferido = (diferido observado - predicho diferido) / 2.424
```

La tabla 2 normaliza el total; la tabla 4, el diferido sin ajuste por sexo o escolaridad. La selección de una muestra sana no exige un paciente previamente sano. La generalización cultural e institucional es limitada. [4]

El software devuelve bruto, predicho, z, referencia y revisión pendiente. No convierte z en diagnóstico ni incorpora un corte de DCL. Una persona con escolaridad cero no se incluye en el grupo de 1 a 12 años. Si falta identidad, contexto o medición, conserva el bruto y enumera la causa de no normalización.

## Atención

| Componente | Medición que debe conservarse | Instrumento o referencia y límite |
| --- | --- | --- |
| ATE-S01 Atención sostenida | Aciertos, omisiones, errores y velocidad por ensayo | Ruff 2 y 7 es candidato; el rango editorial es 16 a 70 años. No extrapolar sus normas a mayores de 70. [7] |
| ATE-S02 Atención selectiva | Condición de búsqueda, aciertos, errores y tiempo | Ruff 2 y 7 distingue condiciones atencionales. Falta una norma boliviana geriátrica aplicable en las fuentes revisadas. [7] |
| ATE-S03 Atención dividida | Respuestas correctas por lista y condición auditiva | BTA dispone de estudio latinoamericano con Bolivia. Verificar versión, audición y protocolo; no usarlo como sustituto universal de atención sostenida. [8] |

## Función ejecutiva

| Componente | Medición que debe conservarse | Instrumento o referencia y límite |
| --- | --- | --- |
| EJE-S01 Control inhibitorio | Palabra, Color, Palabra Color e índice de interferencia separados | Stroop, referencia latinoamericana con Bolivia. No intercambiar indicadores o ediciones. [9] |
| EJE-S02 Flexibilidad cognitiva | Tiempo, errores y finalización de TMT B; o categorías y perseveraciones de M WCST | Ambos cuentan con estudios latinoamericanos; sus medidas son diferentes. No combinar sus puntuaciones como si fueran equivalentes. [10, 11] |
| EJE-S03 Manipulación de memoria de trabajo | Ensayos y subprueba de manipulación, separados del span directo | Dígitos inversos o secuenciación, según edición. No transferir normas WAIS III a WAIS IV. [12] |
| EJE-S04 Planificación | Movimientos, problemas resueltos, tiempos y violaciones de regla | Torre de Londres Drexel tiene referencia NEURONORMA española. No mezclar variantes de la torre ni asumir normas bolivianas. [13] |
| EJE-S05 Razonamiento y abstracción | Subprueba concreta, respuestas y rúbrica | M WCST aporta formación conceptual; no representa por sí solo todo el razonamiento. Semejanzas o Matrices son candidatos que requieren su edición y manual. [11, 12] |

El estudio TMT de 2015 exigió español nativo, escolaridad mínima de un año y lectoescritura. Sus topes de administración no son puntos de diagnóstico. La escolaridad cero o una aplicación incompatible impiden trasladar automáticamente esa referencia. [10]

## Lenguaje

| Componente | Medición que debe conservarse | Instrumento o referencia y límite |
| --- | --- | --- |
| LEN-S01 Denominación | Respuestas espontáneas, claves y corrección por ítem | BNT estándar, estudio latinoamericano con Bolivia. Esa referencia no se aplica automáticamente al BNT de 15 ítems. [14] |
| LEN-S02 Fluidez verbal | Palabras válidas, repeticiones, intrusiones, consigna y duración | F A S y animales tienen referencias latinoamericanas con Bolivia. Cada letra o categoría utiliza su condición propia. [15] |
| LEN-S03 Comprensión lingüística | Versión, ítems administrados y errores; control auditivo | Token Test, referencia NEURONORMA española. No se verificó un baremo boliviano equivalente en esta revisión. [16] |
| LEN-S04 Repetición verbal | Respuestas literales y rúbrica de la subprueba | WAB R incluye repetición. Falta identificar una edición española y una norma aplicable a esta población. [17] |
| LEN-S05 Producción verbal y discurso | Muestra obtenida con tarea definida, contenido y fluidez según rúbrica | WAB R distingue habla espontánea de repetición. Una nota narrativa de Enfermería no equivale a administrar esa subprueba. [17] |

## Función visuoespacial

| Componente | Medición que debe conservarse | Instrumento o referencia y límite |
| --- | --- | --- |
| VIS-S01 Relaciones espaciales | Subprueba, aciertos, forma y control visual | VOSP o JLO tienen referencias españolas NEURONORMA; son pruebas diferentes. No se asignan esas normas como bolivianas. [18] |
| VIS-S02 Visuoconstrucción | Producción gráfica original y corrección por elementos; fase identificada | Rey Osterrieth dispone de estudio latinoamericano con Bolivia. Utilizar la fase de copia; el recuerdo no la sustituye. [19] |

## Selección práctica y condiciones de uso

La selección se realiza según la pregunta clínica, alfabetización, idioma, visión, audición y capacidad motora. No es necesario administrar todos los instrumentos candidatos a cada residente. Tampoco existe evidencia revisada de que un único cribado resuelva nuestros 19 componentes.

| Opción | Utilidad para este proyecto | Condición antes de incorporarla |
| --- | --- | --- |
| RUDAS | Candidato inicial cuando preocupa el efecto cultural o de escolaridad; resultado global de cribado | La validación peruana incluye personas analfabetas, pero no acredita una norma boliviana. Confirmar versión y autorización digital. [20] |
| MoCA y MoCA Basic | Cribado o evaluación inicial, según versión y población | Los términos actuales distinguen uso del total e interpretación de dominios; la integración en otro producto requiere autorización separada. [21] |
| ACE III | Evaluación breve ampliada con traducción española disponible | Sus cinco apartados no equivalen exactamente a los cinco dominios de RememberMind. Confirmar normas y condiciones de integración. [22] |
| NEUROPSI | Candidato ampliado en español y con estratificación educativa | Distinguir breve de Atención y Memoria y sus ediciones. Material comercial no reproducible; no copiar manuales o tablas desde repositorios informales. [23] |
| Mini Cog | Cribado breve en determinados contextos | Su desarrollador señala límites para DCL y para personas no familiarizadas con la tarea del reloj. No completa el perfil de 19 componentes. [24] |

La disponibilidad pública de un formulario o la compra de un manual no constituyen una licencia de digitalización. Esta entrega programa fórmulas científicas y controles de datos; no incorpora reactivos, láminas, listas de palabras o manuales protegidos. Los resultados de aplicaciones legítimas pueden documentarse conforme a las condiciones de su titular. [6, 12, 21, 23]

## Integración con Petersen y revisión del resultado

La propuesta NIA AA de DCL requiere integrar preocupación por cambio, evidencia objetiva de rendimiento inferior, conservación relativa de independencia y ausencia de demencia. Un puntaje aislado no establece la etiología Alzheimer. Las referencias habituales de desviación respecto de la norma son orientaciones; no justifican un único corte automático para toda prueba, educación o cultura. [25]

La integración de Petersen debe conservar los requisitos C01 a C05 con soportes identificables. La dependencia física, la falta de oportunidad para realizar una actividad o un total de actividades básicas no prueban por sí solos pérdida cognitiva de independencia instrumental. Cuando faltan soportes, la salida debe explicar la abstención.

Cuando todos los requisitos y los estados de dominio estén fundamentados, el patrón puede distinguir memoria afectada aislada, memoria y otros dominios afectados, un dominio no amnésico o varios dominios no amnésicos. Son patrones de apoyo a la valoración; no se deducen de la cantidad de observaciones o caídas. La revisión del resultado aparece después del cálculo y la conservación, con profesional, fecha y justificación.

## Aplicación realizada y pendientes medibles

- Implementado el método `HVLT-R-BO-2015-F5`, con normalización independiente de aprendizaje total y recuerdo libre.
- Implementados rechazos explícitos de forma, idioma, población, escolaridad, lectoescritura, demora y datos incompatibles. Se mantiene NULL como ausencia y cero como medición real.
- Registrado el método en el motor y presentado su disponibilidad en la preparación de la valoración, sin atribuirlo a aplicaciones nominales existentes.
- Corregido el inventario para contar aplicaciones COMPLETA anteriores al corte, excluyendo anuladas, futuras y de otros residentes.
- Las 19 tareas conservan su identidad de origen. No se han generado aciertos, tiempos, dibujos, protocolos o diagnósticos que no estaban registrados.
- La siguiente incorporación depende de las mediciones originales y del procedimiento elegido para cada componente, además de las condiciones editoriales cuando correspondan. No exige aprobar previamente el desarrollo de software.

El grano relacional se mantiene: una aplicación corresponde a un instrumento y sus respuestas; una revisión de regla conserva una antecesora y una decisión; una evaluación mantiene revisiones profesionales separadas. Esta investigación no modifica la estructura ni el total de 106 tablas.

## Referencias verificadas

1. Atri y colaboradores. DETeCD ADRD Validated clinical assessment instruments. Alzheimer’s and Dementia, 2025. [Artículo y guía](https://alz-journals.onlinelibrary.wiley.com/doi/10.1002/alz.14335).
2. National Institute on Aging. [Assessing Cognitive Impairment in Older Patients](https://www.nia.nih.gov/health/health-care-professionals-information/assessing-cognitive-impairment-older-patients).
3. NICE. NG97, [recomendaciones de evaluación y diagnóstico](https://www.nice.org.uk/guidance/ng97/chapter/recommendations).
4. Arango Lasprilla y colaboradores. HVLT R Normative data for the Latin American Spanish speaking adult population. NeuroRehabilitation, 2015;37:699–718. [Publicación original, DOI 10.3233/NRE-151286](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151286).
5. Peña Casanova y colaboradores. NEURONORMA FCSRT. [PubMed 19661107](https://pubmed.ncbi.nlm.nih.gov/19661107/).
6. PAR. [HVLT R](https://www.parinc.com/products/HVLT-R) y [condiciones de licencias](https://www.parinc.com/about/connect-with-us/licensing-team).
7. PAR. [Ruff 2 and 7 Selective Attention Test](https://www.parinc.com/products/RUFF2AND7).
8. Rivera y colaboradores. Brief Test of Attention, 2015. [PubMed 26639928](https://pubmed.ncbi.nlm.nih.gov/26639928/).
9. Rivera y colaboradores. Stroop Color Word Interference Test, 2015. [PubMed 26639926](https://pubmed.ncbi.nlm.nih.gov/26639926/).
10. Arango Lasprilla y colaboradores. Trail Making Test, 2015. [Publicación original, DOI 10.3233/NRE-151284](https://journals.sagepub.com/doi/pdf/10.3233/NRE-151284).
11. Arango Lasprilla y colaboradores. Modified Wisconsin Card Sorting Test, 2015. [PubMed 26639931](https://pubmed.ncbi.nlm.nih.gov/26639931/).
12. Pearson. [WAIS IV español](https://www.pearsonclinical.es/portals/0/docproductos/13534059942.pdf) y [permisos de uso](https://www.pearsonclinical.es/politica-de-privacidad/solicitud-permisos-uso-tests.html).
13. Peña Casanova y colaboradores. NEURONORMA Tower of London Drexel. [Publicación original](https://academic.oup.com/acn/article-abstract/24/4/413/3645).
14. Olabarrieta Landa y colaboradores. Boston Naming Test, 2015. [PubMed 26639925](https://pubmed.ncbi.nlm.nih.gov/26639925/).
15. Olabarrieta Landa y colaboradores. Verbal Fluency Tests, 2015. [PubMed 26639930](https://pubmed.ncbi.nlm.nih.gov/26639930/).
16. Peña Casanova y colaboradores. NEURONORMA Boston Naming Test and Token Test. [PubMed 19648582](https://pubmed.ncbi.nlm.nih.gov/19648582/).
17. Pearson. [Western Aphasia Battery Revised](https://www.pearsonassessments.com/store/en/usd/p/100000194.html).
18. Peña Casanova y colaboradores. NEURONORMA VOSP and Judgment of Line Orientation. [PubMed 19648584](https://pubmed.ncbi.nlm.nih.gov/19648584/).
19. Rivera y colaboradores. Rey Osterrieth Complex Figure, 2015. [PubMed 26639929](https://pubmed.ncbi.nlm.nih.gov/26639929/).
20. Dementia Australia. [RUDAS y guía de administración](https://www.dementia.org.au/professionals/assessment-and-diagnosis-dementia/rowland-universal-dementia-assessment-scale-rudas). Custodio y colaboradores. RUDAS PE, 2020. [Estudio primario](https://pmc.ncbi.nlm.nih.gov/articles/PMC7232574/).
21. MoCA Cognition. [Términos de uso actuales](https://mocacognition.com/terms-of-use/), apartados 5 y 10.2, y [versiones oficiales](https://www.mocacognition.com/paper). La página antigua de permisos contiene una formulación diferente sobre certificación; se utiliza el texto vigente de términos, sin inferir permiso de integración.
22. University of Sydney. [ACE III y formación](https://www.sydney.edu.au/brain-mind/our-clinics/dementia-test.html).
23. Titular NEUROPSI. [Instrumentos y condiciones](https://neuropsi.com.mx/). Ostrosky Solís y colaboradores. [NEUROPSI Attention and Memory, PubMed 17848126](https://pubmed.ncbi.nlm.nih.gov/17848126/).
24. Mini Cog. [Desarrollo y límites](https://mini-cog.com/instrument-development/) y [permisos](https://mini-cog.com/contact/).
25. Albert y colaboradores. NIA AA Recommendations for MCI, 2011. [Artículo original conservado por University of Pennsylvania](https://www.med.upenn.edu/digitalneuropathologylab/assets/user-content/documents/educational-resources/the-diagnosis-of-mild-cognitive-impairment-due-to-alzheimer%27s.pdf).

Las referencias se revisaron el 18 de octubre de 2026. Este informe documenta evidencia y decisiones de implementación; no atribuye autoría ni aprobación a los profesionales del establecimiento.
