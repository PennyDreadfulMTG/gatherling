<?php

namespace Gatherling\Views\Components;

class Medal extends Component
{
    public function __construct(public string $medal)
    {
        parent::__construct('partials/medal');
    }
}
