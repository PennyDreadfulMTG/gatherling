<?php

declare(strict_types=1);

namespace Gatherling\Tests;

use Gatherling\Data\Setup;
use Gatherling\Log;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\TestRunner\StartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade as EventFacade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class DatabaseTestListener implements Extension
{
    public function bootstrap(Configuration $configuration, EventFacade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class() implements StartedSubscriber {
            public function notify(Started $event): void
            {
                Log::info('Tests started, setting up test database');
                Setup::setupTestDatabase();
            }
        });

        $facade->registerSubscriber(new class() implements FinishedSubscriber {
            public function notify(Finished $event): void
            {
                Log::info('Tests ended, dropping test database');
                Setup::dropTestDatabase();
            }
        });
    }
}
