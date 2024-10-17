<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class OrganizerSelect extends Component
{
    public SeriesDropMenu $seriesDropMenu;

    /** @param list<string> $playerSeries */
    public function __construct(public string $action, array $playerSeries, string $selected)
    {
        $this->seriesDropMenu = new SeriesDropMenu($selected, null, $playerSeries);
    }
}
