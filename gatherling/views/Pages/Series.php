<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\Time;
use Gatherling\Views\Components\ReportLink;
use Gatherling\Models\Series as SeriesModel;

class Series extends Page
{
    public array $activeSeries;

    public function __construct(array $activeSeriesNames)
    {
        parent::__construct();
        $this->title = 'Event Information';
        $this->activeSeries = [];
        foreach ($activeSeriesNames as $seriesName) {
            $series = new SeriesModel($seriesName);
            $mostRecentEvent = $series->mostRecentEvent();
            $nextEvent = $series->nextEvent();
            $mostRecentEventDoesntCount = !$mostRecentEvent || !$mostRecentEvent->start || strtotime($mostRecentEvent->start) + (86400 * 7 * 4) < time();
            if ($mostRecentEventDoesntCount && !$nextEvent) {
                continue;
            }
            $formatName = $nextEvent ? $nextEvent->format : $mostRecentEvent->format;
            $regularTime = $series->start_day ? date('l, h:i a', strtotime($series->start_time)) : "Not scheduled yet";
            $masterDocumentLink = empty($series->this_season_master_link) ? $mostRecentEvent->threadurl : $series->this_season_master_link;
            $season = $series->this_season_season;
            $nextEventStart = $nextEvent && $nextEvent->start ? new Time(strtotime($nextEvent->start), time()) : null;
            $this->activeSeries[] = [
                'seriesName' => $seriesName,
                'logoSrc' => SeriesModel::logoSrc($seriesName),
                'formatName' => $formatName,
                'hosts' => implode(", ", array_slice($series->organizers, 0, 3)),
                'regularTime' => $regularTime,
                'masterDocumentLink' => $masterDocumentLink,
                'season' => $season,
                'reportLink' => new ReportLink($mostRecentEvent->name),
                'nextEventStart' => $nextEventStart,
            ];
        }
    }
}
