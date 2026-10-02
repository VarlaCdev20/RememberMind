$ErrorActionPreference = "Stop"

Write-Host "RememberMind Codex UI/UX skill setup" -ForegroundColor Cyan

if (-not (Test-Path ".git")) {
    throw "Run this script from the RememberMind repository root."
}

$branch = (git branch --show-current).Trim()
Write-Host "Current branch: $branch"

if ($branch -eq "main" -or $branch -eq "master") {
    throw "Do not configure this directly on $branch. Use a branch based on REFAC_BDD."
}

if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
    throw "npm is required to install ui-ux-pro-max-cli."
}

$target = ".agents/skills/ui-ux-pro-max/SKILL.md"

if (Test-Path $target) {
    Write-Host "ui-ux-pro-max is already present at $target. Leaving it untouched." -ForegroundColor Yellow
} else {
    Write-Host "Installing/updating ui-ux-pro-max CLI..." -ForegroundColor Cyan
    npm install -g ui-ux-pro-max-cli@latest

    if (-not (Get-Command uipro -ErrorAction SilentlyContinue)) {
        throw "uipro command was not found after installation."
    }

    Write-Host "Installing UI/UX Pro Max skill for Codex..." -ForegroundColor Cyan
    uipro init --ai codex
}

if (-not (Test-Path $target)) {
    throw "UI/UX Pro Max skill was not found at $target after setup."
}

Write-Host "Installed Codex skills:" -ForegroundColor Green
Get-ChildItem ".agents/skills" -Directory | ForEach-Object { Write-Host " - $($_.Name)" }

Write-Host ""
Write-Host "No git commit or push was performed." -ForegroundColor Green
Write-Host "Review with: git status; git diff" -ForegroundColor Green
