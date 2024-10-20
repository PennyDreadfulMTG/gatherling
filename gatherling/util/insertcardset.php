<?php

declare(strict_types=1);

use Gatherling\Models\CardSet;
use Gatherling\Models\Player;

use function Gatherling\Views\request;
use function Gatherling\Views\server;

set_time_limit(0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/../lib.php';

function main(): void
{
    global $argv;
    global $CONFIG;

    if (PHP_SAPI == 'cli') {
        if (!isset($argv[1])) {
            throw new \Exception('No set provided');
        }
        if (strlen($argv[1]) < 4) {
            CardSet::insert($argv[1]);
        } else {
            CardSet::insertFromLocation($argv[1]);
        }
    } else { // CGI
        if (!(Player::getSessionPlayer()?->isSuper() ?? false)) {
            redirect('index.php');
        }
        if (isset($_REQUEST['cardsetcode'])) {
            // Due to an ancient bug in MS-DOS, Windows-based computers can't handle Conflux correctly.
            $code = request()->string('cardsetcode') == 'CON' ? 'CON_' : request()->string('cardsetcode');
            CardSet::insert($code);
        } elseif (isset($_FILES['cardsetfile'])) {
            $tmp_name = $_FILES['cardsetfile']['tmp_name'];
            assert(is_string($tmp_name));
            CardSet::insertFromLocation($tmp_name);
        } else {
            throw new \Exception('No set provided');
        }
    }

    if (isset($_REQUEST['return'])) {
        $args = '';
        if (isset($_REQUEST['ret_args'])) {
            $args = $_REQUEST['ret_args'];
        }

        echo "Return to <a href='{$CONFIG['base_url']}{$_REQUEST['return']}?{$args}'>{$_REQUEST['return']}</a><br/>";
        echo '<script>';
        echo "  window.setTimeout(() => { location.href = \"{$CONFIG['base_url']}{$_REQUEST['return']}?{$args}\"}, 5000);";
        echo '</script>';
    }
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
