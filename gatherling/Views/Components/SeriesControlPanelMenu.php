<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Series;

class SeriesControlPanelMenu
{
    public string $seriesName;

    public function __construct(public Series $series)
    {
        $this->seriesName = $series->name;
    }
}
