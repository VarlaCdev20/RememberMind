---
name: remembermind-database-audit
description: Validate RememberMind migrations, models, seeders, factories and persistence against the frozen 69-table operational V2 baseline while keeping future expert-system persistence separate.
---

# RememberMind database audit

1. Read `database/AGENTS.md`.
2. Read the relevant sections of `REMEMBERMIND_BDD_BASELINE_CONGELADO.md` and `REMEMBERMIND_BDD_69_TABLAS.md`.
3. Read `docs/REMEMBERMIND_MAPA_MAESTRO.md` when functional intent matters, but never use it to override frozen DB structure.
4. Compare current migrations, Models, relationships, seeders/factories and tests with the baseline.
5. Classify findings:
   - implementation mismatch that can be corrected to baseline;
   - legacy V1 artifact to migrate/remove;
   - role/permission issue handled through Spatie rather than business schema;
   - actual proposed structural change requiring owner approval;
   - future expert-system persistence that must stay outside the 69-table operational baseline.
6. Never modify frozen tables/columns/PK/FK/cardinality/index rules outside the approved baseline without explicit approval.
7. Check central invariants: resident/admission, bed occupancy, medication/prescription resident match, instrument question/application match, consent contact/resident match, history deletion, role boundaries.
8. Check longitudinal structures are preserved and not collapsed into a single overwritable current value.
9. Check `alertas` and `eventos_alerta` remain the normal operational alert/history structure and are not repurposed as expert-system storage.
10. Check PK/FK types/lengths, nullability, FK indexes, approved priority indexes, delete behavior and Eloquent configuration.
11. Verify role/permission seeders reflect the current 10-role functional model, including `GERENTE`, without adding business tables.
12. Preserve portability across configured engines; do not claim SQLite proves engine-specific concurrency.
13. Validate `migrate:fresh --seed` only on a confirmed disposable testing/development DB.
14. Run relevant tests and report exact mismatch, impacted files and whether the baseline remained unchanged.

Do not "fix" a code mismatch by altering the frozen schema unless the owner explicitly approves the structural change.
