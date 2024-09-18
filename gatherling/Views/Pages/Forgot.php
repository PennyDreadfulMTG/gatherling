<?php

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\Submit;
use Gatherling\Views\Components\TextInput;
use Gatherling\Views\Components\PasswordInput;

class Forgot extends Page
{
    public ?PasswordInput $newPasswordInput;
    public ?Submit $resetPassword;
    public ?TextInput $identifierInput;
    public ?Submit $sendLoginLink;

    public function __construct(public bool $hasResetPassword, public bool $passwordResetFailed, public bool $showForgotForm, public bool $showNewPasswordForm, public ?string $token, public ?string $email, public bool $sentLoginLink, public bool $cantSendLoginLink, public bool $cantFindPlayer)
    {
        parent::__construct();
        $this->title = 'Login';
        if ($this->showNewPasswordForm) {
            $this->newPasswordInput = new PasswordInput('New Password', 'password');
            $this->resetPassword = new Submit('Reset Password');
        }
        if ($this->showForgotForm) {
            $this->identifierInput = new TextInput('Email or Username', 'identifier');
            $this->sendLoginLink = new Submit('Send Login Link');
        }
    }
}
