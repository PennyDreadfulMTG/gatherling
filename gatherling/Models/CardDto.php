<?php

declare(strict_types=1);

namespace Gatherling\Models;

class CardDto extends Dto
{
    public int $id;
    public string $name;
    public string $type;
    public string $rarity;
    public string $scryfallId;
    public bool $is_changeling;
    public string $cardset;
    public int $count;
}
