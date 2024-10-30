<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class HostActiveEvents extends Component
{
    public Icon $playersIcon;
    public Icon $structureIcon;
    public Icon $standingsIcon;

    /** @param array<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalueDisplay: string}> $events */
    public function __construct(public array $events)
    {
        $this->playersIcon = new Icon('lucide:users');
        $this->structureIcon = new Icon('lucide:trophy');
        $this->standingsIcon = new Icon('lucide:chevron-right');
    }
}
