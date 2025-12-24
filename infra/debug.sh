#!/bin/bash
echo "🔍 Starting Debug Diagnostics..."

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

echo "📂 Checking Log Permissions:"
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app ls -la storage/logs/

echo "📦 Checking Migration Status:"
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan migrate:status

echo "📜 Fetching LAST 50 lines of Laravel Log (RAW):"
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app tail -n 50 storage/logs/laravel.log

echo "🐳 Fetching Container Logs (App - Last 20):"
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml logs --tail=20 app

echo "✅ Diagnostics Complete."
