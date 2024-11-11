<?php

declare(strict_types=1);

namespace Tests\Helpers\Types;

use Gatherling\Helpers\Types\SimpleType;
use PHPUnit\Framework\TestCase;

class SimpleTypeTest extends TestCase
{
    public function testDisplayName(): void
    {
        $this->assertEquals('a number', SimpleType::INT->getDisplayName());
        $this->assertEquals('a whole number', SimpleType::INT->getDisplayName(123.4));
        $this->assertEquals('a whole number', SimpleType::INT->getDisplayName('123.4'));
        $this->assertEquals('some text', SimpleType::STRING->getDisplayName());
        $this->assertEquals('some text', SimpleType::STRING->getDisplayName('123.4'));
        $this->assertEquals('some text', SimpleType::STRING->getDisplayName(''));
    }
}
