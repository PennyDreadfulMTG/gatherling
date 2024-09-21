<?php

namespace Gatherling\Auth;

class LoginResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $errors = [],
    ) {
    }

    public function hasError(LoginError $error): bool
    {
        return in_array($error, $this->errors, true);
    }
}
