<?php

namespace Gatherling\Views;

class Request
{
    /** @param array<int|string, mixed> $vars */
    public function __construct(private array $vars)
    {
    }

    public function int(string $key): int
    {
        $value = $this->optionalInt($key);
        if ($value === null) {
            throw new \InvalidArgumentException("Missing integer value: " . $key);
        }
        return $value;
    }

    public function optionalInt(string $key): ?int
    {
        if (!isset($this->vars[$key])) {
            return null;
        }
        if (!is_numeric($this->vars[$key])) {
            throw new \InvalidArgumentException("Invalid integer value for $key: " . var_export($this->vars[$key], true));
        }
        return (int) $this->vars[$key];
    }

    public function string(string $key): string
    {
        $value = $this->optionalString($key);
        if ($value === null) {
            throw new \InvalidArgumentException("Missing string value: " . $key);
        }
        return $value;
    }

    public function optionalString(string $key): ?string
    {
        if (!isset($this->vars[$key])) {
            return null;
        }
        if (!is_string($this->vars[$key])) {
            throw new \InvalidArgumentException("Invalid string value for $key: " . var_export($this->vars[$key], true));
        }
        return (string) $this->vars[$key];
    }

    /** @return list<int> */
    public function listInt(string $key): array
    {
        if (!isset($this->vars[$key])) {
            return [];
        }
        if (!is_array($this->vars[$key])) {
            throw new \InvalidArgumentException("Invalid list of integers: " . var_export($this->vars[$key], true));
        }
        $result = [];
        foreach ($this->vars[$key] as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException("Invalid integer value: " . var_export($value, true));
            }
            $result[] = (int) $value;
        }
        return $result;
    }
}
