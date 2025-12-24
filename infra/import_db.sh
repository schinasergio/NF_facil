#!/bin/bash
echo "📦 Importing Database..."

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

# Load DB Creds from .env to avoid hardcoding
# Simple grep/sed extraction or loading the file
export $(grep -v '^#' $ENV_FILE | xargs)

if [ ! -f "dump_dev.sql" ]; then
    echo "❌ Error: dump_dev.sql not found in current directory."
    exit 1
fi

echo "🔹 Importing into Database: $DB_DATABASE..."
# Use cat to pipe the file into the mysql command inside the container
cat dump_dev.sql | $COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T db mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"

if [ $? -eq 0 ]; then
    echo "✅ Import Successful!"
else
    echo "❌ Import Failed."
    exit 1
fi

echo "🔹 Re-applying 'ativo' column fix (if missing)..."
# Check if column exists, if not add it.
# We can just try adding it and ignore error, or check first.
# Safer to check.
CHECK_SQL="SELECT count(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$DB_DATABASE' AND TABLE_NAME = 'companies' AND COLUMN_NAME = 'ativo';"
HAS_ATIVO=$($COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T db mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" -sN -e "$CHECK_SQL")

if [ "$HAS_ATIVO" -eq "0" ]; then
    echo "⚠️ 'ativo' column is MISSING. Re-applying Hotfix..."
    $COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T db mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "ALTER TABLE companies ADD COLUMN ativo BOOLEAN DEFAULT 1;"
    echo "✅ Hotfix Re-applied."
else
    echo "✅ 'ativo' column exists. No fix needed."
fi

echo "🧹 Clearing Cache..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan config:clear
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan cache:clear

echo "✅ Migration Complete."
