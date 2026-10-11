# database/AGENTS.md — RememberMind DB V2.1

Applies to `database/` and any task that changes migrations, seeders, factories or persistence structure. Inherit root rules.

## Absolute authority

Before structural DB work, consult:
- `docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md`
- `docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md`

These define the frozen Operational DB V2.1. The 69-table V2.0 dictionary is historical only.

The current code is not authority over the frozen baseline.

## Frozen schema

The frozen V2.1 baseline has **70 operational tables**. The owner approved one V2.2 extension, `objetivos_signos_vitales`, on 2026-10-04, so the current inventory is **71 operational tables**. See `docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md`. Laravel/Jetstream/Sanctum/Spatie technical tables are outside that count.

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

## User/person separation

`usuarios` is an access-account table, not personal identity.

Do not duplicate personal/professional/family identity into `usuarios`. Respect:
- account → `usuarios`;
- worker/professional → `personal`;
- family/responsible → `contactos`.

Do not invent role/permission tables; Spatie owns technical role/permission tables.

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

Do not add indexes indiscriminately. If a new structural index is outside the frozen baseline and is needed, treat it as a structural proposal.

## Portability

PostgreSQL is the integration/CI database. SQLite `:memory:` is used for configured fast tests.

Prefer Laravel Schema Builder and portable APIs. If engine-specific behavior is unavoidable, document and test the limitation.

Do not claim that SQLite proves PostgreSQL-specific constraints or locking behavior; validate those with PostgreSQL integration tests.

## Migration ordering

Respect dependencies so referenced tables exist before FK creation. Reorder/group migration files only if it preserves the exact approved schema and avoids circular dependencies.

Do not solve circular dependency by deleting FK, changing it to text, or making a required relation nullable.

## V1 migrations

Final clean installation must represent V2 directly. Business V1 migrations should not remain indefinitely as "create V1 → mutate → delete → recreate V2".

Never edit an already-applied historical migration. A structural proposal requires owner approval, an ADR, a new forward migration and synchronized baseline/test documentation.

## Seeders and factories

Use synthetic data only.

Seeders/factories must respect:
- V2 FK;
- valid state combinations;
- dependency order;
- active role/permission model;
- institutional flow.

Factories should create valid objects by default. Invalid states belong in tests that intentionally construct them.

Do not use runtime seeders/factories to bypass formal resident admission.
Never hardcode passwords, automatically create users/residents/personnel as a fallback, select the first available row to fill an FK, or accept incomplete data instead of validating it. Local sample data must be explicit and restricted to local/testing.

## Safe destructive validation

Run `php artisan migrate:fresh --seed` only when relevant and on a confirmed disposable development/testing DB.

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
- exact approved tables/columns;
- PK/FK names/types;
- nullability;
- UNIQUE;
- indexes;
- relationships;
- invariants;
- seeders/factories;
- Models;
- absence of directly related V1 structures;
- safe `migrate:fresh --seed` when applicable;
- related tests.

When validating the whole V2.1 baseline, distinguish the 70 operational tables from technical package/framework tables.
