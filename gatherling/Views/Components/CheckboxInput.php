<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class CheckboxInput extends Component
{
    public function __construct(public string $label, public string $name, public bool $isChecked = false, public ?string $reminderText = null)
    {
        parent::__construct('partials/checkboxInput');
    }
}
