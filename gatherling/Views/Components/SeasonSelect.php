<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\Component;

class SeasonSelect extends Component
{
    public SeriesDropMenu $seriesDropMenu;
    public SeasonDropMenu $seasonDropMenu;

    public function __construct(?string $seriesName, ?int $season)
    {
        $this->seriesDropMenu = new SeriesDropMenu($seriesName, 'All');
        $this->seasonDropMenu = new SeasonDropMenu($season, null);
        parent::__construct('partials/seasonSelect');
    }
}
