<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Models\Event;
use Gatherling\Models\EventDto;
use Gatherling\Views\Pages\EventReport;
use Gatherling\Views\Pages\PlayerEventList;

use function Gatherling\Views\get;
use function Gatherling\Views\server;

require_once 'lib.php';

function main(): void
{
    $eventName = get()->optionalString('event') ?? get()->optionalString('name');
    if ($eventName && Event::exists($eventName)) {
        $event = new Event($eventName);
        $notYetStarted = DB::value('SELECT `start` > NOW() AS okay FROM events WHERE `name` = :name', ['name' => $event->name]);
        $canPrereg = $event->prereg_allowed && $notYetStarted;
        $page = new EventReport($event, $canPrereg);
    } else {
        $format = get()->string('format', '');
        $series = get()->string('series', '');
        $season = get()->string('season', '');
        $events = eventList($format, $series, $season);
        $page = new PlayerEventList($format, $series, $season, $events);
    }
    $page->send();
}

/** @return list<EventDto> */
function eventList(string $format, string $series, string $season): array
{
    $sql = '
        SELECT
            e.name,
            e.format,
            COUNT(DISTINCT n.player) AS players,
            e.host,
            e.cohost
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

    return DB::select($sql, EventDto::class, $params);
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
