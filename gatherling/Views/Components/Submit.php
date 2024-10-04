<?php

namespace Gatherling\Views\Components;

class Submit extends Component
{
    public function __construct(public string $label, public string $name = 'action')
    {
        parent::__construct('partials/submit');
    }
}
