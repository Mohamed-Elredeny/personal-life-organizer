# syntax=docker/dockerfile:1.7
# ---------- Stage 1: build assets with Vite ----------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm ci --no-audit --no-fund
COPY resources/ resources/
COPY public/ public/
RUN npm run build

# ---------- Stage 2: PHP runtime ----------
FROM serversideup/php:8.2-fpm-nginx AS runtime

# Switch to root to install deps, then drop back to www-data
USER root

ENV PHP_OPCACHE_ENABLE=1 \
    SSL_MODE=off \
    NGINX_WEBROOT=/var/www/html/public \
    PHP_OPEN_BASEDIR=""

# System deps for sqlite + intl (extensions already in the base image)
RUN install-php-extensions pdo_sqlite

# Copy app source
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
# Bring in compiled assets
COPY --chown=www-data:www-data --from=assets /app/public/build /var/www/html/public/build

# Install composer deps (production)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress \
    && composer dump-autoload --optimize \
    && chown -R www-data:www-data /var/www/html

# Ensure storage + bootstrap/cache are writable
RUN chmod -R ug+rwx storage bootstrap/cache

# Entrypoint script (migrations on boot + caches)
COPY --chmod=755 docker/entrypoint.sh /etc/entrypoint.d/99-life-os.sh

USER www-data
