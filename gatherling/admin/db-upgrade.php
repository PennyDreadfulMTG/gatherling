<?php

namespace Gatherling\Admin;

use Gatherling\Data\Setup;

require_once __DIR__ . '/../lib.php';

function main(): void
{
    Setup::setupDatabase();
    Setup::setupTestDatabase();
    echo "done";
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
