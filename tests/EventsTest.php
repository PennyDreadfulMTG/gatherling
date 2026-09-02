<?php

declare(strict_types=1);

namespace Gatherling\Tests;

require_once __DIR__ . '/../gatherling/bootstrap.php';

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Matchup;
use Gatherling\Tests\Support\TestCases\DatabaseCase;
use PHPUnit\Framework\Attributes\Depends;
use Safe\DateTimeImmutable;

use function Safe\json_encode;

final class EventsTest extends DatabaseCase
{
    public function testSeriesCreation(): Series
    {
        if (!Series::exists('Test')) {
            $series = new Series('');
            $series->name = 'Test';
            $series->active = 1;
            $series->start_time = '00:00' . ':00';
            $series->start_day = 'Friday';
            $series->save();
        }

        $series = new Series('Test');
        self::assertEquals($series->name, 'Test');

        return $series;
    }

    #[Depends('testSeriesCreation')]
    public function testEventCreation(Series $series): Event
    {
        $recentEvents = $series->getRecentEvents(1);
        if (count($recentEvents) == 0) {
            $number = 1;
        } else {
            $event = $recentEvents[0];
            do {
                $number = $event->number + 1;
                $event = $event->findNext();
            } while ($event != null);
        }
        $name = sprintf('%s %d.%02d', $series->name, 1, $number);

        $event = new Event('');
        $start = new DateTimeImmutable('2024-12-03 02:25');
        $event->start = $start;
        $event->name = $name;

        $host = Player::findOrCreateByName('JimmyTheHost');

        $event->format = 'Modern';
        $event->host = $host->name;
        $event->cohost = null;
        $event->client = 1;
        $event->kvalue = 16;
        $event->series = $series->name;
        $event->season = 1;
        $event->number = $number;
        $event->threadurl = '';
        $event->metaurl = '';
        $event->reporturl = '';

        $event->prereg_allowed = 1;
        $event->player_reportable = 1;

        $event->mainrounds = 3;
        $event->mainstruct = 'Swiss';
        $event->finalrounds = 3;
        $event->finalstruct = 'Single Elimination';
        $event->save();

        $event = new Event($name);
        self::assertEquals($event->name, $name);
        self::assertEquals($event->start, $start);

        return $event;
    }

    #[Depends('testEventCreation')]
    public function testRegistration(Event $event): Event
    {
        for ($i = 0; $i < 10; $i++) {
            $event->addPlayer('testplayer' . $i);
        }
        // 10 players have expressed interest in the event.
        self::assertEquals(10, count($event->getEntries()));
        // No players have filled out decklists.
        self::assertEquals(0, count($event->getRegisteredEntries(false, true)));

        $deck = $this->insertDeck('testplayer0', $event, '60 Plains', '');
        self::assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = $this->insertDeck('testplayer1', $event, '60 Island', '');
        self::assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = $this->insertDeck('testplayer2', $event, '40 Swamp', '');
        self::assertNotEmpty($deck->errors, 'No errors for a 40 card deck.');
        $deck = $this->insertDeck('testplayer3', $event, "60 Swamp\n100 Relentless Rats", '15 Swamp');
        self::assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = $this->insertDeck('testplayer4', $event, "20 Mountain\n20 Forest\n\n\n\n\n\n\n\n\n\n\n\n4 Plains\n4 Plains\n4 Plains\n4 Plains\n4 Plains\n\n\n", '');
        self::assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = $this->insertDeck('testplayer5', $event, "54 Mountain\n6 Seven Dwarves", '1 Seven Dwarves');
        self::assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = $this->insertDeck('testplayer6', $event, "50 Mountain\n10 Seven Dwarves", '');
        self::assertNotEmpty($deck->errors, json_encode($deck->errors));
        $deck = $this->insertDeck('testplayer7', $event, "55 Mountain\n5 Seven Dwarves", '5 Seven Dwarves');
        self::assertNotEmpty($deck->errors, json_encode($deck->errors));
        // None of this changes entry status.
        self::assertEquals(10, count($event->getEntries()));
        // 5 Valid decks (0, 1, 3, 4, 5), 3 invalid decks (2, 6, 7), and 2 not submitted decks (8, 9).
        $registeredEntries = $event->getRegisteredEntries(false, true);
        self::assertEquals(5, count($registeredEntries));

        return $event;
    }

    #[Depends('testRegistration')]
    public function testEventStart(Event $event): Event
    {
        self::assertEquals($event->active, 0);
        self::assertEquals($event->current_round, 0);

        self::assertEquals(10, count($event->getEntries()));
        $event->startEvent(true);
        // The five invalid or unentered decks are pruned at start time.
        self::assertEquals(5, count($event->getEntries()));
        self::assertEquals(3, count($event->getRoundMatches(1)));

        $event = new Event($event->name);
        self::assertEquals($event->active, 1);
        self::assertEquals($event->current_round, 1);

        $matches = $event->getRoundMatches(1);
        self::assertEquals(count($matches), 3);
        self::assertNotEmpty($matches[0]->playera);
        self::assertNotEmpty($matches[0]->playerb);

        return $event;
    }

    #[Depends('testEventStart')]
    public function testReporting(Event $event): Event
    {
        $matches = $event->getRoundMatches(1);
        self::assertEquals(count($matches), 3);
        Matchup::saveReport('W20', $matches[0]->id, 'a');
        Matchup::saveReport('L20', $matches[0]->id, 'b');
        Matchup::saveReport('W20', $matches[1]->id, 'a');
        Matchup::saveReport('W20', $matches[1]->id, 'b');
        $matches = $event->getRoundMatches(1);
        self::assertEquals('verified', $matches[0]->verification);
        self::assertEquals('failed', $matches[1]->verification);
        Matchup::saveReport('L20', $matches[1]->id, 'b');
        $matches = $event->getRoundMatches(1);
        self::assertEquals('verified', $matches[1]->verification);
        return $event;
    }
}
