---
title: "RememberMind — Baseline funcional congelado de roles"
status: CURRENT
version: "2.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: true
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> La extensión BDD V2.2 no otorga permisos nuevos por inferencia. Ver [índice BDD](../base-de-datos/README.md).

# RememberMind — Baseline funcional congelado de roles

**Estado:** CONGELADO
**Aprobación:** modificación expresamente aprobada por la responsable del proyecto
**Ámbito:** roles, permisos, navegación, dashboards y flujos funcionales
**Persistencia:** Spatie Permission; no se crean tablas empresariales de roles
**BDD:** núcleo Operativo V2.1 de 70 tablas congeladas, más extensiones explícitamente aprobadas; consultar el índice BDD para inventario vigente.

## Modelo funcional

**Personal y documentos — respuestas R01–R03 del 25/10/2026:** la propietaria eligió R01A añadiendo un campo para pedir documentación por rol, R02A y R03A. El mínimo inmediato permanece obligatorio; otros respaldos son opcionales por defecto, con solicitudes expresas por rol/destinatarios. Gerencia puede anular documentos laborales erróneos con motivo, autor/fecha y archivo/historia conservados; no documentos clínicos ni borrado. Puede cambiar personal entre ACTIVO/INACTIVO con motivo/fecha, gestionando acceso por separado; Administración revisa asignaciones afectadas sin eliminar el pasado. [Constancia literal](../especificacion-funcional/vistas/gerencia/07-DECISIONES-RESIDUALES-PERSONAL.md#respuestas-adoptadas-el-25102026) y [solicitud por rol](../especificacion-funcional/vistas/gerencia/08-SOLICITUD-DOCUMENTAL-POR-ROL.md). Adopción funcional, no aprobación de DDL/catálogos concretos o instalación de permisos; soporte relacional, integración y pruebas pendientes.

**Decisiones conjuntas de Gerencia 15–29 — aprobación expresa del 25/10/2026:** la propietaria respondió `15A, 16B, 17B, 18A, 19B Y A, 20A, 21A, 22A, 23A, 24A Y B, 25A, 26B Y C, 27B, 28A, 29B`. Se adopta el siguiente alcance adicional al ya registrado; [respuestas literales y matriz completa](../especificacion-funcional/roles/GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos).

| Ámbito | Decisión vigente del bloque 15–29 |
|---|---|
| Credenciales laborales | Contraseña temporal y cambio obligatorio en el primer ingreso; no se adopta enlace para establecer la contraseña inicial |
| Recuperación de cuentas | Gerencia puede iniciar/reemitir enlace de recuperación para sus ocho roles destino autorizados, sin ver ni elegir la nueva contraseña, cambiar roles o reactivar cuentas suspendidas |
| Vínculos familiares | Gerencia puede registrar/corregir el vínculo de contacto con residente, parentesco y trazabilidad. No concede publicación clínica o autorización de salidas por implicación |
| Cuenta Familiar | Activa al completar datos y contar con vínculo autorizado; acceso limitado al contenido permitido de residentes vinculados |
| Comunicación de planificación | Avisos internos de cambios confirmados a Administración y trabajadores afectados; no envíos externos ni cambio del reparto de operación diaria |
| Reportes | Consulta y exportación PDF/CSV de los mismos indicadores gerenciales agregados, con permisos/contexto y auditoría. No incluye extracción clínica, archivos laborales o listados nominales masivos |
| Navegación y presentación | Contactos por residente y por persona; Áreas y equipo unificados; planificación semanal; cobertura en tabla; ocupación mapa/tabla; alertas en una bandeja; Galería directa; Pendientes en Inicio; residentes en tarjetas con tabla alternativa |

Estas decisiones aprueban funcionalidad y límites, no verifican implementación ni instalan permisos o DDL. Las ampliaciones de recuperación, vínculos y exportación quedan expresamente autorizadas en este alcance; no deben volver a presentarse como pendientes de decisión. La integración, validación de estructura y pruebas de cada operación siguen pendientes.

**Estado inicial de cuenta laboral — decisión expresa del 25/10/2026:** la propietaria eligió «A»: la nueva cuenta queda ACTIVA al completarse correctamente el alta laboral conjunta, respetando el mínimo documental inmediato, rol permitido y autorización contextual. No requiere activación manual adicional ni implica validación documental, competencia clínica o reactivación de cuentas existentes. [Alcance y primer acceso pendiente](../especificacion-funcional/vistas/gerencia/03-ALTA-TRABAJADOR.md#8-estado-inicial-de-acceso-adoptado). La Action actual ya asigna ese estado; integración y pruebas del circuito completo pendientes. Sin cambios de aplicación, estructura o permisos instalados.

**Mínimo documental del alta laboral — decisión expresa del 25/10/2026:** la propietaria eligió «A»: adjuntar cédula, fotografía y CV al alta; además título/certificado de formación para las seis profesiones y matrícula para Medicina. Solo los demás documentos requeridos que admitan plazo siguen el plazo de siete días y la única prórroga aprobada; los opcionales no se convierten en obligaciones vencidas. Adjuntar no equivale a validar ni acredita competencia clínica. [Matriz y alcance](../especificacion-funcional/vistas/gerencia/03-ALTA-TRABAJADOR.md#7-mínimo-documental-inmediato-ratificado). Implementación del circuito completo y pruebas pendientes; sin cambios estructurales o de permisos instalados.

**Avisos documentales — decisión expresa del 25/10/2026:** la propietaria eligió «A»: avisar dentro del sistema al trabajador y a Gerencia dos días antes y al vencer el plazo, si faltan entregas. Se aplica también al plazo prorrogado, con deduplicación y alcance propio para el trabajador y autorizado para Gerencia. No incluye envíos externos ni suspensión automática. [Flujo consolidado](../especificacion-funcional/vistas/gerencia/02-PERSONAL.md#12-decisión-registrada-documentación-pendiente-con-plazo). Implementación y pruebas pendientes; sin cambios estructurales o permisos instalados.

**Prórroga documental de Gerencia — decisión expresa del 25/10/2026:** la propietaria eligió «a»: una sola prórroga de siete días adicionales. Gerencia puede concederla con motivo y trazabilidad, sumando siete días calendario al vencimiento original del expediente de alta; catorce días desde el alta en total. No es automática ni renovable sucesivamente. Se mantiene el aviso y decisión gerencial al vencer, sin suspensión automática. [Constancia](../especificacion-funcional/vistas/gerencia/02-PERSONAL.md#12-decisión-registrada-documentación-pendiente-con-plazo). Implementación y pruebas pendientes; sin cambios estructurales.

**Carga de documentos laborales — decisión expresa del 25/10/2026:** la propietaria eligió «c»: trabajador y Gerencia pueden subir archivos; el trabajador solo sobre su documentación propia y Gerencia dentro de su alcance de personal. Gerencia los revisa. Se conserva quién cargó cada archivo y cuándo, separado de titular y revisión; subir no equivale a validar. [Constancia y alcance](../especificacion-funcional/vistas/gerencia/02-PERSONAL.md#13-decisión-registrada-carga-compartida-y-revisión-gerencial). Implementación y pruebas pendientes, sin DDL ni permisos instalados por esta constancia.

**Documentación pendiente en altas de Gerencia — decisiones expresas del 25/10/2026:** la propietaria eligió «A con un tiempo delimitado para que completen documentacion» y precisó «7» ante las alternativas de días calendario desde el alta. El alta conjunta puede completarse con documentos pendientes identificados y plazo de entrega de **siete días calendario desde la fecha del alta**; se mantienen los datos obligatorios de persona y cuenta. La entrega se distingue de su validación posterior. Al vencer, eligió «A»: señalar documentación vencida y avisar a Gerencia, sin suspensión automática. Gerencia decide el seguimiento y puede conceder la única prórroga de siete días adicionales o suspender manualmente dentro del alcance ya autorizado; no implica baja laboral ni acredita competencia profesional. Trabajador y Gerencia cargan; Gerencia revisa. Los avisos son internos, dos días antes y al vencer, según las decisiones específicas de este apartado. [Constancia y límites](../especificacion-funcional/vistas/gerencia/02-PERSONAL.md#12-decisión-registrada-documentación-pendiente-con-plazo). Implementación pendiente; sin cambios de esquema o permisos instalados.

**Alta laboral conjunta de Gerencia — decisión expresa del 25/10/2026:** la propietaria eligió «B»: las nuevas altas de trabajadores de este flujo incluyen obligatoriamente ficha laboral y cuenta vinculada, con correo y rol autorizado, en una operación atómica. No se ofrece registro sin cuenta inicial. La decisión no modifica la opcionalidad general de la relación personal/usuario, no exige crear cuentas retroactivas ni acredita competencia clínica. Familiar mantiene su proceso separado. [Constancia, alcance y requisitos de integridad](../especificacion-funcional/vistas/gerencia/02-PERSONAL.md#11-decisión-registrada-cuenta-obligatoria-durante-el-alta-laboral). Integración y pruebas pendientes; sin cambios de estructura o permisos instalados.

**Mantenimiento de cuentas — decisión expresa del 25/10/2026:** tras definir los ocho roles destino del alta, la propietaria eligió «Editar sus datos y activar/suspender su acceso». Gerencia recibe ese alcance funcional sobre las mismas cuentas de Administración, los seis roles profesionales y Familiar; no se amplía a cuentas de Gerencia o Superadministración, aunque tengan roles adicionales. Editar datos no incluye reasignar roles, editar el catálogo de permisos o eliminar. Suspender acceso no equivale a baja laboral ni borra historia. La activación mantiene los requisitos de identidad/competencia/vínculo de cada operación y no publica datos familiares por implicación. Esta decisión amplía de forma expresa el alcance de mantenimiento que la decisión de alta por sí sola no concedía. [Constancia y límites](../especificacion-funcional/vistas/gerencia/02-PERSONAL.md#9-decisión-registrada-mantenimiento-de-datos-y-acceso). Implementación y pruebas de la ampliación pendientes; sin cambios de permisos instalados o esquema en esta entrega documental.

**Alta de cuentas — decisión expresa del 25/10/2026:** la propietaria precisó: «Gerencia crea Administración, los seis roles profesionales y Familiar; Superadministración crea Gerencia y Superadministración», manteniendo su instrucción previa de que Superadministración crea todos. Gerencia queda autorizada funcionalmente para crear cuentas de ocho roles destino: `ADMINISTRADOR`, `ENFERMEROS`, `MEDICO GENERAL/GERIATRA`, `PSICOLOGO/A`, `NUTRICIONISTA`, `FISIOTERAPEUTA`, `PEDAGOGO` y `FAMILIAR`. No puede crear `GERENTE` ni `SUPERADMINISTRADOR`; Superadministración puede crear los diez. Esta extensión sustituye exclusivamente la reserva anterior del alta de cuentas y no autoriza modificar el catálogo de permisos, reasignar roles de cuentas preexistentes, suspender o eliminar por implicación. El alta de Familiar mantiene su identidad de contacto/vínculo, sin convertirlo en personal. La implementación y sus pruebas siguen pendientes; no se ejecutó seeder ni se cambió estructura al registrar esta decisión. [Definición progresiva y constancia literal](../especificacion-funcional/vistas/gerencia/02-PERSONAL.md#7-decisión-de-la-propietaria-alta-de-cuentas-por-rol).

**Lectura IADL para Petersen — decisión expresa 19/10/2026:** Medicina y
Psicología reciben `controles_funcionales_iadl.ver` para los residentes de su
alcance; Superadministración conserva consulta técnica. La Policy exige cuenta
activa, permiso y competencia profesional donde corresponde. No concede
escritura IADL ni acredita C03 sin valoración original y soportes aptos.
[Decisión y despliegue](../sistema-experto/PROPUESTA_LECTURA_IADL_PETERSEN_2026_10_19.md).

**Petersen original — decisión expresa 19/10/2026:** Medicina/Psicología
registran y corrigen solo sus cadenas con `valoraciones_petersen.ver`, `.crear`
y `.rectificar`; Superadministración consulta con `.ver`. No se concede acceso
a otras fuentes por implicación ni se alteran las revisiones posteriores.
[Contrato aprobado](../sistema-experto/PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md).

**Gobierno de reglas — decisión expresa 18/10/2026:** Medicina y Psicología gestionan y aprueban con `reglas_expertas.ver`, `.gestionar` y `.revisar`, cuenta/personal activos y competencia real compatible. Superadministración consulta con `.ver`; los permisos de escritura no se conceden por su excepción técnica ni por el editor de roles. La firma del conocimiento es distinta de las dos revisiones de una evaluación. [Contrato y alcance aprobado](../sistema-experto/PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md).

**Decisión de la propietaria — 18/10/2026:** guardar ejecuciones observacionales corresponde únicamente a Medicina y Psicología, mediante `evaluaciones_expertas.ejecutar_observaciones`, profesión propia compatible, cuenta/personal activos y Policy contextual. Enfermería consulta; Superadministración no recibe este permiso por su excepción técnica general. [Alcance exacto](../sistema-experto/PROPUESTA_CONSERVACION_OBSERVACIONAL_V1.md). No habilita diagnóstico ni clasificación DCL.

- `SUPERADMINISTRADOR` → máxima autoridad técnica y supervisión global del sistema.
- `GERENTE` → dirección institucional y gestión del personal.
- `ADMINISTRADOR` (nombre visual: **Administración**) → coordinación de la operación diaria.
- Profesionales → atención y registro clínico/interdisciplinario dentro de su competencia.
- `FAMILIAR` → acceso únicamente a residentes vinculados e información autorizada.

Roles profesionales vigentes: `ENFERMEROS`, `MEDICO GENERAL/GERIATRA`, `PSICOLOGO/A`, `PEDAGOGO`, `NUTRICIONISTA` y `FISIOTERAPEUTA`. `VOLUNTARIO` permanece fuera de alcance.

## Separación obligatoria

| Nivel | Finalidad | Escritura propia | No obtiene por su rol |
| --- | --- | --- | --- |
| Superadministrador | Control técnico, cuentas, roles, seguridad, auditoría y supervisión integral | Técnica, administrativa y operativa conforme a reglas de negocio | Escritura clínica por el solo rol de Superadministrador |
| Gerente | Personal, áreas, turnos maestros, planificación, cobertura e indicadores | Institucional y de RR. HH. no financiero | Diagnóstico, prescripción, notas o valoraciones clínicas |
| Administración | Preadmisión, admisión, alojamiento, jornadas, asignaciones, documentos, visitas, actividades y alertas | Operativa y administrativa | Gestión técnica de cuentas, dirección de RR. HH. o escritura clínica |
| Profesionales | Atención y registro por disciplina | Clínica/interdisciplinaria según competencia, permiso y contexto | Facultades de otras disciplinas |
| Familiar | Consulta autorizada | Ninguna clínica | Historia clínica sensible o residentes no vinculados |

## Reglas de autorización

Toda operación sensible exige conjuntamente:

`sesión autenticada + cuenta ACTIVA + permiso Spatie explícito + Policy/servicio contextual + regla de negocio + alcance/relación/competencia`.

La visibilidad del sidebar no reemplaza la autorización backend. Superadministrador conserva lectura global y administración amplia en dominios técnicos, administrativos y operativos, pero la lectura nunca concede mutación clínica. Gerente y Administración tampoco reciben escritura clínica por el nombre de su rol.

La escritura clínica exige siempre rol profesional competente, permiso explícito, Policy o servicio contextual, personal activo y regla de negocio. Un usuario con `SUPERADMINISTRADOR + MEDICO GENERAL/GERIATRA` puede ejecutar actos médicos por su rol profesional; el rol `SUPERADMINISTRADOR` no constituye un bypass clínico.

## Flujos congelados

- Preadmisión: `PENDIENTE → APROBADA | RECHAZADA`. Aprobar no crea residente.
- Admisión: preadmisión aprobada → preparación documental y contactos → consentimiento/seguro → cama disponible → confirmación → residente admitido.
- No existe alta directa de residentes fuera de la formalización de admisión.
- Gerencia define personal, pertenencia institucional y planificación maestra; Administración abre la jornada y realiza asignaciones operativas; el profesional ejecuta y registra la atención.
- Alertas: generación → reconocimiento → asignación/coordinación → seguimiento → atención → cierre, preservando `eventos_alerta`.
- La prescripción ordinaria corresponde exclusivamente a `MEDICO GENERAL/GERIATRA`; Enfermería administra medicación, pero no prescribe.

## Navegación y dashboards

- Superadministrador: sistema, institución, residencia, operación, lectura del expediente clínico y reportes globales. El residente permanece como núcleo del expediente; no se convierte cada tabla en una opción del sidebar.
- Gerente: personal, áreas, turnos, planificación, cobertura, consulta administrativa de residentes/ocupación, alertas e incidentes relevantes y reportes gerenciales.
- Administración: admisiones, residentes administrativos, alojamiento, jornadas, asignaciones, documentos, consentimientos, seguros, visitas, actividades, alertas e incidentes.
- Profesionales: residentes asignados, pendientes y funciones propias de su competencia.
- Familiar: información expresamente autorizada del residente vinculado.

Cada nivel utiliza un perfil de dashboard distinto. Los accesos y métricas se filtran por permiso; ninguna opción se protege solamente mediante CSS.

## SUPERADMINISTRADOR

- **Lectura:** total sobre los dominios reales de RememberMind y la auditoría técnica.
- **Administración:** global en sistema, institución, residencia y operación, siempre respetando admisión formal, ocupación única, integridad referencial, estados e invariantes.
- **Escritura clínica:** solo mediante competencia profesional adicional + permisos + Policy/servicio + contexto.
- **Auditoría:** consulta total; las entradas históricas de Spatie Activitylog no se editan ni eliminan para ocultar acciones.

## Modo «Ver como rol»

`Ver como rol` es una previsualización temporal de experiencia almacenada en sesión mediante `preview_role`.

- No es impersonación.
- No cambia `Auth::user()`, `cod_usuario`, roles ni permisos persistidos.
- No utiliza `loginUsingId()` ni atribuye acciones a otro usuario.
- Cambia exclusivamente la presentación del sidebar y dashboard hacia el rol seleccionado.
- Muestra un banner permanente y claramente visible.
- Es estrictamente de solo lectura: oculta o deshabilita controles mutables y un middleware bloquea solicitudes backend no seguras mientras el modo está activo.
- Alterar manualmente la sesión no concede permisos: solo un usuario autenticado con rol real `SUPERADMINISTRADOR` puede mantener el estado de preview.
- Salir del modo elimina el estado temporal y restaura la experiencia completa de Superadministrador.

## Gobierno de datos

Este baseline es exclusivamente funcional. No agrega, elimina, renombra ni modifica tablas, columnas, PK, FK, cardinalidades o restricciones de las 70 tablas operativas V2.1. Los datos de roles y permisos pertenecen a las tablas técnicas de Spatie, excluidas del conteo operativo. Cualquier necesidad futura de alterar el esquema debe detenerse y seguir el procedimiento formal de aprobación del baseline de base de datos.
