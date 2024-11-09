<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class GameName extends Component
{
    private const MTGO = 1;
    private const MTGA = 2;
    private const PAPER = 3;

    public string $name;
    public ?string $iconClass;

    public function __construct(Player $player, int|string|null $game = 'gatherling', bool $html = true)
    {
        $iconClass = null;
        $name = $player->name;
        if ($html) {
            if ($game == self::MTGO && !empty($player->mtgo_username)) {
                $iconClass = 'ss ss-pmodo';
                $name = $player->mtgo_username;
            } elseif ($game == self::MTGA && !empty($player->mtga_username)) {
                $iconClass = 'ss ss-parl3';
                $name = $player->mtga_username;
            } elseif (($game == self::PAPER || $game == 'discord') && !empty($player->discord_handle)) {
                $iconClass = 'fab fa-discord';
                $name = $player->discord_handle;
            } elseif ($game == 'gatherling') {
                $iconClass = 'ss ss-dd2';
            }
        }

        $this->iconClass = $iconClass;
        $this->name = $name;
    }
}
