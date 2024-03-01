FROM registry.gitlab.com/parasol-software/advplus/advplus-v2/php:8.1-nginx

RUN pecl install -o -f redis &&  \
    rm -rf /tmp/pear && \
    docker-php-ext-enable redis

RUN apk add --update linux-headers \
    && pecl install xdebug

COPY ./docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/nginx-uploads.conf /etc/nginx/app/uploads.conf
RUN mkdir -p /var/cache/nginx/s3-cache

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ./docker/dev/php.development.ini $PHP_INI_DIR/conf.d/php.development.ini

COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY ./docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
COPY ./docker/dev/wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/wait-for-db.sh

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY . .

RUN rm -rf ./vendor/parasol-software
RUN rm -rf ./vendor/parasol-software2

RUN composer install

RUN php ./artisan storage:link

STOPSIGNAL SIGQUIT

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["serve"]
