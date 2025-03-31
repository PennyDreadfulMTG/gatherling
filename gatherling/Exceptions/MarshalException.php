<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Gatherling\Helpers\Types\Type;
use Gatherling\Helpers\Types\TypeMismatch;

class MarshalException extends GatherlingException
{
    public function __construct(
        mixed $value,
        public readonly Type $expectedType,
        TypeMismatch $typeMismatch
    ) {
        $type = gettype($value);
        $repr = var_export($value, true);
        parent::__construct("Unable to marshal variable of gettype $type as {$expectedType->getTypeName()} due to $typeMismatch->value: $repr");
    }
}
