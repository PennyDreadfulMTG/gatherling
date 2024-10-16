<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class SelectInput extends Component
{
    public string $id;
    public Select $select;

    /** @param ?array<string, string> $options */
    public function __construct(public string $label, public string $name, ?array $options, mixed $selected = null, ?string $id = null)
    {
        parent::__construct('partials/selectInput');
        $this->id = $id ?: $name;
        $options = $options ?: [];
        $this->select = new Select($name, $options, $selected, $id);
    }
}
