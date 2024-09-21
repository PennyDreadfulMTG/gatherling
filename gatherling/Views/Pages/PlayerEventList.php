<?php

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\FormatDropMenu;
use Gatherling\Views\Components\SeasonDropMenu;
use Gatherling\Views\Components\SeriesDropMenu;

class PlayerEventList extends Page
{

    public FormatDropMenu $formatDropMenu;
    public SeriesDropMenu $seriesDropMenu;
    public SeasonDropMenu $seasonDropMenu;
    public array $events;
    public bool $hasMore;

    public function __construct(string $format, string $series, string $season, array $events)
    {
        parent::__construct();

        $this->formatDropMenu = new FormatDropMenu($format, true);
        $this->seriesDropMenu = new SeriesDropMenu($series, true);
        $this->seasonDropMenu = new SeasonDropMenu($season, true);

        $this->events = [];
        foreach ($events as $event) {
            $this->events[] = [
                'eventReportLink' => 'eventreport.php?event=' . rawurlencode($event['name']),
                'name' => $event['name'],
                'format' => $event['format'],
                'numPlayers' => $event['players'],
                'host' => $event['host'],
                'cohost' => $event['cohost'],
            ];
        }

        $this->hasMore = count($events) >= 100;
    }
}
