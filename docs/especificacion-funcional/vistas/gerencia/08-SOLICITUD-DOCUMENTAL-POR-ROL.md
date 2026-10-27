---
title: "Gerencia: solicitud de documentación por rol laboral"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISION_AND_FUNCTIONAL_FORM_SPECIFICATION
runtime_verified: false
---

# Solicitar documentación por rol

## 1. Decisión y alcance

La propietaria eligió R01A y añadió: «QUE HAYA UN CAMPO PARA AGREGAR ALGUN TIPO DE PEDIDA DE DOCUMENTACION POR ROL». [Respuesta literal y R02/R03](07-DECISIONES-RESIDUALES-PERSONAL.md#respuestas-adoptadas-el-25102026).

Se mantiene el mínimo inmediato del alta. Los demás respaldos son opcionales por defecto; Gerencia puede solicitar documentación adicional según el rol laboral y destinatarios de su alcance. La petición no modifica automáticamente el mínimo de todas las altas futuras ni convierte el catálogo observado en una lista de obligaciones generales.

El nombre visible del control principal es **«Documento que se solicita»** y la acción **«Solicitar documentación»**. Es una especificación funcional de formulario, no una columna añadida. Un nombre libre de petición no crea un tipo de documento persistente, un permiso o una regla clínica nueva.

## 2. Ubicación y recorrido

Entrada prevista: **Personal → Documentación → Solicitar documentación**, o acceso contextual desde la ficha de un trabajador. No se crea otro módulo. El componente/ruta definitivos se localizarán en Identidad reutilizando el proceso documental existente; aún no están instalados.

Elegir rol → indicar documento e instrucciones → resolver personas destinatarias → revisar fecha límite y alcance → confirmar → conservar solicitud y destinatarios reales → mostrar seguimiento → recibir archivos → Gerencia revisa cada entrega → conservar historial.

El trabajador recibe acceso solo a sus solicitudes propias desde su perfil/documentación y a su estado de entrega. No puede modificar destinatarios, fechas o requisitos mediante el navegador. Los avisos permanecen internos; no se autorizan correo o servicios externos por esta decisión.

## 3. Campos y validaciones previstas

Esta distribución es propuesta técnica para cumplir la solicitud aprobada. La obligatoriedad del contenido de una petición no es una nueva lista de requisitos institucionales.

| ID | Campo visible | Interacción y validación | Persistencia / límite |
|---|---|---|---|
| SD01 | Rol destinatario | Selector de Administración o una de las seis profesiones. No Familiar, Gerencia o Superadministración en este flujo laboral | Usar roles existentes; no crear catálogo paralelo |
| SD02 | Documento que se solicita | Campo requerido con nombre claro; permitir seleccionar un tipo existente cuando corresponda | Separar definición del tipo y solicitud concreta; un texto libre no sustituye FK ni alta autorizada de catálogo |
| SD03 | Indicaciones de entrega | Texto que explique qué se necesita, a qué período corresponde y cómo corregirlo | Sin diagnósticos, datos ajenos o reglas de extracción de texto libre |
| SD04 | Personas destinatarias | Selección explícita de personal del rol y alcance, con revisión de nombres antes de confirmar | Conservar membresía de destinatarios emitida; no recalcularla retrospectivamente con el rol actual |
| SD05 | Fecha límite de entrega | Visible en revisión final. Si corresponde al expediente de alta, usar el plazo vigente de siete días y única prórroga aprobados | No utilizar caducidad del archivo como plazo de entrega. Una petición posterior requiere fecha prospectiva explícita, sin mora retroactiva o prórroga nueva automática |
| SD06 | Motivo/contexto de la solicitud | Identificar finalidad laboral concreta, diferenciada del estado del archivo | No atribuir al trabajador retraso en documentos que emite la institución |
| SD07 | Solicitante y fecha de emisión | Sesión y reloj del servidor, de solo lectura | Conservar autoría real e instante; nunca elegir al primer usuario para completar una FK |
| SD08 | Entrega y archivo por destinatario | Reutilizar carga documental, tipo compatible y cadena de correcciones | Vincular entrega con solicitud/destinatario correctos; coincidencia de nombre no prueba cumplimiento |

El límite de tamaño/longitud se concretará con el contrato técnico de archivos y campos reales al implementar; no se promete almacenamiento que el esquema no soporte. Solicitar un tipo nuevo requiere verificar su representación y gobierno del catálogo antes de instalarlo.

## 4. Botones, estados y mensajes

| Control | Efecto previsto | Guardas |
|---|---|---|
| Solicitar documentación | Abrir el formulario | Cuenta activa y competencia gerencial, permiso concreto por mapear |
| Seleccionar personas | Filtrar por rol/alcance y mostrar nombres | No selección masiva silenciosa ni targets excluidos |
| Revisar solicitud | Mostrar documento, indicaciones, personas y fecha límite | Sin registros definitivos o avisos todavía |
| Confirmar solicitud | Guardar solicitud y destinatarios como una operación consistente; mostrar resultado real | Revalidar targets/permiso al confirmar; impedir doble envío/reintentos duplicados |
| Cancelar | Salir conservando contexto del listado, sin emitir petición | Confirmar salida si hay cambios; no borrar solicitudes emitidas |
| Ver seguimiento | Abrir entregas, pendientes de revisión y observados por persona | Solo alcance permitido; no mostrar un agregado como si cada archivo estuviera validado |
| Adjuntar respuesta | Trabajador propio o Gerencia para persona autorizada | Entrega no valida; reutilizar archivo e historial según su identidad real |

Loading, selección vacía, datos inválidos, targets que cambian antes de confirmar, permiso revocado, fallo de archivo y fallo de guardado deben tener mensajes distintos. Éxito significa solicitud conservada, no solo formulario cerrado. Una respuesta histórica no desaparece porque el trabajador cambie de rol o quede inactivo; su acceso futuro sigue las autorizaciones vigentes.

La UI distingue «solicitud preparada», «solicitud emitida», «sin entregar», «recibido para revisión», «observado» y «validado» cuando sus datos reales lo sustenten. Son etiquetas de flujo, no aprobación de nuevos valores SQL. No se incorpora eliminación de solicitudes o anulación de una petición entera por inferencia desde R02: esa decisión corresponde al documento laboral erróneo.

## 5. Normalización y capacidad pendiente

Granos funcionales a conservar: solicitud concreta, destinatario de esa solicitud, entrega/archivo y revisión. Rol de destino y membresía efectiva no son lo mismo: el rol sirve para seleccionar, la membresía conserva quién recibió la petición. Tipo documental, petición y documento entregado tampoco son equivalentes.

No se ha identificado una entidad existente que conserve íntegramente estos hechos. Se debe contrastar catálogo, documentos, relaciones y consumidores antes de proponer el destino. No introducir listas de destinatarios en texto/JSON, plazos en observacion, fecha de entrega en fecha_vencimiento ni Activitylog como EAV de solicitudes.

**Aprobado:** capacidad funcional de pedir documentación por rol. **Pendiente técnico:** escritor/lector, permisos concretos, representación relacional, invariantes, índices y consumidores. Cualquier tablas/columnas/FK/catálogos nuevos requieren propuesta exacta y aprobación estructural específica; no se solicitan permisos sobre un diseño aún inexistente.

## 6. Aceptación adicional no ejecutada

Extiende [P01–P36](06-ACEPTACION-PERSONAL-DOCUMENTOS.md). Todos los escenarios S01–S08 están especificados, no ejecutados.

| Caso | Campos/acción | Resultado verificable requerido |
|---|---|---|
| S01 | SD01–SD07, confirmar | Petición válida conserva rol, contenido, fecha, actor y destinatarios exactos; solo emitidos tras confirmación |
| S02 | SD01/SD04, autorización | Denegar trabajador que intenta emitir, rol/target excluido, ID manipulado y permiso revocado, sin datos/avisos laterales |
| S03 | SD04, historia | Cambio de rol/inactividad posterior no reescribe destinatarios o entregas pasadas; consulta sigue autorización vigente |
| S04 | Confirmar, idempotencia | Doble clic, reintento y dos confirmaciones concurrentes no duplican la misma solicitud/destinatarios; verificar PostgreSQL desechable |
| S05 | SD08, entrega | Archivo del titular/tipo equivocados no satisface la petición; carga de Gerencia conserva cargador real y no valida automáticamente |
| S06 | SD05, plazos | Alta usa límite vigente; solicitud posterior no recibe plazo pasado por defecto ni reinicia los siete días de un alta; sin mora por opcionales no solicitados |
| S07 | Atomicidad | Fallo parcial revierte solicitud/membresías/avisos nuevos y compensa archivos nuevos pertinentes; preserva documentos históricos |
| S08 | Formulario y retorno | Revisar/cancelar, errores, foco, teclado, loading y nombres legibles funcionan; no código/ID visible ni datos de otras personas |

**Estado:** especificación integrada; implementación, pruebas backend/frontend, representación SQL y revisión visual pendientes. No se han emitido peticiones, cargado archivos o cambiado registros reales en esta tarea documental.
