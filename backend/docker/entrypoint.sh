#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f ".env" ] && [ -f ".env.docker.example" ]; then
    cp .env.docker.example .env
fi

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ -f "artisan" ]; then
    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        php artisan key:generate --force --no-interaction
    fi

    php artisan config:clear
fi

exec "$@"
