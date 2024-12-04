<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Throwable;

use function Gatherling\Helpers\db;

class DatabaseException extends GatherlingException
{
    /** @param array<array-key, mixed> $params */
    public function __construct(
        string $baseMessage,
        ?Throwable $previous = null,
        string $sql = '',
        array $params = [],
    )
    {
        $msg = $baseMessage;
        if ($sql) {
            $msg .= ' (' . db()->interpolateQuery($sql, $params) . ')';
        }
        parent::__construct($msg, 0, $previous);
    }
}
