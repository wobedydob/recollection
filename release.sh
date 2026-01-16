#!/bin/bash

# Release script for Recollection
# Builds the frontend with .env.strato (if available) and packages everything for deployment

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

# Check if .env.strato exists and use it for the build
ENV_SWAPPED=false
if [ -f ".env.strato" ]; then
    echo "Found .env.strato - using STRATO environment for build"

    # Backup current .env if it exists
    if [ -f ".env" ]; then
        mv .env .env.backup
    fi

    # Use .env.strato as .env for the build
    cp .env.strato .env
    ENV_SWAPPED=true
else
    echo "No .env.strato found - using default .env"
fi

# Run the build
echo ""
./build.sh

# Restore original .env if we swapped it
if [ "$ENV_SWAPPED" = true ]; then
    rm .env
    if [ -f ".env.backup" ]; then
        mv .env.backup .env
    fi
fi

# Create release directory
echo ""
echo "Creating release package..."
rm -rf .release
mkdir -p .release

# Copy API files
cp -r api .release/

# Copy public folder (built frontend)
cp -r public .release/

# Copy root files
cp .htaccess .release/
cp index.php .release/

# Copy .env.strato as .env for the release (or .env.example as template)
if [ -f ".env.strato" ]; then
    cp .env.strato .release/.env
    echo "Included .env.strato as .env in release"
else
    cp .env.example .release/.env.example
    echo "Included .env.example - remember to create .env on the server"
fi

echo ""
echo "Release package created in .release/"
echo ""
echo "Contents:"
ls -la .release/
echo ""
echo "Upload the contents of .release/ to your STRATO hosting root."
