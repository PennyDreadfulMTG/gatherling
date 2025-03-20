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
    public string $playerCountText;
    public int $deckCount;
    public bool $isActive;
    public float $percentReported;
    /** @var list<string> */
    public array $subevents;
    public PlayerLink $hostLink;
    public string $eventReportLink;
    public string $seasonLeaderboardLink;

    public function __construct(Event $event)
    {
        $this->threadLink = $event->threadurl;
        $this->eventName = $event->name;
        $this->eventDate = $event->start->format('j F Y');
        $this->eventFormat = $event->format;
        $playerCount = $event->getPlayerCount();
        $this->playerCountText = "$playerCount Player" . ($playerCount === 1 ? '' : 's');
        $this->deckCount = count($event->getDecks());
        $this->isActive = (bool) $event->active;
        if ($event->active) {
            $this->percentReported = $playerCount === 0 ? 0 : round($this->deckCount * 100 / $playerCount);
        }
        $this->subevents = [];
        foreach ($event->getSubevents() as $subevent) {
            if ($subevent->rounds === null || $subevent->rounds === 0) {
                continue;
            }
            if ($subevent->type != 'Single Elimination') {
                $this->subevents[] = "{$subevent->rounds} rounds {$subevent->type}";
            } else {
                $finalists = pow(2, $subevent->rounds);
                $this->subevents[] = "Top $finalists playoff";
            }
        }
        if ($event->host) {
            $host = new Player($event->host);
            $this->hostLink = new PlayerLink($host);
        }
        $this->eventReportLink = $event->reporturl;
        $this->seasonLeaderboardLink = 'seriesreport.php?series=' . rawurlencode($event->series ?? '') . '&season=' . rawurlencode((string) $event->season);
    }
}
