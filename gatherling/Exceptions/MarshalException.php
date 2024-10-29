<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

class MarshalException extends GatherlingException
{
    public function __construct(public mixed $value, public string $typeRequested)
    {
        $type = gettype($this->value);
        $repr = var_export($this->value, true);
        parent::__construct("Unable to marshal variable of type $type as $this->typeRequested: $repr");
    }
}
