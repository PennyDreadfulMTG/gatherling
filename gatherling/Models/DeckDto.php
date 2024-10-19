<?php

declare(strict_types=1);

namespace Gatherling\Models;

class DeckDto extends Dto
{
    public int $id;
    public string $name;
    public string $playername;
    public ?string $archetype;
    public ?string $format;
    public ?string $tribe;
    public ?string $notes;
    public ?string $deck_hash;
    public ?string $sideboard_hash;
    public ?string $whole_hash;
    public ?string $created_date;
    public ?string $deck_colors;
}
