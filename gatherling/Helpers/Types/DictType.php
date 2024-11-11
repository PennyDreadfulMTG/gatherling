<?php

declare(strict_types=1);

namespace Gatherling\Helpers\Types;

class DictType extends CompositeType
{
    private static ?self $int = null;
    private static ?self $string = null;
    private static ?self $intOrString = null;

    public static function int(): self
    {
        return self::$int ??= new self(SimpleType::INT);
    }

    public static function string(): self
    {
        return self::$string ??= new self(SimpleType::STRING);
    }

    public static function intOrString(): self
    {
        return self::$intOrString ??= new self(new UnionType(SimpleType::INT, SimpleType::STRING));
    }

    public function getTypeName(): string
    {
        return sprintf('array<string, %s>', $this->valueType->getTypeName());
    }

    public function getDisplayName(mixed $comparedTo = null): string
    {
        return "a labeled list of {$this->valueType->getPluralDisplayName()}";
    }

    public function getPluralDisplayName(): string
    {
        return "labeled lists of {$this->valueType->getPluralDisplayName()}";
    }
}
