<?php

namespace Gatherling\Views\Components;

class FormatError extends Component
{
    public function __construct(public string $message, public string $formatName = '', public string $view = '')
    {
        parent::__construct('partials/formatError');
    }
}
