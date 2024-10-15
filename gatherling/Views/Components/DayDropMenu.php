<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class DayDropMenu extends DropMenu
{
    public function __construct(string $name)
    {
        parent::__construct($name, opts(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']));
    }
}
