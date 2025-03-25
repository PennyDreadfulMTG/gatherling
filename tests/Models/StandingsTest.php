<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Standings;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Event;
use Gatherling\Models\StandingsMode;
use Gatherling\Tests\Support\TestCases\DatabaseCase;
use Safe\DateTimeImmutable;

final class StandingsTest extends DatabaseCase
{
    public function testGetEventStandings(): void
    {
        $event = $this->createTestEvent(['name' => 'getEventStandings Test Event']);
        $eventName = $event->name;
        $players = ['Player1', 'Player2', 'Player3', 'Player4', 'Player5'];

        foreach ($players as $player) {
            Player::findOrCreateByName($player);
        }
        // Create test standings with different scores and matched states
        $standing1 = new Standings($eventName, 'Player1', 1);
        $standing1->save();
        $standing1->new = false;
        $standing1->matches_played = 3;
        $standing1->matches_won = 2;
        $standing1->active = 1;
        $standing1->score = 3;
        $standing1->matched = 0;
        $standing1->OP_Match = 0.55;
        $standing1->PL_Game = 0.6;
        $standing1->OP_Game = 0.5;
        $standing1->games_won = 4;
        $standing1->games_played = 6;
        $standing1->byes = 0;
        $standing1->draws = 0;
        $standing1->save();

        $standing2 = new Standings($eventName, 'Player2', 2);
        $standing2->save();
        $standing2->new = false;
        $standing2->matches_played = 2;
        $standing2->matches_won = 2;
        $standing2->active = 1;
        $standing2->score = 9;
        $standing2->matched = 1;
        $standing2->OP_Match = 0.75;
        $standing2->PL_Game = 0.8;
        $standing2->OP_Game = 0.7;
        $standing2->games_won = 6;
        $standing2->games_played = 6;
        $standing2->byes = 0;
        $standing2->draws = 0;
        $standing2->save();

        $standing3 = new Standings($eventName, 'Player3', 3);
        $standing3->save();
        $standing3->new = false;
        $standing3->matches_played = 3;
        $standing3->matches_won = 1;
        $standing3->active = 1;
        $standing3->score = 6;
        $standing3->matched = 0;
        $standing3->OP_Match = 0.65;
        $standing3->PL_Game = 0.7;
        $standing3->OP_Game = 0.6;
        $standing3->games_won = 5;
        $standing3->games_played = 9;
        $standing3->byes = 0;
        $standing3->draws = 1;
        $standing3->save();

        $standing4 = new Standings($eventName, 'Player4', 4);
        $standing4->save();
        $standing4->new = false;
        $standing4->matches_played = 3;
        $standing4->matches_won = 1;
        $standing4->active = 1;
        $standing4->score = 6;
        $standing4->matched = 0;
        $standing4->OP_Match = 0.70;
        $standing4->PL_Game = 0.7;
        $standing4->OP_Game = 0.6;
        $standing4->games_won = 4;
        $standing4->games_played = 8;
        $standing4->byes = 0;
        $standing4->draws = 1;
        $standing4->save();

        $standing5 = new Standings($eventName, 'Player5', 5);
        $standing5->save();
        $standing5->new = false;
        $standing5->matches_played = 3;
        $standing5->matches_won = 0;
        $standing5->active = 1;
        $standing5->score = 6;
        $standing5->matched = 0;
        $standing5->OP_Match = 0.65;
        $standing5->PL_Game = 0.8;
        $standing5->OP_Game = 0.6;
        $standing5->games_won = 0;
        $standing5->games_played = 0;
        $standing5->byes = 0;
        $standing5->draws = 0;
        $standing5->save();

        // Test getting all standings (isactive = 0)
        $standings = Standings::getEventStandings($eventName);
        self::assertCount(5, $standings);
        self::assertEquals('Player2', $standings[0]->player); // Highest score
        self::assertEquals('Player4', $standings[1]->player); // Same score, highest OP_Match
        self::assertEquals('Player5', $standings[2]->player); // Same score, lower OP_Match but higher PL_Game
        self::assertEquals('Player3', $standings[3]->player); // Same score, lowest tiebreakers
        self::assertEquals('Player1', $standings[4]->player); // Lowest score

        // Test getting unmatched active players (isactive = 1)
        $standings = Standings::getEventStandings($eventName, StandingsMode::NEXT_UNPAIRED);
        self::assertCount(1, $standings);
        self::assertContains($standings[0]->player, ['Player1', 'Player3', 'Player4', 'Player5']); // Only unmatched players

        // Test getting active players by seed (isactive = 2)
        $standings = Standings::getEventStandings($eventName, StandingsMode::SEEDED);
        self::assertCount(5, $standings);
        self::assertEquals('Player1', $standings[0]->player);
        self::assertEquals('Player2', $standings[1]->player);
        self::assertEquals('Player3', $standings[2]->player);
        self::assertEquals('Player4', $standings[3]->player);
        self::assertEquals('Player5', $standings[4]->player);

        // Test getting active players by score (isactive = 3)
        $standings = Standings::getEventStandings($eventName, StandingsMode::ACTIVE_STANDINGS);
        self::assertCount(5, $standings);
        self::assertEquals('Player2', $standings[0]->player); // Highest score
        self::assertEquals('Player4', $standings[1]->player); // Same score, highest OP_Match
        self::assertEquals('Player5', $standings[2]->player); // Same score, lower OP_Match but higher PL_Game
        self::assertEquals('Player3', $standings[3]->player); // Same score, lowest tiebreakers
        self::assertEquals('Player1', $standings[4]->player); // Lowest score
    }

    public function testGetOpponents(): void
    {
        $event = $this->createTestEvent([
            'name' => 'getOpponents Test Event',
            'series' => 'getOpponents Test Series',
            'start' => new DateTimeImmutable('2025-01-01'),
            'host' => 'TestHost',
            'mainrounds' => 3,
            'finalrounds' => 1
        ]);

        $eventName = $event->name;
        $players = ['Player1', 'Player2', 'Player3', 'Player4'];

        foreach ($players as $player) {
            Player::findOrCreateByName($player);
        }

        // Create test standings
        $standing1 = new Standings($eventName, 'Player1', 1);
        $standing1->save();
        $standing2 = new Standings($eventName, 'Player2', 2);
        $standing2->save();
        $standing3 = new Standings($eventName, 'Player3', 3);
        $standing3->save();
        $standing4 = new Standings($eventName, 'Player4', 4);
        $standing4->save();

        // Create test matches using Event model
        $event->addMatch($standing1, $standing2, 1, 'A', 2, 0);
        $event->addMatch($standing1, $standing3, 2, 'B', 0, 2);

        self::assertNotNull($event->mainid);
        self::assertNotNull($event->finalid);

        // Test getting opponents
        $opponents = $standing1->getOpponents($eventName, $event->mainid);
        self::assertCount(2, $opponents);
        self::assertContains('Player2', array_map(fn($o) => $o->player, $opponents));
        self::assertContains('Player3', array_map(fn($o) => $o->player, $opponents));

        // Test with no matches
        $opponents = $standing4->getOpponents($eventName, $event->mainid);
        self::assertCount(0, $opponents);
    }

    public function testGetAvailableLeagueOpponents(): void
    {
        $event = $this->createTestEvent([
            'name' => 'getAvailableLeagueOpponents Test Event',
            'series' => 'Test Series',
            'start' => new DateTimeImmutable('2025-01-01'),
            'host' => 'TestHost',
            'mainrounds' => 3,
            'finalrounds' => 1,
            'mainstruct' => 'League',
            'finalstruct' => 'Single Elimination'
        ]);

        $eventName = $event->name;
        $players = ['Player1', 'Player2', 'Player3', 'Player4'];

        foreach ($players as $player) {
            Player::findOrCreateByName($player);
        }

        // Create test standings
        $standing1 = new Standings($eventName, 'Player1', 1);
        $standing1->save();
        $standing2 = new Standings($eventName, 'Player2', 2);
        $standing2->save();
        $standing3 = new Standings($eventName, 'Player3', 3);
        $standing3->save();
        $standing4 = new Standings($eventName, 'Player4', 4);
        $standing4->save();

        // Create test matches using Event model
        $event->addMatch($standing1, $standing2, 1, 'A', 2, 0);

        self::assertNotNull($event->mainid);

        // Test getting available opponents
        $opponents = $standing1->getAvailableLeagueOpponents($event->mainid, 1, 3);
        self::assertCount(2, $opponents);
        self::assertContains('Player3', $opponents);
        self::assertContains('Player4', $opponents);

        // Test with league length reached
        $opponents = $standing1->getAvailableLeagueOpponents($event->mainid, 1, 1);
        self::assertCount(0, $opponents);

        // Test with no matches
        $opponents = $standing1->getAvailableLeagueOpponents($event->mainid, 0, 3);
        self::assertCount(0, $opponents);
    }
}
