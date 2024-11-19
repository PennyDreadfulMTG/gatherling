<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Throwable;

class NotFoundException extends BadRequestException
{
    /** @var list<int|string> */
    public array $ids;

    /** @param list<int|string|null> $ids */
    public function __construct(
        string $message,
        int $code,
        ?Throwable $previous,
        public string $type,
        array $ids
    ) {
        $this->ids = array_values(array_filter($ids, fn($id) => $id !== null));
        parent::__construct($message, $code, $previous, 404);
    }

    public function getUserMessage(): string
    {
        $msg = 'The requested ' . strtolower($this->type);
        if ($this->ids !== []) {
            $msg .= " (" . implode(', ', $this->ids) . ")";
        }
        return $msg . " was not found";
    }
}
