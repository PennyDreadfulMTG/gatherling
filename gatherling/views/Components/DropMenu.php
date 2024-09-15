<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

abstract class DropMenu extends Component
{
    public string $name;
    public ?string $default;
    public array $options;

    public function __construct(string $name, array $options, ?string $default = null)
    {
        parent::__construct('partials/dropMenu');
        $this->name = $name;
        $this->options = $options;
    }
}
