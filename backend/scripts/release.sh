#!/usr/bin/env sh
set -e

cd "$(dirname "$0")/.."

php artisan config:clear
php artisan migrate --force
php artisan contrackpro:ensure-passport --no-interaction

if [ "${SEED_DEFAULT_DATA:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
