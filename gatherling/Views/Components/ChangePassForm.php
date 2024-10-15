<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ChangePassForm extends Component
{
    public function __construct(public bool $tooShort)
    {
        parent::__construct('partials/changePassForm');
    }
}
