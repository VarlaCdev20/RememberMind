# O.R.I.O.N. — Normalizaciones de referencia implementadas

Fecha: 19/10/2026. Solicitud: «realiza las normalizaciones». Estado: cinco
ecuaciones nuevas implementadas, con seis perfiles de selección y pruebas.
Las referencias se calcularon bajo condiciones explícitas; no se habilitaron
inferencias, clasificación clínica ni perfiles sobre residentes reales.

**Corte vigente:** [cierre técnico de los 19 componentes](CIERRE_TECNICO_FASE_3_2026_10_19.md),
catálogo versión 8. SCAND y TB-A completan las tres comparaciones que faltaban;
la adopción institucional y validación clínica permanecen pendientes.

**Actualización intermedia:** el [capítulo de referencias geriátricas adicionales](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md)
incorpora cinco perfiles más y eleva la cobertura a 16 de 19 componentes bajo
condiciones. Este documento conserva el corte de las cinco ecuaciones españolas;
sus ocho pendientes históricos se redujeron entonces a ATE-S02 y LEN-S04/S05.

## Alcance y fuentes de verdad

Se continúa el [manual investigado para mayores de 60](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md)
y la [fase 3](FASE_3_COMPONENTES_2026_10_19.md). El registro actual, extractor,
perfil y catálogo siguen siendo los puntos de integración. No se crean tablas,
preguntas en la BDD, permisos, pantallas o un motor alternativo.

Las fuentes son los originales de
[Iñesta et al., 2021](https://doi.org/10.3390/ijerph18199958),
[Calderón-Rubio et al., 2021](https://doi.org/10.3390/ijerph182412977),
[Luque-Tirado et al., 2025](https://doi.org/10.1177/13872877251376906) y la
[calculadora pública de sus autores SABIEX](https://drive.google.com/file/d/1p-RDT6F85EsXPxALV-l6R8Sq-E4qGEr0/view).
Sus localizadores, acceso y huellas están en el manual. Las tablas de artículos
y fórmulas XML ya cotejadas se reutilizan; no se presentan nuevas descargas de
manuales completos que no se hayan obtenido.

## Ecuaciones y unidades

En SABIEX: `E = años escolares − 3`, `A = edad − 55`, `S = 0 varón / 1 mujer`.
En cada regresión constante, `z = (bruto − predicho) / DE residual`.

| Selección | Componente / bruto | Predicho | DE residual | Localizador |
| --- | --- | --- | --- | --- |
| `DSB-ES-SABIEX-2021-LONGITUD` | EJE-S03; longitud inversa 0–8 | `3.695 + 0.003 E²` | `0.854` | Tabla 4; C9/E9 de autores |
| `LN-ES-SABIEX-2021-LONGITUD` | EJE-S03; longitud letras/números 0–8 | `5.014 − 0.048 A` | `1.075` | Tabla 4; C10/E10 |
| `SPAN-ES-SABIEX-2021-LONGITUDES` | Las dos anteriores en una aplicación | Cada modelo por separado | Cada DE propia | Mismas fuentes; no es una sexta ecuación |
| `JLO-ES-SABIEX-2021-30` | VIS-S01; aciertos 0–30, ambos segmentos correctos | `22.252 − 4.263 S + 0.014 E²` | `4.446` | Tabla 2; C23/E23 |
| `FCSRT-ES-SABIEX-2021-APRENDIZAJE` | MEM-S03; total libre + adicional con claves, 0–48 | `40.31 + 0.288 E + 1.886 S` | `3.865` | Tabla 2 FCSRT-Imm; C15/E15 |

La medida FCSRT corresponde al **total con claves**, no a una norma aislada de
los aciertos adicionales ni a una diferencia causal entre recuperación y
almacenamiento. Se reutilizan las sumas y validaciones del calculador de palabras,
conservando intacta la captura exterior y su identidad normativa.

Para `PFCSRT-ES-2025-IMAGENES-A-LIBRE` se suman los libres de los tres ensayos
0–16, total 0–48, bajo MEM-S01. Se usan `a = edad−67.46`, `e = escolaridad−10.30`:

```text
predicho = 27.38 − 0.05 a + 0.17 e + 1.23 S − 0.25 a S
varianza residual = 106.86 − 2.55 predicho
DE residual = raíz(varianza residual)
z = (libre total − predicho) / DE residual
```

No se usa esa regresión para recuerdo con claves, reconocimiento o diferido.
No se calcula percentil exacto ni se importa el corte del total de otra medida.

## Población y validación de entrada

SABIEX conserva límites de referencia **55–87 años y 3–22 años escolares**.
Requiere español nativo, nacimiento/crianza en España, actividad cognitiva
universitaria y contexto normativo español documentados, administración
estandarizada y condiciones sensoriales compatibles. Estas condiciones restringen
el cálculo a su contexto de referencia. No se exige que quien se evalúa esté
cognitivamente sano, carezca de quejas o cumpla un tamizaje «normal» para poder
comparar su medición.

pFCSRT conserva **50–88 años y escolaridad 0–30**, contexto normativo español
documentado, forma A de imágenes con recuerdo inmediato, codificación y
administración acreditadas y condiciones sensoriales compatibles. No se añade
un requisito de alfabetización a esta referencia. No se exige nacimiento en
España a este estudio por una condición no identificada en su texto.

El objetivo institucional continúa siendo mayores de 60; los intervalos anteriores
describen el alcance de cada fuente, no nuevas edades de admisión. Una persona
de 90 años no se redondea a 87 u 88. Estar dentro del rango no convierte
automáticamente a una población residencial boliviana en la muestra española.
La publicación de un artículo tampoco acredita adopción institucional.

La identidad distingue palabras/imágenes, forma, idioma, protocolo y subprueba.
Se rechazan cadenas, booleanos, fracciones o no finitos donde se exige entero.
El sexo requerido por el modelo debe estar documentado como 0/1; no se infiere
desde nombres. Cero escolar es dato válido solamente en la referencia que lo
incluye. Falta o contexto incompatible deja `esperado`, `DE`, varianza y `z` nulos.
No se completan entradas desde defaults.

## Integración y contrato de salida

`NormalizadorReferenciasEspana` se registra en `RegistroMetodosInstrumentales`.
`ExtractorCapturaInstrumental` conserva el vínculo explícito entre instrumento,
versión, pregunta, respuesta y aplicación. El perfil recibe resultados por medida
sin inventar alteraciones A/P o DCL.

El perfil SPAN conjunto permite seleccionar ambos resultados para una misma
aplicación WAIS-III. Evita configurar dos perfiles incompatibles para la misma
identidad instrumental. Cada tarea conserva su resultado: una longitud ausente
no invalida la otra; un contexto común incompatible impide ambas comparaciones.
Las longitudes no se suman ni se promedian.

Cada medida incluye tipo `NORMALIZACION_REFERENCIA`, estado `NORMALIZADO` o
`NO_NORMALIZABLE`, bruto, predicho, DE, varianza, z, dirección, motivos y
advertencias. La salida conserva la referencia, localizador, captura, ausencia de
diagnóstico, revisión pendiente y `adopcion_institucional=NO_CONFIGURADA`.
`NORMALIZADO` significa operación estadística realizada, no estado cognitivo normal.

El catálogo `CAPTURA-COGNITIVA-6-20261019` separa:

- `metodos_disponibles`: los siete perfiles de la base previa, ocho componentes.
- `calculos_descriptivos_disponibles`: las tres familias brutas existentes.
- `normalizadores_referencia_disponibles`: los seis selectores nuevos, cinco ecuaciones.

En MEM-S03, EJE-S03 y VIS-S01 cambia la disponibilidad técnica a
`NORMALIZADOR_REFERENCIA_CON_CONTEXTO_EXPLICITO`; MEM-S01 ya tenía un método previo.
Las advertencias no desaparecen por obtener un z. La configuración institucional
de captura/publicación permanece sin cambios; no se escriben vínculos de preguntas
ni mediciones de residentes para hacer que los modelos calculen.

## Cobertura y pendientes concretos

Hay **once componentes con al menos un algoritmo normativo programado**:
ocho de la base previa y tres adicionales de referencia española. Los cuatro
componentes que tienen nuevas referencias incluyen MEM-S01, ya cubierto antes;
no se cuenta por segunda vez. La aplicabilidad institucional de las referencias
nuevas sigue pendiente y no se presenta como validada por el código.

| Componente sin normalizador completo | Motivo verificable |
| --- | --- |
| MEM-S04 | HVLT-R reconocimiento español geriátrico: falta conversión normativa compatible completa; la extensión FCSRT no es la misma prueba |
| ATE-S01/S02 | Ruff: manual y tablas de conversión pendientes; la ampliación encontrada cubre varones y no se extrapola a mujeres |
| EJE-S04 | TOL-DX: tablas completas de la versión y rangos compatibles sin recuperar/verificar |
| EJE-S05 | Razonamiento: falta conversión legítima de subprueba/edición; no usar normas de dígitos |
| LEN-S03 | Token: rúbrica contradictoria en la fuente chilena; otra publicación normativa no equivale a sus tablas completas |
| LEN-S04/S05 | WAB castellana: aritmética disponible; no se verificó baremo geriátrico pertinente. Convertir a porcentaje del máximo no sería normalización poblacional |

Son **ocho componentes** pendientes de algoritmo normativo, no once. Además
permanece sin implementar FCSRT-Del SABIEX por el conflicto de la tabla con C16;
no se utiliza esa variante para completar artificialmente otra casilla.

Investigación adicional de Ruff: se recuperó el artículo completo y localizó su
apéndice D. La descarga pública con extensión `.doc` devolvió 1816 bytes HTML,
no un documento de tablas. Se comprobó la cabecera y no se interpretó como
manual. Las búsquedas complementarias no proporcionaron aquí una conversión
íntegra verificada. No se ejecutaron documentos ni eludieron restricciones.

Se localizaron dos alternativas para continuar las brechas sin renombrar pruebas:
[TOL-F/Gutenberg, estudio original de 2019](https://pmc.ncbi.nlm.nih.gov/articles/PMC6669988/)
aporta referencia alemana estratificada hasta 79.99 años, pero no corresponde
a TOL-DX. [Registro colombiano API](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/)
incluye Raven parte A de 12 elementos y normas por edad/escolaridad. Su texto
define bandas 61–65 y 65 o más y remite a un suplemento para mayores de 60;
se debe cotejar esa selección antes de programar un caso de edad 65. La muestra
general es mayoritariamente joven y el subconjunto mayor es mucho menor.
Estas referencias no se cuentan como implementadas ni sustituyen WAIS o TOL-DX.

## Verificación

Prueba inicial: **32 pruebas / 898 aserciones**, incluyendo 31 casos unitarios y
una prueba de integración con seis aplicaciones sintéticas en SQL. Se contrastan
los cinco ejemplos con aritmética decimal independiente. La prueba SQL recorre
la operación autorizada de captura, consulta y perfil, conserva PK de fuentes,
usa edad al momento de la aplicación y acredita cero evaluaciones expertas
creadas. El perfil conjunto conserva dos medidas de una única aplicación.

Se prueban extremos de edad/escolaridad, ausencia, cero, sexo, identidad,
condiciones desconocidas o falsas, longitudes frente a totales, forma JLO corta,
sumas contradictorias y varianza dependiente. No se infiere validación clínica
de pruebas sintéticas. Las ejecuciones ampliadas se conservan en el seguimiento
de fase 3. Build/QA visual no aplican: sin cambio frontend.

Suite ampliada SQLite: **210 pruebas / 2522 aserciones**, 68.87 s.
Misma selección en PostgreSQL desechable: **210 pruebas / 2521 aserciones**,
91.205 s; clúster detenido y retirado al finalizar. La diferencia de una aserción
entre motores se conserva. Pint y sintaxis PHP: PASS. No se presenta esta
verificación de ingeniería como validación clínica o gate experto independiente.

## Archivos de este incremento

- `motor-experto/src/motor_experto/dominio/instrumentos/NormalizadorReferenciasEspana.php` (nuevo).
- `motor-experto/src/motor_experto/dominio/instrumentos/RegistroMetodosInstrumentales.php`.
- `motor-experto/src/motor_experto/dominio/instrumentos/CatalogoCapturaCognitiva.php`.
- `tests/Unit/NormalizacionReferenciasEspanaTest.php` (nuevo).
- `tests/Feature/CapturaInstrumentalInstitucionalTest.php`.
- Este contrato, manual investigado, seguimiento de fase 3 e índice del experto.

No hay migraciones, cambios de permisos, de reglas expertas, de historias clínicas,
de configuración institucional o de dependencias. No se ejecutó Petersen, G09 o
un motor de laboratorio sobre residentes reales. El cierre completo de los
diecinueve sigue requiriendo resolver los ocho algoritmos pendientes y comprobar
la pertinencia de sus referencias, sin fabricar baremos.
