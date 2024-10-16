<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class DeckInfoCell extends Component
{
    public bool $isValid;
    public string $name;
    public int $deckId;
    public CardCounts $cardCounts;
    public Placing $placing;
    public DeckInfo $deckInfo;

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/deckInfoCell');

        $this->name = $deck->name ? strtoupper($deck->name) : '** NO NAME **';
        $this->isValid = $deck->isValid();
        $this->cardCounts = new CardCounts($deck);
        $event = $deck->getEvent();
        $this->deckInfo = new DeckInfo($event, $deck);
        $this->placing = new Placing($event, $deck);
    }
}
