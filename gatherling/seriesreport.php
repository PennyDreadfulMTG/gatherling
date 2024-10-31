<?php

declare(strict_types=1);

use Gatherling\Views\Pages\SeriesReport;

use function Gatherling\Helpers\get;
use function Gatherling\Helpers\server;
use function Safe\ini_set;

require_once 'lib.php';

// Some of the Pauper series seasons had 1000+ entrants and 256 tourneys.
// We should find a better way to display that but for now make them work
// without hitting the PHP memory limit.
ini_set('memory_limit', '512M');

function main(): never
{
    $seriesName = get()->optionalString('series');
    $season = get()->int('season', 0);
    $page = new SeriesReport($seriesName, $season);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
