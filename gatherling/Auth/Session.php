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
        $token = $_COOKIE['remember_me'] ?? null;
        if (empty($_SESSION) && $token) {
            self::clear($token);
            return;
        }
        if (!$token) {
            $token = bin2hex(random_bytes(32));
        }
        $details = json_encode($_SESSION);
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
    }

    private static function clear(string $token): void
    {
        // We take the opportunity to delete expired sessions here, too
        $sql = 'DELETE FROM sessions WHERE token = :token OR expiry < NOW()';
        $args = ['token' => $token];
        DB::execute($sql, $args);
        setcookie('remember_me', '', time() - 60 * 60, '/');
    }
}
