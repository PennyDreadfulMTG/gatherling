<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class OrganizerSelect extends Component
{
    public SeriesDropMenu $seriesDropMenu;

    public function __construct(public string $action, array $playerSeries, string $selected)
    {
        parent::__construct('partials/dropMenu');
        $this->seriesDropMenu = new SeriesDropMenu($selected, null, $playerSeries);
    }
}
