@echo off
echo Laravel E-Shop Docker Debug Tool
echo ==================================

echo.
echo 1. Checking Docker installation...
docker --version
if %ERRORLEVEL% neq 0 (
    echo ERROR: Docker is not installed or not in PATH
    pause
    exit /b 1
)

echo.
echo 2. Checking Docker Compose...
docker-compose --version
if %ERRORLEVEL% neq 0 (
    echo ERROR: Docker Compose is not installed
    pause
    exit /b 1
)

echo.
echo 3. Checking if .env file exists...
if exist .env (
    echo OK: .env file exists
) else (
    echo WARNING: .env file does not exist, will be created from .env.docker
)

echo.
echo 4. Checking container status...
docker-compose ps

echo.
echo 5. Checking Docker logs (app)...
echo --- APP LOGS ---
docker-compose logs --tail=20 app

echo.
echo 6. Checking Docker logs (db)...
echo --- DATABASE LOGS ---
docker-compose logs --tail=20 db

echo.
echo 7. Testing database connection...
docker-compose exec -T db mysql -uroot -proot_password -e "SHOW DATABASES;"
if %ERRORLEVEL% neq 0 (
    echo ERROR: Cannot connect to database
) else (
    echo OK: Database connection successful
)

echo.
echo 8. Checking Laravel status...
docker-compose exec -T app php artisan --version
if %ERRORLEVEL% neq 0 (
    echo ERROR: Laravel is not working properly
) else (
    echo OK: Laravel is working
)

echo.
echo 9. Checking file permissions...
docker-compose exec -T app ls -la storage/
docker-compose exec -T app ls -la bootstrap/cache/

echo.
echo Debug completed!
pause
