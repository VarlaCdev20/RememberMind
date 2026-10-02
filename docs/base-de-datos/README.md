# Base de datos de RememberMind

Esta carpeta contiene la documentación canónica y oficial de la **BDD Operativa V2.1** (70 tablas operativas).

## Índice documental

- [Datos ficticios para desarrollo local](SEEDERS_LOCALES.md): carga explícita e idempotente de las 70 tablas operativas, restringida a local/testing.

### 1. Fuente de verdad vigente (V2.1 — CONGELADO)

1. [Baseline congelado de Base de Datos Operativa](REMEMBERMIND_BDD_BASELINE_CONGELADO.md)
   Fija el alcance formal, principios de arquitectura, clasificación canónica (11 maestras, 46 transaccionales, 7 intermedias, 6 auxiliares = 70 operativas), reglas de autorización y gobernanza estricta.
2. [Diccionario de las 70 tablas (V2.1)](REMEMBERMIND_BDD_70_TABLAS.md)
   Define exhaustivamente las 70 tablas operativas, atributos, tipos, claves primarias, foráneas, restricciones de integridad (`CHECK`), índices y relaciones vigentes.

### 2. Decisiones de arquitectura

- [Decisión de Arquitectura — BDD Operativa V2.1](DECISION_ARQUITECTURA_BDD_V2_1.md)
  Documenta la incorporación formal de `valoraciones_enfermeria_preadmision` (tabla 70), el reemplazo definitivo de la columna temporal JSON y el endurecimiento del modelo de autoría clínica y técnica dual.

### 3. Documentación histórica (V2.0)

- [Diccionario histórico de 69 tablas (V2.0 Histórico)](REMEMBERMIND_BDD_69_TABLAS.md)
  Conservado exclusivamente como referencia y trazabilidad histórica previa a la incorporación de la tabla 70.
- [Resultado de la migración a BDD V2](RESULTADO_MIGRACION_APLICACION_V2.md)
  Registro histórico de la migración inicial de la capa de aplicación hacia el baseline V2.0.
- [Inventario inicial y clasificación](INVENTARIO_MIGRACION_APLICACION.md)
  Registro del inventario preliminar de componentes.

Ante cualquier discrepancia con documentos históricos o de auditorías pasadas, **prevalecen de manera absoluta el Baseline Congelado y el Diccionario de las 70 tablas vigentes**.

---

## Reglas de gobernanza y mantenimiento

- **BDD Congelada:** No se puede agregar, eliminar, fusionar, dividir, renombrar ni modificar tablas, atributos, PK, FK, cardinalidades ni reglas estructurales sin el proceso de aprobación y versionado correspondiente.
- **Entidad central:** `residentes` es la entidad maestra principal del sistema. Solo se crea mediante la formalización de la admisión en una transacción atómica.
- **Autoría clínica separada:** Se distingue estrictamente entre autor clínico (`personal`) y actor técnico (`usuarios`).
- **Tablas técnicas excluidas:** Las tablas de Laravel, Jetstream, Fortify, Sanctum y Spatie Permission/Activitylog no forman parte del conteo de las 70 tablas operativas.
- **Sistema experto:** Fuera del alcance de este baseline operativo.

---

## Implementación Laravel

La estructura operativa está distribuida en las siguientes migraciones versionadas:

- [`2026_09_18_000100_create_bdd_v2_core_tables.php`](../../database/migrations/2026_09_18_000100_create_bdd_v2_core_tables.php)
- [`2026_09_18_000200_create_bdd_v2_clinical_tables.php`](../../database/migrations/2026_09_18_000200_create_bdd_v2_clinical_tables.php)
- [`2026_09_18_000300_create_bdd_v2_care_medication_instrument_tables.php`](../../database/migrations/2026_09_18_000300_create_bdd_v2_care_medication_instrument_tables.php)
- [`2026_09_18_000400_create_bdd_v2_professional_social_alert_tables.php`](../../database/migrations/2026_09_18_000400_create_bdd_v2_professional_social_alert_tables.php)
- [`2026_09_24_000100_normalize_valoracion_enfermeria_preadmision.php`](../../database/migrations/2026_09_24_000100_normalize_valoracion_enfermeria_preadmision.php)
- [`2026_09_24_000300_harden_v2_data_integrity.php`](../../database/migrations/2026_09_24_000300_harden_v2_data_integrity.php)
- [`2026_09_25_000100_add_cod_personal_valorador_to_valoraciones_enfermeria_preadmision.php`](../../database/migrations/2026_09_25_000100_add_cod_personal_valorador_to_valoraciones_enfermeria_preadmision.php)

El flujo transaccional de admisión se implementa en [`FormalizarAdmision.php`](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php).
La verificación automatizada del baseline se encuentra en [`BddOperativaV2Test.php`](../../tests/Feature/BddOperativaV2Test.php) y [`ValoracionEnfermeriaAutoriaTest.php`](../../tests/Feature/ValoracionEnfermeriaAutoriaTest.php).
