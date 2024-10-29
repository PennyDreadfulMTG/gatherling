<?php

declare(strict_types=1);

namespace Gatherling\Models;

class FinalistDto extends Dto
{
    public ?int $deck;
    public string $medal;
    public string $player;
}
