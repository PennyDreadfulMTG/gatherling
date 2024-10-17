<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Player;

class RecentMatchTable extends Component
{
    /** @var array<array-key, array{eventName: string, round: string, res: string, playerWins: int, playerLosses: int, opponentLink: ?PlayerLink}> */
    public array $matches = [];

    public function __construct(Player $player)
    {
        $matches = $player->getRecentMatches();

        foreach ($matches as $match) {
            $res = 'Draw';
            if ($match->playerWon($player)) {
                $res = 'Win';
            }
            if ($match->playerLost($player)) {
                $res = 'Loss';
            }
            if ($match->playera == $match->playerb) {
                $res = 'BYE';
            }
            $opp = $player->name === $match->playera ? $match->playerb : $match->playera;
            $event = new Event($match->getEventNamebyMatchid());

            $opponentLink = null;
            if ($opp) {
                $opponentLink = new PlayerLink(new Player($opp), $event->client);
            }

            $wins = $player->name ? $match->getPlayerWins($player->name) : 0;
            $losses = $player->name ? $match->getPlayerLosses($player->name) : 0;
            $this->matches[] = [
                'eventName' => $event->name ?? '',
                'round' => (string) $match->round,
                'res' => $res,
                'playerWins' => $wins ?: 0,
                'playerLosses' => $losses ?: 0,
                'opponentLink' => $opponentLink,
            ];
        }
    }
}
