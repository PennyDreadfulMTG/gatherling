<?php

namespace Gatherling\Views\Components;

class PasswordInput extends Component
{
    public function __construct(public string $label, public string $name)
    {
    }
}
