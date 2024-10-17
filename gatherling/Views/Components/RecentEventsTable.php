<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Series;

class RecentEventsTable extends Component
{
    /** @var array<array{eventLink: string, eventName: string, startTime: ?Time, playerCount: int, host: string, cohost: string}> */
    public array $events;

    public function __construct(public Series $series)
    {
        parent::__construct('partials/recentEventsTable');
        $recentEvents = $series->getRecentEvents();
        $now = time();
        foreach ($recentEvents as $event) {
            $eventStartTime = $event->start ? strtotime($event->start) : null;
            $startTime = $eventStartTime ? new Time($eventStartTime, $now) : null;
            $this->events[] = [
                'eventLink' => 'event.php?name=' . rawurlencode($event->name ?? ''),
                'eventName' => $event->name ?? '',
                'startTime' => $startTime,
                'playerCount' => $event->getPlayerCount(),
                'host' => $event->host ?? '',
                'cohost' => $event->cohost ?? '',

            ];
        }
    }
}
