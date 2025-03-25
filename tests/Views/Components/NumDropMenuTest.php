<?php

declare(strict_types=1);

namespace Gatherling\Tests\Views\Components;

use Gatherling\Views\Components\NumDropMenu;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class NumDropMenuTest extends TestCase
{
    public function testNumDropMenu(): void
    {
        $numDropMenu = new NumDropMenu('test', 'Test', 10, 5);
        $html = new Crawler($numDropMenu->render());
        self::assertEquals('test', $html->filter('select')->attr('name'));
        self::assertCount(12, $html->filter('select')->filter('option'));
        self::assertEquals('5', $html->filter('select')->filter('option[selected]')->attr('value'));

        $numDropMenu = new NumDropMenu('test', 'Test', 10, null);
        $html = new Crawler($numDropMenu->render());
        self::assertEquals('test', $html->filter('select')->attr('name'));
        self::assertCount(12, $html->filter('select')->filter('option'));
        // Nothing is explicitly marked as selected, not even the default option
        self::assertEquals(0, $html->filter('select')->filter('option[selected]')->count());

        $numDropMenu = new NumDropMenu('test', 'Test', 10, 0);
        $html = new Crawler($numDropMenu->render());
        self::assertEquals('test', $html->filter('select')->attr('name'));
        self::assertCount(12, $html->filter('select')->filter('option'));
        self::assertEquals('0', $html->filter('select')->filter('option[selected]')->attr('value'));
    }
}
