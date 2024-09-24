<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Series;
use Gatherling\Views\Pages\Page;
use Gatherling\Views\Components\SeasonSelect;
use Gatherling\Views\Components\SeasonStandings;

class SeriesReport extends Page
{
    public SeasonSelect $seasonSelect;
    public ?SeasonStandings $seasonStandings;

    public function __construct(?string $seriesName, ?string $season)
    {
        parent::__construct();
        $this->title = 'Season Report';
        $this->seasonSelect = new SeasonSelect($seriesName, $season);
        if ($seriesName && $season) {
            $series = new Series($seriesName);
            $this->seasonStandings = new SeasonStandings($series, $season);
        }
    }
}
