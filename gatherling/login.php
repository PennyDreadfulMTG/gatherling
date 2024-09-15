<?php

use Gatherling\Models\Player;
use Gatherling\Views\Pages\Login;

require_once 'lib.php';

function main(): void
{
    $mode = $_REQUEST['mode'] ?? null;

    if ($mode == 'Log In with Discord') {
        redirect('auth.php');
    }

    $username = $_POST['username'] ?? null;
    $target = $_REQUEST['target'] ?? null;

    testLogin($mode, $username, $_POST['password'], $target);

    $loginFailed = isset($_POST['mode']);
    $ipAddressChanged = isset($_GET['ipaddresschanged']);

    $page = new Login(
        $loginFailed,
        $ipAddressChanged,
        $_REQUEST['message'] ?? '',
        $username ?? '',
        $target ?? '',
        $_SESSION['DISCORD_ID'] ?? '',
    );
    echo $page->render();
}

function testLogin(?string $mode, ?string $username, ?string $password, ?string $target): void
{
    if (!isset($username) || !isset($password)) {
        return;
    }
    $auth = Player::checkPassword($username, $password);
    // The $admin check allows an admin to su into any user without a password.
    $admin = Player::isLoggedIn() && Player::getSessionPlayer()->isSuper();
    if (!$auth && !$admin) {
        return;
    }
    header('Cache-control: private');
    $_SESSION['username'] = $username;
    if (!isset($target)) {
        $target = 'player.php';
    }
    if (strlen($_POST['password']) < 8 && !$admin) {
        $target = 'player.php?mode=changepass&tooshort=true';
    }
    header("location: $target");
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
