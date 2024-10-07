<?php

declare(strict_types=1);

use Gatherling\Models\Deck;
use Gatherling\Models\Player;
use Gatherling\Views\Redirect;
use Gatherling\Views\Pages\DeckDownload;

use function Gatherling\Views\server;

require_once 'lib.php';

function main(): void
{
    $id = $_GET['id'] ?? $_POST['id'] ?? null;
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
