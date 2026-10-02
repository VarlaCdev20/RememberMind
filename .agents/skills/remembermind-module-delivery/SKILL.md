---
name: remembermind-module-delivery
description: Implement or complete a RememberMind operational module end-to-end across backend, UI, permissions, persistence, integration, audit and tests. Use for substantial cross-layer features of the normal institutional and care system.
---

# RememberMind module delivery

1. Read applicable `AGENTS.md` files for the directories you will touch.
2. Read `docs/REMEMBERMIND_MAPA_MAESTRO.md` for current functional intent of the normal operational system.
3. Consult the frozen DB baseline when persistence/schema is involved and current module docs when available.
4. Inspect the existing V2 implementation before creating parallel code: Models, relationships, Actions, Services, Policies, Requests, Controllers/Livewire, routes, views, Design System, permissions, tests, seeders/factories.
5. Reconstruct the real institutional flow: actors, purpose, states, transitions, permissions, negative cases, invariants, longitudinal history, alerts, traceability and downstream dependencies.
6. Preserve the 10-role model. Distinguish `GERENTE` from `ADMINISTRADOR` according to the map master and root AGENTS.
7. Keep ordinary operational modules separate from the future expert-system module unless the task explicitly targets that future area.
8. Normal modules may generate alerts from explicit approved deterministic rules. Preserve `alertas` + `eventos_alerta`, deduplication and lifecycle history.
9. For health/care modules, preserve longitudinal data and provide appropriate history/trend/comparison access instead of overwriting a single current value.
10. Clinical/care forms should use relevant approved fields, units, validation and context. Do not reduce a real process to minimal generic fields merely for speed.
11. Do not invent clinical thresholds; alert-driving rules require an approved source/context.
12. Do not invent new sensitive institutional/clinical rules. Isolate and request a decision only for the material ambiguous rule; continue all safe work.
13. If the frozen DB would need structural change, stop only that structural action, document impact/options and request approval.
14. Implement the minimum coherent end-to-end surface. Fix directly related security/V2/legacy/integration blockers.
15. Ensure backend authorization remains the source of truth; UI hiding alone is insufficient.
16. For UI work, invoke `$remembermind-ui-review` and `$ui-ux-pro-max` when available.
17. Add/update tests for happy path, authorization negatives, invalid states/invariants, longitudinal-history preservation, alert lifecycle/deduplication and regressions proportional to risk.
18. Run narrow tests first, then the related suite; run `npm run build` for meaningful frontend changes.
19. Review `git diff`/`git status`, remove task-related debug/TODO/mock/legacy artifacts and create a coherent local commit when fully verified and self-contained. Never push without explicit instruction.
20. Report Implementado, Integración, Seguridad, Pruebas, Build, Legacy, Commit and true Pendientes.

Success means the institutional process works end-to-end without depending on future expert-system functionality.
