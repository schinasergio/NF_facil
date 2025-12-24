#!/bin/bash

# Stop on error
set -e

echo "🚀 Starting Server Setup..."

# 1. Update and Install Dependencies
echo "📦 Installing Dependencies (Docker, Git)..."
# Assuming Debian/Ubuntu
sudo apt-get update
sudo apt-get install -y git docker.io
# Enable Docker
sudo systemctl enable --now docker
sudo usermod -aG docker $USER

# Install Docker Compose Plugin (if not present)
if ! docker compose version > /dev/null 2>&1; then
    sudo apt-get install -y docker-compose-v2 || sudo apt-get install -y docker-compose
fi

echo "✅ Dependencies Installed."

# 2. Clone Repository
REPO_DIR="NF_facil"
REPO_URL="git@github.com:schinasergio/NF_facil.git"

if [ -d "$REPO_DIR" ]; then
    echo "📂 Repository exists. Pulling latest..."
    cd $REPO_DIR
    git pull origin release/production
else
    echo "📂 cloning repository..."
    # Ensure StrictHostKeyChecking is loose for first connect or let user handle it?
    # Better to just clone.
    git clone $REPO_URL $REPO_DIR
    cd $REPO_DIR
    git checkout release/production
fi

# 3. Setup Environment Variables
# Check if .env is a directory (bad state from Docker volume mount error) and remove it
if [ -d ".env" ]; then
    echo "⚠️  Found directory named .env. Removing..."
    rm -rf .env
fi

if [ ! -f ".env" ]; then
    echo "⚙️ Creating .env with Production defaults..."
    cp .env.example .env
fi

# Inject Production Values (Run always to ensure config is correct)
echo "🔧 Configuring .env for Production..."
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
# Enable lines if commented
sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=db/' .env
sed -i 's/# DB_PORT=3306/DB_PORT=3306/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=nffacil/' .env
sed -i 's/# DB_USERNAME=root/DB_USERNAME=nffacil/' .env
sed -i 's/# DB_PASSWORD=/DB_PASSWORD=secret/' .env
# Update existing values if not comented
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/' .env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=nffacil/' .env

sed -i 's/CACHE_DRIVER=file/CACHE_DRIVER=redis/' .env
sed -i 's/SESSION_DRIVER=file/SESSION_DRIVER=redis/' .env
sed -i 's/QUEUE_CONNECTION=sync/QUEUE_CONNECTION=redis/' .env
sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' .env

# 4. Trigger Deployment
echo "🚀 Triggering Deploy Script..."
# Ensure docker group permissions apply
if command -v sg > /dev/null 2>&1; then
    sg docker -c "bash infra/deploy.sh"
else
    # Fallback if sg is missing (rare), try direct run
    bash infra/deploy.sh
fi
