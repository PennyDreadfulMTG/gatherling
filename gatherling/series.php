<?php

declare(strict_types=1);

use Gatherling\Models\Series as SeriesModel;
use Gatherling\Views\Pages\Series;

require_once 'lib.php';

function main(): void
{
    $activeSeriesNames = SeriesModel::activeNames();
    $page = new Series($activeSeriesNames);
    $page->send();
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
