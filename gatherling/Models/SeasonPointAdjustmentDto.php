<?php

declare(strict_types=1);

namespace Gatherling\Models;

class SeasonPointAdjustmentDto extends Dto
{
    public int $adjustment;
    public string $reason;
}
