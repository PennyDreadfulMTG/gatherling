<?php

namespace Gatherling\Auth;

use Gatherling\Models\Player;

class Registration
{
    public const SUCCESS = 0;
    public const ERROR_PLAYER_EXISTS = -3;
    public const ERROR_PASSWORD_MISMATCH = -1;

    public static function register(string $username, string $pw1, string $pw2, string $email, int $emailStatus, ?float $timezone, ?string $discordId, ?string $discordName)
    {
        $player = Player::findOrCreateByName(trim($username));
        if ($player && !is_null($player->password)) {
            return self::ERROR_PLAYER_EXISTS;
        }
        if (strcmp($pw1, $pw2) != 0) {
            return self::ERROR_PASSWORD_MISMATCH;
        }
        if (empty($pw1) && !isset($discordId)) {
            return self::ERROR_PASSWORD_MISMATCH;
        }
        $player->password = hash('sha256', $pw1);
        $player->super = Player::activeCount() == 0;
        $player->emailAddress = $email;
        $player->emailPrivacy = $emailStatus;
        $player->timezone = $timezone;
        if (isset($discordId)) {
            $player->discord_id = $discordId;
        }
        if (isset($discordName)) {
            $player->discord_handle = $discordName;
        }
        $player->save();
        $_SESSION['username'] = $username;
        return self::SUCCESS;
    }
}
