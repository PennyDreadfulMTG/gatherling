<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Models\Image;
use Gatherling\Models\Database;
use Gatherling\Views\ImageResponse;

require_once 'lib.php';

function main(): void
{
    $sql = 'SELECT image, type, size FROM trophies WHERE event = :event';
    $args = ['event' => $_GET['event']];
    $values = DB::selectOnlyOrNull($sql, $args);
    $image = Image::fromValues($values);
    $response = new ImageResponse($image);
    $response->send();
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
