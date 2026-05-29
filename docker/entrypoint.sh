#!/usr/bin/env sh
# Runs on every boot (serversideup/php image picks up scripts in /etc/entrypoint.d/)
set -e

# Make sure the volume-mounted SQLite path exists
mkdir -p /var/www/html/storage/database
if [ ! -f /var/www/html/storage/database/life-os.sqlite ]; then
    touch /var/www/html/storage/database/life-os.sqlite
fi

cd /var/www/html

# Migrate (idempotent — only runs new migrations)
php artisan migrate --force --no-interaction || true

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

# Filament-specific (cached components)
php artisan filament:cache-components || true
