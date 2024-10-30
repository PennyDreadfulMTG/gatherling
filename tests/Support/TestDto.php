<?php

declare(strict_types=1);

namespace Gatherling\Tests\Support;

use Gatherling\Models\Dto;

class TestDto extends Dto
{
    public ?int $id;
    public string $name;
    public ?float $value;
    public ?bool $is_active;
}
