<?php

declare(strict_types=1);

namespace Tests\Helpers\Types;

use Gatherling\Helpers\Types\DictType;
use Gatherling\Helpers\Types\SimpleType;
use Gatherling\Helpers\Types\UnionType;
use PHPUnit\Framework\TestCase;

class UnionTypeTest extends TestCase
{
    public function testDisplayNames(): void
    {
        $intFloat = new UnionType(SimpleType::INT, SimpleType::FLOAT);
        $stringInt = new UnionType(SimpleType::STRING, SimpleType::INT);
        $heckaMonster = new UnionType($stringInt, SimpleType::BOOL, DictType::intOrString());
        $this->assertEquals('a number', $intFloat->getDisplayName());
        $this->assertEquals('numbers', $intFloat->getPluralDisplayName());
        $this->assertEquals('some text or a number', $stringInt->getDisplayName());
        $this->assertEquals('text or numbers', $stringInt->getPluralDisplayName());
        $this->assertEquals('some text or a number or true or false or a labeled list of numbers or text', $heckaMonster->getDisplayName());
        $this->assertEquals('text or numbers or true/false values or labeled lists of numbers or text', $heckaMonster->getPluralDisplayName());
    }
}
