<?php

declare(strict_types=1);

namespace Gatherling\Tests\Support\Listeners;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PassedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade as EventFacade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

use function Gatherling\Helpers\logger;

class LogTestListener implements Extension
{
    public function bootstrap(Configuration $configuration, EventFacade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements PassedSubscriber {
            public function notify(Passed $event): void
            {
                // Don't output the log if the test passed
                logger()->clear();
            }
        });
        $facade->registerSubscriber(new class implements FailedSubscriber {
            public function notify(Failed $event): void
            {
                // Output the log if the test failed
                logger()->flush();
            }
        });
    }
}
