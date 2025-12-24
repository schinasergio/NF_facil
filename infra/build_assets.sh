#!/bin/bash
echo "🏗️ Building Assets on Server..."

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

echo "🔹 Installing Node.js & NPM (Temporary)..."
# Using apt-get since php-fpm is usually Debian-based
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T -u root app apt-get update
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T -u root app apt-get install -y nodejs npm

echo "🔹 Verifying Node Version..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app node -v
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app npm -v

echo "📦 Installing JS Dependencies..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app npm install --no-audit --no-fund

echo "🔨 Building Production Assets..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app npm run build

echo "🧹 Cleaning up (Partial)..."
# We keep node installed for potential future usage or debugging, can be removed later.
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan view:clear

echo "✅ Assets Built Successfully!"
