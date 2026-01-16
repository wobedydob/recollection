#!/bin/bash

# Release script for Recollection (Laravel)
# Creates a deployment package for production

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "Building release package..."
echo ""

# 1. Install production dependencies
echo "Installing production dependencies..."
php /etc/php/composer/composer2.phar install --no-dev --optimize-autoloader

# 2. Create release directory
echo ""
echo "Creating release package..."
rm -rf .release
mkdir -p .release

# Copy Laravel application files
cp -r app .release/
cp -r bootstrap .release/
cp -r config .release/
cp -r database .release/
cp -r public .release/
cp -r resources .release/
cp -r routes .release/
cp -r vendor .release/

# Create storage structure
mkdir -p .release/storage/app/public
mkdir -p .release/storage/framework/cache/data
mkdir -p .release/storage/framework/sessions
mkdir -p .release/storage/framework/views
mkdir -p .release/storage/logs
touch .release/storage/logs/.gitkeep

# Copy essential files
cp artisan .release/
cp composer.json .release/
cp composer.lock .release/

# Copy production .env
if [ -f ".env.production" ]; then
    cp .env.production .release/.env
    echo "Included .env.production as .env"
else
    cp .env.example .release/.env.example
    echo "WARNING: No .env.production found - create .env on server"
fi

echo ""
echo "Release package created in .release/"
echo ""
du -sh .release/
echo ""
echo "=== DEPLOYMENT ==="
echo "1. Upload .release/ contents to your server"
echo "2. Set document root to 'public/' folder"
echo "3. Run: php artisan migrate"
echo "4. Run: php artisan key:generate"
echo "5. Set permissions: chmod -R 775 storage bootstrap/cache"
echo ""
