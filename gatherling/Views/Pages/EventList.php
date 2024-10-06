<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Player;
use Gatherling\Models\Database;
use Gatherling\Views\Components\FormatDropMenu;
use Gatherling\Views\Components\SeasonDropMenu;
use Gatherling\Views\Components\SeriesDropMenu;

use function Gatherling\Views\get;

class EventList extends Page
{
    public string $title = 'Event Host Control Panel';
    public FormatDropMenu $formatDropMenu;
    public SeriesDropMenu $seriesDropMenu;
    public SeasonDropMenu $seasonDropMenu;
    public bool $hasPlayerSeries;
    /** @var list<array{name: string, format: string, players: int, host: string, start: string, active: int, finalized: int, cohost: string, series: string, kvalue: int}> */
    public array $results;
    public bool $hasMore;

    public function __construct(string $seriesName, string $season)
    {
        parent::__construct();
        $player = Player::getSessionPlayer();
        $playerSeries = $player->organizersSeries();

        $result = queryEvents($player, $playerSeries, $seriesName);
        $seriesShown = $results = $finalizedResults = [];

        while ($event = $result->fetch_assoc()) {
            if ($event['finalized'] == 1) {
                $finalizedResults[] = $event;
            } else {
                $results[] = $event;
            }
            $seriesShown[] = $event['series'];
        }
        $results = array_merge($results, $finalizedResults);

        $hasMore = $result->num_rows == 100;
        $result->close();

        if ($seriesName) {
            $seriesShown = $playerSeries;
        } else {
            $seriesShown = array_unique($seriesShown);
        }

        if (!isset($_GET['format'])) {
            $_GET['format'] = '';
        }

        $kvalueMap = [
            0  => 'none',
            8  => 'Casual',
            16 => 'Regular',
            24 => 'Large',
            32 => 'Championship',
        ];

        foreach ($results as &$event) {
            $event['kvalueDisplay'] = $kvalueMap[$event['kvalue']] ?? '';
            $event['link'] = 'event.php?name='.rawurlencode($event['name']);
            $event['isOngoing'] = $event['finalized'] == 0 && $event['active'] == 1;
        }

        $this->formatDropMenu = new FormatDropMenu(get()->optionalString('format'), true);
        $this->seriesDropMenu = new SeriesDropMenu($seriesName, 'All', $seriesShown);
        $this->seasonDropMenu = new SeasonDropMenu($season, true);
        $this->hasPlayerSeries = count($playerSeries) > 0;
        $this->results = $results;
        $this->hasMore = $hasMore;
    }
}

/** @param list<string> $playerSeries */
function queryEvents(Player $player, array $playerSeries, string $seriesName): \mysqli_result|bool
{
    $db = Database::getConnection();
    $seriesEscaped = [];
    foreach ($playerSeries as $oneSeries) {
        $seriesEscaped[] = $db->escape_string($oneSeries);
    }
    $seriesString = '"' . implode('","', $seriesEscaped) . '"';

    $query = "SELECT e.name AS name, e.format AS format,
        COUNT(DISTINCT n.player) AS players, e.host AS host, e.start AS start,
        e.active, e.finalized, e.cohost, e.series, e.kvalue
        FROM events e
        LEFT OUTER JOIN entries AS n ON n.event_id = e.id
        WHERE (e.host = \"{$db->escape_string($player->name)}\"
            OR e.cohost = \"{$db->escape_string($player->name)}\"
            OR e.series IN (".$seriesString.'))';
    $format = get()->optionalString('format');
    if ($format) {
        $query = $query . " AND e.format=\"{$db->escape_string($format)}\" ";
    }
    if (strcmp($seriesName, '') != 0) {
        $query = $query." AND e.series=\"{$db->escape_string($seriesName)}\" ";
    }
    $season = get()->optionalString('season');
    if ($season) {
        $query = $query . " AND e.season=\"{$db->escape_string($season)}\" ";
    }
    $query = $query . ' GROUP BY e.name ORDER BY e.start DESC LIMIT 100';

    return $db->query($query);
}
