<?php

declare(strict_types=1);

namespace Gatherling\Helpers\Types;

class UnionType implements Type
{
    /** @var list<Type> */
    private array $types;

    public function __construct(Type ...$types)
    {
        $this->types = array_values($types);
    }

    public function getTypeName(): string
    {
        $typeNames = array_map(fn(Type $type) => $type->getTypeName(), $this->types);
        return implode('|', $typeNames);
    }

    public function getDisplayName(mixed $comparedTo = null): string
    {
        $displayNames = array_map(fn(Type $type) => $type->getDisplayName(), $this->types);
        return implode(' or ', array_unique($displayNames));
    }

    public function getPluralDisplayName(): string
    {
        $displayNames = array_map(fn(Type $type) => $type->getPluralDisplayName(), $this->types);
        return implode(' or ', array_unique($displayNames));
    }
}
