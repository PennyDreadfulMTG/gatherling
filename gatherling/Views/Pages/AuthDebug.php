<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\AuthDebugInfo;
use League\OAuth2\Client\Token\AccessToken;

class AuthDebug extends Page
{
    public AuthDebugInfo $authDebugInfo;

    public function __construct(AccessToken $token)
    {
        parent::__construct();
        $this->title = 'Auth Debug';
        $this->authDebugInfo = new AuthDebugInfo($token);
    }
}
