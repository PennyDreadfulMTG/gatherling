<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Tests\Support\TestCases\DatabaseCase;
use Safe\DateTimeImmutable;

use function Gatherling\Helpers\parseCardsWithQuantity;

class DeckTest extends DatabaseCase
{
    protected Event $event;
    protected Player $player;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = $this->createTestEvent([
            'name' => 'Test Event',
            'series' => 'Test Series',
            'start' => new DateTimeImmutable('2024-01-01'),
            'host' => 'JimmyTheHost'
        ]);

        $this->player = new Player('');
        $this->player->name = 'testplayer';
        $this->player->save();

        self::assertTrue($this->event->addPlayer($this->player->name));
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

    public function testSave(): void
    {
        $deck = new Deck(0);
        $deck->name = 'Name';
        $deck->archetype = 'Aggro';
        $deck->notes = '';
        $deck->playername = $this->player->name;
        $deck->eventname = $this->event->name;
        $deck->event_id = $this->event->id;
        $deck->save();

        $deck = new Deck($deck->id);
        self::assertFalse($deck->new);
        self::assertEquals($deck->playername, $this->player->name);
        self::assertEquals($deck->eventname, $this->event->name);
    }
}
