<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use Gatherling\Data\Db;

// This needs a separate file because Gatherling\Data\Db uses some of the other helpers
// and we don't want an infinite dependency loop.

function db(): Db
{
    static $db;

    if (!$db) {
        $db = new Db();
    }

    return $db;
}
