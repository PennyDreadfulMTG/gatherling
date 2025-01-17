<?php

declare(strict_types=1);

namespace Gatherling\Models;

class DeckInfoDto extends Dto
{
    public int $id;
    public string $archetype;
    public string $name;
    public string $playername;
    public string $format;
    public string $created_date;
}
