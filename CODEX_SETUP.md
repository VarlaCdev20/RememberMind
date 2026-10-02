# RememberMind — Codex setup

This configuration is prepared for the V2 line based on `REFAC_BDD` and the working branch `codex/agents-md`.

## Functional orientation

Before substantial normal-system work, Codex should use:

```text
docs/REMEMBERMIND_MAPA_MAESTRO.md
```

as the current functional map of RememberMind.

The map defines the normal operational product: institution, personnel, admission, resident care, daily health follow-up, medication, longitudinal history, charts/comparison, alerts, shift continuity, family access and traceability.

The future expert system is intentionally separate. Do not mix semantic-network reasoning, decision trees, multicriteria inference or expert prediction into normal operational tasks unless a future expert-system document/module explicitly requests it.

## Current functional role model

RememberMind currently uses 10 functional roles:

```text
SUPERADMINISTRADOR
GERENTE
ADMINISTRADOR
ENFERMEROS
MEDICO GENERAL/GERIATRA
PSICOLOGO/A
PEDAGOGO
NUTRICIONISTA
FISIOTERAPEUTA
FAMILIAR
```

`VOLUNTARIO` is outside current scope.

Gerente and Administrador are distinct:
- Gerente: personnel/staffing/HR-oriented management supported by the current model.
- Administrador: daily institutional operation, admission, beds, residents, documents, journeys/assignments, visits and administrative follow-up.

Roles/permissions belong to Spatie technical tables and do not change the frozen 69-table operational schema.

## Included instruction hierarchy

```text
AGENTS.md
app/AGENTS.md
database/AGENTS.md
resources/AGENTS.md
tests/AGENTS.md
```

## Included RememberMind skills

```text
.agents/skills/remembermind-module-delivery/
.agents/skills/remembermind-ui-review/
.agents/skills/remembermind-database-audit/
.agents/skills/remembermind-security-review/
.agents/skills/remembermind-release-check/
```

The upstream UI/UX Pro Max skill is installed separately with:

```text
scripts/setup-codex.ps1
```

## Instruction responsibilities

```text
AGENTS.md
→ always-on global rules

app/AGENTS.md
→ Laravel backend, security, roles, transactions, longitudinal/alert behavior

database/AGENTS.md
→ frozen 69-table operational V2 model

resources/AGENTS.md
→ forms, validation, charts, alerts, dashboards, responsive UX

tests/AGENTS.md
→ executable verification and regressions

docs/REMEMBERMIND_MAPA_MAESTRO.md
→ what the normal product must become and how its flows connect

SKILL.md
→ how Codex should perform specialized work
```

## Why `.agents/skills`

Project-local skills are stored under:

```text
.agents/skills/<skill-name>/SKILL.md
```

The root `AGENTS.md` contains global invariants while nested files contain scoped rules.

## Install / update local checkout

From the RememberMind repository:

```bash
git fetch origin
git switch codex/agents-md
git pull origin codex/agents-md
```

If the local branch does not exist yet:

```bash
git fetch origin
git switch --track origin/codex/agents-md
```

## Install UI/UX Pro Max from Git Bash

From Git Bash:

```bash
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$(pwd -W)/scripts/setup-codex.ps1"
```

From PowerShell instead:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-codex.ps1
```

The setup script intentionally does not use `--force`. If `.agents/skills/ui-ux-pro-max/SKILL.md` already exists it is left untouched.

## Verify skills

Git Bash:

```bash
ls .agents/skills
```

Expected RememberMind/project UI skills include:

```text
remembermind-module-delivery
remembermind-ui-review
remembermind-database-audit
remembermind-security-review
remembermind-release-check
ui-ux-pro-max
```

Other installed design skills may also appear and are not a problem.

## Recommended Codex workflow

For a normal module:

```text
1. Read the applicable AGENTS files.
2. Read docs/REMEMBERMIND_MAPA_MAESTRO.md.
3. Inspect the real V2 implementation.
4. Consult the frozen DB baseline if persistence is involved.
5. Reconstruct the real institutional/clinical workflow.
6. Implement end-to-end.
7. Run security/UI/database specialized reviews when relevant.
8. Test and build.
9. Run release check.
10. Commit locally when complete; do not push without explicit instruction.
```

## Smoke prompts in Codex

Database:

```text
$remembermind-database-audit verifica que la BDD operativa actual siga el baseline congelado de 69 tablas, que Gerente esté tratado como rol Spatie y que no se mezcle persistencia futura del sistema experto.
```

UI:

```text
$remembermind-ui-review revisa el seguimiento diario de un residente: formularios completos, labels/unidades, validaciones, historial, gráficas, antes/después, alertas y responsive usando $ui-ux-pro-max.
```

Operational module:

```text
$remembermind-module-delivery completa el módulo de preadmisiones respetando el flujo V2 y el mapa maestro, sin introducir lógica del futuro sistema experto.
```

Alerts:

```text
$remembermind-module-delivery revisa el flujo normal de alertas: creación por reglas aprobadas, deduplicación, eventos de ciclo de vida, responsable, atención y notificaciones externas seguras.
```

Security:

```text
$remembermind-security-review revisa la separación de permisos entre Gerente, Administrador, personal clínico y Familiar en el flujo actual.
```

Release:

```text
$remembermind-release-check verifica la tarea actual antes del commit local.
```

## Verify the project

Use the project commands:

```bash
composer test
npm run build
```

For DB reconstruction, only on a confirmed disposable development/testing DB:

```bash
php artisan migrate:fresh --seed
```

Never run destructive DB reset against unknown/shared/real data.

## Important

- The frozen 69-table DB baseline remains authoritative for operational schema.
- `docs/REMEMBERMIND_MAPA_MAESTRO.md` is the functional reference for the normal system.
- The future expert system will have its own document/design and must not be silently mixed into operational code.
- Clinical alert rules must not be invented by developers; approved evidence/context is required.
- External UI/UX Pro Max advice does not override RememberMind rules or its canonical Design System.
- Update UI/UX Pro Max intentionally; do not blindly regenerate it with `--force`.
