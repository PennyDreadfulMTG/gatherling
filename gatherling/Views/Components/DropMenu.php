<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class DropMenu extends Component
{
    /** @param list<array{value: string, text: string, isSelected?: bool}> $options */
    public function __construct(public string $name, public array $options, public ?string $default = null, public ?string $id = null, public string $defaultValue = '')
    {
    }
}
