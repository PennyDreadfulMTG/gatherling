<?php

declare(strict_types=1);

require_once 'gatherling/lib.php';

use PHPUnit\Framework\TestCase;

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
            'child' => [
                'baz' => 'quux',
                'child' => [
                    'foo' => 'bar',
                ],
            ],
        ], $arr);
    }
}
