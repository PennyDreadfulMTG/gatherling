<?php

namespace Gatherling;

use Gatherling\Models\Formats;
use Gatherling\Models\Player;

require_once __DIR__.'/../lib.php';

function main(): void
{
    if (PHP_SAPI != 'cli' && $_SERVER['REQUEST_METHOD'] == 'GET') { // unauthorized POST is okay
        ini_set('max_execution_time', 300);
        if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
            redirect('index.php');
        }
    }
    set_time_limit(0);
    Formats::updateDefaultFormats();

    // maybe we want to do these things we get an exception? if so we'll need to throw a more specific exception
    //             Log::info("Please add $set to the database");
    //        } else {
    //            redirect("util/insertcardset.php?cardsetcode={$set}&return=util/updateDefaultFormats.php");
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
