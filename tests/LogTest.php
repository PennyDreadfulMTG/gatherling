<?php

declare(strict_types=1);

namespace Gatherling\Tests;

use Gatherling\Log;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogTest extends TestCase
{
    public function testLogsErrorWhenExceptionThrown(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('error');
        Log::setLogger($loggerMock);
        Log::error('An error occurred');
    }
}
