<?php

declare(strict_types=1);

namespace Gatherling\Models;

class CalendarEventDTO extends DTO
{
    public int $d;
    public string $name;
    public string $threadurl;
}
