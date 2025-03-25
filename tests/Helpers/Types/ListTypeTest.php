<?php

declare(strict_types=1);

namespace Gatherling\Tests\Helpers\Types;

use Gatherling\Helpers\Types\ListType;
use PHPUnit\Framework\TestCase;

class ListTypeTest extends TestCase
{
    public function testDisplayNames(): void
    {
        self::assertEquals('a list of numbers', ListType::int()->getDisplayName());
        self::assertEquals('lists of numbers', ListType::int()->getPluralDisplayName());
    }
}
