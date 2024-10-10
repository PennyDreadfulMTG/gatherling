<?php

declare(strict_types=1);

namespace Gatherling\Auth;

use Gatherling\Log;
use Gatherling\Models\Player;

class Login
{
    public static function login(?string $username, ?string $password): LoginResult
    {
        $errors = [];
        if (!isset($username)) {
            $errors[] = LoginError::MISSING_USERNAME;
        }
        if (!isset($password)) {
            $errors[] = LoginError::MISSING_PASSWORD;
        }
        if ($errors) {
            return new LoginResult(false, $errors);
        }
        assert(is_string($username) && is_string($password));
        $auth = Player::checkPassword($username, $password);
        // The $admin check allows an admin to su into any user without a password.
        $admin = Player::getSessionPlayer()?->isSuper() ?? false;
        if (!$auth && !$admin) {
            return new LoginResult(false, [LoginError::INVALID_CREDENTIALS]);
        }
        if (strlen($password) < 8 && !$admin) {
            $errors[] = LoginError::PASSWORD_TOO_SHORT;
        }
        return new LoginResult(true, $errors);
    }
}
