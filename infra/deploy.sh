#!/bin/bash

# Stop on error
set -e

echo "🚀 Starting Deployment..."

# 1. Pull latest changes
echo "📦 Pulling latest changes..."
git pull origin release/production

# Detect Docker Compose
COMPOSE_CMD=""
if docker compose version > /dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
elif command -v docker-compose > /dev/null 2>&1; then
    COMPOSE_CMD="docker-compose"
else
    echo "❌ Error: Docker Compose not found (tried 'docker compose' and 'docker-compose')."
    exit 1
fi

echo "🔹 Using Compose command: $COMPOSE_CMD"

# 2. Build and Start Containers
echo "🐳 Building and Starting Containers..."
$COMPOSE_CMD -f infra/docker-compose.prod.yml up -d --build

# 3. Wait for database to be ready (DietPi/Pi4 might be slow)
echo "⏳ Waiting 30s for services to stabilize..."
sleep 30

# Ensure App Key Exists
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=" .env | grep -v "base64"; then
    echo "🔑 Generating Application Key..."
    $COMPOSE_CMD -f infra/docker-compose.prod.yml exec -T app php artisan key:generate
fi

# 4. Run Migrations
echo "📦 Running Migrations..."
$COMPOSE_CMD -f infra/docker-compose.prod.yml exec -T app php artisan migrate --force

# 5. Clear and Cache Config
echo "🧹 Optimizing Configuration..."
$COMPOSE_CMD -f infra/docker-compose.prod.yml exec -T app php artisan config:cache
$COMPOSE_CMD -f infra/docker-compose.prod.yml exec -T app php artisan route:cache
$COMPOSE_CMD -f infra/docker-compose.prod.yml exec -T app php artisan view:cache

# 6. Set Permissions (if needed)
# $COMPOSE_CMD -f infra/docker-compose.prod.yml exec -T app chown -R www-data:www-data storage

echo "✅ Deployment Finished Successfully!"
