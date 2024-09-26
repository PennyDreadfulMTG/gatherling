<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;

class EventHelper
{
    /** @return array<string, string|array<string, bool|int|string>> */
    public static function playerDropMenuArgs(Event $event, string|int $letter, string $def = "\n"): array
    {
        // If the event is active, only list players who haven't already dropped.
        // Otherwise, list all registered players.
        $playerNames = $event->getRegisteredPlayers((bool) $event->active);
        sort($playerNames, SORT_STRING | SORT_NATURAL | SORT_FLAG_CASE);

        $default = strcmp("\n", $def) == 0 ? "- Player $letter -" : '- None -';
        $options = [];
        foreach ($playerNames as $player) {
            $options[] = [
                'isSelected' => strcmp($player, $def) == 0,
                'value'      => $player,
                'text'       => $player,
            ];
        }

        return [
            'name'    => "newmatchplayer$letter",
            'default' => $default,
            'options' => $options,
        ];
    }

    /** @return array<string, string|array<string, bool|int|string>> */
    public static function roundDropMenuArgs(Event $event, int|string $selected): array
    {
        $options = [];
        for ($r = 1; $r <= ((int) $event->mainrounds + (int) $event->finalrounds); $r++) {
            $star = $r > $event->mainrounds ? '*' : '';
            $options[] = [
                'isSelected' => $selected == $r,
                'value'      => $r,
                'text'       => "$r$star",
            ];
        }

        return [
            'name'    => 'newmatchround',
            'default' => '- Round -',
            'options' => $options,
        ];
    }
}
