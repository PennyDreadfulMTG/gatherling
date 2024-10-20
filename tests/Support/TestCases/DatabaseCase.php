<?php

declare(strict_types=1);

namespace Gatherling\Tests\Support\TestCases;

use PHPUnit\Framework\TestCase;

use function Gatherling\Helpers\db;

// Slightly odd name because PHPUnit issues a warning if "Test" is in the name.
abstract class DatabaseCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!empty($this->requires())) {
            return;
        }
        db()->begin($this->transactionName());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (!empty($this->requires())) {
            return;
        }
        db()->rollback($this->transactionName());
    }

    private function transactionName(): string
    {
        $classHash = substr(md5(get_class($this)), 0, 7);
        return $this->name() . '_' . $classHash;
    }
}
