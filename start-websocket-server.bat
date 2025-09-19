@echo off
echo Starting Laravel Chat App WebSocket Server...
echo.

REM Check if PHP is available
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP and add it to your system PATH
    pause
    exit /b 1
)

REM Check if we're in the correct directory
if not exist "artisan" (
    echo ERROR: artisan file not found
    echo Please run this script from the Laravel project root directory
    pause
    exit /b 1
)

REM Set environment variables for WebSocket server
set REVERB_SERVER_HOST=0.0.0.0
set REVERB_SERVER_PORT=8080

echo Configuration:
echo - Host: %REVERB_SERVER_HOST%
echo - Port: %REVERB_SERVER_PORT%
echo - Environment: %APP_ENV%
echo.

echo Starting WebSocket server...
echo Press Ctrl+C to stop the server
echo.

REM Start the WebSocket server
php artisan reverb:start --host=%REVERB_SERVER_HOST% --port=%REVERB_SERVER_PORT%

echo.
echo WebSocket server stopped.
pause
