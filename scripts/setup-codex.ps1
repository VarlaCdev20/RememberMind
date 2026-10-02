$ErrorActionPreference = 'Stop'

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
if (-not (Test-Path -LiteralPath (Join-Path $repoRoot '.git'))) {
    throw 'Run this script from a RememberMind Git checkout.'
}

$required = @(
    'AGENTS.md',
    'app/AGENTS.md',
    'database/AGENTS.md',
    'resources/AGENTS.md',
    'tests/AGENTS.md',
    '.agents/skills/remembermind-module-delivery/SKILL.md',
    '.agents/skills/remembermind-ui-review/SKILL.md',
    '.agents/skills/remembermind-database-audit/SKILL.md',
    '.agents/skills/remembermind-security-review/SKILL.md',
    '.agents/skills/remembermind-release-check/SKILL.md'
)

$missing = @($required | Where-Object { -not (Test-Path -LiteralPath (Join-Path $repoRoot $_)) })
if ($missing.Count -gt 0) {
    throw "Missing Codex files: $($missing -join ', ')"
}

$rootInstructions = Get-Content -LiteralPath (Join-Path $repoRoot 'AGENTS.md') -Raw
if ($rootInstructions -notmatch '70 operational tables' -or $rootInstructions -notmatch 'V2\.1') {
    throw 'AGENTS.md does not describe the current 70-table V2.1 baseline.'
}

$uiSkillCandidates = @(
    (Join-Path $repoRoot '.agents/skills/ui-ux-pro-max/SKILL.md'),
    (Join-Path $env:USERPROFILE '.codex/skills/ui-ux-pro-max/SKILL.md'),
    (Join-Path $env:USERPROFILE '.agents/skills/ui-ux-pro-max/SKILL.md')
)
$uiSkill = $uiSkillCandidates | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1

Write-Host 'RememberMind Codex files: OK' -ForegroundColor Green
Write-Host 'Project skills:' -ForegroundColor Cyan
Get-ChildItem -LiteralPath (Join-Path $repoRoot '.agents/skills') -Directory | ForEach-Object {
    Write-Host " - $($_.Name)"
}
if ($uiSkill) {
    Write-Host "Optional UI/UX Pro Max skill found: $uiSkill" -ForegroundColor Green
} else {
    Write-Warning 'Optional UI/UX Pro Max skill was not found. The RememberMind skills remain usable.'
}

git -C $repoRoot status --short --branch
Write-Host 'Verification only: no packages were installed, no data was changed, and no commit or push was made.'
