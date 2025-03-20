<?php

declare(strict_types=1);

use Gatherling\Models\Image;
use Gatherling\Models\ImageDto;
use Gatherling\Views\ImageResponse;

use function Gatherling\Helpers\db;
use function Gatherling\Helpers\server;

require_once __DIR__ . '/bootstrap.php';

function main(): never
{
    $sql = 'SELECT logo AS image, imgtype AS type, imgsize AS size FROM series WHERE name = :series';
    $args = ['series' => $_GET['series']];
    $values = db()->selectOnlyOrNull($sql, ImageDto::class, $args);
    $image = Image::fromValues($values);
    $response = new ImageResponse($image);
    $response->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
