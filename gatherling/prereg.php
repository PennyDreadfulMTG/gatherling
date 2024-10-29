<?php

declare(strict_types=1);

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Views\LoginRedirect;
use Gatherling\Views\Redirect;

use function Gatherling\Helpers\get;
use function Gatherling\Helpers\server;

require_once 'lib.php';

function main(): void
{
    $prevent_registration = 0;
    $player = Player::getSessionPlayer();

    if ($player == null) {
        (new LoginRedirect('player.php'))->send();
    }

    if (!isset($_GET['event']) || !isset($_GET['action'])) {
        (new Redirect('player.php'))->send();
    }

    $event = new Event(get()->string('event', ''));
    $series = new Series($event->series);

    $playerIsBanned = $series->isPlayerBanned($player->name);
    if ($playerIsBanned) {
        (new Redirect('bannedplayer.php'))->send();
    }

    if ($event->prereg_allowed != 1) {
        (new Redirect('player.php'))->send();
    }

    if ($event->finalized) {
        // No more changes
        (new Redirect('player.php'))->send();
    }

    // check for max registerd players
    if ($event->isFull()) {
        $prevent_registration = 1;
    }

    if ($series->discord_require_membership && $series->discord_guild_id) {
        $found = 0;
        if (!isset($_SESSION['DISCORD_TOKEN'])) {
            (new Redirect('auth.php'))->send();
        }

        require __DIR__ . '/authlib.php';
        global $provider;

        $token = load_cached_token();
        $guilds = get_user_guilds($token);
        foreach ($guilds as $g) {
            if (intval($g['id']) == $series->discord_guild_id) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $prevent_registration = 1;
        }
    }

    $location = 'player.php';
    if ($_GET['action'] == 'reg' && $prevent_registration != 1) {
        $event->addPlayer($player->name);
        $ename = $event->id;
        $location = "deck.php?player={$player->name}&event={$ename}&mode=create";
    } elseif ($_GET['action'] == 'unreg') {
        $event->removeEntry($player->name);
    }

    (new Redirect($location))->send();
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
