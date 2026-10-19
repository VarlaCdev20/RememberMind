# O.R.I.O.N. — Cinco referencias geriátricas adicionales

Consulta e implementación: 19/10/2026, America/La_Paz. Población de interés:
personas mayores de 60 años. Estado: **implementación matemática de referencia**,
con adopción institucional sin configurar y revisión profesional pendiente.

Continúa las [normalizaciones españolas](NORMALIZACIONES_REFERENCIA_2026_10_19.md),
el [manual investigado](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md) y el
[objetivo de fase 3](FASE_3_COMPONENTES_2026_10_19.md). El catálogo versión 7
contiene algoritmos de comparación para **16 de 19 componentes**, incluyendo
referencias extranjeras y una tesis identificada como tal. El contador significa
disponibilidad técnica bajo condiciones; no significa que los dieciséis sean
aplicables a cualquier residente, edad, instrumento o escolaridad.

## 1. Fuentes primarias y calidad

| Fuente | Original consultado | Alcance y nivel de evidencia |
| --- | --- | --- |
| Torres et al., publicado en línea en 2019 | [Artículo completo depositado en PMC](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/), DOI [10.1080/23279095.2019.1627357](https://doi.org/10.1080/23279095.2019.1627357) | Investigación original en revista. Datos colombianos API; métodos y tablas 10–11. No es una norma boliviana. |
| Unterrainer et al., 2019 | [Original Cambridge, acceso abierto](https://doi.org/10.1017/S1355617718001248); [depósito PMC](https://pmc.ncbi.nlm.nih.gov/articles/PMC6669988/) | Investigación original de TOL-F. Se consultaron texto, tablas HTML y PDF completo de diez páginas, con cotejo visual en pp. 527–528. |
| Duarte Pedroza, 2017 | [Repositorio de la Universidad Nacional de Colombia](https://repositorio.unal.edu.co/handle/unal/59359); [PDF original](https://repositorio.unal.edu.co/bitstreams/a4cf8c6a-3b02-4202-b8c4-080ce90e7d57/download) | Tesis de Magíster en Psicología con énfasis en Neuropsicología Clínica y Cognoscitiva, dirigida por Patricia Montañés. PDF de 172 páginas; no se presenta como artículo revisado por pares ni como edición del manual de 2020. |

El libro posterior *Neuronorma.Colombia*, primera edición 2020, se identificó
en la [vista previa editorial](https://api.pageplace.de/preview/DT0400.9789587942019_A42711398/preview-9789587942019_A42711398.pdf)
y en [JSTOR, editorial UNAL](https://www.jstor.org/publisher/cefcunacol).
ISBN impreso 9789587941999, digital 9789587942019. Su existencia no acredita que
las tablas de la tesis permanezcan iguales en el libro: **no se sustituyeron
fuentes ni se afirmó disponer del manual completo de 2020**.

## 2. Reconocimiento — MEM-S04

Método `CERAD-RECONOCIMIENTO-CO-API-2019`. Sirve para registrar discriminación
de palabras previamente presentadas frente a distractores. Se usa la tarea
CERAD-Col del artículo: aprendizaje de diez palabras en tres ensayos, diferido
previo a siete minutos y reconocimiento con diez objetivos y diez distractores.
El programa recibe los recuentos originales corregidos por el profesional;
no genera listas ni administra la prueba.

Se utiliza porque el original describe tanto la corrección como la referencia
por edad y escolaridad. La operación publicada es:

```text
correctos = aciertos en objetivos + rechazos correctos de distractores
bruto = máximo(0, correctos − 10)
      = máximo(0, aciertos en objetivos − falsas alarmas)
distancia estandarizada = (bruto − media del grupo) / DE del grupo
```

Ejemplo sintético: nueve objetivos acertados y dos falsas alarmas producen
bruto siete. Se conservan ambos recuentos. El truncamiento en cero pertenece
a CERAD-Col; **no se cambia el índice firmado de reconocimiento HVLT-R**.
Una diferencia respecto de la media no identifica por sí misma alteración
objetiva de memoria, causa, enfermedad de Alzheimer ni DCL.

## 3. Razonamiento no verbal — EJE-S05

Método `RAVEN-A-CO-API-2019`. Sirve para comparar la identificación de patrones
visuales en **Raven parte A, doce elementos**, tal como se identifica en el
original colombiano. Recibe aciertos y número de elementos puntuados.
Se utiliza porque la tarea se describe explícitamente como razonamiento no
verbal y publica las parejas media/DE para los grupos mayores.

Se calcula `(aciertos − media) / DE`. No se suman Semejanzas y Matrices WAIS;
no se emplean normas de Raven completo ni se concluye razonamiento general.
Aunque el artículo organiza Raven dentro de su apartado visuoespacial, aquí
se vincula únicamente con el componente primario de razonamiento no verbal;
**la misma aplicación no aporta una segunda medición a VIS-S01**.

## 4. Cancelación de letras — ATE-S01

Método `CANCELACION-A-CO-API-2019`. El original denomina esta tarea de atención
sostenida; requiere cancelación de objetivos A en una hoja y registro del tiempo.
Se reciben aciertos de los dieciséis objetivos y segundos efectivos. La
comparación publicada usa **aciertos**, sin dividirlos por tiempo ni convertir
segundos en un índice de vigilancia no definido por el estudio.

Se incorpora por su definición y sus datos publicados, conservando su alcance
de tarea. No sustituye los veinte ensayos Ruff ni demuestra mantenimiento
atencional durante periodos prolongados. No se atribuye automáticamente una
segunda evidencia de atención selectiva a ATE-S02.

## 5. Datos de comparación colombianos

Las siguientes parejas son `media / DE`, no coeficientes ni errores residuales.
Se seleccionan únicamente bajo identidad de versión, español nativo,
lectoescritura, contexto de referencia antioqueño documentado, condiciones
sensoriales compatibles y administración estandarizada.

| Medida | Tabla 10: escolaridad 1–4 | Tabla 10: 5–25 | Tabla 11: 1–4 | Tabla 11: 5–25 |
| --- | --- | --- | --- | --- |
| CERAD reconocimiento, 0–10 | 9.76 / 0.54 | 9.45 / 1.09 | 9.35 / 1.17 | 9.37 / 1.30 |
| Raven A, 0–12 | 8.43 / 1.94 | 8.16 / 2.00 | 8.06 / 2.11 | 7.71 / 2.26 |
| Cancelación A, 0–16 | 12.00 / 2.83 | 13.67 / 2.31 | **Sin datos** | 13.83 / 1.94 |

Restricciones decisivas:

- La muestra general comprende 2673 participantes de 18–86 años, escolaridad
  1–25, con una edad media de 32.94. No se llama «muestra geriátrica de 2673».
- Las tablas mayores tienen grupos educativos de 22/32 personas y 17/24,
  respectivamente. `n_grupo_educativo` no certifica el número válido de cada tarea.
- El original titula los intervalos 61–65 y «65 y más». La edad 65 queda
  **NO_NORMALIZABLE** por solapamiento sin resolver. Se permiten 61–64 y 66–86.
- El rótulo escolar 0–4 no permite incluir analfabetismo: el método excluyó
  personas sin escolaridad y sin lectoescritura. El límite inferior es uno.
- La celda de cancelación en el grupo mayor con baja escolaridad aparece
  `. (.)`; no se reemplaza por cero, por el grupo anterior ni por el de más estudios.
- Las distribuciones no se asumen normales. La distancia z es descriptiva;
  no se calcula un percentil gaussiano ni se añaden puntos de corte clínicos.

## 6. Planificación — EJE-S04

Método `TOL-F-DE-GHS-2019-24`. Sirve para comparar resolución de problemas
de planificación en el mínimo número de movimientos. El original evalúa
**TOL-F informatizado con pantalla táctil**, 24 problemas, ocho de cada dificultad
de cuatro, cinco y seis movimientos. Incluye práctica, límites de un minuto
por problema y veinte minutos para la tarea. La puntuación original debe provenir
de esa administración; el software no reconstruye problemas ni asigna ceros
a ensayos que no constan.

Se utiliza por disponer de tablas publicadas y verificables por edad, sexo y
nivel educativo. Se consultan las tablas 5 y 6; no se calcula una regresión.
La muestra general alemana fue de 7703 personas. El perfil incorpora solo las
cuatro bandas geriátricas 60–64, 65–69, 70–74 y 75–79. El resumen «hasta 80»
no extiende la tabla a los 80 años cumplidos.

`sexo_norma`: 0 varón, 1 mujer. `nivel_educativo_ghs` conserva la clasificación
original: nivel 2, nueve años sin formación profesional; nivel 3, diez a doce
años y formación profesional; nivel 4, cualificación de acceso universitario;
nivel 5, titulación universitaria. Se agrupan 2–3 y 4–5. El nivel 1 fue excluido
por los autores de estas normas. **No se convierten años escolares bolivianos
automáticamente en estos códigos alemanes**.

Ejemplo sintético verificado: mujer de 70–74 años, grupo educativo 4–5,
doce problemas resueltos en el mínimo de movimientos → percentil publicado 52,
celda n=83. La celda femenina 75–79, grupo alto, tiene n=50. Se advierten las
celdas con menos de cien participantes. Los enteros percentilares están
redondeados; 0 y 100 no se invierten a z infinita. La tabla no publica una fila
para cero aciertos: se conserva ese bruto sin asignarle un percentil.

Identidad, contexto alemán, modalidad táctil, comprensión de instrucciones,
condiciones sensoriales/motoras y administración deben estar documentados.
No se aplican estas tablas a TOL-DX, versiones de 12/16/32 problemas, otra
modalidad o mayores de 79 años. La comparación de planificación no es DCL.

## 7. Comprensión verbal — LEN-S03

Método `TOKEN-CO-UNAL-2017-TABLAS`. Sirve para situar la ejecución de órdenes
verbales de complejidad creciente en el Token abreviado de De Renzi y Faglioni.
El perfil identifica el procedimiento colombiano de 36 instrucciones; recibe
el total ya corregido, entre 0 y 36 en medios puntos, sin reconstruir rúbricas.
Se incorpora porque la fuente original pública ofrece tablas etarias y escolares,
con mejor cobertura de edades avanzadas que las alternativas jóvenes descartadas.

Procedimiento matemático:

1. Consultar NSS(A), escalar por edad, en las tablas 23–30 para 60–90 años.
2. Consultar la celda de NSS(A) y escolaridad 0–18 en la tabla 32.
3. Conservar por separado NSS(A), NSS(A&E) y rango percentilar **solo por edad**.

La tabla escolar equivale a sumar 2 para escolaridad 0–3, 1 para 4–7,
0 para 8–14 y −1 para 15–18. Las 323 celdas de la tabla 32 se cotejaron contra
el PDF; no se dedujo una regla de redondeo desde una regresión. El texto
escribe `NSS(A&E)=NSS(A)−0.251×(Educ−11)`, pero no define su conversión
fraccionaria a entero: NSS(A)=10 y Educ=12 da 9.749 por fórmula y 10 en tabla.
**Este perfil consulta la tabla**, conserva esa distinción y no impone `floor`.
Tampoco recorta un escalar publicado 20 a 18.

Casos sin comparación:

- Tabla 24: bruto 27.5, edad 63–65, no tiene correspondencia inequívoca.
- Tabla 25: primera celda `≤20.5–22`, edad 66–68; se bloquean los brutos
  hasta 22 sin decidir qué quiso decir el autor. El resto de la tabla sí se consulta.
- Edad fuera de 60–90, escolaridad fuera de 0–18, cuarto de punto,
  otra versión, falta de lectoescritura, contexto o condiciones de administración.

La tesis incluyó 438 personas colombianas de 50–90 años y excluyó cuidados
residenciales especializados. Esa limitación de representatividad se conserva;
no se convierte en requisito de que un paciente esté sano para poder calcular
una comparación. La referencia necesita pertinencia profesional para la residencia.
No produce z ni percentil final tras corrección escolar. No se adoptan cortes
de afasia como reglas de deterioro cognitivo.

## 8. Contrato de salida e integración

Los cinco selectores son `NORMALIZACION_REFERENCIA`. Se añaden al registro
existente; el extractor conserva PK de aplicación, respuesta y pregunta.
`PerfilInstrumentalCognitivo` mantiene cada aplicación y componente por separado.
No se crean catálogos BDD, preguntas operativas, tablas o pantallas.

La salida conserva método, fuente, localizador, bruto, contexto original,
estado, motivos, dirección y límites. Específicamente:

- Colombia API: `media_referencia`, `de_referencia`, `z` descriptiva y estrato;
  `de_residual=null`, `percentil=null`.
- TOL-F: `percentil_publicado`, estrato y n; `z=null`.
- Token: `escalar_edad`, `escalar_edad_escolaridad`, `rango_percentil_edad`,
  números de tabla y `nivel_evidencia=TESIS_MAESTRIA_ORIGINAL`; sin z o percentil final.

La explicación distingue media grupal, predicción por regresión, percentil publicado
y corrección escalar tabulada. Todas conservan `clasificacion_clinica=null`,
`diagnostico=null`, `revision_profesional=PENDIENTE`,
`adopcion_institucional=NO_CONFIGURADA`. No se añaden bindings institucionales ni
se habilitan Petersen/G09 o resultados de laboratorio sobre residentes reales.

## 9. Acceso, huellas y reproducibilidad

| Archivo público consultado | Bytes | SHA-256 |
| --- | ---: | --- |
| TOL-F, HTML PMC | 267036 | `6170dc4b568cf6e1935d7112305acf7f5912a5fb665c0b7797f3f5d54fd65675` |
| TOL-F, PDF Cambridge | 399978 | `2d4abac10cc07da9b7f3f4a98dac9b9e06da06e008f30bc0fc7732be525227b4` |
| API colombiano, HTML PMC | 384863 | `f9e86e8c991244b817cb1f113d4ba1ef41c72bbedc207f9d858dc23c68d3df56` |
| Tesis UNAL 2017, PDF | 2106756 | `a04ecccec349e66fdc777f406621f860c543897360491756a5ba5ca82655601e` |

Se comprobaron **384 percentiles TOL-F y dieciséis n** contra las tablas HTML
originales mediante un extractor independiente de PHP, además del cotejo visual
en PDF. Para Token se inspeccionaron visualmente pp. 103–106 y 108 y se
cotejaron las 323 celdas escolares. No se reproducen reactivos, manuales completos
o producciones clínicas en el repositorio.

Incidencias: el lector web de PMC quedó sin respuesta; la descarga HTTP pública
ordinaria sí recuperó el artículo. El suplemento API `Supp_1.docx` devolvió
1816 bytes de HTML, SHA-256
`0526e18307c60c7a76715d8066c5fff0c4a690dede8b101e016c30c9bdc645a6`;
**no es DOCX válido** ni se utilizó para resolver la edad 65. La ruta PDF API
también devolvió HTML, por lo que no se declara un PDF completo obtenido.
El original HTML sí contiene las tablas utilizadas. Los artefactos temporales
permanecen fuera del repositorio y no contienen datos de residentes.

## 10. Componentes pendientes y búsquedas de descarte

Quedan sin conversión normativa completa **ATE-S02, LEN-S04 y LEN-S05**.
La WAB castellana ya tiene sus dos cálculos brutos; una suma o un porcentaje
del máximo no constituye una normalización geriátrica.

| Búsqueda / original | Decisión fundada |
| --- | --- |
| [Ruff ampliado, Caban-Holt et al., 2012](https://pmc.ncbi.nlm.nih.gov/articles/PMC3734957/) | La norma ampliada estudia varones de 60–90; no acredita aplicación a mujeres. Su apéndice D publicado no se obtuvo como documento válido; no se inventa la conversión de brutos a T. |
| [d2, investigación universitaria 2024](https://doi.org/10.32541/recie.2023.v8i1.pp21-39) | Muestra 18–39; se descarta para mayores de 60 aunque el título diga atención sostenida/selectiva. |
| [BDAE español, Pineda et al., 2000](https://pubmed.ncbi.nlm.nih.gov/11105470/); [original de coautor](https://aalfredoardila.wordpress.com/wp-content/uploads/2013/07/2000-pineda-et-al-the-boston-diagnostic-aphasia-examination_spanish-version-the-influence-of-demographic-variables.pdf) | Muestra laboral de 19–60, solo doce participantes 51–60. No se extrapola a 61–90 ni se usa como norma WAB. |
| [Stricks et al., 1998](https://pubmed.ncbi.nlm.nih.gov/9656604/) | Estudio relevante en mayores anglófonos/hispanohablantes; abstract insuficiente para programar cada conversión BDAE. Se conserva como búsqueda de texto/tablas completas. |
| [SP-CAT, original 2024](https://doi.org/10.1080/02687038.2024.2319362) | Adaptación piloto multicultural; no se convirtió su media general en norma geriátrica ni se renombró como WAB. |
| [Conversación y tarea narrativa, 2022](https://pmc.ncbi.nlm.nih.gov/articles/PMC9132141/) | Inglés, muestras pequeñas, tareas propias: no permite normalizar WAB castellana. |

Otra fuente prioritaria localizada es [SCAND, Lojo-Seoane et al., 2023](https://doi.org/10.1080/13854046.2023.2173304),
con [ficha universitaria y enlace al depósito UCM](https://investigacion.usc.gal/documentos/64046e84d5b0fa1e7b2771b0)
([handle](https://hdl.handle.net/20.500.14352/102342)). Se investigará la tarea
exacta de cancelación, su medida, tablas y ajustes; no se cuenta como implementada.
La ficha UCM se recuperó, pero su enlace de descarga devolvió la página
**Login**, HTML de 315163 bytes, SHA-256
`bfd9b151fa76775dca2fd8e4bf287bf8ab76e1e2b471f279a5a29140fc39b5b9`.
No se confundió con el PDF ni se eludió el control de acceso.

La pertinencia institucional sigue requiriendo evaluar idioma habitual, escolaridad,
edición disponible, representatividad de residentes institucionalizados y rangos
de edad. Investigar una referencia no autoriza a fabricar datos ni a declarar
«normal» un campo vacío. La fase 3 continúa abierta.

## 11. Verificación de implementación

Pruebas específicas iniciales de los cinco métodos: **88 pruebas / 5725
aserciones**, SQLite, 3.03 s. Incluyen once ejemplos de distancia grupal,
dieciséis estratos TOL-F, diez ejemplos Token y sus 584 combinaciones de banda
etaria/medio punto: 538 comparables y 46 abstenciones. Cubren cero, ausencia,
tipado, fuentes incompatibles, contexto desconocido, límites etarios/escolares,
celdas vacías, sexo no documentado y conservación de resultados sin diagnóstico.

La prueba SQL recorre cinco aplicaciones sintéticas mediante la acción autorizada,
consulta y perfil; comprueba edad a la fecha de aplicación, fuentes con PK,
ausencia de doble contribución a ATE-S02 y cero evaluaciones expertas creadas.
Las pruebas previas se mantienen y se actualiza únicamente el inventario real
del catálogo. Resultados ampliados por motor en el seguimiento de fase 3.

Suite relacionada final: **298 pruebas / 8248 aserciones**, SQLite, 69.56 s;
**298 pruebas / 8247 aserciones**, PostgreSQL desechable, 84.071 s. Se conserva
la diferencia de una aserción por motor. El clúster temporal se detuvo y retiró;
no se usó la base operativa. JUnit y log de PostgreSQL:
`remembermind-pg-ec07c75726b54d2e807ae111f5cb314d`, en el directorio temporal.

Sin cambios frontend: build/QA visual no aplican. La verificación técnica no
equivale a validación clínica independiente ni a revisión profesional realizada.
