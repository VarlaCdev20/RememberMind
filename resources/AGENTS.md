# resources/AGENTS.md — RememberMind UI/UX

Applies to `resources/` and visible frontend behavior. Inherit root rules.

## Visual objective

RememberMind must feel:
`warm + human + professional + clear + operational + accessible + friendly`

Avoid:
- generic CRUD/template appearance;
- cold hospital aesthetic;
- unrelated styles per module;
- decorative dashboards with low operational value.

## Operational system first

The normal UI must fully support institution, resident care, health follow-up, medication, longitudinal history, charts, alerts, shift continuity and administration **without depending on the future expert system**.

Unless the task explicitly targets the expert-system module, do not add expert predictions, semantic-network explanations, learned decision-tree outputs or autonomous treatment suggestions to ordinary operational screens.

## Canonical Design System

Before substantial UI work inspect:

`resources/frontend/styles/design-system/`

and the related tokens/components/patterns/layouts.

The repository's canonical Design System is the visual source of truth. Reuse it before adding local CSS.

Do not create a second palette, typography scale, button system, card system, modal system or role-specific visual language.

Historical/ad-hoc styles may exist. When touched, migrate them toward the canonical system when reasonably in scope; do not extend obsolete patterns.

## UI/UX Pro Max

For relevant UI/UX tasks use the installed `$ui-ux-pro-max` skill as design/quality guidance.

Use it for:
- layout and responsive review;
- accessibility;
- forms and validation;
- touch/interaction;
- navigation;
- glassmorphism;
- modals/drawers;
- feedback/toasts;
- charts and data presentation.

Its recommendations do not override RememberMind's domain, permissions or canonical Design System. Synthesize recommendations into existing tokens/components instead of generating a parallel design system.

For RememberMind-specific UI review, also use `$remembermind-ui-review`.

## Glassmorphism

Adopt **moderate warm glassmorphism** as a modern accent, not as the entire UI.

Good uses:
- navbar/sidebar;
- headers/toolbars;
- modal/drawer/pop-up surfaces;
- selected highlighted cards/KPIs;
- overlays.

Avoid heavy glass on:
- long forms;
- dense tables;
- clinical history lists;
- every input/card/cell.

Glass must never reduce contrast, legibility or performance.

Prefer centralized semantic tokens/components such as glass background/border/shadow/blur instead of hardcoded `rgba()`/`backdrop-filter` values per module.

Provide readable fallback when backdrop filtering is unavailable.

## Shared semantics, not role palettes

Do not create new `--rm-enf-*`, `--rm-medico-*`, `--rm-gerente-*`, etc.

Roles share one visual system. Roles differ through content, priorities, permissions and actions.

Migrate old role-specific tokens when the affected area is changed and migration is safe.

## Semantic colors

Use stable semantics:
- success → green;
- info → blue;
- warning → amber/orange;
- error/invalid/risk/critical alert → red/danger.

Use red prominently when something is genuinely in alert/error/incorrect/risk state.

Do not use red decoratively or for normal neutral actions. If everything is red, real alerts lose meaning.

Never communicate state with color alone; combine color with text/icon/context where relevant.

Clinical severity must come from approved backend/domain rules. Blade/JS must not invent medical thresholds.

## Forms: labels are mandatory

Every visible input/select/textarea must have a clear visible label associated correctly (`for`/`id` or valid equivalent).

Placeholder is only example/helper text; never the only label.

Before submit, make required fields understandable.

Prefer reusable form components that support:
- label;
- required marker;
- helper text;
- units where applicable;
- error text;
- disabled/read-only state;
- `aria-invalid`;
- `aria-describedby`.

## Clinical/care forms

Do not reduce a real clinical/care process to two generic inputs when the approved schema supports meaningful detail.

Use the relevant approved fields and group them into a usable workflow.

A longitudinal health/care form should show, when applicable:
- resident identity/context;
- current date/time of the event;
- visible units;
- last relevant value;
- recent trend/chart;
- approved personalized objective/range when available;
- relevant active alerts;
- field-specific validation;
- observation/context fields present in the approved model.

If a clinically needed structured field is missing from the frozen schema, do not invent a hidden JSON field. Report the structural need.

Use progressive disclosure/sections/steps when a detailed form would otherwise become overwhelming. Complete does not mean visually exhausting.

## Validation UX

Validation is a first-class UX requirement.

Every meaningful form should handle:
- initial state;
- valid input;
- invalid format;
- implausible/error input where an approved rule exists;
- clinically unusual but possible input;
- processing;
- success;
- failure.

Backend validation remains authoritative. Client validation is only additional UX.

Show field-specific errors near the field. In long forms, an error summary may be added but does not replace inline errors.

Preserve correctly entered data after validation failure where technically possible.

For dynamic errors, use accessible announcement (`aria-live`, `role="alert"` or appropriate equivalent).

In long forms, move focus/attention to the first relevant error when helpful.

Do not visually treat every unusual clinical value as a data-entry error. Distinguish:
- impossible/invalid input;
- valid measurement requiring warning/confirmation;
- actual alert created by an approved rule.

## Longitudinal charts and comparison

Charts are part of the clinical follow-up UX when the underlying data is longitudinal and comparison adds meaning.

For relevant measurements support useful windows such as:
- 24 hours;
- 7 days;
- 30 days;
- custom period when appropriate.

Where data/rules support it, visualize:
- previous value;
- baseline/history;
- authorized target/range;
- min/max;
- trend;
- meaningful before/after comparison;
- markers for alerts/interventions/events.

Potential domains include:
- blood pressure;
- heart rate;
- respiratory rate;
- temperature;
- oxygen saturation;
- glucose if recorded;
- pain;
- anthropometry;
- mobility/functional measures;
- wounds over time;
- sleep/intake/hydration and other quantitative/structured tracking when the stored data supports valid visualization.

Do not invent trends when only one observation exists. Do not imply causation simply because an intervention and a change appear near each other on a chart.

## Before/after UX

When the workflow has a meaningful intervention/period, make comparison understandable:

```text
ANTES → INTERVENCIÓN/PERIODO → DESPUÉS → CAMBIO
```

Show only variables that are genuinely comparable and authorized for the viewer.

## Notifications / pop-ups

Use one reusable toast/pop-up system with semantic variants:
- success;
- info;
- warning;
- danger.

Success should clearly confirm the completed action.
Warning should explain a non-critical condition.
Danger/red should communicate error, invalid action or actual alert/risk.

A generic toast such as "There were errors" may supplement but never replace field-level validation.

Transient toasts are for interaction feedback. Persistent institutional/clinical alerts must also remain visible in the relevant page/banner/card/panel and must not depend on a disappearing toast.

Do not let notification stacks cover critical resident information or primary actions.

## Alert center and alert UX

Normal operational alerts do not require the expert system.

An alert view should make clear:
- resident/resource;
- type/category;
- severity if defined;
- created time;
- current state;
- responsible/assignment when applicable;
- short reason/context;
- next action;
- event history.

Do not create a wall of identical red cards. Prioritize by severity/state/recency and provide filters.

Active alerts must remain visible until resolved according to the real workflow.

Alert actions should support the actual lifecycle: recognize, assign, attend, close/annul or other approved transition.

If an alert was also sent by email/WhatsApp, external delivery status may be shown when useful, but the internal alert remains primary.

## Modals

Use modals for:
- confirmations;
- sensitive decisions;
- short focused forms;
- concise contextual details.

Do not put huge workflows, full records or giant tables into a modal just to avoid creating a page.

A sensitive confirmation must explain:
- what action will happen;
- what record/person it affects;
- the consequence.

Avoid vague "¿Está seguro?" without context.

Danger actions use danger/red semantics when truly destructive/sensitive.

Modal accessibility:
- move focus into modal;
- trap/manage interaction appropriately;
- support keyboard;
- restore focus reasonably on close;
- use safe Escape/backdrop behavior;
- warn before discarding meaningful unsaved changes.

Use coherent subtle fade/scale or spatial motion and respect reduced-motion.

## Loading and duplicate submit

Perceptible async actions need loading feedback.

Disable/guard the triggering control while the action is processing to reduce duplicate submit. Backend idempotency/invariants remain required.

Do not leave click → no visible response.

## Empty/error/no-permission states

Distinguish:
- no records;
- no search results;
- loading;
- validation error;
- technical error;
- no permission;
- not found.

A blank table is not a complete empty state.

Errors should include a recovery path when possible.

## Layout and density

Design by user process, not database columns.

For daily clinical/operational screens use compact **operational density**:
- clear hierarchy;
- efficient vertical space;
- readable touch targets;
- no giant decorative headers/cards.

Compact does not mean cramped.

Use the canonical spacing/type/radius/shadow scales rather than arbitrary values.

Avoid hardcoded hex/font sizes/radii/shadows when semantic tokens exist.

## Navigation and sidebar

Organize navigation by institutional meaning and role responsibility, not table order.

Conceptual groups can include:
- Inicio;
- Gestión institucional;
- Personal;
- Residentes;
- Atención clínica;
- Cuidados;
- Actividades;
- Alertas;
- Reportes;
- Administración/Seguridad.

Exact groups depend on real modules and role.

Do not show the same overloaded menu to every role. Visual hiding improves UX but never substitutes backend authorization.

The active location must be obvious. On mobile use an appropriate drawer/collapsible pattern rather than squeezing the full sidebar.

## Dashboards

Dashboard question:
**What does this role need to know or do now?**

Prefer actionable information such as pending work, active alerts, bed availability, medication due, resident assignments, incidents, expiring documents or staffing coverage when these are real and authorized.

Do not invent/hardcode metrics to fill space.

Use charts only when trend/distribution/comparison adds understanding. Do not use a chart as decoration. Include labels/legend/tooltips as appropriate and never rely on color alone.

## Role-oriented views

Do not make every role's dashboard/content identical.

- **Gerente:** personnel, staffing/coverage, organization and HR-oriented institutional information supported by the current model.
- **Administrador:** preadmission/admission/residents/beds/documents/contacts/occupancy/journeys/operational assignments/visits.
- **Nursing:** assigned residents/location, active orders, medication schedules, alerts, shift controls, care executions and continuity.
- **Medical:** clinical record, trends, alerts requiring medical review, studies, indications and prescribing workflows.
- **Psychology/Nutrition/Physiotherapy/Pedagogy:** resident follow-up and interventions within professional scope.
- **Family:** only authorized linked-resident information.
- **Superadmin:** broad supervision without automatic clinical-write UI.

## Daily-shift UX

For Nursing/operational care views, make the shift understandable:

**Start:** assigned residents, active alerts, previous shift handoff, medication/care/control due.

**During:** quick access to detailed but usable clinical/care forms and resident context.

**End:** unresolved alerts, pending/omitted care, important changes, incidents and structured handoff.

Do not force users to rediscover the same resident/context on every registration screen.

## Tables

Use tables when users need comparison across records.

Show operationally useful columns, not every DB column.

Use pagination for growing lists.

For many row actions prefer one primary action plus a contextual menu rather than a row of tiny icons.

On mobile choose priority columns, controlled horizontal scroll, cards or progressive detail based on usability.

## Resident context

In clinical workflows make the current resident unmistakable. Maintain useful context such as name/location/authorized critical information where it reduces wrong-resident risk.

Do not expose sensitive data merely because it is already loaded.

Persistent clinical alerts and authorized restrictions must have sufficient hierarchy and danger semantics when appropriate.

A resident summary should favor:
- today/current status;
- trends;
- pending work;
- active alerts;
- history/navigation.

## Accessibility

Follow semantic HTML and WCAG-oriented behavior:
- sufficient contrast;
- visible focus;
- keyboard operability;
- labels;
- meaningful button/link semantics;
- ARIA only where needed;
- reduced motion;
- touch targets;
- no hover-only essential actions.

Use `<a>` for navigation and `<button>` for actions.

Icon-only actions require accessible names and should not be ambiguous.

Do not use emoji as the primary professional icon system.

## Blade and frontend logic

Blade is presentation. No Eloquent/SQL queries in views.

`@can` may hide an action but is not backend security.

Client-side calculations do not own permissions, clinical scoring, alert severity or critical rules.

JavaScript/Alpine should manage interaction, chart rendering and UX, not become the domain source of truth.

## Components

Before repeating UI, inspect:
- `resources/views/components/`;
- Design System components/patterns.

Prefer reusable canonical components for:
buttons, inputs, selects, textareas, badges, alerts, toasts, modals, drawers, cards, tables, charts/legends, empty/loading states.

Do not over-componentize trivial spans/divs.

## Responsive

Review relevant pages in desktop, tablet and mobile.

Responsive means usable, not merely "no compiler error".

Do not assume:
- short names;
- one alert;
- one contact;
- present photo;
- tiny descriptions;
- only a few chart points.

Handle long content and optional/missing data.

Operational desktop/tablet may be primary for dense clinical work, but mobile should remain functional.

## Performance

Glass/blur/animation must not cause obvious jank. Avoid applying expensive blur/shadow effects to hundreds of rows/elements.

Charts must request/render bounded data instead of loading unlimited lifetime history by default.

Use lazy loading/reserved media space when appropriate. Do not sacrifice clarity for animation.

## Build and UI Definition of Done

For meaningful UI changes run:

```bash
npm run build
```

and relevant tests.

Before completion verify, as applicable:
- user knows where they are and what to do;
- primary action is clear;
- all inputs have labels/units where relevant;
- detailed clinical forms use approved fields without needless minimalism;
- validation distinguishes invalid input from clinical warning;
- longitudinal data exposes useful history/chart/comparison when appropriate;
- loading/double-submit state exists;
- success/warning/danger feedback is correct;
- critical alert persists beyond toast;
- alert lifecycle/action is understandable;
- modals are appropriate and accessible;
- canonical Design System is reused;
- glassmorphism is controlled;
- Gerente and Administrador views are not conflated;
- role visibility is correct;
- backend is actually connected;
- empty/error/no-permission states are present;
- responsive behavior is reasonable;
- no mock metrics/actions/`href="#"`;
- no obsolete V1 terminology;
- no accidental expert-system behavior in normal operational UI;
- build passes.

A screen is not done merely because it looks attractive.
