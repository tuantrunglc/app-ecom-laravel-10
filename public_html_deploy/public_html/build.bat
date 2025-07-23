@echo off
echo Building Laravel E-Shop with Docker...

echo Step 1: Creating .env file from .env.docker
if not exist .env (
    copy .env.docker .env
    echo .env file created successfully
) else (
    echo .env file already exists
)

echo Step 2: Building Docker containers...
docker-compose build --no-cache
if %ERRORLEVEL% neq 0 (
    echo ERROR: Docker build failed!
    pause
    exit /b 1
)

echo Step 3: Starting containers...
docker-compose up -d
if %ERRORLEVEL% neq 0 (
    echo ERROR: Failed to start containers!
    pause
    exit /b 1
)

echo Step 4: Waiting for containers to be ready...
timeout /t 15

echo Step 5: Installing Laravel dependencies...
docker-compose exec app composer install --no-interaction
if %ERRORLEVEL% neq 0 (
    echo WARNING: Composer install failed, but continuing...
)

echo Step 5b: Building frontend assets...
docker-compose exec app npm install --legacy-peer-deps
docker-compose exec app npm run prod
if %ERRORLEVEL% neq 0 (
    echo WARNING: Asset build failed, trying dev build...
    docker-compose exec app npm run dev
)

echo Step 6: Generating application key...
docker-compose exec app php artisan key:generate --force
if %ERRORLEVEL% neq 0 (
    echo WARNING: Key generation failed, but continuing...
)

echo Step 7: Running database migrations...
docker-compose exec app php artisan migrate --force
if %ERRORLEVEL% neq 0 (
    echo WARNING: Migration failed, database might not be ready yet
)

echo Step 8: Seeding database...
docker-compose exec app php artisan db:seed --force
if %ERRORLEVEL% neq 0 (
    echo WARNING: Database seeding failed
)

echo Step 9: Creating storage link...
docker-compose exec app php artisan storage:link
if %ERRORLEVEL% neq 0 (
    echo WARNING: Storage link creation failed
)

echo Step 10: Clearing and caching configurations...
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan config:cache

echo.
echo ===================================================
echo Laravel E-Shop is now running!
echo.
echo Application: http://localhost:8000
echo phpMyAdmin: http://localhost:8080
echo.
echo Database credentials:
echo Host: localhost:3306
echo Database: eshop_db
echo Username: eshop_user
echo Password: user_password
echo Root Password: root_password
echo ===================================================
echo.

pause
