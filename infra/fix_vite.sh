#!/bin/bash
echo "🎨 Fixing Vite Manifest Error..."

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

echo "🔹 Creating dummy manifest.json with entries..."
# Create directory
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app mkdir -p public/build/assets

# Create dummy asset files
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app touch public/build/assets/app.css
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app touch public/build/assets/app.js

# Create manifest with mapping
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app sh -c "cat > public/build/manifest.json <<EOF
{
  \"resources/css/app.css\": {
    \"file\": \"assets/app.css\",
    \"src\": \"resources/css/app.css\"
  },
  \"resources/js/app.js\": {
    \"file\": \"assets/app.js\",
    \"src\": \"resources/js/app.js\"
  }
}
EOF"

echo "🧹 Clearing Caches..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan view:clear

echo "✅ Vite Fix Applied. (Assets might be missing, but 500 error should be gone)"
