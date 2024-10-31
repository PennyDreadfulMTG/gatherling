<?php

declare(strict_types=1);

use Gatherling\Models\Deck;
use Gatherling\Models\Player;
use Gatherling\Views\Redirect;
use Gatherling\Views\Pages\DeckDownload;

use function Gatherling\Helpers\get;
use function Gatherling\Helpers\post;
use function Gatherling\Helpers\server;

require_once 'lib.php';

function main(): never
{
    $id = get()->optionalInt('id') ?? post()->optionalInt('id') ?? null;
    if (!$id) {
        (new Redirect('player.php'))->send();
    }
    $deck = new Deck($id);
    if ($deck->new || !$deck->canView(Player::loginName())) {
        (new Redirect('player.php'))->send();
    }
    $response = new DeckDownload($deck);
    $response->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
