#!/bin/bash
# piGardenWeb container entrypoint: prepares runtime state, then runs Apache.
set -e
cd /var/www/html

# --- Recreate the Laravel storage skeleton (a mounted volume can be empty) ---
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    public/uploads

# --- APP_KEY: prefer the one passed via env; otherwise persist a generated one
# in the storage volume so it stays stable across container recreations ---
if [ -z "${APP_KEY:-}" ]; then
    if [ -f storage/app/.appkey ]; then
        APP_KEY="$(cat storage/app/.appkey)"
    else
        APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
        echo "$APP_KEY" > storage/app/.appkey
    fi
    export APP_KEY
fi

# --- SQLite database file (kept inside the persisted storage volume) ---
: "${DB_DATABASE:=/var/www/html/storage/app/database.sqlite}"
export DB_DATABASE
if [ ! -f "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

# --- Migrate + (re)build caches. config/view are cached; routes are NOT
# (the app has closure routes, which Laravel can't route:cache) ---
php artisan config:clear >/dev/null 2>&1 || true
php artisan migrate --force || true
php artisan storage:link 2>/dev/null || true
php artisan config:cache || true
php artisan view:cache || true

# --- Hand everything to the web user (artisan ran as root above) ---
chown -R www-data:www-data storage bootstrap/cache public/uploads || true

exec "$@"
