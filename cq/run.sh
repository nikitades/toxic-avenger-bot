#!/bin/sh
vendor/bin/phpstan analyse src tests --configuration phpstan.neon && vendor/bin/phpunit tests