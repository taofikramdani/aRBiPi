#!/bin/sh
set -e

if [ -d /shared-public ]; then
    cp -a /var/www/html/public/. /shared-public/
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

exec "$@"
