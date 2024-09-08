FROM php:8.1-apache as compose
WORKDIR /restore
COPY composer.* ./
RUN apt-get update && apt-get install -y git zip unzip

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN php composer.phar --version && php composer.phar install

FROM php:8.1-apache
LABEL maintainer="Katelyn Gigante"
RUN apt-get update && apt-get install -y git zip unzip

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# We need to mount more than just the web stuff we we'll mount everything
# (except vendor) at /var/www and have the web root be our gatherling dir.
RUN sed -i 's|/var/www/html|/var/www/gatherling|' /etc/apache2/sites-available/000-default.conf

# We already did composer install in the compose stage, so we can copy that over
COPY --from=compose /restore/vendor /var/www/vendor

# Let us upload larger files than 2M so that we can install cardsets from MTGJSON
RUN printf 'upload_max_filesize = 128M\n' >>/usr/local/etc/php/conf.d/uploads.ini

ENV LOG_STDOUT true
ENV LOG_STDERR true
ENV LOG_LEVEL debug
