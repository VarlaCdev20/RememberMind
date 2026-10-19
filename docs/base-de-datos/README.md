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

**Fuentes originales aprobadas — 19/10/2026:** cinco columnas nullable en `aplicaciones_instrumento` para autoría técnica, documento/localizador y rectificación enlazada. FK restrictivas, checks y unicidades; **cero tablas nuevas, 106 de negocio**. [Decisión, diccionario y 4FN](../sistema-experto/PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md).

**Gobierno de reglas aprobado — 18/10/2026:** cinco columnas nullable en `reglas_expertas`, self-FK/FK restrictivas, índices y controles de firma e historia. Una antecesora y una decisión por revisión mantienen la 4FN; **cero tablas nuevas, 106 de negocio**. [Diccionario aprobado y límites](../sistema-experto/PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md).

**Origen observacional aprobado — 18/10/2026:** dos FK nullable e indexadas en `hechos_instantanea_experta`, hacia el control cognitivo y su autor original, con checks y borrado restrictivo. **Sin tablas nuevas: 33 expertas + 73 operativas = 106 tablas de negocio**. [Diccionario y autorización](../sistema-experto/PROPUESTA_CONSERVACION_OBSERVACIONAL_V1.md).

**Revisiones profesionales aprobadas — 17/10/2026:** una tabla de dictámenes,
con cadenas separadas para Psicología y Medicina. Inventario actual:
**33 expertas + 73 operativas = 106 tablas de negocio**.
[Diccionario, aprobación y límites](DECISION_REVISIONES_EXPERTAS_2026_10_17.md).

**Conservación experta V2 aprobada — 17/10/2026:** nueve tablas suplementarias y ampliaciones de D-137: **32 expertas + 73 operativas = 105 tablas de negocio**. [decisión aprobada](DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md). No activa uso asistencial; las tablas técnicas quedan fuera del conteo.

**Extensión aprobada IADL — 17/10/2026:** `controles_funcionales_iadl`; inventario vigente **73 operativas**, sin alterar las anteriores ni las 23 expertas. [Diccionario y límites aprobados](DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md). No autoriza pantallas, permisos ni clasificación clínica.

**Extensión aprobada — 17/10/2026:** una tabla `fotografias_institucionales`; inventario vigente **72 operativas**, conservando las existentes. Véase [decisión Galería y 4FN](DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md). Los conteos anteriores conservan el alcance de sus versiones.


## Inventario y evolución vigente

Heridas / curaciones V2 — 11/10/2026: [extensión aprobada](DECISION_FOTOGRAFIAS_HERIDAS_V2.md). Añade únicamente dos FK opcionales e indexadas a `documentos_clinicos`; ninguna tabla nueva. [Resultado y aceptación pendiente](../frontend/FORMULARIO_HERIDAS_V2_RESULTADO.md).

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

**Códigos correlativos — 13/10/2026:** valores `COD_<prefijo>_<correlativo>`
 y renumeración de existentes autorizados, conservando columnas y PK/FK.
 La tabla técnica `secuencias_codigos` está aprobada y queda fuera de las
 71 tablas operativas y las 23 del experto. Véase
 [decisión de códigos](DECISION_CODIGOS_CORRELATIVOS.md).
