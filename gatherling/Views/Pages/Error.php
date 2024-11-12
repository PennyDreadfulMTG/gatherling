<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

class Error extends Page
{
    public bool $canRetry;

    public function __construct(public string $requestId, public string $message, int $httpStatusCode, public string $debug)
    {
        parent::__construct('Error', true);
        http_response_code($httpStatusCode);
        $this->canRetry = $httpStatusCode >= 400 && $httpStatusCode < 500;
    }
}
