<?php

namespace Gatherling\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Standings;

class StandingsList extends EventFrame
{
    public array $eventStandings;

    public function __construct(Event $event, string|bool $playerLoginName)
    {
        parent::__construct($event);
        $this->eventStandings = Standings::eventStandingsArgs($event->name, $playerLoginName);
    }
}
