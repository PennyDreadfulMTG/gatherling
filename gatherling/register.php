<?php

declare(strict_types=1);

use Gatherling\Auth\Registration;
use Gatherling\Views\LoginRedirect;
use Gatherling\Views\Redirect;
use Gatherling\Views\Pages\Register;

use function Gatherling\Helpers\post;
use function Gatherling\Helpers\server;
use function Gatherling\Helpers\session;

require_once 'lib.php';

function main(): void
{
    $message = '';
    if (isset($_POST['pw1'])) {
        $username = post()->string('username', '');
        $pw1 = post()->string('pw1', '');
        $pw2 = post()->string('pw2', '');
        $email = post()->string('email', '');
        $emailStatus = post()->int('emailstatus', 0);
        $timezone = post()->float('timezone', -5.0);
        $discordId = session()->optionalString('DISCORD_ID');
        $discordName = session()->optionalString('DISCORD_NAME');
        $code = Registration::register($username, $pw1, $pw2, $email, $emailStatus, $timezone, $discordId, $discordName);
        if ($code == 0) {
            (new Redirect('player.php'))->send();
        } elseif ($code == -1) {
            $message = "Passwords don't match. Please go back and try again.";
        } elseif ($code == -3) {
            $message = 'A password has already been created for this account.';
            (new LoginRedirect('player.php', $message, trim($username)))->send();
        }
    }
    $showRegForm = !isset($_POST['pw1']);
    $page = new Register($showRegForm, $message);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
