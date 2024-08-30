Gatherling

An online MTG website for running tournaments.

Setup:
- Install PHP, MariaDB, Composer, php-curl, php-mysqli, php-simplexml.
- sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root mysql
- Copy config.php.example to config.php and fill in the variables needed.
- Run `composer install`
- Visit `/admin/db-upgrade.php`
- Sign up a player by visiting register.php.
- Visit /util/updateDefaultFormats.php

Dev Setup:
You can set up in the same way as above or you may prefer to use a dockerized instance.
- Install Docker.
- `cp config.php.docker config.php`
- `docker-compose up`

This brings up the website on ports 80 and 81, mysql on port 3307, and phpmyadmin on port 8080.
You can change the ports in docker-composer.yml.

- Visit http://localhost/admin/db-upgrade.php to initialize the database.

Tests:
$ vendor/bin/phpunit -v tests/

Lint:
$ vendor/bin/phpcs --standard=PSR12 --runtime-set testVersion 8.1 --ignore=vendor .
$ vendor/bin/phpcbf --standard=PSR12 --runtime-set testVersion 8.1 --ignore=vendor . # autofix
