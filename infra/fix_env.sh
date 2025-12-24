#!/bin/bash
set -e
echo "🔧 Fixing .env configuration in $(pwd)..."

# 1. Fix .env file
if [ -d ".env" ]; then
    echo "⚠️  .env is a DIRECTORY. Removing..."
    rm -rf .env
fi

if [ ! -f ".env" ]; then
    echo "📄 Creating .env from example..."
    cp .env.example .env
fi

# Ensure it's a file now
if [ -d ".env" ]; then
    echo "❌ Failed to remove .env directory."
    exit 1
fi

echo "📝 Configuring production values..."
# Apply critical production settings
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
# Fix lines that might be commented or uncommented
sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=db/' .env
sed -i 's/^DB_HOST=127.0.0.1/DB_HOST=db/' .env
sed -i 's/# DB_PORT=3306/DB_PORT=3306/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=nffacil/' .env
sed -i 's/^DB_DATABASE=laravel/DB_DATABASE=nffacil/' .env
sed -i 's/# DB_USERNAME=root/DB_USERNAME=nffacil/' .env
sed -i 's/# DB_PASSWORD=/DB_PASSWORD=secret/' .env
sed -i 's/^CACHE_DRIVER=file/CACHE_DRIVER=redis/' .env
sed -i 's/^SESSION_DRIVER=file/SESSION_DRIVER=redis/' .env
sed -i 's/^QUEUE_CONNECTION=sync/QUEUE_CONNECTION=redis/' .env
sed -i 's/^REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' .env

echo "✅ .env fixed. Restarting containers..."

# Detect Compose
COMPOSE_CMD=""
if docker compose version > /dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
elif command -v docker-compose > /dev/null 2>&1; then
    COMPOSE_CMD="docker-compose"
else
    echo "❌ Error: Docker Compose not found."
    exit 1
fi

# Stop everything first to ensure mounts release
$COMPOSE_CMD -f infra/docker-compose.prod.yml down --remove-orphans || true

# Start up
echo "🚀 Starting up..."
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml up -d --build

# Generate key if missing
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=" .env | grep -v "base64"; then
    echo "🔑 Generating Application Key..."
    $COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml exec -T app php artisan key:generate
fi

echo "🧹 Clearing caches..."
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml exec -T app php artisan config:cache
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml exec -T app php artisan route:cache

echo "🚀 Restart complete. Try accessing the site now."
