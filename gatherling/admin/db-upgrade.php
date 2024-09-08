<?php

namespace Gatherling\Admin;

use Gatherling\Data\Setup;

set_time_limit(0);

require_once __DIR__.'/../lib.php';

function main(): void
{
    Setup::setupDatabase();
    echo 'done';
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
