---
name: remembermind-module-delivery
description: Implement or complete a RememberMind module end-to-end across backend, UI, permissions, persistence, integration, audit and tests. Use for requests such as "completa el módulo", "implementa el flujo", or substantial cross-layer features.
---

# RememberMind module delivery

1. Read applicable `AGENTS.md` files for the directories you will touch.
2. Consult relevant project sources. Use the frozen DB baseline when persistence/schema is involved and current module docs when available.
3. Inspect the existing V2 implementation before creating parallel code: Models, relationships, Actions, Services, Policies, Requests, Controllers/Livewire, routes, views, Design System, permissions, tests, seeders/factories.
4. Reconstruct the real institutional flow: actors, purpose, states, transitions, permissions, negative cases, invariants, traceability and downstream dependencies.
5. Do not invent new sensitive institutional/clinical rules. Isolate and request a decision only for the material ambiguous rule; continue all safe work.
6. If the frozen DB would need structural change, stop only that structural action, document impact/options and request approval.
7. Implement the minimum coherent end-to-end surface. Fix directly related security/V2/legacy/integration blockers.
8. Ensure backend authorization remains the source of truth; UI hiding alone is insufficient.
9. For UI work, invoke `$remembermind-ui-review` and `$ui-ux-pro-max` when available.
10. Add/update tests for happy path, authorization negatives, invalid states/invariants and regressions proportional to risk.
11. Run the narrow tests first, then the related suite; run `npm run build` for meaningful frontend changes. Use destructive DB verification only on a confirmed disposable environment.
12. Review `git diff`/`git status`, remove task-related debug/TODO/mock/legacy artifacts and create a coherent local commit when the task is fully verified and self-contained. Never push without explicit instruction.
13. Report Implementado, Integración, Seguridad, Pruebas, Build, Legacy, Commit and true Pendientes.

Success means the institutional process works end-to-end, not merely that files were added.
