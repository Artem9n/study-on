COMPOSE=docker-compose
PHP=$(COMPOSE) exec php
CONSOLE=$(PHP) bin/console
COMPOSER=$(PHP) composer

up:
	@${COMPOSE} up -d

down:
	@${COMPOSE} down

clear:
	@${CONSOLE} cache:clear

migration:
	@${CONSOLE} make:migration

migrate:
	@${CONSOLE} doctrine:migrations:migrate

fixtload:
	@${CONSOLE} doctrine:fixtures:load

crud:
	@${CONSOLE} make:crud

encore_dev:
	@${COMPOSE} run --rm node yarn encore dev

encore_prod:
	@${COMPOSE} run --rm node yarn encore production

phpunit:
	@${PHP} bin/phpunit

require:
	@${COMPOSER} require $2

# В файл local.mk можно добавлять дополнительные make-команды,
# которые требуются лично вам, но не нужны на проекте в целом
-include local.mk
