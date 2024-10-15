<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class DropConfirm extends Component
{
    public function __construct(public string $eventName, public string $playerName)
    {
        parent::__construct('partials/dropConfirm');
    }
}
