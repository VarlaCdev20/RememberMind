---
name: remembermind-observability-audit
description: "Instrumentar y revisar trazabilidad técnica y auditoría aprobada de RememberMind con Activitylog, errores y contexto mínimo. Usar en operaciones relevantes, diagnóstico y eventos de negocio; no reemplaza procedencia clínica ni añade otra tabla corporativa o servicio externo."
---

# Purpose

Reconstruir acciones y fallos sin duplicar historia clínica o exponer datos sensibles.

# Use when

Mutaciones institucionales/clinicas, errores, jobs, correlación de operaciones y revisión de configuración real de auditoría.

# Do not use when

Guardar expediente completo en logs, crear audit table paralela, implementar analítica externa de datos clínicos o usar log técnico como evento clínico.

# Mandatory sources

[app/AGENTS](../../../app/AGENTS.md), baseline de proveniencia/eventos/auditoría, docs pertinentes, composer.lock de Activitylog y configuración/providers/modelos/acciones existentes.

# Domain assumptions

Actor técnico y autor clínico tienen propósitos distintos. Registro de dominio prueba el hecho clínico; Activitylog registra actividad aprobada; report/log de Laravel diagnostica fallos.

# Workflow

1. Identificar qué acción requiere auditoría según contrato y qué evidencia ya existe en dominio.
2. Verificar Activitylog realmente instalado/configurado y consumidores; reutilizarlo sin segundo sistema corporativo.
3. Definir quién, qué, cuándo, sobre quién/recurso, cambios autorizados y resultado con datos mínimos; referencia/correlación existente cuando útil, no columna nueva implícita.
4. Alinear auditoría de éxito con la confirmación de la transacción; reportar fallos inesperados por mecanismos Laravel y respuesta segura. No capturar errores para devolver éxito.
5. Excluir secretos/tokens/documentos y payload clínico completo; limitar serialización y acceso a logs.
6. Para jobs/notificaciones revisar transacción, reintentos y trazabilidad de resultado sin atribuir entrega o actuación inexistente.
7. Verificar continuidad del evento clínico independientemente del log técnico.

# Invariants

No auditoría corporativa duplicada, logs con PII clínica excesiva, stack/SQL al usuario, catch vacío ni éxito sin operación confirmada.

# Failure conditions

Audit success tras rollback, autor confundido con usuario, evento de alerta reemplazado por Activitylog o diagnóstico sensible divulgado.

# Escalation rules

Nuevo tratamiento/almacenamiento externo de datos, obligaciones legales o retención nueva requieren decisión; instrumentación compatible mínima es elección técnica.

# Tests required

Operación exitosa produce audit aprobado con actor/recurso; fracaso no produce éxito falso; rollback consistente; secreto/payload excluido y autorización de lectores.

# Definition of Done

Operación reconstruible con contexto mínimo; errores seguros; auditoría y proveniencia separadas; sin infraestructura externa no autorizada.
