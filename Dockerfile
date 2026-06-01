# ─── Stage 1: Build Vite assets ───────────────────────────────────────────
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ─── Stage 2: Production PHP + Nginx ──────────────────────────────────────
FROM webdevops/php-nginx:8.3-alpine

# PostgreSQL PHP extension
RUN apk add --no-cache libpq-dev \
 && docker-php-ext-install pdo_pgsql pgsql \
 && apk del libpq-dev

WORKDIR /app

# Install PHP dependencies (cached layer — only rebuilds when composer files change)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

# Copy built assets from Stage 1 (not in git, so must come from build)
COPY --from=assets /app/public/build ./public/build

# Run post-install scripts + fix permissions
RUN composer run-script post-autoload-dump || true \
 && chown -R application:application /app \
 && chmod -R 775 /app/storage /app/bootstrap/cache

COPY ./render/nginx.conf /opt/docker/etc/nginx/vhost.conf
COPY ./render/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
