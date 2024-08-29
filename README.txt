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

Tests:
$ vendor/bin/phpunit -v tests/

Lint:
$ vendor/bin/phpcs --standard=PSR12 --runtime-set testVersion 8.1 --ignore=vendor .
$ vendor/bin/phpcbf --standard=PSR12 --runtime-set testVersion 8.1 --ignore=vendor . # autofix
