<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Models\Image;
use Gatherling\Models\ImageDto;
use Gatherling\Views\ImageResponse;

use function Gatherling\Helpers\server;

require_once 'lib.php';

function main(): void
{
    $sql = 'SELECT image, type, size FROM trophies WHERE event = :event';
    $args = ['event' => $_GET['event']];
    $values = DB::selectOnlyOrNull($sql, ImageDto::class, $args);
    $image = Image::fromValues($values);
    $response = new ImageResponse($image);
    $response->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
