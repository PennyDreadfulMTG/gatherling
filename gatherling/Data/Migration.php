<?php

declare(strict_types=1);

namespace Gatherling\Data;

class Migration
{
    public function __construct(public int $version, public string $sql)
    {
    }
}
