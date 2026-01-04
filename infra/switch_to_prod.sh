#!/bin/bash
echo "🛡️ Switching to PRODUCTION Mode (Immutable)..."

# Locate .env
if [ -f "NF_facil/.env" ]; then
    cd NF_facil
fi

echo "🛑 Stopping Development Containers..."
docker compose -f infra/docker-compose.dev.yml down

echo "🏗️  Ensuring Production Build..."
docker compose --env-file .env -f infra/docker-compose.prod.yml up -d --build

echo "✅ SERVER IS NOW IN PRODUCTION MODE!"
echo "Changes require a full rebuild to take effect."
