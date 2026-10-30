---
title: "Selección instrumental escalonada adoptada para detección temprana de DCL"
status: APPROVED
version: "1.1"
last_reviewed: 2026-10-28
owner: ApoyoDecision
source_of_truth: true
source_of_truth_scope: [instrument_family_selection, assessment_levels, initial_integration_priority]
verification_scope: OWNER_APPROVAL_AND_STATIC_INTEGRATION_REVIEW_NOT_EVALUACION_POSTERIOR
runtime_verified: false
supersedes: [SELECCION_PROPUESTA_2026_10_28.md]
---

# Selección instrumental adoptada

[Portal](../README.md) · [Propuesta y fuentes consultadas](SELECCION_PROPUESTA_2026_10_28.md) · [Catálogo de investigación](CATALOGO_CANDIDATOS.md).

## 1. Decisión, fecha y alcance

El **28/10/2026**, la propietaria respondió **«apruebo»** a la recomendación de organizar los instrumentos por niveles y priorizar el circuito **entrevista e informante + MoCA o RUDAS + FAQ/Pfeffer**. Se registra esa decisión con los ajustes de la respuesta inmediatamente anterior: AD8 es complemento; las pruebas ampliadas se seleccionan según el caso; un cribado sin hallazgos no cierra automáticamente una preocupación fundada.

Esta aprobación cierra la elección de las familias y su prioridad de trabajo. No certifica validez clínica de la combinación para la residencia, concede derechos de terceros, fija ediciones/baremos/puntos de corte ni cambia competencias o permisos. No autoriza un diagnóstico autónomo. Las condiciones de cada instrumento se cerrarán antes de su activación correspondiente.

## 2. Niveles aprobados

| Nivel | Selección | Función y alcance |
|---|---|---|
| **Inicial prioritario** | Entrevista clínica e información del informante; **MoCA o RUDAS**; **FAQ/Pfeffer**. | Conocer cambio y nivel previo, explorar rendimiento y autonomía instrumental. Elegir el cribado aplicable a la persona; no exigir ambos ni sustituir entrevista por una puntuación. |
| **Apoyo según el caso** | **GDS-15 de Yesavage**, **4AT**, **Barthel existente**. | Síntomas depresivos, posible delirium y contexto de autonomía básica, respectivamente. Su selección no impone los tres a cada evaluación. Barthel mantiene su uso institucional vigente. |
| **Complemento del informante** | **AD8**. | Puede estructurar parte del cambio observado si es aplicable y su uso está autorizado. No es requisito para empezar ni sustituye la entrevista del informante. |
| **Ampliado por profesional competente** | **HVLT-R**, **TMT-A/B**, **fluidez verbal semántica y fonémica**, **Boston Naming Test**, **Figura Compleja de Rey–Osterrieth**. | Repertorio para profundizar cuando el cribado sea insuficiente, existan dudas o se necesite caracterizar dominios. No aplicar todo el conjunto por defecto. |

Son **12 familias seleccionadas**. La entrevista y la historia del informante son fuentes clínicas, no una decimotercera prueba estandarizada. El nivel inicial incluye tres familias instrumentales, con MoCA/RUDAS como alternativas. No se establece un número obligatorio de pruebas ni un tiempo universal de consulta.

Los demás candidatos del catálogo quedan como reserva. Una sustitución futura debe identificar instrumento, finalidad y condiciones; no se activa un equivalente por parecido de nombre ni se intercambian puntuaciones o normas.

## 3. Alcance clínico que se conserva

- Un cribado aislado no confirma DCL ni su subtipo. La evaluación integra historia, funcionamiento, contexto y revisión profesional.
- Si persiste una preocupación fundada, un cribado sin hallazgos no cierra automáticamente el caso; corresponde considerar evaluación adicional según el contexto profesional.
- La falta de información del informante, de oportunidad para realizar una actividad o de una medición interpretable debe permanecer visible. No se rellena con normalidad ni se resuelve exigiendo AD8.
- El entorno residencial puede limitar las actividades instrumentales. No realizar una tarea porque el centro la asume no demuestra incapacidad cognitiva.
- GDS-15 y 4AT son apoyos con finalidades propias. Sus resultados no completan automáticamente la revisión diferencial ni los criterios de DCL.
- La lista no crea frecuencia de seguimiento, reglas de derivación automáticas ni una batería universal de estudios de laboratorio, neuroimagen o biomarcadores.

## 4. Preparación técnica del circuito inicial

Revisión estática del código existente, sin consultar historias ni modificar el catálogo:

| Parte | Capacidad localizada | Condición pendiente para integrar |
|---|---|---|
| Instrumento, aplicación y respuestas | [Instrumento](../../../app/Models/Instrumento.php), [AplicacionInstrumento](../../../app/Models/AplicacionInstrumento.php) y [PrepararCapturaInstrumental](../../../app/Modulos/Valoraciones/Aplicacion/Consultas/PrepararCapturaInstrumental.php). La captura comprueba contexto autorizado e identidad del catálogo. | Identificar la edición/forma/adaptación/idioma de MoCA, RUDAS y FAQ, las entradas autorizadas y los derechos del uso previsto. No crear preguntas o instrumentos de sustitución. |
| Extracción e interpretación | La preparación de captura exige vínculo explícito a instrumento y versión; [la configuración base](../../../config/sistema_experto.php) conserva `perfiles_instrumentales` y `perfiles_interpretativos` vacíos. | Definir perfiles únicamente desde identidad, mediciones y métodos cerrados. Esta revisión no acredita la configuración efectiva de un despliegue ni habilita un perfil por aprobar su familia. |
| Entrevista y cambio corroborado | [ContratoFuentesPetersen](../../../app/Modulos/ApoyoDecision/Aplicacion/Servicios/ContratoFuentesPetersen.php) admite control cognitivo, valoración psicológica y documento clínico para C01. | Determinar la captura original y la procedencia disponibles conforme al [contrato de entradas](../organizacion/REGISTROS_ORIGINALES_Y_VALIDACION.md). No añadir AD8 o una aplicación como soporte directo de C01 por este documento. |
| Función instrumental | [ControlFuncionalIadl](../../../app/Models/ControlFuncionalIadl.php) y el contrato de C03 mantienen la fuente descriptiva IADL existente. | Verificar el recorrido de escritura y la procedencia de FAQ; la aprobación no demuestra que esa conexión esté implementada. Una aplicación de FAQ no se convierte automáticamente en un control IADL o un juicio C03. |

La [matriz D01–D27](../MATRIZ_DATOS_DETECCION_DCL_2026_10_27.md) conserva las brechas de escolaridad, idioma, informante y condiciones, entre otras. La selección aprobada no acredita que esos datos ya estén capturados. Se trabaja sin residentes reales hasta que ingresen por el proceso institucional, sin inventar mediciones ni poblar el producto con ejemplos clínicos.

## 5. Qué falta cerrar para cada ficha

Para **MoCA, RUDAS y FAQ primero**: identidad exacta; población y condiciones de aplicación; materiales autorizados; protocolo de puntuación; normas aplicables y sus límites; capacitación/competencia; permisos de uso y digitalización; fuente original y correcciones; contrato de conexión al experto y verificación técnica. Se conserva la [ficha de cierre de la propuesta](SELECCION_PROPUESTA_2026_10_28.md#5-condiciones-para-cerrar-y-activar-cada-instrumento).

Primera concreción documental: [fichas del circuito inicial](CIRCUITO_INICIAL.md), con referencias de versión, distinción entre FAQ de diez/once ítems y FAS, condiciones de uso y brechas de captura. No constituyen nuevas ediciones aprobadas, licencias obtenidas o perfiles ejecutables.

La evidencia bibliográfica y las restricciones de uso están reunidas en la propuesta. MoCA y AD8 requieren resolver el alcance concreto de integración digital; no se supone licencia concedida. No se adoptan de forma automática puntos de corte de otro país ni se presenta la aprobación de la propietaria como validación profesional independiente.

La preparación técnica reutiliza tablas, modelos, autorización y trazabilidad existentes. No se crean tablas ni se cambian relaciones mediante esta decisión. El guardado y recuperación completos de la evaluación conjunta instrumental/Petersen conservan su pendiente independiente.

## 6. Verificación de esta entrega

Se verifican la correspondencia con la aprobación, los niveles, las doce familias, las referencias locales y la navegación documental. Esta entrega registra la decisión y los pendientes de integración; no modifica configuración, formularios, motor, permisos, BDD o historia. Las pruebas de aplicación y build no aplican a este cambio documental; no se presentan resultados anteriores como pruebas de esta aprobación.
