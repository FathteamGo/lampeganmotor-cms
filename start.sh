#!/bin/bash
set -e

echo "Starting Lampegan Motor CMS locally..."

# Start database container
echo "Starting database..."
docker-compose up -d mysql

# Wait for MySQL to be ready
echo "Waiting for MySQL to boot..."
sleep 5

# Clear caches
echo "Clearing caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Migrate
echo "Running migrations..."
php artisan migrate --force

# Start Queue (background)
echo "Starting queue worker..."
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 > /dev/null 2>&1 &
echo $! > storage/framework/queue.pid

# Start server (background)
echo "Starting web server on http://localhost:8000 ..."
php artisan serve --host=0.0.0.0 --port=8000 > /dev/null 2>&1 &
echo $! > storage/framework/server.pid

echo "Lampegan Motor CMS started successfully. Web server running in background."
