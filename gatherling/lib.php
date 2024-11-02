<?php

declare(strict_types=1);

use Gatherling\Auth\Session;

use function Safe\ob_start;
use function Safe\php_sapi_name;

require_once 'bootstrap.php';

ob_start();

header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (php_sapi_name() !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    Session::start();
}

date_default_timezone_set('US/Eastern'); // force time functions to use US/Eastern time
