<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class ExactMatchTable extends Component
{
    /** @var array<array{medal: Medal, recordString: string, deckLink: DeckLink, playerName: string, threadLink: string, eventName: string}> */
    public array $decks = [];

    /** @param list<Deck> $decks */
    public function __construct(array $decks)
    {
        parent::__construct('partials/exactMatchTable');
        foreach ($decks as $deck) {
            if (!isset($deck->playername)) {
                continue;
            }
            $this->decks[] = [
                'medal' => new Medal($deck->medal ?? 'dot'),
                'recordString' => $deck->recordString(),
                'deckLink' => new DeckLink($deck),
                'playerName' => $deck->playername,
                'threadLink' => $deck->getEvent()->threadurl ?? '',
                'eventName' => $deck->eventname ?? '',
            ];
        }
    }
}
