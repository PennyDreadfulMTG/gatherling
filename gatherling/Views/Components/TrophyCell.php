<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Player;

class TrophyCell extends Component
{
    public ?string $trophySrc;
    /** @var array{playerLink: PlayerLink, manaSrc: string, deckLink: DeckLink}|null */
    public ?array $winner;

    public function __construct(Event $event)
    {
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
