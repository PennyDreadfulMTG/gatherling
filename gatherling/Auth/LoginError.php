<?php

declare(strict_types=1);

namespace Gatherling\Auth;

enum LoginError: string
{
    case MISSING_USERNAME = 'Missing username';
    case MISSING_PASSWORD = 'Missing password';
    case INVALID_CREDENTIALS = 'Invalid credentials';
    case PASSWORD_TOO_SHORT = 'Password is too short';
}
