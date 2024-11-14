<?php

declare(strict_types=1);

namespace Gatherling\Models;

class ValidPlayerDto extends Dto
{
    public string $name;
    public bool $discord_conflict;
    public bool $mtga_conflict;
    public bool $mtgo_conflict;
}
