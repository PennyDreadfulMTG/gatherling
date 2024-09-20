<?php

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Views\Components\EventStandings;

class StandingsList extends EventFrame
{
    public EventStandings $eventStandings;

    public function __construct(Event $event, string|bool $playerLoginName)
    {
        parent::__construct($event);
        $this->eventStandings = new EventStandings($event->name, $playerLoginName);
    }
}
