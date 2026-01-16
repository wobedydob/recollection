#!/bin/bash

# Build script for Recollection
# This builds the Nuxt frontend and copies it to the public folder

set -e

echo "📦 Building frontend..."
cd frontend
npm run generate
cd ..

echo "🗑️  Cleaning public folder..."
rm -rf public/*

echo "📋 Copying built files to public..."
cp -r frontend/.output/public/* public/

echo "✅ Build complete! The public folder is ready to serve."
