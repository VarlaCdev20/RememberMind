# tests/AGENTS.md — RememberMind testing

Applies to `tests/` and test strategy. Inherit root rules.

## Test purpose

Tests must prove:
- correct operations are allowed;
- incorrect operations are rejected;
- authorization boundaries hold;
- institutional/clinical invariants hold;
- longitudinal history is preserved;
- alert lifecycle is correct;
- persistence is consistent;
- regressions are detectable.

A green suite is not an excuse to preserve an obsolete rule.

## Source authority

If a test conflicts with a higher-authority current source, update the test rather than distorting correct V2 behavior.

For current functional behavior of the normal system, use `docs/REMEMBERMIND_MAPA_MAESTRO.md` together with root/scoped AGENTS. For frozen DB structure, the baseline files remain authoritative.

Never weaken Policy, validation, FK, state rules or business invariants merely to make a test pass.

## Normal system vs expert system

Ordinary tests must not assume the presence of semantic-network inference, decision-tree prediction, multicriteria reasoning or autonomous treatment recommendations.

The normal system may test deterministic operational/clinical alerts from explicit approved rules.

Expert-system tests will belong to that future module/document and must be separated from ordinary operational behavior.

## Official invariants to keep covered

Maintain tests for at least:
1. resident cannot be created without formal admission;
2. occupied bed cannot be assigned again;
3. resident cannot have two active occupancies;
4. medication administration cannot use another resident's prescription;
5. instrument answer cannot use a question from another instrument;
6. consent cannot use another resident's contact;
7. duplicate participant in an activity is rejected when prohibited;
8. duplicate study-component result is rejected when prohibited;
9. ordinary physical deletion of clinical history is rejected;
10. Superadmin can read all required operational data;
11. Gerente does not gain clinical write merely by role;
12. Administrador does not gain clinical write merely by role;
13. Nursing does not prescribe;
14. Family only sees authorized information for linked residents;
15. VOLUNTARIO is absent from current scope.

## Active role model

Current functional role set has 10 roles:
- SUPERADMINISTRADOR;
- GERENTE;
- ADMINISTRADOR;
- ENFERMEROS;
- MEDICO GENERAL/GERIATRA;
- PSICOLOGO/A;
- PEDAGOGO;
- NUTRICIONISTA;
- FISIOTERAPEUTA;
- FAMILIAR.

Role/permission seed tests should reflect this current decision without adding business schema tables.

Gerente and Administrador require distinct authorization tests where their responsibilities differ.

## Positive and negative testing

For sensitive work, cover the happy path plus meaningful negatives.

Example:
- Médico can create a prescription when authorized.
- Enfermería cannot prescribe.
- Administrador cannot prescribe merely by role.
- Gerente cannot prescribe merely by role.
- Family cannot prescribe/access unrelated resident.
- Missing permission/state/competence is denied when relevant.

Do not test only "can do"; also test "cannot do".

## Assert effects, not only response code

For mutations, verify:
- expected DB records/state/relationships;
- records that must not exist;
- no partial side effects after failure.

A `403` alone is insufficient if the implementation could already have persisted invalid data.

## Institutional flow

Keep end-to-end coverage for:
`Preadmisión → revisión → APROBADA/RECHAZADA → admisión formal → residente ADMITIDO`

Explicitly verify:
- approval alone does not create resident;
- direct `Residente::create()` path is blocked;
- admission creates the approved related state;
- failure during admission rolls back partial state when testable.

## Beds and concurrency

Test free-bed success and occupied-bed failure.

Test second active occupancy for the same resident is rejected.

Where concurrency behavior matters, add appropriate integration coverage, but do not claim SQLite `:memory:` proves identical DB locking behavior across MySQL/PostgreSQL.

## Medication

Keep prescribing and administration separate.

Cover:
- authorized medical prescription;
- Nursing denial for prescription creation/change;
- Gerente/Administrador denial for prescription creation merely by role;
- authorized administration;
- same-resident prescription invariant;
- relevant invalid/inactive state;
- duplicate/idempotency risk when applicable.

Ordinary operational tests must not expect autonomous medication recommendations.

## Longitudinal clinical data

For relevant health/care modules, verify that new records append history rather than overwrite prior observations.

Cover chronology and resident association for appropriate modules such as:
- vital signs;
- pain;
- anthropometrics;
- cognition;
- behavior;
- sleep;
- intake/hydration/elimination;
- mobility;
- wounds/curations;
- professional evaluations.

When an API/component feeds a chart, test server-side behavior such as:
- correct resident scope;
- authorized access;
- chronological ordering;
- bounded date range/pagination as designed;
- no leakage of another resident's data.

Do not use PHPUnit to pretend it visually verified the chart itself.

## Clinical validation

Where approved rules exist, test separately:
- format/type validation;
- impossible/implausible input rejection or confirmation behavior;
- clinically unusual but plausible measurements being preserved correctly;
- deterministic warning/alert generation;
- absence of invented thresholds.

Do not encode a clinical threshold into a test unless the project has an approved rule/source for it.

If a personalized objective/range exists in the implemented model, cover authorized configuration and its effect without assuming age alone defines normality.

## Alerts

`alertas` and `eventos_alerta` require lifecycle tests.

Cover as applicable:
- deterministic creation from an approved normal-system trigger;
- no duplicate unresolved alert on retry/refresh;
- correct resident/context;
- allowed transitions;
- recognition;
- assignment;
- attention;
- closure/annulment;
- event history preservation;
- unauthorized actor denial.

Do not overwrite prior event records to make the latest state easier to assert.

## External alert notifications

When email/WhatsApp integration exists, use fakes/mocks at the provider boundary.

Test:
- internal alert persists even if channel delivery fails;
- retries do not create duplicate business alerts;
- successful/failed delivery metadata behaves as designed;
- sensitive payload is minimized;
- authorization remains inside RememberMind for detailed access.

Do not call real external providers in the test suite.

## Professional authorship

Test that clinical `cod_personal` is derived from authenticated linked personnel when required and that a manipulated payload cannot attribute a record to a different professional.

## IDOR and family scope

For resources by ID/code, test cross-resident access.

Example:
- Family linked to Resident A can access allowed A view.
- Same family attempting Resident B is denied.
- Denial causes no data exposure/mutation.

Family tests must use the real `residentes_contactos` relationship, not only assign the role.

## Account, permission and Policy layers

When relevant test:
- guest;
- active authorized user;
- inactive account;
- correct role but missing permission;
- contextual Policy denial;
- out-of-scope resident/resource;
- professional competence.

Do not reduce authorization tests to role checks alone.

## States and idempotency

Meaningful transitions need:
- valid source → valid target;
- relevant invalid source → rejected target.

Repeated request/double submit should not create duplicates where domain uniqueness matters.

## Clinical history and correction

Keep history immutable in tests:
- corrections do not erase original clinical record;
- physical ordinary delete is rejected;
- longitudinal measurement history remains available after newer records;
- alert progression preserves `eventos_alerta` history rather than overwriting it.

## Instruments/studies/consents/activities

Use synthetic definitions/data.

Cover cross-entity coherence:
- applied instrument ↔ question;
- study type ↔ component;
- resident ↔ contact consent;
- activity ↔ participant uniqueness.

Do not include copyrighted clinical instrument content merely to test infrastructure.

## DB baseline

Maintain tests that validate:
- exactly 69 operational tables;
- technical package/framework tables excluded from the count;
- future expert-system tables remain outside the operational count;
- key V2 names/columns exist;
- important V1 tables/columns are absent;
- critical FK/UNIQUE behavior works.

Do not turn tests into an unnecessarily duplicated full schema spec when the baseline files are the authority.

## Safe DB test environment

Current `phpunit.xml` uses:
- `APP_ENV=testing`;
- SQLite `:memory:`;
- array cache/session/mail;
- sync queue.

Do not run destructive tests against unknown/real databases.

Use `RefreshDatabase` where appropriate.

Run `php artisan migrate:fresh --seed` only in a confirmed disposable environment and when migration/seeder verification is relevant.

## Factories/seeders

Use synthetic data only.

Factories should generate valid domain state by default. Intentionally invalid state should be explicit inside the test.

Use seeders when the scenario truly depends on common roles/permissions/catalogs; do not make every small test depend on a huge opaque seed unnecessarily.

Role seeders should include the owner-approved 10-role model and exclude `VOLUNTARIO` unless the owner changes scope.

## Test structure

Prefer one clear behavior per test and readable Arrange → Act → Assert flow.

Names should describe behavior, e.g.:

```php
test_enfermeria_no_puede_crear_prescripcion()
```

A broad smoke/baseline test is acceptable when its purpose is explicitly broad.

## Progressive organization

The current V2 repo contains a large `tests/Feature/BddOperativaV2Test.php`. Preserve valid coverage; do not rewrite it only for aesthetics.

As modules grow, move/add tests by responsibility, for example:

```text
tests/Feature/
  Admisiones/
  Preadmisiones/
  Residentes/
  Medicacion/
  Alertas/
  Seguimiento/
  Autorizacion/
  Database/
  Livewire/
```

Do not create dozens of tiny one-test files without benefit.

## PHPUnit/Laravel conventions

Keep the current PHPUnit/Laravel style unless a real need justifies another tool.

Use Feature tests for routes/auth/integration/DB/Livewire/multi-entity flows.

Use Unit tests for genuine isolated logic.

Test Actions/Services/Policies directly when that yields clearer domain coverage.

Do not mock the entire domain in Feature tests until nothing real remains.

## Fakes

Use Laravel fakes at external/secondary boundaries:
- `Storage::fake()`;
- `UploadedFile::fake()`;
- `Notification::fake()`;
- `Queue::fake()`;
- HTTP fake/mock for external services.

Do not fake the core Action/Policy/DB when the behavior being tested depends on them.

## Time and determinism

Freeze time when needed.

Tests must not depend on:
- execution order;
- real internet;
- uncontrolled sleep;
- machine timezone accidents;
- non-reproducible random values for the main assertion.

No `sleep()` as a test synchronization hack.

## Regression tests

A significant bug/security fix should gain a regression test when feasible.

Especially preserve regressions for:
- IDOR;
- permission bypass;
- mass assignment;
- professional authorship;
- resident lifecycle;
- bed occupancy;
- medication;
- history deletion;
- alert duplication/lifecycle;
- Gerente/Administrador permission separation.

## Livewire/UI contracts

Use Livewire tests for server-side component behavior:
- render;
- validation;
- actions;
- authorization;
- filters/pagination;
- longitudinal query behavior;
- emitted notification state where part of the contract.

Do not use PHPUnit to pretend it verified CSS, contrast, glassmorphism, actual chart readability or responsive behavior.

Frontend work also needs build/visual/UX review as described in `resources/AGENTS.md`.

## Suite selection

Small localized change:
- specific test;
- directly related group.

Module change:
- module suite;
- nearby integrations.

Cross-cutting/high-risk change:
- broad/full suite.

Run full suite particularly when changing:
- auth/permissions/Policies shared broadly;
- role/permission seeders;
- base Model behavior;
- admission;
- DB/migrations;
- alert infrastructure;
- shared middleware;
- broad V1 cleanup.

Commands:

```bash
composer test
php artisan test
php artisan test --filter=TestName
```

After fixing a failing test, rerun that test and then the related group.

## Failure handling

Do not ignore failing tests.

Classify:
- introduced by current change;
- pre-existing;
- obsolete test;
- environment problem.

Fix current-change failures. Correct obsolete tests when higher-authority V2/current functional sources prove them outdated. Report unrelated pre-existing/environment failures precisely.

Do not `skip`/`markTestSkipped` merely to obtain green.

## Coverage philosophy

Do not chase a percentage as the primary target. Protect risk, business rules and invariants.

The V2 refactor is not complete until critical institutional and clinical tests pass and tests/factories/seeders no longer rely on legacy schema.

## Test Definition of Done

A testing task is complete when:
- new/updated test was actually run;
- expectation matches current authoritative behavior;
- positive path exists where relevant;
- meaningful negatives exist;
- longitudinal/history behavior is tested where relevant;
- alert lifecycle/deduplication is tested where relevant;
- no real/sensitive data is used;
- no V1 dependency is introduced;
- no accidental expert-system behavior is assumed in normal operational tests;
- related suite passes;
- any unverified portion is explicitly reported.
