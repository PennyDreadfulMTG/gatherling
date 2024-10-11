<?php

declare(strict_types=1);

namespace Gatherling\Tests;

require_once 'gatherling/lib.php';

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Matchup;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

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
        $this->assertEquals($series->name, 'Test');

        return $series;
    }

    /** @depends testSeriesCreation */
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
        $event->start = date('Y-m-d H:00:00');
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
        $this->assertEquals($event->name, $name);
        $this->assertEquals($event->start, date('Y-m-d H:00:00'));

        return $event;
    }

    /** @depends testEventCreation */
    public function testRegistration(Event $event): Event
    {
        for ($i = 0; $i < 10; $i++) {
            $event->addPlayer('testplayer' . $i);
        }
        // 8 players have expressed interest in the event.
        $this->assertEquals(10, count($event->getEntries()));
        // No players have filled out decklists.
        $this->assertEquals(0, count($event->getRegisteredEntries(false, true)));

        $deck = insertDeck('testplayer0', $event, '60 Plains', '');
        $this->assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = insertDeck('testplayer1', $event, '60 Island', '');
        $this->assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = insertDeck('testplayer2', $event, '40 Swamp', '');
        $this->assertNotEmpty($deck->errors, 'No errors for a 40 card deck.');
        $deck = insertDeck('testplayer3', $event, "60 Swamp\n100 Relentless Rats", '15 Swamp');
        $this->assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = insertDeck('testplayer4', $event, "20 Mountain\n20 Forest\n\n\n\n\n\n\n\n\n\n\n\n4 Plains\n4 Plains\n4 Plains\n4 Plains\n4 Plains\n\n\n", '');
        $this->assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = insertDeck('testplayer5', $event, "54 Mountain\n6 Seven Dwarves", '1 Seven Dwarves');
        $this->assertEmpty($deck->errors, json_encode($deck->errors));
        $deck = insertDeck('testplayer6', $event, "50 Mountain\n10 Seven Dwarves", '');
        $this->assertNotEmpty($deck->errors, json_encode($deck->errors));
        $deck = insertDeck('testplayer7', $event, "55 Mountain\n5 Seven Dwarves", '5 Seven Dwarves');
        $this->assertNotEmpty($deck->errors, json_encode($deck->errors));
        // 5 Valid decks (0, 1, 2, and 4, 5), 3 invalid deck (3, 6, 7), and 3 not submitted decks.
        $this->assertEquals(5, count($event->getRegisteredEntries(false, true)));

        return $event;
    }

    /** @depends testRegistration */
    public function testEventStart(Event $event): Event
    {
        $this->assertEquals($event->active, 0);
        $this->assertEquals($event->current_round, 0);

        $event->startEvent(true);

        $event = new Event($event->name);
        $this->assertEquals($event->active, 1);
        $this->assertEquals($event->current_round, 1);

        return $event;
    }

    /** @depends testEventStart */
    public function testReporting(Event $event): Event
    {
        $matches = $event->getRoundMatches(1);
        $this->assertEquals(count($matches), 3);
        Matchup::saveReport('W20', $matches[0]->id, 'a');
        Matchup::saveReport('L20', $matches[0]->id, 'b');
        Matchup::saveReport('W20', $matches[1]->id, 'a');
        Matchup::saveReport('W20', $matches[1]->id, 'b');
        $matches = $event->getRoundMatches(1);
        $this->assertEquals('verified', $matches[0]->verification);
        $this->assertEquals('failed', $matches[1]->verification);
        Matchup::saveReport('L20', $matches[1]->id, 'b');
        $matches = $event->getRoundMatches(1);
        $this->assertEquals('verified', $matches[1]->verification);

        return $event;
    }
}

function insertDeck(string $player, Event $event, string $main, string $side): Deck
{
    $deck = new Deck(0);
    $deck->playername = $player;
    $deck->eventname = $event->name;
    $deck->event_id = $event->id;
    $deck->maindeck_cards = parseCardsWithQuantity($main);
    $deck->sideboard_cards = parseCardsWithQuantity($side);
    $deck->save();

    return $deck;
}
