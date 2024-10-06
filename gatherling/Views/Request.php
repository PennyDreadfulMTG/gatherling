<?php

namespace Gatherling\Views;

class Request
{
    /** @var array<string, mixed> */
    private array $current;

    /**
     * @param array<string, mixed> $request
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     */
    public function __construct(private array $request, private array $get, private array $post)
    {
        $this->request = $request;
        $this->get = $get;
        $this->post = $post;
        $this->current = $this->request;
    }

    public function post(): self
    {
        $this->current = $this->post;
        return $this;
    }

    public function get(): self
    {
        $this->current = $this->get;
        return $this;
    }

    // BAKERT tests
    // BAKERT reimplmeent string this way too, and list?
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
        if (!isset($this->current[$key])) {
            return null;
        }
        if (!is_numeric($this->current[$key])) {
            throw new \InvalidArgumentException("Invalid integer value for $key: " . var_export($this->current[$key], true));
        }
        return (int) $this->current[$key];
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
        if (!isset($this->current[$key])) {
            return null;
        }
        if (!is_string($this->current[$key])) {
            throw new \InvalidArgumentException("Invalid string value for $key: " . var_export($this->current[$key], true));
        }
        return (string) $this->current[$key];
    }

    /** @return list<int> */
    public function listInt(string $key): array
    {
        if (!isset($this->current[$key])) {
            return [];
        }
        if (!is_array($this->current[$key])) {
            throw new \InvalidArgumentException("Invalid list of integers: " . var_export($this->current[$key], true));
        }
        $result = [];
        foreach ($this->current[$key] as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException("Invalid integer value: " . var_export($value, true));
            }
            $result[] = (int) $value;
        }
        return $result;
    }
}
