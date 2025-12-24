#!/bin/bash

# Stop on error
set -e

echo "🚀 Starting Deployment..."

# 1. Pull latest changes
echo "📦 Pulling latest changes..."
git pull origin release/production

# 2. Build and Start Containers
echo "🐳 Building and Starting Containers..."
docker compose -f infra/docker-compose.prod.yml up -d --build

# 3. Wait for database to be ready (optional check, or just sleep)
echo "⏳ Waiting for services to stabilize..."
sleep 10

# 4. Run Migrations
echo "📦 Running Migrations..."
docker compose -f infra/docker-compose.prod.yml exec -T app php artisan migrate --force

# 5. Clear and Cache Config
echo "🧹 Optimizing Configuration..."
docker compose -f infra/docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f infra/docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f infra/docker-compose.prod.yml exec -T app php artisan view:cache

# 6. Set Permissions (if needed)
# docker compose -f infra/docker-compose.prod.yml exec -T app chown -R www-data:www-data storage

echo "✅ Deployment Finished Successfully!"
