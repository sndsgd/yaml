SHELL := /usr/bin/env bash
CWD := $(shell pwd)

DOCKER_BIN ?= $(shell command -v docker 2>/dev/null)

ifeq ($(shell [ -t 0 ] && echo 1),1)
	DOCKER_DEFAULT_OPTIONS ?= -it --rm
else
	DOCKER_DEFAULT_OPTIONS ?= --rm
endif

ifeq ($(shell uname),Linux)
	DOCKER_RUN_USER := -u $(shell id -u):$(shell id -g)
else
	DOCKER_RUN_USER :=
endif

PHP_VERSION ?= 8.2
IMAGE_NAME ?= ghcr.io/sndsgd/php
IMAGE ?= $(IMAGE_NAME):$(PHP_VERSION)
DOCKER_RUN ?= $(DOCKER_BIN) run \
	$(DOCKER_DEFAULT_OPTIONS) \
	$(DOCKER_RUN_USER) \
	--volume $(CWD):$(CWD) \
	--workdir $(CWD) \
	$(IMAGE)

.PHONY: help
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	| awk 'BEGIN {FS = ":.*?## "}; {printf "\033[33m%s\033[0m~%s\n", $$1, $$2}' \
	| column -s "~" -t

.PHONY: prepare-build-directory
prepare-build-directory:
	@rm -rf $(CWD)/build && mkdir $(CWD)/build

###############################################################################
# composer ####################################################################
###############################################################################

COMPOSER_ARGS ?= --help
.PHONY: composer
composer: ## Run an arbitrary composer command
composer:
	$(DOCKER_RUN) /bin/composer $(COMPOSER_ARGS)

.PHONY: composer-install
composer-install: ## Install dependencies
composer-install: override COMPOSER_ARGS = install --no-cache
composer-install: composer

.PHONY: composer-update
composer-update: ## Update dependencies
composer-update: override COMPOSER_ARGS = update --no-cache
composer-update: composer

###############################################################################
# lint ########################################################################
###############################################################################

PHPLINT_ARGS ?= --help
.PHONY: phplint
phplint:
	$(DOCKER_RUN) vendor/bin/parallel-lint $(PHPLINT_ARGS)

.PHONY: lint
lint: override PHPLINT_ARGS = src tests
lint: phplint

###############################################################################
# coding standards ############################################################
###############################################################################

PHPCS_ARGS ?= --help
.PHONY: phpcs
phpcs: lint
	$(DOCKER_RUN) vendor/bin/phpcs $(PHPCS_ARGS)

.PHONY: cs
cs: ## Run coding standards checks
cs: override PHPCS_ARGS = --standard=phpcs.xml src tests
cs: phpcs

PHPCBF_ARGS ?= --help
.PHONY: phpcbf
phpcbf: lint
	$(DOCKER_RUN) vendor/bin/phpcbf $(PHPCBF_ARGS)

.PHONY: cs-fix
cs-fix: ## Run coding standards checks
cs-fix: override PHPCBF_ARGS = --standard=phpcs.xml -p -v src tests
cs-fix: phpcbf

###############################################################################
# static analysis #############################################################
###############################################################################

PHPSTAN_ARGS ?= --help
.PHONY: phpstan
phpstan:
	$(DOCKER_RUN) vendor/bin/phpstan $(PHPSTAN_ARGS)

.PHONY: analyze
analyze: ## Run static analysis checks
analyze: override PHPSTAN_ARGS = analyze --configuration phpstan.neon
analyze: phpstan

###############################################################################
# unit tests ##################################################################
###############################################################################

PHPUNIT_ARGS ?= --help
.PHONY: phpunit
phpunit: lint prepare-build-directory
	$(DOCKER_RUN) vendor/bin/phpunit $(PHPUNIT_ARGS)

.PHONY: test
test: ## Run unit tests
test: override PHPUNIT_ARGS = --do-not-cache-result --no-coverage
test: phpunit

.PHONY: test-coverage
test-coverage: ## Run unit tests and generate coverage report
test-coverage: override PHPUNIT_ARGS = --do-not-cache-result
test-coverage: phpunit

.DEFAULT_GOAL := help
