@echo off
set "DOCKER_PATH=C:\Program Files\Docker\Docker\resources\bin\docker.exe"
set "PATH=%PATH%;C:\Program Files\Docker\Docker\resources\bin"

echo Check if Docker is running...
"%DOCKER_PATH%" info >nul 2>&1
if %errorlevel% neq 0 (
    echo Docker is not running or not reachable.
    echo attempting to use path: %DOCKER_PATH%
    echo Please start Docker Desktop and try again.
    pause
    exit /b
)

echo Building containers...
cd infra
"%DOCKER_PATH%" compose -f docker-compose.dev.yml up -d --build

echo Waiting for database to initialize (10s)...
timeout /t 10

echo Installing Laravel...
"%DOCKER_PATH%" compose -f docker-compose.dev.yml exec app composer create-project laravel/laravel . --force

echo Generating App Key...
"%DOCKER_PATH%" compose -f docker-compose.dev.yml exec app php artisan key:generate

echo Fixing permissions...
"%DOCKER_PATH%" compose -f docker-compose.dev.yml exec app chmod -R 777 storage bootstrap/cache

echo Installation complete! Access http://localhost:8080
pause
