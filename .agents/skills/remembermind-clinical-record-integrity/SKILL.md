---
name: remembermind-clinical-record-integrity
description: "Preservar autoría, fecha clínica, contexto y longitudinalidad en registros clínicos de RememberMind. Usar al crear, corregir, anular o consultar historia; no define interpretación clínica ni sustituye la integridad relacional general."
---

# Purpose

Conservar la verdad temporal y la procedencia de cada evento clínico.

# Use when

Controles, atenciones/notas, valoraciones, resultados, administración de medicación, cuidados e instrumentos; correcciones o históricos.

# Do not use when

Sobrescribir la historia para reflejar estado actual, inventar campos de anulación/versionado, elegir umbrales o conceder competencia.

# Mandatory sources

[app/AGENTS](../../../app/AGENTS.md), baseline/diccionario y decisión de autoría pertinentes, contrato clínico, Model/Action/Policy afectados, registros consumidores y pruebas históricas.

# Domain assumptions

Nuevo evento clínico implica nuevo registro longitudinal según grano del dominio. Fecha del hecho puede diferir del timestamp técnico. Estado actual es una lectura derivada; no reemplaza eventos anteriores.

# Workflow

1. Identificar residente, autor personal y actor usuario, atención, jornada/contexto y fecha/estado solo donde el esquema los contempla.
2. Derivar identidad clínica desde sesión y contexto permitido; validar atención y recurso del mismo residente.
3. Definir creación vs corrección. Usar mecanismo aprobado existente: anulación, suspensión, cierre, corrección, registro compensatorio/enlace cuando existe. No inventar columnas o adendas persistibles sin modelo.
4. Preservar registro original y razón/procedencia exigidas; impedir delete ordinario y sobreescritura encubierta también en rutas alternativas.
5. Verificar lectores históricos, fecha de corte y presentación de anulados/corregidos según contrato.
6. Coordinar audit técnico mínimo con observability-audit y próximo uso con care-continuity si aplica.

# Invariants

No atribuir a otro profesional por input, crear autor fallback, borrar historia física ni confundir cambio de UI con modificación clínica. Nuevo evento ≠ actualización destructiva del anterior.

# Failure conditions

Historia desaparece tras guardar, corrección sin vínculo/mecanismo vigente, timestamps técnicos usados como fecha del hecho sin contrato, lectura de última fila presentada como evolución completa.

# Escalation rules

Si falta mecanismo estructural de corrección o criterio clínico, documentar DOMAIN GAP y aislar operación; no agregar campo para destrabar el formulario.

# Tests required

Nuevo evento conserva anterior; corrección/anulación conserva origen y lectores; autor manipulado/recurso cruzado denegados sin cambios; fecha clínica y orden longitudinal correctos.

# Definition of Done

Evento y contexto reconstruibles, origen preservado, corrección aplicable verificable, permisos y lectores históricos coherentes.
