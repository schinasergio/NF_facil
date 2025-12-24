#!/bin/bash
echo "🔑 Fixing Application Key..."

# Detect Docker Compose
COMPOSE_CMD=""
if docker compose version > /dev/null 2>&1; then
    COMPOSE_CMD="docker compose"
elif command -v docker-compose > /dev/null 2>&1; then
    COMPOSE_CMD="docker-compose"
else
    echo "❌ Error: Docker Compose not found."
    exit 1
fi

echo "🔹 Using Compose command: $COMPOSE_CMD"

# Ensure we are in the right directory or find .env
if [ -f ".env" ]; then
    ENV_FILE=".env"
elif [ -f "NF_facil/.env" ]; then
    cd NF_facil
    ENV_FILE=".env"
else
    echo "❌ Error: .env file not found."
    exit 1
fi

echo "🔹 Checking current key status in .env..."
grep "APP_KEY" .env

echo "🔹 Generating Key (Force)..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan key:generate --force

echo "🔹 Verifying key in .env..."
grep "APP_KEY" .env

echo "🧹 Clearing Caches..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:clear
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:cache
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan route:cache
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan view:cache

echo "✅ Key Generated and Cache Cleared!"
