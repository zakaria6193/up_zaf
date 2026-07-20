# Setup UP1 on another PC for testing
# Safe: only creates local .env / sqlite / vendor / node_modules on THAT machine.
# Does not change the original developer's project or .env.
#
# Prerequisites (install once):
#   - PHP 8.3+ with extensions: sqlite, mbstring, openssl, pdo, tokenizer, xml, curl, fileinfo, gd
#   - Composer: https://getcomposer.org/
#   - Node.js 20+: https://nodejs.org/
#   - Git
#
# Usage (from project root after clone):
#   powershell -ExecutionPolicy Bypass -File .\scripts\setup-local.ps1
#   composer run dev

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

function Write-Step($message) {
    Write-Host ""
    Write-Host "==> $message" -ForegroundColor Cyan
}

Write-Step "Checking PHP / Composer / Node"
$php = Get-Command php -ErrorAction SilentlyContinue
$composer = Get-Command composer -ErrorAction SilentlyContinue
$node = Get-Command node -ErrorAction SilentlyContinue
$npm = Get-Command npm -ErrorAction SilentlyContinue

if (-not $php) { throw "PHP not found. Install PHP 8.3+ and add it to PATH." }
if (-not $composer) { throw "Composer not found. Install Composer and add it to PATH." }
if (-not $node) { throw "Node.js not found. Install Node 20+ and add it to PATH." }
if (-not $npm) { throw "npm not found. Reinstall Node.js." }

Write-Host ("PHP:      " + (php -v | Select-Object -First 1))
Write-Host ("Node:     " + (node -v))
Write-Host ("Composer: " + (composer -V 2>$null | Select-Object -First 1))

Write-Step "Installing app (fresh DB + demo data)"
composer run setup:tester
if ($LASTEXITCODE -ne 0) {
    throw "composer run setup:tester failed"
}

Write-Host ""
Write-Host "Setup complete." -ForegroundColor Green
Write-Host ""
Write-Host "Start the app:" -ForegroundColor Yellow
Write-Host "  composer run dev"
Write-Host ""
Write-Host "Then open: http://127.0.0.1:8000" -ForegroundColor Yellow
Write-Host ""
Write-Host "Demo logins:" -ForegroundColor Yellow
Write-Host "  Admin:    /adminos/login  ->  up1 / 147235689"
Write-Host "  Business: /              ->  mohamed@example.com / password"
Write-Host "  Business: /              ->  fatima@example.com / password"
Write-Host ""
