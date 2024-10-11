<?php

declare(strict_types=1);

namespace Gatherling\Models;

class ReportDto extends Dto
{
    public int $subevent;
    public int $playera_wins;
    public int $playerb_wins;
    public int $playera_losses;
    public int $playerb_losses;
}
