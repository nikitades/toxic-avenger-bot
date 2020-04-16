##This file has to be on the root depth level due to dockerfile restrictions
FROM php:7.4-fpm-alpine

COPY . /var/www/html

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"