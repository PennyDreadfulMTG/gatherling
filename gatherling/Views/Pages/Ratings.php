<?php

namespace Gatherling\Views\Pages;

use DateTime;
use Gatherling\Models\Pagination;
use Gatherling\Views\Components\PlayerLink;
use Gatherling\Views\Components\RatingsTable;
use Gatherling\Views\Components\FormatDropMenuR;

class Ratings extends Page
{
    public FormatDropMenuR $formatDropMenuR;
    public string $highestRatingDate;
    public string $lastTournamentDate;
    public RatingsTable $ratingsTable;
    public string $paginationSafe;

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
