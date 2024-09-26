<?php

declare(strict_types=1);

use Gatherling\Models\Player;
use Gatherling\Auth\Registration;
use Gatherling\Views\Pages\Register;

require_once 'lib.php';
require_once 'lib_form_helper.php';

function main(): void
{
    $message = '';
    if (isset($_POST['pw1'])) {
        $username = $_POST['username'] ?? '';
        $pw1 = $_POST['pw1'] ?? '';
        $pw2 = $_POST['pw2'] ?? '';
        $email = $_POST['email'] ?? '';
        $emailStatus = $_POST['emailStatus'] ?? 0;
        $timezone = (float) $_POST['timezone'];
        $discordId = $_POST['discordId'] ?? null;
        $discordName = $_POST['discordName'] ?? null;
        $code = Registration::register($username, $pw1, $pw2, $email, $emailStatus, $timezone, $discordId, $discordName);
        if ($code == 0) {
            redirect('player.php');
        } elseif ($code == -1) {
            $message = "Passwords don't match. Please go back and try again.";
        } elseif ($code == -3) {
            $message = 'A password has already been created for this account.';
            linkToLogin('your Player Control Panel', 'player.php', $message, trim($_POST['username']));
        }
    }
    $showRegForm = !isset($_POST['pw1']);
    $page = new Register($showRegForm, $message);
    $page->send();
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
