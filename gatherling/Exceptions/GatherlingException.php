<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Exception;
use Throwable;

abstract class GatherlingException extends Exception
{
    public readonly int $httpStatusCode;

    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        int $httpStatusCode = 500,
    ) {
        parent::__construct($message, $code, $previous);
        $this->httpStatusCode = $httpStatusCode;
    }
}
