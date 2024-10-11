<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Player;

class TrophyTable extends Component
{
    public string $playerName;
    /** @var array<array{deckLink: string, eventName: string, trophySrc: string}> */
    public array $trophies;

    public function __construct(Player $player)
    {
        $events = $player->getEventsWithTrophies();
        $this->playerName = $player->name ?? '';
        $this->trophies = array_map(fn($eventName) => [
            'deckLink' => 'deck.php?mode=view&event=' . rawurlencode($eventName),
            'eventName' => $eventName,
            'trophySrc' => Event::trophySrc($eventName),
        ], $events);
    }
}
