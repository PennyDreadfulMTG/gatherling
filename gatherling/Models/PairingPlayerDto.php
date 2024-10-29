<?php

declare(strict_types=1);

namespace Gatherling\Models;

class PairingPlayerDto extends Dto
{
    public string $player;
    public int $byes;
    public int $score;
}
