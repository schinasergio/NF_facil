#!/bin/bash
echo "🐞 Enabling Debug Mode..."

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

echo "🔹 Setting APP_DEBUG=true..."
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=true/' $ENV_FILE

echo "🔹 Verifying .env:"
grep "APP_DEBUG" $ENV_FILE

echo "箒 Clearing Caches..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:clear
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:cache

echo "✅ Debug Mode Enabled."
