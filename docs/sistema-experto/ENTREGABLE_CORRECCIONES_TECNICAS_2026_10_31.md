---
title: "Entregable único de correcciones técnicas del sistema experto"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-31
owner: ApoyoDecision
source_of_truth: false
verification_scope: EXISTING_AUTHORIZED_FLOW_WITHOUT_RULE_SCHEMA_OR_PERMISSION_CHANGES
runtime_verified: true
---

# Alcance vigente

La propietaria pidió terminar las correcciones y revisar las reglas después. Durante esta entrega respondió **«Mantener relaciones y permisos actuales»** a la propuesta de cinco FK/checks y permiso de ejecución conjunta. Se respeta esa decisión: **sin cambios de reglas, relaciones, permisos, tablas ni historia operativa**.

Este documento reúne el cierre compatible, el fundamento de la fase anterior, las pruebas y el estado pendiente. No declara terminado el guardado conjunto: hacerlo necesitaría la transición expresamente rechazada. No se pide nuevamente esa autorización ni se inventa otro mecanismo de almacenamiento.

## 1. Correcciones implementadas

| Problema comprobado | Corrección | Evidencia de regresión |
|---|---|---|
| La consulta de mediciones atrapaba cualquier HttpException y devolvía ausencia; podía esconder un 503. | Solo el 403 esperado conserva la salida restringida. Los demás estados se propagan al manejo de errores correspondiente. | Casos 403, 404 y 503; no convertir un fallo inesperado en una consulta vacía. |
| El lector directo de capturas permitía una fecha de corte futura, aunque las consultas superiores la rechazaban. | Misma guarda temporal en el punto de adquisición, después de autorizar al actor. | Rechazo de corte futuro y conservación de corte actual/histórico. |
| Un vínculo documental incoherente provocaba comprobación de existencia/hash antes de validar residente, vigencia y fecha. | Comprobar admisibilidad antes de acceder al archivo privado. Un documento ajeno, futuro o inactivo no produce I/O de archivo ni normalización. | Mock del almacenamiento exige cero llamadas a existe/hash en esos tres casos. |
| Medicina/Psicología adquirían dos veces el mismo historial observacional por render. | Un solo bloque de lectura, compartido por los roles con sus controles actuales. | Una consulta de paginación OBS por render; se distingue de la exclusión OBS del lector histórico D137. |
| Un fallo observacional marcaba error instrumental y borraba un análisis instrumental correcto. | Error observacional independiente; se retira únicamente su información fallida y se conserva el estado instrumental ya comprobado. | La pantalla mantiene el análisis correcto, informa el fallo OBS y no muestra detalles internos. |

Archivos responsables: `CapturasInstrumentalesResidente`, `ConsultarPerfilInstrumental` y `ResultadosExpertoResidente`. La lógica de reglas y criterios permanece en el motor existente.

## 2. Integración y UX

- Se mantienen las rutas y pantallas existentes; no se añade un módulo o formulario paralelo.
- La guía médica y el plan de captura de la entrega anterior siguen conectados al resultado autorizado. Distinguen ausencia, contradicción y datos no interpretables; no producen una puntuación o diagnóstico nuevo.
- Los fallos de historia, mediciones y observaciones conservan mensajes y estados separados. Una fuente fallida no aparenta una lectura correcta ni cancela una lectura independiente ya verificada.
- La consulta instrumental/Petersen sigue transitoria. No se cambia `persistido=false`, no se anuncia guardado ni se habilita revisión de una evaluación conjunta inexistente.
- La conservación observacional y su revisión siguen su circuito aprobado; no se usan para simular conservación instrumental.

La fuente médica, la ficha de captura y los límites de MoCA/RUDAS/FAQ están reunidos en el [entregable de adquisición y fuentes profesionales](ENTREGABLE_INTEGRACION_Y_FUENTES_DCL_2026_10_31.md). Sus referencias respaldan conceptos; no se convierten en reglas nuevas durante esta corrección.

## 3. Seguridad, persistencia e historia

La autorización se comprueba antes de la adquisición. Las correcciones no conceden acceso por rol, no cambian responsabilidades y no permiten eludir las Policies. Un documento ajeno enlazado incorrectamente no se lee como archivo ni se presenta como original del residente. Sus metadatos necesarios se comprueban en backend para rechazar el vínculo; no se publican como evidencia propia.

No se ejecutó una migración ni se modificó la BDD operativa. No se crearon tablas, FK, índices, checks, catálogos o permisos. No se introdujeron JSON/EAV, almacenamiento oculto, un historial paralelo o fuentes clínicas de respaldo inventadas. Los cambios locales ajenos a esta tarea se preservan.

La propuesta estructural se registra como **REJECTED** en [la decisión de conservación del resultado](../base-de-datos/PROPUESTA_CONSERVACION_RESULTADO_INSTITUCIONAL_2026_10_31.md). Se conserva para explicar el límite y evitar otra solicitud repetida.

## 4. Verificación y límites de evidencia

Las regresiones se escribieron antes de corregir la implementación. La primera ejecución detectó acceso indebido al almacenamiento, fallos HTTP ocultos, corte futuro admitido y pérdida del estado instrumental ante fallo OBS. El contador de consultas se ajustó para identificar la consulta OBS concreta y no confundirla con la consulta D137 que excluye OBS; no se relajó el resultado esperado de una adquisición.

Verificación final ejecutada el 31/10/2026:

| Comprobación | Resultado |
|---|---|
| SQLite, clases completas de captura instrumental, consulta cognitiva y ciclo observacional conservado | **68 pruebas /1032 aserciones correctas**. |
| PostgreSQL18, los mismos grupos en clúster/base desechables y usuario no privilegiado | **68 pruebas /1031 aserciones correctas**, salida0; clúster temporal detenido y retirado al finalizar. |
| Repetición focalizada final del contador OBS y del aislamiento de errores | **2 pruebas /11 aserciones correctas**. |
| QA del apartado compartido de guía de captura | **1 prueba de navegador correcta**: Blade real sin consultas SQL, teclado y anchos1440/1280/1024/768/390, sin desbordamiento. No se atribuye como auditoría visual de toda la aplicación. |
| Build Vite | Correcto; persiste el aviso previo de bundle mayor de500kB. |
| Sintaxis/diff | Tres archivos de implementación PHP comprobados y diff sin errores de whitespace. |
| Revisión independiente de implementación y seguridad | **PASS acotado**: pertenencia documental, autorización, propagación de errores y separación OBS/instrumental; ninguna nueva frontera de permisos ni cambio de reglas. |

Comandos principales: `php vendor/phpunit/phpunit/phpunit --no-progress --do-not-cache-result tests/Feature/CapturaInstrumentalInstitucionalTest.php tests/Feature/EjecucionConsultaCognitivaTest.php tests/Feature/CicloObservacionalConservadoTest.php`; para integración PostgreSQL, perfil `phpunit.postgres.xml` exclusivamente contra la base técnica desechable; `node --test tests/Frontend/plan-captura-dcl.test.js`; `npm run build`.

Los motores de prueba son desechables; no se usan historias reales. No se ejecutó la suite completa de todos los módulos. Una prueba técnica no acredita desempeño diagnóstico en uso real.

Evidencias locales: `output/experto-correcciones-20261031-*.txt`. No forman parte del commit ni contienen historias reales. Los resultados anteriores mantienen su propio corte y no se atribuyen otra vez a este cierre.

## 5. Estado del experto después del cierre

| Parte | Estado y límite |
|---|---|
| Base de conocimiento, red y reglas | Existentes y versionadas; **no modificadas por esta entrega**. |
| Motor institucional | Funcional técnicamente para los contratos adoptados; puede abstenerse justificadamente con memoria vacía. |
| Captura original y contexto | Implementados con fuentes, autoría y permisos existentes. |
| Guía médica y explicación de faltantes | Implementadas e integradas en la consulta de resultados. |
| Consulta de mediciones y observaciones | Correcciones técnicas de errores, fechas, archivo privado y adquisición duplicada aplicadas en este cierre. |
| Conservación observacional | Existe; las pruebas de integración comprueban que se mantiene. |
| Perfiles iniciales MoCA/RUDAS/FAQ | No se activan por semejanza de nombres ni por estar seleccionados. Siguen requiriendo identidad, material aplicable y vínculo exactos; no se crean catálogos ni normas en esta entrega. |
| Conservación e historial conjuntos | **No implementados bajo las relaciones y permisos actuales.** La propietaria decidió mantenerlos; no se presenta la consulta transitoria como historia conservada. |
| Reconstrucción de estados anteriores a una rectificación | La lectura de originales usa también su estado vigente actual. No se inventa retrospectivamente un estado COMPLETA. La consulta al corte no equivale a recuperar una evaluación histórica guardada. |

## 6. Siguiente etapa solicitada

La siguiente etapa será revisar las reglas con su versión, entradas, condiciones, consecuencias y ejemplos esperados. Este cierre no anticipa cambios de umbrales, protocolos, pesos o criterios para resolver pendientes de integración. La conservación conjunta permanece una decisión estructural distinta; corregir reglas no la habilita por sí mismo.

Durante todo el desarrollo no se solicitan estudios con residentes, firmas, referencias humanas ni avales externos. No se contactó a terceros ni se envió correo. Se conserva esta entrega en un commit local de alcance propio; no se realiza push automático.
