---
title: "Base de datos: inventario y decisiones"
status: CURRENT
version: "2.1"
last_reviewed: 2026-11-04
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [schema_routing]
verified_against_commit: 57285c7e3988cf5df86cdbd24f03e6a9650807bf
verification_scope: CURRENT_TREE_DOCUMENTATION_AND_REGISTERED_ROUTES
runtime_verified: false
---

# Base de datos de RememberMind

## Estado vigente

**Corte vigente del 17/11/2026 — 20 expertas.** La [unión instalada de evaluaciones e instantáneas de reglas](DECISION_20_EXPERTAS_REGLAS_2026_11_17.md) conserva 206 registros y deja **60 operativas, 20 expertas y 16 técnicas**, para un total de **96 tablas físicas**.

**Corte anterior del 17/11/2026 — 60 operativas:** la [unión autorizada e instalada de controles cognitivos e IADL](DECISION_60_OPERATIVAS_CONTROLES_2026_11_17.md) conserva los 327 registros afectados y deja **60 operativas + 22 expertas = 82 de negocio**, más **16 técnicas: 98 físicas**. Los cortes siguientes conservan su fecha histórica.

**Corte vigente del 17/11/2026 — pases de turno y textos clínicos:** la [unión autorizada e instalada](DECISION_CONSOLIDACION_PASES_TEXTOS_2026_11_17.md) conserva los 136 registros originales y deja **62 operativas + 22 expertas = 84 de negocio**, más **16 técnicas: 100 tablas físicas**. Las pruebas específicas pasan en SQLite y PostgreSQL; el informe separa cuatro fallos de la ejecución ampliada. Los cortes siguientes son históricos.

**Corte posterior del 16/11/2026 — historial laboral:** la [unión autorizada e instalada](DECISION_CONSOLIDACION_HISTORIA_LABORAL_2026_11_16.md) conserva 208 actos y deja **63 operativas + 22 expertas = 85 tablas de negocio**, más **16 técnicas: 101 físicas**. Los inventarios siguientes corresponden a sus cortes anteriores.

**Consolidación instalada el 16/11/2026:** [documentos, valoraciones y textos clínicos](DECISION_CONSOLIDACION_DOCUMENTOS_VALORACIONES_TEXTOS_2026_11_16.md). Inventario local comprobado: **64 operativas + 22 expertas = 86 de negocio**, más **16 técnicas: 102 físicas**. Las cifras siguientes conservan sus fechas históricas.

**Consolidaciones instaladas el 14/11/2026:** [valoración inicial dentro de preadmisiones](DECISION_CONSOLIDACION_VALORACION_PREADMISION_2026_11_14.md), [ingesta e hidratación en registros de consumo](DECISION_CONSOLIDACION_CONSUMO_2026_11_14.md), [cuidados y criterios](DECISION_CONSOLIDACION_CUIDADOS_Y_CRITERIOS_2026_11_14.md) y [sueño, mediciones y valoraciones profesionales](DECISION_CONSOLIDACION_CLINICA_2026_11_14.md) y [estudios clínicos y vínculos de evidencia](DECISION_CONSOLIDACION_ESTUDIOS_EVIDENCIA_2026_11_14.md). La [consolidación de registros clínicos, asignaciones y componentes de reglas](DECISION_CONSOLIDACION_CLINICA_ASIGNACIONES_REGLAS_2026_11_14.md) deja **68 operativas + 22 expertas = 90 de negocio**, más **16 técnicas**.

**Extensión autorizada e instalada del 29/10/2026:** [idioma principal e informante principal por atención](PROPUESTA_CONTEXTO_IDIOMA_INFORMANTE_2026_10_29.md), tres columnas nullable, FK restrictiva al vínculo existente e índice de soporte. Conserva 4FN en este alcance,28.107 atenciones previas e inventario 106 de negocio + 16 técnicas; sin backfill ni uso inferencial automático.

**Contexto escalar por atención —28/10/2026:** [tres columnas opcionales y conservación de 4FN](DECISION_CONTEXTO_ESCOLARIDAD_4FN_2026_10_28.md), sin nuevas tablas/FK/permisos ni listas de informantes/idiomas. La instrucción posterior autoriza esta extensión acotada; el inventario permanece106 de negocio+16 técnicas.

**Aviso compartido instalado —27/10/2026:** [decisión y diccionario](DECISION_AVISO_COMPARTIDO_CAMPOS_2026_10_27.md), seis columnas en eventos existentes,106 de negocio+16 técnicas, sin nuevas tablas/permisos. [Integración y verificación44](../especificacion-funcional/vistas/gerencia/44-INTEGRACION-AVISO-COMPARTIDO.md) resuelven la pregunta posterior al ensayo43.

**Decisión posterior vigente —27/10/2026:** [no crear tablas adicionales](DECISION_SIN_NUEVAS_TABLAS_2026_10_27.md). La propietaria permite ampliar campos manteniendo grano e integridad; no autoriza JSON/EAV o listas de destinatarios en texto. El techo histórico110 no permite añadir tablas por iniciativa técnica. El inventario siguiente permanece106.

La [extensión de Gerencia35/37 autorizada e instalada el 27/10/2026](DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md) deja **81 operativas +25 expertas =106 tablas de negocio**, además de **16 técnicas**: 122 físicas. Añade seis relaciones y ocho columnas nullable, con contenido previo conservado. La [consolidación del 22/10](DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md) y la auditoría del 25/10 describen el inventario anterior de100 de negocio y116 físicas.

El [máximo de diseño de 110](../sistema-experto/DECISION_MAXIMO_110_TABLAS_2026_10_21.md) sigue siendo capacidad de diseño, no aprobación de DDL. Su presupuesto inicial de seis relaciones correspondía al inventario entonces instalado de 104; no es un presupuesto fijo perpetuo ni autoriza diez tablas ahora.

## Fuentes rectoras

- [Decisión de documentación e historia de Gerencia27/10](DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md), con diccionarios35/37 y evidencia local.
- [Gobierno BDD](../../database/AGENTS.md).
- [Baseline rector](REMEMBERMIND_BDD_BASELINE_CONGELADO.md): núcleo y extensiones aplicables.
- [Diccionario del núcleo V2.1](REMEMBERMIND_BDD_70_TABLAS.md): el nombre histórico no representa el total instalado.
- [Reducción anterior del 20/10](DECISION_REDUCCION_EXPERTA_2026_10_20.md) → [consolidación posterior del 22/10](DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md). Las dos decisiones conservan sus alcances y transiciones.

La conservación conjunta instrumental/Petersen no está instalada. La [propuesta del 31/10](PROPUESTA_CONSERVACION_RESULTADO_INSTITUCIONAL_2026_10_31.md) fue rechazada para mantener relaciones y permisos actuales. No es un DDL pendiente de ejecución autorizada. Los tres catálogos instrumentales activados el 04/11 reutilizan instrumentos/preguntas existentes y no añaden tablas.

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
