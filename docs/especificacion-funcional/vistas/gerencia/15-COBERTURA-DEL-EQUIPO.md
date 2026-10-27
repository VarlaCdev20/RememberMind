---
title: "Gerencia: consulta de cobertura del equipo"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: APPROVED_COVERAGE_TABLE_AND_STATIC_SOURCE_REVIEW
runtime_verified: false
---

# Cobertura del equipo

## 1. Objetivo y contrato

Decisión **22A**: tabla por área y turno con filtros de fecha y detalle de origen. Consultar asignaciones registradas, no determinar suficiencia de dotación o asistencia confirmada sin evidencia. [Decisiones adoptadas](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos) y [planificación](14-PLANIFICACION-DEL-EQUIPO.md).

Gerencia selecciona fecha/área/turno → consulta tabla → abre detalle → comprueba fuente → continúa a ficha autorizada o planificación. La consulta no asigna trabajadores/residentes, no abre jornadas y no modifica turnos.

## 2. Ubicación y diferencia con la navegación actual

Entrada prevista **Cobertura** y botón Ver cobertura desde Planificación. La ruta actual `admin.turnos-enfermeria.index` abre catálogo de turnos, no la tabla que aquí se especifica. Mantener ese catálogo en su función, conectar la consulta nueva con servicios/entidades reales y verificar su ruta al implementar. No inventar una ruta instalada o un segundo dashboard para simular cierre.

Propietario Institucion, consultas de jornadas/asignaciones y lectura autorizada de personal. [TurnosAsignacionesPanel](../../../../app/Modulos/Institucion/Presentacion/Livewire/Paginas/TurnosAsignacionesPanel.php), [Area](../../../../app/Models/Area.php), [AsignacionPersonal](../../../../app/Models/AsignacionPersonal.php) y [jornadas](../../../../database/migrations/2026_09_18_001012_create_jornadas_table.php) son referencias de integración, no aceptación de todas las métricas actuales.

## 3. Composición y tabla

Cabecera «Cobertura del equipo», subtítulo «Asignaciones registradas por área y turno» y Ver planificación. Filtros visibles: fecha, área, turno y estado admitido por el dominio. Aplicar y Limpiar no generan registros. Resumen contextual explica qué jornada/ventana se está consultando.

| ID | Columna o dato | Origen y significado |
|---|---|---|
| CO01 | Fecha/jornada | jornadas.fecha_jornada y estado; límites de ventana del turno |
| CO02 | Área | areas.nombre/estado, relación real de asignación |
| CO03 | Turno y horario | jornadas.cod_turno → turnos; fechas completas al cruzar medianoche |
| CO04 | Personal asignado | personal de las asignaciones válidas del contexto; persona distinta dentro de ese universo |
| CO05 | Función/tipo | funcion/tipo_asignacion reales, sin inferir profesión desde un color |
| CO06 | Situación | Estado registrado de jornada/asignación, distinto de asistencia |
| CO07 | Detalle | Identidad interna de la fuente, presentada con nombres/fechas; no código técnico visible |
| CO08 | Resumen/gráfico opcional | Personas distintas por área y total institucional único, con universo/período explícitos |

Cada fila puede agrupar área/turno/contexto con detalle de personas. El total global deduplica trabajadores multiarea; número de asignaciones se presenta separado. Una jornada de ayer que sigue en curso puede pertenecer a la ventana actual según contrato vigente; no excluirla por filtrar solo la fecha civil de hoy.

## 4. Controles y detalle

- **Aplicar/Limpiar:** consulta autorizada, loading y errores visibles; no reemplazar una consulta fallida por ceros.
- **Ver detalle:** drawer con fecha, área, persona, función, jornada, horario, estado y fuente. Solo incluye identidad laboral necesaria, sin credenciales, expedientes privados o historia clínica.
- **Abrir ficha:** requiere permiso y alcance de personal además de lectura de cobertura.
- **Ver planificación:** abre el proceso manteniendo contexto donde exista; no habilita editar por el solo botón.
- **Cerrar detalle/Volver:** restaura foco/selección y filtros, sin recalcular un dato histórico con estado actual ocultamente.
- **Ver tabla de datos:** alternativa accesible a cualquier gráfica; mismo dataset que los conteos, sin solicitudes externas.

No botones de cerrar alerta clínica, prescribir, aprobar valoraciones o crear residentes en esta consulta. Exportar respeta 26B/C de indicadores agregados; no implica descarga del listado nominal que se muestra autorizado en pantalla.

## 5. Semántica de ausencia y gráficos

Mostrar **«Sin asignaciones registradas para este filtro»** cuando la fuente consultada no tiene filas. No «turno descubierto», «déficit», «riesgo» o porcentaje suficiente sin objetivo/aprobación aplicables. Acceso restringido, datos incompletos y error son estados distintos.

Una barra opcional de personas asignadas por área facilita comparar registros, acompañada de valores y tabla. No escala de seguridad clínica, radar o semáforo de dotación. El número cero significa cero personas con asignación dentro del universo consultado, no falta comprobada de trabajadores en la institución.

No usar el porcentaje de cuentas ACTIVO como indicador de asistencia, ni cada celda de planilla rotativa como registro laboral confirmado. El detalle debe permitir seguir fila → asignación → jornada → turno → persona/área reales.

## 6. Autorización, integración e historia

Cuenta activa + permiso de lectura correspondiente + alcance de cada fuente. El permiso concreto de entrada de cobertura se mapea con los existentes al implementar; `jornadas.ver` o `turnos.ver` no autoriza por sí solo ver todo el expediente laboral. Revalidar tras cada cambio de filtros/ID y después de revocación; no cargar el dataset completo y ocultarlo en frontend.

Conservar diferencias entre planificación, jornada abierta, asignación vigente y atención registrada. La tabla no sustituye una instantánea histórica: si horas del catálogo de turno o estados fueron sobrescritos, indicar la limitación de reconstrucción. No presentar como corte histórico exacto una consulta hecha con datos actuales.

La ruta de Cobertura y los adaptadores pertinentes siguen pendientes; no se cambia el catálogo de turnos de nombre para aparentar nueva funcionalidad. Los defectos de permisos/medianoche ya localizados continúan siendo correcciones requeridas de implementación.

## 7. Diseño y cierre

Propuesta visual en [detalle de pantallas](17-DISENO-PLANIFICACION-COBERTURA.md), con tabla principal y drawer. Tokens institucionales, texto de estado además del color, fila seleccionada legible, controles de al menos 44 px y foco. Responsive conserva encabezados/alternativa apilada, no una tabla ilegible reducida.

[Veintiocho escenarios compartidos](16-ACEPTACION-PLANIFICACION-COBERTURA.md). **Estado:** especificación, composición e integración localizadas; implementación, ruta efectiva, pruebas y QA en navegador pendientes. Las imágenes de datos ficticios no prueban funcionamiento.
