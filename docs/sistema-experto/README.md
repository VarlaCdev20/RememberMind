---
title: "Sistema experto: fuentes, funcionamiento y límites"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [expert_contract_routing]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
---

# Consulta cognitiva y sistema experto

## Recorrido vigente

Fuentes originales autorizadas → adaptadores de ApoyoDecision → motor simbólico PHP → cinco dominios/19 componentes → perfil orientativo Petersen o abstención → explicación. La rama observacional tiene conservación y revisión posterior comprobadas. **La conservación institucional conjunta instrumental/Petersen sigue pendiente.**

La implementación usa `motor-experto/` y `app/Modulos/ApoyoDecision/`; no existe un segundo motor que deba reconstruirse. [Estado actual](../ESTADO_ACTUAL.md) y [auditoría del 25/10](../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) delimitan los defectos abiertos.

## Contratos y decisiones aplicables

| Alcance | Fuente |
|---|---|
| Institucional A–D / reglas G09 | [Contenido adoptado](PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md) |
| Petersen E–H | [Adopción institucional](PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md) |
| Requisitos C01–C05 y cinco dominios | [Contrato funcional exacto](CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md) |
| Captura Petersen y soportes | [Estructura y permisos aprobados](PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md) |
| Procedencia instrumental | [Extensión aprobada de fuentes](PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md) |
| Gobierno y sustitución de reglas | [Decisión aprobada](PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md) |
| Informe y evidencias | [Contrato del informe](CONTRATO_INFORME_COGNITIVO.md) y [fuentes y límites](EVIDENCIAS_COGNITIVAS.md) |
| Inventario y consolidaciones instaladas | [100 tablas de negocio + 16 técnicas](../base-de-datos/README.md) |
| Revisión posterior independiente | [Decisión de revisiones](../base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md) |

El nombre PROPUESTA de un documento no invalida una adopción posterior escrita en él. La adopción tiene alcance concreto; no concede una firma profesional, mediciones originales ni aprobación de DDL adicional.

## Comportamiento y límites que deben preservarse

- Cada dominio mantiene evaluabilidad independiente; A/P requieren sustento. NULL, negación, ausencia y contradicción no se convierten en normalidad.
- C1 ∧ C2 ∧ C3 de G09 pertenecen a la misma aplicación. Observación nominal no equivale a medición instrumental.
- C02 se deriva de los cinco dominios; no se captura como juicio original. C01/C03/C04/C05 siguen sus soportes y compuertas.
- Los cuatro perfiles son orientativos de posible DCL. Una abstención fundada no se sustituye por un resultado deseado.
- Medicina y Psicología revisan después y por separado. No se exige su firma antes de desarrollar o ejecutar técnicamente el contrato aprobado.
- La evaluación conjunta conserva `persistido=false` mientras no exista guardado y recuperación institucional íntegros. No vincular su revisión a un DTO transitorio.
- La cohorte de 30 casos es sintética y optativa. Sus nombres naturales no la convierten en historias clínicas originales ni entrenamiento estadístico automático.

## Conservación: evidencia y brecha

[La referencia SQL completa](RECUPERACION_SQL_COMPLETA_REFERENCIA_2026_10_22.md) demuestra igualdad del corpus con sus límites, pero no es instalable: 229 tablas hipotéticas, problemas de anchura y FK históricas incompletas. No sustituye el diseño institucional. [Las cuatro consolidaciones ya instaladas](CIERRE_REFACTOR_BLOQUES_2026_10_22.md) sí redujeron 104 → 100 en su alcance; no cierran el DTO conjunto.

## Investigación y entregas anteriores

[Catálogo completo de contratos, decisiones, investigación y cortes](INDICE_DOCUMENTAL.md). Las fuentes bibliográficas no crean mediciones; las pruebas de prototipos no certifican el producto institucional. Los 104 escenarios citados históricamente no tienen manifiesto local identificado; no se atribuye su ejecución.

`IMPLEMENTACION_V1.md` no existe en este árbol. No se recrea su contenido ni se usa como requisito de lectura; las entradas anteriores que lo mencionaban se concilian con las fuentes presentes.
