<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

abstract class DropMenu extends Component
{
    public string $name;
    public array $options;
    public ?string $default;
    public ?string $id;

    public function __construct(string $name, array $options, ?string $default = null, ?string $id = null)
    {
        parent::__construct('partials/dropMenu');
        $this->name = $name;
        $this->options = $options;
        $this->default = $default;
        $this->id = $id;
    }
}
