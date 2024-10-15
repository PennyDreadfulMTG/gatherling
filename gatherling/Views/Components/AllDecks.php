<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class AllDecks extends Component
{
    public string $upPlayer;
    /** @var array<array-key, array{medalSrc: string, recordString: string, deckLink: DeckLink, isValid: bool, eventLink: string, eventName: string, targetUrl: string}> */
    public array $decks;

    public function __construct(Player $player)
    {
        $decks = $player->getAllDecks();
        $this->upPlayer = strtoupper($player->name ?? '');

        foreach ($decks as $deck) {
            $event = $deck->getEvent();
            $targetUrl = $event->authCheck($player->name) ? 'event' : 'eventreport';
            $eventLink = $targetUrl . '.php?event=' . rawurlencode($event->name ?? '');
            $this->decks[] = [
                'medalSrc' => $deck->medal ? 'styles/images/' . rawurlencode($deck->medal) . '.png' : '',
                'recordString' => $deck->recordString(),
                'deckLink' => new DeckLink($deck),
                'isValid' => $deck->isValid(),
                'eventLink' => $eventLink,
                'eventName' => $event->name ?? '',
                'targetUrl' => $targetUrl,
            ];
        }
    }
}
