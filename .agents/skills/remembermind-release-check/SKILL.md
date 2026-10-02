---
name: remembermind-release-check
description: Perform the final verification pass for a RememberMind task before local commit: tests, build, V2/legacy scan, security/invariant checks, diff review and verification reporting. Use when a task/module is believed complete or before committing.
---

# RememberMind release check

1. Read applicable `AGENTS.md`.
2. Inspect `git status` and `git diff`; identify pre-existing/user changes and keep them out of the task commit.
3. Re-check the requested behavior end-to-end.
4. Re-check sensitive authorization negatives and the business invariants touched.
5. Search the changed scope for task-related red flags:
   - `AdultoMayor`, `adultos_mayores`, `cod_am`, `cod_usu`;
   - TODO/FIXME/mock/placeholder/debug;
   - empty catches or fake success;
   - direct resident creation;
   - raw destructive deletes;
   - unguarded mass assignment;
   - UI actions that have no backend.
6. Run the narrow test(s), then the related suite; broaden to full suite for cross-cutting/high-risk changes.
7. Run `npm run build` for meaningful frontend changes.
8. Run migration/seed verification only on a confirmed disposable environment when persistence work requires it.
9. Do not claim responsive/visual/accessibility verification merely from PHPUnit/build; perform the relevant UI review.
10. If a verification cannot run, label it unverified and give the exact command/reason.
11. If fully verified, stage only task files/hunks and create a coherent local conventional commit. Never push without explicit instruction.
12. Produce final sections: Implementado, Integración, Seguridad, Pruebas, Build, Legacy, Commit, Pendientes.
