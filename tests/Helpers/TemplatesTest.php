<?php

declare(strict_types=1);

namespace Gatherling\Tests\Helpers;

require_once __DIR__ . '/../../gatherling/bootstrap.php';

use stdClass;
use PHPUnit\Framework\TestCase;

use function Gatherling\Helpers\getObjectVarsCamelCase;
use function Gatherling\Helpers\toCamel;

final class TemplatesTest extends TestCase
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
        self::assertEquals([
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
        self::assertEquals('fooBar', toCamel('foo_bar'));
        self::assertEquals('opMatch', toCamel('OP_Match'));
        self::assertEquals('adWords', toCamel('AdWords'));
        self::assertEquals('alreadyCamelCase', toCamel('alreadyCamelCase'));
        self::assertEquals('xmlHttpRequest', toCamel('XMLHttpRequest'));
        self::assertEquals('userDto', toCamel('userDTO'));
    }
}
