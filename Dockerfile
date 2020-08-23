FROM php:7.2-apache as compose
WORKDIR /restore
COPY composer.* ./
RUN apt-get update && \
    apt-get install -y git zip unzip

RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN php composer.phar install


FROM php:7.2-apache
LABEL maintainer="Katelyn Gigante"

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN mkdir -p /var/www/html/
WORKDIR /var/www/html/
COPY ./gatherling /var/www/html/
COPY --from=compose /restore/vendor /var/www/html/vendor
RUN php admin/db-upgrade.php
RUN php util/scryfallsync.php
RUN php util/updateDefaultFormats.php

## Expose used ports
EXPOSE 80

ENV LOG_STDOUT true
ENV LOG_STDERR true
ENV LOG_LEVEL debug
