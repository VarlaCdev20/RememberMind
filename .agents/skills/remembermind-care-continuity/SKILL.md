---
name: remembermind-care-continuity
description: "Conectar registros, intervenciones, pendientes y pase de turno de RememberMind con el siguiente punto operativo autorizado. Usar cuando información clínica queda aislada o un flujo atraviesa profesionales/turnos; no crea historia ni agenda paralelas."
---

# Purpose

Hacer que la información guardada llegue al profesional autorizado cuando debe actuar.

# Use when

Pendientes/omisiones, seguimiento de incidentes y alertas, planificación/ejecución de cuidado, pases y coordinación interdisciplinaria.

# Do not use when

Mostrar todos los datos a todos los roles, inventar nueva entidad tarea, duplicar datos longitudinales o imponer plazo clínico sin contrato.

# Mandatory sources

[Mapa funcional](../../../docs/sistema/MAPA_DOMINIO_FUNCIONAL.md), baseline de planes/medicación/jornadas/alertas, docs de turno, PaseTurnoService/MiTurnoService y sus llamadores/tests.

# Domain assumptions

Persistir es una parte del proceso. Planificado, ejecutado, omitido y pendiente significan cosas distintas. La continuidad debe respetar competencia, vigencia y ámbito informativo.

# Workflow

1. Trazar evento → registro → intervención → seguimiento → pendiente → pase → próximo profesional usando entidades y estados reales.
2. Mapear para cada dato origen, residente, vigencia/fecha, autor, receptor/permiso, momento, acción y consulta consumidora.
3. Verificar que omisiones, tareas vencidas según regla aprobada y alertas abiertas se conservan y aparecen en próximo punto correspondiente.
4. Reutilizar plan → intervención → programación → ejecución; no marcar cuidado como realizado por estar agendado. Con medicación conservar orden/horario/administración y omisión.
5. Emitir `ORPHAN CLINICAL INFORMATION` cuando el dato exista pero no llegue a su próximo uso: dato/origen, punto requerido, consumidor ausente, impacto y conexión propuesta.
6. Implementar conexión compatible en alcance; comprobar recepción/cierre según contrato sin inventar confirmaciones persistidas.

# Invariants

Continuidad no duplica historia, otorga permisos amplios ni convierte coordinación administrativa en acto clínico. Ninguna consulta oculta omisiones por filtrar solo éxitos.

# Failure conditions

Registro persistido invisible al turno siguiente; pendientes desaparecen al cambiar jornada; atención clínica asumida por notificación enviada; cuidado previsto etiquetado realizado.

# Escalation rules

Falta de receptor/responsabilidad, plazo o estado institucional aprobado requiere decisión localizada. Una consulta/conexión faltante compatible se corrige técnicamente.

# Tests required

WORKFLOW de dos momentos/actores autorizados: dato creado → pendiente visible → acción válida → historial preservado; negativo de receptor sin alcance; anulado/omitido y cambio de turno.

# Definition of Done

Cada información relevante tiene origen, siguiente uso y consumidor con permiso; pendientes/omisiones siguen visibles hasta transición válida.
