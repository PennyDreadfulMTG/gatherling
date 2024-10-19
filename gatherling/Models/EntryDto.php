<?php

namespace Gatherling\Models;

class EntryDto extends Dto
{
    public ?int $deck_id;
    public string $medal;
    public ?int $ignored;
    public int $drop_round;
    public int $initial_byes;
    public ?int $initial_seed;
}

