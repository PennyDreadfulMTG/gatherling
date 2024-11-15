<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

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
        opMatch: string,
        plGame: string,
        opGame: string,
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
                'matchScore' => $standing->score,
                'opMatch' => number_format($standing->OP_Match, 3),
                'plGame' => number_format($standing->PL_Game, 3),
                'opGame' => number_format($standing->OP_Game, 3),
                'matchesPlayed' => $standing->matches_played,
                'byes' => $standing->byes,
            ];
            $rank++;
            $standingInfoList[] = $standingInfo;
        }
        $this->standings = $standingInfoList;
    }
}
