<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Pages\BannedPlayer;

require_once 'lib.php';

function main(): void
{
    $page = new BannedPlayer();
    echo $page->render();
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
