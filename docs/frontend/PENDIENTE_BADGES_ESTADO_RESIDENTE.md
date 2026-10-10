---
title: "Pendiente: etiquetas de estado en «Mis residentes»"
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

# Pendiente: etiquetas de estado en «Mis residentes»

La tarjeta de Enfermería queda **sin etiqueta superior** «Estable», «Vigilancia» o «Riesgo» hasta que la institución apruebe una regla clínica para esos estados. La ausencia de alertas no demuestra estabilidad clínica; la prioridad de una alerta y el nivel de supervisión tampoco equivalen por sí solos a una clasificación general del residente.

Antes de implementarlas, definir para cada etiqueta: registro y profesional que la determinan, criterios, fecha/hora de vigencia, prioridad entre estados simultáneos y qué mostrar si falta información. Reutilizar datos y permisos V2 existentes; cualquier cambio estructural requiere la gobernanza de la BDD congelada.
