<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Gatherling\Player;

include 'email.php';
include 'lib.php';
require_once 'lib_form_helper.php';

session_start();
print_header('Login');
printPageHeader();

if (isset($_POST['view']) && $_POST['view'] === 'new_password') {
    if (resetPassword($_POST['token'], $_POST['password'])) {
        echo '<p>Your password has been reset. You can now <a href="login.php">log in</a>.</p>';
    } else {
        echo '<p class="error">Unable to reset your password. Please try again.</p>';
        printForgotForm();
    }
} elseif (isset($_GET['token'])) {
    printNewPasswordForm($_GET['token']);
} elseif (isset($_POST['view']) && $_POST['view'] === 'send_login_link') {
    if (isset($_POST['identifier']) && str_contains($_POST['identifier'], '@')) {
        $player = Player::findByEmail($_POST['identifier']);
    } else {
        $player = Player::findByName($_POST['identifier']);
    }
    if ($player) {
        $email = $player->emailPrivacy ? "your registered email" : $player->emailAddress;
        if (sendLoginLink($player)) {
            echo '<p>A link has been sent to ' . htmlentities($email) . '</p>';
        } else {
            echo '<p class="error">Unable to send a link to ' . htmlentities($email) . '</p>';
            printForgotForm();
        }
    } else {
        echo '<p class="error">Unable to find a player with that email or username</p>';
        printForgotForm();
    }
} else {
    printForgotForm();
}

printPageFooter();
print_footer();

function printPageHeader(): void
{
    ?>
    <div class="grid_10 suffix_1 prefix_1">
        <div id="gatherling_main" class="box">
            <div class="uppertitle"> Login to Gatherling </div>
            <center>
                <h3>Resetting your Gatherling password</h3>
    <?php
}

function printPageFooter(): void
{
    ?>
            </center>
        </div> <!-- gatherling_main -->
    </div> <!-- grid 10 pre 1 suff 1 -->
    <?php
}

function printForgotForm(): void
{
    ?>
        <p>Enter your email or username and we'll send you a link to get back into your account.</p>
        <form action="forgot.php" method="post">
            <input type="hidden" name="view" value="send_login_link" />
            <table class="form">
                <?php
                    print_text_input('Email or Username', 'identifier');
                    print_submit('Send Login Link');
                ?>
            </table>
        </form>
        <p>If you aren't able to reset your password this way please message a Gatherling Administrator
            on the <a href="https://discord.gg/2VJ8Fa6">Discord</a></p>
    <?php
}

function printNewPasswordForm($token): void
{
    ?>
        <p>Enter your new password.</p>
        <form action="forgot.php" method="post">
            <input type="hidden" name="view" value="new_password" />
            <input type="hidden" name="token" value="<?= htmlentities($token) ?>" />
            <table class="form">
                <?php
                    print_password_input('New Password', 'password');
                    print_submit('Reset Password');
                ?>
            </table>
        </form>
    <?php
}

function sendLoginLink($player): bool
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

function generateSecureResetLink($name): string
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

function resetPassword($token, $newPassword): bool
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
