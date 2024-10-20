<?php

declare(strict_types=1);

namespace Gatherling\Tests;

require_once 'gatherling/lib.php';

use stdClass;
use PHPUnit\Framework\TestCase;

final class LibTest extends TestCase
{
    public function testObjectVarsCamelCase(): void
    {
        $grandchild = new stdClass();
        $grandchild->foo = 'bar';
        $child = new stdClass();
        $child->baz = 'quux';
        $child->child = $grandchild;
        $parent = new stdClass();
        $parent->child = $child;
        $parent->monkey = 'business';
        $parent->OP_Match = 1;
        $child->list = [1, 2, 3, 4, 5];
        $arr = getObjectVarsCamelCase($parent);
        $this->assertEquals([
            'monkey' => 'business',
            'opMatch' => 1,
            'child'  => [
                'baz'   => 'quux',
                'child' => [
                    'foo' => 'bar',
                ],
                'list' => [1, 2, 3, 4, 5],
            ],
        ], $arr);
    }

    public function testToCamel(): void
    {
        $this->assertEquals('fooBar', toCamel('foo_bar'));
        $this->assertEquals('opMatch', toCamel('OP_Match'));
        $this->assertEquals('adWords', toCamel('AdWords'));
        $this->assertEquals('alreadyCamelCase', toCamel('alreadyCamelCase'));
        $this->assertEquals('xmlHttpRequest', toCamel('XMLHttpRequest'));
        $this->assertEquals('userDto', toCamel('userDTO'));
    }
}
