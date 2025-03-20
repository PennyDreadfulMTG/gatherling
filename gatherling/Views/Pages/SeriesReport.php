<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Series;
use Gatherling\Views\Pages\Page;
use Gatherling\Views\Components\SeriesReportFilter;
use Gatherling\Views\Components\SeasonStandings;

class SeriesReport extends Page
{
    public SeriesReportFilter $seriesReportFilter;
    public ?SeasonStandings $seasonStandings;

    public function __construct(?string $seriesName, ?int $season)
    {
        parent::__construct('Season Report', true);
        $this->seriesReportFilter = new SeriesReportFilter($seriesName, $season);
        if ($seriesName && $season) {
            $series = new Series($seriesName);
            $this->seasonStandings = new SeasonStandings($series, $season);
        }
    }
}
