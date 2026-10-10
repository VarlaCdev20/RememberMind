---
title: "Fase 9B: tareas, ubicación y observaciones de Enfermería"
status: HISTORICAL
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> HISTORICAL — DO NOT USE AS CURRENT SOURCE OF TRUTH. Snapshot; nombres/deuda pueden haber cambiado desde la entrega.

# Fase 9B: tareas, ubicación y observaciones de Enfermería

## Alcance

Solo las tres tarjetas de la fila inferior del dashboard. Se conserva el grid
3 + 3 + 2 + 2 + 2, la identidad visual, los componentes compartidos y las rutas
existentes. No hay migraciones, permisos, endpoints, cambios clínicos ni polling
nuevo. La BDD Operativa V2.1 permanece congelada.

## Tareas del turno

Fuente: `ejecuciones_cuidado`, con `intervenciones_cuidado` y `personal` precargados.
En modo `EN_TURNO`, se limitan a los residentes del dashboard, la jornada activa
y el profesional actual. En consulta de equipo, se limitan a esos residentes,
jornadas activas y personal con rol ENFERMEROS. No se reutiliza la agenda como
checklist ni se añaden casillas de ejecución falsas.

Se cuentan todas las ejecuciones persistidas de ese alcance. Una completada
requiere simultáneamente estado `REALIZADA`, `COMPLETADA` o `FINALIZADA` y
`fecha_hora_ejecucion` no nula; `OMITIDA` no aumenta el progreso. La barra solo
aparece con total mayor a cero. El listado muestra a lo sumo cinco, ordenando
pendientes, en proceso, reprogramadas y otros estados, después por hora
programada real. Estado, responsable y prioridad provienen de datos guardados;
no se calcula vencimiento desde una regla nueva. Sin turno o sin registros se
explica la situación sin números simulados.

## Ubicación

Fuente: `ocupaciones_cama` con `cama.habitacion` precargadas. Se usa exactamente
el conjunto de residentes del dashboard. Solo se admite una ocupación `ACTIVA`
con cama `ACTIVA`, sin liberación y con asignación no futura. Si falta o hay
múltiples asignaciones verificables, el residente se cuenta como sin ubicación
verificable; nunca se elige la primera cama. Se muestran a lo sumo seis celdas
ordenadas por código de habitación/cama. Las alertas son el conteo real que ya
acompaña a cada residente en el dashboard. La cuadrícula no pretende ser un
plano arquitectónico y no inventa pisos/sectores ni datos de camas vacías.

## Conducta y estado emocional

Fuente: `registros_conductuales`, no mensajes familiares ni comentarios
administrativos. Se seleccionan registros vigentes de los residentes del
dashboard cuya `fecha_hora` esté entre ahora − 24 horas y ahora; por tanto,
puede informar una observación del relevo anterior. Se requiere una descripción,
un estado de ánimo estructurado o un cambio de conducta real. Orden descendente,
máximo tres. El texto no se interpreta para inferir emoción ni importancia,
pues el modelo no tiene nivel de importancia. Foto real o iniciales; fecha/hora
del registro. El texto extenso se limita visualmente a tres líneas solo cuando
existe enlace a la pestaña de seguimiento de la ficha para leerlo completo.

## Navegación, permisos y estados

Las tres lecturas respetan `ejecuciones_cuidado.ver`, `ocupaciones_cama.ver` y
`registros_conductuales.ver`. Los enlaces hacia la ficha se renderizan solo si
existe la ruta y el usuario tiene `enfermeria.ver_ficha_paciente`. Tareas enlaza
a `tab=cuidados`; notas, a `tab=seguimiento`; ubicación, a la ficha. No se crea
un acceso a detalle de ejecución o nota inexistente. Sin datos se distingue
ausencia de turno, ausencia de tareas/observaciones y falta de ubicación.
Los skeletons solo se muestran durante `refrescarTurno` de Livewire ya existente.

## Deuda técnica observada

La pantalla completa de tareas `admin.enfermeria.tareas` tiene referencias
preexistentes a relaciones `adultoMayor`, `responsable` y `jornada.turno` que no
existen con esos nombres en `EjecucionCuidado`; se omitió el enlace “Ver tareas”
para no dirigir a una vista potencialmente rota. Su corrección queda fuera de
esta fase. La BDD impide dos ocupaciones activas para el mismo residente, pero
la lectura conserva una defensa ante inconsistencia. No existe una entidad
genérica de checklist operativo distinta de ejecuciones de cuidado. Tampoco
hay una prioridad estructurada en `RegistroConductual`.
