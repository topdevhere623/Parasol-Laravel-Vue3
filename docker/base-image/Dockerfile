FROM php:8.1-fpm-alpine

WORKDIR /image-build

# Add Build Dependencies
RUN apk add --no-cache --virtual .build-deps  \
    zlib-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libxml2-dev \
    bzip2-dev \
    zip \
    libzip-dev \
    tzdata \
    g++ \
    imagemagick \
    imagemagick-dev \
    autoconf \
    make \
    nginx

# Add Production Dependencies
RUN apk add --update --no-cache \
    jpegoptim \
    pngquant \
    optipng \
    nano \
    icu-dev \
    freetype-dev

# Configure & Install Extension
RUN docker-php-ext-configure \
    opcache --enable-opcache &&\
    docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/ && \
    docker-php-ext-install \
    mysqli \
    pdo_mysql \
    intl \
    gd \
    xml \
    bz2 \
    pcntl \
    bcmath \
    zip && \
    pecl install imagick

RUN docker-php-ext-enable imagick
RUN docker-php-ext-configure exif
RUN docker-php-ext-install exif
RUN docker-php-ext-enable exif


# Using Nginx and configs https://github.com/h5bp/server-configs-nginx for passqueries to nginx unit
ENV NGIX_SCFG_VER=4.2.0
ENV NGIX_SCFG_URL="https://github.com/h5bp/server-configs-nginx/archive/refs/tags/$NGIX_SCFG_VER.tar.gz" \
    NGIX_SCFG_URL_SHA1SUM=075fc7116e40a27b76d68cd42dbf379186a19bb7

RUN curl -fsSLO "$NGIX_SCFG_URL" \
    && echo "${NGIX_SCFG_URL_SHA1SUM}  $NGIX_SCFG_VER.tar.gz" | sha1sum -c - \
    && tar -xzf $NGIX_SCFG_VER.tar.gz \
    && cp /etc/nginx/fastcgi_params server-configs-nginx-$NGIX_SCFG_VER/ \
    && rm -rf /etc/nginx/* \
    && mv server-configs-nginx-$NGIX_SCFG_VER/* /etc/nginx/ \
    && rm -rf "$NGIX_SCFG_VER.tar.gz server-configs-nginx-$NGIX_SCFG_VER" /etc/nginx/conf.d/*

RUN mkdir -p /run/nginx




# Using https://github.com/aptible/supercronic for laravel schedule
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.1.12/supercronic-linux-amd64 \
    SUPERCRONIC=supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=048b95b48b708983effb2e5c935a1ef8483d9e3e

RUN curl -fsSLO "$SUPERCRONIC_URL" \
    && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
    && chmod +x "$SUPERCRONIC" \
    && mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" \
    && ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic \
    && mkdir /etc/supercronic \
    && echo '*/1 * * * * php /app/artisan schedule:run' > /etc/supercronic/laravel

WORKDIR /app

RUN rm -rf /image-build

EXPOSE 80
