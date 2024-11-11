<?php

declare(strict_types=1);

namespace Gatherling\Helpers\Types;

interface Type
{
    public function getTypeName(): string;
    public function getDisplayName(mixed $comparedTo = null): string;
    public function getPluralDisplayName(): string;
}
