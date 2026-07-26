#!/usr/bin/env sh
set -e

cd /var/www/html
export COMPOSER_PROCESS_TIMEOUT="${COMPOSER_PROCESS_TIMEOUT:-1200}"

if [ ! -f ".env" ] && [ -f ".env.docker.example" ]; then
    cp .env.docker.example .env
fi

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
fi

if [ -f "artisan" ]; then
    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        php artisan key:generate --force --no-interaction
    fi

    php artisan config:clear

    if [ ! -f "storage/oauth-private.key" ] || [ ! -f "storage/oauth-public.key" ]; then
        php artisan passport:keys --force
    fi

    php artisan contrackpro:ensure-passport --no-interaction

    mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    find storage bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
    find storage bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || true

    if [ -f "storage/oauth-private.key" ]; then
        chown www-data:www-data storage/oauth-private.key 2>/dev/null || true
        chmod 600 storage/oauth-private.key 2>/dev/null || true
    fi

    if [ -f "storage/oauth-public.key" ]; then
        chown www-data:www-data storage/oauth-public.key 2>/dev/null || true
        chmod 644 storage/oauth-public.key 2>/dev/null || true
    fi
fi

exec "$@"
