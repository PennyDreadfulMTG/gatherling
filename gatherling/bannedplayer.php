<?php

declare(strict_types=1);

namespace Gatherling;

use function Gatherling\Views\server;

use Gatherling\Views\Pages\BannedPlayer;

require_once 'lib.php';

function main(): void
{
    $page = new BannedPlayer();
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
