<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\SearchForm;
use Gatherling\Views\Components\DisplayDecks;
use Gatherling\Views\Components\MostPlayedDecks;

class DeckSearch extends Page
{
    public SearchForm $searchForm;
    public DisplayDecks $displayDecks;
    public ?MostPlayedDecks $mostPlayedDecks = null;

    /**
     * @param list<int> $results
     * @param list<string> $errors
     * @param array<string, string> $colors
     */
    public function __construct(array $results, string $phpSelf, public array $errors, string $playerName, string $cardName, string $formatName, string $archetype, string $seriesName, string $medals, array $colors)
    {
        parent::__construct();
        $this->title = 'Deck Search';
        $this->searchForm = new SearchForm(count($results), $phpSelf, $playerName, $cardName, $formatName, $archetype, $seriesName, $medals, $colors);
        if ($results) {
            $this->displayDecks = new DisplayDecks($results);
        } elseif (!$this->errors) {
            $this->mostPlayedDecks = new MostPlayedDecks();
        }
    }
}
