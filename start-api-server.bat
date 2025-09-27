@echo off
echo Starting API-only server on port 8001...
echo API Server: http://localhost:8001
echo API Health: http://localhost:8001/api/health
echo API Auth: http://localhost:8001/api/auth/user
echo.
echo Press Ctrl+C to stop the server
echo.

php artisan serve --host=0.0.0.0 --port=8001