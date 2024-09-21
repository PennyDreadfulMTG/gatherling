<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;

class EventStandings extends Component
{
    public array $standings;

    public function __construct(
        public string $eventName,
        ?string $playerName = null,
    ) {
        parent::__construct('partials/eventStandings');
        $event = new Event($eventName);
        $standings = Standings::getEventStandings($eventName, 0);
        $rank = 1;
        $standingInfoList = [];
        foreach ($standings as $standing) {
            $standingInfo = getObjectVarsCamelCase($standing);
            $standingInfo['rank'] = $rank;
            $standingInfo['shouldHighlight'] = $standing->player == $playerName;
            $standingInfo['matchScore'] = $standing->score;
            $sp = new Player($standing->player);
            $standingInfo['gameName'] = new GameName($sp, $event->client);
            $rank++;
            $standingInfoList[] = $standingInfo;
        }
        $this->standings = $standingInfoList;
    }
}
