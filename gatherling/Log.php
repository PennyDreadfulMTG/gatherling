<?php

namespace Gatherling;

use Gatherling\Exceptions\ConfigurationException;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
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
                new StreamHandler('php://stderr', Logger::DEBUG),
                0, // No buffer limit
                Logger::DEBUG, // Minimum log level to buffer
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
            throw new ConfigurationException("Environment configuration missing");
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

    public static function emergency($message, array $context = []): void
    {
        self::getLogger()->emergency($message, $context);
    }

    public static function alert($message, array $context = []): void
    {
        self::getLogger()->alert($message, $context);
    }

    public static function critical($message, array $context = []): void
    {
        self::getLogger()->critical($message, $context);
    }

    public static function error($message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    public static function warning($message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }

    public static function notice($message, array $context = []): void
    {
        self::getLogger()->notice($message, $context);
    }

    public static function info($message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    public static function debug($message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }

    public static function log($level, $message, array $context = []): void
    {
        self::getLogger()->log($level, $message, $context);
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
