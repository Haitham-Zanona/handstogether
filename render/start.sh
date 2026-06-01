#!/usr/bin/env sh
set -e

echo "▶ Clearing stale caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "▶ Running database migrations..."
php artisan migrate --force

echo "▶ Building application caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "▶ Creating storage symlink..."
php artisan storage:link || true

echo "▶ Starting Nginx + PHP-FPM..."
/opt/docker/bin/service.d/nginx/run &
/opt/docker/bin/service.d/php-fpm/run
