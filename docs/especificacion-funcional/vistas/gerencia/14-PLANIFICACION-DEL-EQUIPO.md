---
title: "Gerencia: planificación del equipo"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: APPROVED_FLOW_AND_STATIC_SCHEDULE_REVIEW
runtime_verified: false
---

# Planificación del equipo

## 1. Contrato y recorrido

Decisiones [21A y 23A](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos): semana inicial con alternativa mensual y tabla accesible; cambios confirmados avisan dentro del sistema a Administración y trabajadores afectados. Gerencia planifica; Administración abre jornadas y realiza asignaciones operativas según [baseline de roles](../../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). No se cambian límites de guardia, descanso o responsabilidades clínicas por diseñar el calendario.

Inicio → Planificación → seleccionar semana/área/profesión/persona → consultar horarios registrados → preparar operación autorizada → comprobar fechas, relaciones y conflictos → revisar cambios/destinatarios → confirmar guardado real → avisos internos y continuidad de Administración → [Cobertura](15-COBERTURA-DEL-EQUIPO.md).

El estado «Cambio sin guardar» representa captura en memoria; no se aprueba una entidad SQL de borrador ni una publicación de plan inexistente. Generar una planilla rotativa o mover un bloque visual no demuestra asignaciones persistidas. No guardar arrastrando sin confirmación de efectos y permiso.

## 2. Ruta y propietarios existentes

`admin.turnos-asignaciones.index` abre la página y [TurnosAsignacionesPanel](../../../../app/Modulos/Institucion/Presentacion/Livewire/Paginas/TurnosAsignacionesPanel.php), con variante calendario/planilla. Institucion conserva turnos/jornadas/asignaciones; Identidad proporciona personal; avisos reutilizan capacidades internas autorizadas. No crear agenda paralela.

`admin.turnos-enfermeria.index` corresponde a [TurnosEnfermeriaPanel](../../../../app/Modulos/Institucion/Presentacion/Livewire/Paginas/TurnosEnfermeriaPanel.php): catálogo de turnos, **no consulta completa de cobertura**. Las fichas anteriores que lo usaban como destino conceptual de Cobertura se corrigen mediante esta constatación; el destino real de la nueva tabla sigue por integrar, sin afirmar que basta renombrar ese catálogo.

[AsignacionTurnoPanel](../../../../app/Modulos/Institucion/Presentacion/Livewire/Paginas/AsignacionTurnoPanel.php) y [GestionarAsignacionesJornada](../../../../app/Modulos/Institucion/Aplicacion/Acciones/GestionarAsignacionesJornada.php) relacionan residentes con profesionales por jornada. Ese grano es distinto de asignaciones_personal laboral y de la planificación maestra; este calendario no modifica camas o niveles de supervisión por implicación.

## 3. Distribución y controles

| Zona | Componentes y contenido | Acción/efecto |
|---|---|---|
| Cabecera | «Planificación del equipo», descripción, Ver cobertura | Abre consulta conservando fecha/área si el destino lo permite |
| Barra temporal | Anterior, Hoy, Siguiente, rango semanal y selector Semana/Mes/Lista | Cambiar consulta; no guardar datos |
| Filtros | Área, profesión, trabajador y estado existente | Universo autorizado; limpiar explícitamente |
| Calendario | Siete columnas de días y bloques legibles por turno/horario | Abrir detalle, fecha de inicio/fin y cruce al día siguiente |
| Detalle lateral | Persona, área, jornada, turno y situación real; formulario solo según permiso | No perder contexto al cambiar selección con captura pendiente |
| Revisión | Resumen de cambios y destinatarios internos afectados | Confirmar o volver a corregir; sin aviso antes de guardar |
| Pie/contexto | Leyenda con texto, fuente y estado de consulta | Registrado distinto de cambio sin guardar o algoritmo de planificación |

Horario nocturno debe mostrar inicio y finalización con sus fechas cuando cambian de día. No deducir duración de 24/48 horas de hora_inicio=hora_fin: el catálogo actual rechaza horas iguales y necesita una representación temporal explícita para otros contratos.

## 4. Datos y persistencia

| ID | Campo o dato | Destino/fuente | Límite |
|---|---|---|---|
| PL01 | Persona | personal y cuenta vinculada; cod_personal en asignaciones_personal | Nombre humano, personal/cuenta aptos; no primer registro por defecto |
| PL02 | Área | areas / asignaciones_personal.cod_area | Existente y vigente para el caso; ACTIVO/ACTIVA debe conciliarse |
| PL03 | Turno | turnos.cod_turno, nombre y estado | Catálogo real; nombre por sí solo no determina ventana |
| PL04 | Inicio y fin del turno | turnos.hora_inicio/hora_cierre | Mostrar fecha de ambos extremos, conservar cruce de medianoche |
| PL05 | Fecha de jornada | jornadas.fecha_jornada | Distinta de fecha de registro; no crear jornada ABIERTA como efecto oculto de consulta |
| PL06 | Días seleccionados | Entrada que debe resolverse a jornadas concretas | Una selección semanal no demuestra repetición indefinida; mostrar fechas que se guardarán |
| PL07 | Inicio/fin de vigencia capturados | Entradas de formulario actuales | La fecha final no se conserva íntegramente en el recorrido actual; no prometer recurrencia hasta ella |
| PL08 | Función/tipo | asignaciones_personal.funcion/tipo_asignacion | Catálogos existentes, sin codificar otra entidad en texto |
| PL09 | Estado de asignación | asignaciones_personal.estado | Distinguir vigente, histórico y propuesta sin guardar |
| PL10 | Motivo/observación | observacion de asignación y actividad según su función | No JSON/EAV de horarios, grupos o vigencias como sustituto relacional |
| PL11 | Actor/instante de cambio | Sesión, fecha_asignacion y auditoría pertinente | fecha_asignacion no conserva sola todo el historial de modificaciones |
| PL12 | Destinatarios del aviso | Administración y personal afectado según cambio confirmado | No directorio global, no familiares, no correo externo |

Turnos define una ventana; jornadas una ocurrencia fechada; asignaciones_personal vincula trabajador/área/jornada. Una definición maestra recurrente y sus versiones no se simulan insertando fechas sueltas sin explicar qué se conservó.

## 5. Formularios y acciones

Permisos observados: turnos.ver/gestionar/crear/editar, asignaciones_personal.gestionar y turnos.asignar en consumidores distintos. No son equivalentes. Gerencia conserva el alcance institucional aprobado; las asignaciones operativas de Administración no se conceden por mostrar el calendario. Cada entrada debe mapear actor, permiso, estado y target en backend.

- **Crear/editar turno:** nombre, inicio, cierre, orden, estado y observación según catálogo existente. Cambiar un turno compartido puede afectar jornadas e historia: identificar consumidores antes de ejecutar y no recalcular el pasado como si sus horas originales hubieran cambiado.
- **Preparar horario:** persona/área/turno, fechas y días que correspondan al modelo autorizado. Mostrar las ocurrencias concretas antes de guardar. «Fecha final» no se declara soportada solo porque exista un input.
- **Revisar cambios:** diferencia legible anterior/nueva, conflictos y destinatarios. El botón no publica ni notifica.
- **Guardar y avisar:** revalidar relación, actividad, fechas, conflictos e identidad del actor; guardar atómicamente la operación que el modelo realmente soporte. Avisos solo después de commit confirmado; reintento sin duplicar horarios ni notificaciones.
- **Cancelar cambio:** descartar captura no guardada con confirmación, sin modificar historia.
- **Finalizar:** operación explícita con alcance/fecha real; no finja aplicación diferida si solo cambia estado inmediatamente.

Si el guardado funciona pero un aviso falla, informar ambos hechos por separado y permitir seguimiento/reintento seguro; no presentar rollback inexistente o duplicar el cambio para reenviar. Modo Ver como rol no admite mutaciones.

## 6. Conflictos y continuidad

Comprobar intervalos completos, fechas adyacentes y medianoche, excluyendo solo la asignación editada cuando proceda. Validar en backend, con concurrencia PostgreSQL desechable; colorear un evento no constituye prevención de conflicto. No inventar umbral de horas máximas o descanso mínimo sin contrato aplicable.

La baja laboral R03A o área inactiva afecta elegibilidad conforme al flujo; no borra horarios pasados. Administración revisa las asignaciones afectadas. DA01 sobre desactivar áreas con relaciones vigentes permanece identificada en [Áreas](12-AREAS-Y-EQUIPO.md#8-decisiones-materiales-no-resueltas-por-20a).

Los avisos internos incluyen qué cambió, fecha/turno, área y acceso a la fuente permitida, sin exponer datos de otros trabajadores/residentes. No enviar mensajes por arrastre, previsualización, carga inicial o regeneración de planilla.

## 7. Hallazgos estáticos a corregir al implementar

- [GestionarHorariosPersonal](../../../../app/Modulos/Institucion/Aplicacion/Acciones/GestionarHorariosPersonal.php) no recibe un actor explícito y depende del recorrido superior; comprobar autorización de cada mutación, no solo del componente al abrirse. Auditoría H02 identificó el problema relacionado.
- El formulario [PersonalInstitucionalHorarios](../../../../app/Modulos/Institucion/Presentacion/Livewire/Funcionalidades/PersonalInstitucionalHorarios.php) acepta fecha final, pero la llamada de guardado inspeccionada no la transmite; la Action resuelve una fecha por día seleccionado. No atribuir repetición de varias semanas a esa operación.
- El solapamiento filtra jornadas por las fechas seleccionadas antes de comparar; revisar días adyacentes y uso de hora_fin frente a hora_cierre. Auditoría H03 mantiene la regresión nocturna localizada.
- La Action serializa un grupo y observaciones en JSON dentro de observacion; no extenderlo como representación normalizada de planificación/historia. La regla del modelo congelado exige analizar una solución legítima antes de migrar.
- Finalizar recibe una fecha pero la actualización inspeccionada cambia estado de registros del grupo sin utilizarla como vigencia retenida. No mostrar «finaliza el día…» como contrato ya cumplido.
- [GestionarTurnosInstitucionales](../../../../app/Modulos/Institucion/Aplicacion/Acciones/GestionarTurnosInstitucionales.php) usa permisos y estado propios; la entrada HTTP alternativa no es automáticamente equivalente. Su modificación de turnos requiere revisar dependencia histórica.
- [GeneradorPlanillaEnfermeriaService](../../../../app/Modulos/Institucion/Infraestructura/Exportaciones/GeneradorPlanillaEnfermeriaService.php) calcula una planilla; no confundir todas sus casillas con filas laborales ya guardadas. Verificar la fuente de cada evento.

Hallazgos de lectura de código, no resultados nuevos de pruebas. El contrato aprobado no se limita por un bug, pero la UI no puede anunciar capacidades de vigencia/historia que no se conserven.

## 8. Diseño y aceptación

Referencia visual propuesta en [Diseño detallado](17-DISENO-PLANIFICACION-COBERTURA.md). No usar fotos de pacientes ni nombres reales en las imágenes. Tokens del Design System, superficies cálidas, calendario legible y paleta de estados con texto. Teclado, modo lista, foco, cancelación y movimiento reducido son requisitos de implementación.

[Veintiocho casos de aceptación compartidos con Cobertura](16-ACEPTACION-PLANIFICACION-COBERTURA.md). **Estado:** flujo y diseño especificados; implementación, capacidades relacionales de vigencia/historia, avisos, permisos efectivos y QA funcional pendientes. No se cambia aplicación o BDD ni se envían avisos en esta tarea.
