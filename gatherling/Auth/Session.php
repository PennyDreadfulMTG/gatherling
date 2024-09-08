<?php

namespace Gatherling\Auth;

use Gatherling\Data\DB;

class Session
{
    private static $LIFETIME = 60 * 60 * 24 * 60;

    public static function start(): void
    {
        session_start();
        self::init();
        register_shutdown_function([self::class, 'save']);
    }

    private static function init(): void
    {
        if (!empty($_SESSION)) {
            return;
        }
        if (!isset($_COOKIE['remember_me'])) {
            return;
        }
        $token = $_COOKIE['remember_me'];
        $_SESSION = self::load($token);
    }

    private static function load(string $token): array
    {
        $sql = '
            SELECT
                details
            FROM
                sessions
            WHERE
                token = :token
            AND
                expiry >= NOW()';
        $args = ['token' => $token];
        $details = DB::value($sql, $args);
        return $details ? json_decode($details, true) : [];
    }

    private static function save(): void
    {
        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];
        } else {
            $token = bin2hex(random_bytes(32));
        }
        // Force to object so that we get '{}' instead of '[]' when empty
        $details = json_encode((object) $_SESSION);
        $expiry = time() + self::$LIFETIME;
        $sql = '
            INSERT INTO
                sessions (token, details, expiry)
            VALUES
                (:token, :details, FROM_UNIXTIME(:expiry))
            ON DUPLICATE KEY UPDATE
                details = :details,
                expiry = FROM_UNIXTIME(:expiry)';
        $args = [
            'token' => $token,
            'details' => $details,
            'expiry' => $expiry,
        ];
        DB::execute($sql, $args);
        setcookie('remember_me', $token, $expiry, '/');
        $sql = 'DELETE FROM sessions WHERE expiry < NOW()';
        DB::execute($sql);
    }
}
