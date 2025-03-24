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
    private function insertDeck(string $player, Event $event): void
    {
        $deck = new Deck(0);
        $deck->playername = $player;
        $deck->eventname = $event->name;
        $deck->event_id = $event->id;
        $deck->maindeck_cards = parseCardsWithQuantity('60 Swamp');
        $deck->save();
    }

    public function testFindOrCreateByName(): void
    {
        $player = Player::findOrCreateByName('test');
        $this->assertEquals('test', $player->name);
        $this->assertNull($player->password);

        $player->password = 'password';
        $player->save();
        $this->assertEquals('password', $player->password);

        $player2 = Player::findOrCreateByName('test');
        $this->assertEquals($player, $player2);
        $this->assertEquals('password', $player2->password);
    }

    public function testFindByName(): void
    {
        $player = Player::findByName('foo');
        $this->assertNull($player);

        Player::findOrCreateByName('foo');

        $player = Player::findByName('foo');
        $this->assertNotNull($player);

        $player2 = Player::findByName('bar');
        $this->assertNull($player2);
    }

    public function testOrganizersSeries(): void
    {
        $player = Player::findOrCreateByName('An Organizer');
        $this->assertEmpty($player->organizersSeries());

        $player->save();
        $this->assertEmpty($player->organizersSeries());

        $series = new Series('');
        $series->name = 'My Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '12:00:00';
        $series->active = 1;
        $series->save();
        $this->assertEmpty($player->organizersSeries());

        $player->super = 1;
        $player->save();
        $this->assertContains($series->name, $player->organizersSeries());

        $player->super = 0;
        $player->save();
        $this->assertEmpty($player->organizersSeries());

        $this->assertNotEmpty($player->name);
        $series->addOrganizer($player->name);
        $this->assertEquals([$series->name], $player->organizersSeries());
    }

    public function testGetMatchesEvent(): void
    {
        $series = new Series('');
        $series->name = 'getMatchesEvent Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '12:00:00';
        $series->active = 1;
        $series->save();

        $event = new Event('');
        $event->name = 'getMatchesEvent Test Event';
        $host = Player::findOrCreateByName('TestHost');
        $event->host = $host->name;
        $event->start = new DateTimeImmutable('2025-01-01');
        $event->series = $series->name;
        $event->format = 'Standard';
        $event->mainrounds = 3;
        $event->mainstruct = 'Swiss';
        $event->finalrounds = 1;
        $event->finalstruct = 'Single Elimination';
        $event->save();
        $event = new Event($event->name);

        // Create test players
        $player1 = Player::findOrCreateByName('TestPlayer1');
        $player2 = Player::findOrCreateByName('TestPlayer2');

        // Add players to event
        $event->addPlayer($player1->name);
        $event->addPlayer($player2->name);

        // Create and assign decks to players
        $this->insertDeck($player1->name, $event);
        $this->insertDeck($player2->name, $event);

        // Verify no matches exist yet
        $matches = $player1->getMatchesEvent($event->name);
        $this->assertCount(0, $matches, 'Player should have no matches before event starts');

        // Start event
        $event->startEvent(true);

        // Get matches for player1
        $matches = $player1->getMatchesEvent($event->name);
        $this->assertGreaterThan(0, count($matches), 'Player should have matches after event starts');

        // Verify match properties
        foreach ($matches as $match) {
            $this->assertTrue(
                $match->playera === $player1->name || $match->playerb === $player1->name,
                'Match should involve the player'
            );
            $this->assertEquals($event->mainid, $match->subevent, 'Match should be in main event');
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
        $this->assertGreaterThan(1, count($matches), 'Player should have multiple matches');

        // Verify matches are ordered by subevent timing and round
        $lastMatch = null;
        foreach ($matches as $match) {
            if ($lastMatch !== null) {
                $this->assertLessThanOrEqual(
                    $match->round,
                    $lastMatch->round,
                    'Matches should be ordered by round'
                );
            }
            $lastMatch = $match;
        }
    }
}
