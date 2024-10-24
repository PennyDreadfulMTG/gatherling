<?php

declare(strict_types=1);

namespace Gatherling\Views;

class Response
{
    /** @var array<string, string> */
    private array $headers = [];

    public function getHeader(string $header): ?string
    {
        return $this->headers[$this->normalize($header)] ?? null;
    }

    public function setHeader(string $header, string $value): void
    {
        $this->headers[$this->normalize($header)] = $value;
    }

    public function body(): string
    {
        return '';
    }

    public function send(): never
    {
        foreach ($this->headers as $header => $value) {
            header("$header: $value");
        }
        echo $this->body();
        exit;
    }

    private function normalize(string $header): string
    {
        return ucwords(strtolower($header), '-');
    }
}
