<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Standings;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

use function Gatherling\Helpers\parseCardsWithQuantity;

class EventTest extends TestCase
{
    /**
     * @param array{
     *   series?: string,
     *   host?: string,
     *   cohost?: string,
     *   name?: string,
     *   start?: DateTimeImmutable,
     *   kvalue?: int,
     *   format?: string,
     *   season?: int,
     *   number?: int,
     *   mainstruct?: string,
     *   mainrounds?: int,
     *   finalstruct?: string,
     *   finalrounds?: int,
     *   threadurl?: string,
     *   reporturl?: string,
     *   metaurl?: string
     * } $overrides
     */
    private function createTestEvent(array $overrides = []): Event
    {
        /** @var ?Series */
        static $series = null;
        if (!isset($series)) {
            $series = new Series('');
            $series->name = 'Test Series for Test Event';
            $series->start_day = 'Monday';
            $series->start_time = '00:00:00';
            $series->active = 1;
            $series->save();
        }

        $host = Player::findOrCreateByName($overrides['host'] ?? 'Test Host');
        $cohost = null;
        if (isset($overrides['cohost'])) {
            $cohost = Player::findOrCreateByName($overrides['cohost']);
        }

        $event = new Event('');
        $event->name = $overrides['name'] ?? 'Test Event';
        $event->host = $host->name;
        $event->cohost = $cohost->name ?? null;
        $event->start = $overrides['start'] ?? new DateTimeImmutable('2025-06-01');
        $event->kvalue = $overrides['kvalue'] ?? 8;
        $event->format = $overrides['format'] ?? 'Standard';
        $event->series = $series->name;
        $event->season = $overrides['season'] ?? 1;
        $event->number = $overrides['number'] ?? 1;
        $event->mainstruct = $overrides['mainstruct'] ?? 'Swiss';
        $event->mainrounds = $overrides['mainrounds'] ?? 1;
        $event->finalstruct = $overrides['finalstruct'] ?? 'Single Elimination';
        $event->finalrounds = $overrides['finalrounds'] ?? 1;
        $event->threadurl = $overrides['threadurl'] ?? '';
        $event->reporturl = $overrides['reporturl'] ?? '';
        $event->metaurl = $overrides['metaurl'] ?? '';
        $event->save();
        $event = new Event($event->name);
        return $event;
    }

    public function testStructureSummary(): void
    {
        $event = new Event('');
        $event->mainstruct = 'Single Elimination';
        $event->mainrounds = 3;
        $event->finalstruct = 'League';
        $event->finalrounds = 1;
        $this->assertEquals('3 rounds of Single Elimination followed by  5 open matches', $event->structureSummary());
        $event->mainstruct = 'Swiss';
        $event->mainrounds = 6;
        $event->finalstruct = 'Single Elimination';
        $event->finalrounds = 3;
        $this->assertEquals('6 rounds of Swiss followed by Top 8 cut', $event->structureSummary());
    }

    public function testAssignTrophiesFromMatches(): void
    {
        $host = Player::findOrCreateByName('JimmyTheHost');

        $tests = [
            ['1st', '2nd'], // 2 players
            ['1st', '2nd', 'dot'], // 3 players
            array_merge( // 14 players
                ['1st', '2nd'],
                array_fill(0, 2, 't4'),
                array_fill(0, 10, 'dot')
            ),
            array_merge( // 60 players
                ['1st', '2nd'],
                array_fill(0, 2, 't4'),
                array_fill(0, 4, 't8'),
                array_fill(0, 52, 'dot')
            )
        ];

        foreach ($tests as $expectedMedals) {
            $numPlayers = count($expectedMedals);

            $event = $this->createTestEvent([
                'name' => $numPlayers . '_person_event_with_knockout',
                'host' => $host->name,
                'mainrounds' => 3,
                'finalrounds' => $numPlayers <= 7 ? 1 : ($numPlayers <= 16 ? 2 : 3)
            ]);

            for ($i = 1; $i <= $numPlayers; $i++) {
                $event->addPlayer("Player$i");
                $deck = new Deck(0);
                $deck->playername = "Player$i";
                $deck->eventname = $event->name;
                $deck->event_id = $event->id;
                $deck->maindeck_cards = parseCardsWithQuantity('60 Swamp');
                $deck->save();
            }

            $event->startEvent(true);
            $this->assertSame($numPlayers, count($event->getEntries()));

            $matches = $event->getRoundMatches(1);
            $this->assertEquals(ceil($numPlayers / 2), count($matches));
            for ($i = 1; $i <= $event->mainrounds + $event->finalrounds; $i++) {
                $matches = $event->getRoundMatches($i);
                foreach ($matches as $match) {
                    Matchup::saveReport('W20', $match->id, 'a');
                    Matchup::saveReport('L20', $match->id, 'b');
                }
            }
            $event->assignTrophiesFromMatches();
            $entries = $event->getEntries();
            foreach ($expectedMedals as $i => $medal) {
                $this->assertEquals($medal, $entries[$i]->medal);
            }
        }
    }

    public function testAssignMedalsByStandings(): void
    {
        $tests = [
            ['1st'], // 1 player
            ['1st', '2nd', 'dot'], // 3 players
            array_merge( // 14 players
                ['1st', '2nd'],
                array_fill(0, 2, 't4'),
                array_fill(0, 10, 'dot')
            ),
            array_merge( // 60 players
                ['1st', '2nd'],
                array_fill(0, 2, 't4'),
                array_fill(0, 4, 't8'),
                array_fill(0, 52, 'dot')
            )
        ];

        foreach ($tests as $expectedMedals) {
            $numPlayers = count($expectedMedals);
            $name = "{$numPlayers}_person_event";

            $event = $this->createTestEvent(['name' => $name]);

            for ($i = 1; $i <= $numPlayers; $i++) {
                $player = Player::findOrCreateByName("Player{$name}_$i");
                $event->addPlayer($player->name);

                $standing = new Standings($name, $player->name);
                $standing->event = $name;
                $standing->player = $player->name;
                $standing->active = 1;
                $standing->score = $numPlayers - $i;
                $standing->save();
            }

            $event->assignMedalsByStandings();
            $entries = $event->getEntries();

            $this->assertEquals($numPlayers, count($entries), "Wrong number of entries for $name");

            for ($i = 0; $i < $numPlayers; $i++) {
                $this->assertEquals(
                    $expectedMedals[$i],
                    $entries[$i]->medal,
                    "Wrong medal at position $i for $name"
                );
            }
        }
    }

    public function testIsOrganizer(): void
    {
        $organizer = Player::findOrCreateByName('TestOrganizer');
        $nonOrganizer = Player::findOrCreateByName('NonOrganizer');

        $event = $this->createTestEvent(['name' => 'isOrganizer Test Event']);
        $series = new Series($event->series);
        $series->addOrganizer($organizer->name);

        $this->assertTrue($event->isOrganizer($organizer->name), 'Series organizer should be recognized');
        $this->assertFalse($event->isOrganizer($nonOrganizer->name), 'Non-organizer should not be recognized');
    }

    public function testHasRegistrant(): void
    {
        $player = Player::findOrCreateByName('TestPlayer');
        $nonRegistrant = Player::findOrCreateByName('NonRegistrant');

        $event = $this->createTestEvent(['name' => 'hasRegistrant Test Event']);
        $event->addPlayer($player->name);

        $this->assertTrue($event->hasRegistrant($player->name), 'Player should be recognized as registrant');
        $this->assertFalse($event->hasRegistrant($nonRegistrant->name), 'Non-registrant should not be recognized');
    }

    public function testGetSubevents(): void
    {
        $event = $this->createTestEvent([
            'name' => 'getSubevents Test Event',
            'mainrounds' => 3,
            'mainstruct' => 'Swiss',
            'finalrounds' => 2,
            'finalstruct' => 'Single Elimination'
        ]);

        $subevents = $event->getSubevents();
        $this->assertCount(2, $subevents, 'Event should have exactly two subevents');

        // First subevent should be main event (timing = 1)
        $this->assertEquals(1, $subevents[0]->timing);
        $this->assertEquals(3, $subevents[0]->rounds);
        $this->assertEquals('Swiss', $subevents[0]->type);
        $this->assertEquals($event->name, $subevents[0]->parent);

        // Second subevent should be finals (timing = 2)
        $this->assertEquals(2, $subevents[1]->timing);
        $this->assertEquals(2, $subevents[1]->rounds);
        $this->assertEquals('Single Elimination', $subevents[1]->type);
        $this->assertEquals($event->name, $subevents[1]->parent);
    }

    public function testGetMatches(): void
    {
        $event = $this->createTestEvent([
            'name' => 'getMatches Test Event',
            'mainrounds' => 2,
            'mainstruct' => 'Swiss',
            'finalrounds' => 1,
            'finalstruct' => 'Single Elimination'
        ]);

        // Add some players
        $player1 = Player::findOrCreateByName('Player1');
        $player2 = Player::findOrCreateByName('Player2');
        $player3 = Player::findOrCreateByName('Player3');
        $player4 = Player::findOrCreateByName('Player4');
        $event->addPlayer($player1->name);
        $event->addPlayer($player2->name);
        $event->addPlayer($player3->name);
        $event->addPlayer($player4->name);

        // Start the event to create initial pairings
        $event->startEvent(false);

        // Get all matches and verify they are ordered correctly
        $matches = $event->getMatches();
        $this->assertGreaterThan(0, count($matches), 'Event should have matches after starting');

        // Verify matches have correct properties
        foreach ($matches as $match) {
            $this->assertNotNull($match->subevent, 'Match should have a subevent');
            $this->assertNotNull($match->round, 'Match should have a round number');
            $this->assertNotNull($match->playera, 'Match should have player A');
            $this->assertNotNull($match->playerb, 'Match should have player B');
            $this->assertEquals($event->name, $match->eventname, 'Match should reference correct event');

            // Verify match is either in main event or finals
            $this->assertContains($match->timing, [1, 2], 'Match timing should be either 1 (main) or 2 (finals)');

            // Verify round number is valid
            if ($match->timing === 1) {
                $this->assertLessThanOrEqual($event->mainrounds, $match->round, 'Main event match round should not exceed mainrounds');
            } else {
                $this->assertLessThanOrEqual($event->finalrounds, $match->round, 'Finals match round should not exceed finalrounds');
            }
        }

        // Verify matches are ordered by timing and round
        for ($i = 1; $i < count($matches); $i++) {
            $curr = $matches[$i];
            $prev = $matches[$i - 1];

            if ($curr->timing === $prev->timing) {
                $this->assertGreaterThanOrEqual(
                    $prev->round,
                    $curr->round,
                    'Matches with same timing should be ordered by round'
                );
            } else {
                $this->assertGreaterThan(
                    $prev->timing,
                    $curr->timing,
                    'Matches should be ordered by timing (main event before finals)'
                );
            }
        }
    }

    public function testAddPairing(): void
    {
        $event = $this->createTestEvent([
            'name' => 'addPairing Test Event',
            'mainrounds' => 3,
            'finalrounds' => 2
        ]);

        // Add some players
        $player1 = Player::findOrCreateByName('Player1');
        $player2 = Player::findOrCreateByName('Player2');
        $event->addPlayer($player1->name);
        $event->addPlayer($player2->name);

        // Create standings for the players
        $standings1 = new Standings($event->name, $player1->name);
        $standings1->event = $event->name;
        $standings1->player = $player1->name;
        $standings1->active = 1;
        $standings1->save();

        $standings2 = new Standings($event->name, $player2->name);
        $standings2->event = $event->name;
        $standings2->player = $player2->name;
        $standings2->active = 1;
        $standings2->save();

        // Test main round pairing
        $matchId = $event->addPairing($standings1, $standings2, 2, 'P');
        $match = new Matchup($matchId);
        $this->assertEquals($event->mainid, $match->subevent);
        $this->assertEquals(2, $match->round);
        $this->assertEquals('unverified', $match->verification);
        $this->assertEquals($player1->name, $match->playera);
        $this->assertEquals($player2->name, $match->playerb);

        // Test final round pairing (round > mainrounds)
        $matchId = $event->addPairing($standings1, $standings2, 4, 'P');
        $match = new Matchup($matchId);
        $this->assertEquals($event->finalid, $match->subevent);
        $this->assertEquals(1, $match->round); // Should be round 1 of finals
        $this->assertEquals('unverified', $match->verification);

        // Test BYE pairing
        $matchId = $event->addPairing($standings1, $standings1, 1, 'BYE');
        $match = new Matchup($matchId);
        $this->assertEquals('verified', $match->verification);
        $this->assertEquals($player1->name, $match->playera);
        $this->assertEquals($player1->name, $match->playerb);
    }

    public function testAddMatch(): void
    {
        $event = $this->createTestEvent([
            'name' => 'addMatch Test Event',
            'mainrounds' => 3,
            'finalrounds' => 2
        ]);

        // Add some players
        $player1 = Player::findOrCreateByName('Player1');
        $player2 = Player::findOrCreateByName('Player2');
        $event->addPlayer($player1->name);
        $event->addPlayer($player2->name);

        // Create standings for the players
        $standings1 = new Standings($event->name, $player1->name);
        $standings1->event = $event->name;
        $standings1->player = $player1->name;
        $standings1->active = 1;
        $standings1->save();

        $standings2 = new Standings($event->name, $player2->name);
        $standings2->event = $event->name;
        $standings2->player = $player2->name;
        $standings2->active = 1;
        $standings2->save();

        // Test BYE match (round 1)
        $event->addMatch($standings1, $standings1, 1, 'BYE');

        // Test match in progress (round 2)
        $event->addMatch($standings1, $standings2, 2, 'P');

        // Test regular match in main rounds (round 2)
        $event->addMatch($standings1, $standings2, 2, 'A', 2, 0);

        // Test draw match (round 3)
        $event->addMatch($standings1, $standings2, 3, 'D', 1, 1);

        // Test match in finals (round > mainrounds)
        $event->addMatch($standings1, $standings2, 4, 'B', 0, 2);

        $matches = $event->getMatches();
        $this->assertCount(5, $matches);

        // BYE match should be first (round 1)
        $byeMatch = $matches[0];
        $this->assertEquals('BYE', $byeMatch->result);
        $this->assertEquals('verified', $byeMatch->verification);
        $this->assertEquals($player1->name, $byeMatch->playera);
        $this->assertEquals($player1->name, $byeMatch->playerb);

        // In progress match should be second (round 2)
        $progressMatch = $matches[1];
        $this->assertEquals('P', $progressMatch->result);
        $this->assertEquals('unverified', $progressMatch->verification);
        $this->assertEquals(0, $progressMatch->playera_wins);
        $this->assertEquals(0, $progressMatch->playerb_wins);

        // Regular match should be third (round 2)
        $match = $matches[2];
        $this->assertEquals($event->mainid, $match->subevent);
        $this->assertEquals(2, $match->round);
        $this->assertEquals('verified', $match->verification);
        $this->assertEquals($player1->name, $match->playera);
        $this->assertEquals($player2->name, $match->playerb);
        $this->assertEquals(2, $match->playera_wins);
        $this->assertEquals(0, $match->playerb_wins);
        $this->assertEquals('A', $match->result);

        // Draw match should be fourth (round 3)
        $drawMatch = $matches[3];
        $this->assertEquals('D', $drawMatch->result);
        $this->assertEquals(1, $drawMatch->playera_wins);
        $this->assertEquals(1, $drawMatch->playerb_wins);

        // Finals match should be last (timing=2)
        $finalMatch = $matches[4];
        $this->assertEquals($event->finalid, $finalMatch->subevent);
        $this->assertEquals(1, $finalMatch->round); // Should be round 1 of finals
        $this->assertEquals('B', $finalMatch->result);
    }

    public function testGetNextPreRegister(): void
    {
        // Create events with different dates and states
        $past = $this->createTestEvent([
            'name' => 'Past Event',
            'start' => new DateTimeImmutable('-1 day')
        ]);
        $past->prereg_allowed = 1;
        $past->save();

        $future1 = $this->createTestEvent([
            'name' => 'Future Event 1',
            'start' => new DateTimeImmutable('+1 day')
        ]);
        $future1->prereg_allowed = 1;
        $future1->save();

        $future2 = $this->createTestEvent([
            'name' => 'Future Event 2',
            'start' => new DateTimeImmutable('+2 days')
        ]);
        $future2->prereg_allowed = 1;
        $future2->save();

        $private = $this->createTestEvent([
            'name' => 'Private Event',
            'start' => new DateTimeImmutable('+1 hour')
        ]);
        $private->prereg_allowed = 1;
        $private->private = 1;
        $private->save();

        $active = $this->createTestEvent([
            'name' => 'Active Event',
            'start' => new DateTimeImmutable('+3 days')
        ]);
        $active->prereg_allowed = 1;
        $active->active = 1;
        $active->save();

        $finalized = $this->createTestEvent([
            'name' => 'Finalized Event',
            'start' => new DateTimeImmutable('+4 days')
        ]);
        $finalized->prereg_allowed = 1;
        $finalized->finalized = 1;
        $finalized->save();

        $noPreReg = $this->createTestEvent([
            'name' => 'No PreReg Event',
            'start' => new DateTimeImmutable('+5 days')
        ]);
        $noPreReg->prereg_allowed = 0;
        $noPreReg->save();

        // Test getting all events
        $events = Event::getNextPreRegister(10);
        $this->assertCount(2, $events, 'Should only get 2 valid upcoming pre-reg events');
        $this->assertEquals('Future Event 1', $events[0]->name, 'First event should be the soonest future event');
        $this->assertEquals('Future Event 2', $events[1]->name, 'Second event should be the later future event');

        // Test limit
        $limitedEvents = Event::getNextPreRegister(1);
        $this->assertCount(1, $limitedEvents, 'Should respect the limit parameter');
        $this->assertEquals('Future Event 1', $limitedEvents[0]->name, 'Should get the soonest future event when limited');
    }

    public function testGetSeasonPointAdjustment(): void
    {
        $event = $this->createTestEvent(['name' => 'Test Event for Season Points']);
        $player = Player::findOrCreateByName('Test Player');

        // Test getting season points when none exist
        $result = $event->getSeasonPointAdjustment($player->name);
        $this->assertEquals(['adjustment' => 0, 'reason' => ''], $result);

        // Test getting season points after setting them
        $event->setSeasonPointAdjustment($player->name, 5, 'Test reason');
        $result = $event->getSeasonPointAdjustment($player->name);
        $this->assertEquals(['adjustment' => 5, 'reason' => 'Test reason'], $result);

        // Test updating existing season points
        $event->setSeasonPointAdjustment($player->name, 10, 'Updated reason');
        $result = $event->getSeasonPointAdjustment($player->name);
        $this->assertEquals(['adjustment' => 10, 'reason' => 'Updated reason'], $result);

        // Test getting season points for non-existent player
        $result = $event->getSeasonPointAdjustment('NonExistentPlayer');
        $this->assertEquals(['adjustment' => 0, 'reason' => ''], $result);
    }

    public function testResetEvent(): void
    {
        // Create an event with some players and start it
        $event = $this->createTestEvent([
            'name' => 'resetEvent Test Event',
            'mainrounds' => 3,
            'mainstruct' => 'Swiss'
        ]);

        // Add some players
        $player1 = Player::findOrCreateByName('ResetTestPlayer1');
        $player2 = Player::findOrCreateByName('ResetTestPlayer2');
        $player3 = Player::findOrCreateByName('ResetTestPlayer3');
        $event->addPlayer($player1->name);
        $event->addPlayer($player2->name);
        $event->addPlayer($player3->name);

        // Start the event and create some matches/standings
        $event->startEvent(false);
        $this->assertEquals(1, $event->active);

        // Drop a player to test undropping
        $event->dropPlayer($player3->name);

        // Create some matches
        $matches = $event->getRoundMatches(1);
        foreach ($matches as $match) {
            Matchup::saveReport('W20', $match->id, 'a');
            Matchup::saveReport('L20', $match->id, 'b');
        }

        // Set some medals
        $event->setFinalists($player1->name, $player2->name, [], []);

        // Verify initial state
        $this->assertGreaterThan(0, count($event->getMatches()), 'Should have matches before reset');
        $this->assertEquals('1st', $event->getEntries()[0]->medal, 'Should have medals before reset');

        // Get initial state to verify changes
        $initialMatches = $event->getMatches();
        $this->assertNotEmpty($initialMatches, 'Should have matches before reset');

        // Now reset the event
        $event->resetEvent();

        // Verify everything is reset:

        // 1. Check event state
        $this->assertEquals(0, $event->active);
        $this->assertEquals(0, $event->current_round);

        // 2. Check matches are deleted
        $this->assertEmpty($event->getMatches(), 'All matches should be deleted');

        // 3. Check medals are reset to 'dot'
        $entries = $event->getEntries();
        foreach ($entries as $entry) {
            $this->assertEquals('dot', $entry->medal, 'All medals should be reset to dot');
        }

        // 4. Check players are undropped
        $entries = $event->getEntries();
        foreach ($entries as $entry) {
            $this->assertEquals(0, $entry->drop_round, 'All players should be undropped');
        }

        // 5. Verify we can start a new event with these players
        $event->startEvent(false);
        $this->assertEquals(1, $event->active);
        $this->assertGreaterThan(0, count($event->getMatches()), 'Should be able to create new matches after reset');
    }

    public function testRepairRound(): void
    {
        // Create test event with 4 players
        $event = $this->createTestEvent([
            'name' => 'repairRound_test_event',
            'mainrounds' => 3,
            'mainstruct' => 'Swiss'
        ]);

        // Add 4 players
        for ($i = 1; $i <= 4; $i++) {
            $event->addPlayer("Player$i");
        }

        // Start event which will create initial pairings
        $event->startEvent(false);

        // Verify matches exist for round 1
        $matches = $event->getRoundMatches(1);
        $this->assertCount(2, $matches, 'Should have 2 matches for 4 players before repair');

        // Call repairRound
        $event->repairRound();

        // Verify the matches were deleted
        $matches = $event->getRoundMatches(1);
        $this->assertCount(0, $matches, 'All matches should be deleted after repair');
    }

    public function testMatchesOfType(): void
    {
        // Create test event with 4 players
        $event = $this->createTestEvent([
            'name' => 'matchesOfType_test_event',
            'mainrounds' => 3,
            'mainstruct' => 'Swiss'
        ]);

        // Add 4 players
        for ($i = 1; $i <= 4; $i++) {
            $event->addPlayer("Player$i");
        }

        // Start event which will create initial pairings
        $event->startEvent(false);

        // Initially all matches should be unfinished
        $unfinishedMatches = $event->matchesOfType('unfinished');
        $this->assertCount(2, $unfinishedMatches, 'Should have 2 unfinished matches initially');
        $finishedMatches = $event->matchesOfType('finished');
        $this->assertCount(0, $finishedMatches, 'Should have no finished matches initially');

        // Complete one match
        $firstMatch = $unfinishedMatches[0];
        Matchup::saveReport('W20', $firstMatch->id, 'a');
        Matchup::saveReport('L20', $firstMatch->id, 'b');

        // Now should have one finished and one unfinished match
        $unfinishedMatches = $event->matchesOfType('unfinished');
        $this->assertCount(1, $unfinishedMatches, 'Should have 1 unfinished match after completing one');
        $finishedMatches = $event->matchesOfType('finished');
        $this->assertCount(1, $finishedMatches, 'Should have 1 finished match after completing one');

        // Complete the second match
        $secondMatch = $unfinishedMatches[0];
        Matchup::saveReport('W20', $secondMatch->id, 'a');
        Matchup::saveReport('L20', $secondMatch->id, 'b');

        // Now all matches should be finished
        $unfinishedMatches = $event->matchesOfType('unfinished');
        $this->assertCount(0, $unfinishedMatches, 'Should have no unfinished matches after completing all');
        $finishedMatches = $event->matchesOfType('finished');
        $this->assertCount(2, $finishedMatches, 'Should have all matches finished after completing all');

        // Verify match properties
        foreach ($finishedMatches as $match) {
            $this->assertEquals('verified', $match->verification, 'Finished match should have verified status');
            $this->assertEquals(1, $match->round, 'Match should be from round 1');
            $this->assertNotNull($match->playera, 'Match should have player A');
            $this->assertNotNull($match->playerb, 'Match should have player B');
        }
    }
}
