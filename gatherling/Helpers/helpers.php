<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use DateTimeZone;
use Gatherling\Logger;
use Safe\DateTimeImmutable;

function request(): Request
{
    return new Request($_REQUEST);
}

function get(): Request
{
    return new Request($_GET);
}

function post(): Request
{
    return new Request($_POST);
}

function session(): Request
{
    return new Request(isset($_SESSION) ? $_SESSION : []);
}

function server(): Request
{
    return new Request($_SERVER);
}

function config(): Request
{
    global $CONFIG;
    return new Request($CONFIG);
}

function marshal(mixed $value): Marshaller
{
    return new Marshaller($value);
}

/**
 * True when running under PHPUnit.
 *
 * Used to keep test runs out of anything that talks to the outside world or that
 * installs global handlers competing with PHPUnit's. Checked without autoloading so
 * it is safe to call from bootstrap.php.
 */
function isUnderTest(): bool
{
    return defined('TESTING') || class_exists(\PHPUnit\Framework\TestCase::class, false);
}

function files(): Files
{
    return new Files($_FILES);
}

function logger(): Logger
{
    static $logger;

    if (!$logger) {
        $logger = new Logger();
    }

    return $logger;
}

function datetime(string $datetime): DateTimeImmutable
{
    return new DateTimeImmutable($datetime, new DateTimeZone(date_default_timezone_get()));
}
