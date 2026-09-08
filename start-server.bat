@echo off
title FoodHub Express - Local Server (Port 8000)
color 0A
cd /d "%~dp0"

echo ================================================================
echo    🐼 FOODHUB EXPRESS - LOCAL SERVER LAUNCHER (PORT 8000)
echo ================================================================
echo.
echo  [1/2] Opening default browser to http://localhost:8000 ...
start "" "http://localhost:8000"
echo.
echo  [2/2] Starting PHP server on port 8000...
echo        - Storefront: http://localhost:8000
echo        - Dishes Menu: http://localhost:8000/menu.php
echo        - Admin Panel: http://localhost:8000/admin
echo.
echo ================================================================
echo    Press Ctrl+C to stop the server at any time.
echo ================================================================
echo.

php -S 127.0.0.1:8000
pause
