<?php

declare(strict_types=1);

namespace Gatherling\Models;

class HostedEventDto extends Dto
{
    public string $name;
    public int $players;
    public string $start;
    public int $active;
    public int $finalized;
    public string $series;
    public int $current_round;
}
