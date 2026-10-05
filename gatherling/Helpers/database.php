<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use Gatherling\Data\Db;
use Gatherling\Exceptions\ConfigurationException;

// This needs a separate file because Gatherling\Data\Db uses some of the other helpers
// and we don't want an infinite dependency loop.

function databaseHostname(bool $test = false): string
{
    $hostname = config()->string('db_hostname', '127.0.0.1');
    if ($test) {
        $hostname = config()->string('db_test_hostname', $hostname);
    }
    if ($hostname == "") {
        throw new ConfigurationException('Hostname can\'t be empty');
    }
    return $hostname;
}

function databaseName(bool $test = false): string
{
    if ($test) {
        $name = config()->string('db_test_database');
    } else {
        $name = config()->string('db_database');
    }
    if ($name == "") {
        throw new ConfigurationException('Database name can\'t be empty');
    }
    return $name;
}

function databasePassword(bool $test = false): string
{
    $password = config()->string('db_password');
    if ($test) {
        $password = config()->string('db_test_password', $password);
    }
    return $password;
}

function databasePort(bool $test = false): int
{
    $port = config()->int('db_port', 3306);
    if ($test) {
        $port = config()->int('db_test_port', $port);
    }
    if ($port < 1 || $port > 65535) {
        throw new ConfigurationException('Database port must be between 1 and 65535');
    }
    return $port;
}

function databaseUsername(bool $test = false): string
{
    $username = config()->string('db_username');
    if ($test) {
        $username = config()->string('db_test_username', $username);
    }
    if ($username == "") {
        throw new ConfigurationException('Username can\'t be empty');
    }
    return $username;
}

function db(): Db
{
    static $db;

    if (!$db) {
        $db = new Db();
    }

    return $db;
}
