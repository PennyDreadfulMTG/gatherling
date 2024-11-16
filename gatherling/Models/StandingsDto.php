<?php

declare(strict_types=1);

namespace Gatherling\Models;

class StandingsDto extends Dto
{
    public ?int $active;
    public ?int $matches_played;
    public ?int $games_won;
    public ?int $games_played;
    public ?int $byes;
    public ?float $OP_Match;
    public ?float $PL_Game;
    public ?float $OP_Game;
    public ?int $score;
    public int $seed;
    public int $matched;
    public int $matches_won;
    public int $draws;
}
