#!/bin/sh
cd "$(dirname "$0")" || echo "CD failed!"
cd ../
docker build -t nikitades/toxic-avenger-bot-php-fpm:latest -f php-fpm.dockerfile .
docker push nikitades/toxic-avenger-bot-php-fpm:latest
cd deploy || echo "No deploy folder found!"
docker build -t nikitades/toxic-avenger-bot-nginx:latest -f nginx.dockerfile .
docker push nikitades/toxic-avenger-bot-nginx