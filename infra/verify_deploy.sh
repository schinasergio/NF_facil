#!/bin/bash
echo "🔍 Verifying Deployment..."

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

echo "🔹 1. Ensuring Database Migrations are Updated..."
$COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan migrate --force

echo "🔹 2. Seeding Test Data (Idempotent check)..."
# We run seed again; Eloquent create might duplicate if not careful, but our seeder uses create.
# To be safe, let's just count them first.
COUNT=$($COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan tinker --execute="echo App\Models\Customer::where('razao_social', 'like', '%TESTE%')->count();")

echo "   Found $COUNT test customers."

if [ "$COUNT" -lt "2" ]; then
    echo "   ⚠️ Less than 2 test customers found. Running Seeder..."
    $COMPOSE_CMD --env-file $ENV_FILE -f infra/docker-compose.prod.yml exec -T app php artisan db:seed --class=TestCustomerSeeder
else
    echo "   ✅ Test Customers already exist."
fi

echo "✅ Verification Complete."
