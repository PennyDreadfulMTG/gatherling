<?php

declare(strict_types=1);

namespace Gatherling\Admin;

use Gatherling\Data\Setup;

use function Gatherling\Helpers\logger;
use function Gatherling\Helpers\server;
use function Safe\set_time_limit;

require_once __DIR__ . '/../bootstrap.php';

try {
    set_time_limit(0);
} catch (\Exception $e) {
    // set_time_limit is not allowed here but we'll try our best to complete in time.
    logger()->warning('Failed to set time limit, trying anyway: ' . $e->getMessage());
}

function main(): never
{
    global $argv;
    if (in_array('--test-database', $argv ?? [])) {
        echo "Updating test database\n";
        Setup::setupTestDatabase();
    } else {
        echo "Updating production database\n";
        Setup::setupDatabase();
    }
    echo "Done\n";
    exit;
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
