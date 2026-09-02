#!/bin/sh
set -e

cd /app

if [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

export DB_CONNECTION=sqlite
export DB_DATABASE="${DB_DATABASE:-/app/database/database.sqlite}"

if [ -f .env ]; then
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
fi

php artisan config:clear
php artisan cache:clear

DB_PATH="${DB_DATABASE}"
mkdir -p "$(dirname "$DB_PATH")"

if [ ! -f "$DB_PATH" ] || [ ! -s "$DB_PATH" ]; then
    touch "$DB_PATH"
    php artisan migrate:fresh --seed --force
else
    php artisan migrate --force
fi

php artisan db:seed --force

php artisan storage:link 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
