# AGENTS.md — RememberMind

## Mission

RememberMind is an institutional system for a real geriatric residence. Treat it as an operational, clinical and administrative system, not as a demo, CRUD collection or academic prototype.

A feature is complete only when the applicable process, actors, states, business rules, authorization, validation, persistence, integration, traceability, error handling, UX and tests work together.

## Sources of truth

Use project sources contextually. Do not design RememberMind from memory when the repository already defines the behavior.

Priority when sources conflict:

1. Current explicit instruction from the project owner.
2. `docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md` and `docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md` for DB structure, persistence terminology, PK/FK, relationships and structural invariants.
3. Current functional documentation for the module.
4. Current V2 code as implementation evidence, not automatic functional truth.
5. Tests as executable evidence; they may be outdated.
6. README and auxiliary technical documentation.
7. Legacy/history only to understand migration intent; never to reintroduce V1.

Never silently blend contradictory sources. Follow the higher-authority source and correct the lower implementation. Ask only when higher-authority sources do not resolve a material institutional decision.

## Frozen DB governance

The Operational DB V2.1 baseline is frozen and contains exactly 70 operational tables. The owner approved the V2.2 `objetivos_signos_vitales` extension on 2026-10-04, bringing the current inventory to 71; see `docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md`. `residentes` is the central entity. Always consult `docs/base-de-datos/` before DB analysis or changes; the 69-table V2.0 document is historical only.

Without explicit owner approval, do not add/delete/merge/split/rename/modify:
- tables or columns;
- PK/FK;
- cardinalities;
- structural rules;
- catalogs;
- `cod_*` naming;
- relationships.

Do not replace FK with text, introduce JSON/EAV to avoid the model, or simplify structural relationships.

If a structural issue is detected:
1. document the issue;
2. explain impact and options;
3. isolate the blocked structural change;
4. continue safe work that does not depend on it;
5. request approval before changing the frozen model.

`database/AGENTS.md` contains the detailed database rules.

## V2 terminology

Use these terms consistently:
- postulante/adulto mayor: person still in preadmission;
- residente: formally admitted person;
- usuario: access account;
- personal: worker/professional, optionally linked to a user account;
- contacto: family/responsible/related person.

Do not use them as synonyms. Do not create new dependencies on `AdultoMayor`, `adultos_mayores`, `adulto_mayor`, `cod_am`, `cod_usu` or other V1 names.

## Mandatory institutional flow

Preserve:

`Preadmisión PENDIENTE → revisión → APROBADA/RECHAZADA → admisión formal con cama → residente ADMITIDO`

Rules:
- approving a preadmission does not create a resident;
- the resident is created only during formal admission;
- no direct resident-creation CRUD may bypass admission;
- occupied beds are unavailable;
- a resident cannot have two active bed occupancies;
- atomic institutional operations must use transactions when required.

## Autonomy

Codex has high technical autonomy.

For ordinary reversible technical choices:
- inspect relevant code;
- decide;
- implement;
- test;
- review;
- correct;
- integrate.

Do not interrupt the owner for routine programming choices.

Ask before decisions that create or change:
- frozen DB structure;
- clinical/institutional rules;
- professional responsibilities;
- sensitive permission boundaries;
- critical institutional flow;
- legal obligations;
- irreversible/high-impact actions;
- real-data destruction or exposure.

Technical autonomy means Codex decides **how** to implement. It does not mean Codex invents **what** the institution should require.

## Responsible scope

Keep the requested task as primary scope, but automatically fix directly related blockers such as:
- security/authorization defects;
- V2 contradictions;
- broken relationships;
- business-rule violations;
- directly related legacy;
- regressions introduced by the task;
- obvious N+1/query defects in the changed flow;
- duplicated logic that blocks safe maintenance.

For unrelated problems: report them; do not turn a localized task into a global rewrite.

Use the Boy Scout rule in changed code:
`tocar → comprender → corregir → mejorar razonablemente → verificar`.

## Architecture

Respect and evolve the existing Laravel architecture. Check `composer.lock` and `package-lock.json` for installed versions. Project stack:
- PHP `^8.3`;
- Laravel `^13.0`;
- Jetstream `^5.5`;
- Sanctum `^4.0`;
- Livewire `^4.3`;
- Spatie Permission `^7.3`;
- Spatie Activitylog `^4.12`;
- PHPUnit `^12.5`;
- Tailwind CSS `^3.4`;
- Alpine.js `^3.15`;
- Vite `^8`.

Use existing Models, Actions, Services, Policies, Form Requests, Controllers, Livewire components, events/listeners/jobs and Blade components when appropriate.

Frontend Livewire code belongs in `app/Frontend/Livewire/`; modular backend code belongs in `app/Backend/Modulos/`. Do not add `app/Livewire/` or `app/Services/`. The canonical user PK is string `cod_usuario`, never `cod_usu` or an auto-increment key; the resident PK is string `cod_residente`, never `cod_am`.

Guideline:
- Models: relationships, casts, scopes, entity-local behavior and small invariants.
- Actions: explicit institutional operations, especially multi-entity/transactional/reusable work.
- Services: reusable capabilities or coordination that does not belong to one entity/action.
- Policies: contextual authorization.
- Requests/Livewire validation: input shape; business rules still belong in domain/backend.
- Controllers: thin.
- Livewire: interaction/UI state; not the only home for critical business rules.
- Blade: presentation; no SQL or institutional logic.

Do not introduce Repository Pattern, Unit of Work, CQRS, Event Sourcing, microservices, full Clean/Hexagonal architecture or interfaces-for-every-class by preference alone.

## Authorization

Hiding a button is never sufficient security.

Sensitive operations must enforce the applicable combination of:

`authenticated session + active account + explicit permission + contextual Policy + valid business rule + relation/scope/professional competence`

Authenticate with `usuarios.correo` and require state `ACTIVO`. Public registration is disabled. Never hardcode passwords, create users/residents/personnel as a fallback, select the first available row to satisfy an FK, or persist incomplete data in place of validation.

Important boundaries:
- SUPERADMINISTRADOR: read all, not automatic clinical write.
- GERENTE: institutional direction, personnel and master planning, not clinical write merely by role.
- ADMINISTRADOR: daily operations, not account/personnel management or clinical write merely by role.
- MEDICO GENERAL/GERIATRA: ordinary prescribing role.
- ENFERMEROS: may administer/document care; do not prescribe or modify the medical order.
- FAMILIAR: only authorized information for linked residents.
- A professional may not attribute a clinical record to another professional through manipulated input.

`app/AGENTS.md` contains backend-specific rules.

## Clinical history and audit

Do not physically delete ordinary clinical history or overwrite longitudinal records to hide errors.

Use the domain mechanism where applicable:
- state;
- annulment;
- suspension;
- closure;
- correction;
- compensating record;
- link to previous record.

Use `spatie/laravel-activitylog` for approved technical/business audit. Do not create a second corporate audit table. Clinical provenance remains in domain records.

## Errors

Never swallow failures or fake success.

Prohibited:
- empty `catch`;
- catch-and-ignore;
- returning success after a failed operation;
- exposing stack traces, SQL, secrets or server internals to end users.

Expected validation/business failures should produce clear actionable messages. Unexpected errors should use Laravel's error/reporting mechanisms with safe user-facing responses and only necessary diagnostic context.

## Research

Always consult project sources first.

When requirements are missing, external research is allowed to understand real geriatric, clinical, legal or technical practice. Prefer official regulation, public-health bodies, standards, official framework/package docs and high-quality scientific sources.

External evidence is not automatically a RememberMind rule. New clinical rules, responsibilities, sensitive permissions, legal obligations or DB changes require owner decision.

For Laravel/packages, verify the actual installed version and current official documentation before relying on an uncertain API.

## UI/UX

Use `resources/AGENTS.md` for visual work.

For RememberMind UX/UI use the specialized stack documented in
`docs/frontend/STACK_SKILLS_UX_UI.md` and its shared visual contract.
`remembermind-ui-review` selects the applicable stages; do not load every
specialty for a localized task. An owner-approved image controls the visual
structure of its screen, with necessary domain/accessibility adaptations.
Generic design skills remain auxiliary and cannot override this contract.

RememberMind should be professional, warm, clear, accessible and role/process-oriented. Respect the canonical Design System and use UI/UX Pro Max when relevant.

A UI must not invent clinical severity; the domain determines meaning and the UI represents it.

## Testing

Use `tests/AGENTS.md`.

Tests must cover the relevant positive path and meaningful negatives, authorization, invariants and regressions. Do not weaken correct tests or correct business rules merely to get green.

Project commands:

```bash
composer install
npm ci
composer dev
composer test
php artisan test
php artisan test --filter=Something
npm run dev
npm run build
```

Testing configuration uses SQLite `:memory:`. The application must remain portable with the supported DB engines.
Use PostgreSQL for integration/CI validation.
Run `php artisan migrate` only against the intended configured database.

Run `php artisan migrate:fresh --seed` only on a confirmed disposable development/testing database.

## Dependency policy

Prefer existing framework/project capabilities.

A new dependency is allowed when it solves a real problem, is maintained, compatible, appropriately licensed, secure enough for the use case and reduces complexity/risk.

Ask before introducing:
- paid/mandatory external services;
- proprietary critical infrastructure;
- third-party storage/processing of clinical data;
- architecture-wide replacements;
- material privacy changes.

## Git and data safety

Before relevant changes inspect:

```bash
git status
git diff
```

Treat pre-existing user changes as intentional unless proven otherwise.

Do not initiate destructive commands such as:
- `git reset --hard`;
- `git clean -fd`;
- `git checkout -- .`;
- `git restore .`;
- force push;
- destructive DB reset on unknown/important data.

Never overwrite `.env` wholesale or expose secrets.

Use synthetic data in tests, seeders, examples and screenshots.

## Commits

Local commits are allowed.

For a small, self-contained, fully verified task, create a local commit automatically. Larger work may use coherent logical commits.

Before commit:
- inspect status/diff;
- stage only relevant files/hunks;
- exclude user-unrelated changes, secrets, debug artifacts and real data;
- ensure applicable tests/build pass.

Use concrete conventional messages.

Do not push automatically. Push requires explicit owner instruction.

## Legacy

Do not create permanent V1/V2 compatibility layers.

During a task:
- migrate/remove directly related executable legacy;
- report unrelated legacy.

After critical V2 modules are stable, repository-wide executable legacy cleanup is mandatory.

Final V2 state must not retain business migrations, Models, Actions, Services, Policies, Livewire, Requests, tests, reports or routes that still depend on obsolete V1 domain names.

## Definition of Done

"Code written" is not "task finished".

A task is finished only when, proportionally to scope:
- requested behavior works end-to-end;
- business states/transitions/invariants are enforced;
- backend authorization is correct;
- validation is correct;
- persistence/relationships are consistent;
- atomic operations use transactions where needed;
- audit/traceability are preserved;
- UI states are complete when applicable;
- integration/navigation/routes are connected;
- related legacy is removed;
- relevant tests pass;
- frontend build passes when applicable;
- no essential TODO/FIXME/mock/placeholder/hardcode/debug substitute remains;
- regressions introduced by the task are corrected.

If a verification could not be run, explicitly distinguish **implemented** from **verified** and state what remains to run.

## Final report

For significant work, report concisely:
- Implementado
- Integración
- Seguridad
- Pruebas
- Build
- Legacy
- Commit
- Pendientes

## Scoped instructions

Additional rules apply under:
- `app/AGENTS.md`
- `database/AGENTS.md`
- `resources/AGENTS.md`
- `tests/AGENTS.md`

Scoped files may strengthen these rules but must not weaken frozen DB governance, backend authorization, traceability, V2 migration, data protection or Git safety.
