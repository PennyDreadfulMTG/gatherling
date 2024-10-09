<?php

declare(strict_types=1);

namespace Gatherling\Models;

class PlayerRatingDTO extends DTO
{
    public string $player;
    public int $rating;
    public int $wins;
    public int $losses;
}
