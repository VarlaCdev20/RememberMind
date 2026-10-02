---
name: remembermind-security-review
description: Review a RememberMind flow for backend authorization, Gerente/Administrador separation, professional competence, IDOR, sensitive-data exposure, alert notifications, audit and negative permission paths.
---

# RememberMind security review

1. Read root and `app/AGENTS.md` plus `docs/REMEMBERMIND_MAPA_MAESTRO.md` when the flow is part of the normal operational system.
2. Identify the resource, actor, operation, resident/context and expected business state.
3. Verify the full authorization chain as applicable:
   authenticated → active account → explicit permission → Policy → business state → relation/scope/competence.
4. Check negative actors, not only the happy role:
   - Superadmin read vs clinical write;
   - Gerente personnel/staffing responsibilities vs clinical operations;
   - Administrador institutional operation vs clinical operations;
   - Médico prescription;
   - Nursing no prescription;
   - Family linked-resident-only;
   - other professional scope.
5. Ensure Gerente and Administrador are not granted interchangeable permissions merely for convenience.
6. Check IDOR on direct IDs/codes, resident history, charts, alert detail, download URLs and API endpoints.
7. Check mass assignment and actor impersonation; client input must not choose another professional/owner/state when server context should decide it.
8. Check sensitive data is not over-fetched/serialized/logged/sent to the browser.
9. Check file downloads re-authorize access and private storage is used where required.
10. For email/WhatsApp alert notifications, verify the message contains only the minimum necessary context and that detailed clinical information remains behind RememberMind authentication/authorization.
11. Verify notification retries/failures cannot create duplicate business alerts or change alert state incorrectly.
12. Check denial leaves no mutation and add/update negative regression tests.
13. Check audit/clinical provenance and alert-event history are preserved without duplicating full clinical payloads into logs.
14. Keep normal operational security separate from future expert-system reasoning; do not grant broader access merely because a future intelligent module may consume data.
15. Report vulnerabilities by concrete operation and remediation; fix directly related issues when within task scope.

Never solve an authorization problem by granting a broad role/global bypass.
