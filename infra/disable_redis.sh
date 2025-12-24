#!/bin/bash
echo "🔌 Disabling Redis (Falling back to File/Sync)..."

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

echo "🔹 Reverting Drivers..."
sed -i 's/^CACHE_DRIVER=.*/CACHE_DRIVER=file/' $ENV_FILE
sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' $ENV_FILE
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' $ENV_FILE

echo "🔹 Verifying .env:"
grep "CACHE_DRIVER" $ENV_FILE
grep "SESSION_DRIVER" $ENV_FILE

echo "🧹 Clearing Caches..."
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

$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:clear
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:cache

echo "✅ Redis Disabled. Drivers set to File/Sync."
