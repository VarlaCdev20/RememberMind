---
title: "Fase 9: distribución asistencial y agenda de Enfermería"
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

> HISTORICAL — DO NOT USE AS CURRENT SOURCE OF TRUTH. Evidencia de entrega localizada; límites clínicos no aprobados siguen en [decisiones](../DECISIONES_PENDIENTES.md).

# Fase 9: distribución asistencial y agenda de Enfermería

## Alcance

Solo se modifican las tarjetas de Distribución por nivel de atención y Agenda de
medicación y cuidados. Se conserva el grid de escritorio 3 + 3 + 2 + 2 + 2,
los tokens oficiales y los módulos de fases anteriores. Sin cambios estructurales
a la BDD Operativa V2.1 ni mutaciones de datos clínicos.

## Clasificación asistencial no disponible

Se revisó el diccionario vigente de 70 tablas y los modelos relacionados:

- `valoraciones_enfermeria_preadmision.dependencia_funcional`: dato de valoración
  de preadmisión, nullable y textual; no es una clasificación actual consolidada
  de los residentes del turno.
- `asignaciones_residente_jornada.nivel_supervision`: describe supervisión en una
  asignación; no existe regla validada que lo convierta en carga asistencial.
- `PlanCuidado::nivel_cuidado`: accessor de `prioridad` del plan, con fallback
  `MODERADO`; no es una clasificación asistencial del residente.

Por eso esta tarjeta muestra un valor no disponible y NO genera canvas, segmentos,
porcentajes ni datasets. Con residentes muestra “Clasificación asistencial no
disponible”; sin residentes, “No hay residentes asignados al turno”. El alcance
es el mismo array `residentes` del dashboard: asignación profesional en turno o
contexto del equipo en modo consulta. No se calcula una distribución global.

Deuda: definir y aprobar una fuente consolidada y sus categorías en el dominio,
sin reinterpretar riesgo, prioridades, edad, diagnóstico ni supervisión. Cualquier
necesidad estructural debe pasar por la gobernanza de la BDD congelada.

## Agenda

`MiTurnoService::prepararAgendaResumen()` consume la agenda unificada COMPLETA
antes del recorte histórico de diez filas. No hace consultas ni cambia estados.
La misma agenda alimenta `medicacion_turno` y la próxima atención del paciente.

Solo presenta eventos con hora programada explícita. Las ejecuciones heredadas
sin programación permanecen en la fuente original y sus históricos, pero no se
presentan en esta tarjeta con la hora de inicio del turno como sustituto.

Orden de presentación: RETRASADO, PRÓXIMO, PENDIENTE, REALIZADO; dentro de cada
estado, fecha/hora ascendente y desempate por acción y residente. Máximo cinco
eventos. Los tipos son MEDICACION, el tipo real del plan y CUIDADO. No se inventan
controles ni horarios. La tarjeta diferencia una omisión registrada de una
administración realizada, aunque el servicio use REALIZADO para ambos resultados.

Se conserva la regla del servicio: RETRASADO después de 60 minutos sin registro;
PRÓXIMO en los siguientes 60 minutos; no se introduce un umbral nuevo en Blade.
El componente reutilizable recibe hora, fecha ISO, acción, paciente, tipo, estado,
icono y motivo de omisión. Los eventos no tienen ruta individual comprobada y no
son falsamente clicables. “Ver agenda” usa `admin.enfermeria.agenda` cuando existe.

## Estados y presentación

Sin jornada activa se explica que no hay turno; con jornada y sin eventos
programados se informa esa ausencia específica. Skeleton de cuatro filas durante
el refresco Livewire real. Sin scroll interno, animaciones continuas, fotografías,
colores nuevos ni librerías añadidas. Lista ordenada semántica, hora con datetime,
estados textuales y check para eventos realizados. Los títulos pueden envolver
para mantener legibilidad en las tarjetas de tres columnas y en móviles.

## Deuda preexistente del servicio

La agenda original clasifica las ejecuciones directas con una hora sustituta si
no hay programación y puede resolver una ejecución de cuidado solo por jornada.
Esta fase no modifica esas reglas clínicas ni los contadores existentes; excluye
el primer caso de la tarjeta mediante metadatos explícitos. El enlace a agenda
completa conserva su implementación actual, que requiere una auditoría separada
de coherencia entre vistas. El resumen no intenta reemplazarla ni reescribirla.
