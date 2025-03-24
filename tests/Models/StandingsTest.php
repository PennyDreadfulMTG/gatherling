<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Standings;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Event;
use Gatherling\Models\StandingsMode;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

final class StandingsTest extends TestCase
{
    public function testGetEventStandings(): void
    {
        $host = Player::findOrCreateByName('TestHost');
        $series = new Series('');
        $series->name = 'getEventStandings Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '00:00:00';
        $series->active = 1;
        $series->save();

        $event = new Event();
        $event->name = 'getEventStandings Test Event';
        $event->host = $host->name;
        $event->start = new DateTimeImmutable('2025-01-01');
        $event->series = $series->name;
        $event->format = 'Standard';
        $event->client = 1;
        $event->mainrounds = 3;
        $event->mainstruct = 'Swiss';
        $event->finalrounds = 1;
        $event->finalstruct = 'Single Elimination';
        $event->save();

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
        $this->assertCount(5, $standings);
        $this->assertEquals('Player2', $standings[0]->player); // Highest score
        $this->assertEquals('Player4', $standings[1]->player); // Same score, highest OP_Match
        $this->assertEquals('Player5', $standings[2]->player); // Same score, lower OP_Match but higher PL_Game
        $this->assertEquals('Player3', $standings[3]->player); // Same score, lowest tiebreakers
        $this->assertEquals('Player1', $standings[4]->player); // Lowest score

        // Test getting unmatched active players (isactive = 1)
        $standings = Standings::getEventStandings($eventName, StandingsMode::NEXT_UNPAIRED);
        $this->assertCount(1, $standings);
        $this->assertContains($standings[0]->player, ['Player1', 'Player3', 'Player4', 'Player5']); // Only unmatched players

        // Test getting active players by seed (isactive = 2)
        $standings = Standings::getEventStandings($eventName, StandingsMode::SEEDED);
        $this->assertCount(5, $standings);
        $this->assertEquals('Player1', $standings[0]->player);
        $this->assertEquals('Player2', $standings[1]->player);
        $this->assertEquals('Player3', $standings[2]->player);
        $this->assertEquals('Player4', $standings[3]->player);
        $this->assertEquals('Player5', $standings[4]->player);

        // Test getting active players by score (isactive = 3)
        $standings = Standings::getEventStandings($eventName, StandingsMode::ACTIVE_STANDINGS);
        $this->assertCount(5, $standings);
        $this->assertEquals('Player2', $standings[0]->player); // Highest score
        $this->assertEquals('Player4', $standings[1]->player); // Same score, highest OP_Match
        $this->assertEquals('Player5', $standings[2]->player); // Same score, lower OP_Match but higher PL_Game
        $this->assertEquals('Player3', $standings[3]->player); // Same score, lowest tiebreakers
        $this->assertEquals('Player1', $standings[4]->player); // Lowest score
    }

    public function testGetOpponents(): void
    {
        $host = Player::findOrCreateByName('TestHost');
        $series = new Series('');
        $series->name = 'getOpponents Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '00:00:00';
        $series->active = 1;
        $series->save();

        $event = new Event();
        $event->name = 'getOpponents Test Event';
        $event->host = $host->name;
        $event->start = new DateTimeImmutable('2025-01-01');
        $event->series = $series->name;
        $event->format = 'Standard';
        $event->client = 1;
        $event->mainrounds = 3;
        $event->mainstruct = 'Swiss';
        $event->finalrounds = 1;
        $event->finalstruct = 'Single Elimination';
        $event->save();
        $event = new Event($event->name);

        $eventName = $event->name;
        $players = ['Player1', 'Player2', 'Player3'];

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

        // Create test matches using Event model
        $event->addMatch($standing1, $standing2, 1, 'A', 2, 0);
        $event->addMatch($standing1, $standing3, 2, 'B', 0, 2);

        $this->assertNotNull($event->mainid);
        $this->assertNotNull($event->finalid);

        // Test getting opponents
        $opponents = $standing1->getOpponents($eventName, $event->mainid, 1);
        $this->assertCount(2, $opponents);
        $this->assertContains('Player2', array_map(fn($o) => $o->player, $opponents));
        $this->assertContains('Player3', array_map(fn($o) => $o->player, $opponents));

        // Test with no matches
        $opponents = $standing1->getOpponents($eventName, $event->mainid, 0);
        $this->assertCount(0, $opponents);
    }

    public function testGetAvailableLeagueOpponents(): void
    {
        $host = Player::findOrCreateByName('TestHost');
        $series = new Series('');
        $series->name = 'Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '00:00:00';
        $series->active = 1;
        $series->save();

        $event = new Event();
        $event->name = 'getAvailableLeagueOpponents Test Event';
        $event->host = $host->name;
        $event->start = new DateTimeImmutable('2025-01-01');
        $event->series = $series->name;
        $event->format = 'Standard';
        $event->client = 1;
        $event->mainrounds = 3;
        $event->mainstruct = 'League';
        $event->finalrounds = 1;
        $event->finalstruct = 'Single Elimination';
        $event->save();
        $event = new Event($event->name);

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

        $this->assertNotNull($event->mainid);

        // Test getting available opponents
        $opponents = $standing1->getAvailableLeagueOpponents($event->mainid, 1, 3);
        $this->assertCount(2, $opponents);
        $this->assertContains('Player3', $opponents);
        $this->assertContains('Player4', $opponents);

        // Test with league length reached
        $opponents = $standing1->getAvailableLeagueOpponents($event->mainid, 1, 1);
        $this->assertCount(0, $opponents);

        // Test with no matches
        $opponents = $standing1->getAvailableLeagueOpponents($event->mainid, 0, 3);
        $this->assertCount(0, $opponents);
    }
}
