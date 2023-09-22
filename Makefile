.PHONY: install
install:
	bash .docker/install.sh

.PHONY: down
down:
	docker-compose down

.PHONY: stop
stop:
	docker-compose stop

.PHONY: build
build:
	docker-compose build

.PHONY: composer-install
composer-install:
	docker-compose exec app composer install

.PHONY: up
up:
	docker-compose up -d app db dbtest redis

.PHONY: migrate
migrate:
	docker-compose exec app php artisan migrate

.PHONY: tests
tests:
	docker-compose exec app env composer tests

.PHONY: bash
bash:
	docker-compose exec app bash
