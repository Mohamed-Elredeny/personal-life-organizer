# syntax=docker/dockerfile:1.7
# ---------- Stage 1: PHP deps ----------
FROM dunglas/frankenphp:1-php8.2-alpine AS composer_deps
RUN apk add --no-cache git
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --no-scripts --no-autoloader \
    --ignore-platform-req=ext-intl

# ---------- Stage 2: Build assets with Vite ----------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js tailwind.config.js postcss.config.js ./
RUN npm ci --no-audit --no-fund
COPY resources/ resources/
COPY public/ public/
COPY --from=composer_deps /app/vendor /app/vendor
RUN npm run build

# ---------- Stage 3: Runtime (FrankenPHP) ----------
FROM dunglas/frankenphp:1-php8.2-alpine AS runtime

# Required PHP extensions for Laravel + Filament
RUN install-php-extensions pdo_sqlite intl gd zip bcmath opcache

# Composer in runtime so dump-autoload + post-install scripts work
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# App source
COPY . .
COPY --from=composer_deps /app/vendor /app/vendor
COPY --from=assets /app/public/build /app/public/build

# Clear any stale cache, finalize autoload, run post-install
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php \
    && composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && composer run-script post-install-cmd --no-dev \
    && mkdir -p storage/database storage/framework/{sessions,views,cache/data} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# FrankenPHP serves Laravel out of /app/public on port 8080 by default
ENV SERVER_NAME=:8080

EXPOSE 8080

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
