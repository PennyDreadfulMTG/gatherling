<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Views\Pages\BannedPlayer;

use function Gatherling\Helpers\server;

require_once 'lib.php';

function main(): void
{
    $page = new BannedPlayer();
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
