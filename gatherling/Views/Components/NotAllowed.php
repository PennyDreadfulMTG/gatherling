<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class NotAllowed extends Component
{
    public function __construct(public string $reason)
    {
        parent::__construct('partials/notAllowed');
    }
}
