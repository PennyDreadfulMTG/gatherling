<?php

declare(strict_types=1);

namespace Gatherling\Models;

class MetaDTO extends DTO
{
    public string $player;
    public string $deckname;
    public string $archetype;
    public string $colors;
    public string $medal;
    public int $id;
    public int $srtordr;
}
