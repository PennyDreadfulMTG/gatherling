<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ErrorMessage extends Component
{
    public function __construct(public array $errors)
    {
        parent::__construct('partials/errorMessage');
    }
}
