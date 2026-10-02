# AGENTS.md — RememberMind

## Mission

RememberMind is an institutional system for a real geriatric residence. Treat it as an operational, clinical and administrative system, not as a demo, CRUD collection or academic prototype.

The operational product must be useful and complete **without depending on the future expert system**. Its core mission is to organize the institution, resident admission, daily care, clinical follow-up, medication, interdisciplinary work, longitudinal history, alerts, communication and traceability.

A feature is complete only when the applicable process, actors, states, business rules, authorization, validation, persistence, integration, traceability, error handling, UX and tests work together.

## Sources of truth

Use project sources contextually. Do not design RememberMind from memory when the project already defines the behavior.

Priority when sources conflict:

1. Current explicit instruction from the project owner.
2. `docs/REMEMBERMIND_MAPA_MAESTRO.md` for the current functional vision of the normal operational system.
3. `REMEMBERMIND_BDD_BASELINE_CONGELADO.md` and `REMEMBERMIND_BDD_69_TABLAS.md` for frozen DB structure, persistence terminology, PK/FK, relationships and structural invariants.
4. Current functional documentation for the module.
5. Current V2 code as implementation evidence, not automatic functional truth.
6. Tests as executable evidence; they may be outdated.
7. README and auxiliary technical documentation.
8. Legacy/history only to understand migration intent; never to reintroduce V1.

For DB structure, the frozen baseline remains authoritative even when another document describes desired behavior.

Never silently blend contradictory sources. Follow the higher-authority source for the concern being changed and correct lower implementation. Ask only when authoritative sources do not resolve a material institutional/clinical decision.

## Separation: operational system vs expert system

Do not mix the normal operational system with the future expert system.

The operational system includes, among other things:
- institution and staff management;
- preadmission/admission;
- residents and beds;
- clinical record;
- daily care and shift continuity;
- medication;
- longitudinal monitoring;
- charts/comparisons;
- direct rule-based operational/clinical alerts when rules have been explicitly approved;
- email/WhatsApp notification integrations when authorized;
- audit and traceability.

The expert system will be documented separately and may later include semantic networks, decision trees, inference, multicriteria models, predictive reasoning, expert recommendations or intelligent risk classification.

Unless the task explicitly targets the expert-system document/module, **do not introduce expert inference, semantic-network logic, learned decision trees, prediction or autonomous treatment recommendations into ordinary operational features**.

The normal system may generate an alert from an explicit approved rule; that does not by itself make it an expert-system function.

## Frozen DB governance

The Operational DB V2 is frozen and contains exactly 69 operational tables. `residentes` is the central entity. Technical Laravel/Jetstream/Sanctum/Spatie tables and future expert-system persistence are outside that operational count.

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
- personal: worker/professional linked to an account;
- contacto: family/responsible/related person.

Do not use them as synonyms. Do not create new dependencies on `AdultoMayor`, `adultos_mayores`, `adulto_mayor`, `cod_am`, `cod_usu` or other V1 names.

## Active functional roles

Current owner-approved functional roles are **10**:

1. `SUPERADMINISTRADOR`
2. `GERENTE`
3. `ADMINISTRADOR`
4. `ENFERMEROS`
5. `MEDICO GENERAL/GERIATRA`
6. `PSICOLOGO/A`
7. `PEDAGOGO`
8. `NUTRICIONISTA`
9. `FISIOTERAPEUTA`
10. `FAMILIAR`

`VOLUNTARIO` remains outside current scope.

Important distinction:
- `GERENTE`: higher-level personnel/HR organization, staffing, coverage and institutional personnel management supported by the current model; no automatic clinical competence.
- `ADMINISTRADOR`: daily institutional operation, preadmissions/admissions, beds, residents, contacts, documentation, journeys/operational assignments, activities/visits and administrative follow-up; no clinical write merely by role.

Roles/permissions remain in Spatie technical tables; do not add business columns/tables merely to represent `GERENTE`.

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

## Daily-care and longitudinal principle

RememberMind must preserve the resident's evolution rather than only the latest value.

When relevant, flows should support:
- current record;
- previous values;
- baseline/history;
- trend data;
- before/after comparison when clinically meaningful;
- active alerts;
- professional authorship and business date/time;
- continuity between shifts and professionals.

Do not overwrite longitudinal health/care history to maintain a single "current state".

## Clinical forms and validation

Clinical/care forms must represent the approved process, not be artificially reduced to two generic fields.

Use all relevant approved DB fields and present appropriate context. If a clinically necessary structured field is missing from the frozen schema, document the gap instead of silently adding a column or hiding the data in JSON/EAV.

Separate:
- input/format validation;
- plausible-value validation;
- business/domain validation;
- approved clinical-rule evaluation;
- personalized resident objectives when explicitly configured by an authorized professional;
- longitudinal change/trend analysis.

Do not hardcode clinical thresholds from intuition, age alone or developer assumptions. Clinical thresholds/rules require an identified source, applicable population/context and professional/institutional approval before they drive alerts.

An unusual but plausible value should not automatically be rejected simply because it is clinically concerning; preserve the real measurement and apply confirmation/alert workflow according to the approved rule.

## Alerts

Operational alerts are a first-class system feature.

Preserve the distinction:
- `alertas`: the alert;
- `eventos_alerta`: lifecycle/history.

A normal operational alert may be created from an explicit approved rule, incident, pending/omitted process or other defined event. Do not require the expert system for this.

When applicable, alert behavior should support:
- creation;
- severity/category defined by approved rules;
- assignment/responsible context;
- recognition;
- attention;
- escalation when institutionally defined;
- closure/annulment;
- deduplication/idempotency;
- full event history.

Do not overwrite alert history. Do not create a new alert on every refresh/retry for the same unresolved business event.

External notification (email/WhatsApp) is secondary to the internal alert. Channel failure must not remove or invalidate the internal alert.

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
- expert-system clinical reasoning;
- autonomous treatment recommendation behavior;
- irreversible/high-impact actions;
- real-data destruction or exposure.

Technical autonomy means Codex decides **how** to implement. It does not mean Codex invents **what** the institution or clinical practice should require.

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

Respect and evolve the existing Laravel architecture. Current verified stack:
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

Important boundaries:
- SUPERADMINISTRADOR: read all, not automatic clinical write.
- GERENTE: personnel/HR and staffing management only as authorized; no clinical write by role.
- ADMINISTRADOR: institutional/operational management, not clinical write merely by role.
- MEDICO GENERAL/GERIATRA: ordinary prescribing role.
- ENFERMEROS: may administer/document care; do not prescribe or modify the medical order.
- PSICOLOGO/A, NUTRICIONISTA, FISIOTERAPEUTA and PEDAGOGO: write only within professional scope.
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

## External notifications and integrations

Email and WhatsApp may be used only as complementary notification channels when explicitly configured/authorized.

Rules:
- internal RememberMind state remains the source of truth;
- send the minimum sensitive information necessary;
- do not turn email/WhatsApp into a parallel clinical record;
- re-authorize detailed access inside RememberMind;
- model retries/failures safely;
- avoid duplicate business alerts because a delivery retry occurs;
- paid/external services, privacy-impacting integrations or clinical-data processing require owner approval.

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

External evidence is not automatically a RememberMind rule. New clinical rules, responsibilities, sensitive permissions, legal obligations, alert thresholds or DB changes require owner/professional decision as appropriate.

For Laravel/packages, verify the actual installed version and current official documentation before relying on an uncertain API.

## UI/UX

Use `resources/AGENTS.md` for visual work.

RememberMind should be professional, warm, clear, accessible and role/process-oriented. Respect the canonical Design System and use UI/UX Pro Max when relevant.

A UI must not invent clinical severity; the approved domain rule determines meaning and the UI represents it.

## Testing

Use `tests/AGENTS.md`.

Tests must cover the relevant positive path and meaningful negatives, authorization, invariants and regressions. Do not weaken correct tests or correct business rules merely to get green.

Current verified commands:

```bash
composer dev
composer test
php artisan test
php artisan test --filter=Something
npm run dev
npm run build
```

Testing configuration uses SQLite `:memory:`. The application must remain portable with the supported DB engines.

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
- longitudinal history/traceability are preserved where applicable;
- atomic operations use transactions where needed;
- operational alerts preserve lifecycle/history when applicable;
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

Scoped files may strengthen these rules but must not weaken frozen DB governance, backend authorization, traceability, V2 migration, data protection, separation from expert-system logic or Git safety.
