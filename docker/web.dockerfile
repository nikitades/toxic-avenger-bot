FROM php:8-fpm

RUN DEBIAN_FRONTEND=noninteractive apt update && apt install libpq-dev -y
RUN docker-php-ext-install pdo pdo_pgsql pgsql pcntl

RUN adduser --disabled-password --gecos '' toxicavenger

COPY . /app
COPY docker/app-entrypoint.sh /app/entrypoint.sh
COPY docker/wait-for-it.sh /app/wait-for-it.sh
WORKDIR /app

RUN chown -R toxicavenger:toxicavenger . && chmod -R 755 .
RUN bin/console cache:warmup

USER toxicavenger

ENTRYPOINT ["/app/entrypoint.sh"]