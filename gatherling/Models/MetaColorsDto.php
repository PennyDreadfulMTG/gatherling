<?php

declare(strict_types=1);

namespace Gatherling\Models;

class MetaColorsDto extends Dto
{
    /** @psalm-suppress PossiblyUnusedProperty */
    public string $colors;
    /** @psalm-suppress PossiblyUnusedProperty */
    public int $cnt;
}
