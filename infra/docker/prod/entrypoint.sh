#!/bin/sh
set -e

cd /var/www/html

# Ensure runtime-writable directories exist with expected permissions.
mkdir -p \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

# On Render, default to running migrations unless explicitly disabled.
run_migrations="${RUN_MIGRATIONS:-}"
if [ -n "${PORT:-}" ] && [ -z "$run_migrations" ]; then
  run_migrations="true"
fi

if [ "$run_migrations" = "true" ]; then
  php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Render expects an HTTP server bound to 0.0.0.0:$PORT.
if [ -n "${PORT:-}" ]; then
  exec php artisan serve --host=0.0.0.0 --port="$PORT"
fi

exec php-fpm
