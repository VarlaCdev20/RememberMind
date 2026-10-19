# Fase 2 — Adquisición y procedencia de fuentes originales

Fecha: 19/10/2026. Alcance: captura propia, transcripción cotejada, conciliación y rectificación de aplicaciones instrumentales. La vía técnica está implementada y comprobada en SQLite/PostgreSQL. El cotejo de instrumentos históricos y las mediciones originales ausentes siguen pendientes; no se declara cerrada toda la fase por disponer de software.

## Decisión y ejecución local

La propietaria aprobó expresamente «Sí, aprobar estructura y permiso propuestos», sobre la [propuesta de cinco columnas y un permiso](PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md). Se aplicó exclusivamente `2026_10_19_010000_add_procedencia_aplicaciones_instrumento` en `remembermind_dev`, entorno `local`, PostgreSQL en loopback, después de verificar un respaldo privado de formato custom y su índice de contenido. No se ejecutaron scripts SQL del paquete ni se reinició la base.

El seeder acotado `PermisoTranscripcionInstrumentalSeeder` concedió `aplicaciones_instrumento.transcribir` a Medicina y Psicología. Superadministración no recibió esa mutación. No se ejecutó una sincronización general de roles. El permiso permite metadatos del original del residente; descargar documentos sigue sujeto a su autorización separada.

Antes y después coincidieron cantidad y SHA-256 del contenido previo de 487 aplicaciones, 2296 respuestas y siete documentos. Las cinco columnas nuevas permanecieron NULL en las filas anteriores: no se inventaron transcriptores, instantes técnicos, documentos ni antecesoras históricos. Se mantuvieron 122 tablas físicas, incluidas las técnicas; el inventario aprobado continúa siendo de 106 tablas de negocio, sin tablas nuevas en esta fase.

## Qué cambió

- El formulario compartido de Valoraciones distingue aplicación propia de transcripción. Solicita documento, aplicador original, fecha/hora, localizador y cotejo; incluye vínculo a una aplicación previa o motivo de rectificación cuando corresponda. La persona autenticada que registra queda separada del aplicador histórico.
- `AplicarInstrumento` comparte validación entre captura, conciliación y transcripción. Una respuesta conserva un único tipo original; NULL, cero y «No» mantienen significados distintos. En transcripción, una puntuación no aportada no se rellena desde la opción actual; una incompatible exige conciliar el catálogo.
- Documento y localizador identifican la entrada original. El reintento idéntico devuelve la aplicación existente; un cambio de contenido requiere indicar la cabeza anterior y el motivo. La corrección crea otra aplicación/respuestas y anula la anterior para nuevas lecturas, conservando contenido, autor y fecha anteriores.
- Una aplicación previa sin fuente puede vincularse después de cotejar exactamente cabecera, puntajes y respuestas. Solo se añaden documento/localizador y auditoría de la vinculación. No se inventa quién digitó antes. Un estado `VIGENTE` se conserva; vincularlo no lo convierte en `COMPLETA` ni habilita su cálculo.
- El backend vuelve a comprobar cuenta, permiso, competencia, contexto del residente, catálogo, autor original y archivo/hash. Conciliar no crea aplicaciones, respuestas ni códigos. El guardado y Activitylog comparten transacción; un fallo revierte todos sus efectos.
- Los Models impiden cambiar el contenido original por edición ordinaria. La migración protege FK restrictivas, parejas de metadatos, identidad de primera fuente y única sucesora. El rollback rechaza retirar columnas que ya contengan procedencia.
- Se retiraron `GestionarEvaluaciones`, `AnularEvaluacionGeriatricaHttp` y `AdultoMayorEvaluacionController`. Sus URLs existentes delegan ahora a `EvaluacionInstrumentalController` y las Actions comunes. Se eliminó la interpretación automática de DCL/severidad mediante el nombre MOCA/MMSE y un total manual; tampoco se combina una fecha antigua con la hora actual.
- El extractor puro exige identidad instrumental explícita en el perfil y conserva la aplicación original de cada respuesta. No rellena edición/forma/idioma desde la referencia bibliográfica. Los selectores sin respuesta conservan su definición y permanecen sin dato.

## Recorrido y API

Con una cuenta autorizada, entrar al historial instrumental del residente (`/admin/salud-seguimiento/{cod_residente}/evaluaciones-geriatricas`) y abrir la captura compartida. Medicina/Psicología pueden elegir «Transcripción de un documento original», completar los datos cotejados y revisar antes de guardar. El expediente de resultados muestra los metadatos disponibles de la fuente y distingue los que faltan en una aplicación anterior.

Las herramientas de importación pueden usar el mismo contrato, sin llamar SQL directo:

```text
POST /admin/instrumentos/{instrumento}/residentes/{residente}/transcripciones/conciliar
POST /admin/instrumentos/{instrumento}/residentes/{residente}/transcripciones
```

La API y el modal permiten aportar puntuaciones y observaciones originales por respuesta, así como atención, clasificación e interpretación consignadas en el original. El modal muestra estos campos adicionales solo en transcripción, conserva NULL/cero y exige revisar de nuevo si cambian después de confirmar. Una puntuación incompatible con el catálogo no se sustituye: se informa el conflicto sin guardar. La clasificación transcrita se identifica como texto del original, no como una conclusión nueva de O.R.I.O.N. No se cargaron nuevos registros de residentes en esta entrega.

## Investigación y límites del dato

El [informe de fuentes originales](ORION_FUENTES_ORIGINALES_E_INVESTIGACION_2026_10_19.md) documenta siete métodos normativos con publicaciones primarias, entradas, unidades, condiciones y material que debe identificarse. Distingue identidad del método investigado e identidad realmente acreditada para una aplicación. HL7 R4 sirve como referencia de procedencia; no añade infraestructura ni obligaciones legales.

El catálogo local tiene 29 preguntas de opción y carece de los ensayos/tiempos/condiciones tipados de esos métodos. No hay una vinculación comprobada que permita deducirlos desde categorías nominales. `perfiles_instrumentales` sigue vacío: habilitar referencias por parecido de nombres atribuiría un instrumento no acreditado. El trabajo de interpretación adicional de fase 3 mantiene su expediente separado.

## Matriz de aceptación y evidencia

| Requisito | Implementación principal | Prueba/evidencia |
| --- | --- | --- |
| Autor original separado del transcriptor; fecha clínica explícita | `Valoraciones/Aplicacion/Acciones/AplicarInstrumento`, `PrepararCapturaInstrumental`, modal compartido | `CapturaInstrumentalInstitucionalTest`, `CapturaInstrumentalFormularioTest` |
| Fuente propia, archivo/hash, cotejo y autorización contextual | Mismas Action/consulta, contrato compartido de archivos | Negativos de documento ajeno, autor incompatible, archivo ausente/alterado, permiso revocado; metadatos permitidos con descarga denegada |
| Reintento, conflicto y corrección sin sobrescritura | `AplicarInstrumento::conciliacion`, Models, migración | Vinculación exacta, VIGENTE sin promoción, NULL/0, puntajes distintos, conservación del original, rollback de auditoría |
| Dos transcriptores no crean dos fuentes/sucesoras | Locks de residente/documento/cadena, índices UNIQUE | `TranscripcionInstrumentalConcurrenciaPostgresTest`: dos procesos y conexiones reales, primera fuente y rectificación |
| Captura única y rutas antiguas seguras | `EvaluacionInstrumentalController`, `AnularAplicacionInstrumento`, registro de rutas | POST antiguo sin respuestas rechazado; captura válida, anulación conservadora y restauración arbitraria 409 |
| Migración y reversión sin pérdida | Migración específica de procedencia | Down/up con respuestas previas; rollback bloqueado cuando hay metadatos; FK y triggers conservados en SQLite, integración PostgreSQL |
| Identidad no inferida y respuesta de su aplicación | `motor-experto/.../ExtractorCapturaInstrumental`, `CapturasInstrumentalesResidente` | `NormalizacionBateriaBolivia2015Test`, pregunta sin respuesta e identidad ausente |
| Integración arquitectónica y seguridad transversal | Consultas/Actions en propietarios existentes | `FolderOrganizationTest`, `ApplicationBoundaryTest`, `PresentationBoundaryTest`, `SeguridadTransversalClinicaTest` |

Pruebas sobre una exportación del índice de Git con dependencias instaladas desde sus locks, sin `.env` ni historias operativas:

- SQLite: **858 pruebas, 11963 aserciones, PASS**. Selección: captura institucional/formulario, normalización Bolivia, seguridad transversal, límites arquitectónicos, interpretación fase 3, preparación/perfil instrumental y `SistemaExperto`.
- PostgreSQL: **69 pruebas, 627 aserciones, PASS**. Selección: ambas suites de captura, seguridad transversal y concurrencia de transcripciones. Cluster creado con base/runner desechables en loopback, storage aislado; parada del servidor confirmada al terminar.
- Integración adicional con el cálculo WAB incorporado por la tarea de fase 3: **160 pruebas, 1535 aserciones, PASS** en SQLite (captura, normalización, métodos y perfil). Esta selección se solapa con la regresión anterior; no se suman como pruebas distintas. Ambos verificadores del motor independiente de Laravel: **PASS**.
- `npm ci` y `npm run build`: **PASS** en la copia aislada. Pint de los 22 PHP añadidos/modificados: **PASS**. La revisión independiente de seguridad/persistencia no conserva hallazgos P1/P2 en el alcance.
- La suite completa del repositorio no se ejecutó en esta entrega. Estos resultados no acreditan desempeño clínico ni una aprobación profesional de reglas.
- QA visual/responsive: **no verificada**. `cua.getState()` devolvió `Local browser use is disabled in Work settings`; no se sustituyó esa comprobación por el build o pruebas Livewire.

La evidencia detallada y el respaldo están en almacenamiento privado local, excluidos de Git. Los tests usan datos sintéticos y no contienen reactivos protegidos ni historias reales. El commit incluye únicamente el alcance de fase 2, preservando los cambios ajenos simultáneos.

## Checklist

- [x] Investigación primaria y diccionario de adquisición documentados.
- [x] Ampliación y permiso aprobados, probados e instalados en desarrollo.
- [x] Autoría original, fecha clínica, digitación y fuente separados.
- [x] Conciliación, reintento, vinculación y rectificación sin duplicados ni pérdida de historia.
- [x] Controles backend, auditoría atómica y pruebas SQLite/PostgreSQL.
- [x] Formularios y consultas conectados; build verificado.
- [x] Puntuaciones/notas por respuesta y cabecera original disponibles desde el modal, con conciliación previa y protección de cambios.
- [ ] Identificar/cotejar los protocolos realmente aplicados y sus mediciones/condiciones originales ausentes.
- [ ] Activar solo perfiles cuyas PK, preguntas y edición/forma/idioma estén acreditados.
- [ ] Ejecutar una nueva carga histórica desde esos originales, con conciliación previa.
- [ ] Comprobar visualmente el recorrido, foco y reflow en los viewports institucionales.

Los cuatro pendientes no se cierran creando datos ni convirtiendo bibliografía en autoría histórica. La trazabilidad exacta de una evaluación experta conservada, incluidos definiciones/selectores/versiones, mantiene el alcance de la fase 6.

## Seguimiento de los pendientes del 19/10/2026

Se completó la parte de captura que antes solo estaba disponible por API. Se verificaron puntuación cero, puntuación ausente, incompatibilidad con la opción, nota original, cabecera, confirmación invalidada, datos ocultos excluidos de captura propia y dirty state. La revisión independiente detectó un bloqueo al elegir transcripción antes del primer residente: corregido y cubierto por regresión, conservando la protección al cambiar entre residentes.

Evidencia del incremento, sobre el candidato aislado y sin datos operativos: **78 pruebas / 1845 aserciones SQLite PASS** (captura, transcripción, seguridad y límites de capas); **73 pruebas / 653 aserciones PostgreSQL PASS** (captura, transcripción y seguridad). PostgreSQL se creó con runner/base desechables y su parada fue confirmada. `npm run build`, Pint y revisión independiente del cierre: **PASS**. No se repitió la suite global ni se modificaron tablas, permisos o registros clínicos en este incremento.

La comprobación adicional de fuentes fue de solo lectura. Los siete archivos conservados siguen íntegros. El catálogo contiene Barthel y diecinueve registros nominales de componentes; las respuestas nominales no aportan los ensayos/aciertos/tiempos requeridos por los métodos cognitivos investigados. Las versiones y condiciones genéricas del paquete no identifican una edición/forma realmente administrada. Las menciones textuales a pruebas se conservan como contenido de origen, junto a la declaración de la propietaria; no se usan para negar por sí solas que las observaciones sean reales. No se estableció una equivalencia instrumental no documentada ni se ejecutó una nueva carga.

**Dictamen visual: FAIL por cobertura incompleta, sin defecto visual observado.** Después de la indicación de que Work estaba habilitado, se reinició la sesión CUA y se volvió a consultar su inventario. Continuó devolviendo `Local browser use is disabled in Work settings`, con cero navegadores disponibles. No se empleó otra vía para sortear esa restricción.

| Criterio visual/funcional | Cobertura real | Estado |
| --- | --- | --- |
| Render del formulario, metadatos, validaciones y persistencia | Pruebas Livewire y backend con registros sintéticos | Verificado por pruebas; no acredita CSS ni interacción física |
| 1440, 1280, 1024, 768 y 390 CSS px; tema y reflow | Sin superficie de navegador accesible | Pendiente |
| Teclado, foco/trap/retorno, Escape y backdrop | Sin interacción real accesible | Pendiente |
| Contraste, targets táctiles, textos visibles y estados de carga | Sin capturas actuales | Pendiente |
| Revisión de identidad aplicada y mediciones históricas | Fuentes disponibles no identifican los protocolos requeridos | Pendiente de originales, no de autorización profesional |

Este seguimiento cierra la limitación técnica de captura detallada. No cambia a completas las casillas de fuentes, perfiles, nueva carga ni QA visual sin sus evidencias correspondientes.

## Comprobación automática y diccionario completo

El [incremento de adquisición del 19/10/2026](FASE_2_COMPROBACION_ADQUISICION_2026_10_19.md) añade comprobación por aplicación de identidad, selectores, respuestas, procedencia y causas del cálculo, con completitud de entradas para 60+ y denominadores explícitos. Se entrega el [diccionario exacto de los 23 métodos y 19 componentes](DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md). Verificación: 182 pruebas tanto en SQLite como en PostgreSQL desechable, sin nuevas escrituras clínicas ni cambios de estructura/permisos. El cotejo agregado de los cuatro CSV confirma que sus 286 respuestas son nominales y no contienen las mediciones numéricas originales requeridas. Los pendientes de fuentes, vinculación real y QA anteriores permanecen abiertos.
