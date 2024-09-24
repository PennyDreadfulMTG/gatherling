<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Views\Components\Component;

class SeasonStandings extends Component
{
    public string $seriesName;
    public string $season;
    public array $seasonEvents;
    public array $players;

    public function __construct(Series $series, string $season)
    {
        $seasonEventNames = $series->getSeasonEventNames($season);
        $points = $series->seasonPointsTable($season);
        $cutoff = $series->getSeasonCutoff($season);
        uasort($points, [self::class, 'reverseTotalSort']);

        $seasonEvents = [];
        foreach ($seasonEventNames as $eventName) {
            $shortName = preg_replace("/^{$series->name} /", '', $eventName);
            $reportLink = 'eventreport.php?event=' . rawurlencode($eventName);
            $seasonEvents[] = [
                'shortName' => $shortName,
                'reportLink' => $reportLink,
            ];
        }

        $players = [];
        $count = 0;
        foreach ($points as $playerName => $playerPoints) {
            $player = new Player($playerName);
            $count++;
            $classes = '';
            if ($count % 2 != 0) {
                $classes = 'odd';
            }
            if ($count == $cutoff) {
                $classes .= ' cutoff';
            }
            $events = [];
            foreach ($seasonEventNames as $eventName) {
                $why = $points = null;
                if (isset($playerPoints[$eventName])) {
                    if (is_array($playerPoints[$eventName])) {
                        $why = $playerPoints[$eventName]['why'];
                        $points = $playerPoints[$eventName]['points'];
                    } else {
                        $points = $playerPoints[$eventName];
                    }
                }
                $events[] = [
                    'points' => $points,
                    'why' => $why,
                ];
            }
            $players[] = [
                'classes' => $classes,
                'count' => $count,
                'playerLink' => new PlayerLink($player),
                'totalPoints' => $playerPoints['.total'],
                'events' => $events,
            ];
        }

        $this->seriesName = $series->name;
        $this->season = $season;
        $this->seasonEvents = $seasonEvents;
        $this->players = $players;

        parent::__construct('partials/seasonStandings');
    }

    private static function reverseTotalSort($a, $b)
    {
        if ($a['.total'] == $b['.total']) {
            return 0;
        }
        return ($a['.total'] < $b['.total']) ? 1 : -1;
    }
}
