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

La API permite aportar puntuaciones originales por respuesta y metadatos completos de cabecera. El modal conserva respuestas tipadas y procedencia; la captura detallada de puntuaciones originales por respuesta/campos adicionales de cabecera se realiza por ese contrato de conciliación. No se cargaron nuevos registros de residentes en esta entrega.

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
- [ ] Identificar/cotejar los protocolos realmente aplicados y sus mediciones/condiciones originales ausentes.
- [ ] Activar solo perfiles cuyas PK, preguntas y edición/forma/idioma estén acreditados.
- [ ] Ejecutar una nueva carga histórica desde esos originales, con conciliación previa.
- [ ] Comprobar visualmente el recorrido, foco y reflow en los viewports institucionales.

Los cuatro pendientes no se cierran creando datos ni convirtiendo bibliografía en autoría histórica. La trazabilidad exacta de una evaluación experta conservada, incluidos definiciones/selectores/versiones, mantiene el alcance de la fase 6.
