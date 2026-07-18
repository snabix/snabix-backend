ARG PHP_IMAGE=php:8.3-fpm-alpine3.24
ARG COMPOSER_IMAGE=composer:2.8.12
ARG CADDY_IMAGE=caddy:2.11.4-alpine

FROM ${COMPOSER_IMAGE} AS composer
FROM ${CADDY_IMAGE} AS caddy

FROM ${PHP_IMAGE} AS php-base

RUN apk add --no-cache \
        curl \
        icu-libs \
        libpq \
        libzip \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        linux-headers \
        oniguruma-dev \
        postgresql-dev \
    && docker-php-ext-install -j"$(getconf _NPROCESSORS_ONLN)" \
        bcmath \
        exif \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        sockets \
        zip \
    && if ! php -m | grep -qi '^redis$'; then \
        pecl install redis \
        && docker-php-ext-enable redis; \
    fi \
    && apk del .build-deps \
    && addgroup -S -g 10001 snabix \
    && adduser -S -D -H -u 10001 -G snabix snabix

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY .docker/php/production.ini /usr/local/etc/php/conf.d/zz-snabix-production.ini
COPY .docker/php-fpm/zz-production.conf /usr/local/etc/php-fpm.d/zz-snabix-production.conf

WORKDIR /var/www/html

FROM php-base AS vendor

COPY . .

RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --classmap-authoritative \
    && rm -rf \
        .git \
        node_modules \
        storage/logs/* \
        storage/framework/cache/data/* \
        storage/framework/sessions/* \
        storage/framework/views/*

FROM php-base AS runtime

ARG APP_REVISION=unknown
ARG IMAGE_SOURCE=https://github.com/snabix/snabix-backend

LABEL org.opencontainers.image.title="Snabix Backend" \
      org.opencontainers.image.revision="${APP_REVISION}" \
      org.opencontainers.image.source="${IMAGE_SOURCE}"

ENV APP_REVISION="${APP_REVISION}"

COPY --from=caddy /usr/bin/caddy /usr/local/bin/caddy
COPY --from=vendor --chown=snabix:snabix /var/www/html /var/www/html
COPY --chown=root:root .docker/bin/container-entrypoint /usr/local/bin/container-entrypoint
COPY --chown=root:root .docker/bin/healthcheck /usr/local/bin/snabix-healthcheck

RUN chmod 0755 /usr/local/bin/container-entrypoint /usr/local/bin/snabix-healthcheck \
    && mkdir -p \
        bootstrap/cache \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R snabix:snabix bootstrap/cache storage

USER 10001:10001

EXPOSE 9000 8080

ENTRYPOINT ["container-entrypoint"]
CMD ["php-fpm", "--nodaemonize"]
