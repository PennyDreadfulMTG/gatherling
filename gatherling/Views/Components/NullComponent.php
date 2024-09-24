<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class NullComponent extends Component
{
    public function __construct()
    {
    }

    public function render(): string
    {
        return '';
    }
}
