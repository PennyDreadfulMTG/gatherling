<?php

declare(strict_types=1);

namespace Gatherling\Tests\Views\Components;

use DateInterval;
use Gatherling\Views\Components\Time;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Symfony\Component\DomCrawler\Crawler;

class TimeTest extends TestCase
{
    public function testRender(): void
    {
        $tz = new \DateTimeZone('UTC');

        $now = (new DateTimeImmutable('now', $tz));
        $timeComponent = new Time($now, $now);
        $html = new Crawler($timeComponent->render());
        $this->assertEquals("just now", $html->filter('time')->text());

        $specificDate = new DateTimeImmutable("2024-02-01 12:00:00", $tz);
        $oneYearPreviously = $specificDate->sub(DateInterval::createFromDateString('1 year'));
        $timeComponent = new Time($oneYearPreviously, $specificDate);
        $expected = '<time datetime="2023-02-01T12:00:00+00:00">Feb 1st</time>' . "\n";
        $actual = $timeComponent->render();
        $this->assertEquals($expected, $actual);

        $now = new DateTimeImmutable('2024-08-29T12:00:00-07:00');
        $this->assertStringContainsString('just now', (new Time($now, $now))->render());
        $recently = new DateTimeImmutable('2024-08-29T11:30:00-07:00');
        $this->assertStringContainsString('30 minutes ago', (new Time($recently, $now))->render());
        $soon = new DateTimeImmutable('2024-08-29T12:15:00-07:00');
        $this->assertStringContainsString('15 minutes from now', (new Time($soon, $now))->render());
        $yesterday = new DateTimeImmutable('2024-08-28T09:30:00-07:00');
        $this->assertStringContainsString('1 day ago', (new Time($yesterday, $now))->render());
        $lastMonth = new DateTimeImmutable('2024-07-28T09:30:00-07:00');
        $this->assertStringContainsString('Jul 28th', (new Time($lastMonth, $now))->render());
        $aFewWeeks = new DateTimeImmutable('2024-08-02T09:30:00-07:00');
        $this->assertStringContainsString('3 weeks ago', (new Time($aFewWeeks, $now))->render());
        $longAgo = new DateTimeImmutable('2023-11-01T09:30:00-07:00');
        $this->assertStringContainsString('Nov 1st', (new Time($longAgo, $now))->render());
        $nextMonth = new DateTimeImmutable('2024-09-30T09:30:00-07:00');
        $this->assertStringContainsString('Sep 30th', (new Time($nextMonth, $now))->render());
        $farFuture = new DateTimeImmutable('2026-08-28T09:30:00-07:00');
        $this->assertStringContainsString('Aug 2026', (new Time($farFuture, $now))->render());
        // New York time is Gatherling's "home" time.
        $differsNewYorkAndLosAngeles = new DateTimeImmutable('2024-06-13T23:30:00-07:00');
        $this->assertStringContainsString('Jun 14th', (new Time($differsNewYorkAndLosAngeles, $now))->render());
    }
}
