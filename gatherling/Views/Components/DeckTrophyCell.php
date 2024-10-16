<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class DeckTrophyCell extends Component
{
    public bool $showTrophy = false;
    public ?Trophy $trophy = null;

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/deckTrophyCell');

        if (!$deck->medal == '1st') {
            return;
        }
        $this->showTrophy = true;
        $this->trophy = $deck->getEvent()->hastrophy ?  new Trophy($deck->getEvent()) : null;
    }
}
