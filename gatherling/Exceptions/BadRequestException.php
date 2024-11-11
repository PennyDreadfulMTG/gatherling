<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Throwable;

abstract class BadRequestException extends GatherlingException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, int $httpStatusCode = 400)
    {
        parent::__construct($message, $code, $previous, $httpStatusCode);
    }

    abstract public function getUserMessage(): string;
}
