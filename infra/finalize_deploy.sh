#!/bin/bash
echo "🚀 Finalizing Deployment..."

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

echo "🔹 Generating Key string (Container side)..."
# Generate key string using PHP inside container, no ANSI codes, just the string
NEW_KEY=$($COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php -r "echo 'base64:'.base64_encode(random_bytes(32));")

echo "🔑 Generated Key: $NEW_KEY"

if [ -z "$NEW_KEY" ]; then
    echo "❌ Failed to generate key."
    exit 1
fi

echo "🔹 Writing Key to HOST .env..."
# Use sed on the HOST to write to the file
sed -i "s|^APP_KEY=.*|APP_KEY=$NEW_KEY|" $ENV_FILE

echo "🔹 Verifying .env content:"
grep "APP_KEY" $ENV_FILE

echo "🔄 Restarting Containers (Full Reload)..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml down
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml up -d

echo "🧹 Clearing Caches (Post-Restart)..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:cache
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan route:cache
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan view:cache

echo "✅ Finalize Script Complete."
