<?php

declare(strict_types=1);

namespace Gatherling\Models;

class EventSubeventDto extends Dto
{
    public int $mainid;
    public int $rounds;
    public string $type;
}
