<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Views\Pages\BannedPlayer;

require_once 'lib.php';

function main(): void
{
    $page = new BannedPlayer();
    $page->send();
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
