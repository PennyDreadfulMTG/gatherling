<?php

declare(strict_types=1);

use Gatherling\Auth\LoginError;
use Gatherling\Views\Pages\Login;
use Gatherling\Auth\Login as LoginHelper;

require_once 'lib.php';

function main(): void
{
    $mode = $_REQUEST['mode'] ?? null;

    if ($mode == 'Log In with Discord') {
        redirect('auth.php');
    }

    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    $result = LoginHelper::login($username, $password);

    if ($result->success) {
        header('Cache-control: private');
        $_SESSION['username'] = $username;
        $target = $_REQUEST['target'] ?? 'player.php';
        if ($result->hasError(LoginError::PASSWORD_TOO_SHORT)) {
            $target = 'player.php?mode=changepass&tooshort=true';
        }
        header("location: $target");
    }

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
    $page->send();
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
