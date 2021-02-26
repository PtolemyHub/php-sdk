PHP_IMAGE_NAME=chrishautenne/php:7.4.15

USER_OPTION=-u $(shell id -u):$(shell id -g)
SRC_VOLUME_OPTION=-v $(shell pwd):/usr/src
EXPERIMENT_DIRECTORY_VOLUME_OPTION=$(shell pwd)/../kulla-dev/symfony/kulla/src

php-bash:
	@docker run --rm -it $(SRC_VOLUME_OPTION) -v ~/.composer:/.composer $(USER_OPTION) $(PHP_IMAGE_NAME) bash

phar-build:
	@docker run --rm $(SRC_VOLUME_OPTION) $(USER_OPTION) ryderone/docker-box-project:3.11.1 box compile

phar-run:
	@docker run --rm -it $(SRC_VOLUME_OPTION) $(USER_OPTION) $(PHP_IMAGE_NAME) ./build/ptolemy-php.phar map

phar-copy:
	@cp build/ptolemy-php.phar ../kulla-dev/symfony/bin/ptolemy-php

ptolemy-bash:
	@docker run --rm -it $(SRC_VOLUME_OPTION) -v $(EXPERIMENT_DIRECTORY_VOLUME_OPTION):/var/app $(USER_OPTION) $(PHP_IMAGE_NAME) bash

ptolemy-map:
	@docker run --rm -it $(SRC_VOLUME_OPTION) -v $(EXPERIMENT_DIRECTORY_VOLUME_OPTION):/var/app $(USER_OPTION) $(PHP_IMAGE_NAME) ./ptolemy-php map /var/app