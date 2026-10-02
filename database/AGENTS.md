# database/AGENTS.md — RememberMind DB V2

Applies to `database/` and any task that changes migrations, seeders, factories or persistence structure. Inherit root rules.

## Absolute authority

Before structural DB work, consult:
- `REMEMBERMIND_BDD_BASELINE_CONGELADO.md`
- `REMEMBERMIND_BDD_69_TABLAS.md`

These define the frozen Operational DB V2.

The current code is not authority over the frozen baseline.

## Operational DB vs expert-system persistence

The frozen DB contains exactly **69 operational tables**.

These tables support the normal institutional, clinical, care, medication, longitudinal-follow-up and alert system.

Future expert-system persistence is explicitly outside this 69-table operational baseline. Do not add semantic-network, decision-tree, inference, prediction, knowledge-base or expert-rule tables to the operational baseline without a separate approved design/change process.

Likewise, ordinary alert tables (`alertas`, `eventos_alerta`) belong to the normal operational system and do not by themselves imply expert-system persistence.

## Frozen schema

There are exactly **69 operational tables**. Laravel/Jetstream/Sanctum/Spatie technical tables are outside that count.

Without explicit owner approval, do not:
- add/delete/merge/split/rename a table;
- add/delete/rename/move a column;
- change PK/FK/cardinality;
- change structural rules;
- add a new catalog;
- replace FK with text;
- replace `cod_*` with `id`;
- use JSON/EAV to avoid the normalized model;
- remove a relationship to simplify implementation.

If the frozen model appears insufficient: document problem, impact and alternatives; request approval; do not silently modify.

## Central entity and naming

Central entity: `residentes`.
Primary identifier: `cod_residente`.

Operational table names are Spanish plural snake_case as defined by baseline.

Operational PKs use `cod_<entidad>`. The frozen baseline uses string codes (generally `string(20)` for `cod_*` keys unless the approved schema says otherwise).

FK must preserve the referenced PK name and type/length.

Do not introduce V1 names such as:
`AdultoMayor`, `adultos_mayores`, `adulto_mayor`, `cod_am`, `cod_usu`.

## User/person/role separation

`usuarios` is an access-account table, not personal identity.

Do not duplicate personal/professional/family identity into `usuarios`. Respect:
- account → `usuarios`;
- worker/professional → `personal`;
- family/responsible → `contactos`.

Do not invent role/permission tables; Spatie owns technical role/permission tables.

The current functional role decision includes `GERENTE` in addition to the previously documented roles. This is a Spatie/permission concern and does **not** authorize modification of the 69 operational business tables.

If personnel/HR requirements for Gerente need structured data absent from the frozen schema (contracts, salary, recruitment files, etc.), report the gap and request structural approval instead of adding columns/tables by convenience.

## Nullability and types

A FK may be nullable only where the approved schema allows it.

Do not make required FK nullable merely to get inserts working.

Preserve logical types and decimal precision. Do not replace clinical/monetary `decimal(p,s)` with float.

Do not automatically add `timestamps()` or `softDeletes()` to frozen tables. If the table has no `created_at`, `updated_at` or `deleted_at`, configure Eloquent accordingly instead of changing schema.

## Normalization

Keep the approved normalized structure.

Do not create:
- universal `campo/valor` clinical tables;
- generic data buckets;
- dynamic EAV;
- JSON arrays replacing contacts, diagnoses, medications, instruments or other approved entities.

Do not merge prescription and medication administration.

Do not reduce longitudinal history to one overwritable "current value".

## Longitudinal data

The frozen schema intentionally preserves temporal evolution through entities such as:
- `signos_vitales`;
- `valoraciones_dolor`;
- `mediciones_antropometricas`;
- `controles_cognitivos`;
- `registros_conductuales`;
- `registros_sueno`;
- `registros_ingesta`;
- `registros_hidratacion`;
- `registros_eliminacion`;
- `registros_movilidad`;
- `heridas` / `curaciones_herida`;
- `aplicaciones_instrumento`;
- professional evaluations.

Charts, before/after comparison and trends should be computed/read from this history. Do not add duplicate "current" columns/tables or aggregate snapshots unless separately approved.

## Clinical validation and thresholds

Do not add DB columns or catalogs just to store ad-hoc clinical thresholds without approval.

Clinical rules/ranges may be implemented at application/configuration level only when compatible with the frozen model and formally approved. If the desired personalized range requires new persistent structured fields absent from V2, document the need and request schema approval.

Do not encode developer-invented clinical thresholds into CHECK constraints or migrations.

## FK and delete policy

PK/FK types and lengths must match.

Index every FK.

For clinical/historical/sensitive transactions, do not indiscriminately use `cascadeOnDelete()`. Prefer the approved restrictive behavior. Annulling/deactivating belongs to domain state when defined.

Never use cascade to erase history because it makes cleanup easier.

## Critical invariants

Persistence and application layers together must protect:

1. `usuarios.correo` unique.
2. No two active occupancies for the same bed.
3. No two active occupancies for the same resident.
4. Active resident-contact pair is not duplicated invalidly.
5. Consent contact belongs to the same resident.
6. One answer per question/application unless the instrument explicitly permits multiple.
7. No duplicate resident in one activity when prohibited.
8. No duplicate study component result without explicit rule.
9. Medication administration references a prescription for the same resident.
10. Instrument answer references a question from the applied instrument.
11. No ordinary physical deletion of clinical history.

Lifecycle rules are enforced by Actions/Services/Policies too; DB constraints alone are insufficient.

## Bed concurrency

UI filtering is not enough.

Bed assignment must handle the race where two users see a bed as free at the same time. Use an appropriate transaction/locking strategy compatible with the supported engines, without modifying frozen schema unless approved.

## Formal admission

A preadmission approval does not create a resident.

Formal admission is the resident-creation boundary and must be atomic. Failed admission must not leave orphan/partial resident, admission, occupancy, contact link, consent or state history.

## Medication structure

Preserve:
`medicamentos → prescripciones → horarios_prescripcion → administraciones_medicacion`

Do not merge these concepts.

## Care structure

Preserve:
`planes_cuidado → intervenciones_cuidado → programaciones_cuidado / ejecuciones_cuidado`

Do not invent `tareas_cuidado`.

## Alerts structure

Preserve:
`residentes 1:N alertas`
and
`alertas 1:N eventos_alerta`.

`alertas` represents the active/business alert; `eventos_alerta` preserves lifecycle history.

Do not create duplicate alert-history tables or overwrite events to store only latest state.

Do not add expert-system inference fields/tables into these entities by convenience.

## Instruments

Preserve:
`instrumentos → preguntas_instrumento → opciones_pregunta`
and
`aplicaciones_instrumento → respuestas_instrumento`

Do not store instrument definitions/responses as generic JSON to simplify development.

## Clinical studies

Preserve the generic approved structure:
`tipos_estudio_clinico → componentes_estudio`
and
`estudios_clinicos → resultados_estudio / informes_estudio / documentos_clinicos`

Do not create one new table per laboratory/imaging study without approved schema change.

## Clinical files

Large files live in private storage; DB stores the approved identifier/path/metadata. Do not move PDFs/images/DICOM into BLOB by convenience.

Generate technical hashes; do not ask users to type them.

Use `cod_documento_anterior` when the approved schema models document succession. Do not add an arbitrary `version` column.

## Priority indexes

Besides PK/UNIQUE/FK, preserve the baseline priority indexes, including the approved resident/date, preadmission/state, occupancy/state, prescription/state, medication-administration/date, instrument/date, alert/state/date and study/date indexes.

These indexes support longitudinal and operational queries, including charts and alert dashboards.

Do not add indexes indiscriminately. If a new structural index is outside the frozen baseline and is needed, treat it as a structural proposal.

## Portability

The project must remain compatible with configured SQLite/MySQL/MariaDB/PostgreSQL usage.

Prefer Laravel Schema Builder and portable APIs. If engine-specific behavior is unavoidable, document and test the limitation.

Tests currently use SQLite `:memory:`; do not claim that SQLite proves identical locking behavior across other engines.

## Migration ordering

Respect dependencies so referenced tables exist before FK creation. Reorder/group migration files only if it preserves the exact approved schema and avoids circular dependencies.

Do not solve circular dependency by deleting FK, changing it to text, or making a required relation nullable.

## V1 migrations

Final clean installation must represent V2 directly. Business V1 migrations should not remain indefinitely as "create V1 → mutate → delete → recreate V2".

Before rewriting already-applied migrations, determine whether the environment is still disposable/development or whether shared/deployed data exists. Never assume rewriting history is safe.

## Seeders and factories

Use synthetic data only.

Seeders/factories must respect:
- V2 FK;
- valid state combinations;
- dependency order;
- current active role/permission model;
- institutional flow.

Role/permission seeders should represent the owner-approved 10-role functional model, including `GERENTE` and excluding `VOLUNTARIO`, without modifying business schema.

Factories should create valid objects by default. Invalid states belong in tests that intentionally construct them.

Do not use runtime seeders/factories to bypass formal resident admission.

## Safe destructive validation

`php artisan migrate:fresh --seed` is a required V2 verification only on a confirmed disposable development/testing DB.

Never run it on production, meaningful staging, shared/unknown data or any environment whose data must be preserved.

## Eloquent synchronization

After migration work, verify Models match:
- `$table`;
- `$primaryKey`;
- `$keyType`;
- `$incrementing`;
- `$timestamps`;
- casts;
- relationships.

Fix Models to match the approved DB, not the DB to match incorrect Models.

## DB Definition of Done

For relevant DB work verify:
- exact approved 69 operational tables/columns;
- expert-system persistence has not leaked into operational schema;
- PK/FK names/types;
- nullability;
- UNIQUE;
- indexes;
- relationships;
- longitudinal-history structures preserved;
- alert/event structures preserved;
- invariants;
- seeders/factories;
- current role seed model includes Gerente where appropriate;
- Models;
- absence of directly related V1 structures;
- safe `migrate:fresh --seed` when applicable;
- related tests.

When validating the whole V2 baseline, distinguish the 69 operational tables from technical package/framework tables and future separately approved expert-system structures.
