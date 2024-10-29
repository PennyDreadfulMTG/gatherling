<?php

declare(strict_types=1);

namespace Gatherling\Models;

class SubeventDto extends Dto
{
    public ?string $parent;
    public int $rounds;
    public int $timing;
    public string $type;
}
