<?php

declare(strict_types=1);

namespace Gatherling\Helpers\Types;

abstract class CompositeType implements Type
{
    protected function __construct(
        protected Type $valueType,
    ) {
    }

    abstract public function getTypeName(): string;
    abstract public function getDisplayName(mixed $comparedTo = null): string;
    abstract public function getPluralDisplayName(): string;
}
