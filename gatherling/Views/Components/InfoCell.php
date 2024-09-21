<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Player;

class InfoCell extends Component
{
    public string $threadLink;
    public string $eventName;
    public string $eventDate;
    public string $eventFormat;
    public int $playerCount;
    public int $deckCount;
    public bool $isActive;
    public float $percentReported;
    public array $subEvents;
    public PlayerLink $hostLink;
    public string $reportLink;
    public string $seasonLeaderboardLink;

    public function __construct(Event $event)
    {
        parent::__construct('partials/infoCell');
        $this->threadLink = $event->threadurl;
        $this->eventName = $event->name;
        $this->eventDate = $event->start ? date('j F Y', strtotime($event->start)) : '';
        $this->eventFormat = $event->format;
        $this->playerCount = $event->getPlayerCount();
        $this->deckCount = count($event->getDecks());
        $this->isActive = (bool) $event->active;
        if ($event->active) {
            $this->percentReported = $this->playerCount === 0 ? 0 : round($this->deckCount * 100 / $this->playerCount);
        }
        $this->subEvents = [];
        foreach ($event->getSubevents() as $subevent) {
            if ($subevent->type != 'Single Elimination') {
                $this->subEvents[] = "{$subevent->rounds} rounds {$subevent->type}";
            } else {
                $finalists = pow(2, $subevent->rounds);
                $this->subEvents[] = "Top $finalists playoff";
            }
        }
        if ($event->host) {
            $host = new Player($event->host);
            $this->hostLink = new PlayerLink($host);
        }
        $this->reportLink = $event->reporturl;
        $this->seasonLeaderboardLink = 'seriesreport.php?series=' . rawurlencode($event->series) . '&season=' . rawurlencode((string) $event->season);
    }
}
