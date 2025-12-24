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
if [ ! -f ".env" ]; then
    echo "⚙️ Creating .env from .env.example..."
    cp .env.example .env.production
    # We might need to inject the production values here if we had them secure
    # For now, we rely on the user or the committed .env (which shouldn't be there)
    # OR we assume .env.production is what we want.
fi

# 4. Trigger Deployment
echo "🚀 Triggering Deploy Script..."
# Ensure docker group permissions apply
if command -v sg > /dev/null 2>&1; then
    sg docker -c "bash infra/deploy.sh"
else
    # Fallback if sg is missing (rare), try direct run
    bash infra/deploy.sh
fi
