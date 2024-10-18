<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class SubmitLeagueResultForm extends Component
{
    public string $playerName;
    public LeagueOpponentDropMenu $leagueOpponentDropMenu;

    public function __construct(public string $eventName, int $round, Player $player, int $subevent)
    {
        $this->playerName = $player->name ?? '';
        $this->leagueOpponentDropMenu = new LeagueOpponentDropMenu($eventName, $round, $player, $subevent);
    }
}
