ARG PHP_VERSION=8.0
ARG NGINX_VERSION=1.19

###################################
## PHP-FPM                       ##
### TODO: Adicionar --target dev ##
###################################

FROM php:${PHP_VERSION}-fpm-alpine AS php_fpm

RUN apk add --no-cache --update \
        acl \
        fcgi \
    ;

RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        postgresql-dev \
    ; \
    \
    docker-php-ext-configure zip; \
    docker-php-ext-install -j$(nproc) \
        intl \
        pdo_pgsql \
        zip \
    ; \
    pecl install \
        xdebug \
    ; \
    pecl clear-cache; \
    docker-php-ext-enable \
        opcache \
        xdebug \
    ; \
    \
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
    \
    apk del .build-deps; \
    php -m; \
    php --ini; \
    php --version

###########
## *.ini ##
###########

RUN set -eux; \
    ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini; \
    mkdir $PHP_INI_DIR/available-conf

COPY docker/php/conf.d/zz-misc.ini docker/php/conf.d/zz-perf-default.ini docker/php/conf.d/zz-security.ini docker/php/conf.d/zz-cidadania.ini $PHP_INI_DIR/conf.d/
COPY docker/php/available-conf/zz-perf.ini-development docker/php/available-conf/zz-perf.ini-development $PHP_INI_DIR/available-conf/

RUN ln -s $PHP_INI_DIR/available-conf/zz-perf.ini-production $PHP_INI_DIR/conf.d/zz-perf.ini

###############
## php-fpm.d ##
###############

VOLUME /var/run/php

COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/

#################
## Healthcheck ##
#################

COPY docker/php/docker-healthcheck.sh /usr/local/bin/healthcheck
RUN chmod +x /usr/local/bin/healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["healthcheck"]

################
## Entrypoint ##
################

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

##################
## Certificates ##
##################

COPY docker/cidadania.gov.crt /usr/local/share/ca-certificates/cidadania.gov.crt
RUN update-ca-certificates

##############
## Composer ##
##############

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

#############
## Symfony ##
#############

ARG APP_ENV=prod

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-dev --no-scripts --no-progress; \
    composer clear-cache

COPY .env ./
COPY bin bin/
COPY config config/
COPY migrations migrations/
COPY public public/
COPY src src/

##################################
## Desenvolvimento              ##
## TODO: Adicionar --target dev ##
##################################

COPY .env.test ./
COPY .php-cs-fixer.dist.php ./
COPY phpstan.neon.dist ./
COPY phpunit.xml.dist ./
COPY tests tests/
COPY fixtures fixtures/

ENV SYMFONY_PHPUNIT_VERSION=9

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    chmod +x bin/console bin/phpunit; sync; \
    composer run-script --no-dev post-install-cmd

VOLUME /app/var

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

###########
## Nginx ##
###########

FROM nginx:${NGINX_VERSION}-alpine AS nginx

COPY --from=php_fpm /app /app
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
