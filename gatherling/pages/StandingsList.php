<?php

namespace Gatherling\Pages;

use Gatherling\Event;
use Gatherling\Standings;

class StandingsList extends EventFrame {
    public array $eventStandings;

    public function __construct(Event $event, string|bool $playerLoginName) {
        parent::__construct($event);
        $this->eventStandings = Standings::eventStandingsArgs($event->name, $playerLoginName);
    }
}
