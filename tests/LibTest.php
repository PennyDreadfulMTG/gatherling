<?php

declare(strict_types=1);

namespace Gatherling\Tests;

require_once 'gatherling/lib.php';

use stdClass;
use PHPUnit\Framework\TestCase;
use Gatherling\Views\Pages\Page;
use Gatherling\Views\Components\Component;

final class LibTest extends TestCase
{
    public function testObjectVarsCamelCase()
    {
        $grandchild = new stdClass();
        $grandchild->foo = 'bar';
        $child = new stdClass();
        $child->baz = 'quux';
        $child->child = $grandchild;
        $parent = new stdClass();
        $parent->child = $child;
        $parent->monkey = 'business';
        $arr = getObjectVarsCamelCase($parent);
        $this->assertEquals([
            'monkey' => 'business',
            'child'  => [
                'baz'   => 'quux',
                'child' => [
                    'foo' => 'bar',
                ],
            ],
        ], $arr);
    }
}
