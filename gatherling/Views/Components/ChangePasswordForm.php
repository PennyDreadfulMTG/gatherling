<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\Submit;
use Gatherling\Views\Components\TextInput;

class ChangePasswordForm extends Component
{
    public TextInput $usernameTextInput;
    public TextInput $newPasswordTextInput;
    public Submit $submitButton;

    public function __construct()
    {
        parent::__construct('partials/changePasswordForm');
        $this->usernameTextInput = new TextInput('Username', 'username');
        $this->newPasswordTextInput = new TextInput('New Password', 'new_password');
        $this->submitButton = new Submit('Change Password');
    }
}
