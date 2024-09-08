<?php

declare(strict_types=1);

namespace Gatherling\Tests;

use Gatherling\Data\DB;
use PHPUnit\Framework\TestCase;

// Slightly odd name because PHPUnit issues a warning if "Test" is in the name.
abstract class DatabaseCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::begin($this->transactionName());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        DB::rollback($this->transactionName());
    }

    private function transactionName(): string
    {
        $classHash = substr(md5(get_class($this)), 0, 7);

        return $this->name().'_'.$classHash;
    }
}
