<?php

declare(strict_types=1);

namespace Gatherling\Models;

class CalendarEventDto extends Dto
{
    public int $d;
    public string $name;
    public string $threadurl;
}
