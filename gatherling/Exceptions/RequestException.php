<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Gatherling\Helpers\Types\Type;
use Throwable;

class RequestException extends BadRequestException
{
    public function __construct(
        private string $key,
        private Type $expectedType,
        private mixed $value,
        Throwable $previous
    ) {
        $s = "Error processing request parameter '{$key}', expecting {$expectedType->getTypeName()} but got " . var_export($value, true);
        parent::__construct($s, 0, $previous);
    }

    public function getUserMessage(): string
    {
        $s = "The {$this->key} field requires {$this->expectedType->getDisplayName($this->value)} but it was ";
        if ($this->value === null) {
            $s .= "missing";
        } elseif ($this->value === '') {
            $s .= "blank";
        } elseif (is_scalar($this->value)) {
            $s .= "'{$this->value}'";
        } else {
            $s .= "invalid";
        }
        return $s;
    }
}
