---
title: "Gerencia: Familiares y contactos"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISIONS_AND_STATIC_CONTACT_FLOW_REVIEW
runtime_verified: false
---

# Familiares y contactos

## 1. Propósito, actores y fuentes

Gerencia necesita localizar contactos, registrar/corregir vínculos familiares y gestionar cuentas Familiar de su alcance. La persona de contacto, su relación con cada residente y su cuenta son hechos distintos. Los contactos pueden existir sin cuenta; no son personal ni se les exigen documentos laborales.

Autoridad: [decisiones 17B, 18A y 19B/A](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos), [baseline de roles](../../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md), [diccionario V2](../../../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [alcance de Familiar](../../roles/familiar.md). Esta ficha no amplía publicación clínica, autorización de salida, representación legal o permisos de otras disciplinas.

Propietarios: **Social** mantiene el vínculo/red de apoyo; **Identidad** mantiene la cuenta; **Residentes** aporta el contexto y su Policy. Reutilizar estos propietarios, sin nuevo módulo, directorio o tabla de familiares paralela.

## 2. Entrada y dos presentaciones adoptadas

Menú previsto de Gerencia: **Familiares y contactos**. Selector **Por residente / Por persona**, usando los mismos contactos y vínculos. No se ha elegido una presentación predeterminada entre ambas; esa preferencia no bloquea especificar las dos.

Ruta existente de referencia: `admin.familia-social.red-apoyo`, declarada en [rutas Social](../../../../app/Modulos/Social/Presentacion/Rutas/web.php) dentro del grupo de [routes/web.php](../../../../routes/web.php). Entrada general exige lectura `residentes_contactos.ver`. [RedApoyoPanel](../../../../app/Modulos/Social/Presentacion/Livewire/Paginas/RedApoyoPanel.php) se centra en residente; no acredita una vista global por persona o el nuevo acceso de Gerencia ya integrados. Las rutas HTTP heredadas de familiares redirigen a la pestaña de la ficha del residente.

| Zona | Por residente | Por persona |
|---|---|---|
| Cabecera | Título, selector de presentación y búsqueda de residente | Mismo título/selector; búsqueda de contacto |
| Contexto | Nombre del residente y estado administrativo permitido | Nombre del contacto, cuenta si existe y estado del contacto |
| Resultados | Contactos vinculados: nombre, parentesco, responsable/contacto de emergencia según datos, vínculo y acceso | Un contacto por identidad; resumen de residentes vinculados sin duplicar la persona por cada relación |
| Detalle | Relación con ese residente y datos de contacto pertinentes | Identidad compartida y lista de vínculos; cada vínculo abre su contexto |
| Acciones | Vincular contacto existente, registrar uno nuevo, ver/corregir vínculo | Registrar contacto, editar identidad y abrir residente/vínculo autorizado; cuenta separada |
| Filtros | Residente, nombre de contacto y estado del vínculo | Nombre/documento/contacto, existencia/estado de cuenta y vínculos, según fuentes reales |

No mostrar códigos/ID como identidad. Distinguir contacto INACTIVO, vínculo INACTIVO y cuenta SUSPENDIDA; el mismo contacto puede tener relaciones diferentes con varios residentes. Mantener selector, búsqueda y retorno; cambiar de presentación no replica registros ni otorga acceso nuevo.

## 3. Flujo y continuidad

1. Buscar residente o persona dentro del alcance permitido.
2. Comprobar coincidencias y seleccionar contacto existente antes de ofrecer crear otro. Nombre coincidente no justifica fusión automática.
3. Registrar identidad/contacto si es nuevo, con datos separados; no dividir un nombre completo para inventar apellidos.
4. Registrar residente, parentesco y demás condiciones del vínculo realmente autorizadas. Mostrar permisos de información/salida sin activarlos por defecto o derivarlos del parentesco.
5. Revisar y guardar; conservar autor/fecha y correcciones según mecanismos adecuados. No cambiar la titularidad de un vínculo histórico para apuntarlo a otro residente.
6. Si se necesita cuenta Familiar, reutilizar la identidad existente; crear/vincular la cuenta únicamente con datos y vínculo autorizado, activa según 18A.
7. Familiar ingresa y consulta solo contenido autorizado; sin publicación no aparece historia clínica por el solo vínculo. Revocar un vínculo reevalúa ese acceso y no borra relaciones con otros residentes.

Alta de contacto no implica cuenta obligatoria. Alta laboral de cuatro pasos no se aplica a Familiar. Para una operación conjunta de cuenta/contacto/vínculo, garantizar atomicidad; si un contacto ya existía y la cuenta falla, no borrar su identidad histórica como compensación.

## 4. Matriz de campos y origen

SQL observado en las migraciones de [contactos](../../../../database/migrations/2026_09_18_001005_create_contactos_table.php), [residentes_contactos](../../../../database/migrations/2026_09_18_001016_create_residentes_contactos_table.php) y [usuarios](../../../../database/migrations/2026_09_18_001001_create_usuarios_table.php). No se leyó la base institucional en esta tarea. Los campos nullable no se convierten en obligatorios o valores inventados por llenar la interfaz.

| ID | Campo visible | Destino y forma | Validación/límite de responsabilidad |
|---|---|---|---|
| FC01 | Nombres | contactos.nombres, string(100) | Datos originales; requerido para contacto nuevo |
| FC02 | Apellido paterno | contactos.apellido_paterno, string(80) | Requerido; no generar «SIN APELLIDO» como respaldo |
| FC03 | Apellido materno | contactos.apellido_materno, nullable string(80) | Mantener ausencia |
| FC04 | Documento de identidad | contactos.numero_documento, nullable string(30) | Identificar coincidencias; no afirmar UNIQUE que la migración no contiene |
| FC05 | Teléfono | contactos.telefono, nullable string(30) | Formato/longitud real; no duplicar silenciosamente en celular |
| FC06 | Celular | contactos.celular, nullable string(30) | Dato distinto de teléfono; el resumen puede mostrar el disponible indicando tipo |
| FC07 | Correo del contacto | contactos.correo, nullable string(120) | Email cuando exista; no reemplaza correo de inicio de sesión automáticamente |
| FC08 | Dirección | contactos.direccion, nullable string(255) | Restringir detalle; panel actual limita 150, por conciliar técnicamente |
| FC09 | Estado del contacto | contactos.estado | Separado de vínculo y cuenta. No habilitar cambio global de contacto por inferencia de corregir un vínculo |
| FC10 | Observación de contacto | contactos.observacion, nullable text | Solo contexto de persona, no parentescos múltiples codificados en texto |
| FC11 | Residente vinculado | residentes_contactos.cod_residente, FK | Residente existente del proceso admitido/alcance; no crear residente por este formulario |
| FC12 | Contacto elegido | residentes_contactos.cod_contacto, FK | Contacto permitido, sin mover relaciones históricas a otra persona |
| FC13 | Parentesco | residentes_contactos.parentesco, string(40) | Relativo a ese residente, no atributo global del contacto; validar máximo 40 |
| FC14 | Responsable principal | residentes_contactos.responsable_principal, boolean | No acredita representación legal ni atribuciones clínicas; respetar contrato institucional de designación |
| FC15 | Contacto de emergencia | residentes_contactos.contacto_emergencia, boolean | Independiente de responsable; no igualar automáticamente ni imponer unicidad por analogía |
| FC16 | Información autorizada | residentes_contactos.autoriza_informacion, boolean | Conservar decisión válida; 17B no autoriza por sí sola editar este permiso. Sin fuente, no activar acceso a información |
| FC17 | Salida autorizada | residentes_contactos.autoriza_salida, boolean | Separado de parentesco/información; no autoriza salidas concretas sin su proceso |
| FC18 | Estado del vínculo | residentes_contactos.estado | ACTIVO/INACTIVO en operaciones actuales; comprobar contexto y no borrar historia |
| FC19 | Observación del vínculo | residentes_contactos.observacion, nullable text | Contexto de esa relación; no EAV de responsables económicos/salud o consentimientos |
| FC20 | Cuenta de acceso | contactos.cod_usuario, nullable UNIQUE FK usuarios | Vínculo opcional; impedir tomar una cuenta ajena por ID manipulado; no duplicar personal |
| FC21 | Correo, rol y estado de cuenta | usuarios.correo/estado y Spatie | FAMILIAR, datos aptos/vínculo autorizado para 18A; no cambiar rol de cuenta existente ni activar una suspendida al editar vínculo |

Los campos de autorizaciones se muestran para comprender acceso. El control para otorgarlas sigue el proceso competente y su soporte; si ese mecanismo falta, se documenta la brecha y se continúa con el contacto/vínculo sin fingir autorización. No se requiere publicar datos clínicos para completar este módulo administrativo.

## 5. Botones, permisos y efectos

Permisos citados son nombres observados, no grants instalados. Cada solicitud HTTP/Livewire debe reevaluar cuenta activa, rol, permiso, target/contexto y relación. Guardar no confía solo en el control de mount o la visibilidad del botón.

| ID | Control | Efecto/guardas previstos |
|---|---|---|
| FV01 | Por residente / Por persona | Cambiar presentación sin mutación ni ampliar universo autorizado |
| FV02 | Buscar / Limpiar filtros | Mostrar coincidencias autorizadas y limpiar búsqueda; no listar clínica de fondo |
| FV03 | Ver contacto | Ficha única y vínculos permitidos; lectura por fuente y estado |
| FV04 | Registrar contacto | Identidad nueva sin cuenta ni vínculo falsos; permisos de contactos y contexto |
| FV05 | Vincular contacto existente | 17B, permiso observado residentes_contactos.gestionar más target; impedir par activo duplicado |
| FV06 | Editar datos de contacto | Gerencia dentro de su alcance; advertir que identidad compartida se refleja en otros vínculos, sin sobrescribir parentescos |
| FV07 | Corregir vínculo | 17B; parentesco/observación con autoría y sucesión/historia apropiadas; no reasignar residente o permisos por defecto |
| FV08 | Gestionar vigencia del vínculo | Acto sensible contextual, distinto de suspender cuenta. Operaciones existentes activar/desactivar requieren conciliar alcance/soporte y preservar historia; no se declara instalado |
| FV09 | Crear cuenta Familiar | Alta autorizada con datos/vínculo válidos; crear ACTIVO según 18A sin personal laboral; credenciales mediante proceso de identidad vigente |
| FV10 | Activar / Suspender cuenta | Mantenimiento aprobado para Familiar; confirmación persona/efecto y roles excluidos; no altera contacto/vínculo |
| FV11 | Recuperar acceso | 16B, enlace seguro reemitido para cuenta de alcance; sin mostrar contraseña o levantar suspensión |
| FV12 | Ver historial / Cancelar | Consulta autorizada y retorno; cancelar captura no borra contacto/vínculo existente |

No se incorpora un botón de eliminar persona, borrar vínculos con consentimientos, fusionar identidades, otorgar salida o publicar historia. Marcar responsable/emergencia requiere preservar fuentes/reglas existentes, sin interpretarlo como autorización de información.

## 6. Jerarquía, mensajes y accesibilidad

Cabecera «Familiares y contactos», explicación de ambos modos y búsqueda; contexto de residente/persona siempre visible en formularios. Campos de identidad y vínculo en bloques separados; acceso en bloque propio, no un estado mezclado.

Tabla con nombres legibles y acciones con texto; fotos solo si existen y están autorizadas, alternativa neutra. Datos extensos en detalle, no múltiples tarjetas repetidas por cada atributo. Sin gráficas decorativas de parentescos ni porcentajes de apoyo familiar inventados.

Tokens y componentes del [Design System](../../../frontend/CONTRATO_VISUAL_UX_UI.md): superficies neutras/crema, salvia para acciones, ámbar para pendientes, rojo suave para advertencias concretas; color acompañado de texto, no severidad clínica nueva. Nunito Sans/Inter conforme a tokens; mínimo táctil 44 px, foco visible y errores junto al campo.

Estados diferenciados: sin contactos, sin vínculos, sin cuenta, cuenta suspendida, búsqueda sin coincidencias, acceso restringido, carga, conflicto, fallo y éxito confirmado. Al cambiar residente con captura sin guardar, advertir y no persistir el formulario para el residente nuevo por accidente.

Modales/drawers reutilizados con título, labels, Escape, foco atrapado/retorno y confirmación por cambios pendientes. Reflow 1440/1280/1024/768/390; tabla accesible o tarjetas equivalentes, teclado y lectores de pantalla. Son requisitos pendientes de QA, no resultados observados.

## 7. Integración, fuentes y diferencias

- [GestionarRedApoyo](../../../../app/Modulos/Social/Aplicacion/Acciones/GestionarRedApoyo.php) reutilizable, pero guarda parentesco con máximo de entrada 100 frente a SQL 40; modifica identidad de contacto durante una operación de vínculo; su payload de alta no especifica ambos booleanos de autorización obligatorios en SQL. Validar grano/scope y no inventar valores habilitantes.
- Sus operaciones de responsable/emergencia actualizan relaciones por IDs y desmarcan otros vínculos; el contrato no se demuestra por ese código. Una política de unicidad de emergencia no nace de la exclusividad implementada. Escrituras masivas deben preservar historia y pertenencia real de cada relación.
- [GestionarVinculoFamiliarHttp](../../../../app/Modulos/Social/Aplicacion/Acciones/GestionarVinculoFamiliarHttp.php) y [Request](../../../../app/Modulos/Social/Presentacion/Http/Requests/StoreFamiliarAdultoRequest.php) usan nombres heredados, firstOrNew y creación que separa un nombre para inferir apellidos; asignan autoriza_informacion=true y emergencia desde responsable. Estos defaults contradicen la separación funcional; no reproducirlos en la nueva documentación como reglas aprobadas.
- [VincularContactoResidenteHttp](../../../../app/Modulos/Social/Aplicacion/Acciones/VincularContactoResidenteHttp.php) recibe booleanos explícitos y Gate de residente; no prueba permiso para conceder cada autorización ni ownership completo solo por exists.
- [GestionarUsuariosPanel](../../../../app/Modulos/Identidad/Aplicacion/Acciones/GestionarUsuariosPanel.php) tiene un recorrido Familiar y asigna permisos de información/emergencia por defecto; [GestionarUsuariosHttp](../../../../app/Modulos/Identidad/Aplicacion/Acciones/GestionarUsuariosHttp.php) no realiza la misma persistencia de submodelos. No considerar equivalentes las dos entradas de creación de cuenta.
- [ResidentePolicy](../../../../app/Modulos/Residentes/Presentacion/Policies/ResidentePolicy.php) exige al Familiar permiso de lectura, contacto ACTIVO y vínculo ACTIVO con autoriza_informacion para ese residente. Su lectura no publica todas las fuentes. `viewAny` rechaza el rol Familiar.

Son hallazgos estáticos localizados, no demostración nueva de explotación en producción o pruebas de ejecución. Las correcciones corresponden a implementación posterior autorizada, reutilizando propietarios y eliminando legacy directamente relacionado.

## 8. Persistencia y cierre

Conservar contacto → cuenta opcional; contacto ↔ residente mediante residentes_contactos; visitas/consentimientos mantienen sus FK. El diccionario exige evitar el par activo duplicado. El modelo [ResidenteContacto](../../../../app/Models/ResidenteContacto.php) comprueba al guardar, pero algunas actualizaciones masivas pueden eludir eventos y la migración inspeccionada tiene índice, no UNIQUE filtrado. Concurrencia se debe demostrar en PostgreSQL desechable; no se modifica FK/índice por iniciativa propia.

Contactos y vínculos no tienen timestamps/autor nominal en las migraciones inspeccionadas. Actividad técnica no debe usarse como EAV para ocultar historia de autorizaciones o relaciones que el dominio no conserve. Corregir parentesco no se resuelve reescribiendo consentimientos del pasado; una representación histórica insuficiente requiere propuesta específica, no datos inventados.

[Formularios, privacidad y continuación](10-FORMULARIOS-FAMILIARES-PRIVACIDAD.md) y [24 escenarios de aceptación](11-ACEPTACION-FAMILIARES-CONTACTOS.md). **Estado:** especificación desarrollada; implementación integral, rutas/permisos concretos de Gerencia, pruebas y QA visual pendientes. Sin cambios de aplicación, BDD, grants, comunicaciones o datos durante esta tarea.
