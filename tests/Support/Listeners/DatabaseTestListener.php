<?php

declare(strict_types=1);

namespace Gatherling\Tests\Support\Listeners;

use Gatherling\Data\Setup;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\TestRunner\StartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade as EventFacade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

use function Gatherling\Helpers\logger;

class DatabaseTestListener implements Extension
{
    public function bootstrap(Configuration $configuration, EventFacade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements StartedSubscriber {
            public function notify(Started $event): void
            {
                logger()->info('Tests started, setting up test database');
                Setup::setupTestDatabase();
            }
        });

        $facade->registerSubscriber(new class implements FinishedSubscriber {
            public function notify(Finished $event): void
            {
                logger()->info('Tests ended, dropping test database');
                Setup::dropTestDatabase();
            }
        });
    }
}
