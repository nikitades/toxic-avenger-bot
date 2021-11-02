#!/bin/bash

./wait-for-it.sh database:5432 -t 15
/app/bin/console d:m:m --no-interaction --allow-no-migration

php-fpm -F
