<?php

declare(strict_types=1);

use Gatherling\Models\CardSet;
use Gatherling\Models\Player;
use Gatherling\Views\Pages\InsertCardSet;
use Gatherling\Views\Redirect;

use function Gatherling\Helpers\files;
use function Gatherling\Helpers\request;
use function Gatherling\Helpers\server;

set_time_limit(0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/lib.php';

function main(): void
{
    global $argv;
    if (PHP_SAPI == 'cli') {
        if (!isset($argv[1])) {
            throw new \Exception('No set provided');
        }
        if (strlen($argv[1]) < 4) {
            $messages = CardSet::insert($argv[1]);
        } else {
            $messages = CardSet::insertFromLocation($argv[1]);
        }
        foreach ($messages as $message) {
            print("$message\n");
        }
        return;
    }

    if (!(Player::getSessionPlayer()?->isSuper() ?? false)) {
        (new Redirect('index.php'))->send();
    }
    $cardSetCode = request()->optionalString('cardsetcode');
    if ($cardSetCode) {
        // Due to an ancient bug in MS-DOS, Windows-based computers can't handle Conflux correctly.
        $code = $cardSetCode == 'CON' ? 'CON_' : $cardSetCode;
        $messages = CardSet::insert($code);
    } elseif ($file = files()->optionalFile('cardsetfile')) {
        $tmp_name = $file->tmp_name;
        $messages = CardSet::insertFromLocation($tmp_name);
    } else {
        throw new \Exception('No set provided');
    }
    $page = new InsertCardSet($messages);
    $page->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
