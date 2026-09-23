#!/bin/sh
set -e

cd /app

if [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

export DB_CONNECTION=sqlite
export DB_DATABASE="${DB_DATABASE:-/app/database/database.sqlite}"
export SESSION_DRIVER="${SESSION_DRIVER:-database}"

if [ -f .env ]; then
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
    sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=${SESSION_DRIVER}|" .env

    if [ -n "$APP_URL" ]; then
        sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
    fi

    # Keep PayMongo keys in .env when provided by the host (e.g. Render).
    for key in PAYMONGO_SECRET_KEY PAYMONGO_PUBLIC_KEY PAYMONGO_WEBHOOK_SECRET; do
        val=$(printenv "$key" || true)
        if [ -n "$val" ]; then
            if grep -q "^${key}=" .env; then
                sed -i "s|^${key}=.*|${key}=${val}|" .env
            else
                printf '%s=%s\n' "$key" "$val" >> .env
            fi
        fi
    done
fi

php artisan config:clear
php artisan cache:clear
php artisan view:clear

DB_PATH="${DB_DATABASE}"
mkdir -p "$(dirname "$DB_PATH")"
mkdir -p storage/framework/sessions storage/framework/cache storage/framework/views
chmod -R 775 storage bootstrap/cache database 2>/dev/null || true

if [ ! -f "$DB_PATH" ] || [ ! -s "$DB_PATH" ]; then
    touch "$DB_PATH"
    php artisan migrate:fresh --seed --force
else
    php artisan migrate --force
    php artisan db:seed --force
fi

if [ ! -e public/storage ]; then
    php artisan storage:link --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
