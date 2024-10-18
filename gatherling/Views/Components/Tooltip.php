<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class Tooltip extends Component
{
    public function __construct(public string $text, public string $tooltip)
    {
    }
}
