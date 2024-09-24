<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class PlayerLink extends Component
{
    public GameName $gameName;
    public string $playerLink;

    public function __construct(Player $player, string $game = 'gatherling')
    {
        parent::__construct('partials/playerLink');
        $this->gameName = new GameName($player, $game);
        $this->playerLink = "profile.php?player={$player->name}";
    }
}
