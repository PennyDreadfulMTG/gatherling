<?php

declare(strict_types=1);

namespace Gatherling\Tests\Views;

use Gatherling\Views\Components\Component;
use Gatherling\Views\TemplateHelper;
use PHPUnit\Framework\TestCase;

class TemplateHelperTest extends TestCase
{
    public function testRenderComponent()
    {
        $iconConstructor = function (string $name, string $src) {
            return new class ($name, $src) extends Component {
                public function __construct(public string $name, public string $src)
                {
                    parent::__construct('partials/testIcon');
                }
            };
        };
        $icon1 = $iconConstructor('icon1', 'src1');
        $icon2 = $iconConstructor('icon2', 'src2');

        $itemConstructor = function (string $name, ?Component $icon = null) {
            return new class ($name, $icon) extends Component {
                public function __construct(public string $name, public ?Component $icon = null)
                {
                    parent::__construct('partials/testItem');
                }
            };
        };
        $items = [$itemConstructor('Stick', $icon1), $itemConstructor('Bat & Ball', $icon2), $itemConstructor('Crossbow')];
        $component = new class ('Fun & Games', $items) extends Component
        {
            public function __construct(public string $name, public array $items)
            {
                parent::__construct('partials/testComponent');
            }
        };
        $actual = $component->render();

        $expected = "<h1>Fun &amp; Games</h1>"
            . "<ul>"
            . '        <li><img alt="icon1 icon" src="src1" /> Stick</li>'
            . '        <li><img alt="icon2 icon" src="src2" /> Bat &amp; Ball</li>'
            . '        <li>Crossbow</li>'
            . "</ul>";
        $this->assertEquals($expected, str_replace("\n", '', $actual));
    }
}
