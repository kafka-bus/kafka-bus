# Образ для воспроизводимых бенчмарков: PHP 8.2 + ext-rdkafka, без Xdebug.
# Контекст сборки — корень монорепозитория (path-repositories тянут packages/*).
FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip librdkafka-dev \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && docker-php-ext-install opcache \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN { \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.jit=off'; \
        echo 'memory_limit=-1'; \
    } > /usr/local/etc/php/conf.d/benchmarks.ini

WORKDIR /app
COPY . .
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader

ENTRYPOINT ["vendor/bin/phpbench", "run", "--report=kafka-bus"]
CMD ["--dump-file=benchmarks/results/latest.xml"]
