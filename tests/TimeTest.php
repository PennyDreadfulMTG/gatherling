<?php

declare(strict_types=1);

namespace Gatherling\Tests;

require_once 'gatherling/lib.php';

use PHPUnit\Framework\TestCase;

final class TimeTest extends TestCase
{
    public function testHumanDate()
    {
        $now = strtotime('2024-08-29T12:00:00-07:00');
        $this->assertEquals('just now', human_date($now, $now));
        $recently = strtotime('2024-08-29T11:30:00-07:00');
        $this->assertEquals('30 minutes ago', human_date($recently, $now));
        $soon = strtotime('2024-08-29T12:15:00-07:00');
        $this->assertEquals('15 minutes from now', human_date($soon, $now));
        $yesterday = strtotime('2024-08-28T09:30:00-07:00');
        $this->assertEquals('1 day ago', human_date($yesterday, $now));
        $lastMonth = strtotime('2024-07-28T09:30:00-07:00');
        $this->assertEquals('Jul 28th', human_date($lastMonth, $now));
        $aFewWeeks = strtotime('2024-08-02T09:30:00-07:00');
        $this->assertEquals('3 weeks ago', human_date($aFewWeeks, $now));
        $longAgo = strtotime('2023-11-01T09:30:00-07:00');
        $this->assertEquals('Nov 1st', human_date($longAgo, $now));
        $nextMonth = strtotime('2024-09-30T09:30:00-07:00');
        $this->assertEquals('Sep 30th', human_date($nextMonth, $now));
        $farFuture = strtotime('2026-08-28T09:30:00-07:00');
        $this->assertEquals('Aug 2026', human_date($farFuture, $now));
        // New York time is Gatherling's "home" time.
        $differsNewYorkAndLosAngeles = strtotime('2024-06-13T23:30:00-07:00');
        $this->assertEquals('Jun 14th', human_date($differsNewYorkAndLosAngeles, $now));
    }

    public function testTimeElement()
    {
        $now = strtotime('2024-08-29T12:00:00-07:00');
        $elem = time_element($now, $now);
        // New York time is Gatherling's "home" time.
        $this->assertEquals('<time datetime="2024-08-29T15:00:00-04:00">just now</time>', $elem);
    }
}
