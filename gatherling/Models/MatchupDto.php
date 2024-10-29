<?php

declare(strict_types=1);

namespace Gatherling\Models;

class MatchupDto extends Dto
{
    public int $id;
    public int $subevent;
    public int $round;
    public string $playera;
    public string $playerb;
    public string $result;
    public int $playera_wins;
    public int $playera_losses;
    public int $playera_draws;
    public int $playerb_wins;
    public int $playerb_losses;
    public int $playerb_draws;
    public int $timing;
    public string $type;
    public int $rounds;
    public string $format;
    public string $series;
    public int $season;
    public string $verification;
    public string $eventname;
    public int $event_id;
}
