<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Player;

class TrophyCell extends Component
{
    public ?string $trophySrc;
    public array $winner;

    public function __construct(Event $event)
    {
        parent::__construct('partials/trophyCell');
        if ($event->hastrophy) {
            $this->trophySrc = Event::trophySrc($event->name);
        }
        $deck = $event->getPlaceDeck('1st');
        $winnerName = $event->getPlacePlayer('1st');
        if ($winnerName) {
            $winner = new Player($winnerName);
            $this->winner = [
                'playerLink' => new PlayerLink($winner),
                'manaSrc' => $deck->manaSrc(),
                'deckLink' => new DeckLink($deck),
            ];
        }
    }
}
