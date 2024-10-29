<?php

declare(strict_types=1);

use Gatherling\Models\Series as SeriesModel;
use Gatherling\Views\Pages\Series;

use function Gatherling\Helpers\server;

require_once 'lib.php';

function main(): void
{
    $activeSeriesNames = SeriesModel::activeNames();
    $page = new Series($activeSeriesNames);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
