<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use function Safe\strtotime;

class HostActiveEvents extends Component
{
    /** @var array<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalueDisplay: string, link: string, isOngoing: bool, currentRound: int, settingsLink: string, registrationLink: string, matchesLink: string, standingsLink: string, structureSummary: string, startTime: Time}> */
    public array $events = [];
    public Icon $playersIcon;
    public Icon $structureIcon;
    public Icon $standingsIcon;

    /** @param array<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalueDisplay: string, link: string, isOngoing: bool, currentRound: int, settingsLink: string, registrationLink: string, matchesLink: string, standingsLink: string, structureSummary: string}> $events */
    public function __construct(array $events)
    {
        $this->playersIcon = new Icon('lucide:users');
        $this->structureIcon = new Icon('lucide:trophy');
        $this->standingsIcon = new Icon('lucide:chevron-right');

        $now = time();
        foreach ($events as $event) {
            $event['startTime'] = new Time(strtotime($event['start']), $now);
            $this->events[] = $event;
        }
    }
}
