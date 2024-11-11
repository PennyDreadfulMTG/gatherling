<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use Gatherling\Exceptions\MarshalException;
use Gatherling\Helpers\Types\DictType;
use Gatherling\Helpers\Types\ListType;
use Gatherling\Helpers\Types\SimpleType;
use Gatherling\Helpers\Types\TypeMismatch;

class Marshaller
{
    public function __construct(private mixed $value)
    {
    }

    public function int(int|false $default = false): int
    {
        $value = $this->optionalInt();
        if ($value === null) {
            if ($default !== false) {
                return $default;
            }
            throw new MarshalException($this->value, SimpleType::INT, TypeMismatch::NULL);
        }
        return $value;
    }

    public function optionalInt(): ?int
    {
        if ($this->value === null) {
            return null;
        }
        $value = (new Marshaller($this->value))->optionalFloat();
        $this->strictIntCheck($value);
        return (int) $value;
    }

    public function string(string|false $default = false): string
    {
        $value = $this->optionalString();
        if ($value === null) {
            if ($default !== false) {
                return $default;
            }
            throw new MarshalException($this->value, SimpleType::STRING, TypeMismatch::NULL);
        }
        return $value;
    }

    public function optionalString(): ?string
    {
        if ($this->value === null) {
            return null;
        }
        if (!is_string($this->value)) {
            throw new MarshalException($this->value, SimpleType::STRING, TypeMismatch::NOT_OF_TYPE);
        }
        return $this->value;
    }

    public function float(float|false $default = false): float
    {
        $value = $this->optionalFloat();
        if ($value === null) {
            if ($default !== false) {
                return $default;
            }
            throw new MarshalException($this->value, SimpleType::FLOAT, TypeMismatch::NULL);
        }
        return $value;
    }

    public function optionalFloat(): ?float
    {
        if ($this->value === null) {
            return null;
        }
        if (!is_numeric($this->value)) {
            throw new MarshalException($this->value, SimpleType::FLOAT, TypeMismatch::NOT_OF_TYPE);
        }
        return (float) $this->value;
    }

    /** @return list<int> */
    public function ints(): array
    {
        if ($this->value === null) {
            return [];
        }
        if (!is_array($this->value)) {
            throw new MarshalException($this->value, ListType::int(), TypeMismatch::NOT_ARRAY);
        }
        $result = [];
        foreach ($this->value as $value) {
            if (!is_numeric($value)) {
                throw new MarshalException($value, ListType::int(), TypeMismatch::INVALID_VALUE_TYPE);
            }
            $this->strictIntCheck($value);
            $result[] = (int) $value;
        }
        return $result;
    }


    /** @return list<string> */
    public function strings(): array
    {
        if ($this->value === null) {
            return [];
        }
        if (!is_array($this->value)) {
            throw new MarshalException($this->value, ListType::string(), TypeMismatch::NOT_ARRAY);
        }
        $result = [];
        foreach ($this->value as $value) {
            if (!is_string($value)) {
                throw new MarshalException($value, ListType::string(), TypeMismatch::INVALID_VALUE_TYPE);
            }
            $result[] = $value;
        }
        return $result;
    }

    /** @return array<string, int|string> */
    public function dictIntOrString(): array
    {
        if ($this->value === null) {
            return [];
        }
        if (!is_array($this->value)) {
            throw new MarshalException($this->value, DictType::intOrString(), TypeMismatch::NOT_ARRAY);
        }
        $result = [];
        foreach ($this->value as $key => $value) {
            if (is_scalar($value)) {
                try {
                    $this->strictIntCheck($value);
                    $result[$key] = (int) $value;
                    continue;
                } catch (MarshalException) {
                    if (is_string($value)) {
                        $result[$key] = $value;
                        continue;
                    }
                }
            }
            throw new MarshalException($value, DictType::intOrString(), TypeMismatch::INVALID_VALUE_TYPE);
        }
        return $result;
    }

    /** @return array<string, int> */
    public function dictInt(): array
    {
        if ($this->value === null) {
            return [];
        }
        if (!is_array($this->value)) {
            throw new MarshalException($this->value, DictType::int(), TypeMismatch::NOT_ARRAY);
        }
        $result = [];
        foreach ($this->value as $key => $value) {
            if (!is_string($key)) {
                throw new MarshalException($key, DictType::int(), TypeMismatch::INVALID_KEY_TYPE);
            }
            if (!is_int($value)) {
                throw new MarshalException($value, DictType::int(), TypeMismatch::INVALID_VALUE_TYPE);
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /** @return array<string, string> */
    public function dictString(): array
    {
        if ($this->value === null) {
            return [];
        }
        if (!is_array($this->value)) {
            throw new MarshalException($this->value, DictType::string(), TypeMismatch::NOT_ARRAY);
        }
        $result = [];
        foreach ($this->value as $key => $value) {
            if (!is_string($key)) {
                throw new MarshalException($key, DictType::string(), TypeMismatch::INVALID_KEY_TYPE);
            }
            if (!is_string($value)) {
                throw new MarshalException($value, DictType::string(), TypeMismatch::INVALID_VALUE_TYPE);
            }
            $result[$key] = $value;
        }
        return $result;
    }

    private function strictIntCheck(mixed $value): void
    {
        if (!is_scalar($value) && $value !== null) {
            throw new MarshalException($value, SimpleType::INT, TypeMismatch::NOT_SCALAR);
        }
        $canBeInt = is_int($value) || is_float($value) || (is_string($value) && is_numeric($value));
        if (!$canBeInt) {
            throw new MarshalException($value, SimpleType::INT, TypeMismatch::NOT_INT_COMPATIBLE);
        }
        if ((string)(int) $value !== (string) $value) {
            throw new MarshalException($value, SimpleType::INT, TypeMismatch::NOT_WHOLE_NUMBER);
        }
    }
}
