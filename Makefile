.PHONY: install test lint build clean test-core test-symfony test-all lint-core lint-symfony lint-all install-core install-symfony test-filter

install: install-core install-symfony

install-core:
	composer install

install-symfony:
	cd packages/symfony-bundle && composer install

test: test-all

test-all: test-core test-symfony

test-core:
	vendor/bin/phpunit tests

test-symfony:
	cd packages/symfony-bundle && vendor/bin/phpunit

test-filter:
ifndef FILTER
	@echo "Error: FILTER parameter required"
	@echo "Usage: make test-filter FILTER=testMethodName"
	@exit 1
endif
	vendor/bin/phpunit --filter $(FILTER)

lint: lint-all

lint-all: lint-core lint-symfony

lint-core:
	vendor/bin/phpstan analyze src --level=5

lint-symfony:
	cd packages/symfony-bundle && vendor/bin/phpstan analyze src --level=5

build: lint-all test-all

build-core: lint-core test-core

build-symfony: lint-symfony test-symfony

clean:
	rm -rf vendor
	rm -rf packages/symfony-bundle/vendor
	rm -rf .phpunit.cache
	rm -rf packages/symfony-bundle/.phpunit.cache
	rm -f .phpunit.result.cache
	rm -f packages/symfony-bundle/.phpunit.result.cache
