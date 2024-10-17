<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ErrorMessage extends Component
{
    /** @param list<string> $errors */
    public function __construct(public array $errors)
    {
    }
}
