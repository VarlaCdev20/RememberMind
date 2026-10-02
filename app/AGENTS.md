# app/AGENTS.md — RememberMind backend

Applies to `app/`. Inherit all root rules.

## Core backend contract

Every sensitive operation must be correct even when called without the intended UI, with manipulated parameters, from another route, repeatedly, in an invalid state or concurrently.

Backend protects the institution; UI only assists the user.

The normal operational backend must remain complete without depending on the future expert system.

## Operational vs expert-system boundary

Unless the task explicitly targets the expert-system module/document, backend code in the ordinary application must not introduce:
- semantic-network reasoning;
- learned decision trees;
- predictive models;
- multicriteria inference;
- autonomous diagnosis;
- autonomous treatment recommendations.

Ordinary backend code may evaluate **explicit approved operational/clinical rules** to create warnings or alerts. That is part of the normal system, not automatically an expert-system feature.

## Existing architecture

Before creating code, inspect related:
- Models and relationships;
- Actions;
- Services;
- Policies;
- Form Requests;
- Controllers;
- Livewire classes;
- events/listeners/jobs/notifications;
- Traits/providers/helpers.

Prefer correcting/reusing the current V2 implementation over parallel `*V2`, `*New`, `*Final` classes.

### Responsibilities

**Models**
- map exactly to frozen V2 tables;
- configure `$table`, `$primaryKey`, `$keyType`, `$incrementing`, `$timestamps`, casts and relationships correctly;
- may contain scopes, accessors/mutators and small entity invariants;
- must not become institutional-process god objects.

V2 `cod_*` keys are string codes; do not assume auto-increment `id`.

**Actions**
Use for explicit business operations such as formalizing admission, approving/rejecting preadmission, assigning bed, creating/suspending a prescription, administering medication, registering an incident, progressing an alert or executing care.

Prefer Actions when an operation changes multiple entities, needs a transaction, contains several rules or is reused.

**Services**
Use for reusable capabilities/coordination/calculation/integration. Appropriate examples include notification delivery, trend preparation for UI, document handling or reusable domain calculations that do not own expert reasoning.

Do not create `GeneralService`, `HelperService` or dumping-ground services.

**Policies**
Decide contextual authorization only. Do not mutate state in a Policy.

**Controllers**
Receive → authorize → validate → delegate → respond.

**Livewire**
Own UI state/interactions, not the only copy of critical rules.

**Blade**
Presentation only. No SQL/Eloquent querying or clinical/business interpretation.

## Input safety and mass assignment

Never pass raw client payloads indiscriminately into `create`, `update`, `fill` or `forceFill`.

Avoid:

```php
Model::create($request->all());
```

Select validated, authorized fields explicitly.

Do not trust client-supplied actor/owner/state fields when they can be derived from authenticated context. In particular, a professional must not be able to submit another `cod_personal` and attribute a clinical record to them.

## Resident lifecycle

Preserve the guard that prevents direct resident creation.

Do not bypass the approved formal-admission Action/path with:
- raw DB inserts;
- alternate controllers;
- factories/seeders used as runtime paths;
- hidden helper methods.

Approving preadmission does not create a resident. Formal admission does.

## Authorization contract

Sensitive mutations require the applicable combination:

`authenticated + active account + permission + Policy + business state + relation/scope/competence`

Do not implement global clinical bypasses such as:

```php
if ($user->hasRole('SUPERADMINISTRADOR')) return true;
```

Current functional role boundaries:
- Superadmin reads all but does not automatically write clinical content.
- Gerente manages personnel/staffing/HR-oriented processes supported by the current model; no clinical write merely by role.
- Administrator manages daily institutional operation; no diagnosis/prescription merely by role.
- Médico is the ordinary prescribing role.
- Enfermería may consult prescriptions/horarios and register care/administration, but does not prescribe or alter the medical order.
- Psicología, Nutrición, Fisioterapia and Pedagogía write within their professional scopes.
- Familiar is restricted to linked residents and authorized data only.

Protect direct-resource access against IDOR.

For a Family user, querying all residents and hiding rows in Blade is incorrect. Scope server-side.

## Gerente vs Administrador

Do not collapse these roles back into one concept.

**Gerente** primarily concerns:
- personnel management;
- staffing/coverage planning;
- organization of areas/availability;
- personnel lifecycle information supported by the current schema.

**Administrador** primarily concerns:
- preadmission/admission;
- residents;
- rooms/beds/occupancy;
- contacts/documents/consents;
- journeys and operational assignments;
- activities/visits;
- operational incident/alert follow-up;
- daily institutional coordination.

If a requested HR feature needs data not present in the frozen 69-table schema, report the structural gap instead of silently adding it.

## Professional identity

Where a clinical record requires `cod_personal`, derive it from the authenticated user's active `personal` relationship when applicable. Reject missing/incompatible professional context.

Do not let an authenticated user impersonate another professional through request input.

## Business and clinical validation

Separate:
- input/format validation;
- plausibility validation;
- authorization;
- business invariants;
- approved clinical-rule evaluation;
- DB integrity.

A `required|string` or `numeric` rule does not prove the operation is clinically or institutionally valid.

Validate cross-entity coherence, for example:
- administration and prescription are for the same resident;
- consent contact belongs to the resident;
- instrument answer uses a question from the applied instrument;
- bed is still available;
- transition is valid from the current state.

### Clinical values

When health measurements are registered:
- validate type/unit/shape;
- validate impossible/clearly implausible input when a rule is established;
- preserve plausible real measurements even when concerning;
- do not reject a concerning real measurement solely because it is outside a preferred range;
- evaluate explicit approved alert rules after/present with registration as appropriate;
- preserve the source measurement in longitudinal history.

Do not invent thresholds from age alone, developer intuition or unverified constants.

If a rule can trigger a clinical warning/alert, its source/context and institutional/professional approval must be known outside the UI.

## Longitudinal follow-up

The system must preserve and efficiently retrieve chronological evolution for approved longitudinal entities such as:
- vital signs;
- pain;
- anthropometrics;
- cognition;
- behavior;
- sleep;
- intake/hydration/elimination;
- mobility;
- wounds/curations;
- instruments;
- professional evaluations.

Backend APIs/queries used by charts should return authorized, bounded, ordered datasets. Avoid loading an unbounded lifetime history into every screen.

Before/after comparison and trend calculation are presentation/support capabilities; they must not silently change or reinterpret source records.

## Transactions and concurrency

Use `DB::transaction()` (or equivalent) when a multi-entity operation is one institutional unit.

Formal admission is atomic. A failed critical step must not leave a resident, admission, bed occupancy, contact link, history or consent in an inconsistent partial state.

For race-prone resources, re-check within the appropriate transactional context. Avoid unsafe check-then-act patterns.

Evaluate concurrency especially for:
- bed assignment/release;
- admission;
- medication administration;
- status changes;
- alert progression/assignment where conflicts matter;
- closures;
- other one-at-a-time institutional resources.

## Idempotency

Protect sensitive operations against accidental duplicate submit/retry when domain uniqueness matters.

Do not create duplicate admissions, occupancies, administrations, participants, alert events or duplicate business alerts because a request/notification retry occurred.

## Medication

Keep distinct:
`medicamento → prescripción → horarios → administración`

Registering administration must not silently change prescribed drug/dose/frequency/order.

Do not implement autonomous medication prescribing or dosage recommendations in the normal operational backend.

## Care plans

Keep distinct:
- plan: objective;
- intervention: what should be done;
- programming: when;
- execution: what actually occurred.

Normal care workflows should support operational status and continuity between shifts without inventing `tareas_cuidado`.

## Alerts

`alertas` is the alert; `eventos_alerta` is lifecycle/history.

Normal-system alerts may arise from explicitly defined operational/clinical rules, incidents or workflow conditions.

When relevant, implement:
- deterministic creation rule;
- deduplication/idempotency;
- severity/category only from approved rules;
- assignment/responsible context;
- recognition;
- attention;
- closure/annulment;
- escalation when institutionally defined;
- immutable event history.

Do not overwrite alert history when changing state.

Do not create the same unresolved alert repeatedly on page refresh, polling or job retry.

## External alert notifications

Email/WhatsApp are notification channels, not the source of truth.

Use an integration/service boundary and queue/retry where appropriate.

Rules:
- internal alert persists even if external delivery fails;
- failure/retry must not duplicate the business alert;
- send minimum necessary sensitive context;
- detailed clinical information is viewed after authenticated authorization in RememberMind;
- log delivery metadata/errors without copying full clinical payloads;
- external provider/privacy/paid-service decisions require owner approval.

## Audit and technical logs

Use Spatie Activitylog for the approved audit concern, especially sensitive state/permission/prescription/correction/document-validation actions.

Technical logs answer "what failed technically"; domain audit answers "what institutional action occurred"; clinical tables preserve clinical provenance. Do not merge these concerns.

Do not log full clinical payloads, passwords, tokens or sensitive documents unnecessarily.

## Deletion and clinical correction

Do not bypass Model/domain deletion guards using Query Builder or raw SQL.

Ordinary clinical deletion is prohibited. Use domain state/annul/suspend/close/correct/compensate behavior and preserve longitudinal history.

## Error handling

Expected domain errors should be explicit and actionable.

Catch exceptions only to recover, translate, add meaningful context, perform compensation/rollback or report appropriately. Otherwise let the central handler handle them.

Prohibited:
- empty `catch`;
- broad `Throwable` catches by habit;
- fake success;
- leaking stack traces/SQL/secrets to clients.

## Queries and performance

Prefer Eloquent/Query Builder and existing relationships/scopes.

Review:
- N+1 and eager loading;
- queries in loops;
- `Model::all()` on growing datasets;
- pagination;
- selected columns;
- unnecessary relations;
- bounded date ranges for longitudinal charts;
- alert queries by resident/state/date;
- current shift/resident assignment queries.

Performance never justifies bypassing Policies, business rules or the frozen schema.

## Files and integrations

Sensitive clinical documents use private storage and must be re-authorized on download. A URL is not authorization.

Do not accept file hashes from the client as trusted data; generate them.

External integrations should sit behind a clear Service/integration boundary. Define failure behavior so secondary integrations cannot leave primary institutional state inconsistent.

## Dates and state transitions

Distinguish technical timestamp from business event date/time.

Do not replace a real clinical/business date with `now()` when the user must record when the event actually occurred.

Validate source state before every meaningful transition. Do not invent new business states without owner approval.

## Events, jobs, observers and cache

Use events/listeners/jobs when they improve separation, not to hide the main institutional workflow.

Do not hide critical multi-entity business behavior in observers when an explicit Action is clearer.

Do not cache mutable critical information (permissions, bed availability, active prescriptions, active alerts) without correct invalidation.

Jobs that send alert notifications must be safely retryable.

## Legacy

No new V1 relationships, Models or field names.

When directly related legacy is encountered, migrate to V2 rather than adding permanent adapters.

## Backend Definition of Done

Before marking backend work complete, verify the applicable:
- happy path;
- negative authorization paths;
- Gerente/Administrador boundary;
- input validation;
- plausibility/domain validation when relevant;
- state transition;
- cross-entity invariants;
- longitudinal history preservation;
- transaction/rollback;
- concurrency/idempotency risk;
- alert lifecycle/deduplication when relevant;
- notification failure behavior when relevant;
- audit/provenance;
- error semantics;
- relationships;
- tests;
- directly related legacy cleanup;
- no accidental expert-system inference in ordinary operational code.

Search the changed scope for suspicious patterns such as:
`AdultoMayor`, `cod_am`, `cod_usu`, empty `catch`, raw delete bypasses, raw request mass assignment, `Model::all()` in large views, TODO/FIXME/debug substitutes.
