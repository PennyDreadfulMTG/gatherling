<?php

declare(strict_types=1);

namespace Gatherling\Models;

class DeckDto extends Dto
{
    public int $cnt;
    public string $playername;
    public string $name;
    public ?string $archetype;
    public ?string $format;
    public ?string $created_date;
    public int $id;
}
