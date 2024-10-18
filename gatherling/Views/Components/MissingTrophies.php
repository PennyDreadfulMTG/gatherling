<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Series;

class MissingTrophies extends Component
{
    public bool $noRecentEvents;
    /** @var array<array{hasWinner: bool, eventName: string, eventLink: string, startTime: ?Time, playerLink: string, playerName: string, deckLink: ?DeckLink}> */
    public array $eventsMissingTrophies;

    public function __construct(Series $series)
    {
        $recentEvents = $series->getRecentEvents(1000);

        $this->noRecentEvents = count($recentEvents) == 0;

        $now = time();
        foreach ($recentEvents as $event) {
            if (!$event->hastrophy) {
                $finalists = $event->getFinalists();
                $winningPlayer = $winningDeck = null;
                $hasWinner = false;
                foreach ($finalists as $finalist) {
                    if ($finalist['medal'] == '1st') {
                        $winningPlayer = $finalist['player'];
                        $winningDeck = new Deck($finalist['deck']);
                        $hasWinner = true;
                    }
                }
                $eventStartTime = $event->start ? strtotime($event->start) : null;
                $startTime = $eventStartTime ? new Time($eventStartTime, $now) : null;
                $this->eventsMissingTrophies[] = [
                    'hasWinner' => $hasWinner,
                    'eventName' => $event->name ?? '',
                    'eventLink' => 'event.php?name=' . rawurlencode($event->name ?? ''),
                    'startTime' => $startTime,
                    'playerLink' => 'profile.php?player=' . rawurlencode($winningPlayer ?? ''),
                    'playerName' => $winningPlayer ?? '',
                    'deckLink' => $winningDeck ? new DeckLink($winningDeck) : null,
                ];
            }
        }
    }
}
