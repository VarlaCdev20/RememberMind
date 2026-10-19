# O.R.I.O.N. — G08: auditoría instrumental de los 15 subcomponentes (expediente experimental)

> Procedencia: investigación del worktree `pr15-ci-utf8`, HEAD `8085ec0e`, incorporada selectivamente el 18/10/2026. Esta entrega verifica el código de laboratorio; no vuelve a acreditar los artículos ni una ejecución remota de CI. Los conteos históricos del expediente no sustituyen el inventario vigente de 106 tablas de negocio. Evidencia actual en [auditoría Fase 01](FASE01_AUDITORIA_Y_EVIDENCIA_2026_10_18.md).

Fecha: 2026-10-17. Estado: **investigación documental cotejada contra el expediente G08 del 13/10/2026; NO cierre clínico G08**. Estas fichas no autorizan administración o puntuación de instrumentos.

## Convención de estados

- `FUENTE_NORMATIVA_CANDIDATA`: expediente del proyecto identifica publicación normativa, algunas con participación boliviana. **No** equivale a edición ni punto de corte adoptados.
- `NO_RESUELTO`: falta como mínimo una de edición, aplicabilidad poblacional, norma, atribución exacta o permiso de uso.
- `PERFIL_INTERPRETATIVO_ADMITIDO`: reservar para decisión ulterior con evidencia completa y responsable identificable; **ninguna fila presenta este estado**.

## Matriz G08 por componente

| Componente | Prueba candidata | Constructo | Evidencia y pendiente crítico | Estado |
|---|---|---|---|---|
| ATE-S01 | Ruff 2 & 7 | sostenida | Edición/protocolo y rango 16–70 editorial según expediente; población geriátrica >70 no cubierta automáticamente | `NO_RESUELTO` |
| ATE-S02 | Ruff 2 & 7 | selectiva | Edición/norma, percepción de distractores y rango etario por verificar | `NO_RESUELTO` |
| ATE-S03 | Brief Test of Attention (BTA) | dividida | Rivera et al. 2015 doi:10.3233/NRE-151283; interpretación concurrente, licencia, administración, edad/escolaridad pendientes | `FUENTE_NORMATIVA_CANDIDATA` |
| EJE-S01 | Stroop | inhibición | Rivera et al. 2015 doi:10.3233/NRE-151281; condición interferencia, visión/color/lectura y versión pendientes | `FUENTE_NORMATIVA_CANDIDATA` |
| EJE-S02 | TMT-B / M-WCST | flexibilidad | Arango-Lasprilla et al. 2015 doi:10.3233/NRE-151284 (TMT) y 10.3233/NRE-151280 (M-WCST); separar velocidad, visión y motricidad | `FUENTE_NORMATIVA_CANDIDATA` |
| EJE-S03 | WAIS-IV dígitos inversos y secuenciación | manipulación/actualización | No equivalencia automática entre invertir y actualizar; edición, permiso y normas poblacionales pendientes | `NO_RESUELTO` |
| EJE-S04 | Torre de Londres | planificación | Variantes no comparables automáticamente; selección de edición y norma pendiente | `NO_RESUELTO` |
| EJE-S05 | M-WCST | razonamiento/abstracción | Prueba de flexibilidad y formación conceptual; validez de atribución a razonamiento general no acreditada | `NO_RESUELTO` |
| LEN-S01 | Boston Naming Test, forma estándar | denominación | Olabarrieta-Landa et al. 2015 doi:10.3233/NRE-151278; edición y normas dependientes de población | `FUENTE_NORMATIVA_CANDIDATA` |
| LEN-S02 | Pruebas de fluidez verbal | fluidez | Olabarrieta-Landa et al. 2015 doi:10.3233/NRE-151279; letra/categoría/duración/idioma/educación deben coincidir | `FUENTE_NORMATIVA_CANDIDATA` |
| LEN-S03 | Token Test | comprensión | Peña-Casanova et al. 2009 PMID 19648582; muestra de España, no norma Bolivia; audición/idioma | `NO_RESUELTO` |
| LEN-S04 | WAB-R | repetición | Edición, derechos, rango de edad/adaptación y control auditivo-motor pendientes | `NO_RESUELTO` |
| LEN-S05 | WAB-R | discurso | Discurso requiere tarea definida; no NLP libre; permisos y normas pendientes | `NO_RESUELTO` |
| VIS-S01 | VOSP espacial/JLO | relaciones espaciales | Peña-Casanova et al. 2009 doi:10.1093/arclin/acp040; normas españolas no homologadas a Bolivia | `NO_RESUELTO` |
| VIS-S02 | Rey–Osterrieth copia | visuoconstrucción | Rivera et al. 2015 doi:10.3233/NRE-151285; fase copia no equivale a recuerdo de figura | `FUENTE_NORMATIVA_CANDIDATA` |

## Plantilla de cierre instrumental (repetir por cada combinación válida)

1. `codigo_componente` + `codigo_regla`; objetivo cognitivo exacto y distinciones con dominios vecinos.
2. Autor/editor y **edición exacta** de instrumento; subprueba, tarea, versión y modalidad.
3. Condiciones de aplicación (idioma, escolaridad, edad, población, limitaciones sensoriales/motoras/psicológicas).
4. Términos autorizados de puntuación; norma aplicable (cita y tabla o algoritmo con permiso); procedimientos y condiciones de interpretación **sin inventar umbrales**.
5. Derechos de reproducción/administración/implementación y responsable de autorización.
6. Entrada de BDD operativa exacta y respuesta original; identificación de residente en ensayo real solamente tras permisos institucionales; conjunto sintético para pruebas técnicas.
7. Perfil semántico candidato: valores de entrada tipados, evidencia insuficiente, contraprueba, confusores y atribución primaria.
8. Casos que demuestran validez interpretativa, contradicción y aplicabilidad; competencia y dictamen del revisor.

## Dependencia con G09 experimental

El ejecutor G09-EXP puede comprobar 15 reglas AND sobre condiciones **ya interpretadas** que procedan de fixtures `TEST_ONLY` exclusivamente. No calcula puntuaciones clínicas, percentiles ni diagnósticos y no incluye adaptadores BDD operativos. Ningún instrumento de esta matriz está integrado como perfil autorizado de interpretación clínica. La investigación documental G08 avanza con identificación de siete fuentes normativas del expediente previo, pero **falta precisar versión, norma aplicable y algoritmo de cada subcomponente**. En un entorno futuro, una entrada sin perfil acreditado debe quedar no interpretable; no debe marcarse como dificultad a partir de texto libre o puntaje global.

## Referencias del proyecto y bibliografía prioritaria

- [Expediente G08 original](https://drive.google.com/file/d/13IR23ftx8SopS0G1QcUFH6m5RfcX1nXo/view)
- [Matriz G09 maestra](https://docs.google.com/document/d/1uWfx3CAbcJNQj6isFYmaFdOvK-RMJYgyYIIMrahMZcc/edit)
- [Enmienda experimental EXP-01](https://docs.google.com/document/d/1jLV4WU1PMvNHk7hnERVCmVCT6QaXtBe2eIyQMKBACPc/edit)
- Brief Test of Attention: doi:10.3233/NRE-151283
- Stroop: doi:10.3233/NRE-151281
- Trail Making Test: doi:10.3233/NRE-151284
- Modified Wisconsin Card Sorting Test: doi:10.3233/NRE-151280
- Boston Naming Test: doi:10.3233/NRE-151278
- Verbal fluency tests: doi:10.3233/NRE-151279
- Rey–Osterrieth copy/recall: doi:10.3233/NRE-151285

**Limitación científica:** estas referencias derivan del expediente instrumental G08; no se han reextraído manuales protegidos por derechos ni creado interpretaciones nuevas. La correspondencia exacta con edición/población debe resolverse antes de uso con datos reales.

## Cotejo bibliográfico adicional con publicaciones primarias (17/10/2026)

La auditoría contrastó los artículos normativos originales mencionados en el expediente del proyecto. **Ninguno se transforma automáticamente en un perfil clínico activo**. No se han implementado fórmulas normativas ni cortes en el código.

| Publicación | Confirmación verificable | Restricción de interpretación |
|---|---|---|
| BTA — Rivera y colaboradores (2015), [PMID 26639928](https://pubmed.ncbi.nlm.nih.gov/26639928/) | Normas multicéntricas de 11 países, incluida Bolivia, con muestra total de 3977 adultos sanos; el estudio identifica atención auditiva dividida | Requiere examinar edición y protocolo auditivo exactos; la presencia de Bolivia no acredita automáticamente una interpretación aplicable a todo residente |
| Stroop — Rivera y colaboradores (2015), [PMID 26639926](https://pubmed.ncbi.nlm.nih.gov/26639926/) | Población multicéntrica de 11 países, Bolivia incluida | La interpretación de inhibición requiere precisar condición de interferencia, edición, lectura, percepción de colores y variables normativas |
| TMT — Arango-Lasprilla y colaboradores (2015), [PMID 26639932](https://pubmed.ncbi.nlm.nih.gov/26639932/) | Estudio de 11 países, Bolivia incluida; modelos distintos para TMT-A y TMT-B | No inferir flexibilidad desde TMT-A ni confundir velocidad/motricidad con el proceso ejecutivo |
| M-WCST — Arango-Lasprilla y colaboradores (2015), [PMID 26639931](https://pubmed.ncbi.nlm.nih.gov/26639931/) | Normas latinoamericanas multicéntricas | Verificar subresultado antes de atribuir flexibilidad; razonamiento general EJE-S05 aún sin respaldo suficiente |
| Boston Naming Test estándar — Olabarrieta-Landa y colaboradores (2015), [PMID 26639925](https://pubmed.ncbi.nlm.nih.gov/26639925/) | **3779 adultos sanos de diez países, entre ellos Bolivia** | No extrapolar edición estándar a BNT abreviado ni BNT-2, ni omitir escolaridad/idioma |
| Fluidez verbal — Olabarrieta-Landa y colaboradores (2015), [PMID 26639930](https://pubmed.ncbi.nlm.nih.gov/26639930/) | Normas de once países, entre ellos Bolivia | Los protocolos fonémico y semántico no son intercambiables; registrar modalidad y reglas de administración |
| Rey–Osterrieth — Rivera y colaboradores (2015), [PMID 26639929](https://pubmed.ncbi.nlm.nih.gov/26639929/) | 3977 participantes de once países, Bolivia incluida, con resultados separados de copia y recuerdo inmediato | Para VIS-S02 considerar específicamente **copia**; recuerdo pertenece prioritariamente a Memoria |

**Estado científico:** confirmación bibliográfica positiva de la existencia y alcance general de los estudios. Siguen abiertas: la compatibilidad con cada edición disponible, condiciones de aplicación, cobertura por edad/escolaridad/población institucionalizada, método de normalización e interpretación del componente y derechos de uso. Los 15 componentes permanecen sin `PERFIL_INTERPRETATIVO_ADMITIDO`. La regla experimental podrá funcionar con fixtures **TEST_ONLY**, pero no con respuestas clínicas reales.

## Conciliación con el esquema operacional (17/10/2026)

La revisión de las migraciones `2026_09_18_001011_create_instrumentos_table.php`, `2026_09_18_001057_create_preguntas_instrumento_table.php`, `2026_09_18_001058_create_opciones_pregunta_table.php`, `2026_09_18_001059_create_aplicaciones_instrumento_table.php` y `2026_09_18_001060_create_respuestas_instrumento_table.php` confirmó las **fuentes que ya existen**: `instrumentos`, `preguntas_instrumento`, `opciones_pregunta`, `aplicaciones_instrumento` y `respuestas_instrumento`.

`aplicaciones_instrumento` utiliza **`fecha_hora` DATETIME** (no `fecha_aplicacion`), `cod_residente`, `cod_personal`, `estado` y datos de resultados globales como `puntaje_total`, `clasificacion`, `interpretacion`. Ninguno de esos totales o textos de clasificación constituye evidencia admisible de un componente sin vinculación con la tarea/versión/norma. En las respuestas, `valor_texto` y `valor_numero` no son intercambiables; las respuestas deben identificarse por `cod_pregunta` y `cod_aplicacion`.

El puente experimental ya demuestra la lectura íntegra desde **SQLite exclusivamente en memoria con registros sintéticos**, pero **la mera existencia de estas columnas no resuelve G08**: siguen sin seleccionarse ni verificarse la edición, la normativa aplicable y la interpretación de las 15 pruebas en personas reales.

Para cerrar por instrumento, las fichas requerirán correspondencia verificable entre **preguntas codificadas, tareas/subpruebas reales, instrumento, edición, idioma, baremo, población, confusores y fundamento de decisión**. No se extrapolarán reglas de un estudio de normas a una edición no identificada.


## Incremento G08 — catálogo verificable y controles de aplicabilidad (17/10/2026)

Se agregó el [dossier bibliográfico por instrumento](G08_FICHAS_CANDIDATAS_PUBLICACIONES_Y_BRECHAS.md), contrastado con los siete resúmenes primarios indexados en PubMed y las decisiones del expediente G08. Distingue **6 subcomponentes con fuente normativa candidata**, **7 artículos candidatos distintos** y **15 subcomponentes todavía sin perfil interpretativo admitido**. EJE-S05 mantiene estado `NO_RESUELTO` pese a compartir un artículo sobre M-WCST, porque la atribución al razonamiento general no está acreditada.

Se implementaron `CatalogoExpedientesG08Investigacion` y `AuditorAplicabilidadG08Investigacion`. El primero registra constructo, instrumentos, tareas, publicaciones localizadas y pendientes. El segundo verifica coherencia de instrumento/tarea y lista explícitamente las pruebas documentales por edad, escolaridad, norma, idioma, edición, aplicación, derechos, condiciones sensoriales, confusores y revisión.

**Decisión de ingeniería vigente:** una checklist completa `declaracion_completa=true` es solamente una declaración del evaluador técnico; **nunca** convierte el registro en perfil clínico autorizado. Todas las salidas mantienen `NO_INTERPRETABLE_POR_G08`, `puede_emitir_A_o_P=false`, `evidencia_clinica=null` y `perfil_normativo_activado=false`.

La suite `g08_auditoria_instrumental.php` cubre las 15 fichas, sus fuentes, seis estados bibliográficos, alternativas TMT-B/M-WCST, exclusión de BNT abreviado, diferenciación copia/recuerdo ROCF, confusores BTA, advertencia Ruff para edad mayor a 70 según el expediente (sin adoptar un rango normativo en software), rechazo de razonamiento EJE-S05 y denegación de uso asistencial. Se incluyó en `.github/workflows/orion-g09-experimental.yml`; las pruebas específicas resultaron satisfactorias en GitHub Actions.

La próxima decisión científica no consiste en activar una puntuación, sino en **seleccionar con sustento una edición/protocolo concreto y comprobar la compatibilidad de su tabla normativa**. Las normas de adultos saludables de varios países, aunque incluyan Bolivia, no autorizan extrapolación automática a residentes institucionalizados.


## Precisión de anexos bolivianos (17/10/2026)

Se verificaron directamente las publicaciones completas de los siete estudios candidatos, identificando los **12 localizadores de tablas** para los **6 subcomponentes** G08 con referencia normativa candidata. Véase [G08_LOCALIZADORES_BOLIVIA_PUBLICACIONES_2015.md](G08_LOCALIZADORES_BOLIVIA_PUBLICACIONES_2015.md).

Tabla boliviana por subresultado: BTA **A2**; Stroop **interferencia A35** (no confundir con Word, Color, Word-Color); TMT **B A14**; M-WCST **categorías A2**, **perseveraciones A13**, **errores totales A24**; Boston Naming Test **estándar A2**; fluidez **letra F A2**, **letra A A13**, **letra S A24**, **animales A35**; Rey–Osterrieth **copia A2**. El nombre «A2» se reutiliza **entre publicaciones**: nunca constituye una clave normativa independiente sin DOI, instrumento y tarea.

Al menos dos hallazgos son importantes para controlar errores: el **BNT estándar Bolivia** usa una tabla por escolaridad, mientras la tabla boliviana de **Stroop Interference** no se encuentra estratificada por edad/escolaridad en su anexo. En cambio BTA y TMT-B ofrecen tablas bolivianas por edad/escolaridad. Estas diferencias refutan adoptar «una sola fórmula normativa» para todos los dominios.

Se añadió `LocalizadoresAnexosBoliviaG08Investigacion` y la prueba `g08_anexos_bolivia.php`; comprueban identidad por componente, instrumento, métrica, país, PMID, DOI y número de anexo. **No se han importado valores normativos ni se ofrece un percentil o criterio de DCL.** La selección de edición, permisos, algoritmo y aplicabilidad geriátrica permanece pendiente; las referencias editoriales `>77` no acreditan por sí mismas generalización a población institucionalizada.

**G08 sigue 0/15 perfiles interpretativos admitidos y el uso asistencial continúa bloqueado.**


## Captura cruda y comparabilidad de cohortes (17/10/2026)

La especificación G08 avanza desde el **localizador de un anexo** hacia un **contrato de captura cruda por tarea**, documentado en [G08_CONTRATO_CAPTURA_Y_COMPARABILIDAD_MUESTRA.md](G08_CONTRATO_CAPTURA_Y_COMPARABILIDAD_MUESTRA.md). Se incorporaron `ContratoCapturaCrudaG08Investigacion` y `AuditorCapturaYComparabilidadG08Investigacion` junto a la prueba `g08_captura_y_muestras.php`.

Para los 12 pares tarea–anexo localizados se tipifican los **campos crudos de ensayo y la unidad** antes de cualquier normalización: BTA separa aciertos N y L, Stroop conserva sus mediciones básicas pero NO calcula el índice de interferencia, TMT-B conserva tiempo y errores, M-WCST diferencia categorías de tipos de errores, BNT conserva denominaciones correctas, fluidez conserva modalidad/tiempo/respuestas y Rey–Osterrieth usa solamente copia. Las claves son contratos **ficticios** y todavía no están homologadas a preguntas BDD del instrumento real.

La revisión primaria de metodología BTA, TMT, Stroop y BNT confirmó que sus muestras son de voluntarios sanos comunitarios con rangos y criterios de selección: 18–95 años, español materno, alfabetización y al menos un año de escolaridad, además de condiciones de cribado y exclusión. Estos requisitos describen **cómo se seleccionó la muestra del estudio**, no umbrales diagnósticos de O.R.I.O.N. ni reglas automáticas de elegibilidad de residentes. El auditor advierte sobre origen, idioma, educación, edad, población institucionalizada y diferencias de selección; una persona institucionalizada no se presume equivalente a la muestra comunitaria.

**Invariantes:** ninguna condición documental habilita norma ni estudio clínico. Las salidas mantienen `NO_INTERPRETABLE_POR_G08`, `NO_HOMOLOGADO_A_GERIATRICO`, `percentil=null`, `interpretacion_A_P=null` y `uso_asistencial_autorizado=false`, aun si el fixture afirma haber verificado todos sus campos. **0/15 perfiles G08 admitidos.**
