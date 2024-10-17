<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;

class Trophy extends Component
{
    public string $eventLink;
    public string $trophySrc;

    public function __construct(Event $event)
    {
        parent::__construct('partials/trophy');

        $this->eventLink = 'deck.php?mode=view&event=' . rawurlencode((string) ($event->id ?? ''));
        $this->trophySrc = Event::trophySrc($event->name ?? '');
    }
}
