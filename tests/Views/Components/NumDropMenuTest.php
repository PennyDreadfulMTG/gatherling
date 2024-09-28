<?php

namespace Tests\Views\Components;

use Gatherling\Views\Components\NumDropMenu;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class NumDropMenuTest extends TestCase
{
    public function testNumDropMenu(): void
    {
        $numDropMenu = new NumDropMenu('test', 'Test', 10, 5);
        $html = new Crawler($numDropMenu->render());
        $this->assertEquals('test', $html->filter('select')->attr('name'));
        $this->assertCount(12, $html->filter('select')->filter('option'));
        $this->assertEquals('5', $html->filter('select')->filter('option[selected]')->attr('value'));

        $numDropMenu = new NumDropMenu('test', 'Test', 10, '5');
        $html = new Crawler($numDropMenu->render());
        $this->assertEquals('test', $html->filter('select')->attr('name'));
        $this->assertCount(12, $html->filter('select')->filter('option'));
        $this->assertEquals('5', $html->filter('select')->filter('option[selected]')->attr('value'));

        $numDropMenu = new NumDropMenu('test', 'Test', 10, null);
        $html = new Crawler($numDropMenu->render());
        $this->assertEquals('test', $html->filter('select')->attr('name'));
        $this->assertCount(12, $html->filter('select')->filter('option'));
        $this->assertEquals('0', $html->filter('select')->filter('option[selected]')->attr('value'));
    }
}
