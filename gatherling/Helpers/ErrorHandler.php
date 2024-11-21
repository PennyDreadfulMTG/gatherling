<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use Gatherling\Exceptions\BadRequestException;
use Gatherling\Exceptions\GatherlingException;
use Gatherling\Exceptions\NotFoundException;
use Gatherling\Exceptions\NotFoundInDatabaseException;
use Gatherling\Views\Pages\Error;
use Throwable;

class ErrorHandler
{
    public function handle(Throwable $e): never
    {
        $requestId = Request::getRequestId();

        if ($e instanceof NotFoundInDatabaseException) {
            $e = new NotFoundException($e->getMessage(), $e->getCode(), $e, $e->type, $e->ids);
        }

        $message = (string)$e;

        if ($e instanceof BadRequestException) {
            logger()->warning($message);
            $userMessage = $e->getUserMessage();
        } else {
            logger()->error($message);
            $userMessage = "Oops! Something went wrong.";
        }
        $debug = config()->optionalString('env') === 'dev' ? $message : '';

        if ($e instanceof GatherlingException) {
            $httpStatusCode = $e->httpStatusCode;
        } else {
            $httpStatusCode = 500;
        }
        $page = new Error($requestId, $userMessage, $httpStatusCode, $debug);
        $page->send();
    }
}
