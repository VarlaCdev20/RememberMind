---
name: remembermind-database-audit
description: Validate RememberMind migrations, models, seeders, factories and persistence against the frozen 70-table V2.1 baseline without changing structural DB rules. Use for schema audits, migration failures, relationship mismatches, V1 cleanup, or requests to verify the database.
---

# RememberMind database audit

1. Read `database/AGENTS.md`.
2. Read the relevant sections of `docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md` and `docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md`.
3. Compare current migrations, Models, relationships, seeders/factories and tests with the baseline.
4. Classify findings:
   - implementation mismatch that can be corrected to baseline;
   - legacy V1 artifact to migrate/remove;
   - actual proposed structural change requiring owner approval.
5. Never modify frozen tables/columns/PK/FK/cardinality/index rules outside the approved baseline without explicit approval.
6. Check central invariants: resident/admission, bed occupancy, medication/prescription resident match, instrument question/application match, consent contact/resident match, history deletion, role boundaries.
7. Check PK/FK types/lengths, nullability, FK indexes, approved priority indexes, delete behavior and Eloquent configuration.
8. Preserve portability across configured engines; do not claim SQLite proves engine-specific concurrency.
9. Validate `migrate:fresh --seed` only on a confirmed disposable testing/development DB.
10. Run relevant tests and report exact mismatch, impacted files and whether the baseline remained unchanged.

Do not "fix" a code mismatch by altering the frozen schema unless the owner explicitly approves the structural change.
