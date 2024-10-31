<?php

declare(strict_types=1);

use Gatherling\Auth\Session;
use Gatherling\Models\Player;

use function Gatherling\Helpers\config;
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

const MTGO = 1;
const MTGA = 2;
const PAPER = 3;

function json_headers(): void
{
    header('Content-type: application/json');
    header('Cache-Control: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Access-Control-Allow-Origin: *');
    header('HTTP_X_USERNAME: ' . Player::loginName());
}
