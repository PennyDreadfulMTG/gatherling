<?php

declare(strict_types=1);

use Gatherling\Data\DB;
use Gatherling\Views\ICal;
use Gatherling\Models\Database;

require_once 'lib.php';

function main(): void
{
    $name = 'Gatherling Tournament Schedule';
    $description = 'Magic Player Run Events on Magic: The Gathering Online';
    $ourEvents = array_merge(lastNEvents(50), upcomingEvents());
    $calendarEvents = [];
    foreach ($ourEvents as $event) {
        $calendarEvents[] = [
            'start' => $event['d'],
            // All events will last for 5 hours for now.
            // TODO: Make this scale based on number of rounds.
            'end' => $event['d'] + (60 * 60 * 5),
            'name' => $event['name'],
            'url' => $event['threadurl'],
        ];
    }
    $page = new ICal($name, $description, $calendarEvents);
    $page->send();
}

/** @return list<array{d: int, name: string, threadurl: string}> */
function lastNEvents(int $n): array
{
    return events('start < NOW()', $n);
}

/** @return list<array{d: int, name: string, threadurl: string}> */
function upcomingEvents(): array
{
    return events('start > NOW()');
}

/** @return list<array{d: int, name: string, threadurl: string}> */
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
    return DB::select($sql, $params);
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
