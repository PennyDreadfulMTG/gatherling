<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Models\CalendarEventDto;
use Gatherling\Views\ICal;

use function Gatherling\Views\server;

require_once 'lib.php';

function main(): void
{
    $name = 'Gatherling Tournament Schedule';
    $description = 'Magic Player Run Events on Magic: The Gathering Online';
    $ourEvents = array_merge(lastNEvents(50), upcomingEvents());
    $calendarEvents = [];
    foreach ($ourEvents as $event) {
        $calendarEvents[] = [
            'start' => $event->d,
            // All events will last for 5 hours for now.
            // TODO: Make this scale based on number of rounds.
            'end' => $event->d + (60 * 60 * 5),
            'name' => $event->name,
            'url' => $event->threadurl,
        ];
    }
    $page = new ICal($name, $description, $calendarEvents);
    $page->send();
}

/** @return list<CalendarEventDto> */
function lastNEvents(int $n): array
{
    return events('start < NOW()', $n);
}

/** @return list<CalendarEventDto> */
function upcomingEvents(): array
{
    return events('start > NOW()');
}

/** @return list<CalendarEventDto> */
function events(string $where, int $limit = 0): array
{
    $sql = "
        SELECT
            UNIX_TIMESTAMP(start) AS d,
            name,
            threadurl
        FROM
            events
        WHERE
            {$where}";
    $params = [];
    if ($limit) {
        $sql .= ' ORDER BY start DESC LIMIT :limit';
        $params['limit'] = $limit;
    }
    return DB::select($sql, CalendarEventDto::class, $params);
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
