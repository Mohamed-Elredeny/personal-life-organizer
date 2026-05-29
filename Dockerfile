# syntax=docker/dockerfile:1.7
# ---------- Stage 1: PHP deps (composer install -> vendor/) ----------
FROM serversideup/php:8.2-cli AS composer_deps
USER root
RUN install-php-extensions pdo_sqlite intl
WORKDIR /app
# Copy only files needed by composer to maximize layer cache
COPY composer.json composer.lock ./
# Install without scripts (post-install needs artisan + full source); run scripts in runtime stage
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts --no-autoloader

# ---------- Stage 2: Build assets with Vite ----------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js tailwind.config.js postcss.config.js ./
RUN npm ci --no-audit --no-fund
COPY resources/ resources/
COPY public/ public/
# Bring in vendor so Filament theme's @import to vendor/filament/.../theme.css resolves
COPY --from=composer_deps /app/vendor /app/vendor
RUN npm run build

# ---------- Stage 3: PHP runtime ----------
FROM serversideup/php:8.2-fpm-nginx AS runtime

USER root

ENV PHP_OPCACHE_ENABLE=1 \
    SSL_MODE=off \
    PHP_OPEN_BASEDIR=""

RUN install-php-extensions pdo_sqlite intl

WORKDIR /var/www/html

# App source
COPY --chown=www-data:www-data . .
# Composer deps from stage 1
COPY --chown=www-data:www-data --from=composer_deps /app/vendor /var/www/html/vendor
# Compiled assets from stage 2
COPY --chown=www-data:www-data --from=assets /app/public/build /var/www/html/public/build

# Clear any stale cached package manifest (could reference removed packages)
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php

# Finalize autoloader + run post-install (publishes Filament translations)
RUN composer dump-autoload --optimize --classmap-authoritative \
    && composer run-script post-install-cmd --no-dev \
    && chown -R www-data:www-data /var/www/html

# Pre-create the volume mount target so chown works before the volume mounts
RUN mkdir -p /var/www/html/storage/database \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R ug+rwx storage bootstrap/cache

# Migrations + caches run in fly.toml [deploy] release_command, not at boot —
# the serversideup/php image starts nginx + php-fpm via s6-overlay as PID 1.
