<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class PlayerSearchForm extends Component
{
    public function __construct(public string $playerName)
    {
    }
}
