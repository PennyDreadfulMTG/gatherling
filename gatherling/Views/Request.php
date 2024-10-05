<?php

namespace Gatherling\Views;

class Request
{
    /** @var array<string, mixed> */
    private array $post;
    /** @var array<string, mixed> */
    private array $get;
    /** @var array<string, mixed> */
    private array $request;

    public function __construct()
    {
        $this->post = $_POST;
        $this->get = $_GET;
        $this->request = $_REQUEST;
    }

    public function post(): self
    {
        return $this->createDataAccessor($this->post);
    }

    public function get(): self
    {
        return $this->createDataAccessor($this->get);
    }

    public function request(): self
    {
        return $this->createDataAccessor($this->request);
    }

    /** @param array<string, mixed> $data */
    private function createDataAccessor(array $data): self
    {
        $accessor = new self();
        $accessor->post = $data;
        $accessor->get = $data;
        $accessor->request = $data;
        return $accessor;
    }

    public function int(string $key, int $default = 0): int
    {
        if (!isset($this->request[$key]) || !is_numeric($this->request[$key])) {
            return $default;
        }
        return (int) $this->request[$key];
    }

    public function intOr(string $key, ?int $default = null): ?int
    {
        return isset($this->request[$key]) && is_numeric($this->request[$key]) ? (int) $this->request[$key] : $default;
    }

    public function string(string $key, string $default = ''): string
    {
        if (!isset($this->request[$key]) || !is_string($this->request[$key])) {
            return $default;
        }
        return (string) $this->request[$key];
    }

    public function stringOr(string $key, ?string $default = null): ?string
    {
        return isset($this->request[$key]) && is_string($this->request[$key]) ? (string) $this->request[$key] : $default;
    }
}
