FROM php:8.1-apache
LABEL maintainer="Katelyn Gigante"

RUN mkdir -p /var/www/html/
WORKDIR /var/www/html/

RUN apt-get update && apt-get install -y git zip unzip

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

COPY composer.* ./

RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN php composer.phar --version && php composer.phar install

# Let us upload larger files than 2M so that we can install cardsets from MTGJSON
RUN printf 'upload_max_filesize = 128M\n' >>/usr/local/etc/php/conf.d/uploads.ini

ENV LOG_STDOUT true
ENV LOG_STDERR true
ENV LOG_LEVEL debug
