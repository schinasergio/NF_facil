#!/bin/bash
echo "🔧 Fixing permissions for storage directory..."

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

echo "🔹 Using Compose command: $COMPOSE_CMD"

# Fix permissions inside the container
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml exec -T app chown -R www-data:www-data /var/www/html/storage
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml exec -T app chmod -R 775 /var/www/html/storage

echo "✅ Permissions fixed."
