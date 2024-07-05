.PHONY: setup
setup:
	docker compose build && \
	docker compose run --rm composer setup

.PHONY: up
up:
	docker compose up -d php

.PHONY: test
test:
	docker compose run --rm php php -version && \
	docker compose run --rm php php-cs-fixer fix && \
	docker compose run --rm php ./vendor/bin/phpunit

.PHONY: prod
prod:
	docker compose run --rm php php -version && \
	docker compose run --rm php php-cs-fixer fix -vv && \
	docker compose run --rm php ./vendor/bin/phpunit --coverage-text

.PHONY: fix
fix:
	docker compose run --rm php php -version && \
	docker compose run --rm php php-cs-fixer fix
