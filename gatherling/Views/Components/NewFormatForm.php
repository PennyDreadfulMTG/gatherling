<?php

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\Component;

class NewFormatForm extends Component
{
    public string $action;

    public function __construct(public string $seriesName)
    {
        parent::__construct('partials/newFormatForm');
    }
}
