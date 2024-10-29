<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

class Request
{
    /** @param array<int|string, mixed> $vars */
    public function __construct(private array $vars)
    {
    }

    public function int(string $key, int|false $default = false): int
    {
        return marshal($this->vars[$key] ?? null)->int($default);
    }

    public function optionalInt(string $key): ?int
    {
        return marshal($this->vars[$key] ?? null)->optionalInt();
    }

    public function string(string $key, string|false $default = false): string
    {
        return marshal($this->vars[$key] ?? null)->string($default);
    }

    public function optionalString(string $key): ?string
    {
        return marshal($this->vars[$key] ?? null)->optionalString();
    }

    public function float(string $key, float|false $default = false): float
    {
        return marshal($this->vars[$key] ?? null)->float($default);
    }

    public function optionalFloat(string $key): ?float
    {
        return marshal($this->vars[$key] ?? null)->optionalFloat();
    }

    /** @return list<int> */
    public function listInt(string $key): array
    {
        return marshal($this->vars[$key] ?? null)->ints();
    }

    /** @return list<string> */
    public function listString(string $key): array
    {
        return marshal($this->vars[$key] ?? null)->strings();
    }

    /** @return array<string, int|string> */
    public function dictIntOrString(string $key): array
    {
        return marshal($this->vars[$key] ?? null)->dictIntOrString();
    }

    /** @return array<string, int> */
    public function dictInt(string $key): array
    {
        return marshal($this->vars[$key] ?? null)->dictInt();
    }

    /** @return array<string, string> */
    public function dictString(string $key): array
    {
        return marshal($this->vars[$key] ?? null)->dictString();
    }
}
