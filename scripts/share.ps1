# Share UP1 temporarily with someone on another network.
# Does NOT change .env. Stop the script (Ctrl+C) when done - local work stays as before.
#
# Usage (from project root):
#   powershell -ExecutionPolicy Bypass -File .\scripts\share.ps1
#
# Optional: if Laravel is already on :8000, the script reuses it.

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

function Write-Step($message) {
    Write-Host ""
    Write-Host "==> $message" -ForegroundColor Cyan
}

# Refresh PATH so a just-installed cloudflared is found
$env:Path = [System.Environment]::GetEnvironmentVariable("Path", "Machine") + ";" +
    [System.Environment]::GetEnvironmentVariable("Path", "User")

if (Test-Path "C:\php84\php.exe") {
    $env:Path = "C:\php84;" + $env:Path
}

$cloudflared = Get-Command cloudflared -ErrorAction SilentlyContinue
if (-not $cloudflared) {
    Write-Host "cloudflared is not installed or not on PATH." -ForegroundColor Red
    Write-Host "Install with: winget install --id Cloudflare.cloudflared -e"
    Write-Host "Then close and reopen the terminal, and run this script again."
    exit 1
}

Write-Step "Building frontend assets (needed for remote visitors)"
npm run build
if ($LASTEXITCODE -ne 0) {
    throw "npm run build failed"
}

# public/hot forces the browser to Vite on localhost - remote users cannot use that.
$hotFile = Join-Path $ProjectRoot "public\hot"
$hotBackup = Join-Path $ProjectRoot "public\hot.sharebak"
$movedHot = $false
if (Test-Path $hotFile) {
    Write-Step "Temporarily disabling Vite hot file (restored when you stop sharing)"
    Move-Item -Force $hotFile $hotBackup
    $movedHot = $true
}

$startedServe = $false
$serveProcess = $null

function Test-LocalApp {
    try {
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/up" -UseBasicParsing -TimeoutSec 3
        return $response.StatusCode -eq 200
    } catch {
        return $false
    }
}

if (Test-LocalApp) {
    Write-Step "Laravel already responding on http://127.0.0.1:8000 - reusing it"
} else {
    Write-Step "Starting php artisan serve on 127.0.0.1:8000"
    $serveProcess = Start-Process -FilePath "php" -ArgumentList "artisan","serve","--host=127.0.0.1","--port=8000" -WorkingDirectory $ProjectRoot -PassThru -WindowStyle Minimized
    $startedServe = $true
    Start-Sleep -Seconds 2
    if (-not (Test-LocalApp)) {
        throw "Laravel did not start on port 8000. Start it yourself, then re-run this script."
    }
}

function Restore-ShareState {
    if ($movedHot -and (Test-Path $hotBackup)) {
        Move-Item -Force $hotBackup $hotFile
        Write-Host "Restored public/hot (Vite can work again with npm run dev)." -ForegroundColor Green
    }
    if ($startedServe -and $serveProcess -and -not $serveProcess.HasExited) {
        Stop-Process -Id $serveProcess.Id -Force -ErrorAction SilentlyContinue
        Write-Host "Stopped temporary php artisan serve." -ForegroundColor Green
    }
}

try {
    Write-Step "Opening Cloudflare quick tunnel (Ctrl+C to stop sharing)"
    Write-Host "Send the https://....trycloudflare.com URL to the other person." -ForegroundColor Yellow
    Write-Host "Demo logins:" -ForegroundColor Yellow
    Write-Host "  Admin:    /adminos/login  ->  up1 / 147235689"
    Write-Host "  Business: /              ->  mohamed@example.com / password"
    Write-Host "  Business: /              ->  fatima@example.com / password"
    Write-Host ""

    & cloudflared tunnel --url http://127.0.0.1:8000
} finally {
    Write-Step "Cleaning up share session"
    Restore-ShareState
}
