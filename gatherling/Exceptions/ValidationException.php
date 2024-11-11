<?php

declare(strict_types=1);

namespace Gatherling\Exceptions;

class ValidationException extends BadRequestException
{
    public function getUserMessage(): string
    {
        return 'The request was invalid';
    }
}
