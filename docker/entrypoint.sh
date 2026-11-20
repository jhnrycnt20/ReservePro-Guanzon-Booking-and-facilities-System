#!/bin/sh
set -e

cd /app

# Render injects the public HTTPS URL here. Blueprint "host" is scheme-less — never use it raw.
if [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
elif [ -n "$APP_URL" ] && ! printf '%s' "$APP_URL" | grep -Eq '^https?://'; then
    export APP_URL="https://${APP_URL}"
fi

export DB_CONNECTION=sqlite
export DB_DATABASE="${DB_DATABASE:-/app/database/database.sqlite}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export SESSION_SECURE_COOKIE="${SESSION_SECURE_COOKIE:-true}"
export SESSION_SAME_SITE="${SESSION_SAME_SITE:-lax}"

if [ -f .env ]; then
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
    sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=${SESSION_DRIVER}|" .env

    if [ -n "$APP_URL" ]; then
        # Escape sed replacement specials (& and \).
        safe_url=$(printf '%s' "$APP_URL" | sed 's/[&\\]/\\&/g')
        sed -i "s|^APP_URL=.*|APP_URL=${safe_url}|" .env
    fi

    # Never sed-replace APP_KEY (can corrupt base64). Laravel reads it from the process env.
    # Keep timezone + session cookie flags + PayMongo keys in .env when provided by the host.
    for key in APP_TIMEZONE SESSION_SECURE_COOKIE SESSION_SAME_SITE PAYMONGO_SECRET_KEY PAYMONGO_PUBLIC_KEY PAYMONGO_WEBHOOK_SECRET; do
        val=$(printenv "$key" || true)
        if [ -n "$val" ]; then
            safe_val=$(printf '%s' "$val" | sed 's/[&\\]/\\&/g')
            if grep -q "^${key}=" .env; then
                sed -i "s|^${key}=.*|${key}=${safe_val}|" .env
            else
                printf '%s=%s\n' "$key" "$val" >> .env
            fi
        fi
    done

    # Default resort timezone if unset (avoids UTC hiding same-day Check-in).
    if ! grep -q '^APP_TIMEZONE=' .env; then
        printf 'APP_TIMEZONE=Asia/Manila\n' >> .env
    fi

    # Sync mail settings from the host. Never keep mailpit SMTP on Render (causes 500 on password reset).
    for key in MAIL_MAILER MAIL_HOST MAIL_PORT MAIL_USERNAME MAIL_PASSWORD MAIL_ENCRYPTION MAIL_FROM_ADDRESS MAIL_FROM_NAME; do
        val=$(printenv "$key" || true)
        if [ -n "$val" ]; then
            safe_val=$(printf '%s' "$val" | sed 's/[&\\]/\\&/g')
            if grep -q "^${key}=" .env; then
                sed -i "s|^${key}=.*|${key}=${safe_val}|" .env
            else
                printf '%s=%s\n' "$key" "$val" >> .env
            fi
        fi
    done

    # Only strip legacy Mailpit hosts. Real Gmail SMTP comes from Render env vars.
    if grep -Eq '^MAIL_HOST=mailpit' .env; then
        sed -i 's/^MAIL_MAILER=.*/MAIL_MAILER=log/' .env
        sed -i 's/^MAIL_HOST=.*/MAIL_HOST=127.0.0.1/' .env
    fi
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
