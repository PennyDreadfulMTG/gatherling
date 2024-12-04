<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Throwable;

class NotFoundInDatabaseException extends DatabaseException
{
    /**
     * @param list<int|string> $ids
     * @param array<string, mixed> $params
     */
    public function __construct(
        string $message,
        ?Throwable $previous,
        string $sql,
        array $params,
        public string $type,
        public array $ids
    ) {
        parent::__construct($message, $previous, $sql, $params);
    }
}
