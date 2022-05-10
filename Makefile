.PHONY: setup
setup:
	docker compose build && \
	docker compose run --rm composer setup

.PHONY: up
up:
	docker compose up -d php

.PHONY: test
test:
	docker compose exec php php -version && \
	docker compose exec php php-cs-fixer fix && \
	docker compose exec php ./vendor/bin/phpunit

.PHONY: prod
prod:
	docker compose exec php php -version && \
	docker compose exec php php-cs-fixer fix -vv && \
	docker compose exec php ./vendor/bin/phpunit --coverage-text

.PHONY: fix
fix:
	docker compose exec php php -version && \
	docker compose exec php php-cs-fixer fix
