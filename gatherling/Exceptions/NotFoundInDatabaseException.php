<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Throwable;

class NotFoundInDatabaseException extends DatabaseException
{
    /** @param list<int|string> $ids */
    public function __construct(
        string $message,
        int $code,
        ?Throwable $previous,
        public string $type,
        public array $ids
    ) {
        parent::__construct($message, $code, $previous);
    }
}
