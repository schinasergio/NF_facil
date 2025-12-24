#!/bin/bash
echo "🔑 Correcting Malformed Application Key..."

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

echo "🔹 Current Key (Corrupted):"
grep "APP_KEY" $ENV_FILE

echo "🔹 Wiping APP_KEY..."
# Replace any line starting with APP_KEY= with just APP_KEY=
sed -i 's/^APP_KEY=.*/APP_KEY=/' $ENV_FILE

echo "🔹 Generating Fresh Key..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan key:generate --force

echo "🔹 Verifying New Key:"
grep "APP_KEY" $ENV_FILE

echo "🧹 Clearing Caches..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:cache
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan route:cache

echo "✅ App Key Fixed. Testing connectivity..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app curl -I http://localhost || echo "⚠️ Could not curl localhost inside container"
