---
title: "Gerencia: ficha, campos y operaciones de Personal"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: APPROVED_DECISIONS_AND_STATIC_CODE_SCHEMA_REVIEW
runtime_verified: false
---

# Ficha y operaciones de Personal

## 1. Alcance y fuentes rectoras

Esta especificación desarrolla las decisiones de [Personal](02-PERSONAL.md), [alta en cuatro pasos](03-ALTA-TRABAJADOR.md) y [respuestas 15–29](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos). El actor es Gerencia, con cuenta activa, permiso efectivo y autorización contextual. El alcance laboral comprende Administración y las seis profesiones; las cuentas con Gerencia/Superadministración quedan fuera aunque tengan otro rol permitido.

El [baseline de roles](../../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) gobierna responsabilidades; el [baseline BDD](../../../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y [diccionario](../../../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md) gobiernan persistencia. La lectura de código no concede permisos. Este documento no modifica estructura, catálogos, aplicación o registros.

## 2. Flujo de trabajo

**Objetivo:** localizar un trabajador, comprender su situación laboral, de acceso y documental, y ejecutar la operación autorizada.

Inicio → Personal → buscar/filtrar → ficha → información/documentación/acceso/planificación/historial → acción y confirmación → resultado real → volver conservando filtros y página.

- El alta usa el formulario reutilizado de cuatro pasos. La edición conserva identidad y relaciones; no crea otra cuenta.
- La acción primaria del listado es «Registrar trabajador». En la ficha, la acción primaria depende de la tarea seleccionada; suspender acceso no compite visualmente con consultar.
- Los documentos completos no equivalen a acceso activo ni a competencia clínica. Estado laboral, cuenta y documentación se presentan por separado.
- Recuperación: errores por campo conservan datos recuperables; un conflicto obliga a refrescar la versión pertinente; un fallo de comunicación posterior no deshace ficticiamente el alta.

Entradas actuales localizadas: `admin.personal-institucional` mediante [PersonalInstitucionalPanel](../../../../app/Modulos/Identidad/Presentacion/Livewire/Paginas/PersonalInstitucionalPanel.php), con middleware `personal_institucional.ver`; ficha de cuenta mediante [UsuarioFichaPanel](../../../../app/Modulos/Identidad/Presentacion/Livewire/Paginas/UsuarioFichaPanel.php). No se declara una nueva ruta de ficha laboral ni una ruta de recuperación gerencial instalada.

## 3. Distribución de listado y ficha

| Zona | Contenido y controles previstos | Estado de fuente |
|---|---|---|
| Cabecera del listado | «Personal», breve explicación, Registrar trabajador | Acción requiere alta laboral y cuenta autorizadas |
| Búsqueda y filtros | Nombre/documento, rol/función y acceso; Limpiar filtros, tabla/tarjetas, paginación | Reutilizar filtros reales. Estado laboral y área requieren consulta propia; no renombrar un filtro de cuenta como laboral |
| Resultados | Nombre/foto, profesión o función, área contextual, estado laboral, acceso, resumen documental y acciones | No códigos/ID visibles. «Sin información» distinto de «inactivo» |
| Cabecera de ficha | Nombre, profesión, estado laboral y estado de acceso | Identidad desde personal; foto desde cuenta cuando exista |
| Información | Identidad/contacto y datos laborales permitidos | Campos F01–F14 |
| Documentación | Resumen de entregas/revisión/plazo, listado y detalle por archivo | Contrato de documentación separado |
| Acceso | Correo, rol de consulta, ACTIVO/SUSPENDIDO, suspender/activar/recuperar según contexto | Campos F15–F18; no editor de roles |
| Planificación | Enlace a planificación autorizada, datos de asignaciones con fecha/área/turno | No inventar pertenencia desde la primera asignación encontrada |
| Historial | Operaciones auditadas y sucesión documental accesibles al actor | No suponer que un texto de log permite reconstruir datos no guardados |

Cambiar tabla/tarjetas modifica presentación, no permisos o universo de resultados. No se confunde este listado laboral con el apartado de Familiares/contactos.

## 4. Matriz de campos y persistencia

«Actual» describe código/DDL observado; no una nueva aprobación de reglas. Nulabilidad SQL y obligatoriedad del formulario son dimensiones diferentes. Los límites de formato actuales se conservan como evidencia para contrastar, no como requisitos legales deducidos.

| ID | Campo visible / entrada | Destino existente | Validación actual o límite funcional | Fuente de obligatoriedad |
|---|---|---|---|---|
| F01 | Nombres / nombres | personal.nombres | Required, string, máximo 100; Action normaliza mayúsculas | Validación observada |
| F02 | Apellido paterno / ap_paterno | personal.apellido_paterno | Required, máximo 80 | Validación observada |
| F03 | Apellido materno / ap_materno | personal.apellido_materno | Nullable, máximo 80; conservar ausencia | Validación observada |
| F04 | Documento / numero_documento | personal.numero_documento | Único; Action acepta 5–10 dígitos, columna string(30) | Formato observado; no ensanchar por UI |
| F05 | Expedido / expedido | personal.expedicion_documento | Required en Action, máximo 20; SQL nullable | Validación observada |
| F06 | Nacimiento / fecha_nacimiento | personal.fecha_nacimiento | Required en Action; rango actual 18–100 años; SQL nullable | Validación observada, no norma laboral nueva |
| F07 | Género / genero | personal.genero | Action admite M/F; SQL string(20) nullable | Catálogo/validación existente; no añadir valores por inferencia |
| F08 | Teléfono / telefono | personal.telefono | Action: ocho dígitos, comienza 6/7; SQL nullable string(30) | Validación observada |
| F09 | Dirección / direccion | personal.direccion | Required en Action, máximo 255; SQL nullable | Validación observada |
| F10 | Profesión/función | personal.profesion | Action utiliza el rol seleccionado al crear; preservar al editar sin cambio de rol | No inferir especialidad o competencia desde el permiso |
| F11 | Matrícula / matricula_prof | personal.matricula_profesional | Nullable, máximo 50; archivo de matrícula es un requisito distinto | Código observado y mínimo documental aprobado |
| F12 | Ingreso laboral / fecha_ingreso | personal.fecha_ingreso | Nullable, date; no es fecha técnica de creación de cuenta | Código observado |
| F13 | Estado laboral / estado_personal | personal.estado | ACTIVO/INACTIVO; R03A aprueba operación propia con motivo y fecha. No confundir con suspensión de cuenta | Cuenta separada; Administración revisa asignaciones afectadas. Retención de motivo/fecha por resolver técnicamente |
| F14 | Observaciones / observaciones | personal.observacion | Nullable, texto; no guardar plazos o prórrogas como estructura oculta | Campo propio del trabajador |
| F15 | Correo / correo | usuarios.correo | Required, email, máximo 120, único; minúsculas/trim | Alta conjunta aprobada |
| F16 | Rol / roles_seleccionados | Tablas técnicas Spatie, vínculo real usuario/rol | Una selección autorizada para alta; no reasignación de roles preexistentes por Gerencia | Ocho destinos aprobados; siete laborales |
| F17 | Contraseña temporal / contrasena_temporal | usuarios.contrasena mediante mutador hash | Actual 12–72 caracteres; no persistir ni registrar valor legible | 15A aprobada; cambio inicial aún por integrar |
| F18 | Estado de acceso | usuarios.estado | Nueva cuenta ACTIVO; activar/suspender mediante operación propia y confirmación | Decisiones 4 y 14 |
| F19 | Foto de perfil / foto_perfil | usuarios.foto más almacenamiento | Nullable; imagen JPG/JPEG/PNG/WEBP, máximo actual 2 MB | No sustituye automáticamente el archivo FOTO_DOC del checklist |
| F20 | Documentos / archivos_temporales | documentos y almacenamiento mediante contrato | Alta admite actualmente PDF/JPG/JPEG/PNG/DOC/DOCX hasta 5 MB; ficha admite hasta 10 MB y formatos más limitados | Divergencia técnica a conciliar; no presentar ambos límites como uno |
| F21 | Fecha del alta y plazo de entrega | Sin columna específica identificada en usuarios/personal/documentos | Identidad y fecha del alta deben quedar retenidas; fecha_ingreso no sirve como sustituto | Requisito aprobado, persistencia por resolver |
| F22 | Prórroga y motivo | Sin relación/columnas específicas identificadas en esas tablas | Conservar alta, límite anterior/nuevo, actor y fecha; una sola prórroga | Requisito aprobado, persistencia por resolver |
| F23 | Área/turno del resumen | asignaciones_personal con jornada/área y contexto vigentes | No escribir área en personal.observacion ni escoger primera fila para simular pertenencia | Relaciones y planificación existentes |

No se agregan al formulario como funcionales teléfono alternativo, institución de formación, años de experiencia, subtipo de Enfermería, nivel de responsabilidad o forzar_cambio_password: la Action actual los rechaza con contenido. El cambio de contraseña obligatorio ya está aprobado; hay que implementarlo en identidad con soporte real, no enviar ese campo y fingir éxito.

## 5. Matriz de botones, permisos y efectos

Los permisos citados son nombres observados, no grants instalados. Cada mutación reevalúa sesión, cuenta, actor, target, roles destino, estado y contexto en backend. Los flujos pendientes no se habilitan dando permisos globales como atajo.

| ID | Botón/entrada | Capacidad y contexto | Efecto/guardado | Aceptación |
|---|---|---|---|---|
| A01 | Registrar trabajador | personal.gestionar y usuarios.crear actuales, más límites de Gerencia | Abrir wizard; crear solo al finalizar | P01–P04 |
| A02 | Ver ficha | Lectura de personal y fuentes autorizadas; target existente | Solo consulta; mantener retorno y filtros | P05 |
| A03 | Editar datos | personal.gestionar; correo/foto requieren usuarios.editar en Action | Actualizar campos autorizados, auditar; no cambiar rol, contraseña o estado como efecto lateral | P06–P07 |
| A04 | Suspender acceso | usuarios.cambiar_estado observado y cuenta destino gestionable | Confirmar persona y efecto; usuarios.estado=SUSPENDIDO, sin baja laboral o borrado | P08 |
| A05 | Activar acceso | Mismo permiso y target; revalidar requisitos | usuarios.estado=ACTIVO sin inventar competencia o vínculo | P09 |
| A06 | Recuperar acceso | 16B; permiso/entrada concretos por mapear | Iniciar/reemitir enlace seguro, auditar; nunca mostrar contraseña recuperada | P10–P11 |
| A07 | Consultar documentación | Lectura autorizada de titular y archivo | Abrir checklist/detalle; no publicar rutas privadas | P12 |
| A08 | Subir documento | documentos.subir o documentos.gestionar actuales más ownership laboral | Trabajador propio o Gerencia en alcance; archivo y documento CARGADO, sin autovalidación | P13–P14 |
| A09 | Revisar: validar | documentos.gestionar actual y responsabilidad de Gerencia | VALIDADO, actor/fecha y auditoría; distinguir archivo entregado de validado | P15 |
| A10 | Revisar: observar | Igual alcance, motivo requerido actual 5–255 | OBSERVADO con motivo; no crear RECHAZADO como catálogo nuevo | P16 |
| A11 | Entregar corrección | Mismo alcance de carga; nueva versión de archivo | Documento sucesor y anterior REEMPLAZADO, sin borrado | P17 |
| A12 | Conceder prórroga | Competencia gerencial aprobada; permiso/entrada por mapear | Una sola, +7 días al límite original; sin campo genérico ni estado SQL inventado | P18–P20 |
| A13 | Ver planificación | Permiso de lectura y target autorizado | Consultar recorrido propietario; no conceder asignaciones por abrir ficha | P21 |
| A14 | Ver historial | Alcance de cuenta/documentos y auditoría autorizada | Consulta, autorías/fechas reales; no descargar todo expediente por implicación | P22 |
| A15 | Anular documento | R02A aprobada, permiso observado documentos.archivar/gestionar más target laboral autorizado | Motivo, actor/fecha, ANULADO e historia; sin borrado, documentos clínicos o cuentas fuera de alcance | P23 |
| A16 | Cambiar estado laboral | R03A aprobada; permiso concreto por mapear, separado de usuarios.cambiar_estado | ACTIVO/INACTIVO con motivo/fecha; cuenta separada y revisión de asignaciones por Administración, sin eliminar pasado | P24 |

Las exportaciones agregadas 26B/C no autorizan descargar expedientes laborales o enviar archivos por correo. Los botones de esos procesos deben conservar su permiso y alcance propios.

## 6. Confirmaciones, errores y microinteracciones

- Cabecera legible y datos mínimos; documento completo/dirección reservados a la ficha autorizada, no a cada tarjeta.
- Edición y carga en componentes compartidos; no inventar segunda paleta. Tokens de crema, salvia y superficies neutras del [contrato visual](../../../frontend/CONTRATO_VISUAL_UX_UI.md), sin colores como única indicación de estado.
- Botones de al menos 44 px, foco visible, labels, errores junto al campo, loading y prevención de doble envío. Modales con encabezado, Escape, foco y retorno; confirmación al salir con cambios.
- «Cuenta suspendida» y «Trabajador inactivo» son mensajes diferentes. Acción A04/A05 debe nombrar cuenta/acceso; el mensaje actual «estado del personal» no es suficiente.
- Sin filas: estado vacío; filtro sin coincidencias: Limpiar filtros; permiso revocado: acceso denegado; error de servidor: mensaje seguro y reintento. No traducir ninguna de esas condiciones a cero documentos o situación laboral normal.
- Error final de wizard dirige al paso pertinente; rollback de SQL y compensación de archivos nuevos, nunca borrado de archivos históricos.
- Confirmación de suspensión identifica a la persona y la pérdida de acceso; prórroga muestra fechas anterior y nueva antes de guardar; recuperación confirma solicitud real, sin asegurar entrega externa no verificada.

## 7. Persistencia, seguridad e integración por contrastar

Las migraciones de [usuarios](../../../../database/migrations/2026_09_18_001001_create_usuarios_table.php), [personal](../../../../database/migrations/2026_09_18_001002_create_personal_table.php) y [documentos](../../../../database/migrations/2026_09_18_001019_create_documentos_table.php) son evidencia de diseño local, no inventario leído en la base institucional durante esta tarea. Usuarios no tiene timestamps; personal.fecha_ingreso es fecha laboral. No basta sumar siete días a now() cada vez que se abre la ficha.

Actividad técnica con Spatie conserva operaciones conforme al contrato aprobado. No utilizar su contenido genérico como EAV para simular una entidad de plazo/prórroga o un flag de primer acceso que no exista. Cualquier extensión estructural necesita diccionario y decisión específica; no se propone ni instala DDL en esta tarea.

La [Action de alta](../../../../app/Modulos/Identidad/Aplicacion/Acciones/GuardarPersonalInstitucional.php) tiene transacción y compensación de archivos; el [cambio de estado](../../../../app/Modulos/Identidad/Aplicacion/Acciones/CambiarEstadoPersonal.php) modifica cuenta. La autorización documental actual comprueba permisos pero debe acreditar ownership profesional y exclusión de cuentas en cada entrada. Los defaults visuales no prueban estas garantías.

## 8. Cierre y trazabilidad

**Operaciones laborales/documentales ratificadas en R02A/R03A:**

- Anular documento: abrir el archivo vigente del titular autorizado → indicar motivo → confirmar «Dejar este documento sin vigencia» → conservar ANULADO, actor/fecha, archivo e historia. No es borrar ni invalidar toda la cadena; la corrección usa sustitución. Los antecesores reemplazados/anulados no se reactivan por una anulación.
- Cambiar estado laboral: identificar trabajador y ACTIVO/INACTIVO actual → elegir estado opuesto → registrar motivo/fecha → revisar consecuencias y confirmar → conservar transición e historial. La representación de fecha/motivo no se improvisa en columnas ajenas.
- La cuenta conserva su estado hasta una operación explícita de acceso; la reactivación laboral no reactiva la cuenta. Administración recibe continuidad de las asignaciones afectadas por el proceso vigente; ni la baja ni la reactivación borran o recrean automáticamente turnos, atenciones o historia clínica. Las guardas de personal activo continúan aplicándose a cada acto profesional.

Campos F01–F23 y acciones A01–A16 se enlazan con [P01–P36](06-ACEPTACION-PERSONAL-DOCUMENTOS.md). Las [respuestas R01–R03](07-DECISIONES-RESIDUALES-PERSONAL.md#respuestas-adoptadas-el-25102026) están adoptadas. La [documentación laboral](05-DOCUMENTACION-LABORAL.md) desarrolla catálogo, revisión, plazos y sucesión; [solicitud por rol](08-SOLICITUD-DOCUMENTAL-POR-ROL.md) añade campos/acciones específicos y ocho escenarios adicionales.

**Documentado:** proceso, campos/destinos, acciones/controles, integración y casos requeridos. **No acreditado:** implementación integral, cambios de permisos, pruebas de aplicación, funcionamiento en navegador o capacidad estructural completa. Sin cambios de aplicación o BDD.
