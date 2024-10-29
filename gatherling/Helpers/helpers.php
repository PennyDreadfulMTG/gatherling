<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use Gatherling\Logger;
use Psr\Log\LoggerInterface;

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
    return new Request($_SESSION);
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
