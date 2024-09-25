<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Deck;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

class DeckTest extends DatabaseCase
{
    public function testSave(): void
    {
        $event = new Event('');
        $event->name = 'Test Event';
        $event->start = '2024-01-01';
        $event->kvalue = 16;
        $event->format = 'Standard';
        $event->save();

        $event = new Event($event->name);

        $player = new Player('');
        $player->name = 'testplayer';
        $player->save();

        $this->assertTrue($event->addPlayer($player->name));

        $deck = new Deck(0);
        $deck->name = 'Name';
        $deck->archetype = 'Aggro';
        $deck->notes = '';
        $deck->playername = $player->name;
        $deck->eventname = $event->name;
        $deck->event_id = $event->id;
        $deck->maindeck_cards = parseCardsWithQuantity('60 Island');
        $deck->sideboard_cards = parseCardsWithQuantity('15 Swamp');
        $deck->save();

        // BAKERT assert something
    }
}
