<?php

namespace Tests\Models;

use Gatherling\Models\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testStructureSummary()
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
}
