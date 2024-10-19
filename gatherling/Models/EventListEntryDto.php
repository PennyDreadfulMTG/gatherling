<?php

declare(strict_types=1);

namespace Gatherling\Models;

class EventListEntryDto extends Dto
{
    public string $name;
    public string $format;
    public int $players;
    public string $host;
    public ?string $cohost;
}
