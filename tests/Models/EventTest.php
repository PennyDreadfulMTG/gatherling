<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Event;
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

        foreach ($tests as $expected_medals) {
            $num_players = count($expected_medals);
            $name = "{$num_players}_person_event";

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
                'Single Elimination',
                '1',
                '1',
                '1',
                '1'
            );
            $event = new Event($name);

            $players = [];
            for ($i = 1; $i <= $num_players; $i++) {
                $player = Player::findOrCreateByName("Player{$name}_$i");
                $event->addPlayer($player->name);
                $players[] = $player;

                $standing = new Standings($name, $player->name);
                $standing->event = $name;
                $standing->player = $player->name;
                $standing->active = 1;
                $standing->score = $num_players - $i;
                $standing->save();
            }

            $event->assignMedalsByStandings();
            $entries = $event->getEntries();

            $this->assertEquals($num_players, count($entries), "Wrong number of entries for $name");

            for ($i = 0; $i < $num_players; $i++) {
                $this->assertEquals(
                    $expected_medals[$i],
                    $entries[$i]->medal,
                    "Wrong medal at position $i for $name"
                );
            }
        }
    }
}
