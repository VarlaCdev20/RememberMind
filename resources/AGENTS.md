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

Do not create new `--rm-enf-*`, `--rm-medico-*`, `--rm-psico-*`, etc.

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

Clinical severity must come from domain/backend. Blade/JS must not invent medical thresholds.

## Forms: labels are mandatory

Every visible input/select/textarea must have a clear visible label associated correctly (`for`/`id` or valid equivalent).

Placeholder is only example/helper text; never the only label.

Before submit, make required fields understandable.

Prefer reusable form components that support:
- label;
- required marker;
- helper text;
- error text;
- disabled/read-only state;
- `aria-invalid`;
- `aria-describedby`.

## Validation UX

Validation is a first-class UX requirement.

Every meaningful form should handle:
- initial state;
- valid input;
- invalid input;
- processing;
- success;
- failure.

Backend validation remains authoritative. Client validation is only additional UX.

Show field-specific errors near the field. In long forms, an error summary may be added but does not replace inline errors.

Preserve correctly entered data after validation failure where technically possible.

For dynamic errors, use accessible announcement (`aria-live`, `role="alert"` or appropriate equivalent).

In long forms, move focus/attention to the first relevant error when helpful.

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

Examples of conceptual groups:
- Inicio;
- Gestión institucional;
- Residentes;
- Atención clínica;
- Cuidados;
- Actividades;
- Reportes;
- Administración/Seguridad.

Exact groups depend on real modules and role.

Do not show the same overloaded menu to every role. Visual hiding improves UX but never substitutes backend authorization.

The active location must be obvious. On mobile use an appropriate drawer/collapsible pattern rather than squeezing the full sidebar.

## Dashboards

Dashboard question:
**What does this role need to know or do now?**

Prefer actionable information such as pending work, active alerts, bed availability, medication due, resident assignments, incidents or expiring documents when these are real and authorized.

Do not invent/hardcode metrics to fill space.

Use charts only when trend/distribution/comparison adds understanding. Do not use a chart as decoration. Include labels/legend/tooltips as appropriate and never rely on color alone.

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

## Role-oriented views

Do not make every role's dashboard/content identical.

Examples:
- Administration: preadmission/admission/residents/beds/documents/contacts/occupancy.
- Nursing: resident/location, active orders, medication schedules, alerts, shift tasks and care records.
- Medical: deeper clinical record and prescribing workflows.
- Family: only authorized linked-resident information.
- Superadmin: broad supervision without automatic clinical-write UI.

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

Client-side calculations do not own permissions, clinical scoring, state or critical rules.

JavaScript/Alpine should manage interaction, not become the domain source of truth.

## Components

Before repeating UI, inspect:
- `resources/views/components/`;
- Design System components/patterns.

Prefer reusable canonical components for:
buttons, inputs, selects, textareas, badges, alerts, toasts, modals, drawers, cards, tables, empty/loading states.

Do not over-componentize trivial spans/divs.

## Responsive

Review relevant pages in desktop, tablet and mobile.

Responsive means usable, not merely "no compiler error".

Do not assume:
- short names;
- one alert;
- one contact;
- present photo;
- tiny descriptions.

Handle long content and optional/missing data.

Operational desktop/tablet may be primary for dense clinical work, but mobile should remain functional.

## Performance

Glass/blur/animation must not cause obvious jank. Avoid applying expensive blur/shadow effects to hundreds of rows/elements.

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
- all inputs have labels;
- validation is visible and accessible;
- loading/double-submit state exists;
- success/warning/danger feedback is correct;
- critical alert persists beyond toast;
- modals are appropriate and accessible;
- canonical Design System is reused;
- glassmorphism is controlled;
- role visibility is correct;
- backend is actually connected;
- empty/error/no-permission states are present;
- responsive behavior is reasonable;
- no mock metrics/actions/`href="#"`;
- no obsolete V1 terminology;
- build passes.

A screen is not done merely because it looks attractive.
