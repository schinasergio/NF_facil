#!/bin/bash
# Fetch the last occurrence of SQLSTATE error and surrounding lines
echo "🔍 Extracting Last SQL Error..."
# Use docker compose to exec grep inside the container
docker compose --env-file .env -f infra/docker-compose.prod.yml exec -T app grep -C 5 "SQLSTATE" storage/logs/laravel.log | tail -n 20
