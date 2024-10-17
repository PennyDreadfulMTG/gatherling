<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class DeckTrophyCell extends Component
{
    public bool $showTrophy = false;
    public ?Trophy $trophy = null;

    public function __construct(Deck $deck)
    {
        if (!$deck->medal == '1st') {
            return;
        }
        $this->showTrophy = true;
        $this->trophy = $deck->getEvent()->hastrophy ?  new Trophy($deck->getEvent()) : null;
    }
}
