#!/bin/sh
set -e

# ENV SYMFONY_PHPUNIT_VERSION=9

# Se esse container for chamado com -f ou --foo-bar, então executamos o php-fpm
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

if [ "$1" = "php-fpm" ] || [ "$1" = "php" ] || [ "$1" = "bin/console" ]; then
    echo "APP_ENV: $APP_ENV"

    PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
    PERF_INI_RECOMMENDED="$PHP_INI_DIR/available-conf/zz-perf.ini-production"

    if [ "$APP_ENV" != "prod" ]; then
        PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
        PERF_INI_RECOMMENDED="$PHP_INI_DIR/zz-perf.ini-development"
    fi

    ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"
    ln -sf "$PERF_INI_RECOMMENDED" "$PHP_INI_DIR/zz-perf.ini"

    mkdir -p var/cache var/log
    setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
    setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

    if [ "$APP_ENV" != "prod" ]; then
        composer install --prefer-dist --no-progress --no-interaction
        composer clear-cache
    fi

    if grep -q DATABASE_URL .env; then
        echo "Aguardando banco de dados estar acessível..."

        ATTEMPTS_LEFT_TO_REACH_DATABASE=30
        until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
            if [ $? -eq 255 ]; then
                ATTEMPTS_LEFT_TO_REACH_DATABASE=0
                break
            fi

            sleep 1
            ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
            echo -e "Ainda aguardando banco de dados ficar acessível\n$ATTEMPTS_LEFT_TO_REACH_DATABASE tentativas restantes"
        done

        if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
            echo "O banco não subiu ou não é alcançável:"
            echo "$DATABASE_ERROR"
            exit 1
        else
            echo "Banco de dados está acessível"
        fi

        if ls -A migrations/*.php >/dev/null 2>&1; then
            php bin/console doctrine:migrations:migrate --no-interaction
        fi
    fi

    if [ "$APP_ENV" == "prod" ]; then
        rm -rf \
            .env.test \
            .php-cs-fixer.dist.php \
            phpunit.xml.dist \
            phpstan.neon.dist \
            tests \
            fixtures \
            /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
            $(php-config --extension-dir)/xdebug.so \
        ;
    fi
fi

exec docker-php-entrypoint "$@"
