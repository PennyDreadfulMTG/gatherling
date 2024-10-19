<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Gatherling\Models\Player;
use Gatherling\Views\Pages\Forgot;

use function Gatherling\Helpers\get;
use function Gatherling\Helpers\post;
use function Gatherling\Helpers\server;

include 'util/email.php';
require_once 'lib.php';
require_once 'lib_form_helper.php';

function main(): void
{
    $hasResetPassword = $passwordResetFailed = $showForgotForm = $showNewPasswordForm = $sentLoginLink = $cantSendLoginLink = $cantFindPlayer = false;
    $token = $email = null;
    if (isset($_POST['view']) && $_POST['view'] === 'new_password') {
        if (resetPassword(post()->string('token'), post()->string('password'))) {
            $hasResetPassword = true;
        } else {
            $passwordResetFailed = $showForgotForm = true;
        }
    } elseif (isset($_GET['token'])) {
        $showNewPasswordForm = true;
        $token = get()->string('token');
    } elseif (isset($_POST['view']) && $_POST['view'] === 'send_login_link') {
        $identifier = post()->string('identifier', '');
        if (str_contains($identifier, '@')) {
            $player = Player::findByEmail($identifier);
        } else {
            $player = Player::findByName($identifier);
        }
        if ($player) {
            $email = $player->emailPrivacy ? "your registered email" : $player->emailAddress;
            if (sendLoginLink($player)) {
                $sentLoginLink = true;
            } else {
                $cantSendLoginLink = $showForgotForm = true;
            }
        } else {
            $cantFindPlayer = $showForgotForm = true;
        }
    } else {
        $showForgotForm = true;
    }

    $page = new Forgot($hasResetPassword, $passwordResetFailed, $showForgotForm, $showNewPasswordForm, $token, $email, $sentLoginLink, $cantSendLoginLink, $cantFindPlayer);
    $page->send();
}

function sendLoginLink(Player $player): bool
{
    $link = generateSecureResetLink($player->name);
    $body = <<<END
        <p>Hi $player->name,</p>

        <p>Sorry to hear you’re having trouble logging into Gatherling. We got a message that you forgot your password.
        If this was you, you can reset your password now.</p>

        <p></p><a href="$link">Reset your password</a></p>

        <p>If you didn’t request a login link or a password reset, you can ignore this message.</p>

        <p>Only people who know your Gatherling password or click the link in this email can log into your account.</p>
    END;
    return sendEmail($player->emailAddress, 'Gatherling Login Link', $body);
}

function generateSecureResetLink(string $name): string
{
    global $CONFIG;

    $key = $CONFIG['password_reset_key'];
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // Token expires in 1 hour
    $payload = [
        'iss' => $CONFIG['base_url'], // Issuer
        'aud' => $CONFIG['base_url'], // Audience
        'iat' => $issuedAt, // Issued at
        'exp' => $expirationTime, // Expiration time
        'name' => $name // Also embed the player name, so we don't need to look up anything
    ];
    $token = JWT::encode($payload, $key, 'HS256');
    return $CONFIG['base_url'] . "/forgot.php?token=$token";
}

function resetPassword(string $token, string $newPassword): bool
{
    global $CONFIG;

    try {
        $payload = JWT::decode($token, new Key($CONFIG['password_reset_key'], 'HS256'));
    } catch (Exception $e) {
        return false;
    }
    if (!isset($payload->name) || !isset($payload->exp)) {
        return false;
    }
    if (time() > $payload->exp) {
        return false;
    }
    $player = Player::findByName($payload->name);
    if (!$player) {
        return false;
    }
    $player->setPassword($newPassword);
    return true;
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
