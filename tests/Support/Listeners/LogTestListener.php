<?php

declare(strict_types=1);

namespace Gatherling\Tests\Support\Listeners;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade as EventFacade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

use function Gatherling\Helpers\logger;

class LogTestListener implements Extension
{
    public function bootstrap(Configuration $configuration, EventFacade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(
            new class implements FailedSubscriber {
                public function notify(Failed $event): void
                {
                    if (isset($_ENV['DEBUG'])) {
                        logger()->flush();
                    }
                }
            }
        );

        $facade->registerSubscriber(
            new class implements ErroredSubscriber {
                public function notify(Errored $event): void
                {
                    if (isset($_ENV['DEBUG'])) {
                        logger()->flush();
                    }
                }
            }
        );

        $facade->registerSubscriber(
            new class implements ExecutionFinishedSubscriber {
                public function notify(ExecutionFinished $event): void
                {
                    logger()->clear();
                }
            }
        );
    }
}
