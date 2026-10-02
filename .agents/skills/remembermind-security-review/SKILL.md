---
name: remembermind-security-review
description: Review a RememberMind flow for backend authorization, professional competence, IDOR, mass assignment, sensitive-data exposure, audit and negative permission paths. Use for permissions, roles, clinical mutations, family access, downloads, APIs or security-sensitive changes.
---

# RememberMind security review

1. Read root and `app/AGENTS.md`.
2. Identify the resource, actor, operation, resident/context and expected business state.
3. Verify the full authorization chain as applicable:
   authenticated → active account → explicit permission → Policy → business state → relation/scope/competence.
4. Check negative actors, not only the happy role:
   - Superadmin read vs clinical write;
   - Administrator institutional vs clinical operations;
   - Médico prescription;
   - Nursing no prescription;
   - Family linked-resident-only;
   - other professional scope.
5. Check IDOR on direct IDs/codes, download URLs and API endpoints.
6. Check mass assignment and actor impersonation; client input must not choose another professional/owner/state when server context should decide it.
7. Check sensitive data is not over-fetched/serialized/logged/sent to the browser.
8. Check file downloads re-authorize access and private storage is used where required.
9. Check denial leaves no mutation and add/update negative regression tests.
10. Check audit/clinical provenance is preserved without duplicating full clinical payloads into logs.
11. Report vulnerabilities by concrete operation and remediation; fix directly related issues when within task scope.

Never solve an authorization problem by granting a broad role/global bypass.
