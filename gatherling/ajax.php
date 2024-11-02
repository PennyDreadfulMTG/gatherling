<?php

declare(strict_types=1);

require_once 'api.php';

use function Gatherling\Helpers\server;

function ajax_main(): never
{
    if (!isset($_REQUEST['action'])) {
        // The below is for backwards compat
        if (isset($_GET['deck'])) {
            $_REQUEST['action'] = 'deckinfo';
        } elseif (isset($_GET['addplayer']) && isset($_GET['event'])) {
            $_REQUEST['action'] = 'addplayer';
        } elseif (isset($_GET['delplayer']) && isset($_GET['event'])) {
            $_REQUEST['action'] = 'delplayer';
        } elseif (isset($_GET['dropplayer']) && isset($_GET['event'])) {
            $_REQUEST['action'] = 'dropplayer';
        }
    }
    main();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    ajax_main();
}
