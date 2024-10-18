<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class DayDropMenu extends DropMenu
{
    public function __construct(string $name, string $default = 'Monday')
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $options = array_map(fn (string $day) => ['value' => $day, 'text' => $day, 'isSelected' => ($day === $default)], $days);
        parent::__construct($name, $options);
    }
}
