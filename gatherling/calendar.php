<?php

declare(strict_types=1);

use Gatherling\Models\CalendarEventDto;
use Gatherling\Views\ICal;

use function Gatherling\Helpers\db;
use function Gatherling\Helpers\server;

require_once __DIR__ . '/bootstrap.php';

function main(): never
{
    $name = 'Gatherling Tournament Schedule';
    $description = 'Magic Player Run Events on Magic: The Gathering Online';
    $ourEvents = events();
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
function events(): array
{
    $sql = "
        SELECT UNIX_TIMESTAMP(start) AS d, name, threadurl
          FROM events
         WHERE start >= NOW() - INTERVAL 2 WEEK
      ORDER BY start";
    return db()->select($sql, CalendarEventDto::class);
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
