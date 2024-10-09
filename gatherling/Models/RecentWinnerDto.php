<?php

declare(strict_types=1);

namespace Gatherling\Models;

class RecentWinnerDto extends Dto
{
    public string $event;
    public string $player;
    public string $name;
    public int $id;
}
