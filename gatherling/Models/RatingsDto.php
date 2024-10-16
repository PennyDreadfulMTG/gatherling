<?php

declare(strict_types=1);

namespace Gatherling\Models;

class RatingsDto extends Dto
{
    public string $name;
    public int $event_id;
    public float $rating;
    public ?string $medal;
    public ?int $deck_id;
}
