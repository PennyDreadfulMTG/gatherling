<?php

declare(strict_types=1);

namespace Gatherling\Views;

use InvalidArgumentException;

class JsonResponse extends Response
{
    /** @param array<array-key, mixed> $data */
    public function __construct(private array $data)
    {
        $this->setHeader('Content-type', 'application/json');
    }

    public function body(): string
    {
        $result = json_encode($this->data);
        if ($result === false) {
            throw new InvalidArgumentException('Failed to encode data to JSON');
        }
        return $result;
    }
}
