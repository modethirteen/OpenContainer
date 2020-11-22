FROM php:7.4.12-cli

# dependencies
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    unzip

# php
ENV PHP_PECL_XDEBUG_VERSION=2.9.8
ENV PHP_PECL_ZIP_VERSION=1.19.1
RUN pecl install xdebug-${PHP_PECL_XDEBUG_VERSION} \
    && pecl install zip-${PHP_PECL_ZIP_VERSION} \
    && docker-php-ext-enable xdebug zip
RUN rm /usr/local/etc/php/php.ini-*
ADD development.ini /usr/local/etc/php/php.ini