---
name: remembermind-module-delivery
description: "Arquitectar y entregar un módulo o flujo completo de RememberMind coordinando contratos, backend, UX, integración, seguridad, persistencia y pruebas. Usar para cambios sustanciales entre capas; absorbe module-architect y selecciona especialistas pertinentes, sin cargarlos todos."
---

# Purpose

Coordinar una entrega coherente de proceso; cubre la responsabilidad module-architect sin crear coordinador duplicado.

# Use when

“Completa el módulo”, “implementa el flujo”, una funcionalidad sustancial o reorganización entre varias capas/roles.

# Do not use when

Un retoque localizado, gate de lectura o propuesta de otra arquitectura. Seleccionar surgical-patch/safe-refactor/verify-and-stop si basta su alcance.

# Mandatory sources

AGENTS aplicables, [pipeline del sistema](../../../docs/sistema/STACK_SKILLS_SISTEMA.md), fuentes/contrato del módulo, código/tests/consumidores existentes y UX cuando haya pantalla.

# Domain assumptions

Completitud requiere actores, estados, reglas, autorización, persistencia, navegación, continuidad y evidencia; no solo archivos. Selección proporcional evita cargar todas las skills por defecto.

# Workflow

1. Inspeccionar git status/diff y fuentes con source-of-truth; investigate-first si causa incierta.
2. Definir alcance/aceptación, guardrails y DOMAIN DESIGN con domain-architect; workflow y matriz de permisos pertinentes.
3. Mapear entradas/backend/relaciones/routes/UI/consumidores, invariantes, transacción, audit y pruebas; reutilizar componentes y código V2.
4. Seleccionar integridad/continuidad/alertas/experto solo si el caso los toca. Para UI, remembermind-ui-review elige especialidades; ui-ux-pro-max es auxiliar puntual, no paso obligatorio.
5. Consolidar plan; en trabajo complejo reviewers de lectura y un implementador. Nunca agentes editando simultáneamente los mismos archivos.
6. Implementar mínimo flujo completo y corregir bloqueos directamente relacionados de autorización/V2/legacy; no ampliar a reescritura global.
7. Probar con system-testing y gates pertinentes, build/QA visual cuando aplica; subsanar hallazgos fuera del gate y volver a comprobar lo afectado.
8. Release-check coordina evidencia y commit local. Reportar Implementado, Integración, Seguridad, Pruebas, Build, Legacy, Commit, Pendientes; no push automático.

# Invariants

Sin cambios sensibles/estructurales no autorizados, CRUD directo de residente, operación clínica solo en UI, pseudopersistencia, placeholders esenciales ni capas V1 permanentes.

# Failure conditions

Módulo no conectado a navegación, datos sin consumidor, pruebas/build/QA declarados por suposición o entrega que oculta un pendiente esencial.

# Escalation rules

Aislar decisión material no resuelta y continuar plan independiente. No pedir aprobación de cada archivo, extracción o componente reversible ya autorizado.

# Tests required

Matriz proporcional positiva/negativa por estado/actor, invariantes/persistencia, recorrido y regresión; integración PostgreSQL y UI cuando riesgo lo exija.

# Definition of Done

Contrato aceptado implementado de punta a punta, consumidores conectados, evidencia suficiente por dimensión, legacy relacionado eliminado y commit de alcance propio verificable.
