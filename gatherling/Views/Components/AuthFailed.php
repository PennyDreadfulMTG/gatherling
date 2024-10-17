<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class AuthFailed extends Component
{
    public function __construct()
    {
        parent::__construct('partials/authFailed');
    }
}
