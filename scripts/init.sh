#!/bin/bash

# Step 0: Ensure .env file exists
if [ ! -f .env ]; then
  echo "📄 Creating .env from .env.example..."
  cp .env.example .env
else
  echo "📄 .env already exists — skipping copy"
fi

# Step 1: Build and start Docker containers
echo "🚀 Building and starting Docker containers..."
docker-compose up -d --build

# Step 2: Install Composer dependencies inside container
echo "📦 Installing Composer dependencies..."
docker exec base_backend_app composer install

# Step 3: Generate Laravel app key
echo "🔐 Generating app key..."
docker exec base_backend_app php artisan key:generate

# Step 4: Run migrations
echo "🧬 Running migrations..."
docker exec base_backend_app php artisan migrate

# Step 5: Storage link (optional)
echo "🔗 Linking storage folder..."
docker exec base_backend_app php artisan storage:link

# Step 6: Jump into the container
echo "🐳 Entering app container..."
docker exec -it base_backend_app bash
