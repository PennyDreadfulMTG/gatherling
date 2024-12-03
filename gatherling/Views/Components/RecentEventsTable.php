<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Series;
use Safe\DateTimeImmutable;

use function Safe\strtotime;

class RecentEventsTable extends Component
{
    /** @var array<array{eventLink: string, eventName: string, startTime: ?Time, playerCount: int, host: string, cohost: string}> */
    public array $events = [];

    public function __construct(public Series $series)
    {
        $recentEvents = $series->getRecentEvents();
        $now = new DateTimeImmutable();
        foreach ($recentEvents as $event) {
            $startTime = new Time($event->start, $now);
            $this->events[] = [
                'eventLink' => 'event.php?name=' . rawurlencode($event->name),
                'eventName' => $event->name,
                'startTime' => $startTime,
                'playerCount' => $event->getPlayerCount(),
                'host' => $event->host,
                'cohost' => $event->cohost ?? '',

            ];
        }
    }
}
