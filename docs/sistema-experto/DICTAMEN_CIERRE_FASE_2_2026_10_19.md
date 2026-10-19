# Dictamen de cierre de la fase 2: fuentes originales

Fecha: 19/10/2026. Código de referencia: `c375b5b8`. Investigación primaria, cotejo de archivos y auditoría de la instalación local. Este expediente reúne el resultado final de la revisión; no cambia el alcance de aceptación para declarar un cierre sin evidencia.

**Decisión vigente de la propietaria:** «Son observaciones clínicas por componente», en respuesta a si las 286 aplicaciones eran pruebas estandarizadas con aciertos/tiempos u observaciones clínicas. **Resultado: conciliación y trazabilidad del lote observacional cerradas**, lectura institucional comprobada y 228 pruebas aprobadas en cada motor. La QA visual permanece pendiente por restricción del navegador. Las pruebas estandarizadas no administradas no constituyen mediciones históricas faltantes que deban fabricarse o buscarse mediante bibliografía.

## 1. Alcance y jerarquía

La aceptación de fuentes exige identificar qué acto ocurrió, sus preguntas/respuestas, autoría/fechas y origen, y conciliar las importaciones con historia. La aclaración directa de la propietaria resuelve la naturaleza de las 286 entradas: son observaciones por componente. Edición, aciertos y tiempos de una prueba distinta **no aplican a ese acto**. Se utiliza la [decisión estructural aprobada](PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md), el [contrato de adquisición implementado](FASE_2_FUENTES_ORIGINALES_2026_10_19.md), el [diccionario de 23 métodos / 19 componentes](DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md) y el [plan integral](PLAN_CIERRE_INTEGRAL_2026_10_19.md). El Barthel previo y la aplicación futura de métodos cognitivos conservan sus alcances propios; no se atribuye a esta conciliación el cotejo de sus originales.

La propietaria declaró que las transcripciones proceden de registros reales. Esta declaración se conserva. Las etiquetas genéricas de los archivos no se usan para negar la existencia de una observación; tampoco identifican por sí mismas una edición estandarizada. No se modifican esos originales para hacerlos coincidir con la bibliografía.

## 2. Investigación real contrastada

Consulta: 19/10/2026. Se seleccionaron fuentes oficiales de sus organizaciones y publicaciones originales. No se consultaron nombres, expedientes ni datos identificables de residentes en buscadores o servicios externos.

| Fuente y localizador comprobado | Resultado útil para fase 2 | Aplicación al contrato existente |
| --- | --- | --- |
| [ITC, International Guidelines for Test Use, 2001](https://www.intestcom.org/files/ijt_testuse_guidelines.pdf), DOI 10.1207/S15327574IJT0102_1; §§2.5.7, 2.5.10–11, 2.6.1–8 y 2.7.5–12 | Administración conforme al manual, registro de desviaciones y tiempos, conversión y comparación apropiadas al contexto | Mantener separados tiempo medido y duración de agenda; cotejar versión, condiciones y puntuación antes de normalizar |
| [ITC, Quality Control, versión 1.2 del 12/10/2013](https://www.intestcom.org/files/guideline_quality_control.pdf), documento ITC-G-QC-20131012; §§1.2.10, 2.3.1.4–7 y 2.3.2.1–10 | Tratamiento explícito de ausencias, cotejo de captura, reglas para duplicados y verificación del algoritmo/tablas | Conciliación previa, NULL conservado, conflictos documentados y correcciones enlazadas; no introducir medidas para obtener una respuesta esperada |
| [ITC, adaptación de tests, 2.ª edición, 2017, versión 2.4](https://www.intestcom.org/files/guideline_test_adaptation_2ed.pdf), SSI-1/2 y Doc-1/2 | Comparabilidad y documentación de adaptaciones y restricciones poblacionales | La norma bibliográfica mantiene su población; no identifica la prueba administrada ni acredita una adaptación local |
| [Atri et al., DETeCD-ADRD: Validated clinical assessment instruments, 2025](https://pubmed.ncbi.nlm.nih.gov/39713939/), DOI 10.1002/alz.14335, resumen | Instrumentos validados y rendimiento integrado con perfil clínico y juicio profesional | Conservar diferenciadas observación, medición instrumental y resultado; una nota descriptiva no aporta los aciertos/tiempos de otra prueba |
| [OMS, Ageing and health, 1/10/2025](https://www.who.int/news-room/fact-sheets/detail/ageing-and-health), Overview y Ageing explained | Cohorte de 60 o más años y heterogeneidad del envejecimiento | Edad cumplida en la aplicación ≥60; comprobar además el rango particular de cada método, sin atribuir rendimiento por edad |
| [HL7 FHIR R5, Provenance](https://hl7.org/fhir/R5/provenance.html), §§6.3.1–2 y estructura occurred/recorded/agent/entity | Identificación de actividad, momento, agentes y fuentes/versiones | Aplicador original, transcriptor actual, fecha clínica y registro técnico separados. Referencia de diseño, sin introducir FHIR ni afirmar conformidad |

La ficha de [Mahoney y Barthel, 1965](https://pubmed.ncbi.nlm.nih.gov/14258950/) se comprobó para identificar la referencia bibliográfica funcional. No se usa una ficha sin resumen para atribuirle un protocolo local completo. La copia del artículo localizada en King's College London devuelve 404; no se presenta como manual leído. El registro local `BARTHEL_100` conserva su identidad declarada; no se lo remapea a los métodos cognitivos.

**Límites de acceso documentados:** PMC del artículo DETeCD exigió CAPTCHA; se leyó el resumen en PubMed y no se atribuyen detalles de tablas inaccesibles. NICE NG97, incluido el PDF oficial, devolvió error/403; no se extraen requisitos nuevos de él en esta revisión. El editor de HVLT-R devolvió una página bibliográfica, pero no se obtuvo nuevamente su sección de métodos mediante el lector; las ecuaciones y requisitos previos conservan la evidencia de la [fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), sin fingir una nueva lectura completa.

### Definiciones completadas por investigación

Instrumento de referencia y fuente, identidad requerida, entradas, tipo/columna, unidades, rango etario, condiciones y limitaciones de los 23 métodos están en el diccionario ejecutable documentado. Son requisitos del método. El nombre/edición aplicado, escolaridad exacta, tiempos, aciertos y ayudas de una persona requieren evidencia del acto correspondiente. Una media publicada puede describir una población; incorporarla como si fuera una medición individual cambiaría la procedencia del dato.

## 3. Auditoría actual de la base

Transacción PostgreSQL `REPEATABLE READ READ ONLY`, exclusivamente `local`, `remembermind_dev`, host loopback. Se revierte al terminar. No se ejecutan migraciones, seeders, modificaciones de historia ni scripts aportados por terceros.

| Comprobación | Resultado del 19/10/2026 |
| --- | ---: |
| Instrumentos / preguntas / opciones | 20 / 29 / 68 |
| Aplicaciones / respuestas | 487 / 2296 |
| Aplicaciones del catálogo `BARTHEL_100` | 201 |
| Respuestas asociadas a ese catálogo, con número y puntaje | 2010 |
| Aplicaciones por componente / respuestas nominales | 286 / 286 |
| Aplicaciones con documento FK vinculado | 0 / 487 |
| Aplicaciones con localizador de fuente | 0 / 487 |
| Aplicaciones con pareja usuario de registro + fecha técnica | 0 / 487 |
| Pares aplicación/pregunta duplicados | 0 |
| Respuestas cuya pregunta corresponde a otro instrumento | 0 |
| Perfiles instrumentales institucionales configurados | 0 |
| Aplicaciones con edad histórica ≥60 | 487 / 487 |
| Documentos registrados con archivo y SHA-256 coincidente | 7 / 7 |

**Distinción necesaria:** la base sí contiene 2010 valores numéricos de Barthel. La ausencia numérica previamente informada corresponde a las 286 respuestas del paquete cognitivo, no a toda la base. Las 2010 respuestas no constituyen los ensayos/tiempos de los 19 componentes. Todos los conteos incluyen la historia examinada y no equivalen a aplicaciones normalizables ni a resultados clínicos aprobados.

El registro digital conserva PK, autor clínico y fecha; no se describe como historia inexistente. Las nuevas FK documentales permanecen NULL, pero el lote sí tiene procedencia verificable mediante la referencia de origen en sus registros, los CSV privados retenidos, el plan autorizado y Activitylog. Esa trazabilidad preexistente fue cotejada fila por fila en esta entrega. No se rellena retroactivamente quién digitó antes ni se adjudica a un PDF una respuesta que solo consta en el CSV.

## 4. Cotejo ampliado de archivos

Se revisaron el Excel de 37 hojas, el JSON operativo, los cuatro CSV instrumentales, el contexto auxiliar, la transcripción aportada y el contenido textual extraíble de los siete documentos privados: dos TXT y cinco PDF de tres páginas. Los PDF contienen texto y no imágenes de hojas originales. El cotejo fue de extracción/documentación, no una evaluación visual de formularios clínicos ni de validez de la administración.

| Fuente | Resultado relevante |
| --- | --- |
| CSV instrumentales | 19 instrumentos, 19 preguntas OPCION, 286 aplicaciones/respuestas; 286 textos y cero valores numéricos, lógicos o puntajes |
| Excel, `BDD_RESPUESTAS_INSTRUMENTO!A1:I287` | Las mismas 286 respuestas nominales; no aporta mediciones numéricas/lógicas adicionales |
| JSON operativo, `respuestas_instrumento` | 286 textos, cero números/lógicos/puntajes; no aparece una medición adicional que pueda utilizarse como ensayo o tiempo |
| Contexto auxiliar / hoja `AUX_CONTEXTO_INSTRUMENTAL!A1:N287` | Metadatos para 286 aplicaciones; el campo edición contiene una identificación genérica y el perfil es G08-TEST-v1, sin correspondencia acreditada con una forma/edición de los métodos |
| Escolaridad auxiliar | 172 categorías Primaria y 114 Secundaria; no son años escolares exactos ni permiten asignar automáticamente 6 o 12 |
| Cronología y LEEME horarios | Contienen redistribución y duración de referencia para agenda; no documentan la duración medida de cada tarea ni la demora de recuerdo |
| Documentos y transcripción | Descripciones de observaciones/actividades; el cotejo no acredita una hoja con la identidad y las medidas originales necesarias para configurar un método cognitivo |

Se mantiene toda información aportada sin confundir un campo relleno con una identificación suficiente. «Condiciones conocidas» en un contexto genérico no contesta por sí solo cada pregunta específica de audición, visión, administración o idioma de un método.

No se ejecutaron el SQL, el validador Python aportado ni una nueva importación. No se incorporan claves de resultados esperados o criterios de ensayo como evidencia clínica.

### Conciliación efectiva del lote confirmado

Se reabrieron las cinco fuentes CSV retenidas de la importación original, el plan y el resultado de carga. Para cada fila se comprobó: SHA-256 de su contenido original canónico, registro lógico del CSV, identidad de destino, campos transcritos según las transformaciones declaradas, todos sus vínculos y una única auditoría `transcripcion_registro_incorporado` con la misma huella/origen/destino. Se cotejaron **648 filas**: 19 definiciones + 19 preguntas + 38 opciones + 286 aplicaciones + 286 respuestas.

Las 286 aplicaciones coinciden con el residente, fecha y aplicador del plan confirmado: Psicología, `COD_PER_000027`. Se conserva la redistribución horaria que la propietaria había seleccionado y su procedencia; no se la convierte en tiempo medido de una prueba. Los 19 catálogos siguen `OBSERVACIONAL` e `INACTIVO` para nuevas aplicaciones. Total, máximo y clasificación de esas aplicaciones permanecen NULL. Se confirmaron cero firmas, clasificaciones o perfiles normativos generados por esta operación.

Este cotejo acredita correspondencia de transcripción y conservación; no certifica la administración de una prueba ni su validez clínica. **Cero escrituras en la base.** La evidencia privada nueva es `fase2-conciliacion-observacional.json` y `fase2-trazabilidad-observacional-privada.csv`, en `storage/app/private/`.

Se comprobó además la consulta institucional real de los cinco residentes usando la cuenta existente vinculada a la profesional de Psicología, con los gates actuales. Recuperó las **286 observaciones del lote** como `NO_APLICA_A_OBSERVACION`, sin método ni entradas normativas. No se inició sesión, creó cuenta, amplió acceso, firmó una evaluación ni guardó un resultado en nombre de la profesional. La transacción de comprobación mantuvo solo lectura.

## 5. Entregables concretos de esta revisión

- [x] Investigación final con referencias y localizadores verificables, alcances y accesos fallidos declarados.
- [x] Auditoría agregada de base, identidad de los valores numéricos y separación respecto del paquete nominal.
- [x] Cotejo del Excel/JSON/documentos para buscar mediciones adicionales, con SHA-256 antes y después.
- [x] Listado privado de las 487 aplicaciones para localizar documento/página/entrada y resolver el cotejo sin duplicados.
- [x] Conciliación efectiva de las 648 filas del lote observacional con sus fuentes retenidas, transformaciones, vínculos y auditoría individual.
- [x] Contrato de lectura corregido: `OBSERVACIONAL` no exige perfil normativo, conserva sus respuestas y no admite configurar una prueba sobre ese catálogo.
- [x] Requisitos de entrega de originales y recorrido de conciliación usando las Actions/API existentes.
- [x] Actualización del plan, índice y estado real de cierre, sin alterar datos ni estructura.

Artefactos locales privados, excluidos de Git: `storage/app/private/fase2-cierre-auditoria-bdd.json`, `fase2-cotejo-archivos.json` y `fase2-cotejo-aplicaciones-privado.csv`. El CSV contiene únicamente identidades, fechas, autor y metadatos de procedencia actuales; no contiene nombres ni valores de respuestas. El listado es una ayuda de cotejo, no una carga ni otro expediente asistencial. Su acceso mantiene las protecciones del almacenamiento privado.

## 6. Estado de cada requisito con la aclaración vigente

| Pendiente de aceptación | Evidencia necesaria | Acción existente una vez disponible |
| --- | --- | --- |
| Identificar el acto de las 286 aplicaciones | Confirmación directa de la propietaria; 19 catálogos explícitamente OBSERVACIONAL, preguntas/respuestas y autor original | **Resuelto:** observaciones clínicas por componente; identidad del catálogo preservada |
| Cotejar origen y destino | CSV retenidos + SHA de fila + plan confirmado + referencias de origen en registros + Activitylog | **Conciliadas 648 filas**, incluidos vínculos, residente, autor y fechas; no es necesario generar una segunda fuente o transcripción duplicada |
| Aciertos/tiempos/edición de prueba estandarizada | No hubo una prueba estandarizada declarada en esas entradas | **No aplica a este lote**. La investigación queda preparada para futuras aplicaciones, sin atribuir valores históricos |
| Configurar perfil normativo para las observaciones | No corresponde a su naturaleza declarada | **No aplica**; el lector rechaza esa configuración y las contabiliza por separado |
| Nueva carga de las mismas observaciones | El lote ya está incorporado y la correspondencia actual fue conciliada | **No procede repetirla**; se preservan historia y auditorías sin crear duplicados |
| Aceptación visual | Acceso habilitado al navegador para comprobar recorrido, foco y viewports institucionales | QA visual con datos sintéticos; no reemplazar por build o pruebas Livewire |

### Captura futura o corrección del original

Si se realizan nuevas pruebas estandarizadas, registrar su fecha real, instrumento/código/edición/forma/idioma, aplicador, valores con unidades y condiciones/ayudas/desviaciones del método. El diccionario sirve para esa adquisición; no obliga a reconstruir una prueba que no ocurrió. Si aparece un original que corrige una observación, cotejar su documento/página/entrada con la PK previa y usar la rectificación enlazada. El listado privado de 487 aplicaciones facilita ese trabajo sin convertir el Barthel previo en parte del lote ya conciliado.

Recorrido implementado: captura compartida → transcripción → conciliación → revisión de diferencias → guardar/vincular/rectificar. Endpoints vigentes: `POST /admin/instrumentos/{instrumento}/residentes/{residente}/transcripciones/conciliar` y `POST .../transcripciones`. Medicina/Psicología conservan el permiso aprobado; Superadministración consulta. No se requiere una aprobación profesional previa para seguir desarrollando; el dato original necesario para el cálculo conserva su exigencia de procedencia.

## 7. Verificación y estado de aceptación

El incremento cambia el lector autorizado, la comprobación pura, dos controles de atribución instrumental del puente y sus pruebas. `ComprobacionAdquisicionInstrumental` versión 2 distingue `OBSERVACION_CLINICA` / `NO_APLICA_A_OBSERVACION`; las aplicaciones observacionales 60+ se cuentan aparte y no incrementan `aplicaciones_sin_extraccion`. La ausencia de un perfil sigue siendo un faltante para una fuente instrumental, no para una observación declarada. El tipo se toma del catálogo original explícito, nunca del nombre o del código.

`CapturasInstrumentalesResidente` conserva las respuestas nominales, no agrega `SIN_PERFIL_EXPLICITO` a catálogos observacionales y rechaza asignarles un perfil. Continúan los mismos permisos, Policy, corte temporal y controles de coherencia. No se cambian las reglas clínicas, sus umbrales o ecuaciones ni se convierten observaciones en puntuaciones, A/P o DCL.

### Hallazgo independiente y corrección

La revisión independiente detectó un P2 preexistente directamente relacionado: un vínculo declarativo hacia `VAR-MEM-INST-01` podía marcar una observación como componente instrumental verificado y producir una conclusión candidata de Memoria. Se corrigió en una fase separada de implementación:

- `ConstructorMemoriaInstrumental` no asigna ese indicador a un catálogo OBSERVACIONAL.
- `AdmisibilidadFuenteCOGMEM` lo rechaza también cuando otro adaptador aporte el indicador `true`, con `OBSERVACION_NO_ACREDITA_COMPONENTE_INSTRUMENTAL`.
- `AdmisibilidadObservacionalTest` reprodujo `ADMISIBLE` antes de corregir y pasó después. La nueva regresión SQL conserva el original, no emite la conclusión y mantiene el caso positivo instrumental anterior.

Revisión posterior del mismo revisor: **PASS estático acotado, P2 cerrado**, sin P1/P2 nuevos en el delta. No ejecutó SQL ni pruebas, ni auditó la conciliación privada; esa evidencia corresponde al implementador. No se atribuye una aprobación clínica a la revisión de código.

### Pruebas finales del candidato

| Comprobación | Resultado |
| --- | --- |
| SQLite `:memory:` | **228 pruebas / 6885 aserciones PASS**, 2 min 01 s |
| PostgreSQL desechable, misma selección | **228 pruebas / 6884 aserciones PASS**, 2 min 34 s; parada del servidor confirmada |
| Lector local autorizado del lote, transacción READ ONLY | **286/286 observaciones recuperadas**, sin normalización ni escrituras |
| Fuentes → plan → fila → auditoría → destino | **648/648 filas conciliadas** |
| Independencia del motor y dominio O.R.I.O.N. | **PASS** en ambos scripts |
| Pint de los ocho archivos PHP propios | **PASS** |

Las selecciones finales incluyen captura, permisos, comprobación, límites de edad, perfil, atribución instrumental, red integrada, gobierno experimental y conservación/release. XML/logs: `storage/app/private/fase2-cierre-final-{sqlite,postgres}.junit.xml` y `.log`. Candidato sin `.env`, con código de `c375b5b8` más los ocho archivos propios y storage separado por motor; se preservan cambios ajenos. La suite preliminar de 186 casos precede a la corrección del P2 y no sustituye ni se suma a este resultado final. No se ejecutó la suite global.

Verificación nueva adicional: huellas de aplicaciones, respuestas y documentos estables; integridad de archivos antes/después; conteos y enlaces internos de documentación. Build no aplica al incremento del backend/documentación sin cambios visuales o assets. No se añade legacy, dependencia, permiso o estructura.

Se intentó nuevamente el inventario CUA: cero navegadores y `Local browser use is disabled in Work settings`. La QA visual sigue **NO VERIFICADA**; no se ejecuta una vía alternativa para sortear la restricción.

| Criterio del plan | Dictamen actual |
| --- | --- |
| Investigación, definiciones y adquisición técnica | TERMINADO, con evidencia enlazada |
| Identidad y fuentes del lote observacional | **CONCILIADAS**, 648 filas y 286 aplicaciones/respuestas |
| Edición/aciertos/tiempos y perfil normativo de esas observaciones | **NO APLICA**, según aclaración de la propietaria |
| Nueva carga del mismo lote | **NO PROCEDE**, ya está incorporado y cotejado |
| QA visual completa | NO VERIFICADA POR RESTRICCIÓN DEL NAVEGADOR |
| Adquisición/trazabilidad del lote observacional de fase 2 | **CERRADA Y VERIFICADA** en el alcance descrito |
| Aceptación visual del recorrido incluido en el checklist | **PENDIENTE**; no se presenta como verificación integral de interfaz |

La aclaración de la propietaria resuelve la identidad del acto y evita exigir mediciones de una prueba no administrada. El cierre corresponde a las fuentes observacionales efectivamente aportadas y conciliadas. Las inferencias y la conservación completa permanecen en sus fases; la aceptación visual continúa expresamente pendiente.
