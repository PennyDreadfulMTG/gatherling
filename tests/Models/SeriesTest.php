<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Series;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

class SeriesTest extends DatabaseCase
{
    public function testSave(): void
    {
        $series = new Series('');
        $this->assertTrue($series->new);
        $series->name = 'Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '12:00:00';
        $series->active = 1;
        $series->prereg_default = 1;
        $series->mtgo_room = '#testroom';
        $series->save();

        $series = new Series('Test Series');
        $this->assertFalse($series->new);
        $this->assertEquals('Test Series', $series->name);
        $this->assertEquals('Monday', $series->start_day);
        $this->assertEquals('12:00:00', $series->start_time);
        $this->assertEquals(1, $series->active);
        $this->assertEquals(1, $series->prereg_default);
        $this->assertEquals('testroom', $series->mtgo_room);
    }
}
