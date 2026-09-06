@echo off
title FoodHub Express - Local Server (Port 8001)
color 0A
cd /d "%~dp0"

echo ================================================================
echo    🐼 FOODHUB EXPRESS - LOCAL SERVER LAUNCHER
echo ================================================================
echo.
echo  [1/2] Launching PHP server on port 8001...
echo        - Local URL: http://localhost:8001
echo        - Admin URL: http://localhost:8001/admin
echo        - Menu URL:  http://localhost:8001/menu.php
echo.
echo  [2/2] Opening browser in 2 seconds...
echo.
echo ================================================================
echo    Press Ctrl+C at any time to stop the server.
echo ================================================================
echo.

start "" "http://localhost:8001"
php -S 127.0.0.1:8001
pause
