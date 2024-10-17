<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Series;
use Gatherling\Views\Components\DayDropMenu;

class SeriesForm
{
    public string $seriesName;
    public bool $isActive;
    public DayDropMenu $dayDropMenu;
    public ?TimeDropMenu $timeDropMenu;
    public bool $preregDefault;
    public TextInput $mtgoRoomTextInput;

    public function __construct(public Series $series)
    {
        $this->seriesName = $series->name;
        $this->isActive = $series->active == 1;
        $this->dayDropMenu = new DayDropMenu('start_day', $series->start_day ?? 'Monday');
        if ($series->start_time) {
            $timeParts = explode(':', $series->start_time);
            $this->timeDropMenu = new TimeDropMenu('hour', $timeParts[0], $timeParts[1]);
        }
        $this->preregDefault = $series->prereg_default == 1;
        $this->mtgoRoomTextInput = new TextInput('MTGO Room', 'mtgo_room', $series->mtgo_room);
    }
}
