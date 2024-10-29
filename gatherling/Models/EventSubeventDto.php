<?php

declare(strict_types=1);

namespace Gatherling\Models;

class EventSubeventDto extends Dto
{
    public int $mainid;
    public string $rounds;
    public string $type;
}
