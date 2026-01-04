#!/bin/bash
echo "🔄 Switching to DEVELOPMENT Mode (Hot Reload)..."

# Locate .env
if [ -f "NF_facil/.env" ]; then
    cd NF_facil
fi

echo "🛑 Stopping Production Containers..."
docker compose -f infra/docker-compose.prod.yml down

echo "🚀 Starting Development Containers..."
# We use the same image, but override with dev compose file for volumes
docker compose --env-file .env -f infra/docker-compose.dev.yml up -d

echo "✅ SERVER IS NOW IN DEV MODE!"
echo "Files in app/, resources/, routes/ are now synced directly."
