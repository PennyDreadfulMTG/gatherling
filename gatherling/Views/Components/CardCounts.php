<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class CardCounts extends Component
{
    public int $numCardsMaindeck;
    public int $numCardsSideboard;

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/cardCounts');
        $this->numCardsMaindeck = $deck->getCardCount($deck->maindeck_cards);
        $this->numCardsSideboard = $deck->getCardCount($deck->sideboard_cards);
    }
}