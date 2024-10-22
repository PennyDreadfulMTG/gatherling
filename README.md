# Gatherling

A website for running Magic: the Gathering tournaments.

## Local Setup

- Install PHP, MariaDB, Composer, php-curl, php-mysqli, php-pdo, php-pod_mysql, php-simplexml.
- $ sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root mysql
- $ cp config.php.example config.php # Edit the variables needed. Use these values in the SQL below.
- $ sudo mysql -u root
- mysql> CREATE USER 'gatherling'@'localhost' IDENTIFIED BY 'Pa$$w0rD';
- mysql> GRANT ALL ON `gatherling`.* TO 'gatherling'@'localhost';
- mysql> GRANT ALL ON `gatherling_test`.* TO 'gatherling'@'localhost';
- $ composer install
- $ php gatherling/admin/db-upgrade.php
- $ ln -s gatherling/gatherling /path/to/your/web/root

## Docker Setup

- Install Docker.
- $ cp config.php.docker config.php
- $ docker-compose up
- $ docker exec -it gatherling-web-1 sh -c "cd /var/www && php gatherling/admin/db-upgrade.php"
This brings up the website on ports 80 and 81, mysql on port 3307, and adminer on port 8080.
You can change the ports in docker-composer.yml.

## Dev

- Run `composer qa` to run tests, lint, both static type checkers and javasscript tests.

## Tests

- $ composer test
or with a dockerized setup:
- $ docker exec -it gatherling-web-1 sh -c "cd /var/www && vendor/bin/phpunit tests"

## Lint
$ composer lint # phpcs
$ composer autofix # phpcbf
$ composer static # phpstan and psalm

## JavaScript Tests

- Install bun and run `bun install`.
- $ bun test
