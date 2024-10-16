<?php

namespace Gatherling\Views\Components;

class LoginRequired extends Component
{
    public function __construct()
    {
        parent::__construct('partials/loginRequired');
    }
}
