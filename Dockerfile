# ─── Stage 1: Build Vite assets ───────────────────────────────────────────
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ─── Stage 2: Production (PHP-FPM + Nginx + Supervisor) ───────────────────
FROM php:8.3-fpm-alpine

# System packages + PHP extensions
RUN apk add --no-cache \
        nginx \
        supervisor \
        libpq \
    && apk add --no-cache --virtual .build-deps \
        libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql bcmath \
    && docker-php-ext-enable opcache \
    && apk del .build-deps \
    && mkdir -p /var/log/supervisor /run/nginx /var/log/nginx

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# PHP dependencies (layer-cached)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Application code
COPY . .

# Built assets from Stage 1
COPY --from=assets /app/public/build ./public/build

# Finalize
RUN composer run-script post-autoload-dump || true \
 && mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache/data \
 && chown -R www-data:www-data /app \
 && chmod -R 775 /app/storage /app/bootstrap/cache

COPY ./render/nginx.conf  /etc/nginx/http.d/default.conf
COPY ./render/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY ./render/start.sh    /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
