<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Exceptions\ConfigurationException;
use Gatherling\Helpers\Request;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;
use Stringable;

use function Gatherling\Helpers\config;
use function Gatherling\Helpers\server;

class Logger implements LoggerInterface
{
    private MonologLogger $logger;

    public function __construct()
    {
        $this->logger = new MonologLogger('gatherling');
        if (defined('TESTING') && TESTING) {
            $bufferHandler = new BufferHandler(
                new StreamHandler('php://stderr', Level::Debug),
                0, // No buffer limit
                Level::Debug, // Minimum log level to buffer
                false, // Don't flush when script ends (flushgg manually)
                false // Do NOT flush on overflow
            );
            $this->logger->pushHandler($bufferHandler);
            return;
        }
        if (PHP_SAPI === 'cli') {
            $this->logger->pushHandler(new StreamHandler('php://stderr', Level::Debug));
            return;
        }
        $this->logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Level::Warning));
        $environment = config()->optionalString('env');
        if (!$environment) {
            throw new ConfigurationException('Environment configuration missing');
        }
        if ($environment === 'dev') {
            $this->logger->pushHandler(new BrowserConsoleHandler(Level::Info));
        }
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function log(mixed $level, Stringable|string $message, array $context = []): void
    {
        $context = array_merge($this->getRequestContext(), $context);
        // Work around a mismatch between LoggerInterface and Monolog\Logger types.
        $logLevel = $this->normalizeLogLevel($level);
        $this->logger->log($logLevel, $message, $context);
    }

    private function normalizeLogLevel(mixed $level): Level
    {
        if ($level instanceof Level) {
            return $level;
        }

        if (is_string($level)) {
            $level = strtolower($level);
            $acceptableNames = ['alert', 'critical', 'debug', 'emergency', 'error', 'info', 'notice', 'warning'];
            if (in_array($level, $acceptableNames, true)) {
                return Level::fromName($level);
            }
        }

        return Level::Warning;
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function clear(): void
    {
        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof BufferHandler) {
                $handler->clear();
            }
        }
    }

    public function flush(): void
    {
        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof BufferHandler) {
                $handler->flush();
            }
        }
    }

    /** @return array<string, mixed> */
    private function getRequestContext(): array
    {
        return [
            'request_id' => Request::getRequestId(),
            'url' => server()->string('REQUEST_URI', 'unknown'),
            'referer' => server()->string('HTTP_REFERER', 'none'),
            'post' => empty($_POST) ? [] : $_POST,
            'session' => empty($_SESSION) ? [] : $_SESSION,
            'method' => server()->string('REQUEST_METHOD', 'unknown'),
            'ip' => server()->string('REMOTE_ADDR', 'unknown'),
            'user_agent' => server()->string('HTTP_USER_AGENT', 'unknown')
        ];
    }

}
