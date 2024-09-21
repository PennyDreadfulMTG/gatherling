<?php

namespace Gatherling\Views\Components;

class RatingsTable extends Component
{
    public PlayerLink $playerLink;

    public function __construct(public int $minMatches, public array $ratingsData)
    {
        foreach ($this->ratingsData as &$vals) {
            $vals['playerLink'] = new PlayerLink($vals['player']);
        }
    }
}
