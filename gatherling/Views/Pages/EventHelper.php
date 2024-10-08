<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;

class EventHelper
{
    /** @return array{name: string, default: string, options: list<array{isSelected: bool, value: int, text: string}>} */
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
