<?php

namespace Gatherling;

use Gatherling\Log;
use Gatherling\Models\Player;
use Gatherling\Models\Formats;
use Gatherling\Exceptions\SetMissingException;

require_once __DIR__ . '/../lib.php';

function main(): void
{
    if (PHP_SAPI != 'cli' && $_SERVER['REQUEST_METHOD'] == 'GET') { // unauthorized POST is okay
        ini_set('max_execution_time', 300);
        if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
            redirect('index.php');
        }
    }
    set_time_limit(0);
    try {
        Formats::updateDefaultFormats();
        echo "done";
    } catch (SetMissingException $e) {
        Log::error($e->getMessage());
        echo $e->getMessage();
        exit(0);
    }
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
