<?php

declare(strict_types=1);

namespace Gatherling\Tests\Helpers\Types;

use Gatherling\Helpers\Types\SimpleType;
use PHPUnit\Framework\TestCase;

class SimpleTypeTest extends TestCase
{
    public function testDisplayName(): void
    {
        self::assertEquals('a number', SimpleType::INT->getDisplayName());
        self::assertEquals('a whole number', SimpleType::INT->getDisplayName(123.4));
        self::assertEquals('a whole number', SimpleType::INT->getDisplayName('123.4'));
        self::assertEquals('some text', SimpleType::STRING->getDisplayName());
        self::assertEquals('some text', SimpleType::STRING->getDisplayName('123.4'));
        self::assertEquals('some text', SimpleType::STRING->getDisplayName(''));
    }
}
