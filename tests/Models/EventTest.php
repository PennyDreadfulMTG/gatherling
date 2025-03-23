<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Standings;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

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
                'finalrounds' => $numPlayers <= 7 ? 1 : ($numPlayers <= 16 ? 2 : 3)
            ]);
            for ($i = 1; $i <= $numPlayers; $i++) {
                $event->addPlayer("Player$i");
            }
            $event->startEvent(false);
            $this->assertSame($numPlayers, count($event->getEntries()));

            $matches = $event->getRoundMatches(1);
            $this->assertEquals(ceil($numPlayers / 2), count($matches));
            foreach ($matches as $match) {
                Matchup::saveReport('W20', $match->id, 'a');
                Matchup::saveReport('L20', $match->id, 'b');
            }
            for ($i = 2; $i <= $event->mainrounds + $event->finalrounds; $i++) {
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
}
