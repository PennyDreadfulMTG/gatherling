<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ManualVerificationForm extends Component
{
    public TextInput $usernameInput;
    public Submit $submitButton;

    public function __construct()
    {
        $this->usernameInput = new TextInput('Username', 'username');
        $this->submitButton = new Submit('Verify Player');
    }
}
