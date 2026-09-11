# Auditoría de ventanas, formularios, botones y pantallas

Fecha de corte: 11 de septiembre de 2026  
Rama revisada: `COMPLETAR_SISTEMA`  
Alcance: interfaz Blade/Livewire, rutas web, controladores, permisos, formularios, modales, enlaces, persistencia y pruebas existentes.  
Esta auditoría no modifica la arquitectura ni la base de datos.

## 1. Veredicto

**PARCIALMENTE FUNCIONAL; requiere correcciones antes de considerarse completamente presentable.**

El núcleo de residentes, admisiones, enfermería, alertas, medicación, cuidados, usuarios y reportes tiene flujos reales. Sin embargo, la interfaz también contiene rutas que aparentan ser módulos distintos y terminan mostrando el mismo dashboard, entradas de Salud que no seleccionan la sección anunciada, botones visibles sin acción, vistas de Familia y Social que son marcadores de posición y controles Livewire cuya autorización no coincide con el permiso mostrado.

No se encontraron vistas estáticas faltantes: todas las llamadas literales a `view()`, `@include` y `@extends` resuelven a archivos existentes. Las plantillas Blade compilan correctamente con `php artisan view:cache`.

## 2. Dimensión real de la interfaz

| Elemento | Cantidad |
|---|---:|
| Rutas totales | 254 |
| Rutas administrativas | 217 |
| Rutas administrativas GET/HEAD | 165 |
| Rutas administrativas HTTP de mutación | 53 |
| Acciones GET únicas | 98 |
| Rutas GET atendidas por Livewire | 62 |
| Rutas profesionales que apuntan al dashboard genérico | 34 |
| Componentes Livewire PHP | 72 |
| Controladores HTTP | 28 |
| Vistas Blade | 345 |
| Vistas `pages` | 111 |
| Vistas `livewire` | 101 |
| Componentes Blade | 58 |
| Plantillas PDF | 33 |
| Formularios `<form>` | 89 |
| Botones `<button>` | 854 |
| Enlaces `<a>` | 317 |
| Acciones `wire:click` | 667 |
| Envíos `wire:submit` | 41 |

Las 165 rutas GET no equivalen a 165 pantallas diferentes. Varias son exportaciones, vistas de archivo, alias de una misma pantalla Livewire o rutas provisionales al dashboard.

## 3. Mapa de pantallas y comportamiento

### 3.1 Acceso, navegación y perfil

| Pantalla | Dónde aparece | Qué muestra y hace | Estado |
|---|---|---|---|
| Portada pública | `/` | Presentación institucional, secciones informativas, tema claro/oscuro y acceso al inicio de sesión. | Parcial: logo y redes sociales usan `href="#"`. |
| Inicio de sesión | `/login` | Autentica por `correo`; bloquea usuarios cuyo estado no sea `ACTIVO`. | Funcional y probado. |
| Dashboard global | `/dashboard` | Selecciona dashboard según rol y registra el primer acceso del día. | Parcial: el rol médico es redirigido al dashboard de Enfermería. |
| Barra superior | Todas las pantallas autenticadas | Perfil, tema visual y campana de alertas/notificaciones. | Funcional con observaciones de accesibilidad. |
| Barra lateral | Todas las pantallas autenticadas | Menú contextual por rol y por módulo. Oculta rutas marcadas como provisionales. | Funcional, pero contiene accesos de Salud cuyo destino no activa el tab correspondiente. |
| Perfil Jetstream | Menú de usuario | Datos de perfil, contraseña, sesiones y doble factor. | Funcional según tests; quedan vistas de equipos/API/registro de Jetstream aunque esas funciones están deshabilitadas. |

Acciones visibles:

- Tema claro/oscuro: cambia clase global mediante `data-theme-toggle`.
- Campana: abre panel, actualiza cada 12 segundos, cuenta alertas y tomas próximas/vencidas, permite ir al registro, atender o cerrar según permiso.
- Perfil: actualización de datos, contraseña, sesiones y 2FA.
- Enlaces de redes sociales: actualmente no navegan a una red real.

### 3.2 Usuarios, roles y personal

| Pantalla | Ruta/ubicación | Formularios, botones y resultado | Estado |
|---|---|---|---|
| Usuarios | `admin.usuarios.index` | Busca y filtra; crea y edita con asistente; activa/desactiva; abre ficha; vincula familiar con residente; gestiona documentación; exporta PDF/Excel/CSV. | Funcional con controles extensos. |
| Crear/editar usuario | `admin.usuarios.create`, `admin.usuarios.edit` y modales Livewire | Datos personales, ubicación, rol, área, profesión/cargo/especialidad, contacto, credenciales y documentos. | Funcional; hay dos experiencias de edición que deben mantenerse consistentes. |
| Ficha de usuario | `admin.usuarios.show` | Resumen, documentación, horarios, acceso, restablecimiento de contraseña y expediente. | Funcional con autorización interna. |
| Paquete documental | rutas `admin.usuarios.documentos.*` | Vista, descarga, impresión y envío por correo. | Funcional; la autorización está en el controlador y no en middleware de ruta. |
| Roles y permisos | `admin.roles-permisos.index` | Selecciona rol, activa/desactiva permisos, guarda y restaura cambios, lista usuarios del rol. | Funcional; requiere pruebas específicas de escalamiento de privilegios. |
| Personal institucional | `admin.personal-institucional` | Directorio en tarjetas/tabla, filtros, alta/edición, horarios, documentos, estado y paneles de resumen. | Parcial: exportaciones visibles sin acción y permiso incorrecto para cambiar estado. |
| Áreas institucionales | `admin.areas-institucionales.index` | CRUD de áreas, estado, ficha, gráficas y reportes PDF/Excel/CSV. | Funcional en código; interfaz muy grande y con estilos duplicados. |
| Horarios y asignaciones | `admin.turnos-asignaciones.index` | Calendario, asignaciones, planilla de enfermería, plazas, filtros y exportación. | Funcional, pero varias mutaciones no comprueban permiso dentro del método Livewire. |

Hallazgos concretos:

- Los tres botones de suspender/reactivar personal usan `@can('usuarios.eliminar')`. Ese permiso no existe en los seeders. El permiso vigente es `usuarios.cambiar_estado`; por ello el control puede quedar oculto para todos.
- `PersonalInstitucionalPanel::toggleEstado()` cambia el estado sin una comprobación de autorización propia. Un usuario que alcance el componente puede invocar la acción Livewire directamente.
- “Exportar PDF” de la cabecera de Personal institucional no tiene `wire:click`, enlace ni formulario.
- En el resumen de Personal, los selectores “Periodo” y “Área”, “Exportar Excel” y “Exportar PDF” no tienen enlace, modelo ni acción.

### 3.3 Residentes y expediente administrativo

| Pantalla/carpeta | Dónde se muestra | Qué hace | Estado |
|---|---|---|---|
| Listado de adultos mayores | `admin.adultos-mayores.index` | Lista, busca, crea, edita, abre expediente, archiva/restaura y cambia estado. | Funcional y probado. |
| Expediente | `admin.adultos-mayores.show` | Contenedor Alpine de ocho carpetas: identificación, red de apoyo, documentos, salud, cognitivo, participación, historial y reportes. | Parcial: mezcla flujos HTTP, Alpine y Livewire y contiene controles muertos. |
| Identificación | pestaña del expediente | Datos personales, residencia y estado institucional. | Funcional de consulta. |
| Red de apoyo | pestaña del expediente | Familiares, vínculos, responsable y contacto de emergencia. | Funcional en el panel Livewire específico; la vista grande del expediente duplica contenidos. |
| Documentos | pestaña del expediente y ruta de documentos | Sube, consulta, edita, archiva, restaura y descarga archivos privados. | Flujo principal funcional; “Ver todos los documentos” usa `href="#"`. |
| Salud | pestaña del expediente | Ficha clínica, atenciones, observaciones, signos, medicación y enlaces a paneles clínicos. | Funcional en gran parte, con duplicidad respecto a Ficha 360 y ficha médica integrada. |
| Cognitivo | pestaña del expediente | Evaluaciones cognitivas/geriátricas y acceso a resultados. | Parcial: el visor detallado de evaluación geriátrica está marcado TODO. |
| Participación | pestaña del expediente | Actividades, participaciones y asignaciones de voluntariado. | Parcial: hay botón de eliminar voluntario sin acción. |
| Historial | pestaña del expediente | Estado, movimientos y bitácora resumida. | Consulta funcional; “Ver trazabilidad completa” y “Ver bitácora completa” usan `href="#"`. |
| Reportes | pestaña del expediente | Reportes individuales y específicos. | Funcional en las rutas de descarga existentes. |

Problema de contenido: `_familiares.blade.php` contiene una sección de asignaciones de voluntariado idéntica a la existente en `_actividades.blade.php`, incluidos botones Ver, Editar y Eliminar. El botón Eliminar no ejecuta ninguna acción. Esto hace que la pantalla de familiares muestre contenido que pertenece a Participación/Voluntariado.

### 3.4 Preadmisión, admisión, habitaciones y camas

| Pantalla | Ruta | Qué hace | Estado |
|---|---|---|---|
| Preadmisiones | `admin.admisiones.preadmisiones` | Busca y filtra solicitudes, revisa documentos, aprueba/rechaza y genera reporte. | Funcional y probado. |
| Rechazadas | `admin.admisiones.preadmisiones.rechazadas` | Reutiliza el panel de preadmisiones. | Verificar que la ruta inicialice el filtro rechazado; no se observa un default de ruta explícito. |
| Nueva preadmisión | `admin.admisiones.preadmision` | Wizard por pasos, datos, documentos, valoración inicial y confirmación. | Funcional; la mutación final no contiene control de permiso propio. |
| Valoración de enfermería | `admin.admision.valoracion-enfermeria` | Lista valoraciones y abre detalle. | Funcional de consulta. |
| Valoración médica | `admin.admision.valoracion-medica` | Crea/consulta valoración médica y decisión. | Funcional; revisar autorización de los métodos de guardado. |
| Habitaciones y camas | `admin.habitaciones.index` | CRUD de habitaciones y camas, capacidad, estados y ocupación. | Funcional y probado; evita exceder capacidad. |
| Asignación de pacientes | `admin.asignacion-turno.index` | Asigna residente, enfermero, turno y cama; finaliza asignación. | Funcional y probado. |

El flujo comprobado es preadmisión → valoración → decisión → admisión → cama → ficha. La historia de ocupación y la restricción de doble asignación tienen cobertura de regresión.

### 3.5 Salud, clínica y ficha médica

| Pantalla | Ruta/ubicación | Qué hace | Estado |
|---|---|---|---|
| Selector de Salud | `admin.salud-seguimiento.*.index` | Lista residentes y presenta tabs de resumen, ficha, signos, medicación, administración, valoración, evaluaciones, alertas y reportes. | Defecto de navegación: las rutas específicas no inicializan `seccionActiva`; todas parten de `resumen`. |
| Resumen individual | `admin.salud-seguimiento.resumen` | Resumen clínico del residente. | Funcional. |
| Ficha médica | `admin.salud-seguimiento.ficha` | Datos generales, alergias, condiciones, antecedentes y observaciones; crea/edita/archiva. | Funcional con autorización parcial. |
| Signos vitales | `admin.salud-seguimiento.signos` | Busca, registra, edita/corrige, detalla, anula/restaura y detecta valores de alerta. | Funcional y cubierto por pruebas. |
| Monitor médico de signos | `admin.medico.signos-vitales` | Filtros clínicos, alertas de rango, nueva medición y acceso a ficha. | Funcional. |
| Notas/evolución | ficha médica integrada y pacientes médicos | Registra nota, motivo, tipo, plan e interconsulta. | Funcional; el modal no tiene autorización explícita propia. |
| Ficha clínica integrada | `admin.medico.paciente.ficha` | Seis tabs: resumen, notas, signos, medicación, funcional y geriátrico. | Funcional. |
| Pacientes en seguimiento | rutas `admin.medico.pacientes.*` | Activos, historial e interconsultas; abre ficha y modales clínicos. | Funcional; protegido dentro del componente, no por middleware de ruta. |

Defecto principal: las rutas `/fichas`, `/medicaciones`, `/administraciones`, `/signos-vitales`, `/valoraciones`, `/evaluaciones-geriatricas`, `/alertas` y `/reportes` apuntan al mismo componente, pero no pasan un valor por defecto para el tab. Entrar desde “Medicación” puede mostrar “Resumen de salud” hasta que el usuario cambie manualmente de sección.

### 3.6 Medicación y administración

| Pantalla | Dónde aparece | Acciones | Estado |
|---|---|---|---|
| Selector/listado de medicación | menú Salud → Medicación | Selecciona residente y abre su gestión farmacológica. | Parcial por el defecto de tab descrito arriba. |
| Prescripción del residente | `admin.salud-seguimiento.medicacion` | Lista, busca y filtra; prescribe; edita; suspende/finaliza; muestra agenda diaria. | Funcional tras correcciones recientes. |
| Administración | `admin.salud-seguimiento.administracion` | Muestra agenda, administradas, omitidas, pendientes y vencidas; registra toma u omisión. | Funcional tras correcciones recientes. |
| Ficha 360 → Medicación | `admin.enfermeria.pacientes.ficha`, tab Medicación | Medicación activa, próxima ocurrencia, estado, botón Administrar y trazabilidad. | Funcional. |
| Dashboard de Enfermería | `admin.enfermeria.dashboard` | Cola de dosis pendientes con acciones Administrar/Omitir. | Funcional. |
| Campana | navbar | Avisa tomas dentro de 60 minutos y vencidas de pacientes asignados; enlaza a Administración. | Funcional. |

Reglas observadas:

- Prescripción y administración están separadas. Crear una prescripción ya no crea una omisión ficticia.
- Los estados son `PENDIENTE`, `PRÓXIMA`, `VENCIDA`, `ADMINISTRADA` y `OMITIDA`.
- Frecuencias reconocibles como “Cada 8 horas” generan todas las ocurrencias del día desde la hora inicial.
- Una toma u omisión real se identifica por medicamento, fecha y hora programada y no puede duplicarse.
- Enfermería solo ve recordatorios de residentes asignados.

Problemas visibles pendientes:

- Los modales de medicación aún contienen texto mal codificado: `F?rmaco`, `V?a`, `d?a`, `despu?s`, `enfermer?a`, `n?useas`.
- El formulario libre de frecuencia permite textos que no pueden convertirse en horarios múltiples. En esos casos solo se usa la hora inicial.
- Hay controles de medicación en varias pantallas, lo que aumenta el riesgo de que una corrección se aplique en una interfaz y no en las otras.

### 3.7 Enfermería y cuidados

| Pantalla | Ruta | Qué muestra y hace | Estado |
|---|---|---|---|
| Dashboard de turno | `admin.enfermeria.dashboard` | Turno actual, asignados, tareas, medicación, alertas, signos, seguimiento y accesos rápidos. | Funcional y probado extremo a extremo. |
| Mis pacientes | `admin.enfermeria.pacientes` | Lista/tarjetas, búsqueda, filtros por turno/enfermero/estado, acciones rápidas y ficha. | Funcional y probado. |
| Ficha 360 | `admin.enfermeria.pacientes.ficha` | Resumen, signos, medicación, cuidados, seguimiento, alertas e Historial 360°. | Funcional y probado. |
| Tareas | `admin.enfermeria.tareas`, `admin.plan-cuidado.tareas` | Crea tareas, filtra, registra resultado, omite o reprograma con justificación. | Funcional y probado. |
| Planes de cuidado | `admin.plan-cuidado.index` | Crea, edita borrador, consulta y cierra planes. | Funcional y probado. |
| Seguimiento diario | `admin.seguimiento-diario.index` | Busca, crea y corrige alimentación, hidratación, movilidad, higiene, sueño, ánimo, dolor, incidentes y observación. | Funcional y validado en español. |
| Turnos | `admin.turnos-enfermeria.index` | Crea y edita turnos y franjas. | Funcional. |
| Pase de turno | `admin.enfermeria.pase-turno`, `admin.pase-turno.index` | Genera, consulta y recibe pase, con vigilancia y resumen clínico. | Funcional y probado. |
| “Actividades” de Enfermería | `admin.enfermeria.actividades` | Apunta al mismo `DashboardTurno`. | No es una pantalla de actividades diferente. |
| “Reportes” de Enfermería | `admin.enfermeria.reportes` | Apunta al mismo `DashboardTurno`. | No es una pantalla de reportes diferente. |

La mayoría de las mutaciones críticas de Enfermería valida permiso y paciente asignado dentro del método Livewire. Es el sector con mejor coherencia entre interfaz, backend, alcance y pruebas.

### 3.8 Alertas y notificaciones

| Pantalla | Ruta/ubicación | Acciones | Estado |
|---|---|---|---|
| Panel de alertas | `admin.alertas-clinicas.index` y `admin.enfermeria.alertas` | Buscar/filtrar, detectar, crear manual, ver detalle, asignar responsable, atender, registrar acción, cerrar, ver gráficos y ubicación. | Funcional y probado. |
| Alertas pendientes de residentes | `admin.adultos-mayores.alertas-pendientes` | Resumen de pendientes por residente. | Funcional. |
| Campana | navbar | Contador real, lista, toast nuevo, atención/cierre y recordatorios de medicación. | Funcional y probado. |
| Ficha 360 → Alertas | tab Alertas | Historial y acciones sobre alertas del residente. | Funcional. |

La trazabilidad conserva alerta, responsable, acciones, atención y cierre. Las consultas de enfermería se acotan a pacientes asignados. Las tomas próximas son recordatorios calculados; no crean alertas clínicas persistentes por anticipado.

### 3.9 Valoraciones y VGI

| Pantalla | Ruta/ubicación | Acciones | Estado |
|---|---|---|---|
| Valoración funcional | Salud individual y modales | Crear, editar, detallar, anular/restaurar y marcar vigente. | Funcional. |
| Barthel | ficha clínica/Ficha 360 | Calcula puntaje y clasificación y guarda historia. | Funcional y probado. |
| VGI general | Salud → evaluaciones geriátricas | Selecciona área, instrumento y versión; registra resultados. | Funcional de registro. |
| Evaluaciones por área | Psicología cognitiva/afectiva/funcionamiento/entorno | Busca pacientes y abre evaluación del área. | Funcional. |
| Dashboard Psicología | `admin.psicologia.dashboard` | Casos y evaluaciones asignadas. | Funcional. |
| Detalle VGI | panel de evaluaciones | Existe un comentario `TODO: Implementar visor detallado`. | Incompleto. |

Los modales `EvaluacionGeriatricaAreaModal`, `EvaluacionGeriatricaModal`, `ValoracionInicialModal` y `ValoracionMedicaModal` guardan datos sin comprobar dentro del método el permiso de mutación correspondiente. La visibilidad del botón no reemplaza esa autorización.

### 3.10 Familia y Social

| Pantalla | Ruta | Estado real |
|---|---|---|
| Resumen | `admin.familia-social.resumen` | Funcional, resume familia, contactos y situación social. |
| Red de apoyo | `admin.familia-social.red-apoyo` | Funcional: vincula, edita, activa/desactiva, define responsable y emergencia. |
| Visitas | `admin.familia-social.visitas` | Marcador de posición; muestra “Vista base preparada”. |
| Ficha social | `admin.familia-social.ficha-social` | Marcador de posición; muestra “Vista base preparada”. |

Estas dos últimas rutas son pantallas visibles, pero no tienen formulario, persistencia ni flujo de negocio.

### 3.11 Actividades y voluntariado

| Pantalla | Ruta | Qué hace | Estado |
|---|---|---|---|
| Actividades | `admin.actividades.index` | CRUD, detalle, filtros y estado. | Funcional en código; autorización de mutación insuficiente. |
| Tipos de actividad | `admin.actividades.tipos` | Crear, editar, ver y eliminar tipo. | Funcional en código; autorización de mutación insuficiente. |
| Participación | `admin.actividades.participacion` | Registrar, editar, detallar y cancelar participación. | Funcional en código; autorización de mutación insuficiente. |
| Asistencia | `admin.actividades.asistencia` | Realizar/cancelar/reprogramar y registrar resultado. | Funcional en código; autorización de mutación insuficiente. |
| Reportes de actividad | `admin.actividades.reportes` | Indicadores y filtros. | Revisar exportación extremo a extremo. |
| Voluntariado resumen | `admin.voluntariado.index` | Indicadores y navegación. | Funcional. |
| Voluntarios | `admin.voluntariado.voluntarios.index` | Alta, edición, estado, archivo y perfil. | Funcional con controles de permiso internos. |
| Disponibilidad | `admin.voluntariado.disponibilidad.index` | Calendario, alta/edición, disponible/no disponible y filtros. | Funcional. |
| Asignaciones | `admin.voluntariado.asignaciones.index` | Alta, edición, confirmación, cumplimiento, cancelación y detalle. | Funcional. |
| Asistencia voluntaria | `admin.voluntariado.asistencia.index` | Registro, asistencia, ausencia, justificación y reprogramación. | Funcional. |
| Reportes voluntariado | `admin.voluntariado.reportes.index` | Reutiliza el resumen general. | No existe reporte dedicado. |

Aunque estos componentes existen, el menú personal del rol Voluntario oculta sus seis rutas porque están declaradas provisionales. Las rutas `/admin/voluntario/*` apuntan al dashboard genérico y no al módulo funcional de administración de voluntariado.

### 3.12 Reportes y bitácora

| Pantalla | Ruta | Salidas | Estado |
|---|---|---|---|
| Institucional | `admin.reportes.institucional.preview` | Vista previa, PDF y Excel. | Funcional y probado indirectamente. |
| Adultos mayores | `admin.reportes.adultos.preview` | Filtros, vista previa, PDF y Excel. | Funcional. |
| Salud | `admin.reportes.salud.preview` | Datos clínicos reales, filtros, PDF y Excel. | Funcional. |
| Familiares | `admin.reportes.familiares.preview` | Vista previa, PDF y Excel. | Funcional. |
| Equipo | `admin.reportes.equipo.preview` | Vista previa, PDF y Excel. | Funcional. |
| Actividades | `admin.reportes.actividades.preview` | Vista previa, PDF y Excel. | Funcional. |
| Bitácora | `admin.bitacora.index` | Consulta de actividad/auditoría. | Funcional. |
| Reporte de bitácora | `admin.reportes.bitacora.preview` | Vista previa y PDF. | Funcional. |
| Reporte individual Ficha 360 | ruta PDF de paciente | Signos y datos clínicos reales. | Funcional y probado. |

Las exportaciones reales usan rutas y controladores. Deben distinguirse de los botones de exportación sin acción dentro de Personal institucional.

### 3.13 Portales profesionales y familiares

| Grupo | Rutas declaradas | Resultado real |
|---|---:|---|
| Medicina | 8 | Dashboard, pacientes, ficha y signos son pantallas reales. |
| Psicología | 12 | Dashboard y evaluaciones por área son reales; 5 rutas son alias del dashboard genérico. |
| Fisioterapia | 8 | Las 8 rutas apuntan al dashboard genérico y están ocultas del sidebar. |
| Nutrición | 9 | Valoración y seguimiento tienen entrada visible; las demás rutas están marcadas provisionales y varias apuntan al dashboard genérico. |
| Voluntario | 6 | Las 6 rutas apuntan al dashboard genérico y están ocultas. |
| Portal familiar | 6 | Las 6 rutas apuntan al dashboard genérico y están ocultas. |

Conclusión: una ruta existente no demuestra que exista una pantalla o flujo propio. Hay 34 alias hacia `DashboardController@index`.

## 4. Botones, enlaces y formularios que no hacen lo que anuncian

### Prioridad alta

1. Salud → Fichas/Medicación/Administración/Signos/Valoraciones/Alertas/Reportes abre el componente común con tab `resumen` porque la ruta no define la sección activa.
2. `/dashboard` envía al rol `MEDICO GENERAL/GERIATRA` a `admin.enfermeria.dashboard`.
3. Cambiar estado de Personal institucional usa un permiso inexistente en la vista y carece de autorización dentro del método.
4. Las mutaciones de Actividades solo están detrás de `actividades.ver`; sus métodos de guardar/actualizar/eliminar no exigen permisos específicos.
5. Diversos modales clínicos y de valoración confían en la pantalla contenedora y no autorizan el guardado dentro del método.

### Prioridad media

1. Personal institucional: cuatro botones de exportación visibles sin acción y dos filtros no conectados.
2. Expediente → Documentos: “Ver todos los documentos” no navega.
3. Expediente → Historial: “Ver trazabilidad completa” y “Ver bitácora completa” no navegan.
4. Expediente → Participación y la sección duplicada en Familiares: botón papelera sin acción.
5. Evaluaciones geriátricas: falta visor detallado.
6. Enfermería → Actividades y Enfermería → Reportes solo vuelven a mostrar el dashboard de turno.
7. Voluntariado → Reportes reutiliza el resumen y no genera un reporte dedicado.

### Prioridad baja o deliberadamente deshabilitada

- Enlaces de redes sociales y logo textual de la portada usan `#`.
- Vistas Jetstream de equipos, API tokens, registro y verificación conservan referencias de funciones deshabilitadas. Están condicionadas por feature flags y no aparecen en el flujo normal.
- Botones `disabled` de edición/continuación son bloqueos intencionales y no se consideran enlaces muertos.

## 5. Auditoría de permisos de interfaz

Hay 54 rutas administrativas GET sin middleware Spatie específico. Este conjunto incluye:

- 34 rutas provisionales al dashboard genérico;
- rutas reales de Medicina y Psicología que aplican control de rol dentro del componente;
- cinco rutas documentales de usuario que autorizan dentro del controlador.

El control interno evita varios accesos indebidos, pero la estrategia es inconsistente. En Livewire, cada método público que altera datos debe volver a comprobar el permiso de la acción y el alcance del registro.

Componentes con mutaciones y sin controles de autorización detectables en la clase:

- Actividades: `ActividadesPanel`, `AsistenciaPanel`, `ParticipacionPanel`, `TiposActividadPanel`.
- Admisiones: `PreadmisionWizard`.
- Clínica: `FichaMedicaAdultoModal`, `NotaEvolucionMedicaModal`.
- Documentos: `PersonalInstitucionalDocumentos`.
- Identidad: `PersonalInstitucionalHorarios`, `PersonalInstitucionalPanel`.
- Residentes: `AdultoMayorFormModal`, `AdultosMayoresPanel`.
- Valoraciones: `EvaluacionGeriatricaAreaModal`, `EvaluacionGeriatricaModal`, `ValoracionInicialModal`, `ValoracionMedicaModal`, `ValoracionMedicaPanel`.

Esto no significa que todas sean explotables desde cualquier ruta: algunas se renderizan dentro de una pantalla protegida. Sí significa que la autorización depende del contenedor o de ocultar botones y no está garantizada en el punto exacto donde se escribe en la base de datos.

## 6. Validación y mensajes

Fortalezas:

- Laravel usa catálogo global de validación en español.
- Signos vitales valida rangos biológicos y coherencia sistólica/diastólica.
- Administración exige hora real cuando se administra y motivo cuando se omite.
- Seguimiento exige detalle ante incidente y valida porcentajes/horas.
- Tareas exigen resultado, motivo o nueva fecha/hora según acción.
- Los flujos críticos de Enfermería muestran mensajes de éxito/error.

Debilidades:

- Algunos modales mantienen textos con `?` por codificación dañada.
- Conviven valores visuales `ACTIVO`, `ACTIVA` y `VIGENTE`; cada consulta debe incluir explícitamente las variantes aceptadas.
- Hay formularios HTTP, Livewire y Alpine para conceptos repetidos, por lo que reglas y mensajes pueden diferir.
- Algunos campos libres representan catálogos operativos, por ejemplo frecuencia de medicación; un texto no reconocido no puede generar agenda múltiple.

## 7. Consistencia visual, accesibilidad y experiencia

- Se detectaron 1.859 apariciones de colores hexadecimales y 2.229 clases Tailwind con colores concretos en vistas, además de 1.040 atributos `style`. Parte corresponde a PDF y gráficos, pero las pantallas interactivas también eluden los tokens semánticos.
- Hay componentes reutilizables, pero las pantallas grandes conservan variaciones propias de botones, badges, tarjetas, modales y estados.
- Los iconos sin texto dependen con frecuencia de `title`; no todos tienen `aria-label`.
- Muchos modales son capas visuales sin evidencia uniforme de bloqueo de foco, retorno del foco o cierre con Escape.
- Las tablas grandes tienen contenedor horizontal, pero varias acciones solo se explican por icono.
- La misma información del residente aparece en expediente administrativo, Salud, ficha médica integrada y Ficha 360 con navegación y controles distintos.

## 8. Cobertura de pruebas observada

Cobertura sólida:

- acceso y autenticación;
- permisos de rutas de usuarios;
- preadmisión, decisión, cama y ficha;
- ocupación sin doble asignación;
- turno completo de Enfermería;
- Ficha 360, botones y filtros longitudinales;
- signos vitales, medicación, tareas y seguimiento;
- alertas, acciones, cierre y campana;
- documentos privados;
- reportes PDF con datos reales;
- sidebars por rol.

Cobertura insuficiente o ausente:

- cada botón/enlace de las ocho carpetas del expediente administrativo;
- exportaciones del panel de Personal institucional;
- autorización negativa de cada método Livewire de mutación;
- rutas profesionales provisionales y comprobación de que no simulen módulos reales;
- accesibilidad y teclado de modales;
- textos dañados por codificación;
- inicialización correcta de cada ruta/tab de Salud;
- pantallas Familia y Social de visitas/ficha social;
- visor detallado VGI;
- recorrido de todos los reportes desde el botón visible hasta la descarga.

## 9. Orden recomendado de corrección

1. Corregir destinos de Salud y la redirección del médico; cada menú debe abrir la pantalla anunciada.
2. Bloquear en backend todas las mutaciones Livewire con el permiso específico y alcance contextual.
3. Corregir `usuarios.eliminar` por el permiso real y proteger `toggleEstado()`.
4. Conectar o retirar visualmente botones sin acción de Personal, Expediente e Historial.
5. Separar el contenido de Familiares del contenido de Voluntariado dentro del expediente.
6. Corregir codificación visible en medicación y alertas.
7. Etiquetar claramente como no disponibles las rutas provisionales o eliminarlas de la navegación pública hasta su implementación.
8. Completar el detalle VGI y las dos pantallas sociales solo cuando entren en alcance funcional.
9. Unificar componentes visuales y reemplazar colores directos en pantallas interactivas por tokens semánticos.
10. Añadir smoke tests por rol y pruebas negativas de cada acción de escritura.

## 10. Criterio de cierre de la auditoría

El sistema podrá declararse coherente cuando todo control visible cumpla una de estas condiciones:

- navega a una ruta real y a la sección anunciada;
- abre un modal funcional;
- envía un formulario validado y persiste;
- descarga o genera un archivo real;
- está explícitamente deshabilitado con una razón visible.

Actualmente no cumplen ese criterio los controles y rutas enumerados en las secciones 4 y 5.
