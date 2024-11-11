<?php

declare(strict_types=1);

namespace Tests\Helpers\Types;

use Gatherling\Helpers\Types\DictType;
use PHPUnit\Framework\TestCase;

class DictTypeTest extends TestCase
{
    public function testDisplayNames(): void
    {
        $this->assertEquals('a labeled list of numbers', DictType::int()->getDisplayName());
        $this->assertEquals('labeled lists of numbers', DictType::int()->getPluralDisplayName());
        $this->assertEquals('a labeled list of numbers or text', DictType::intOrString()->getDisplayName());
        $this->assertEquals('labeled lists of numbers or text', DictType::intOrString()->getPluralDisplayName());
    }
}
