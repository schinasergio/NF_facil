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

echo "🔹 Using Compose command: $COMPOSE_CMD"

# Fetch the last occurrence of SQLSTATE error and surrounding lines
echo "🔍 Extracting Last SQL Error..."
# Use docker compose to exec grep inside the container
$COMPOSE_CMD --env-file .env -f infra/docker-compose.prod.yml exec -T app grep -C 5 "SQLSTATE" storage/logs/laravel.log | tail -n 20
