<?php

declare(strict_types=1);

use Gatherling\Views\JsonResponse;

use function Gatherling\Helpers\server;
use function Safe\simplexml_load_string;

require_once __DIR__ . '/bootstrap.php';

function main(): never
{
    $data = filter_input(INPUT_POST, 'data');
    if ($data === false || $data === null) {
        throw new \RuntimeException('No data provided');
    }
    $xml = simplexml_load_string($data) or exit('Error: Cannot create object');
    $quantities = ['main' => [], 'side' => []];
    $deck = ['main' => [], 'side' => []];

    for ($i = 0; $i < count($xml->Cards); $i++) {
        $section = $xml->Cards[$i]['Sideboard'] == 'false' ? 'main' : 'side';
        $name = strval($xml->Cards[$i]['Name']);
        $quantities[$section][$name] = ($quantities[$section][$name] ?? 0) + intval($xml->Cards[$i]['Quantity']);
    }

    foreach ($quantities as $section => $cards) {
        foreach ($cards as $name => $qty) {
            $deck[$section][] = "$qty $name";
        }
    }

    (new JsonResponse($deck))->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
