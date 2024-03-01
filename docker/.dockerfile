FROM node:13.12.0-alpine as npm-build-stage

WORKDIR /app

#add `/app/node_modules/.bin` to $PATH
ENV PATH /app/node_modules/.bin:$PATH

COPY package*.json webpack.mix.js ./

RUN npm install

COPY resources ./resources

RUN npm run prod


FROM registry.gitlab.com/parasol-software/advplus/advplus-v2/php:8.1-nginx

RUN pecl install -o -f redis &&  \
    rm -rf /tmp/pear && \
    docker-php-ext-enable redis

COPY ./docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/nginx-uploads.conf /etc/nginx/app/uploads.conf
RUN mkdir -p /var/cache/nginx/s3-cache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY ./docker/php/php.production.ini $PHP_INI_DIR/conf.d/php.production.ini

COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY ./docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY composer.* ./
ADD parasolcrm ./parasolcrm
ADD parasolcrm-v2 ./parasolcrm-v2

RUN composer install -n --no-dev --no-cache --no-ansi --no-autoloader --no-scripts --prefer-dist

COPY . .

COPY --from=npm-build-stage /app/public /app/public

RUN composer dump-autoload -n --optimize \
    && php ./artisan storage:link

STOPSIGNAL SIGQUIT

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["serve"]
