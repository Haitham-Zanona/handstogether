#!/usr/bin/env sh
set -e

echo "▶ Clearing stale caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear || true

echo "▶ Running database migrations..."
php artisan migrate --force

echo "▶ Building application caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "▶ Creating storage symlink..."
php artisan storage:link || true

echo "▶ Starting Nginx + PHP-FPM via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/app.conf
