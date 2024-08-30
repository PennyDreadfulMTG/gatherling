Gatherling

An online MTG website for running tournaments.

Normal Setup:
- Install PHP, MariaDB, Composer, php-curl, php-mysqli, php-simplexml.
- sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root mysql
- Copy config.php.example to config.php and fill in the variables needed.
- Run `composer install`
- Now proceed to Shared Setup below.


Docker Setup:
You can set up in the same way as above or you may prefer to use a dockerized instance.
- Install Docker.
- `cp config.php.docker config.php`
- `docker-compose up`

This brings up the website on ports 80 and 81, mysql on port 3307, and adminer on port 8080.
You can change the ports in docker-composer.yml.

- Now proceed to Shared Setup below

Shared Setup:
(for docker wrap these in `docker exec -it gatherling-web-1 sh -c "cd /var/repo && {cmd}"`)
- php gatherling/admin/db-upgrade.php
- php gatherling/util/insertcardset.php M10
- php gatherling/util/insertcardset.php ELD
- php gatherling/util/updateDefaultFormats.php
- Sign up a player by visiting register.php.

Tests:
$ vendor/bin/phpunit -v tests/
or with a dockerized setup:
$ docker exec -it gatherling-web-1 sh -c "cd /var/repo && vendor/bin/phpunit tests"

Lint:
$ vendor/bin/phpcs --standard=PSR12 --runtime-set testVersion 8.1 --ignore=vendor .
$ vendor/bin/phpcbf --standard=PSR12 --runtime-set testVersion 8.1 --ignore=vendor . # autofix
