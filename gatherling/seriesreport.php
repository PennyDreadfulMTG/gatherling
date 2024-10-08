<?php

declare(strict_types=1);

use Gatherling\Views\Pages\SeriesReport;

use function Gatherling\Views\get;
use function Gatherling\Views\server;

require_once 'lib.php';

function main(): void
{
    $seriesName = get()->optionalString('series');
    $season = get()->int('season', 0);
    $page = new SeriesReport($seriesName, $season);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
