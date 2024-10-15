<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Log;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Exceptions\NotFoundException;

class RecentDecksTable extends Component
{
    /** @var array<array-key, array{medalSrc: string, deckLink: DeckLink, eventLink: string, eventName: string, recordString: string}> */
    public array $decks = [];

    public function __construct(Player $player)
    {
        parent::__construct('partials/recentDecksTable');
        $event = $player->getLastEventPlayed();
        if (is_null($event)) {
            return;
        }
        if (!$event->id || !$player->name || !$event->name) {
            throw new NotFoundException("Seeming invalid event data for ({$event->id}|{$event->name}|{$player->name}");
        }
        $entry = new Entry($event->id, $player->name);
        if ($entry->deck) {
            $decks = $player->getRecentDecks(6);
        } else {
            $decks = $player->getRecentDecks(5);
        }
        foreach ($decks as $deck) {
            if (!$deck->eventname) {
                Log::warning("I was looking at recent decks for {$player->name} and found a deck with no event name");
                continue;
            }
            $event = new Event($deck->eventname);
            $medalSrc = 'styles/images/' . rawurlencode($deck->medal ?? 'dot') . '.png';
            $deckLink = new DeckLink($deck);
            $targetUrl = $event->authCheck($player->name) ? 'event' : 'eventreport';
            $eventLink = $targetUrl . '.php?event=' . rawurlencode($event->name ?? '');
            $eventName = $event->name ?? '';
            $recordString = $deck->recordString();
            $this->decks[] = [
                'medalSrc' => $medalSrc,
                'deckLink' => $deckLink,
                'eventLink' => $eventLink,
                'eventName' => $eventName,
                'recordString' => $recordString,
            ];
        }
    }
}
