<?php

namespace Gatherling\Views;

class Request
{
    /** @param array<int|string, mixed> $vars */
    public function __construct(private array $vars)
    {
    }

    public function int(string $key, int|false $default = false): int
    {
        if (!isset($this->vars[$key])) {
            if ($default !== false) {
                return $default;
            }
            throw new \InvalidArgumentException("Missing integer value: " . $key);
        }
        $value = $this->vars[$key];
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("Invalid integer value for $key: " . var_export($value, true));
        }
        return (int) $value;
    }

    public function string(string $key, string|false $default = false): string
    {
        $value = $this->optionalString($key);
        if ($value === null) {
            if ($default !== false) {
                return $default;
            }
            throw new \InvalidArgumentException("Missing string value: " . $key);
        }
        return (string) $value;
    }

    public function optionalString(string $key): ?string
    {
        if (!isset($this->vars[$key])) {
            return null;
        }
        $value = $this->vars[$key];
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Invalid string value for $key: " . var_export($value, true));
        }
        return (string) $value;
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

    /** @return array<string, string> */
    public function dictString(string $key): array
    {
        if (!isset($this->vars[$key])) {
            return [];
        }
        if (!is_array($this->vars[$key])) {
            throw new \InvalidArgumentException("Invalid dictionary of strings: " . var_export($this->vars[$key], true));
        }
        $result = [];
        foreach ($this->vars[$key] as $key => $value) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException("Invalid string value for $key: " . var_export($value, true));
            }
            $result[$key] = (string) $value;
        }
        return $result;
    }
}
