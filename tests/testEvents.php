<?php
require('lib.php');

// declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EventsTest extends TestCase
{
    public function testSeriesCreation() {

        if (!Series::exists("Test")) {
            $series = new Series("");
            $series->name = "Test";
            $series->active = true; 
            $series->start_time = "00:00" . ":00";
            $series->start_day = "Friday";
            $series->prereg_default = true;
            $series->pkonly_default = false;
            $series->save();
        }
        
        $series = new Series("Test");
        $this->assertEquals($series->name, "Test");
        return $series;
    }

    /**
     * @depends testSeriesCreation
     */
     public function testEventCreation($series) {
        $recentEvents = $series->getRecentEvents(1);
        if (count($recentEvents) == 0)  {
            $number = 1;
        }
        else {
            $number = $recentEvents[0]->number + 1;
        }
        $name = sprintf("%s %d.%02d", $series->name, 1, $number);
        
        $event = new Event("");
        $event->start = date('Y-m-d H:00:00');
        $event->name = $name;

        $event->format = "Standard";
        $event->host = NULL;
        $event->cohost = NULL;
        $event->kvalue = 16;
        $event->series = $series->name;
        $event->season = 1;
        $event->number = $number;
        $event->threadurl = "";
        $event->metaurl = "";
        $event->reporturl = "";

        $event->prereg_allowed = 1;
        $event->pkonly = 0;
        $event->player_reportable = 1;

        $event->mainrounds = 3;
        $event->mainstruct = "Swiss";
        $event->finalrounds = 3;
        $event->finalstruct = "Single Elimination";
        $event->save();

        $event = new Event($name);
        $this->assert($event->name == $name);
        $this->assert($event->start == date('Y-m-d H:00:00'));
        return $event;
    }

    /**
     * @depends testEventCreation
     */
    public function testRegistration($event) {
        for ($i=0; $i < ; $i++) { 
            $event->addPlayer("testplayer". $i);
        }
        // 8 players have expressed interest in the event.
        $this->assert(count($event->getEntries()) == 8);
        // No players have filled out decklists.
        $this->assert(count($event->getRegisteredEntries()) == 8);
        
    }

}
?>