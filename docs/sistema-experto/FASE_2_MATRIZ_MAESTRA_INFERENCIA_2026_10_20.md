# O.R.I.O.N. — matriz maestra de conocimiento e inferencia

Fecha de cotejo: 20/10/2026. Versión ejecutable: `ORION-COGNITIVO-1` (contrato A–D del 19/10/2026); Petersen: `PETERSEN-INSTITUCIONAL-1` (contrato E–H). Esta matriz describe lo que está implementado y sus condiciones de emisión. No acredita sensibilidad, especificidad ni diagnóstico.

## Fuentes y alcance

El [cotejo documental G06–G12, Anexo A y Documento Maestro](FASE01_COTEJO_DOCUMENTAL_Y_VT_2026_10_18.md) conserva enlaces, hashes y diferencias de cada original. La [matriz VT01–VT15](FASE01_MATRIZ_VT_G09_2026_10_18.csv) registra 225 cruces de reglas y escenarios; varios cruces comparten una prueba, por lo que no equivalen a 225 pacientes ni a 225 ejecuciones independientes. El número «104 escenarios» de la solicitud no identifica un conjunto local reproducible en este corte. Hasta localizar su manifiesto y entradas, no se afirma haberlos ejecutado.

Los 19 componentes son parte de una red conceptual de 5 dominios. Las 286 aplicaciones históricas conciliadas son **observaciones clínicas por componente**; `SIM_DIFICULTAD` significa dificultad documentada y `SIM_SIN_DIFICULTAD` significa que no se documentó dificultad en ese registro. Ninguno acredita una medición objetiva, normalidad, deterioro o DCL. La [decisión A–D adoptada](PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md) permite presentar estas observaciones y activar G09 solo mediante antecedentes instrumentales C1, C2 y C3 de la misma aplicación. Los perfiles interpretativos concretos siguen vacíos por defecto.

## Contrato común de entrada y exclusión

| Elemento | Entrada exigida | Rechazo o abstención |
| --- | --- | --- |
| Procedencia | Residente, fuente y registro originales, profesional, fecha clínica, estado, corte, versión de conocimiento, instrumento y edición; respuesta bruta, pregunta y opción cuando corresponda | Ajeno, posterior al corte, anulado, fuente incompatible, versión ausente o identidad ambigua |
| C1 | Dificultad específica interpretada mediante contrato de perfil versionado y medida bruta identificable de esa aplicación | Observación nominal, puntuación aislada, baremo no aplicable, perfil inexistente o método sin vínculo original |
| C2 | Atribución exacta al componente, tarea, instrumento y edición | Parecido textual, dominio vecino, tarea no identificada |
| C3 | Condiciones de aplicación, confusores y corroboración exigible conocidos para esa tarea | NULL/FALSE predeterminado, contexto no registrado, confusor no evaluado |
| Conjunción | C1 ∧ C2 ∧ C3 en una misma aplicación | Condiciones repartidas entre aplicaciones, incluso si comparten una atención |
| Resolución | Fuentes utilizables, sin valores opuestos dentro del mismo soporte/episodio | Contradicción conserva ambas evidencias y suspende la consecuencia; no hay voto, peso ni desempate |

La fecha de transcripción no sustituye la fecha clínica. Dos respuestas de una tarea o un episodio compartido no se cuentan como corroboración independiente. Una condición ausente produce `condiciones_pendientes`; cuando las condiciones están repartidas entre aplicaciones, `motivos_no_emision` identifica `SIN_CONJUNCION_EN_UNA_MISMA_APLICACION`. `traza.rutas_hallazgos` solo enlaza fuentes admitidas con consecuencias emitidas.

G10 documenta 33 posibles confusores para ATE/EJE/LEN/VIS (7/9/9/8). En la ruta experimental se conservan como declaraciones por tarea, con presencia triestado, efecto, fuente y justificación; presencia o efecto desconocidos impiden esa tarea. La ruta institucional exige que el contrato de cada aplicación los trate dentro de C3, pero no traslada automáticamente las declaraciones sintéticas G10 a residentes ni deduce fatiga, déficit sensorial, idioma o causalidad de una nota libre. Los identificadores y la cobertura A01–A14 están en el [catálogo cotejado](FASE01_CATALOGO_REGLAS_2026_10_18.md); antes de adoptar un perfil instrumental concreto hay que especificar sus confusores aplicables y su captura original.

## Cinco criterios y diecinueve componentes

Las variables `VAR-OBS-{componente}` son descriptivas y tienen relación `CONTEXTUALIZA`; no satisfacen antecedentes instrumentales. Para cada G09, las variables `VAR-{componente}-C1/C2/C3` deben valer `ACREDITADA`. Su fuente es una captura instrumental original con perfil y medidas aplicables. La consecuencia indicada es un **hallazgo candidato específico**, nunca un estado A/P de dominio ni un diagnóstico.

| Criterio | Componente / tarea | Regla versionada | Variables y condición | Consecuencia exacta | Caso positivo / negativos |
| --- | --- | --- | --- | --- | --- |
| COG-MEM | MEM-S01 aprendizaje/adquisición | Sin regla propia por subcomponente | `VAR-OBS-MEM-S01` contextual; contrato instrumental de tarea pendiente | Sin consecuencia específica | Observación visible; abstención instrumental |
| COG-MEM | MEM-S02 recuerdo diferido libre | Sin regla propia por subcomponente | `VAR-OBS-MEM-S02` contextual; contrato instrumental de tarea pendiente | Sin consecuencia específica | Observación visible; abstención instrumental |
| COG-MEM | MEM-S03 recuerdo con claves | Sin regla propia por subcomponente | `VAR-OBS-MEM-S03` contextual; contrato instrumental de tarea pendiente | Sin consecuencia específica | Observación visible; abstención instrumental |
| COG-MEM | MEM-S04 reconocimiento | Sin regla propia por subcomponente | `VAR-OBS-MEM-S04` contextual; contrato instrumental de tarea pendiente | Sin consecuencia específica | Observación visible; abstención instrumental |
| COG-ATE | ATE-S01 atención sostenida | R-ATE-S01-P01 | `VAR-ATE-S01-C1/C2/C3 = ACREDITADA` | ATE-R-SOSTENIMIENTO | Tres hechos de una aplicación / cada C faltante, aplicación dividida |
| COG-ATE | ATE-S02 atención selectiva | R-ATE-S02-P01 | `VAR-ATE-S02-C1/C2/C3 = ACREDITADA` | ATE-R-SELECCION | Igual, con componente exacto |
| COG-ATE | ATE-S03 atención dividida | R-ATE-S03-P01 | `VAR-ATE-S03-C1/C2/C3 = ACREDITADA` | ATE-R-DISTRIBUCION | Igual, con componente exacto |
| COG-EJE | EJE-S01 control inhibitorio | R-EJE-S01-P01 | `VAR-EJE-S01-C1/C2/C3 = ACREDITADA` | EJE-R-INHIBICION | Igual, con componente exacto |
| COG-EJE | EJE-S02 flexibilidad cognitiva | R-EJE-S02-P01 | `VAR-EJE-S02-C1/C2/C3 = ACREDITADA` | EJE-R-FLEXIBILIDAD | Igual, con componente exacto |
| COG-EJE | EJE-S03 memoria de trabajo | R-EJE-S03-P01 | `VAR-EJE-S03-C1/C2/C3 = ACREDITADA` | EJE-R-ACTUALIZACION | Igual; pertenece a EJE, no a MEM |
| COG-EJE | EJE-S04 planificación | R-EJE-S04-P01 | `VAR-EJE-S04-C1/C2/C3 = ACREDITADA` | EJE-R-PLANIFICACION | Igual, con componente exacto |
| COG-EJE | EJE-S05 razonamiento/abstracción | R-EJE-S05-P01 | `VAR-EJE-S05-C1/C2/C3 = ACREDITADA` | EJE-R-RAZONAMIENTO | Igual, con componente exacto |
| COG-LEN | LEN-S01 denominación | R-LEN-S01-P01 | `VAR-LEN-S01-C1/C2/C3 = ACREDITADA` | LEN-R-DENOMINACION | Igual, con componente exacto |
| COG-LEN | LEN-S02 fluidez verbal | R-LEN-S02-P01 | `VAR-LEN-S02-C1/C2/C3 = ACREDITADA` | LEN-R-FLUIDEZ | Igual, con componente exacto |
| COG-LEN | LEN-S03 comprensión | R-LEN-S03-P01 | `VAR-LEN-S03-C1/C2/C3 = ACREDITADA` | LEN-R-COMPRENSION | Igual, con componente exacto |
| COG-LEN | LEN-S04 repetición | R-LEN-S04-P01 | `VAR-LEN-S04-C1/C2/C3 = ACREDITADA` | LEN-R-REPETICION | Igual, con componente exacto |
| COG-LEN | LEN-S05 discurso | R-LEN-S05-P01 | `VAR-LEN-S05-C1/C2/C3 = ACREDITADA` | LEN-R-DISCURSO | Igual, con componente exacto |
| COG-VIS | VIS-S01 relaciones espaciales | R-VIS-S01-P01 | `VAR-VIS-S01-C1/C2/C3 = ACREDITADA` | VIS-R-PERCEPCION | Igual, con componente exacto |
| COG-VIS | VIS-S02 visuoconstrucción | R-VIS-S02-P01 | `VAR-VIS-S02-C1/C2/C3 = ACREDITADA` | VIS-R-CONSTRUCCION | Igual, con componente exacto |

Memoria conserva, además, **MEM-DIFICULTAD** (`VAR-MEM-INST-01 = DIFICULTAD_INSTRUMENTAL` → `DIFICULTAD_EVIDENCIADA`) y **MEM-SIN-DIFICULTAD** (`SIN_DIFICULTAD_INSTRUMENTAL` → `SIN_DIFICULTAD_EVIDENCIADA`). Son reglas de criterio con compuerta EV-CM-2, contexto de componente/condiciones/confusores por aplicación y resolvedor D-131. Su resultado no se copia a los cuatro subcomponentes ni a los demás dominios. Una tarea incompleta se excluye sin invalidar otra completa. Dos hallazgos opuestos del mismo episodio suspenden el resultado.

Los predicados de los 15 G09 son ejecutables con hechos admisibles; los datos observacionales actuales no acreditan C1. La ausencia de un perfil interpretativo específico y de medidas realmente administradas impide emitir el hallazgo para esa tarea, sin bloquear las otras reglas. No se crea un umbral universal para personas mayores de 60 años: cada contrato instrumental debe acreditar idioma, edición, población, edad, escolaridad, método y derechos de uso según su fuente.

## Posible DCL y señales de seguimiento

La [decisión E–H adoptada](PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md) implementa un resolvedor distinto, enlazado por la preparación institucional al mismo residente, corte, usuario y versión instrumental. Exige valoraciones originales admisibles de C01 cambio corroborado, C02 alteración objetiva derivada del dominio, C03 autonomía IADL, C04 evaluación diferencial y C05 calidad del conjunto; los cinco dominios deben tener interpretación original A/P. Con los requisitos acreditados, emite una de cuatro distribuciones orientativas: amnésico de dominio único/múltiple o no amnésico de dominio único/múltiple. Todos P, U, I, X, requisitos no acreditados o impedimento diferencial conservan abstención y motivos. COG-MEM, G09, observaciones, BADL y un z aislado no acreditan por sí solos los requisitos.

La señal descriptiva sustentada hoy es **dificultad observada en un componente concreto**, con su autor, fecha y original. No es una estimación de riesgo de DCL. No hay contrato aprobado de pesos, umbrales o riesgo probabilístico; por ello una puntuación de riesgo, una alerta de severidad y un diagnóstico automático quedan **no ejecutables**. Para habilitarlos faltarían una definición institucional de finalidad, población, predictores, confusores, resultado de referencia, calibración/validación y conducta asociada. La evaluación puede recomendar revisión profesional de las observaciones sin calificar probabilidad.

## Pruebas y trazabilidad ejecutable

- `PerfilInstitucionalAdoptadoTest`: 15 G09, cada C1/C2/C3 ausente, ruta fuente→evidencia→variable→condición→regla→consecuencia, perfil ausente, componente/edición incorrectos, conjunción dividida, contexto de Memoria, episodio compartido y contradicción.
- `PerfilCognitivoDeclarativoTest` e `IntegracionRedCognitivaTest`: red, pertenencia, ejecución declarativa, NULL y contradicciones.
- `ConocimientoCognitivoInstitucionalTest`: consulta Laravel, fuentes SQL, permisos, versiones, 19 observaciones y exclusiones.
- `PetersenInstitucionalTest`, `GobiernoPetersenInstitucionalTest` e `InstantaneaEvaluacionInstitucionalTest`: cuatro patrones sintéticos, abstenciones, originalidad, gobierno, autorización y preparación compuesta. Las pruebas sintéticas verifican software, no validez clínica.

## Fundamento científico y límite de transferencia

- [Petersen et al., *Mild cognitive impairment: clinical characterization and outcome*, 1999](https://pubmed.ncbi.nlm.nih.gov/10190820/): caracterización original de DCL; no define un umbral universal aplicable a estas observaciones.
- [Winblad et al., *Mild cognitive impairment—beyond controversies*, 2004](https://onlinelibrary.wiley.com/doi/10.1111/j.1365-2796.2004.01380.x): amplía el examen a perfiles no amnésicos y multidominio; no sustituye una valoración funcional original.
- [Albert et al., criterios NIA-AA para DCL por enfermedad de Alzheimer, 2011](https://pubmed.ncbi.nlm.nih.gov/21514249/): historia de cambio, evidencia cognitiva, independencia relativa y descarte diferencial; su etiología no puede inferirse de los datos actuales.
- [DETeCD-ADRD, guía clínica para atención especializada, 2025](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772716/): evaluación estructurada, dominios cognitivos, función y contexto clínico; no proporciona reglas de conversión de los 19 literales observacionales a diagnósticos.
- [DETeCD-ADRD, instrumentos validados, 2025](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/): selección e interpretación contextual de pruebas. La bibliografía ofrece métodos candidatos; no demuestra qué prueba recibió cada residente.

Estas publicaciones respaldan el marco de evaluación y las cautelas; la semántica concreta de C1–C3, D-131 y E–H corresponde a decisiones versionadas del proyecto, no a texto literal de las publicaciones. No se copiaron reactivos ni materiales protegidos.
