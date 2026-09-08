# FoodHub Express - PowerShell Server Launcher (Port 8000)
$ErrorActionPreference = "Continue"
Set-Location -Path $PSScriptRoot

Write-Host "================================================================" -ForegroundColor Magenta
Write-Host "   🐼 FOODHUB EXPRESS - LOCAL SERVER LAUNCHER (PORT 8000)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Magenta
Write-Host ""
Write-Host " [1/2] Opening default browser to http://localhost:8000 ..." -ForegroundColor Cyan
Start-Process "http://localhost:8000"

Write-Host " [2/2] Starting PHP server on 127.0.0.1:8000 ..." -ForegroundColor Green
Write-Host "       - Storefront: http://localhost:8000" -ForegroundColor Gray
Write-Host "       - Menu:       http://localhost:8000/menu.php" -ForegroundColor Gray
Write-Host "       - Admin:      http://localhost:8000/admin" -ForegroundColor Gray
Write-Host ""
Write-Host "Press Ctrl+C to terminate server." -ForegroundColor Yellow

php -S 127.0.0.1:8000
