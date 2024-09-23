<?php

namespace Gatherling\Views\Components;

class FormatSaveAsForm extends Component
{
    public function __construct(public string $oldFormatName)
    {
        parent::__construct('partials/formatSaveAsForm');
    }
}
