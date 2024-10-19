<?php

declare(strict_types=1);

use Gatherling\Auth\LoginError;
use Gatherling\Views\Pages\Login;
use Gatherling\Auth\Login as LoginHelper;

use function Gatherling\Helpers\post;
use function Gatherling\Helpers\request;
use function Gatherling\Helpers\server;
use function Gatherling\Helpers\session;

require_once 'lib.php';

function main(): void
{
    $mode = $_REQUEST['mode'] ?? null;

    if ($mode == 'Log In with Discord') {
        redirect('auth.php');
    }

    $username = post()->optionalString('username');
    $password = post()->optionalString('password');

    $result = LoginHelper::login($username, $password);

    if ($result->success) {
        header('Cache-control: private');
        $_SESSION['username'] = $username;
        $target = request()->string('target', 'player.php');
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
        request()->string('message', ''),
        $username ?? '',
        $target ?? '',
        session()->string('DISCORD_ID', ''),
    );
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
