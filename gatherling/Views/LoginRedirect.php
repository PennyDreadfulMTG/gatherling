<?php

declare(strict_types=1);

namespace Gatherling\Views;

class LoginRedirect extends Redirect
{
    public function __construct(string $redirect = '', string $message = '', string $username = '')
    {
        if (!$redirect) {
            $redirect = server()->optionalString('REQUEST_URI') ?? '';
        }
        parent::__construct('login.php?target=' . rawurlencode($redirect) . '&message=' . rawurlencode($message) . '&username=' . rawurlencode($username));
    }
}
