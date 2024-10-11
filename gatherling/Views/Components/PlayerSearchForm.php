<?php

namespace Gatherling\Views\Components;

class PlayerSearchForm extends Component
{
    public function __construct(public string $playerName)
    {
        parent::__construct('partials/playerSearchForm');
    }
}
