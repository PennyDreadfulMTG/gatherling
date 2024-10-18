<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;

class EventStandings extends Component
{
    /** @var list<array{
        shouldHighlight: bool,
        rank: int,
        gameName: GameName,
        matchScore: int,
        opMatch: ?float,
        plGame: ?float,
        opGame: ?float,
        matchesPlayed: int,
        byes: int,
    }> */
    public array $standings;

    public function __construct(
        public string $eventName,
        ?string $playerName = null,
    ) {
        $event = new Event($eventName);
        $standings = Standings::getEventStandings($eventName, 0);
        $rank = 1;
        $standingInfoList = [];
        foreach ($standings as $standing) {
            $sp = new Player($standing->player);
            $standingInfo = [
                'shouldHighlight' => $standing->player == $playerName,
                'rank' => $rank,
                'gameName' => new GameName($sp, $event->client),
                'matchScore' => $standing->score ?? 0,
                'opMatch' => $standing->OP_Match ? number_format($standing->OP_Match, 3) : null,
                'plGame' => $standing->PL_Game ? number_format($standing->PL_Game, 3) : null,
                'opGame' => $standing->OP_Game ? number_format($standing->OP_Game, 3) : null,
                'matchesPlayed' => $standing->matches_played ?? 0,
                'byes' => $standing->byes ?? 0,
            ];
            $rank++;
            $standingInfoList[] = $standingInfo;
        }
        $this->standings = $standingInfoList;
    }
}
