<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use Gatherling\Exceptions\MarshalException;
use Gatherling\Exceptions\RequestException;
use Gatherling\Helpers\Marshaller;

class Request
{
    private static ?string $requestId = null;

    /** @param array<int|string, mixed> $vars */
    public function __construct(private array $vars)
    {
    }

    public static function getRequestId(): string
    {
        if (self::$requestId === null) {
            self::$requestId = substr(str_shuffle('0123456789bcdfghjklmnpqrstvwxz'), 0, 4);
        }
        return self::$requestId;
    }

    public function int(string $key, int|false $default = false): int
    {
        return $this->marshal($key, fn($m) => $m->int($default));
    }

    public function optionalInt(string $key): ?int
    {
        return $this->marshal($key, fn($m) => $m->optionalInt(), true);
    }

    public function string(string $key, string|false $default = false): string
    {
        return $this->marshal($key, fn($m) => $m->string($default));
    }

    public function optionalString(string $key): ?string
    {
        return $this->marshal($key, fn($m) => $m->optionalString());
    }

    public function float(string $key, float|false $default = false): float
    {
        return $this->marshal($key, fn($m) => $m->float($default));
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function optionalFloat(string $key): ?float
    {
        return $this->marshal($key, fn($m) => $m->optionalFloat(), true);
    }

    /** @return list<int> */
    public function listInt(string $key): array
    {
        return $this->marshal($key, fn($m) => $m->ints());
    }

    /** @return list<string> */
    public function listString(string $key): array
    {
        return $this->marshal($key, fn($m) => $m->strings());
    }

    /** @return array<string, int|string> */
    public function dictIntOrString(string $key): array
    {
        return $this->marshal($key, fn($m) => $m->dictIntOrString());
    }

    /** @return array<string, int> */
    public function dictInt(string $key): array
    {
        return $this->marshal($key, fn($m) => $m->dictInt());
    }

    /** @return array<string, string> */
    public function dictString(string $key): array
    {
        return $this->marshal($key, fn($m) => $m->dictString());
    }

    /**
     * @template T
     * @param string $key
     * @param callable(Marshaller): T $f
     * @param bool $nullIfEmptyString
     * @return T
     */
    private function marshal(string $key, callable $f, bool $nullIfEmptyString = false): mixed
    {
        /** @var string|int|float|null $value */
        $value = $this->vars[$key] ?? null;
        if ($nullIfEmptyString && $value === '') {
            $value = null;
        }
        try {
            return $f(marshal($value));
        } catch (MarshalException $e) {
            throw new RequestException($key, $e->expectedType, $value, $e);
        }
    }
}
