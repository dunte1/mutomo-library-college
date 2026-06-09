#!/bin/bash
set -e

if [ ! -f .env ]; then
    echo "ERROR: .env file not found. Copy .env.example to .env and configure it."
    exit 1
fi

php artisan key:generate --force --quiet 2>/dev/null || true
php artisan storage:link --force --quiet 2>/dev/null || true

if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache --quiet
    php artisan route:cache --quiet
    php artisan view:cache --quiet
    php artisan event:cache --quiet
fi

php artisan migrate --force --quiet

echo "Starting OLLMCHS Library System..."

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
