<?php

declare(strict_types=1);

use function Gatherling\Helpers\db;
use function Gatherling\Helpers\server;

require_once __DIR__ . '/bootstrap.php';

function main(): never
{
    header('Content-type: text/plain');
    $sql = 'SELECT name FROM players ORDER BY name';
    $names = db()->strings($sql);
    $n = 10000001;
    foreach ($names as $name) {
        printf("%08d\tx\t%s\tUS\n", $n, $name);
        $n++;
    }
    exit;
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
