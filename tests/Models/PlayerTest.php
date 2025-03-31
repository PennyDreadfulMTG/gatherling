<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Standings;
use Gatherling\Tests\Support\TestCases\DatabaseCase;
use Safe\DateTimeImmutable;

use function Gatherling\Helpers\parseCardsWithQuantity;

final class PlayerTest extends DatabaseCase
{
    public function testFindOrCreateByName(): void
    {
        $player = Player::findOrCreateByName('test');
        self::assertEquals('test', $player->name);
        self::assertNull($player->password);

        $player->password = 'password';
        $player->save();
        self::assertEquals('password', $player->password);

        $player2 = Player::findOrCreateByName('test');
        self::assertEquals($player, $player2);
        self::assertEquals('password', $player2->password);
    }

    public function testFindByName(): void
    {
        $player = Player::findByName('foo');
        self::assertNull($player);

        Player::findOrCreateByName('foo');

        $player = Player::findByName('foo');
        self::assertNotNull($player);

        $player2 = Player::findByName('bar');
        self::assertNull($player2);
    }

    public function testOrganizersSeries(): void
    {
        $player = Player::findOrCreateByName('An Organizer');
        self::assertEmpty($player->organizersSeries());

        $player->save();
        self::assertEmpty($player->organizersSeries());

        $series = new Series('');
        $series->name = 'My Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '12:00:00';
        $series->active = 1;
        $series->save();
        self::assertEmpty($player->organizersSeries());

        $player->super = 1;
        $player->save();
        self::assertContains($series->name, $player->organizersSeries());

        $player->super = 0;
        $player->save();
        self::assertEmpty($player->organizersSeries());

        self::assertNotEmpty($player->name);
        $series->addOrganizer($player->name);
        self::assertEquals([$series->name], $player->organizersSeries());
    }

    public function testGetMatchesEvent(): void
    {
        $event = $this->createTestEvent(['mainrounds' => 3, 'finalrounds' => 1]);

        // Create test players
        $player1 = Player::findOrCreateByName('TestPlayer1');
        $player2 = Player::findOrCreateByName('TestPlayer2');

        // Add players to event
        $event->addPlayer($player1->name);
        $event->addPlayer($player2->name);

        // Create and assign decks to players
        $this->insertDeck($player1->name, $event, '60 Swamp', '');
        $this->insertDeck($player2->name, $event, '60 Island', '');

        // Verify no matches exist yet
        $matches = $player1->getMatchesEvent($event->name);
        self::assertCount(0, $matches, 'Player should have no matches before event starts');

        // Start event
        $event->startEvent(true);

        // Get matches for player1
        $matches = $player1->getMatchesEvent($event->name);
        self::assertGreaterThan(0, count($matches), 'Player should have matches after event starts');

        // Verify match properties
        foreach ($matches as $match) {
            self::assertTrue(
                $match->playera === $player1->name || $match->playerb === $player1->name,
                'Match should involve the player'
            );
            self::assertEquals($event->mainid, $match->subevent, 'Match should be in main event');
        }

        // Create a match in finals
        $event->addMatch(
            new Standings($event->name, $player1->name),
            new Standings($event->name, $player2->name),
            4, // Round > mainrounds
            'P'
        );

        // Get matches again and verify ordering
        $matches = $player1->getMatchesEvent($event->name);
        self::assertGreaterThan(1, count($matches), 'Player should have multiple matches');

        // Verify matches are ordered by subevent timing and round
        $lastMatch = null;
        foreach ($matches as $match) {
            if ($lastMatch !== null) {
                self::assertLessThanOrEqual(
                    $match->round,
                    $lastMatch->round,
                    'Matches should be ordered by round'
                );
            }
            $lastMatch = $match;
        }
    }
}
