<?php

declare(strict_types=1);

namespace Gatherling\Helpers\Types;

enum SimpleType: string implements Type
{
    case INT = 'int';
    case STRING = 'string';
    case FLOAT = 'float';
    case BOOL = 'bool';

    public function getTypeName(): string
    {
        return $this->value;
    }

    public function getDisplayName(mixed $comparedTo = null): string
    {
        $isFloat = is_numeric($comparedTo) && ((float) $comparedTo !== (float) (int) $comparedTo);
        return match ($this) {
            self::INT => $isFloat ? 'a whole number' : 'a number',
            self::STRING => 'some text',
            self::FLOAT => 'a number',
            self::BOOL => 'true or false',
        };
    }

    public function getPluralDisplayName(): string
    {
        return match ($this) {
            self::INT => 'numbers',
            self::STRING => 'text',
            self::FLOAT => 'numbers',
            self::BOOL => 'true/false values',
        };
    }
}
