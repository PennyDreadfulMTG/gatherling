<?php

declare(strict_types=1);

namespace Gatherling\Views;

use InvalidArgumentException;

use function Safe\json_encode;

class JsonResponse extends Response
{
    /** @param array<array-key, mixed> $data */
    public function __construct(private array $data)
    {
        $this->setHeader('Content-type', 'application/json');
    }

    public function body(): string
    {
        return json_encode($this->data);
    }
}
