<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

class Login extends Page
{
    public function __construct(
        public bool $loginFailed,
        public bool $ipAddressChanged,
        public string $message,
        public string $username,
        public string $target,
        public string $discordId
    ) {
        parent::__construct();
        $this->title = 'Login';
    }
}
