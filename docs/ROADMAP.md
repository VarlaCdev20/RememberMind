---
title: "Roadmap documental de RememberMind"
status: CURRENT
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

# Roadmap

Mapa de secuencia; no prueba de implementación. NOW recoge encargo activo. NEXT/LATER/FUTURE son propuestas o candidatas, sin fechas ni compromisos nuevos. Cambios de permisos, clínica/estructura o privacidad necesitan la decisión correspondiente.

# NOW

| Área | Objetivo | Dependencia | Estado | Documento relacionado |
|---|---|---|---|---|
| Contratos documentales | Fase 2: arquitectura, dominio, seguridad, siete módulos y trazabilidad | Fuentes/contratos y revisión independiente final | ENTREGADO con PASS documental estático; runtime false | [TRAZABILIDAD](TRAZABILIDAD.md) |
| Fase 3 | Hardening A–E y evidencia runtime del núcleo | Contratos vigentes y PostgreSQL desechable | CURRENT, hardening acotado verificado; gate global FAIL | [TRAZABILIDAD](TRAZABILIDAD.md) |
| Producto previo | Conservar trabajo sin commit de legacy/integraciones | Revisión posterior de su propietaria | Preservado; no reset ni commit | ESTADO_ACTUAL |

# NEXT

Fase 3 fue autorizada expresamente y se registra en NOW. NEXT conserva trabajo pendiente/Fase 4 propuesto; no autoriza implementación adicional.

| Área | Objetivo | Dependencia | Estado | Documento relacionado |
|---|---|---|---|---|
| Arquitectura/dominio/seguridad | Completar contratos de otros dominios y mantener trazabilidad tras futuros cambios | Cierre Fase 2 y fuentes específicas | PROPUESTA | [Mapa funcional](sistema/MAPA_DOMINIO_FUNCIONAL.md); DECISIONES_PENDIENTES |
| Familia/archivos/reportes | Verificar y corregir filtros por contenido según contrato | Pruebas de reproducción y frontera aprobada | PROPUESTA prioritaria | TECH-001, DEC-OPEN-006 |
| Alertas | Contrastar todos los caminos de creación/transición/eventos | Contrato vigente por operación | PROPUESTA | TECH-002 |
| BDD/integración | Resolver estados y acreditar garantías/rollback/carreras PostgreSQL | Decisión de catálogo y entorno desechable | PROPUESTA | DEC-OPEN-001/002, TECH-005 |

# LATER

| Área | Objetivo | Dependencia | Estado | Documento relacionado |
|---|---|---|---|---|
| Módulos | Documentación funcional por disciplina, entradas/salidas/excepciones y pruebas | Contratos de Fase 2 | PROPUESTA | sistema/MAPA_DOMINIO_FUNCIONAL |
| Histórico | Evaluar migración a docs/historico con inventario de enlaces entrantes | No romper README/AGENTS/skills/referencias | PROPUESTA, sin traslado masivo hoy | GOBERNANZA_DOCUMENTAL |
| UX | Consolidar aplicación del DS y cobertura visual/responsive/accesibilidad | Referencias/contratos y evidencia real | PROPUESTA | frontend/CONTRATO_VISUAL_UX_UI |
| Calidad | Trazabilidad requisito → regla → código → test | Requisitos y contratos vigentes | PROPUESTA | tests/AGENTS; GOBERNANZA_DOCUMENTAL |

# FUTURE

Candidatas coherentes; no funcionalidades existentes ni promesas:

| Área | Objetivo | Dependencia | Estado | Documento relacionado |
|---|---|---|---|---|
| UX | Consolidación completa DS, accesibilidad avanzada, pruebas visuales y responsive exhaustivo | Contratos y QA real | CANDIDATA | frontend/CONTRATO_VISUAL_UX_UI |
| Consultas | Mejoras de reportes/dashboards y performance | Necesidad y medición concreta | CANDIDATA | TECH-001; stack sistema performance |
| Operación | Observabilidad y recuperación ante desastres acreditadas | Entorno, privacidad y simulacro | CANDIDATA | produccion/DESPLIEGUE_SEGURO |
| Integraciones | Interoperabilidad/servicios externos | Decisión institucional, privacidad y contrato técnico | CANDIDATA, no integración aprobada localizada | DECISIONES_PENDIENTES |
| Experto | Motor, base de conocimiento versionada, explicación avanzada, validación humana y métricas | Método/derechos/fuentes/datos y estructura aprobados | PROPUESTA dependiente | DEC-OPEN-005; sistema-experto/README |
| Documentación | Automatización y validación documental CI | Convención y casos reales | CANDIDATA; no configurar CI en Fase 1 | GOBERNANZA_DOCUMENTAL |

# OUT OF SCOPE

Fuera de Fase 3: nuevas funcionalidades, refactor global, cambios estructurales BDD/índices, reglas clínicas/competencias nuevas, rediseño UX/UI general, sistema experto, deploy, commit/push. El hardening autorizado se limita al núcleo y contratos vigentes; no se autoriza corregir cualquier deuda por aparecer aquí.

Fuera del contrato permanente: diagnóstico/tratamiento autónomo que sustituya profesionales, nueva dependencia V1 permanente, permisos por conveniencia y expansión del esquema sin aprobación. Historia experta/arquitectónica no autoriza nuevas tecnologías o tablas.

## Actualización de ejecución Fase 3

Fase 3 pasó de propuesta a CURRENT por instrucción expresa de la propietaria. Lotes A–E implementan hardening acotado y verificación en PostgreSQL desechable; evidencia y límites en [TRAZABILIDAD](TRAZABILIDAD.md). No amplía BDD, permisos clínicos, catálogos, publicación Familiar ni metodología experta. Suite final FAIL (14 fallos previos más una expectativa Familiar revalidada después); cohortes del núcleo, build y reset/seed final PASS. Fallo JS de sidebar fuera del alcance se mantiene visible; evidencia exacta y límites en TRAZABILIDAD.

Fase 4 permanece PROPOSED: resolver calidad global pendiente con alcance aprobado antes de iniciar rediseño general, y tratar DEC-OPEN-001..006 únicamente mediante decisiones de la propietaria. No hay commit/push ni autorización de cambios futuros por esta recomendación.
