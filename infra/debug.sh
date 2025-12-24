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

echo "📂 Listing Storage Logs Directory permissions:"
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml exec -T app ls -la storage/logs/

echo "📜 Fetching last 100 lines of Laravel Log:"
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml exec -T app tail -n 100 storage/logs/laravel.log

echo "🐳 Fetching Container Logs (App):"
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml logs --tail=50 app

echo "✅ Diagnostics Complete."
