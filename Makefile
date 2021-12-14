# Usage
# make - compile and push all the images

.DEFAULT_GOAL := build

build: builx_install composer build_amd64 prune
build_dev: builx_install composer build_amd64

builx_install:
	docker run --privileged --rm tonistiigi/binfmt --install all

composer:
	composer install
	bin/console cache:warmup

build_amd64: build_php build_nginx

build_php:
	docker buildx build -t nikitades/toxicavenger-app:latest -f docker/web.dockerfile --platform linux/amd64 --push .

build_nginx:
	docker buildx build -t nikitades/toxicavenger-nginx:latest -f docker/nginx.dockerfile --platform linux/amd64 --push .

prune:
	docker system prune -af

down:
	docker-compose -f docker-compose.yml down

up:
	docker-compose -f docker-compose.yml up -d

rebuild_and_restart: down build_dev up

restart: down up

logs:
	docker-compose -f docker-compose.yml logs -f