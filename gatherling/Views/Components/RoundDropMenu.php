<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;

class RoundDropMenu extends DropMenu
{
    public function __construct(Event $event, int|string $selected)
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
        parent::__construct('newmatchround', $options, '- Round -');
    }
}
