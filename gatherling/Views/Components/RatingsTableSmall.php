<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;
use Gatherling\Models\Ratings;

class RatingsTableSmall extends Component
{
    /** @var array<array{name: string, rating: int}> */
    public array $ratings;

    public function __construct(Player $player)
    {
        $ratings = new Ratings();
        $compositeRating = $player->getRating('Composite');
        $ratingsInfo = [
            [
                'name' => 'Composite',
                'rating' => $compositeRating ?: 1600,
            ],
        ];
        $ratingNames = $ratings->ratingNames;
        $ratingNames[] = 'Other Formats';
        foreach ($ratingNames as $name) {
            $rating = $player->getRating($name);
            if ($rating != 0 && $rating != 1600) {
                $ratingsInfo[] = [
                    'name' => $name,
                    'rating' => $rating,
                ];
            }
        }

        $this->ratings = $ratingsInfo;
    }
}
