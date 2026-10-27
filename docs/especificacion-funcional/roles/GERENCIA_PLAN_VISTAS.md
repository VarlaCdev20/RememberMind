---
title: "Gerencia: definición progresiva de vistas y operaciones"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: APPROVED_BASELINE_AND_STATIC_NAVIGATION_REVIEW
runtime_verified: false
---

# Gerencia: definición paso a paso

## Alcance y fuentes

La propietaria solicitó detallar vistas, formularios, accesos y controles, inicialmente con preguntas por paso. El 25/10/2026 pidió recibir todas las preguntas pendientes juntas y respondió las quince. El [cuestionario 15–29 y su matriz de respuestas](GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos) conserva opciones históricas y elecciones adoptadas. Este documento conserva el mapa inicial y su historial; no cambia el menú, los permisos ni la BDD.

- [Ficha funcional de Gerencia](gerencia.md).
- [Competencias aprobadas](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md): Gerencia dirige personal, áreas y planificación; Administración abre jornadas y realiza asignaciones operativas.
- [Navegación observada](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php): método `getGerenteSidebar`.
- [Dashboard de referencia](../../arquitectura/DASHBOARDS_POR_ROL.md).
- [Auditoría actual](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md): H02/H03/H04 conservan defectos de horarios/ocupación.

## Paso 1: mapa inicial de vistas

Las diez entradas observadas no equivalen a diez pantallas distintas: «Personal por área» y «Áreas» apuntan al mismo destino. El mapa agrupa esas funciones; la composición definitiva se definirá en cada paso.

| Vista o superficie | Finalidad | Tipo de acción a detallar | Formulario o interacción prevista por el alcance existente |
|---|---|---|---|
| Inicio de Gerencia | Saber cómo está la institución y qué necesita revisión | Consulta y acceso a tareas autorizadas | Filtros de período/fecha y enlaces a los datos que sustentan cada indicador; campos concretos por revisar |
| Personal institucional | Consultar y gestionar trabajadores dentro de su competencia | Gestión de personal | Alta/edición/estado y pertenencia mediante las entradas existentes; campos, requisitos y transiciones se contrastarán antes de cerrar la ficha |
| Áreas y personal por área | Ver organización y distribución del equipo | Gestión de áreas y consulta de distribución | Formularios existentes de área; no duplicar pantalla por tener dos entradas de menú |
| Turnos y planificación | Mantener planificación maestra | Gestión de planificación autorizada | Turno, planificación/horario por la entrada vigente; ventana completa y conflictos deben validarse; no convertir lectura en permiso de asignación |
| Cobertura | Comprobar quién está asignado y en qué contexto | Consulta gerencial | Filtros y detalle de jornada/área/personal; no atribuir cobertura futura a una sola jornada |
| Residentes | Consultar información administrativa necesaria para dirección | Lectura | Búsqueda/filtros y resumen administrativo; sin alta directa o captura clínica por el rol gerencial |
| Ocupación | Conocer disponibilidad y ocupación de camas | Lectura | Filtros y detalle de alojamiento; corregir H04, no ampliar permisos para saltar el 403 |
| Alertas relevantes | Consultar incidencias pertinentes a la dirección | Lectura conforme a contenido y alcance | Filtros y detalle autorizado; lectura no concede cierre, atención clínica o modificación del registro original |
| Reportes gerenciales | Consultar indicadores y reportes institucionales | Consulta; exportación solo si existe autorización específica | Filtros del reporte vigente; no conceder descarga por disponer de la vista previa |
| Galería del centro | Consultar/gestionar fotografías institucionales en su alcance aprobado | Capacidad complementaria ya presente | Reutilizar el formulario existente y su permiso; no confundir fotografías institucionales con documentos clínicos |

## Límites ya establecidos

- No edición clínica, diagnóstico o prescripción por el solo rol GERENTE.
- No gestión técnica global de usuarios/roles/permisos por dirigir personal.
- No ampliar el sistema a nómina/finanzas por inferencia desde «Gerencia».
- Las autorizaciones de registro se comprueban en backend; un botón visible no las prueba.
- Ningún formulario se declara operativo por describirlo aquí. Cada ficha posterior incluirá fuentes, campos, acciones, efectos y pruebas.

## Decisión registrada del paso 1

**¿Qué debe priorizar Inicio de Gerencia?**

Opciones presentadas a la propietaria el 25/10/2026:

1. Personal, turnos y cobertura primero; luego ocupación y alertas — recomendación inicial.
2. Resumen equilibrado de personal, ocupación y alertas.
3. Alertas e incidencias primero; después personal y ocupación.

**Respuesta de la propietaria, 25/10/2026:** «Resumen equilibrado de personal, ocupación y alertas.»

**Estado:** prioridad adoptada. Los tres ámbitos tendrán jerarquía equivalente. No se adopta la recomendación inicial de priorizar personal. El resto de componentes e interacciones se detallará progresivamente en [Inicio de Gerencia](../vistas/gerencia/01-INICIO.md); la elección no modifica permisos ni confirma implementación.

## Orden de desarrollo documental

**Planificación/cobertura e imágenes desarrolladas el 26/10/2026:** [calendario y datos](../vistas/gerencia/14-PLANIFICACION-DEL-EQUIPO.md), [tabla/detalle de cobertura](../vistas/gerencia/15-COBERTURA-DEL-EQUIPO.md), [28 escenarios](../vistas/gerencia/16-ACEPTACION-PLANIFICACION-COBERTURA.md) y [dos propuestas de imágenes detalladas](../vistas/gerencia/17-DISENO-PLANIFICACION-COBERTURA.md). Reutiliza 21A/22A/23A. Se corrige documentalmente el destino conceptual de Cobertura: turnos-enfermeria es catálogo, no la consulta nueva. Se registran vigencias/fecha final no conservadas, grupos serializados y conflictos nocturnos como brechas de implementación. Imágenes ficticias, sin aceptación visual definitiva ni cambios de aplicación/BDD; siguiente ficha: Residentes y ocupación según 24A-B/29B.

**Áreas y equipo desarrollado el 26/10/2026:** [ficha funcional](../vistas/gerencia/12-AREAS-Y-EQUIPO.md), diez campos, once acciones y [veinte escenarios](../vistas/gerencia/13-ACEPTACION-AREAS-EQUIPO.md). Reutiliza entrada 20A y separa área, asignaciones por jornada y equipo permanente no representado. Se documentan desfase de estados ACTIVO/ACTIVA, responsable derivado sin contexto temporal y exportaciones nominales no incluidas en 26B/C. DA01 identifica política de desactivación pendiente y DA02 el grano permanente si se requiere. Sin implementación, cambios estructurales o ejecución de suites; siguiente ficha: planificación y cobertura según 21A/22A/23A.

**Familiares y contactos desarrollado el 26/10/2026:** [dos presentaciones, 21 campos y 12 acciones](../vistas/gerencia/09-FAMILIARES-Y-CONTACTOS.md), [formularios y privacidad](../vistas/gerencia/10-FORMULARIOS-FAMILIARES-PRIVACIDAD.md) y [24 escenarios](../vistas/gerencia/11-ACEPTACION-FAMILIARES-CONTACTOS.md). Reutiliza decisiones 17B/18A/19B-A; no adopta permisos de información/salida desde defaults de código. Se registran diferencias entre entradas HTTP/Livewire, parentesco 100 frente a SQL 40 y capacidad histórica pendiente. Sin implementación, DDL o pruebas de aplicación ejecutadas; siguiente ficha: Áreas y equipo conforme a 20A.

**R01–R03 adoptadas el 25/10/2026:** R01A con petición de documentación por rol, R02A y R03A. Se conserva el mínimo inmediato, los adicionales son opcionales por defecto y se especifica [solicitud por rol](../vistas/gerencia/08-SOLICITUD-DOCUMENTAL-POR-ROL.md). Gerencia puede anular documentos laborales y cambiar ACTIVO/INACTIVO del personal con motivo y trazabilidad, gestionando cuenta por separado y continuidad con Administración. [Respuesta literal y alcance](../vistas/gerencia/07-DECISIONES-RESIDUALES-PERSONAL.md#respuestas-adoptadas-el-25102026). Las tres preguntas ya no están pendientes; siguen pendientes implementación, soporte de datos y pruebas. Aceptación: 36 escenarios base y ocho de solicitud, todos especificados y no ejecutados.

**Especificación de Personal desarrollada el 25/10/2026:** [índice de fichas](../vistas/gerencia/README.md), con 23 campos/destinos, 16 acciones, catálogo y ciclo documental contrastados y 36 escenarios de aceptación. Se identifican brechas reales de catálogos, propiedad/fecha de carga, plazo/prórroga y cambio inicial de contraseña. [R01–R03](../vistas/gerencia/07-DECISIONES-RESIDUALES-PERSONAL.md) reúnen únicamente nuevas decisiones de requisitos adicionales, anulación y baja laboral. No se declara cierre funcional del software ni se propone instalar DDL por completar estas fichas.

**Decisión del paso 2, 25/10/2026:** las tarjetas abren directamente la pantalla completa de su módulo. Se documentó en [Inicio](../vistas/gerencia/01-INICIO.md). El detalle de altas y cuentas continúa en [Personal institucional](../vistas/gerencia/02-PERSONAL.md).

**Decisión del paso 3, completada el 25/10/2026:** Gerencia crea Administración, los seis roles profesionales y Familiar (**ocho roles destino**); no crea Gerencia ni Superadministración. Superadministración crea cuentas de todos los roles. Sustituye la propuesta inicial de reparto de altas. La respuesta literal y el alcance están en la sección 7 de Personal. No se ha modificado código ni instalado permisos.

**Decisión del paso 4, 25/10/2026:** Gerencia también edita datos y activa/suspende acceso de esas cuentas. Cambiar roles, editar permisos y eliminar no están incluidos. La cuenta con Gerencia/Superadministración permanece fuera del alcance aunque tenga otro rol adicional.

**Decisión del paso 5, 25/10/2026:** la propietaria elige «OPCION 1»: dos apartados de menú, **Personal** y **Familiares y contactos**, cada uno con su listado y formulario. No se adopta una entrada única «Cuentas». Se conserva la distinción trabajador/contacto/usuario y el alcance previamente autorizado; sin cambios de aplicación o permisos instalados.

**Decisión del paso 6, 25/10/2026:** la propietaria elige «B»: toda nueva alta de trabajador de este flujo incluye ficha laboral y cuenta. Correo y rol autorizado son necesarios; la creación conjunta es atómica y no ofrece alta sin cuenta inicial. No acredita competencia clínica ni exige completar retroactivamente personal preexistente. Constancia y límites en la sección 11 de [Personal](../vistas/gerencia/02-PERSONAL.md). Continúa el detalle de campos y requisitos documentales; implementación pendiente.

**Decisión del paso 7, 25/10/2026:** la propietaria elige «A con un tiempo delimitado para que completen documentacion». Se permite finalizar el alta con documentos pendientes, identificados y sujetos a plazo. Duración, inicio/cómputo y efecto del vencimiento aún pendientes; no se autoriza suspensión automática por esta elección. Constancia en la sección 12 de [Personal](../vistas/gerencia/02-PERSONAL.md). Siguiente pregunta: duración y cómputo del plazo.

**Precisión del paso 7, 25/10/2026:** la propietaria responde «7» a las alternativas de días calendario desde la fecha de alta. Se adopta un plazo de **siete días calendario desde el alta** para completar la entrega documental. Incluye fines de semana y feriados; la validación posterior se distingue de la entrega. La política de vencimiento, avisos y prórrogas sigue pendiente. Siguiente decisión: efecto de vencer el plazo con documentos sin entregar.

**Decisión del paso 8, 25/10/2026:** la propietaria elige «A»: señalar documentación vencida y avisar a Gerencia. No hay suspensión automática; Gerencia decide el seguimiento, la prórroga o la suspensión manual dentro del alcance aprobado. Debe conservarse la identificación del vencimiento y de las prórrogas concedidas. Canales de aviso y límites de prórroga pendientes. Siguiente decisión: quién carga los documentos y cómo se revisan; ver sección 12 de [Personal](../vistas/gerencia/02-PERSONAL.md).

**Decisión del paso 9, 25/10/2026:** la propietaria elige «c»: el trabajador carga sus documentos y Gerencia también puede subir los recibidos; Gerencia realiza la revisión. Conservar titular, usuario de carga y fecha, diferenciados del revisor y su decisión. No habilita acceso a documentos de otros trabajadores ni autovalidación. Constancia en la sección 13 de [Personal](../vistas/gerencia/02-PERSONAL.md). Siguiente definición: límites de las prórrogas.

**Decisión del paso 10, 25/10/2026:** la propietaria elige «a»: una sola prórroga de siete días adicionales, concedida por Gerencia. Se añade a la fecha límite original; el total con prórroga es de catorce días calendario desde el alta, sin renovaciones sucesivas ni suspensión automática. Se conservan motivo, autor, fecha y ambos plazos. Constancia en la sección 12 de [Personal](../vistas/gerencia/02-PERSONAL.md). Siguiente definición: avisos y recordatorios.

**Decisión del paso 11, 25/10/2026:** la propietaria elige «A»: avisos internos al trabajador y Gerencia dos días antes y al vencer el plazo, si hay entregas pendientes. Se aplica también a la fecha prorrogada, sin duplicar avisos ni confundir revisión pendiente con falta de entrega. No autoriza correo u otro canal externo. Se consolida el flujo en la sección 12 de [Personal](../vistas/gerencia/02-PERSONAL.md); implementación y pruebas pendientes. Siguiente preferencia: organización del formulario en pasos o secciones de una pantalla.

**Decisión del paso 12, 25/10/2026:** la propietaria elige «A»: formulario de cuatro pasos guiados, con navegación de ida y vuelta y guardado al finalizar. Se detallan campos, botones, errores y criterios de aceptación en [Alta de trabajador](../vistas/gerencia/03-ALTA-TRABAJADOR.md). El código conserva siete pasos y comunicaciones de 48 horas; correcciones pendientes. Siguiente definición: conciliar el mínimo documental inmediato con la entrega diferida aprobada.

**Decisión del paso 13, 25/10/2026:** la propietaria elige «A»: cédula, fotografía y CV inmediatos para todos; título/certificado de formación para las seis profesiones y matrícula para Medicina. Los demás requeridos que admitan plazo siguen el circuito de siete días; los opcionales no generan vencimiento. No equivale a aprobación del contenido o competencia clínica. [Matriz ratificada](../vistas/gerencia/03-ALTA-TRABAJADOR.md#7-mínimo-documental-inmediato-ratificado). Siguiente decisión: estado inicial de acceso de la nueva cuenta.

**Decisión del paso 14, 25/10/2026:** la propietaria elige «A»: cuenta ACTIVA al completarse correctamente el alta, con rol autorizado y controles de cada operación. No requiere activación manual adicional ni valida documentos por implicación; no reactiva cuentas existentes. [Detalle](../vistas/gerencia/03-ALTA-TRABAJADOR.md#8-estado-inicial-de-acceso-adoptado). La Action actual ya asigna ACTIVO, pero el circuito completo sigue sin implementar/verificar. Siguiente definición: credenciales del primer acceso.

**Decisiones conjuntas 15–29, 25/10/2026:** `15A, 16B, 17B, 18A, 19B Y A, 20A, 21A, 22A, 23A, 24A Y B, 25A, 26B Y C, 27B, 28A, 29B`. Se adopta contraseña temporal con cambio inicial obligatorio; Gerencia puede reemitir recuperación y registrar/corregir vínculos familiares; Familiar inicia activo con vínculo autorizado. Contactos y ocupación tienen dos modos sobre los mismos datos. Se adoptan Áreas y equipo, planificación semanal, cobertura tabular, avisos internos a Administración/afectados, bandeja única de alertas, PDF/CSV agregados, Galería directa, Pendientes de Gerencia y residentes en tarjetas con alternativa de tabla. [Alcances y exclusiones](GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos). Todo es adopción funcional; no prueba ejecución de permisos, código, mensajes o BDD.

1. Confirmar prioridad del Inicio y definir sus tarjetas, datos y accesos.
2. Personal: listado, ficha y formularios de cada operación.
3. Áreas y distribución del personal.
4. Turnos/planificación y consulta de cobertura, diferenciando responsabilidades.
5. Residentes y ocupación administrativa.
6. Alertas relevantes y reportes.
7. Galería y controles comunes de navegación, perfil, feedback y accesibilidad.
8. Matriz final vista → acción → permiso/contexto → fuente → prueba.

Las decisiones/preferencias pendientes se presentan juntas según la última instrucción y se documenta cada respuesta. Las facultades ya aprobadas no se vuelven a pedir como si fueran nuevas. Los casos adicionales materiales que surjan del contraste se identificarán concretamente; las elecciones técnicas rutinarias se resuelven con autonomía.

**Avance del 26/10/2026 — Residentes y ocupación:** fichas [18](../vistas/gerencia/18-RESIDENTES-GERENCIA.md), [19](../vistas/gerencia/19-OCUPACION-HABITACIONES-CAMAS.md) y [20](../vistas/gerencia/20-ACEPTACION-RESIDENTES-OCUPACION.md): tarjetas iniciales/tabla, mapa/tabla, veinte datos nominales y 24 casos especificados, no ejecutados. Consulta gerencial separada de traslado/admisión; reutilización del directorio, mapa y componentes existentes. No se cambia código o BDD. Se registra además la petición de refinar las imágenes con diseños/componentes reales. Siguiente ficha: bandeja única de Alertas, seguida de Reportes y Galería.

**Avance posterior del 26/10/2026 — Alertas e imágenes:** fichas [21](../vistas/gerencia/21-ALERTAS-GERENCIA.md), [22](../vistas/gerencia/22-DETALLE-TRAZABILIDAD-ALERTAS.md), [23](../vistas/gerencia/23-ACEPTACION-ALERTAS-GERENCIA.md) y [24](../vistas/gerencia/24-DISENO-IMAGENES-ALERTAS.md). Bandeja 25A, doce datos, diez controles de consulta, 24 escenarios adicionales y dos imágenes con datos ficticios. Estados/acciones de otros roles no se adoptan como nuevos permisos gerenciales. Índice actualizado: 164 casos especificados, no ejecutados; sin implementación de aplicación o BDD. Continúa Reportes, después Galería y matriz de cierre de documentación del rol.

**Avance posterior del 26/10/2026 — Reportes e imágenes:** fichas [25](../vistas/gerencia/25-REPORTES-GERENCIA.md), [26](../vistas/gerencia/26-EXPORTACION-REPORTES-GERENCIA.md), [27](../vistas/gerencia/27-ACEPTACION-REPORTES-GERENCIA.md) y [28](../vistas/gerencia/28-DISENO-IMAGENES-REPORTES.md). Decisión 26B/C desarrollada en consulta agregada y revisión PDF/CSV, doce datos/contextos, diez controles y 24 casos nuevos, con dos PNG ficticios. Se identifican fuentes actuales incompatibles con equivalencia de formatos, alcance gerencial y significado temporal; no se modificaron ni se exportaron datos reales. Total: 188 casos especificados, no ejecutados. Continúan Galería y matriz final del rol.

**Avance posterior del 26/10/2026 — Galería y matriz:** fichas [29](../vistas/gerencia/29-GALERIA-GERENCIA.md), [30](../vistas/gerencia/30-CARGA-PRIVACIDAD-GALERIA.md), [31](../vistas/gerencia/31-ACEPTACION-GALERIA-GERENCIA.md), [32](../vistas/gerencia/32-DISENO-IMAGENES-GALERIA.md) y [33](../vistas/gerencia/33-MATRIZ-COBERTURA-GERENCIA.md). Entrada 27B y operaciones privadas aprobadas el 17/10, doce datos/doce controles, 24 casos nuevos y dos imágenes ficticias. Se conserva implementación existente y se especifican ajustes UX/verificación, sin nueva carga o migración. Matriz de fichas principales con 212 casos no ejecutados en estas tareas, imágenes disponibles y pendientes materiales. Próximo trabajo: priorizar implementación/correcciones desde esa matriz, no declarar el rol implementado por terminar las fichas.

**Primer hito de implementación posterior del 26/10 — H04:** [corrección del acceso a Ocupación](../vistas/gerencia/34-CORRECCION-ACCESO-OCUPACION-H04.md). Se reproduce el 403 y se corrige reutilizando mapa/controlador; filtros, detalle y retorno quedan en el ámbito autorizado. 56 pruebas PHP/361 aserciones, dos JS y build correctos; sin DDL, datos o grants nuevos. QA con sesión y resto de fases pendientes. No se presenta este hito como ejecución de los 212 escenarios documentados.

**Renovación posterior del 26/10:** [implementación y pruebas reales](../vistas/gerencia/36-RENOVACION-GERENCIA-IMPLEMENTACION-Y-PRUEBAS.md). Incluye pantallas conectadas, alcance de Personal, alta de cuatro pasos, acceso, recuperación por enlace, calendario/cobertura y consultas gerenciales. La propietaria aprobó el [diseño estructural de Personal](../vistas/gerencia/35-PROPUESTA-ESTRUCTURAL-PERSONAL.md): ocho columnas y cuatro relaciones, probadas solo en bases desechables con documentación, entregas, prórroga, cambio inicial y movimientos laborales. Continúan instalación institucional, Familiares/contactos y aceptación completa; no se declara terminado todo Gerencia.

**Integración siguiente del 26/10:** [informe38](../vistas/gerencia/38-INTEGRACION-CONTACTOS-PLANIFICACION-Y-ACEPTACION.md). Familiares/contactos y acceso inicial por enlace implementados; DA01 adoptada y protegida en backend, planificación real por rol y trazabilidad de ocupación corregidas. Historia nominal propuesta37 con secuencias/FK/inmutabilidad probadas en bases desechables. Permisos17B de contactos aplicados; inventario institucional continúa100 de negocio. Instalación específica35/37 y aceptación exhaustiva siguen separadas y pendientes.
