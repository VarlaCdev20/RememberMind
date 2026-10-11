---
title: "Dashboards por rol: fuentes y límites"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: true
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> Contrato/consultas por rol; no certifica completitud de módulos. Estado comprobado por lectura en [ESTADO_ACTUAL](../ESTADO_ACTUAL.md).

# Dashboards por rol: fuentes y límites

Los paneles comparten `dashboard-header`, `metric-card` y bloques de datos del Design System. La selección visual de bloques no concede permisos. Las rutas y Policies existentes siguen siendo la autoridad para abrir cada módulo.

| Rol | Datos verificables para el dashboard | Fuente actual | Límite |
| --- | --- | --- | --- |
| Superadministrador | Residentes admitidos, camas disponibles, alertas abiertas prioritarias, personal asignado hoy, actividades próximas, preadmisiones por estado, cobertura por área, actividad clínica agregada, administraciones, incidentes y documentos validados | `residentes`, `camas`, `alertas`, `asignaciones_personal` + `jornadas`, `actividades`, `preadmisiones`, `atenciones`, `administraciones_medicacion`, `incidentes`, `documentos` | No hay tabla `egresos`; no presentar ingresos contra egresos como serie verificable. La administración de medicación no tiene denominador universal para un porcentaje global. |
| Gerente | Personal activo, cobertura de jornada, distribución por área y actividades próximas | `personal`, `asignaciones_personal` + `jornadas`, `actividades` | No inferir competencia clínica ni cobertura futura a partir de una sola jornada. |
| Administrador | Preadmisiones, admisiones, camas, residentes, jornadas, actividades, visitas y alertas operativas | `CentroCoordinacionService` y su dashboard existente | No exponer escritura clínica ni tratar todos los documentos como compartibles. |
| Médico | Residentes atendidos en 90 días, atenciones futuras, alertas clínicas prioritarias, estudios propios sin realización, prescripciones y notas recientes | `atenciones`, `alertas`, `estudios_clinicos`, `prescripciones`, `notas_clinicas` filtrados por el profesional cuando corresponde | No existe `valoraciones_medicas` ni una regla universal de valoración pendiente en el modelo congelado; no mostrar un contador inferido. Las acciones previas de valoración y dictamen conservan su cola. |
| Enfermería | Residentes asignados, cuidados y medicaciones pendientes, alertas y agenda del turno, incidentes y registros recientes | `DashboardTurno`, `MiTurnoService`, `DashboardComplementosService` | No convertir ocupación global de camas en tarea del turno ni permitir prescribir. Se retiraron visualizaciones repetidas de ocupación e incidentes. |
| Psicología | Residentes atendidos en 90 días, instrumentos aplicados propios, atenciones del día, alertas a cargo y próximas atenciones | `atenciones`, `aplicaciones_instrumento`, `instrumentos`, `alertas` filtrados por `personal` | No hay un criterio institucional verificable para “valoraciones pendientes”; un cero fijo no demuestra ausencia de registros. |
| Nutrición | Residentes atendidos, valoraciones, planes a cargo, mediciones, alertas y atenciones programadas | `atenciones`, `valoraciones_nutricionales`, `planes_cuidado`, `mediciones_antropometricas`, `alertas` filtrados por `personal` | No derivar estado nutricional nuevo ni anunciar citas que no estén registradas como PROGRAMADA/PENDIENTE. |
| Fisioterapia | Residentes atendidos, valoraciones, planes, dolor, alertas y atenciones programadas | `atenciones`, `valoraciones_funcionales`, `planes_cuidado`, `valoraciones_dolor`, `alertas` filtrados por `personal` | No inferir severidad funcional ni sesiones futuras fuera de las atenciones registradas como PROGRAMADA/PENDIENTE. |
| Pedagogía | Actividades propias, participantes, seguimientos y planes a cargo | `actividades`, `participantes_actividad`, `seguimientos_pedagogicos`, `planes_cuidado` filtrados por `personal` | No convertir controles cognitivos en diagnóstico. |
| Familiar | Residente vinculado con autorización de información, actividades vinculadas y visitas propias | `contactos.cod_usuario` → `residentes_contactos` activos con `autoriza_informacion` → `residentes`, `participantes_actividad`, `visitas` | `documentos` no tiene marca de publicación para familiares: mostrar estado vacío hasta que exista una decisión institucional y un mecanismo de autorización. Nunca consultar notas o historia clínica para este panel. |

Cada recuento debe indicar su período. Las listas muestran un máximo de registros recientes o próximos y un estado vacío cuando no existen datos. La información familiar se restringe en la consulta, antes de renderizar.
