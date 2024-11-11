<?php

declare(strict_types=1);

namespace Gatherling\Helpers\Types;

class ListType extends CompositeType
{
    private static ?self $int = null;
    private static ?self $string = null;

    public static function int(): self
    {
        return self::$int ??= new self(SimpleType::INT);
    }

    public static function string(): self
    {
        return self::$string ??= new self(SimpleType::STRING);
    }

    public function getTypeName(): string
    {
        return sprintf('list<%s>', $this->valueType->getTypeName());
    }

    public function getDisplayName(mixed $comparedTo = null): string
    {
        return "a list of {$this->valueType->getPluralDisplayName()}";
    }

    public function getPluralDisplayName(): string
    {
        return "lists of {$this->valueType->getPluralDisplayName()}";
    }
}
