<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use Gatherling\Exceptions\MarshalException;

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
            throw new MarshalException($this->value, 'int');
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
            throw new MarshalException($this->value, 'string');
        }
        return $value;
    }

    public function optionalString(): ?string
    {
        if ($this->value === null) {
            return null;
        }
        if (!is_string($this->value)) {
            throw new MarshalException($this->value, 'optionalString');
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
            throw new MarshalException($this->value, 'float');
        }
        return $value;
    }

    public function optionalFloat(): ?float
    {
        if ($this->value === null) {
            return null;
        }
        if (!is_numeric($this->value)) {
            throw new MarshalException($this->value, 'optionalFloat');
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
            throw new MarshalException($this->value, 'listInt');
        }
        $result = [];
        foreach ($this->value as $value) {
            if (!is_numeric($value)) {
                throw new MarshalException($value, 'listIntEntry');
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
            throw new MarshalException($this->value, 'listString');
        }
        $result = [];
        foreach ($this->value as $value) {
            if (!is_string($value)) {
                throw new MarshalException($value, 'listStringEntry');
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
            throw new MarshalException($this->value, 'dictIntOrString');
        }
        $result = [];
        foreach ($this->value as $key => $value) {
            if (is_int($value)) {
                $result[$key] = (int) $value;
            } elseif (is_string($value)) {
                $result[$key] = (string) $value;
            } else {
                throw new MarshalException($value, 'dictIntOrStringEntry');
            }
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
            throw new MarshalException($this->value, 'dictInt');
        }
        $result = [];
        foreach ($this->value as $key => $value) {
            if (!is_string($key)) {
                throw new MarshalException($key, 'dictIntKey');
            }
            if (!is_int($value)) {
                throw new MarshalException($value, 'dictIntValue');
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
            throw new MarshalException($this->value, 'dictString');
        }
        $result = [];
        foreach ($this->value as $key => $value) {
            if (!is_string($key)) {
                throw new MarshalException($key, 'dictStringKey');
            }
            if (!is_string($value)) {
                throw new MarshalException($value, 'dictStringValue');
            }
            $result[$key] = $value;
        }
        return $result;
    }

    private function strictIntCheck(mixed $value): void
    {
        if (!is_scalar($value) && !is_null($value)) {
            throw new MarshalException($value, 'strictIntCheckScalar');
        }
        $canBeInt = is_int($value) || is_float($value) || (is_string($value) && is_numeric($value));
        if (!$canBeInt) {
            throw new MarshalException($value, 'strictIntCheckInt');
        }
        if ((string)(int) $value !== (string) $value) {
            throw new MarshalException($value, 'strictIntCheckDetail');
        }
    }
}
