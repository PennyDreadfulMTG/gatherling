<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Series;

use function Safe\strtotime;

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
            if ($event->hastrophy) {
                continue;
            }
            $finalists = $event->getFinalists();
            $winningPlayer = $winningDeck = null;
            $hasWinner = false;
            foreach ($finalists as $finalist) {
                if ($finalist['medal'] == '1st') {
                    $winningPlayer = $finalist['player'];
                    $winningDeck = $finalist['deck'] !== null ? new Deck($finalist['deck']) : null;
                    $hasWinner = true;
                }
            }
            $startTime = $event->start ? new Time(strtotime($event->start), $now) : null;
            $this->eventsMissingTrophies[] = [
                'hasWinner' => $hasWinner,
                'eventName' => $event->name,
                'eventLink' => 'event.php?name=' . rawurlencode($event->name),
                'startTime' => $startTime,
                'playerLink' => 'profile.php?player=' . rawurlencode($winningPlayer ?? ''),
                'playerName' => $winningPlayer ?? '',
                'deckLink' => $winningDeck ? new DeckLink($winningDeck) : null,
            ];
        }
    }
}
