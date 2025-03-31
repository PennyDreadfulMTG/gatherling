<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
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

    public function testGetCastingCosts(): void
    {
        $deck = new Deck(0);
        $deck->name = 'Test Deck';
        $deck->archetype = 'Aggro';
        $deck->notes = '';
        $deck->playername = $this->player->name;
        $deck->eventname = $this->event->name;
        $deck->event_id = $this->event->id;
        $deck->format = 'Modern';

        // Add some cards with different converted mana costs
        $deck->maindeck_cards = [
            'Mountain' => 46, // Lands have CMC 0
            'Lightning Bolt' => 4, // CMC 1
            'Burning Inquiry' => 2, // CMC 1
            'Smuggler\'s Copter' => 2, // CMC 2
            'Dragon Whelp' => 4, // CMC 4
            'Metalwork Colossus' => 2, // CMC 11
        ];

        // Add some sideboard cards that shouldn't be counted
        $deck->sideboard_cards = [
            'Burning Inquiry' => 2, // Shouldn't be counted
            'Smuggler\'s Copter' => 2, // Shouldn't be counted
        ];

        $deck->save();

        self::assertEmpty($deck->errors);

        $costs = $deck->getCastingCosts();

        // Verify only non-zero CMC cards are included
        self::assertArrayNotHasKey(0, $costs, 'Lands should not be included');
        self::assertArrayNotHasKey(3, $costs, 'No three-mana spells');

        // Verify correct quantities for each CMC, without sideboard cards
        self::assertEquals(6, $costs[1], 'Should have 6 one-mana spells');
        self::assertEquals(2, $costs[2], 'Should have 2 two-mana spells');
        self::assertEquals(4, $costs[4], 'Should have 4 four-mana spells');
        self::assertEquals(2, $costs[11], 'Should have 2 eleven-mana spells');
    }
}
