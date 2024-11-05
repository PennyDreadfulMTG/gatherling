<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Standings;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
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
        $series = new Series('');
        $series->name = 'Test Trophy Series';
        $series->active = 1;
        $series->start_time = '00:00:00';
        $series->start_day = 'Friday';
        $series->save();

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

            $event = new Event('');
            $event->name = $numPlayers . '_person_event_with_knockout';
            $event->host = $host->name;
            $event->start = '2025-06-01';
            $event->kvalue = 8;
            $event->format = 'Standard';
            $event->series = $series->name;
            $event->season = 1;
            $event->number = 1;
            $event->mainstruct = 'Swiss';
            $event->mainrounds = 1;
            $event->finalstruct = 'Single Elimination';
            if ($numPlayers <= 7) {
                $event->finalrounds = 1;
            } elseif ($numPlayers <= 16) {
                $event->finalrounds = 2;
            } else {
                $event->finalrounds = 3;
            }
            $event->threadurl = '';
            $event->reporturl = '';
            $event->metaurl = '';
            $event->save();

            $event = new Event($event->name);

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

        $series = new Series('');
        $series->name = 'Test Series With Medals';
        $series->active = 1;
        $series->start_time = '00:00:00';
        $series->start_day = 'Friday';
        $series->save();

        $host = Player::findOrCreateByName('JimmyTheHost');
        $cohost = Player::findOrCreateByName('Nyarlothep');

        foreach ($tests as $expectedMedals) {
            $numPlayers = count($expectedMedals);
            $name = "{$numPlayers}_person_event";

            Event::createEvent(
                '2024',
                '11',
                '02',
                '00',
                $name,
                $name,
                'Standard',
                $host->name,
                $cohost->name,
                '1',
                $series->name,
                'Season',
                '1',
                'ThreadURL',
                'MetaURL',
                'ReportURL',
                '1',
                '1',
                '0',
                '3',
                1,
                'Single Elimination',
                1,
                'Single Elimination',
                '1'
            );
            $event = new Event($name);

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
}
