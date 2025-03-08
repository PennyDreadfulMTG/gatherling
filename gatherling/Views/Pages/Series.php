<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use DateInterval;
use Gatherling\Views\Components\Time;
use Gatherling\Views\Components\EventReportLink;
use Gatherling\Models\Series as SeriesModel;
use Safe\DateTimeImmutable;

use function Safe\strtotime;

class Series extends Page
{
    /** @var array<array{seriesName: string, logoSrc: string, formatName: ?string, hosts: string, regularTime: string, masterDocumentLink: string, season: int|string|null, eventReportLink: EventReportLink|null, nextEventStart: Time|null}> */
    public array $activeSeries;

    /** @param list<string> $activeSeriesNames */
    public function __construct(array $activeSeriesNames)
    {
        parent::__construct('Event Information', false);
        $this->activeSeries = [];
        $now = new DateTimeImmutable();
        foreach ($activeSeriesNames as $seriesName) {
            $series = new SeriesModel($seriesName);
            $mostRecentEvent = $series->mostRecentEvent();
            $nextEvent = $series->nextEvent();
            $mostRecentEventDoesntCount = !$mostRecentEvent || $mostRecentEvent->start < $now->sub(new DateInterval('P4W'));
            if ($mostRecentEventDoesntCount && !$nextEvent) {
                continue;
            }
            $formatName = $nextEvent ? $nextEvent->format : $mostRecentEvent->format;
            $regularTime = $series->start_day ? date('l, h:i a', strtotime($series->start_time)) : "Not scheduled yet";
            $masterDocumentLink = '';
            if ($series->this_season_master_link) {
                $masterDocumentLink = $series->this_season_master_link;
            } elseif ($mostRecentEvent) {
                $masterDocumentLink = $mostRecentEvent->threadurl;
            }
            $season = $series->this_season_season;
            $nextEventStart = $nextEvent ? new Time($nextEvent->start, $now) : null;
            $this->activeSeries[] = [
                'seriesName' => $seriesName,
                'logoSrc' => SeriesModel::logoSrc($seriesName),
                'formatName' => $formatName,
                'hosts' => implode(", ", array_slice($series->organizers, 0, 3)),
                'regularTime' => $regularTime,
                'masterDocumentLink' => $masterDocumentLink,
                'season' => $season,
                'eventReportLink' => $mostRecentEvent ? new EventReportLink($mostRecentEvent->name) : null,
                'nextEventStart' => $nextEventStart,
            ];
        }
    }
}
