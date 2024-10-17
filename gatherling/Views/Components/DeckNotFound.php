<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class DeckNotFound extends Component
{
    public function __construct()
    {
        parent::__construct('partials/deckNotFound');
    }
}
