---
title: "Base de datos: inventario y decisiones"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [schema_routing]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
---

# Base de datos de RememberMind

## Estado vigente

La [extensión de Gerencia35/37 autorizada e instalada el27/10/2026](DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md) deja **81 operativas +25 expertas =106 tablas de negocio**, además de **16 técnicas**:122 físicas. Añade seis relaciones y ocho columnas nullable, con contenido previo conservado. La [consolidación del22/10](DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md) y la auditoría del25/10 describen el inventario anterior de100 de negocio y116 físicas.

El [máximo de diseño de 110](../sistema-experto/DECISION_MAXIMO_110_TABLAS_2026_10_21.md) sigue siendo capacidad de diseño, no aprobación de DDL. Su presupuesto inicial de seis relaciones correspondía al inventario entonces instalado de 104; no es un presupuesto fijo perpetuo ni autoriza diez tablas ahora.

## Fuentes rectoras

- [Decisión de documentación e historia de Gerencia27/10](DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md), con diccionarios35/37 y evidencia local.
- [Gobierno BDD](../../database/AGENTS.md).
- [Baseline rector](REMEMBERMIND_BDD_BASELINE_CONGELADO.md): núcleo y extensiones aplicables.
- [Diccionario del núcleo V2.1](REMEMBERMIND_BDD_70_TABLAS.md): el nombre histórico no representa el total instalado.
- [Reducción anterior del 20/10](DECISION_REDUCCION_EXPERTA_2026_10_20.md) → [consolidación posterior del 22/10](DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md). Las dos decisiones conservan sus alcances y transiciones.

La conservación conjunta instrumental/Petersen no está instalada. Una referencia SQL de ensayo no modifica este inventario ni acredita integridad institucional.

## Decisiones estructurales localizadas

El estado y alcance de cada decisión deben leerse dentro de su documento; el título no basta para aprobar otra modificación.

- [DECISION_ARQUITECTURA_BDD_V2_1.md](DECISION_ARQUITECTURA_BDD_V2_1.md)
- [DECISION_CODIGOS_CORRELATIVOS.md](DECISION_CODIGOS_CORRELATIVOS.md)
- [DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md](DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md)
- [DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md](DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md)
- [DECISION_DOLOR_V2.md](DECISION_DOLOR_V2.md)
- [DECISION_ELIMINACION_V2.md](DECISION_ELIMINACION_V2.md)
- [DECISION_FOTOGRAFIAS_HERIDAS_V2.md](DECISION_FOTOGRAFIAS_HERIDAS_V2.md)
- [DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md](DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md)
- [DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md](DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md)
- [DECISION_MOVILIDAD_V2.md](DECISION_MOVILIDAD_V2.md)
- [DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md](DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md)
- [DECISION_REDUCCION_EXPERTA_2026_10_20.md](DECISION_REDUCCION_EXPERTA_2026_10_20.md)
- [DECISION_REVISIONES_EXPERTAS_2026_10_17.md](DECISION_REVISIONES_EXPERTAS_2026_10_17.md)
- [DECISION_UNIDAD_TALLA_CM.md](DECISION_UNIDAD_TALLA_CM.md)

## Uso operativo y antecedentes

[Seeders locales](SEEDERS_LOCALES.md): datos sintéticos y carga explícita. Nunca usar un reset de pruebas contra datos institucionales.

Los inventarios 69/70/71/73/104/108 y los informes de migración anteriores conservan su fecha. No recalcular retrospectivamente esos informes ni restaurar sus tablas. Las FK, tipos, catálogos y reglas siguen el baseline y extensiones específicas; esta revisión solo corrige documentación.
