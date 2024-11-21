<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;

class TrophyField extends Component
{
    public bool $hasTrophy;
    public string $trophySrc;

    public function __construct(Event $event)
    {
        $this->hasTrophy = (bool) $event->hastrophy;
        $this->trophySrc = 'displayTrophy.php?event=' . rawurlencode($event->name);
    }
}
