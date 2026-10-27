---
title: "Gerencia: Áreas y equipo"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISION_AND_STATIC_AREA_TEAM_REVIEW
runtime_verified: false
---

# Áreas y equipo

**DA01 adoptada por la propietaria el 26/10/2026:** bloquear la desactivación del área mientras conserve asignaciones o planes activos. Primero deben trasladarse o cerrarse mediante sus procesos autorizados. El cambio de estado no realiza traslados, cierre clínico ni borrados implícitos. Esta decisión resuelve la referencia DA01 abierta del corte inicial; DA02 mantiene el equipo por asignación contextual ya aprobado.

## 1. Alcance y autoridad

La propietaria eligió **20A: una entrada «Áreas y equipo»**, con organización y personal en el mismo módulo. [Respuesta adoptada](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos), [funciones de Gerencia](../../roles/gerencia.md), [baseline de roles](../../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md), [baseline BDD](../../../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y [diccionario](../../../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md).

**Objetivo:** conocer las áreas institucionales y el personal relacionado mediante asignaciones comprobables, mantener los datos propios del área y continuar hacia planificación/cobertura. Gerencia planifica; Administración mantiene la operación de jornadas/asignaciones conforme al contrato. Consultar equipo no autoriza asignaciones o escritura clínica por implicación.

Propietario funcional: **Institucion**; Identidad aporta fichas de personal, Reportes agrega información y Social/Clinica/Cuidados conservan sus relaciones pertinentes. No crear un segundo módulo o una relación ficticia personal–área para simplificar la vista.

## 2. Entrada y recorrido

Ruta actual: `admin.areas-institucionales.index`, permiso `areas.ver`, [rutas de Institucion](../../../../app/Modulos/Institucion/Presentacion/Rutas/web.php). [Vista de página](../../../../app/Modulos/Institucion/Presentacion/Vistas/paginas/areas-institucionales/index.blade.php) carga [AreasInstitucionalesPanel](../../../../app/Modulos/Institucion/Presentacion/Livewire/Paginas/AreasInstitucionalesPanel.php). El [sidebar de Gerencia](../../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php) todavía tiene dos entradas al mismo destino; 20A pide consolidarlas en una, sin ocultar capacidades ni duplicar registros.

Inicio → Áreas y equipo → buscar/filtrar área → detalle → Información / Equipo registrado / Planificación y cobertura → operación autorizada → confirmación real → regreso conservando filtros.

El listado mantiene una acción primaria «Registrar área» cuando esté autorizada. Detalle y edición no son la misma operación. Los formularios no solicitan manualmente códigos; la aplicación genera la identidad técnica.

## 3. Distribución de pantalla

| Orden | Zona | Contenido y origen | Continuidad |
|---|---|---|---|
| 1 | Cabecera | «Áreas y equipo», explicación y fecha/período de los datos cuando corresponda | Registrar área; volver al Inicio |
| 2 | Filtros | Nombre y estado del área; contexto de fecha/jornada para el equipo | Buscar, Limpiar; consulta no guarda asignaciones |
| 3 | Listado | Nombre, descripción breve, estado y acceso al detalle | Identidad legible, sin ID/código visible |
| 4 | Detalle de área | Nombre/estado y descripción completa | Editar datos propios, según permiso |
| 5 | Equipo registrado | Tabla de personas, función, jornada/turno y situación de asignación | Abrir ficha autorizada y fuente de asignación |
| 6 | Planificación/cobertura | Enlaces a procesos existentes y resumen con contexto claro | Mantener el área como filtro cuando el destino lo soporte |
| 7 | Reportes | Consulta y PDF/CSV agregados conforme al alcance 26B/C | Sin descarga nominal de expedientes o datos privados por implicación |

No es necesario mostrar varias gráficas, ranking de áreas y paneles de usuarios en el mismo detalle. Tabla y resumen breve primero; información adicional plegable. La presentación de tabla aquí es propuesta técnica para el contenido, no una nueva decisión institucional.

## 4. Campos, fuentes y semántica

La [migración areas](../../../../database/migrations/2026_09_18_001003_create_areas_table.php) solo contiene código, nombre, descripción y estado. El área no tiene jefe, capacidad, presupuesto, especialidad o plantilla permanente como columnas. [Asignaciones](../../../../database/migrations/2026_09_18_001013_create_asignaciones_personal_table.php) vincula jornada, personal y área; no es pertenencia laboral permanente.

| ID | Campo visible | Fuente/destino | Validación o límite |
|---|---|---|---|
| AR01 | Nombre del área | areas.nombre, string(80) UNIQUE | Required; normalizar antes de validar unicidad, sin alterar PK |
| AR02 | Descripción | areas.descripcion, nullable text | Texto propio del área; no guardar listas de personal, responsables o reglas en este campo |
| AR03 | Estado del área | areas.estado, string(20) | Separado de estado de cuenta/personal/asignación. Actualmente conviven ACTIVO/INACTIVO y ACTIVA/INACTIVA según entrada |
| AR04 | Fecha/período de consulta | Contexto de filtro, no columna nueva | Identificar qué se filtra; no simular estado histórico no conservado |
| AR05 | Jornada de la asignación | asignaciones_personal.cod_jornada → jornadas | Existente y autorizada; fecha de jornada, distinta de fecha de registro |
| AR06 | Turno | jornada → turno, horario y estado reales | Aclarar cruce de medianoche; no deducir asistencia |
| AR07 | Persona del equipo | asignaciones_personal.cod_personal → personal, cuenta cuando corresponda | Nombre/profesión y estados diferenciados; no contar usuarios como si fueran trabajadores sin relación |
| AR08 | Función en la asignación | asignaciones_personal.funcion, nullable string(80) | No inventar cargo/responsabilidad si falta |
| AR09 | Tipo y estado de asignación | tipo_asignacion, estado y fecha_asignacion | Catálogos existentes; no sustituir fecha_jornada por fecha_asignacion |
| AR10 | Responsable en el contexto | Asignación pertinente que realmente indique RESPONSABLE/PRINCIPAL | Dato derivado, no FK permanente del área. Si hay varias fuentes incompatibles, mostrar conflicto/contextos, no elegir la primera |

Un trabajador con varias asignaciones se cuenta una vez para «personas distintas» en el universo definido; cada fila de asignación puede aparecer en detalle. Sumar personas por área no equivale al total institucional único. Estado ACTIVO de cuenta no representa por sí solo estado laboral ACTIVO o asistencia.

## 5. Formulario y acciones

**Registrar/editar área:** nombre, descripción y estado propios del área. No pedir jefe o personal desde un selector que no tenga persistencia de ese grano. El efecto de desactivar sobre relaciones vigentes necesita la política concreta identificada en la sección 8.

Validación junto al campo; guardar revalida cuenta/permiso y el target, no se basa solo en que el modal se abrió. Nombre duplicado o cambio concurrente conserva datos recuperables y ofrece corrección, no stack trace SQL. Edición usa la PK original; cambiar nombre no reatribuye atenciones o planes.

| ID | Botón/entrada | Permiso y contexto observados o contrato | Efecto y prueba |
|---|---|---|---|
| AA01 | Buscar / Limpiar filtros | areas.ver y cuenta activa | Consulta autorizada; AE01/AE02 |
| AA02 | Registrar área | areas.crear observado; otra entrada usa areas.gestionar | Abrir formulario, sin registro definitivo; AE03/AE04 |
| AA03 | Editar área | areas.editar observado, target real | Recuperar campos del área; sin edición implícita de asignaciones; AE05 |
| AA04 | Guardar | Revalidar permiso de alta/edición según operación | Guardar dato y auditoría consistente; AE03–AE07 |
| AA05 | Ver área | areas.ver, alcance por fuente | Detalle de área y datos permitidos; AE08 |
| AA06 | Ver equipo / Abrir persona | areas.ver más lectura de personal y contexto de asignación | Abrir ficha solo con autorización; AE09/AE10 |
| AA07 | Ver planificación | Permiso del proceso propietario; no convertir consulta en asignación | Área contextual cuando proceda; AE11 |
| AA08 | Ver cobertura | Lectura del proceso, fecha/jornada y fuentes | Tabla de cobertura adoptada en 22A; AE12/AE13 |
| AA09 | Activar / Desactivar área | areas.cambiar_estado observado; política de relaciones por resolver | Confirmación persona no corresponde: identificar área y efectos. Sin borrado/cascadas; AE14/AE15 |
| AA10 | Ver reporte / PDF / CSV | Lectura/exportación pertinente y 26B/C; areas.reportes observado | Solo agregados aprobados; Excel o nominal no se conceden por presencia en Blade; AE16/AE17 |
| AA11 | Cancelar / Volver | Contexto de lectura; confirmar cambios sin guardar | Cerrar captura o regresar con filtros; AE18–AE20 |

## 6. Equipo, responsable y métricas

La fuente actual [AreasReportDataService](../../../../app/Modulos/Reportes/Aplicacion/Consultas/AreasReportDataService.php) deriva equipo de asignaciones con estado ACTIVA/ACTIVO, pero no acota fecha de jornada/turno ni estados de jornada, personal o área al preparar cada área. El responsable es la primera asignación con tipo RESPONSABLE/PRINCIPAL. Se debe corregir o etiquetar el alcance antes de usar «equipo actual», «responsable actual» o «disponibles hoy».

La vista especificada distingue dos consultas:

- **Asignaciones registradas:** filas y personas que cumplen el filtro elegido, mostrando jornada/fecha/turno y su estado. No equivalen a plantilla permanente.
- **Cobertura de una fecha/turno:** aplica el contrato temporal existente, con días/ventanas reales y fuente comprobable. La suficiencia de dotación no se calcula sin objetivo aprobado.

No afirmar «área sin personal» si no existen asignaciones dentro de un filtro particular; decir «Sin asignaciones registradas para este período». Una consulta fallida o restringida no genera conteo cero. Sin responsable documentado no implica puesto vacante oficial.

Si se usa una gráfica, barras de personas distintas por área con fecha/universo y tabla alternativa. La serie actual denominada evolución agrupa **registros de asignación por fecha_asignacion**, no personas presentes por mes; no llamarla crecimiento/disminución de plantilla ni completar una fecha desconocida con el mes actual como dato histórico.

## 7. Relaciones, seguridad y hallazgos estáticos

- [GestionarAreasInstitucionales](../../../../app/Modulos/Institucion/Aplicacion/Acciones/GestionarAreasInstitucionales.php) usa areas.crear/editar/cambiar_estado y ACTIVO/INACTIVO. [GestionarInstitucionHttp](../../../../app/Modulos/Institucion/Aplicacion/Acciones/GestionarInstitucionHttp.php) usa areas.gestionar y ACTIVA/INACTIVA; asignarPersonal exige área ACTIVA. No son rutas equivalentes por compartir entidad: un área recién creada por un recorrido puede no satisfacer otro.
- La Action normaliza nombre a mayúsculas después de validar unicidad; comprobar duplicados normalizados y conflicto SQL sin debilitar UNIQUE. Guardado y auditoría no están envueltos en una transacción conjunta visible en esta Action; no informar éxito parcial ante fallo de auditoría.
- El panel obtiene todos los usuarios activos para filtro de responsable. No basta ese estado: candidato pertinente debe proceder de relaciones/alcance autorizados; no exponer directorio de cuentas ajeno por un filtro.
- Las lecturas de render/detalle usan el servicio de reportes; no se ve en esos métodos una reautorización contextual de todas las fuentes tras revocar permiso. El middleware inicial no acredita todas las interacciones posteriores.
- La desactivación actual cambia estado sin revisar relaciones vigentes en esa Action. No extrapolarlo como regla institucional aprobada de transición.
- [AreasInstitucionalesExport](../../../../app/Modulos/Reportes/Infraestructura/Exportaciones/AreasInstitucionalesExport.php) incluye nombre de responsable; [UsuariosPorAreaExport](../../../../app/Modulos/Reportes/Infraestructura/Exportaciones/UsuariosPorAreaExport.php) produce filas nominales y datos de contacto. Esos contenidos no forman parte automática de la exportación agregada aprobada en 26B/C.

Hallazgos de código, no pruebas nuevas de explotación o ejecución. El [modelo Area](../../../../app/Models/Area.php) tiene relaciones a asignaciones, atenciones y planes de cuidado; el diccionario también describe actividades. Antes de cambiar vigencia, inventariar referencias y consumidores; no eliminar FK o asumir que el listado es el único consumidor.

## 8. Decisiones materiales no resueltas por 20A

**DA01: desactivación con relaciones vigentes — resuelta el 26/10/2026.** Se bloquea mientras existan asignaciones o planes activos. Los procesos autorizados deben trasladarlos o cerrarlos primero; desactivar no realiza esa transición ni borra relaciones. La comprobación se repite bajo bloqueo transaccional y los escritores relacionados revalidan el área. La decisión pendiente descrita en el corte inicial queda sustituida por esta decisión expresa.

**DA02: pertenencia o jefatura permanente.** Si se requiere un equipo fijo o jefe institucional independiente de jornadas, definir su alcance y verificar representación antes de ofrecer el campo. Por ahora la especificación usa asignaciones con contexto; no fabrica la relación ni cambia estructura para llenar un resumen.

No se reabre la decisión 20A ni se solicita una aprobación estructural genérica. Cualquier propuesta concreta de FK/columnas/catálogos sigue su diccionario, impacto, reversión y aprobación específica.

## 9. Presentación, accesibilidad y cierre

Un menú y un módulo, título legible, nombres humanos y contexto temporal. Superficies, tipografía Nunito Sans/Inter y colores según el [Design System institucional](../../../frontend/CONTRATO_VISUAL_UX_UI.md); estados con texto además del color. No semáforo de déficit inventado ni gráficos de riesgos clínicos desde conteos de personal.

Botones con foco visible, altura mínima 44 px, loading, disabled justificado y prevención de doble envío. Modal de área con título/labels, Escape/foco/retorno, errores por campo y confirmación de salida con cambios. Vacío, sin coincidencias, acceso restringido, error y datos ausentes separados. Tabla responsive, nombres largos y descripción extensa sin saturar; QA en los cinco tamaños institucionales.

[Veinte escenarios de aceptación](13-ACEPTACION-AREAS-EQUIPO.md). **Estado:** especificación trazada y hallazgos documentados; implementación, nuevas pruebas, permisos efectivos, política de desactivación y QA visual pendientes. No se modificó aplicación, BDD o datos en esta tarea documental.
