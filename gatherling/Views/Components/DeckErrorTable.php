<?php

namespace Gatherling\Views\Components;

class DeckErrorTable extends Component
{
    /** @param list<string> $deckErrors */
    public function __construct(public array $deckErrors)
    {
        parent::__construct('partials/deckErrorTable');
    }
}