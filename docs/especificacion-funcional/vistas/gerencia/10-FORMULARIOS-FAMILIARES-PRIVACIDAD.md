---
title: "Gerencia: formularios familiares y privacidad"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: APPROVED_SCOPE_AND_STATIC_FORM_PRIVACY_REVIEW
runtime_verified: false
---

# Formularios familiares y privacidad

## 1. Autoridad y recorrido

Desarrolla [Familiares y contactos](09-FAMILIARES-Y-CONTACTOS.md), campos FC01–FC21 y acciones FV01–FV12, con decisiones 17B, 18A, 19B/A y mantenimiento/recuperación previamente adoptados. Las decisiones de cuenta activa, contraseña temporal y documentos laborales tienen alcance propio; no trasladar el mínimo documental laboral a familiares.

Los formularios siguientes son especificación de interacción sobre entidades existentes; no son nuevas rutas o componentes ya implementados. Social coordina el vínculo; Identidad conserva cuenta y recuperación. El diseño no crea otra tabla de familiares ni amplía publicaciones clínicas.

## 2. Registrar o editar contacto

**Entrada:** botón Registrar familiar o contacto, o Editar datos en ficha permitida. Buscar primero coincidencias por identidad y medios de contacto, sin asumir que correo, nombre o documento aislado permiten fusionar personas.

Bloques: identidad (FC01–FC04), medios de contacto (FC05–FC08), observaciones pertinentes (FC10) y resumen de vínculos/cuenta existentes de solo lectura. Campos originales separados; errores al lado del campo. Estado del contacto es independiente; su cambio global no se ofrece como consecuencia implícita de editar parentesco.

**Botones:** Buscar existente, Usar contacto seleccionado, Guardar contacto, Cancelar. Guardar identidad no crea vínculo, cuenta, autorización de información ni residente. Si se abre desde un residente, ofrecer después Vincular a este residente, manteniendo contexto y confirmación de la relación; no informar vínculo creado antes de guardarlo.

Editar un teléfono o nombre del contacto común afecta su identidad compartida. Avisar de ese alcance, preservar vínculos y permisos, no replicar copias de la persona para cada residente. No mostrar datos de otros contactos no autorizados para resolver una coincidencia.

## 3. Registrar o corregir vínculo

**Entrada:** Vincular contacto existente desde contexto de residente o ficha de contacto. Contexto fijo visible: nombre de la persona y residente, con selección solo durante alta. En corrección, no cambiar la identidad de los extremos de la relación como atajo.

Campos de vínculo: parentesco FC13, observación FC19 y las condiciones FC14–FC18 que realmente permita el proceso competente. La identidad FC01–FC10 permanece de consulta para esta operación; editarla usa su formulario específico.

Responsable principal, emergencia, información y salida se presentan por separado, con origen/estado cuando exista. Los controles para conceder autorizaciones no se habilitan por la sola decisión 17B; deben conservar los permisos y soportes de consentimiento pertinentes. Si faltan, mostrar «Autorización no acreditada» y no un Sí por defecto. Un formulario con un booleano ausente no lo convierte en permiso favorable.

**Botones:** Revisar vínculo, Guardar vínculo, Cancelar. La revisión muestra lo que cambia y su efecto real sobre consultas; guardar reautoriza target y evita el par activo duplicado. Corregir parentesco no valida retrospectivamente una salida ni reescribe consentimiento firmado.

Vigencia del vínculo: existen controles de desactivar/restaurar en los consumidores. Deben mostrar contexto y consecuencias, conservar motivo/autor/fecha por el mecanismo de historia adecuado y no borrar registros. Su integración no equivale a suspensión de cuenta. Si el soporte institucional o la representación de la transición son insuficientes, documentar la brecha antes de exponer esa mutación.

## 4. Crear y gestionar cuenta Familiar

**Decisión posterior de la propietaria, 26/10/2026:** se adopta **enlace seguro al correo del familiar para que elija su contraseña**. Se reutiliza el broker existente, con token hasheado, expiración y un solo uso. El alta se realiza sobre contacto y vínculo autorizado existentes; no concede autorizaciones ni crea Personal. Esta decisión resuelve el pendiente de credenciales que se describe históricamente más abajo. Si el envío falla después de guardar SQL, se informa cuenta creada/envío pendiente y se permite reemitir, sin duplicar el alta.

**Entrada:** Crear cuenta Familiar desde un contacto sin cuenta. Resolver identidad existente, vínculo autorizado y correo de acceso; rol FAMILIAR de catálogo existente, sin selector de roles técnicos. La relación contactos.cod_usuario nullable UNIQUE no se rellena con una cuenta cualquiera para evitar validar.

Crear cuenta con contacto/vínculo aptos deja la nueva cuenta ACTIVO según 18A. Sin vínculo autorizado no se informa un alta Familiar lista para consultar. Puede registrarse el contacto y atender su vínculo por el proceso propio; no fabricar autorización para completar cuenta.

Guardar correo de acceso no modifica silenciosamente correo de contacto ni viceversa. Los dos datos tienen destinos distintos; la UI debe explicar cuál se edita. No se duplica la identidad en usuarios ni se genera una ficha de personal laboral.

**Credenciales:** la decisión expresa del 26/10/2026 adopta el enlace seguro inicial para Familiar, además de la recuperación 16B. Se reutiliza el broker de identidad; Gerencia no elige ni conoce la contraseña. La contraseña temporal 15A corresponde a trabajadores. La confirmación diferencia cuenta guardada, enlace aceptado por el transporte y fallo de envío; aceptación del transporte no acredita recepción o lectura del correo.

**Botones:** Crear cuenta, Activar acceso, Suspender acceso, Recuperar acceso y Cancelar, según estado y operación autorizada. Edición de vínculo no activa/reanuda por efecto lateral una cuenta suspendida. No cambiar rol de cuenta existente, crear Gerencia/Superadministración o asignar permisos adicionales desde este formulario.

## 5. Cadena de autorización de lectura familiar

| Paso | Comprobación | No equivale a |
|---|---|---|
| Cuenta | Autenticación y ACTIVO, permiso explícito | Acceso a todos los residentes |
| Contacto | Contacto asociado a la cuenta, estado válido | Publicación de notas o documentos |
| Vínculo | Misma pareja contacto/residente, ACTIVO, autorización de información según Policy | Representación legal o autorización de salida |
| Residente | Identidad/alcance y estado de la operación, sin creación directa | Admisión formal nueva |
| Fuente concreta | Contenido expresamente permitido y operación de lectura autorizada | Acceso universal a clínica, PDF o exportaciones |

Cambiar de residente en una URL, petición JSON o acción Livewire exige repetir los controles. La revocación se aplica en la siguiente lectura/operación autorizada, no solo al refrescar el menú. Cuenta activa con vínculo revocado no recibe datos de ese residente; otros vínculos válidos se evalúan de manera independiente. No suspender toda la cuenta para ocultar la pérdida de una sola relación.

No enviar al navegador datos clínicos/contactos ajenos aunque luego CSS los oculte. No devolver datos recortados como si fueran el expediente íntegro. Descargar o previsualizar un documento es otra operación de lectura; se reautoriza y no se expone la ruta privada.

## 6. Datos y actos que permanecen separados

- Parentesco: relación con un residente; no atributo global del contacto.
- Responsable principal: designación institucional; no declaración automática de tutoría o permiso clínico.
- Contacto de emergencia: a quién contactar; no autoriza información o salida.
- Información autorizada: condición de lectura del vínculo; cada fuente conserva su alcance/publicación.
- Salida autorizada: condición propia y proceso de salida; no se obtiene por poder ingresar al portal.
- Estado de contacto, vínculo y cuenta: tres objetos; inactivar uno no autoriza reescribir los otros.
- Contacto sin cuenta: permitido. Registro de cuenta Familiar: requiere datos y vínculo autorizado según decisión adoptada.

No se incorporan «responsable de salud», «responsable económico» o acuerdos serializados en observacion como nuevas relaciones normalizadas. Si esas figuras se requieren, localizar primero su contrato y modelo autorizado.

## 7. Historia, errores y concurrencia

Usuarios/contactos/vínculos se guardan con transacciones cuando la operación es conjunta. Conservar contacto previo si falla crear su cuenta; una reversión solo elimina efectos nuevos de esa solicitud por el mecanismo autorizado, nunca historia existente.

Dos usuarios vinculando al mismo tiempo no pueden crear la misma relación ACTIVA duplicada o reasignar responsable en otros residentes. El índice y eventos de modelo inspeccionados no demuestran garantía concurrente por sí solos. Validación, bloqueos y pruebas se completan sin alterar índices/FK congelados unilateralmente.

Las tablas inspeccionadas no retienen nominalmente todos los autores/fechas de cambio del vínculo. No reconstruirlos desde el orden de ID ni declarar un texto libre como historia relacional íntegra. Auditar operaciones técnicas con Spatie; si falta capacidad de dominio para consentimientos/correcciones, proponer estructura exacta antes de migrar.

Mensajes y recuperación: identidad incompleta, contacto ya existente, vínculo duplicado, target no autorizado, cuenta existente, conflicto por edición simultánea y fallo de guardado se distinguen. No usar «vínculo guardado» si se cerró el formulario sin confirmar SQL. Las peticiones heredadas pueden conservar nombres V1 en rutas por compatibilidad actual, pero la nueva interfaz usa residente/contacto/usuario y su implementación posterior elimina legacy directamente relacionado con sus pruebas.

## 8. Evidencia y pendientes acotados

Las fuentes y hallazgos estáticos están en [la ficha principal](09-FAMILIARES-Y-CONTACTOS.md#7-integración-fuentes-y-diferencias). [ResidentePolicy](../../../../app/Modulos/Residentes/Presentacion/Policies/ResidentePolicy.php) gobierna lectura; las mutaciones necesitan la autorización completa propia, no una copia de esa Policy como permiso genérico.

**Definido:** dos presentaciones, formularios, separación de datos, límites de Gerencia/Familiar, relación con cuenta y recuperación, historia e integración. **Pendiente:** mapeo de grants/entradas, soporte verificable de historia, mecanismos existentes para autorizar información/salida y credenciales iniciales de Familiar cuando el contrato no los resuelva. No se consideran bloqueadas las otras capacidades documentadas por esa falta puntual.

Ninguna autorización de información/salida nueva se presume recibida. [24 escenarios especificados](11-ACEPTACION-FAMILIARES-CONTACTOS.md). Sin implementación, pruebas funcionales, QA visual, DDL, comunicaciones o altas realizadas en esta tarea.
