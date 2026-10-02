# RememberMind — Codex setup

This pack is prepared for the `REFAC_BDD` V2 line.

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

## Why `.agents/skills`

Current OpenAI Codex guidance uses portable project-local skills under:

```text
.agents/skills/<skill-name>/SKILL.md
```

Codex discovers `AGENTS.md` hierarchically from the repo root toward the working directory. The root file therefore contains global invariants while nested files contain scoped rules.

## Apply the pack safely

Start from a safe local branch based on `REFAC_BDD`:

```powershell
git status
git fetch origin
git checkout REFAC_BDD
git pull origin REFAC_BDD
git checkout -b codex/setup-remembermind
```

Copy the **contents of the `repo/` directory in this pack into the RememberMind repository root**.

This intentionally replaces the stale root `AGENTS.md` that still references V1 identity such as `cod_usu`.

Then inspect:

```powershell
git status
git diff -- AGENTS.md app/AGENTS.md database/AGENTS.md resources/AGENTS.md tests/AGENTS.md
```

## Install UI/UX Pro Max for Codex

Run from the RememberMind repository root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-codex.ps1
```

The upstream CLI supports:

```text
uipro init --ai codex
```

The setup script intentionally does not use `--force`. If `.agents/skills/ui-ux-pro-max/SKILL.md` already exists it is left untouched.

## Verify skills

```powershell
Test-Path .\.agents\skills\ui-ux-pro-max\SKILL.md
Get-ChildItem .\.agents\skills -Directory
```

Expected local skills:

```text
remembermind-module-delivery
remembermind-ui-review
remembermind-database-audit
remembermind-security-review
remembermind-release-check
ui-ux-pro-max
```

## Smoke prompts in Codex

```text
$remembermind-database-audit verifica que la BDD actual siga el baseline congelado sin modificarla.
```

```text
$remembermind-ui-review revisa el formulario de admisión y usa $ui-ux-pro-max para validación, modales, glassmorphism y responsive.
```

```text
$remembermind-module-delivery completa el módulo de preadmisiones respetando el flujo V2.
```

```text
$remembermind-release-check verifica la tarea actual antes del commit local.
```

## Verify the project

Use the project commands:

```powershell
composer test
npm run build
```

For DB reconstruction, only on a confirmed disposable development/testing DB:

```powershell
php artisan migrate:fresh --seed
```

Never run destructive DB reset against unknown/shared/real data.

## Commit locally

After reviewing and verifying:

```powershell
git status
git diff
git add AGENTS.md app/AGENTS.md database/AGENTS.md resources/AGENTS.md tests/AGENTS.md .agents/skills scripts/setup-codex.ps1 CODEX_SETUP.md codex-pack-manifest.json
git commit -m "chore(codex): configure RememberMind agents and skills"
```

Do not push unless explicitly intended.

## Important

- The frozen DB baseline remains authoritative for schema.
- External UI/UX Pro Max advice does not override RememberMind rules or its canonical Design System.
- Update UI/UX Pro Max intentionally; do not blindly regenerate it with `--force`.
