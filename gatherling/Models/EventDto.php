<?php

declare(strict_types=1);

namespace Gatherling\Models;

class EventDto extends Dto
{
    public int $id;
    public string $name;
    public string $format;
    public string $host;
    public ?string $cohost;
    public string $series;
    public int $season;
    public int $number;
    public string $start;
    public int $kvalue;
    public int $finalized;
    public int $prereg_allowed;
    public ?string $threadurl;
    public ?string $metaurl;
    public ?string $reporturl;
    public int $active;
    public int $current_round;
    public int $player_reportable;
    public int $player_editdecks;
    public int $prereg_cap;
    public int $private_decks;
    public int $private_finals;
    public int $player_reported_draws;
    public int $late_entry_limit;
    public ?int $private;
    public int $client;
}
