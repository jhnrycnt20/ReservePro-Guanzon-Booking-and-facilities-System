#!/bin/sh
set -e

cd /app

if [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

DB_PATH="${DB_DATABASE:-/app/database/database.sqlite}"
mkdir -p "$(dirname "$DB_PATH")"

if [ ! -f "$DB_PATH" ] || [ ! -s "$DB_PATH" ]; then
    touch "$DB_PATH"
    php artisan migrate:fresh --seed --force
else
    php artisan migrate --force
    php artisan db:seed --force
fi

php artisan config:clear
php artisan cache:clear
php artisan storage:link 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
