<?php

namespace Gatherling\Views\Components;

class TextInput extends Component
{
    public string $id;
    public string $value;

    public function __construct(public string $label, public string $name, mixed $value = '', public int $size = 0, public ?string $reminderText = null, ?string $id = null)
    {
        parent::__construct('partials/textInput');
        $this->id = $id ?? $this->name;
        $this->value = $value ?? '';
    }
}
