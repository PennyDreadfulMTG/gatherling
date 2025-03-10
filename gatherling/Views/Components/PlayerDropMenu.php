<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;

class PlayerDropMenu extends DropMenu
{
    public function __construct(Event $event, int|string $letter, string $def = "\n")
    {
        // If the event is active, only list players who haven't already dropped.
        // Otherwise, list all registered players.
        $playerNames = $event->getRegisteredPlayers((bool) $event->active);
        sort($playerNames, SORT_STRING | SORT_NATURAL | SORT_FLAG_CASE);

        $default = $def === "\n" ? "- Player $letter -" : '- None -';
        $options = [];
        foreach ($playerNames as $player) {
            $options[] = [
                'isSelected' => $player === $def,
                'value'      => $player,
                'text'       => $player,
            ];
        }
        $name = "newmatchplayer$letter";
        parent::__construct($name, $options, $default);
    }
}
