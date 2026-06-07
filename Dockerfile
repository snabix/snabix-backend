FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        bash \
        curl \
        git \
        icu-dev \
        linux-headers \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        unzip \
    && docker-php-ext-install \
        bcmath \
        exif \
        intl \
        opcache \
        pdo_pgsql \
        pgsql \
        sockets \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
