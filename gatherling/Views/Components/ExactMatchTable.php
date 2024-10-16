<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class ExactMatchTable extends Component
{
    /** @var array<array{medal: Medal, recordString: string, deckLink: DeckLink, playerName: string, threadLink: string, eventName: string}> */
    public array $decks = [];

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/exactMatchTable');
        if ($deck->maindeck_cardcount < 5) {
            return;
        }
        $decks = $deck->findIdenticalDecks();
        if (count($decks) == 0) {
            return;
        }
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
