#!/bin/sh
set -e

cd /var/www/html

# Ensure runtime-writable directories exist with expected permissions.
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php-fpm
