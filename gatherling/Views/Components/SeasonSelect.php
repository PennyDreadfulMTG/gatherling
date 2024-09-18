<?php

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\Component;

class SeasonSelect extends Component
{
    public SeriesDropMenu $seriesDropMenu;
    public SeasonDropMenu $seasonDropMenu;

    public function __construct(string $seriesName, string $season)
    {
        $this->seriesDropMenu = new SeriesDropMenu($seriesName, true);
        $this->seasonDropMenu = new SeasonDropMenu($season);
        parent::__construct('partials/seasonSelect');
    }
}
