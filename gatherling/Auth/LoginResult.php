<?php

declare(strict_types=1);

namespace Gatherling\Auth;

class LoginResult
{
    /**
     * @param LoginError[] $errors
     */
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
