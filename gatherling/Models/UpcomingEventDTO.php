<?php

declare(strict_types=1);

namespace Gatherling\Models;

class UpcomingEventDTO extends DTO
{
    public int $d;
    public string $name;
    public string $threadurl;
    public string $format;
    public string $series;
    public string $season;
}
