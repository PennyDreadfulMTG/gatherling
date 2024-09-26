<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Matchup;
use Gatherling\Views\Pages\Home;

require_once 'lib.php';

function main(): void
{
    $activeEvents = Event::getActiveEvents(false);
    $upcomingEvents = getUpcomingEvents();
    $stats = stats();
    $player = Player::getSessionPlayer();
    if (!$player instanceof Player) {
        $player = null;
    }
    $mostRecentHostedEvent = $player ? Event::findMostRecentByHost($player->name) : null;
    $recentWinners = recentWinners();
    $page = new Home($activeEvents, $upcomingEvents, $stats, $player, $mostRecentHostedEvent, $recentWinners);
    $page->send();
}

/** @return list<array{d: int, format: string, series: string, name: string, threadurl: string}> */
function getUpcomingEvents(): array
{
    $sql = '
        SELECT
            UNIX_TIMESTAMP(start) AS d, format, series, name, threadurl
        FROM
            events
        WHERE
            start > NOW() AND private = 0
        ORDER BY
            start ASC
        LIMIT 20';
    return DB::select($sql);
}

/** @return array<string, int> */
function stats(): array
{
    return [
        'decks' => Deck::uniqueCount(),
        'matches' => Matchup::count(),
        'events' => Event::count(),
        'activePlayers' => Player::activeCount(),
        'verifiedPlayers' => Player::verifiedCount(),
    ];
}

/** @return list<array<string, int|string>> */
function recentWinners(): array
{
    $sql = "
        SELECT
            e.name as `event`, n.player, d.name, d.id
        FROM
            entries n, decks d, events e
        WHERE
            n.medal = '1st' AND d.id = n.deck AND e.id = n.event_id
        ORDER BY
            e.start DESC
        LIMIT 10";
    $winners = DB::select($sql);
    foreach ($winners as &$winner) {
        $deck = new Deck($winner['id']);
        $winner['manaSymbolSafe'] = $deck->getColorImages();
    }
    return $winners;
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
