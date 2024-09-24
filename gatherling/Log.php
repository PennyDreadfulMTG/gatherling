<?php

namespace Gatherling;

use Gatherling\Exceptions\ConfigurationException;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Log
{
    private static ?LoggerInterface $logger = null;

    private static function getLogger(): LoggerInterface
    {
        global $CONFIG;

        if (self::$logger !== null) {
            return self::$logger;
        }

        self::$logger = new Logger('gatherling');

        if (defined('TESTING') && TESTING) {
            $bufferHandler = new BufferHandler(
                new StreamHandler('php://stderr', Level::Debug),
                0, // No buffer limit
                Level::Debug, // Minimum log level to buffer
                false, // Don't flush when script ends (flush manually)
                false // Do NOT flush on overflow
            );
            self::$logger->pushHandler($bufferHandler);

            return self::$logger;
        }
        if (PHP_SAPI === 'cli') {
            self::$logger->pushHandler(new StreamHandler('php://stderr', Level::Debug));

            return self::$logger;
        }
        self::$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Level::Warning));
        $environment = $CONFIG['env'] ?? null;
        if (!$environment) {
            throw new ConfigurationException('Environment configuration missing');
        }
        if ($environment === 'dev') {
            self::$logger->pushHandler(new BrowserConsoleHandler(Level::Info));
        }

        return self::$logger;
    }

    // A little bit of dependency injection, for tests.
    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function emergency(string $message, array $context = []): void
    {
        self::getLogger()->emergency($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function alert(string $message, array $context = []): void
    {
        self::getLogger()->alert($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function critical(string $message, array $context = []): void
    {
        self::getLogger()->critical($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function notice(string $message, array $context = []): void
    {
        self::getLogger()->notice($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }

    public static function clear(): void
    {
        $logger = self::getLogger();
        // We accept any LoggerInterface, but we can only inspect handlers on a Monolog Logger
        if ($logger instanceof Logger) {
            foreach ($logger->getHandlers() as $handler) {
                if ($handler instanceof BufferHandler) {
                    $handler->clear();
                }
            }
        }
    }

    public static function flush(): void
    {
        $logger = self::getLogger();
        // We accept any LoggerInterface, but we can only inspect handlers on a Monolog Logger
        if ($logger instanceof Logger) {
            foreach ($logger->getHandlers() as $handler) {
                if ($handler instanceof BufferHandler) {
                    $handler->flush();
                }
            }
        }
    }
}
