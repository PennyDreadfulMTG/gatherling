<?php

declare(strict_types=1);

namespace Gatherling\Models;

class CardSetDto extends Dto
{
    public string $name;
    public ?string $code;
    public string $released;
    public string $type;
    public ?string $last_updated;
    public int $count;
    public bool $standard_legal;
    public bool $modern_legal;
}
