FROM composer:2.0.9 AS composer

FROM php:7.4

ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update -qq && apt-get install -qqy \
    wget \
    git \
    apt-utils \
    openssl \
    nano \
    htop \
    unzip \
    apt-transport-https lsb-release ca-certificates \
    software-properties-common \
    libzip-dev \
    && echo 'alias ll="ls -lah --color=auto"' >> /etc/bash.bashrc \
    && docker-php-ext-install zip

WORKDIR /usr/src