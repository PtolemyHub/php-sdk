PHP_DOCKER_TAG=0.0.1
PHP_IMAGE_NAME=ptolemy-docker:$(PHP_DOCKER_TAG)

USER_OPTION=-u $(shell id -u):$(shell id -g)
SRC_VOLUME_OPTION=-v $(shell pwd):/usr/src

docker-build:
	@docker build -t $(PHP_IMAGE_NAME) -f docker/php/Dockerfile .

php-bash:
	@docker run --rm -it $(SRC_VOLUME_OPTION) -v ~/.composer:/.composer $(USER_OPTION) $(PHP_IMAGE_NAME) bash

phar-build:
	@docker run --rm $(SRC_VOLUME_OPTION) $(USER_OPTION) ryderone/docker-box-project:3.11.1 box compile

phar-run:
	@docker run --rm -it $(SRC_VOLUME_OPTION) $(USER_OPTION) $(PHP_IMAGE_NAME) ./build/ptolemy-php.phar map