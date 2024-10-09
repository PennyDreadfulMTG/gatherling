<?php

namespace Gatherling\Models;

class ReportDto extends Dto
{
    public int $subevent;
    public int $playera_wins;
    public int $playerb_wins;
    public int $playera_losses;
    public int $playerb_losses;
}
