<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Models\Image;
use Gatherling\Models\ImageDTO;
use Gatherling\Views\ImageResponse;

use function Gatherling\Views\server;

require_once 'lib.php';

function main(): void
{
    $sql = 'SELECT logo AS image, imgtype AS type, imgsize AS size FROM series WHERE name = :series';
    $args = ['series' => $_GET['series']];
    $values = DB::selectOnlyOrNull($sql, ImageDTO::class, $args);
    $image = Image::fromValues($values);
    $response = new ImageResponse($image);
    $response->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
