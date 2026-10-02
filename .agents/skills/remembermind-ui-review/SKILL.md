---
name: remembermind-ui-review
description: Design, refactor, or review RememberMind UI/UX using its canonical Design System, longitudinal follow-up, detailed forms, charts/comparisons, alert workflows, accessible validation and role-oriented workflows.
---

# RememberMind UI/UX review

1. Read `resources/AGENTS.md` and `docs/REMEMBERMIND_MAPA_MAESTRO.md` plus the relevant current UI files.
2. Inspect the canonical Design System under `resources/frontend/styles/design-system/` before inventing CSS/components.
3. Invoke `$ui-ux-pro-max` for the dominant concern when installed.
4. Treat UI/UX Pro Max as recommendation evidence; RememberMind's domain and Design System stay authoritative.
5. Design from the user's workflow, not the DB schema. Identify role, task, primary action, critical information, error recovery and next step.
6. Preserve the 10-role model and visibly distinguish Gerente from Administrador where their workflows differ.
7. Keep normal operational UI separate from future expert-system UI unless the task explicitly targets the expert module.
8. For longitudinal health/care modules, show useful history, recent values, trends and before/after comparison when the stored data makes it valid.
9. Clinical/care forms must use relevant approved fields, clear units, visible labels, helper text, validation, processing state and duplicate-submit protection. Do not reduce a real process to a couple of generic inputs.
10. Distinguish invalid input from a valid measurement that triggers warning/alert. UI must not invent clinical thresholds.
11. Alerts must be persistent and operational: reason/context, state, responsible/assignment when applicable, lifecycle actions and history. A toast alone is insufficient.
12. Use success=green, info=blue, warning=amber/orange, real error/risk/critical alert=red. State must not rely on color alone.
13. Use moderate warm glassmorphism mainly for navigation, floating surfaces, modal/drawer/pop-up and selected highlights; never trade away contrast, legibility or performance.
14. Use modals for confirmations/short focused work only. Explain consequence, manage focus/keyboard, and use danger semantics only when actually dangerous.
15. Check empty/loading/error/no-permission/not-found states.
16. Check desktop/tablet/mobile, long text, missing photos/data, chart density and touch targets.
17. Ensure Blade/JS does not invent clinical severity, permissions, alert rules or business state.
18. Reuse existing components/tokens; do not create role palettes or a parallel visual system.
19. Run relevant tests and `npm run build`.

Finish with a concise list of UX risks found and what was changed.
