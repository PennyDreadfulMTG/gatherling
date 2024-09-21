<?php

use Gatherling\Data\DB;
use Gatherling\Models\Deck;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Format;
use Gatherling\Models\Player;
use Gatherling\Models\Database;
use Gatherling\Models\Standings;
use Gatherling\Views\Pages\PlayerEventList;
use Gatherling\Views\Pages\EventReport;

require_once 'lib.php';

function main()
{
    $eventName = $_GET['event'] ?? $_GET['name'] ?? null;
    if ($eventName && Event::exists($eventName)) {
        $event = new Event($eventName);
        $notYetStarted = DB::value('SELECT `start` > NOW() AS okay FROM events WHERE `name` = :name', ['name' => $event->name]);
        $canPrereg = $event->prereg_allowed && $notYetStarted;
        $page = new EventReport($event, $canPrereg);
    } else {
        $format = $_GET['format'] ?? '';
        $series = $_GET['series'] ?? '';
        $season = $_GET['season'] ?? '';
        $events = eventList($format, $series, $season);
        $page = new PlayerEventList($format, $series, $season, $events);
    }
    $page->send();
}

function eventList(string $format, string $series, string $season): array
{
    $sql = '
        SELECT
            e.name,
            e.format,
            COUNT(DISTINCT n.player) AS players,
            e.host,
            e.start,
            e.finalized,
            e.cohost,
            e.series,
            e.season
        FROM
            events e
        LEFT OUTER JOIN
            entries AS n ON n.event_id = e.id
        WHERE
            e.start < NOW()';

    $params = [];

    if (!empty($format)) {
        $sql .= ' AND e.format = :format';
        $params['format'] = $format;
    }

    if (!empty($series)) {
        $sql .= ' AND e.series = :series';
        $params['series'] = $series;
    }

    if (!empty($season)) {
        $sql .= ' AND e.season = :season';
        $params['season'] = $season;
    }

    $sql .= '
        GROUP BY
            e.name
        ORDER BY
            e.start DESC
        LIMIT 100';

    return DB::select($sql, $params);
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
