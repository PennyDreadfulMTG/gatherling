<?php

declare(strict_types=1);

namespace Gatherling\Tests\Helpers\Types;

use Gatherling\Helpers\Types\DictType;
use PHPUnit\Framework\TestCase;

class DictTypeTest extends TestCase
{
    public function testDisplayNames(): void
    {
        self::assertEquals('a labeled list of numbers', DictType::int()->getDisplayName());
        self::assertEquals('labeled lists of numbers', DictType::int()->getPluralDisplayName());
        self::assertEquals('a labeled list of numbers or text', DictType::intOrString()->getDisplayName());
        self::assertEquals('labeled lists of numbers or text', DictType::intOrString()->getPluralDisplayName());
    }
}
