<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use DateTime;
use Gatherling\Models\Player;
use Gatherling\Views\Components\PlayerLink;
use Gatherling\Views\Components\RatingsTable;
use Gatherling\Views\Components\FormatDropMenuR;
use Zebra_Pagination as Pagination;

class Ratings extends Page
{
    public FormatDropMenuR $formatDropMenuR;
    public string $highestRatingDate;
    public string $lastTournamentDate;
    public RatingsTable $ratingsTable;
    public string $paginationSafe;

    /** @param list<array{rank: int, playerName: string, player: Player}> $ratingsData */
    public function __construct(
        string $format,
        DateTime $lastTournamentDate,
        public string $lastTournamentName,
        public int $highestRating,
        public string $highestRatedPlayer,
        int $highestRatingTimestamp,
        public int $minMatches,
        public array $ratingsData,
        Pagination $pagination,
    ) {
        parent::__construct();
        $this->title = 'Ratings';
        $this->formatDropMenuR = (new FormatDropMenuR($format));
        $this->highestRatingDate = date('l, F j, Y', $highestRatingTimestamp);
        $this->lastTournamentDate = $lastTournamentDate->format('Y-m-d');
        foreach ($this->ratingsData as &$vals) {
            $vals['playerLink'] = new PlayerLink($vals['player']);
        }
        $this->ratingsTable = new RatingsTable($minMatches, $this->ratingsData);
        // Look, I know, but perfection is coming!
        ob_start();
        $pagination->render();
        $this->paginationSafe = ob_get_clean();
    }
}
