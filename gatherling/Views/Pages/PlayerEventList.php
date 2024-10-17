<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\EventDto;
use Gatherling\Views\Components\FormatDropMenu;
use Gatherling\Views\Components\SeasonDropMenu;
use Gatherling\Views\Components\SeriesDropMenu;

class PlayerEventList extends Page
{
    public FormatDropMenu $formatDropMenu;
    public SeriesDropMenu $seriesDropMenu;
    public SeasonDropMenu $seasonDropMenu;
    /** @var list<array<string, int|string>> */
    public array $events;
    public bool $hasMore;

    /** @param list<EventDto> $events */
    public function __construct(string $format, string $series, string $season, array $events)
    {
        parent::__construct();

        $this->formatDropMenu = new FormatDropMenu($format, true);
        $this->seriesDropMenu = new SeriesDropMenu($series, 'All');
        $this->seasonDropMenu = new SeasonDropMenu($season, 'All');

        $this->events = [];
        foreach ($events as $event) {
            $this->events[] = [
                'eventReportLink' => 'eventreport.php?event=' . rawurlencode($event->name),
                'name' => $event->name,
                'format' => $event->format,
                'numPlayers' => $event->players,
                'host' => $event->host,
                'cohost' => $event->cohost ?? '',
            ];
        }

        $this->hasMore = count($events) >= 100;
    }
}
