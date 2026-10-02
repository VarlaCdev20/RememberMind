---
name: remembermind-ui-review
description: Design, refactor, or review RememberMind UI/UX using its canonical Design System, moderate warm glassmorphism, accessible forms/validation, semantic alerts/toasts/modals, responsive behavior and role-oriented workflows. Use for any meaningful Blade/Livewire/frontend/dashboard/form/sidebar work.
---

# RememberMind UI/UX review

1. Read `resources/AGENTS.md` and the relevant current UI files.
2. Inspect the canonical Design System under `resources/frontend/styles/design-system/` before inventing CSS/components.
3. Invoke `$ui-ux-pro-max` for the dominant concern (e.g. form validation, modal focus, navigation hierarchy, responsive table, glassmorphism) when it is installed.
4. Treat UI/UX Pro Max as recommendation evidence; RememberMind's domain and Design System stay authoritative.
5. Design from the user's workflow, not the DB schema. Identify the role, task, primary action, critical information, error recovery and next step.
6. Use moderate warm glassmorphism mainly for navigation, floating surfaces, modal/drawer/pop-up and selected highlights; never trade away contrast, legibility or performance.
7. Forms: visible associated label for every field, required state, helper text where needed, inline validation, accessible errors, preserved valid input, processing state and duplicate-submit protection.
8. Feedback: success=green, info=blue, warning=amber/orange, real error/risk/critical alert=red. State must not rely on color alone.
9. Use reusable toast/pop-up variants for interaction results. Persistent critical alerts must remain visible in the page and not depend on a transient toast.
10. Use modals for confirmations/short focused work only. Explain consequence, manage focus/keyboard, and use danger semantics only when actually dangerous.
11. Check empty/loading/error/no-permission/not-found states.
12. Check desktop/tablet/mobile, long text, missing photos/data and touch targets.
13. Ensure Blade/JS does not invent clinical severity, permissions or business state.
14. Reuse existing components/tokens; do not create role palettes or a parallel visual system.
15. Run relevant tests and `npm run build`.

Finish with a concise list of UX risks found and what was changed.
