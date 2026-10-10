---
title: "Base de datos de RememberMind"
status: CURRENT
version: "V2.1 + extensiones aprobadas V2.2 / Dolor V2 / Eliminación V2 / Movilidad V2"
last_reviewed: 2026-10-10
owner: RememberMind
source_of_truth: true
verified_against_commit: null
verification_scope: CURRENT_CONTRACT_WITH_OWNER_APPROVED_EXTENSIONS
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# Base de datos de RememberMind

## Inventario y evolución vigente

| Versión | Estado | Alcance | Fuente |
|---|---|---|---|
| V2.0 | HISTORICAL | 69 operativas, antecedente | [diccionario histórico](REMEMBERMIND_BDD_69_TABLAS.md) |
| V2.1 | CURRENT, congelado | Núcleo de 70 operativas; no sustituido en alcance no modificado | [baseline](REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario núcleo](REMEMBERMIND_BDD_70_TABLAS.md), [decisión](DECISION_ARQUITECTURA_BDD_V2_1.md) |
| Extensión V2.2 | APPROVED, aplicable | Una tabla objetivos_signos_vitales; inventario declarado actual 71 | [decisión aprobada](DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md) |
| Eliminación V2 — 09/10/2026 | APPROVED, aplicable | Trece columnas nullable en registros_eliminacion; campos anteriores conservados, 71 tablas operativas | [decisión aprobada](DECISION_ELIMINACION_V2.md) |
| Dolor V2 — 09/10/2026 | APPROVED, aplicable | Tres columnas y self-FK en valoraciones_dolor; inventario operativo 71 sin cambios | [decisión aprobada](DECISION_DOLOR_V2.md) |
| Movilidad V2 — 10/10/2026 | APPROVED, aplicable | Nueve columnas nullable en registros_movilidad; columnas e historia anteriores conservadas, 71 tablas operativas | [decisión aprobada](DECISION_MOVILIDAD_V2.md) |

No escoger número por archivo más nuevo o nombre. Fuente de estructura: núcleo + decisiones aprobadas en su alcance, bajo instrucción actual de propietaria. Técnicas/framework/Spatie fuera del contador operativo.

71 creaciones operativas únicas están presentes estáticamente y el [test de inventario](../../tests/Feature/BddOperativaV2Test.php) exige 71. En Fase 1 no se ejecutó test/DB ni se inspeccionó esquema instalado.

## Fuentes y decisiones

- [Baseline rector](REMEMBERMIND_BDD_BASELINE_CONGELADO.md): reglas, identidad, autoría, invariantes y gobernanza.
- [Diccionario físico V2.1](REMEMBERMIND_BDD_70_TABLAS.md): columnas/PK/FK/relaciones del núcleo.
- [Normalización/autoría V2.1](DECISION_ARQUITECTURA_BDD_V2_1.md).
- [Talla canónica en centímetros](DECISION_UNIDAD_TALLA_CM.md).
- [Objetivos versionados V2.2](DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md).
- [Estado y alcance de evidencia](../ESTADO_ACTUAL.md), [conflictos/decisiones](../DECISIONES_PENDIENTES.md), [deuda por capa](../DEUDA_TECNICA.md).

Auditorías de formularios son evidencia localizada con partes propuestas; no autorizan nuevas reglas/campos. Objetivo individual “propuesto” de auditoría anterior quedó aprobado en el alcance V2.2, no en cualquier extensión futura.

## Gobernanza

Sin decisión explícita no modificar tablas/columnas/PK/FK/cardinalidades/reglas/catálogos/índices congelados. Sin JSON/EAV por conveniencia, FK convertidas en texto, nueva entidad tarea o tablas por examen.

Residentes central; aprobación preadmisión no crea residente. Formalización con cama es atómica. Autor clínico personal y actor técnico usuario separados. Historia preservada, storage clínico privado. [database/AGENTS](../../database/AGENTS.md) detalla reglas.

Conflictos localizados en estados, catálogos y atribución de garantías no se solucionan modificando normativa o producto en esta fase.

## Implementación presente

- [Carpeta de migraciones](../../database/migrations): núcleo inicial desagregado en archivos 2026_09_18_001001..001069; no las cuatro agrupadas ausentes del índice antiguo.
- [Residentes](../../database/migrations/2026_09_18_001006_create_residentes_table.php).
- [Normalización valoraciones](../../database/migrations/2026_09_24_000100_normalize_valoracion_enfermeria_preadmision.php).
- [Hardening](../../database/migrations/2026_09_24_000300_harden_v2_data_integrity.php).
- [Autoría de valoraciones](../../database/migrations/2026_09_25_000100_add_cod_personal_valorador_to_valoraciones_enfermeria_preadmision.php).
- [Talla](../../database/migrations/2026_09_26_000100_set_talla_canonical_centimeters.php).
- [Objetivos V2.2](../../database/migrations/2026_10_04_000100_create_objetivos_signos_vitales_table.php).
- [FormalizarAdmision](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php).
- [Test baseline](../../tests/Feature/BddOperativaV2Test.php), [autoría](../../tests/Feature/ValoracionEnfermeriaAutoriaTest.php), [integridad física](../../tests/Feature/IntegridadFisicaBddV2Test.php), [objetivos](../../tests/Feature/ObjetivosSignosVitalesTest.php).

Rutas descubiertas presentes; garantías de aplicación/SQL y motores se distinguen. PostgreSQL integrado; SQLite :memory: rápido. Test definido o lockForUpdate no prueban carrera PostgreSQL ejecutada.

## Procedimiento local

[Seeders locales](SEEDERS_LOCALES.md): carga explícita sintética; no ejecutar por leer este índice. Reset solo entorno confirmado desechable.

## Historia

**HISTORICAL — DO NOT USE AS CURRENT SOURCE OF TRUTH.**

[Diccionario V2.0](REMEMBERMIND_BDD_69_TABLAS.md), [inventario inicial](INVENTARIO_MIGRACION_APLICACION.md), [resultado inicial](RESULTADO_MIGRACION_APLICACION_V2.md). Se conservan rutas y contenido para trazabilidad.
