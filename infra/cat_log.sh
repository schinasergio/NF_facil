#!/bin/bash
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

$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app tail -n 50 storage/logs/laravel.log
