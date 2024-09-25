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
    protected Event $event;
    protected Player $player;

    protected function setUp(): void
    {
        parent::setUp();

        $event = new Event('');
        $event->name = 'Test Event';
        $event->start = '2024-01-01';
        $event->kvalue = 16;
        $event->format = 'Standard';
        $event->save();

        $this->event = new Event($event->name);

        $this->player = new Player('');
        $this->player->name = 'testplayer';
        $this->player->save();

        $this->assertTrue($this->event->addPlayer($this->player->name));
    }

    public function testSaveWithInvalidDecklist(): void
    {
        $deck = new Deck(0);
        $deck->name = 'Name';
        $deck->archetype = 'Aggro';
        $deck->notes = '';
        $deck->playername = $this->player->name;
        $deck->eventname = $this->event->name;
        $deck->event_id = $this->event->id;

        $deck->maindeck_cards = parseCardsWithQuantity("4 Torbran, Thane of Red Fell\n56 Mountain");
        $deck->sideboard_cards = parseCardsWithQuantity("4 Yarus, Roar of the Old Gods\n11 Forest");
        $deck->save();
    }
}
