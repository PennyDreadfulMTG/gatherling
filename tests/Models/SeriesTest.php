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
        self::assertTrue($series->new);
        $series->name = 'Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '12:00:00';
        $series->active = 1;
        $series->prereg_default = 1;
        $series->mtgo_room = '#testroom';
        $series->save();

        $series = new Series('Test Series');
        self::assertFalse($series->new);
        self::assertEquals('Test Series', $series->name);
        self::assertEquals('Monday', $series->start_day);
        self::assertEquals('12:00:00', $series->start_time);
        self::assertEquals(1, $series->active);
        self::assertEquals(1, $series->prereg_default);
        self::assertEquals('testroom', $series->mtgo_room);
    }
}
