#!/bin/bash
echo "🔧 Fixing Migration Conflict..."

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
fi

echo "🔹 Dropping conflicting 'nfses' table via Tinker..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan tinker --execute="Schema::dropIfExists('nfses');"

echo "🔹 Running Migrations..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan migrate --force

echo "🔹 Seeding Test Customer..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan db:seed --class=TestCustomerSeeder

echo "✅ All Done!"
