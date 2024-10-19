<?php

declare(strict_types=1);

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;

use function Gatherling\Helpers\get;

require_once 'lib.php';
$prevent_registration = 0;
$player = Player::getSessionPlayer();

if ($player == null) {
    linkToLogin('your Player Control Panel', 'player.php');
}

if (!isset($_GET['event']) || !isset($_GET['action'])) {
    header('Location: player.php');
    exit;
}

$event = new Event(get()->string('event', ''));
$series = new Series($event->series);

$playerIsBanned = $series->isPlayerBanned($player->name);
if ($playerIsBanned) {
    header('Location: bannedplayer.php');
    exit;
}

if ($event->prereg_allowed != 1) {
    header('Location: player.php');
    exit;
}

if ($event->finalized) {
    // No more changes
    header('Location: player.php');
    exit;
}

// check for max registerd players
if ($event->isFull()) {
    $prevent_registration = 1;
}

if ($series->discord_require_membership && $series->discord_guild_id) {
    $found = 0;
    if (!isset($_SESSION['DISCORD_TOKEN'])) {
        header('Location: auth.php');
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
if ($_GET['action'] == 'reg' and $prevent_registration != 1) {
    $event->addPlayer($player->name);
    $ename = $event->id;
    $location = "deck.php?player={$player->name}&event={$ename}&mode=create";
} elseif ($_GET['action'] == 'unreg') {
    $event->removeEntry($player->name);
}

header("Location: {$location}");
