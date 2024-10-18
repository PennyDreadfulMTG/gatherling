<?php

declare(strict_types=1);

namespace Gatherling\Views;

use Gatherling\Log;

use function Gatherling\Views\server;

class LoginRedirect extends Redirect
{
    public function __construct(string $redirect = '', string $message = '', string $username = '')
    {
        if (!$redirect) {
            $redirect = server()->optionalString('REQUEST_URI') ?? '';
        }
        // We sometimes seem to get in an infinite redirect loop. Let's avoid that and log something to help diagnose why.
        if (str_contains($redirect, 'login.php')) {
            Log::error('LoginRedirect: login.php in redirect, referer: ' . server()->string('HTTP_REFERER', ''));
            $redirect = 'player.php';
        }
        parent::__construct('login.php?target=' . rawurlencode($redirect) . '&message=' . rawurlencode($message) . '&username=' . rawurlencode($username));
    }
}
