<?php

declare(strict_types=1);

namespace Gatherling\Models;

class MetaDto extends Dto
{
    public int $id;
    public string $player;
    public string $deckname;
    public string $archetype;
    public string $colors;
    public string $medal;
    public int $srtordr;
}
