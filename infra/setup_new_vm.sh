#!/bin/bash
set -e

echo "🚀 Setting up NF_Facil DEV Environment on Debian VM..."

# 1. Install Dependencies
echo "📦 Installing Dependencies..."
if ! command -v git &> /dev/null; then
    sudo apt-get update && sudo apt-get install -y git
fi

# 2. Add user to Docker Group (to avoid sudo)
echo "🐳 Configuring Docker Permissions..."
if ! groups sergin | grep -q docker; then
    sudo usermod -aG docker sergin
    echo "⚠️  User added to docker group. You may need to logout and login again for this to take effect."
else
    echo "✅ User already in docker group."
fi

# 3. Clone/Update Repository
DIR="NF_facil"
if [ -d "$DIR" ]; then
    echo "📂 Updating Repository..."
    cd $DIR
    git pull origin release/production
else
    echo "📂 Cloning Repository..."
    git clone https://github.com/schinasergio/NF_facil.git $DIR
    cd $DIR
    git checkout release/production
fi

# 4. Setup .env for DEV
echo "⚙️  Configuring .env for local/dev..."
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Force Dev Variables
sed -i 's/^APP_ENV=.*/APP_ENV=local/' .env
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=true/' .env
sed -i 's/^DB_HOST=.*/DB_HOST=db/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=nffacil/' .env
# Remove any 'ativo' hotfix reference if it exists in env, though it might be in migration
# We will use the existing docker-compose.dev.yml which mounts volumes

# 5. Start Containers
echo "🚀 Starting Containers..."
# We might need sudo here if the group change hasn't taken effect in this session
# But let's try.
if groups | grep -q docker; then
    docker compose --env-file .env -f infra/docker-compose.dev.yml up -d --build
else
    echo "⚠️  Running with SUDO as group change requires re-login..."
    sudo docker compose --env-file .env -f infra/docker-compose.dev.yml up -d --build
fi

echo "✅ Deployment Scripts Finished!"
