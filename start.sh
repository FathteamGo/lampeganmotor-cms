#!/bin/bash
# Start script for Production (Coolify/Nixpacks)

echo "Starting application in production mode..."

# Clear Laravel caches to ensure fresh start
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations automatically (optional, but safe for production usually)
php artisan migrate --force

# Start the built-in PHP web server (or FrankenPHP / Octane if you prefer)
# Note: Coolify expects the app to run on the configured port, usually 8000 for PHP
echo "Starting Laravel server on port 8000..."
php artisan serve --host=0.0.0.0 --port=8000
