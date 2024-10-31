<?php

declare(strict_types=1);

namespace Gatherling\Models;

class FormatDto extends Dto
{
    public string $name;
    public string $description;
    public string $type;
    public string $series_name;
    public int $singleton;
    public int $commander;
    public int $planechase;
    public int $vanguard;
    public int $prismatic;
    public int $tribal;
    public int $pure;
    public int $underdog;
    public int $limitless;
    public int $allow_commons;
    public int $allow_uncommons;
    public int $allow_rares;
    public int $allow_mythics;
    public int $allow_timeshifted;
    public int $priority;
    public int $min_main_cards_allowed;
    public int $max_main_cards_allowed;
    public int $min_side_cards_allowed;
    public int $max_side_cards_allowed;
    public int $eternal;
    public int $modern;
    public int $standard;
    public int $is_meta_format;
}
