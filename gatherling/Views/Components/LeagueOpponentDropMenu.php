<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;
use InvalidArgumentException;

class LeagueOpponentDropMenu extends DropMenu
{
    public function __construct(public string $eventName, public int $round, public Player $player, int $subevent)
    {
        $event = new Event($eventName);
        if (!$player->name) {
            throw new InvalidArgumentException('Player name is required');
        }
        $playerStandings = new Standings($eventName, $player->name);
        $playernames = $playerStandings->getAvailableLeagueOpponents($subevent, $round, $event->leagueLength());
        $options = [];
        foreach ($playernames as $playername) {
            $oppPlayer = new Player($playername);
            $options[] = ['value' => $playername, 'text' => $oppPlayer->gameName($event->client, false)];
        }
        if (!$options) {
            $options = [['value' => '', 'text' => '- No Available Opponents -']];
        }
        parent::__construct('opponent', $options);
    }
}
