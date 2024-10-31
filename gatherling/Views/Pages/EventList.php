<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\HostedEventDto;
use Gatherling\Models\Player;
use Gatherling\Views\Components\FormatDropMenu;
use Gatherling\Views\Components\HostActiveEvents;
use Gatherling\Views\Components\SeasonDropMenu;
use Gatherling\Views\Components\SeriesDropMenu;

use function Gatherling\Helpers\db;
use function Gatherling\Helpers\get;

class EventList extends Page
{
    public ?HostActiveEvents $hostActiveEvents;
    public FormatDropMenu $formatDropMenu;
    public SeriesDropMenu $seriesDropMenu;
    public SeasonDropMenu $seasonDropMenu;
    public bool $hasPlayerSeries;
    /** @var list<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalueDisplay: string, link: string, isOngoing: bool, currentRound: int, settingsLink: string, registrationLink: string, matchesLink: string, standingsLink: string, structureSummary: string}> */
    public array $events = [];
    public bool $hasMore;

    public function __construct(string $seriesName, string $format, ?int $season)
    {
        parent::__construct('Event Host Control Panel');
        $player = Player::getSessionPlayer();
        $playerSeries = $player?->organizersSeries() ?? [];

        $events = queryEvents($player, $playerSeries, $seriesName, $format, $season);
        $hasMore = count($events) == 100;

        $kvalueMap = [
            0  => 'none',
            8  => 'Casual',
            16 => 'Regular',
            24 => 'Large',
            32 => 'Championship',
        ];

        $activeEvents = $seriesShown = [];
        foreach ($events as $event) {
            $seriesShown[] = $event->series;
            $baseLink = 'event.php?name=' . rawurlencode($event->name) . '&view=';
            $eventInfo = [
                'name' => $event->name,
                'format' => $event->format,
                'players' => $event->players,
                'host' => $event->host,
                'start' => $event->start,
                'active' => $event->active,
                'finalized' => $event->finalized,
                'cohost' => $event->cohost ?? '',
                'series' => $event->series,
                'kvalueDisplay' => $kvalueMap[$event->kvalue] ?? '',
                'link' => 'event.php?name=' . rawurlencode($event->name),
                'isOngoing' => $event->finalized == 0 && $event->active == 1,
                'currentRound' => $event->current_round,
                'settingsLink' => "{$baseLink}settings",
                'registrationLink' => "{$baseLink}reg",
                'matchesLink' => "{$baseLink}match",
                'standingsLink' => "{$baseLink}standings",
                'structureSummary' => (new Event($event->name))->structureSummary(),
            ];
            $this->events[] = $eventInfo;
            if ($event->active == 1 || (!$event->finalized && strtotime($event->start) <= strtotime('+1 day'))) {
                $activeEvents[] = $eventInfo;
            }
        }

        if ($seriesName) {
            $seriesShown = $playerSeries;
        } else {
            $seriesShown = array_values(array_unique($seriesShown));
        }

        $this->hostActiveEvents = count($activeEvents) > 0 ? new HostActiveEvents($activeEvents) : null;
        $this->formatDropMenu = new FormatDropMenu(get()->optionalString('format'), true);
        $this->seriesDropMenu = new SeriesDropMenu($seriesName, 'All', $seriesShown);
        $this->seasonDropMenu = new SeasonDropMenu($season, 'All');
        $this->hasPlayerSeries = count($playerSeries) > 0;
        $this->hasMore = $hasMore;
    }
}

/**
 * @param list<string> $playerSeries
 * @return list<HostedEventDto>
 */
function queryEvents(Player $player, array $playerSeries, string $seriesName, string $format, ?int $season): array
{
    $sql = '
        SELECT e.name, e.format, COUNT(DISTINCT n.player) AS players, e.host, e.start,
               e.active, e.finalized, e.cohost, e.series, e.kvalue, e.current_round
          FROM events e
     LEFT JOIN entries AS n ON n.event_id = e.id
        WHERE (e.host = :player_name OR e.cohost = :player_name OR e.series IN (:series_names))';
    $params = ['player_name' => $player->name, 'series_names' => $playerSeries];
    if ($format) {
        $sql .= ' AND e.format = :format';
        $params['format'] = $format;
    }
    if ($seriesName !== '') {
        $sql .= ' AND e.series = :series_name';
        $params['series_name'] = $seriesName;
    }
    if ($season !== null) {
        $sql .= ' AND e.season = :season';
        $params['season'] = $season;
    }
    $sql .= ' GROUP BY e.name ORDER BY e.finalized, e.start DESC LIMIT 100';

    return db()->select($sql, HostedEventDto::class, $params);
}
