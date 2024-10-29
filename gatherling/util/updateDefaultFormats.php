<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Models\Player;
use Gatherling\Models\Formats;
use Gatherling\Exceptions\SetMissingException;
use Gatherling\Views\Redirect;
use Gatherling\Views\WireResponse;

use function Gatherling\Helpers\logger;
use function Gatherling\Helpers\server;

set_time_limit(0);

require_once __DIR__ . '/../lib.php';

function main(): void
{
    if (PHP_SAPI != 'cli' && $_SERVER['REQUEST_METHOD'] == 'GET') { // unauthorized POST is okay
        if (!(Player::getSessionPlayer()?->isSuper() ?? false)) {
            (new Redirect('index.php'))->send();
        }
    }
    try {
        Formats::updateDefaultFormats();
        $message = 'done';
    } catch (SetMissingException $e) {
        logger()->warning($e->getMessage());
        $message = $e->getMessage();
    }
    $response = new WireResponse($message);
    $response->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
