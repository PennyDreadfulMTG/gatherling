<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use function Safe\strtotime;

class HostEvents extends Component
{
    /** @var list<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalueDisplay: string, link: string, isOngoing: bool, currentRound: int, settingsLink: string, registrationLink: string, matchesLink: string, standingsLink: string, structureSummary: string, startTime: Time}> */
    public array $pendingEvents = [];
    /** @var list<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalueDisplay: string, link: string, isOngoing: bool, currentRound: int, settingsLink: string, registrationLink: string, matchesLink: string, standingsLink: string, structureSummary: string, startTime: Time}> */
    public array $activeEvents = [];

    public bool $hasPendingEvents;
    public bool $hasActiveEvents;

    /**
     * @param list<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalueDisplay: string, link: string, isOngoing: bool, currentRound: int, settingsLink: string, registrationLink: string, matchesLink: string, standingsLink: string, structureSummary: string}> $pendingEvents
     * @param list<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalueDisplay: string, link: string, isOngoing: bool, currentRound: int, settingsLink: string, registrationLink: string, matchesLink: string, standingsLink: string, structureSummary: string}> $activeEvents
     */
    public function __construct(array $pendingEvents, array $activeEvents)
    {
        $this->hasPendingEvents = count($pendingEvents) > 0;
        $this->hasActiveEvents = count($activeEvents) > 0;

        $now = time();
        foreach ($pendingEvents as $event) {
            $event['startTime'] = new Time(strtotime($event['start']), $now);
            $this->pendingEvents[] = $event;
        }
        foreach ($activeEvents as $event) {
            $event['startTime'] = new Time(strtotime($event['start']), $now);
            $this->activeEvents[] = $event;
        }
    }
}
