#!/usr/bin/env sh
set -eu

FPM_USER="${FPM_USER:-www-data}"
FPM_GROUP="${FPM_USER:-www-data}"

getent group "${FPM_GROUP}" &>/dev/null || addgroup "${FPM_GROUP}" -g "${FPM_GROUP}"
id -u "${FPM_USER}" &>/dev/null || adduser "${FPM_USER}" -D -G "${FPM_GROUP}" -u "${FPM_USER}"

chown -R "${FPM_USER}:${FPM_GROUP}" /app/storage

if [ "$1" = 'serve' ] || [ "$1" = 'queue' ] || [ "$1" = 'schedule' ]; then
    php artisan optimize
    php artisan view:cache
fi

if [ "$1" = 'serve' ]; then
    #FPM USER SET
    FPM_CONFIG_PATH="${FPM_CONFIG_PATH:-/usr/local/etc/php-fpm.conf}"
    NGINX_UPLOADS_PROXY_HOST="${NGINX_UPLOADS_PROXY_HOST:-}"
    sed -i "s#%FPM_USER%#${FPM_USER}#g" "$FPM_CONFIG_PATH"
    sed -i "s#%FPM_GROUP%#${FPM_GROUP}#g" "$FPM_CONFIG_PATH"

    chown -R www-data:www-data /var/lib/nginx

    if [ -n "$NGINX_UPLOADS_PROXY_HOST" ]; then
        sed -i "s#\$NGINX_UPLOADS_PROXY_HOST#${NGINX_UPLOADS_PROXY_HOST}#g" "/etc/nginx/app/uploads.conf"
    else
        echo "" >/etc/nginx/app/uploads.conf
    fi

    echo 'Running server mode...'
    echo 'Starting FRM...'
    php-fpm -DO
    echo 'Starting Nginx...'
    exec nginx -g "daemon off;"
fi

if [ "$1" = 'queue' ]; then
    exec su "$FPM_USER" -c "php /app/artisan queue:work --memory=512 --sleep=1 --queue=high,default,low"
fi
if [ "$1" = 'schedule' ]; then
    echo "*/1 * * * * su ${FPM_USER} -c \"php /app/artisan schedule:run\"" >/etc/supercronic/laravel
    exec supercronic /etc/supercronic/laravel # it runs artisan schedule:run
fi

exec "$@"
