<?php

use Gatherling\Views\Pages\SeriesReport;

require_once 'lib.php';

function main(): void
{
    $seriesName = $_GET['series'] ?? null;
    $season = $_GET['season'] ?? null;
    $page = new SeriesReport($seriesName, $season);
    $page->send();
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
