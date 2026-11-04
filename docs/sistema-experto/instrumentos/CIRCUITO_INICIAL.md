---
title: "Fichas y preparación del circuito instrumental inicial"
status: CURRENT
version: "1.4"
last_reviewed: 2026-10-29
owner: ApoyoDecision
source_of_truth: false
verification_scope: PRIMARY_SOURCE_RESEARCH_AND_STATIC_CAPTURE_MAPPING
runtime_verified: false
---

# Circuito inicial: entrevista + MoCA o RUDAS + FAQ

**Actualización vigente del04/11/2026:** [tres perfiles de captura y puntuación activados localmente](../ENTREGA_ACTIVACION_PERFILES_INICIALES_2026_11_04.md), con identidad exacta, fuentes cotejadas y pruebas. La propietaria declaró disponer de materiales/permisos y autorizó la digitalización. Los apartados siguientes conservan el estado del28–29/10; sus pendientes de adquisición y perfiles no describen la activación posterior. La puntuación no acredita cinco dominios ni C02/C03 y la conservación conjunta sigue separada.

**Entrega integrada del 29/10:** [instrumentos, versiones y perfiles de preparación](../ENTREGA_INSTRUMENTOS_Y_PERFILES_INICIALES_2026_10_29.md) reúne el contrato técnico, fuentes actualizadas, mapa hacia hechos e inferencia y criterios de aceptación. La consulta de preparación está integrada al inventario existente; no activa estos instrumentos. El [contexto de captura](../ENTREGA_FASE_CONTEXTO_COGNITIVO_2026_10_29.md) ya conserva escolaridad, idioma e informante principal por atención. Las referencias a brechas de contexto de los apartados siguientes describen el corte anterior o la aplicabilidad instrumental aún pendiente, no ausencia de esa captura ya instalada.

La [selección por familias y niveles está aprobada](SELECCION_ADOPTADA_2026_10_28.md). Esta entrega concreta fichas documentales para su preparación: no declara instrumentos activados, materiales adquiridos o interpretaciones clínicas habilitadas.

## 1. Fichas y estado real

| Familia | Ficha | Identidad localizada y límite | Estado de incorporación |
|---|---|---|---|
| I01 | [MoCA completo](FICHA_MOCA.md) | Referencia pública 8.1 Spain/Spanish; falta cotejar paquete oficial y adaptación aplicable. | Material, alcance de autorización digital, normas y perfil pendientes. |
| I02 | [RUDAS](FICHA_RUDAS.md) | RUDAS-PE del artículo de 2020 y su suplemento; no equiparar con el formulario inglés. | Cotejo del suplemento/material final, condiciones de reutilización, aplicabilidad y perfil pendientes. |
| I04 | [FAQ/Pfeffer](FICHA_FAQ.md) | Distinguidos FAQ original de 10 ítems, traducción UDS y variantes de 11; no elegida aún una edición española ejecutable. | Material exacto y derechos, reglas de ausencia/puntuación, aplicabilidad y conexión IADL pendientes. |

**Cerrado documentalmente:** finalidad, posición, fuentes localizadas, diferencias de versión, límites de interpretación y mapa técnico. **No cerrado para operación:** las condiciones de incorporación que figuran arriba. No hay nuevas reglas, baremos, formularios o datos clínicos por el hecho de completar estas fichas.

## 2. Contexto y captura que necesita el recorrido

Se conservan las necesidades de la [matriz D01–D27](../MATRIZ_DATOS_DETECCION_DCL_2026_10_27.md). La siguiente tabla es un contrato de información y procedencia por resolver en la captura, no una declaración de que todos esos campos existen ni una ampliación de BDD:

| Grupo | Información que debe poder identificarse | Fuente actual / límite |
|---|---|---|
| Caso | Residente, fecha clínica, profesional original y cuenta registradora. | Aplicación y contexto autorizado existentes; no atribuir el acto a otro profesional por input. |
| Identidad del instrumento | Nombre completo, edición/forma, adaptación/idioma y material exacto utilizado. | `instrumentos` tiene código, nombre, versión y descripción; no contiene columnas independientes para todos esos atributos. El perfil debe cotejar identidad, no deducirla del nombre. |
| Aplicabilidad | Escolaridad, alfabetización, idioma de evaluación, visión/audición/motricidad y condiciones relevantes. | La matriz conserva brechas. `AplicarInstrumento` no recibe escolaridad/idioma como campos estructurados propios. No esconder datos estructurales en JSON/EAV o texto genérico para simular cobertura. |
| Informante | Fuente, conocimiento del nivel previo y oportunidad de observar cambios. | Verificar la identidad y procedencia por los registros vigentes; no inventar que el parentesco acredita conocimiento suficiente. AD8 no se convierte en requisito. |
| Original | Respuestas/resultados y ausencias según el protocolo elegido; fecha, autor, documento y rectificación cuando corresponda. | `AplicacionInstrumento` y `RespuestaInstrumento`; la captura genérica no acredita que un formulario estandarizado completo ya esté disponible. |
| Historia funcional | Actividad, oportunidad real, desempeño, ayuda, causa y nivel previo. | `ControlFuncionalIadl` es descriptivo; no sustituye los ítems de FAQ ni genera C03 automáticamente. |

El original se guarda por su flujo autorizado; el experto solo adquiere evidencia con identidad y contrato válidos. Un cribado sin hallazgos no cierra una preocupación fundada y un dato ausente no se vuelve normal.

**Avance posterior del 28/10/2026:** la [referencia explícita de atención en la aplicación](../ENLACE_ATENCION_APLICACION_2026_10_28.md) conecta la FK existente en aplicación propia y transcripción, con metadatos autorizados. El contexto escalar permanece en la atención; no se extraen valores para métodos ni se cierra la incorporación de los instrumentos por este enlace.

## 3. Integración que ya existe y brechas delimitadas

- [PrepararCapturaInstrumental](../../../app/Modulos/Valoraciones/Aplicacion/Consultas/PrepararCapturaInstrumental.php) comprueba contexto, catálogo y vinculación exacta. No se cambian estos controles.
- [AplicarInstrumento](../../../app/Modulos/Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php) conserva respuestas y calcula/contrasta puntuaciones según el catálogo. Ese cálculo genérico debe comprobarse contra cada protocolo antes de usar correcciones, omisiones o respuestas no aplicables.
- [RegistroMetodosInstrumentales](../../../motor-experto/src/motor_experto/dominio/instrumentos/RegistroMetodosInstrumentales.php) no declara un método propio MoCA, RUDAS o FAQ en los métodos revisados. No reutilizar una normalización de otro test para obtener un vínculo artificial.
- [La configuración base](../../../config/sistema_experto.php) mantiene perfiles instrumentales e interpretativos vacíos. Este documento no certifica la configuración efectiva del entorno ni cambia su contenido.
- **Brecha de integración FAQ–IADL:** el [modelo IADL](../../../app/Models/ControlFuncionalIadl.php) tiene ocho actividades descriptivas y el [contrato de soportes](../../../app/Modulos/ApoyoDecision/Aplicacion/Servicios/ContratoFuentesPetersen.php) admite esa fuente para C03. No existe equivalencia ítem-a-actividad ni se acredita aquí un recorrido completo de escritura y procedencia de FAQ. Conservar por separado la aplicación original y el juicio profesional; documentar el diseño pendiente antes de automatizar vínculos. No añadir FK, catálogos, tablas o competencias desde una ficha.

Se consultaron código y migraciones existentes; no se ejecutó una migración ni se verificaron nuevas escrituras en una BDD operativa. El [contrato de entradas previo](../organizacion/REGISTROS_ORIGINALES_Y_VALIDACION.md) mantiene su evidencia histórica de estructura.

## 4. Próxima condición concreta de cierre

Reunir los paquetes oficiales que realmente usará la residencia y sus condiciones de uso. Cotejar edición/adaptación, instrucciones y alcance de reproducción, digitalización, cálculo y almacenamiento. Después fijar el contrato de captura y puntuación de esa edición y verificar sus conexiones; no completar perfiles con identificadores ficticios.

La propietaria confirmó el 28/10/2026 que **aún hay que obtener los formularios y autorizaciones**. Se preparó la [gestión de obtención y tres solicitudes sin enviar](OBTENCION_MATERIALES_Y_PERMISOS.md), con rutas verificadas y alcance del uso previsto.

No se han enviado solicitudes a titulares, aceptado acuerdos, creado cuentas externas ni adquirido servicios. Tener estos documentos o una autorización institucional no equivale a una licencia de terceros. La aprobación ya registrada no se vuelve a solicitar para la misma selección.

## 5. Verificación de la entrega

Fichas enlazadas, selección I01/I02/I04 coherente, fuentes primarias citadas y pendientes identificados sin copiar reactivos o baremos. Cambio documental: sin migraciones, consultas de historias, cambios de permisos, configuración, motor o UI. Build y pruebas de aplicación no acreditan el cierre de una licencia o la validez clínica y no se ejecutan para este cambio.


## 6. Formularios propios de captura (entrega posterior del 28/10)

Tras confirmar que los materiales aún deben obtenerse, la propietaria autorizó **formularios propios de captura**. Se reorganizaron el registro de aplicaciones y la entrevista/atención psicológica existentes, con guía médica opcional y revisión de originales. El [informe de implementación y verificación](../../frontend/FORMULARIOS_CAPTURA_COGNITIVA_2026_10_28.md) delimita sus consumidores, pruebas y límites. Esta entrega posterior no activa las ediciones de MoCA/RUDAS/FAQ ni cambia las condiciones de incorporación descritas arriba. Las secciones 1–5 conservan el alcance documental de su entrega original.


## 7. Obtención documental posterior: originales y restricción NACC

Se obtuvieron **seis documentos públicos** de consulta con [identidad, SHA-256 y páginas](METADATOS_MATERIALES_2026_10_28.json). El [cotejo](COTEJO_MATERIALES_2026_10_28.md) concreta cuáles se recuperaron y cuáles no: originales ingleses RUDAS y FAQ español histórico B7S disponibles; paquete oficial español MoCA y suplemento RUDAS-PE todavía pendientes. El formulario de permisos NACC excluye uso clínico diagnóstico y limita su trámite a investigación; no se presupone una autorización asistencial. Tres consultas `.eml` concretas están preparadas localmente, sin enviar. Este avance no adopta ediciones, puntuaciones, licencias ni perfiles ejecutables; las secciones anteriores conservan sus respectivos cortes.


## 8. Continuidad acordada sin correos externos

La propietaria rechazó contactar a terceros y confirmó continuar con **fuentes públicas y formularios propios de captura**. Los borradores de consultas quedan suspendidos; no se envió ninguno. La [decisión vigente](COTEJO_MATERIALES_2026_10_28.md#decisión-posterior-de-la-propietaria-sin-contacto-externo) prevalece sobre las propuestas anteriores de gestión. Se mantienen la entrevista/atención psicológica y la captura compartida de evaluación implementadas; sus guías de contexto no se presentan como instrumentos validados. Las ediciones y derechos pendientes siguen sin activarse, sin impedir desarrollar la captura propia ya autorizada.
