<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\TimeDropMenu;

class CreateNewSeriesForm extends Component
{
    public DayDropMenu $dayDropMenu;
    public TimeDropMenu $timeDropMenu;

    public function __construct()
    {
        $this->dayDropMenu = new DayDropMenu('start_day');
        $time_parts = explode(':', '12:00:00');
        $this->timeDropMenu = new TimeDropMenu('hour', $time_parts[0], $time_parts[1]);
    }
}
