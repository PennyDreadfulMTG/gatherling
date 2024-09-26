<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class RatingsTable extends Component
{
    public PlayerLink $playerLink;

    /** @param list<array{qplayer: string, qmax: int, player: string, rank: int, playerName: string, player: Player}> $ratingsData */
    public function __construct(public int $minMatches, public array $ratingsData)
    {
        foreach ($this->ratingsData as &$vals) {
            $vals['playerLink'] = new PlayerLink($vals['player']);
        }
    }
}
