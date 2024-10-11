<?php

declare(strict_types=1);

namespace Gatherling\Views;

class Request
{
    /** @param array<int|string, mixed> $vars */
    public function __construct(private array $vars)
    {
    }

    public function int(string $key, int|false $default = false): int
    {
        $value = $this->optionalInt($key);
        if ($value === null) {
            if ($default !== false) {
                return $default;
            }
            throw new \InvalidArgumentException("Missing integer value: " . $key);
        }
        return $value;
    }

    public function optionalInt(string $key): ?int
    {
        if (!isset($this->vars[$key])) {
            return null;
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
        return $value;
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
        return $value;
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


    /** @return list<string> */
    public function listString(string $key): array
    {
        if (!isset($this->vars[$key])) {
            return [];
        }
        if (!is_array($this->vars[$key])) {
            throw new \InvalidArgumentException("Invalid list of strings: " . var_export($this->vars[$key], true));
        }
        $result = [];
        foreach ($this->vars[$key] as $value) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException("Invalid string value: " . var_export($value, true));
            }
            $result[] = (string) $value;
        }
        return $result;
    }

    /** @return array<string, int|string> */
    public function dictIntOrString(string $key): array
    {
        if (!isset($this->vars[$key])) {
            return [];
        }
        if (!is_array($this->vars[$key])) {
            throw new \InvalidArgumentException("Invalid dictionary of integers or strings: " . var_export($this->vars[$key], true));
        }
        $result = [];
        foreach ($this->vars[$key] as $key => $value) {
            if (is_int($value)) {
                $result[$key] = (int) $value;
            } elseif (is_string($value)) {
                $result[$key] = (string) $value;
            } else {
                throw new \InvalidArgumentException("Invalid value in dictionary: " . var_export($value, true));
            }
        }
        return $result;
    }

    /** @return array<string, int> */
    public function dictInt(string $key): array
    {
        if (!isset($this->vars[$key])) {
            return [];
        }
        if (!is_array($this->vars[$key])) {
            throw new \InvalidArgumentException("Invalid dictionary of integers: " . var_export($this->vars[$key], true));
        }
        $result = [];
        foreach ($this->vars[$key] as $key => $value) {
            if (!is_int($value)) {
                throw new \InvalidArgumentException("Invalid integer value for $key: " . var_export($value, true));
            }
            $result[$key] = (int) $value;
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
