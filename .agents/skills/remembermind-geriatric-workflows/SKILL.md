---
name: remembermind-geriatric-workflows
description: "Reconstruir procesos geriátricos institucionales de RememberMind por actores, estados y continuidad: admisión, turno, cuidado, medicación y trabajo interdisciplinario. Usar antes de implementar o reorganizar un proceso; no inventa protocolos clínicos."
---

# Purpose

Asegurar que el caso de uso forme parte de un proceso residencial real.

# Use when

Admisión/alojamiento, jornada y cobertura, seguimiento/cuidado diario, participación interdisciplinaria, actividades/visitas y procesos con pasos de varios actores.

# Do not use when

Retoques CSS, interpretación clínica o un nuevo requisito de consentimiento, periodicidad o competencia sin fuente aprobada.

# Mandatory sources

[Mapa funcional](../../../docs/sistema/MAPA_DOMINIO_FUNCIONAL.md), documentación vigente del proceso, baseline/diccionario/decisiones, roles, Actions/Servicios/Policies y tests actuales.

# Domain assumptions

Postulante, residente, usuario, personal y contacto son entidades distintas. Operación residencial, actividad social, documentación, clínica y cuidado se relacionan sin confundirse.

# Workflow

1. Mapear inicio, propósito, actor/competencia, datos requeridos, estados reales, transiciones, salida y siguiente responsable.
2. Para admisión: preadmisión PENDIENTE → revisión → APROBADA/RECHAZADA; aprobación no crea residente; formalización con cama y requisitos documentados. Resolver valores físicos divergentes, no improvisarlos.
3. Para turno: contexto laboral/asignaciones → residentes → controles/medicación/cuidados → incidencias/alertas → pendientes → pase y recepción según contrato.
4. Diferenciar prescripción, horario y administración; plan, intervención, programación y ejecución. No presentar programado como realizado.
5. Incluir interrupción, error, omisión, duplicado/reintento, recuperación y seguimiento.
6. Entregar tabla paso/actor/estado/entrada/salida/permiso/consumidor y casos negativos; usar care-continuity si hay traspaso.

# Invariants

Cama ocupada indisponible; residente sin dos ocupaciones activas; operación atómica cuando procede. Jornada/área no se impone universalmente a actos que Policy vigente permite fuera de turno. Familiar solo información autorizada del vínculo; roles futuros/voluntariado no crean privilegios.

# Failure conditions

Flujo reducido a CRUD, aprobación que crea residente, actores sin responsabilidad real, seguimiento sin consumidor o obligaciones clínicas deducidas de costumbre externa.

# Escalation rules

Una periodicidad, obligación clínica/legal o responsabilidad nueva requiere decisión institucional concreta. Si fuentes resuelven la regla, aplicar sin nueva consulta.

# Tests required

WORKFLOW con recorrido permitido y bloqueos por estado/actor, ocupación y rollback; verificar destino de pendientes y omisiones. Concurrencia física en PostgreSQL cuando relevante.

# Definition of Done

Proceso y excepciones conectados, estados trazables, autorización aplicable y siguiente punto operativo identificado; sin inventar reglas.
