---
title: "Gerencia: Personal institucional"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: STATIC_PERSONNEL_FORM_AND_AUTHORIZATION_REVIEW
runtime_verified: false
---

# Personal institucional de Gerencia

**Detalle documental del 25/10/2026:** [ficha, campos y acciones](04-FICHA-Y-OPERACIONES-PERSONAL.md), [documentación laboral](05-DOCUMENTACION-LABORAL.md), [36 casos base de aceptación](06-ACEPTACION-PERSONAL-DOCUMENTOS.md), [R01–R03 adoptadas](07-DECISIONES-RESIDUALES-PERSONAL.md#respuestas-adoptadas-el-25102026) y [solicitud por rol con ocho casos adicionales](08-SOLICITUD-DOCUMENTAL-POR-ROL.md). Desarrollan el alcance aprobado sin darlo por implementado; [índice de vistas](README.md).

## 1. Propósito y entrada

Gerencia debe poder consultar y gestionar la información laboral y pertenencia del personal dentro de las competencias aprobadas. La administración técnica de acceso mantiene su frontera propia.

Entrada desde «Ver personal» del [Inicio](01-INICIO.md) o desde el menú. Ruta nombrada existente: `admin.personal-institucional`. Consumidores localizados:

- [PersonalInstitucionalPanel](../../../../app/Modulos/Identidad/Presentacion/Livewire/Paginas/PersonalInstitucionalPanel.php).
- [Vista del listado](../../../../app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/personal-institucional-panel.blade.php).
- [PersonalInstitucionalForm](../../../../app/Modulos/Identidad/Presentacion/Livewire/Funcionalidades/PersonalInstitucionalForm.php) y [formulario Blade](../../../../app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/personal-institucional-form.blade.php).
- [Baseline de competencias](../../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md).

Esta ficha propone organización y localiza los datos existentes. No declara implementadas las correcciones ni aprueba permisos o columnas.

## 2. Listado propuesto

Cabecera «Personal institucional», descripción breve y acceso de regreso al Inicio. Un listado principal, con tabla como vista inicial y alternativa de tarjetas ya existente. Evitar duplicar los mismos trabajadores por cada cuenta/rol o mostrar códigos técnicos como identidad visible.

| Columna | Contenido | Precaución |
|---|---|---|
| Persona | Nombre completo y foto existente; alternativa neutra sin foto | No mostrar ID/código como nombre |
| Profesión o función | Dato laboral y clasificación existente | Profesión no es sinónimo de permiso de acceso |
| Área | Pertenencia/asignación pertinente | Diferenciar vínculo actual de una asignación histórica |
| Estado laboral | Estado propio del trabajador | No reemplazarlo por ACTIVO/SUSPENDIDO de la cuenta |
| Planificación | Acceso a horario/asignaciones registradas | No llamar «presente» o «disponible ahora» a toda asignación del día |
| Acciones | Ver ficha, editar datos laborales y consultar planificación según permiso | Reautorizar cada operación |

Búsqueda por nombre/documento y filtros de profesión/área/estado laboral, solo cuando las fuentes y consulta los resuelvan correctamente. «Limpiar filtros», paginación y conteos de resultados deben conservarse; los filtros propuestos no se presentan como todos implementados en el panel actual.

## 3. Ficha del trabajador

Separar cuatro ámbitos visibles:

1. **Identidad y contacto:** nombre, apellidos, documento, teléfono y dirección según acceso necesario.
2. **Información laboral:** profesión/función, matrícula cuando corresponda, fecha de ingreso, estado laboral y pertenencia institucional.
3. **Planificación:** horarios/asignaciones y vigencia, diferenciados de presencia física y de permisos de cuenta.
4. **Documentación:** únicamente documentos autorizados; los permisos de personal no habilitan automáticamente subida/validación o descarga de todo archivo.

El estado de acceso puede informarse si está autorizado, pero sus controles técnicos no se incorporan automáticamente como facultad gerencial. Los datos personales extensos se reservan a la ficha, evitando saturar el listado.

## 4. Campos y límites localizados del formulario actual

| Grupo | Datos existentes observados | Cómo documentarlos |
|---|---|---|
| Identificación | Nombres, apellidos, documento, expedición, nacimiento y género | Obligatorios/formato según el contrato y validación actual; no se aprueba una regla nueva por esta tabla |
| Contacto | Teléfono y dirección | Revisar necesidad y visibilidad por tarea; no publicar datos privados en resúmenes innecesarios |
| Laboral | Matrícula profesional, fecha de ingreso, observación y estado de personal en la Action | Separar dato profesional de rol de acceso |
| Área y perfil | Clasificación/área asociada por el flujo existente | Revisar el mapeo; un selector visible no garantiza almacenamiento del dato |
| Documentos | Archivos y matriz documental según clasificación | Permisos específicos y trazabilidad de validación; no inventar documentos obligatorios |
| Cuenta | Correo, rol(es), contraseña temporal, foto de cuenta y estado de acceso | No tratarlos como mera edición laboral; implican autorizaciones diferentes |

La Action actual rechaza campos como teléfono alternativo, institución de formación, años de experiencia, subtipo de Enfermería, nivel de responsabilidad y forzar cambio de contraseña cuando llegan con contenido. Que el componente declare estas propiedades no las convierte en funcionalidad persistida aceptada. No prometerlas ni añadir columnas para completar un diseño sin decisión específica.

## 5. Botones y formularios por definir

| Acción visible | Finalidad requerida o propuesta | Estado de definición |
|---|---|---|
| Ver ficha | Consulta laboral autorizada | Detalle visual/entrada concreta por documentar |
| Editar datos laborales | Mantener información del trabajador | Debe funcionar con autorización laboral, sin exigir ni conceder edición técnica de cuenta por implicación |
| Registrar trabajador | Alta laboral y creación de cuenta dentro del alcance aprobado | Opción B adoptada: ficha laboral y cuenta obligatorias en una operación; Gerencia registra Administración y los seis roles profesionales |
| Cambiar estado laboral | R03A aprueba ACTIVO/INACTIVO con motivo y fecha | Cuenta por separado; Administración revisa asignaciones, sin borrar pasado. No usar suspensión de cuenta como sustituto |
| Ver planificación | Consultar horarios y asignaciones | Escritura de planificación requiere su autorización propia; H02/H03 abiertos |
| Consultar documentación | Ver documentos permitidos | Subida/validación/descarga se comprueban por operación |

Los formularios deben distinguir errores por campo, guardado, cancelación, cambios sin guardar, loading y confirmación real. Datos laborales y cuenta no se guardan parcialmente como éxito si la operación conjunta falla.

## 6. Diferencias comprobadas con el código

La Action [GuardarPersonalInstitucional](../../../../app/Modulos/Identidad/Aplicacion/Acciones/GuardarPersonalInstitucional.php) exige `personal.gestionar`; para alta exige además `usuarios.crear`. Cambiar correo/roles/foto de cuenta exige `usuarios.editar`. El [seeder de roles](../../../../database/seeders/RolesAndPermissionsSeeder.php) no asigna esas capacidades técnicas a Gerencia de forma ordinaria.

El listado condiciona «Registrar personal» a `usuarios.crear` y varias acciones a permisos de cuenta. Esto puede ocultar la gestión laboral aprobada o mezclarla con administración de acceso; no se corrige concediendo permisos técnicos globales como atajo.

La Action [CambiarEstadoPersonal](../../../../app/Modulos/Identidad/Aplicacion/Acciones/CambiarEstadoPersonal.php) modifica `usuarios.estado` entre ACTIVO/SUSPENDIDO y exige `usuarios.cambiar_estado`. A pesar del nombre, no es una transición del estado laboral de `personal`. [Personal](../../../../app/Models/Personal.php) y usuario son entidades distintas.

El registro actual está centrado en `User` con vínculo `personal`; la existencia de un modelo de trabajador no demuestra por sí sola que el listado/formulario admita personal sin cuenta. Debe comprobarse antes de implementar el reparto elegido.

## 7. Decisión de la propietaria: alta de cuentas por rol

**Respuesta literal del 25/10/2026:** «GERENCIA CREA TODOS LOS DEMAS ROLOES, MENOS GERENCIA, Y SUPERADMIN CREA TODO».

Esta instrucción sustituye para el alcance de creación la propuesta anterior de reservar toda cuenta a Superadministración. Se documenta como alta de personas/cuentas con roles existentes, sin inventar roles nuevos ni cambiar su catálogo.

- Superadministración puede crear cuentas de todos los roles, incluido Gerencia.
- **Precisión expresa recibida el 25/10/2026:** «Gerencia crea Administración, los seis roles profesionales y Familiar; Superadministración crea Gerencia y Superadministración (recomendado).» Esta precisión completa la respuesta anterior; no restringe la capacidad ya indicada de Superadministración para crear todos los roles.
- Gerencia crea cuentas de **ocho roles destino**: ADMINISTRADOR, ENFERMEROS, MEDICO GENERAL/GERIATRA, PSICOLOGO/A, NUTRICIONISTA, FISIOTERAPEUTA, PEDAGOGO y FAMILIAR.
- Gerencia **no crea** GERENTE ni SUPERADMINISTRADOR. Superadministración conserva alta de los diez roles. La restricción se valida también ante peticiones manipuladas o roles adicionales enviados desde el cliente.
- La decisión de creación no concede por implicación edición del catálogo de permisos, reasignación de roles o eliminación. El mantenimiento de datos y acceso fue aprobado después, en la sección 9, con el mismo alcance de ocho roles destino.
- La creación de Familiar debe usar identidad/vínculo de contacto cuando corresponda; no convertirlo automáticamente en trabajador.

La implementación aún no fue modificada. Debe mantener el límite de roles destino en backend, además del selector visible, y comprobar intentos de escalada/manipulación. Las diferencias de código de la sección anterior describen el estado previo a esta nueva instrucción, no una prohibición de desarrollar el cambio solicitado.

## 8. Próximos pasos

Registrar la elección → cerrar campos y responsables de cada formulario → definir botones/estados de listado y ficha → mapear permisos/contexto → contrastar persistencia y consumidores → pruebas. No volver a pedir las decisiones ya adoptadas de Inicio equilibrado y navegación directa.

## 9. Decisión registrada: mantenimiento de datos y acceso

**Respuesta expresa de la propietaria, 25/10/2026:** «Editar sus datos y activar/suspender su acceso (recomendado).»

Gerencia puede crear, editar datos y activar/suspender el acceso de cuentas de Administración, los seis roles profesionales y Familiar. Se mantiene excluida la gestión de cuentas Gerencia/Superadministración en esta extensión; tener además uno de los ocho roles no permite saltar esa exclusión. Las cuentas con roles incompatibles con ese alcance deben resolverse por Superadministración.

| Operación | Alcance documentado | Límite |
|---|---|---|
| Crear cuenta | Ocho roles destino aprobados | No Gerencia/Superadministración; no roles adicionales manipulados |
| Editar datos | Identificación/contacto y datos de la cuenta/persona dentro del flujo autorizado | No cambio de rol ni edición del catálogo de permisos; revisar propiedad del dato y validación por campo |
| Suspender acceso | Cuenta permitida dentro de esos ocho roles | Confirmar persona/efecto y registrar actor; no borrar historia ni convertirlo en baja laboral |
| Activar acceso | Cuenta permitida dentro de esos ocho roles | Revalidar requisitos de la operación; activación no inventa vínculo, profesión, jornada o publicación familiar |
| Cambiar rol o permisos | No autorizado por esta decisión | Operación independiente, no incluida en «editar datos» |
| Eliminar cuenta o historia | No autorizado por esta decisión | No utilizar eliminación como suspensión |

La implementación sigue pendiente. El control debe residir también en backend y evaluar roles de la cuenta destino al ejecutar cada acción, sin depender del filtro del listado ni confiar en los identificadores enviados por Livewire. La reactivación no modifica reglas clínicas ni da por válidas credenciales profesionales ausentes.

## 10. Decisión registrada: apartados separados

Existen dos tipos de persona que no deben confundirse:

- **Trabajador:** Administración o una de las seis profesiones. Su ficha laboral se diferencia de la cuenta de acceso; campos de matrícula/área/fecha de ingreso solo según el contrato pertinente. La opción B adoptada en la sección 11 exige crear ambas durante el alta, conservando sus entidades distintas.
- **Familiar:** cuenta vinculada a contacto y residente mediante el proceso permitido. No debe exigir matrícula, profesión o relación de personal para completar un alta. Crear la cuenta no publica automáticamente información clínica ni autoriza cualquier vínculo.

**Respuesta expresa de la propietaria, 25/10/2026:** «OPCION 1», tras aclarar que se elegían dos apartados del menú, cada uno con su listado y formulario.

| Apartado adoptado | Personas y finalidad | Entrada de alta prevista |
|---|---|---|
| Personal | Administración y los seis roles profesionales; ficha laboral, documentación y acceso autorizado | «Registrar trabajador», con formulario propio del flujo laboral |
| Familiares y contactos | Contactos y vínculos con residentes; cuenta Familiar cuando corresponda | «Registrar familiar o contacto», con formulario propio de identidad, contacto y vínculo autorizado |

No se adopta un apartado único «Cuentas» como entrada de estas altas. La separación de navegación reutilizará las entidades y capacidades existentes; no crea un segundo directorio de identidades ni exige cuentas a todos los contactos. Ambos apartados deben prevenir altas duplicadas y permitir localizar una persona existente antes de crear otra.

Los nombres de las acciones anteriores describen la organización funcional prevista, no botones ya implementados. Esta decisión no amplía publicación clínica, permisos de documentos, alcance sobre residentes ni estructura de BDD.

## 11. Decisión registrada: cuenta obligatoria durante el alta laboral

**Respuesta expresa de la propietaria, 25/10/2026:** «B», tras presentar la alternativa «Todo trabajador debe tener cuenta desde el alta».

Para las nuevas altas de trabajadores realizadas por Gerencia, el formulario reúne ficha laboral y cuenta de acceso. La identidad laboral y la cuenta permanecen como entidades distintas, vinculadas mediante las relaciones existentes. No se ofrece «sin cuenta por ahora» en este flujo.

- Correo y rol autorizado son necesarios para completar el alta, además de los datos laborales exigidos por el contrato aplicable.
- Si faltan datos necesarios de cualquiera de las dos partes, se muestran errores junto a los campos y no se informa un alta completada. La documentación pendiente se rige por la excepción con plazo adoptada en la sección 12; no se considera requisito satisfecho por estar pendiente.
- La creación de ficha y cuenta vinculadas debe ser atómica: una falla no puede dejar una sola parte creada por esta operación. Los reintentos no deben duplicar identidades, cuentas o vínculos.
- Antes de crear, comprobar identidad y correo existentes; resolver una coincidencia mediante el flujo autorizado, sin crear una segunda persona ni vincularla automáticamente por coincidencia de nombre.
- Tener cuenta no acredita competencia profesional, no habilita actuaciones clínicas por sí mismo ni reemplaza los requisitos documentales de cada operación. El estado inicial ACTIVO se adoptó después, en la sección 14; no define por sí solo requisitos nuevos de credenciales.
- Se mantienen los siete roles laborales destino de Gerencia y sus exclusiones de Gerencia/Superadministración. Familiar conserva su apartado y proceso propios; esta decisión no obliga a todo contacto a tener cuenta.
- El alcance es el alta nueva documentada: no impone crear cuentas retroactivas para todo registro de personal preexistente ni modifica la opcionalidad general del modelo.

**Estado:** decisión funcional adoptada; integración y pruebas pendientes. No se modifica el esquema, se ejecutan altas ni se instalan permisos por documentarla.

## 12. Decisión registrada: documentación pendiente con plazo

**Respuesta expresa de la propietaria, 25/10/2026:** «A con un tiempo delimitado para que completen documentacion».

Se permite finalizar el alta laboral conjunta con documentos pendientes que admitan plazo, indicando cuáles faltan y una fecha límite para completarlos. Se mantienen los datos obligatorios de identidad, ficha y cuenta y el mínimo documental inmediato ratificado en la sección 14. La falta documental no debe ocultarse ni mostrarse como expediente completo. Un documento opcional ausente no se convierte en obligación vencida por esta decisión.

El detalle del trabajador deberá permitir reconocer los documentos requeridos, los entregados, los pendientes y la fecha límite aplicable. La entrega de un archivo y su validación son hechos diferentes; un archivo recibido no se presenta como validado sin la operación correspondiente.

**Plazo adoptado, 25/10/2026:** ante la pregunta «¿Cuánto tiempo tendrán desde la fecha de alta: 7, 15 o 30 días calendario, u otro plazo?», la propietaria respondió «7». Son **siete días calendario desde la fecha del alta**, incluidos fines de semana y feriados. La fecha límite se obtiene sumando siete días a la fecha de alta registrada en la zona institucional America/La_Paz; no se utiliza una fecha de ingreso laboral distinta como inicio del plazo.

El plazo corresponde a completar la entrega documental: la recepción del archivo y su posterior validación deben distinguirse. No atribuir al trabajador una demora de revisión como si no hubiera entregado el documento. El detalle debe mostrar la fecha límite y los documentos todavía pendientes sin confundirlos con los entregados pendientes de revisión.

**Vencimiento adoptado, 25/10/2026:** la propietaria eligió «A» ante la alternativa «Avisar a Gerencia: marcar la documentación como vencida y dejar que Gerencia decida si suspende el acceso o concede una prórroga». Cuando vence el plazo y faltan entregas, se señala la documentación vencida y se avisa a Gerencia. No se suspende automáticamente la cuenta ni se cambia el estado laboral.

Gerencia decide el seguimiento del caso y puede conceder una prórroga o suspender manualmente el acceso dentro del alcance de cuentas ya aprobado. La prórroga debe permitir conocer el plazo anterior, la nueva fecha límite, el motivo, quién la concede y cuándo; no ocultar el vencimiento ni sustituirlo por una entrega inexistente. La suspensión sigue siendo una operación autorizada y confirmada, distinta de una baja laboral.

**Límite de prórroga adoptado, 25/10/2026:** la propietaria respondió «a» a la alternativa «una sola prórroga de siete días adicionales». Gerencia puede conceder **una única prórroga de siete días calendario**, añadidos a la fecha límite original del alta. El plazo inicial es de siete días y, si se concede la prórroga, la nueva fecha límite queda a catorce días calendario desde el alta; no se reinicia el cómputo en la fecha de autorización. El límite corresponde al expediente documental de esa alta, no a cada archivo por separado. No se concede automáticamente ni se permiten prórrogas sucesivas. Si vence también la prórroga con entregas pendientes, se mantiene el seguimiento y aviso a Gerencia, sin suspensión automática ni baja laboral.

**Avisos adoptados, 25/10/2026:** la propietaria responde «A» a «avisar al trabajador y a Gerencia dos días antes y al vencer el plazo», dentro del sistema. El recordatorio previo se muestra dos días calendario antes de la fecha límite vigente y el aviso de vencimiento al superar el plazo con entregas pendientes. Se aplica al plazo inicial y a la fecha de la única prórroga, si Gerencia la concede.

El aviso identifica al trabajador, la fecha límite y los documentos sin entregar, con acceso al detalle autorizado. El trabajador recibe solo avisos propios; Gerencia consulta los correspondientes al personal de su alcance. Debe evitar duplicaciones del mismo aviso, tipo y fecha límite, y comprobar pendientes antes de generarlo. Si ya se entregó todo, no genera una nueva advertencia por falta de entrega. Los documentos recibidos pendientes de revisión no se señalan como no entregados. Al conceder una prórroga se conserva el vencimiento anterior y se actualiza el seguimiento al nuevo plazo, sin borrar el historial ni seguir avisando como si la fecha anterior continuara vigente.

Esta decisión autoriza el comportamiento de notificaciones internas, no envíos por correo, SMS o servicios externos. El horario técnico de ejecución se definirá al integrar la planificación del sistema; todavía no se han programado ni enviado avisos.

| Etapa | Comportamiento funcional adoptado |
|---|---|
| Alta | Ficha laboral y cuenta juntas; se admiten documentos pendientes identificados |
| Plazo inicial | Siete días calendario desde la fecha del alta |
| Carga y revisión | Trabajador y Gerencia cargan; Gerencia revisa, con autoría diferenciada |
| Recordatorio | Aviso interno al trabajador y Gerencia dos días antes, si hay entregas pendientes |
| Vencimiento | Aviso interno y documentación vencida; decisión manual de Gerencia, sin suspensión automática |
| Prórroga | Una sola de siete días adicionales al vencimiento original, con motivo e historial |
| Nuevo plazo | Se aplican recordatorio y aviso de vencimiento a la fecha prorrogada |

**Estado:** flujo de alta documental, mínimo inmediato, plazos, carga/revisión, avisos y composición del formulario definidos funcionalmente. El detalle del catálogo por función y estados de revisión debe contrastarse al implementar. Especificación documental, sin nuevos estados SQL, columnas, permisos instalados o cambios en la aplicación; pruebas de funcionamiento pendientes.

## 13. Decisión registrada: carga compartida y revisión gerencial

**Respuesta expresa de la propietaria, 25/10/2026:** «c», correspondiente a «Ambos pueden subirlos; queda registrado quién cargó cada archivo y Gerencia los revisa».

| Actor | Entrada prevista | Alcance adoptado |
|---|---|---|
| Trabajador | Documentación propia desde su perfil | Subir sus propios archivos; no documentos de otros trabajadores ni validación de los suyos |
| Gerencia | Ficha del trabajador en Personal | Subir documentos recibidos y revisar los archivos del expediente dentro de su alcance autorizado |

Cada carga conserva trabajador titular, tipo de documento, usuario que efectivamente cargó el archivo y fecha/hora. Que Gerencia suba un documento no convierte al trabajador en autor de esa carga ni equivale a validarlo. La revisión registra por separado revisor, fecha y decisión mediante las capacidades existentes o la extensión específicamente autorizada que se requiera; esta decisión no aprueba DDL.

Los archivos quedan sujetos a autorización de lectura/descarga y validación técnica del flujo existente. El trabajador no puede modificar titular, autoría o resultado de revisión mediante identificadores manipulados. Correcciones y nuevas entregas conservan la trazabilidad del archivo anterior; no sobrescribir historia para aparentar que una entrega fue puntual.

**Estado:** carga compartida y revisión gerencial aprobadas funcionalmente; implementación y pruebas pendientes. No se han concedido permisos instalados ni modificado archivos o registros de trabajadores.

## 14. Formulario: cuatro pasos adoptados

**Respuesta expresa de la propietaria, 25/10/2026:** «A»: identidad y contacto → información laboral → cuenta → documentación, en cuatro pasos guiados, con avance/retroceso y guardado al finalizar. [Campos, botones, diferencias de código y aceptación](03-ALTA-TRABAJADOR.md).

La composición está adoptada; la implementación actual mantiene siete pasos y mensajes de 48 horas por corregir.

**Mínimo documental ratificado, 25/10/2026:** la propietaria respondió «A» a mantener cédula, fotografía y CV obligatorios al alta, además de título para las profesiones y matrícula para Medicina. La entrega posterior autorizada se aplica a los demás documentos requeridos que admitan plazo. Véase la matriz de alcance en [Alta de trabajador](03-ALTA-TRABAJADOR.md#7-mínimo-documental-inmediato-ratificado).

Que estos archivos deban adjuntarse al alta no equivale a declararlos revisados, auténticos o suficientes para actuar clínicamente.

**Estado inicial adoptado, 25/10/2026:** la propietaria respondió «A» a «activa al finalizar el alta, con los permisos y controles correspondientes». La nueva cuenta del trabajador queda ACTIVA tras completarse correctamente el alta conjunta, con el mínimo documental inmediato y el rol permitido. No requiere otra activación manual por tener documentos que admitan entrega posterior. Continúan el seguimiento de siete días, la revisión documental y la autorización contextual de cada operación. No se reactivan cuentas preexistentes ni se da por validada la documentación como efecto lateral. [Detalle y aceptación](03-ALTA-TRABAJADOR.md#8-estado-inicial-de-acceso-adoptado).

**Credenciales adoptadas, 25/10/2026:** 15A establece contraseña temporal con cambio obligatorio al primer ingreso; 16B permite a Gerencia iniciar/reemitir recuperación mediante enlace para cuentas de su alcance, sin conocer o elegir la nueva contraseña. Son operaciones distintas. [Contrato y pruebas pendientes](03-ALTA-TRABAJADOR.md#9-credenciales-iniciales-y-recuperación-adoptadas).
