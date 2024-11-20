<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;

class PlayerByeMenu extends DropMenu
{
    public function __construct(Event $event)
    {
        $playerNames = $event->getRegisteredPlayers(true);
        $options = [];
        foreach ($playerNames as $player) {
            $options[] = [
                'value' => $player,
                'text'  => $player,
            ];
        }
        parent::__construct('newbyeplayer', $options, '- Bye Player -');
    }
}
