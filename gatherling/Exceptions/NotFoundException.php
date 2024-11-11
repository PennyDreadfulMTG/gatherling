<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

use Throwable;

class NotFoundException extends BadRequestException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous, 404);
    }

    public function getUserMessage(): string
    {
        return 'The requested resource was not found';
    }
}
