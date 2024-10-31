<?php

declare(strict_types=1);

namespace Gatherling\Admin;

use Gatherling\Data\Setup;

use function Gatherling\Helpers\server;
use function Safe\set_time_limit;
set_time_limit(0);

require_once __DIR__ . '/../lib.php';

function main(): never
{
    Setup::setupDatabase();
    echo 'done';
    exit;
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
