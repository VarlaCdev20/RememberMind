---
name: remembermind-release-check
description: Perform the final verification pass for a RememberMind operational task before local commit: tests, build, V2/legacy scan, role boundaries, longitudinal history, alerts, security and diff review.
---

# RememberMind release check

1. Read applicable `AGENTS.md` and `docs/REMEMBERMIND_MAPA_MAESTRO.md` when the task changes normal-system behavior.
2. Inspect `git status` and `git diff`; identify pre-existing/user changes and keep them out of the task commit.
3. Re-check the requested behavior end-to-end.
4. Re-check sensitive authorization negatives and the business invariants touched.
5. If roles are involved, verify the current 10-role model and especially Gerente vs Administrador separation.
6. For health/care work, verify longitudinal history is preserved and charts/comparisons use real authorized source data rather than mocks or overwritten current values.
7. For alerts, verify creation rule, deduplication/idempotency, lifecycle/event history and notification failure behavior when applicable.
8. Confirm ordinary operational work has not accidentally introduced future expert-system behavior or persistence.
9. Search the changed scope for task-related red flags:
   - `AdultoMayor`, `adultos_mayores`, `cod_am`, `cod_usu`;
   - TODO/FIXME/mock/placeholder/debug;
   - empty catches or fake success;
   - direct resident creation;
   - raw destructive deletes;
   - unguarded mass assignment;
   - UI actions that have no backend;
   - invented clinical thresholds;
   - duplicate alert creation on retry/refresh.
10. Run the narrow test(s), then the related suite; broaden to full suite for cross-cutting/high-risk changes.
11. Run `npm run build` for meaningful frontend changes.
12. Run migration/seed verification only on a confirmed disposable environment when persistence work requires it.
13. Do not claim responsive/visual/accessibility/chart verification merely from PHPUnit/build; perform the relevant UI review.
14. If a verification cannot run, label it unverified and give the exact command/reason.
15. If fully verified, stage only task files/hunks and create a coherent local conventional commit. Never push without explicit instruction.
16. Produce final sections: Implementado, Integración, Seguridad, Pruebas, Build, Legacy, Commit, Pendientes.
