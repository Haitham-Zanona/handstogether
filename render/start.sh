#!/usr/bin/env sh
set -e

echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Caching Laravel config..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Creating storage link..."
php artisan storage:link || true

echo "Running migrations..."
php artisan migrate --force

echo "Starting PHP-FPM & Nginx..."
/opt/docker/bin/service.d/nginx/run &
/opt/docker/bin/service.d/php-fpm/run
