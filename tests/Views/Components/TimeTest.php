<?php

declare(strict_types=1);

namespace Gatherling\Tests\Views\Components;

use Gatherling\Views\Components\Time;
use Gatherling\Tests\Support\Html;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class TimeTest extends TestCase
{
    public function testRender(): void
    {
        $tz = new \DateTimeZone('UTC');

        $now = (new \DateTimeImmutable('now', $tz))->getTimestamp();
        $timeComponent = new Time($now, $now);
        $html = new Crawler($timeComponent->render());
        $this->assertEquals("just now", $html->filter('time')->text());

        $specificDate = (new \DateTimeImmutable("2024-02-01 12:00:00", $tz))->getTimestamp();
        $oneYearPreviously = $specificDate - 60 * 60 * 24 * 365;
        $timeComponent = new Time($oneYearPreviously, $specificDate);
        $expected = '<time datetime="2023-02-01T07:00:00-05:00">Feb 1st</time>' . "\n";
        $actual = $timeComponent->render();
        $this->assertEquals($expected, $actual);
    }
}
