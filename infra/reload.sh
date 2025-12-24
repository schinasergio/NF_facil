#!/bin/bash
echo "🔄 Reloading Application..."

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

# Locate .env
if [ -f ".env" ]; then
    ENV_FILE=".env"
elif [ -f "NF_facil/.env" ]; then
    cd NF_facil
    ENV_FILE=".env"
else
    echo "❌ Error: .env file not found."
    exit 1
fi

echo "🔹 Recreating Containers..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml up -d --force-recreate

echo "🧹 Clearing Caches..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:clear
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan cache:clear

echo "✅ Reload Complete."
