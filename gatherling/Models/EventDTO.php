<?php

declare(strict_types=1);

namespace Gatherling\Models;

class EventDTO extends DTO
{
    public string $name;
    public string $format;
    public int $players;
    public string $host;
    public string $start;
    public int $finalized;
    public string $cohost;
    public string $series;
    public string $season;
}
