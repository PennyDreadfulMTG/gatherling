<?php

namespace Gatherling;

use Gatherling\Log;
use Gatherling\Models\Player;
use Gatherling\Models\Formats;
use Gatherling\Exceptions\SetMissingException;

set_time_limit(0);

require_once __DIR__ . '/../lib.php';

function main(): void
{
    if (PHP_SAPI != 'cli' && $_SERVER['REQUEST_METHOD'] == 'GET') { // unauthorized POST is okay
        if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
            redirect('index.php');
        }
    }
    try {
        Formats::updateDefaultFormats();
        echo "done";
    } catch (SetMissingException $e) {
        Log::warning($e->getMessage());
        echo $e->getMessage();
        exit(0);
    }
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
