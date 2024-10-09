<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Matchup;
use Gatherling\Models\RecentWinnerDTO;
use Gatherling\Models\UpcomingEventDTO;
use Gatherling\Views\Pages\Home;

use function Gatherling\Views\server;

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

/** @return list<UpcomingEventDTO> */
function getUpcomingEvents(): array
{
    $sql = '
        SELECT
            UNIX_TIMESTAMP(start) AS d, format, name
        FROM
            events
        WHERE
            start > NOW() AND private = 0
        ORDER BY
            start ASC
        LIMIT 20';
    return DB::select($sql, UpcomingEventDTO::class);
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
    $winners = DB::select($sql, RecentWinnerDTO::class);
    $results = [];
    foreach ($winners as $winner) {
        $deck = new Deck($winner->id);
        $results[] = [
            'event' => $winner->event,
            'player' => $winner->player,
            'name' => $winner->name,
            'id' => $winner->id,
            'manaSymbolSafe' => $deck->getColorImages(),
        ];
    }
    return $results;
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
