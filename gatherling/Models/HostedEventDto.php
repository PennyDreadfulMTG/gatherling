<?php

declare(strict_types=1);

namespace Gatherling\Models;

class HostedEventDto extends Dto
{
    public string $name;
    public string $format;
    public int $players;
    public string $host;
    public string $start;
    public int $active;
    public int $finalized;
    public ?string $cohost;
    public string $series;
    public int $kvalue;
    public int $current_round;
}
