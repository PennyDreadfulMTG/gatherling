<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class SearchForm extends Component
{
    public string $searchLink;
    public FormatDropMenuDS $formatDropMenuDS;
    public ArchetypeDropMenu $archetypeDropMenu;
    public SeriesDropMenuDS $seriesDropMenuDS;
    public MedalsDropMenu $medalsDropMenu;
    public ColorCheckboxMenu $colorCheckboxMenu;

    /** @param array<string, string> $colors */
    public function __construct(public int $numSearchResults, string $phpSelf, public string $playerName, public string $cardName, string $formatName, string $archetype, string $seriesName, string $medals, array $colors)
    {
        parent::__construct('partials/searchForm');

        $this->searchLink = $phpSelf . '?mode=search';

        $this->formatDropMenuDS = new FormatDropMenuDS($formatName);
        $this->archetypeDropMenu = new ArchetypeDropMenu($archetype);
        $this->seriesDropMenuDS = new SeriesDropMenuDS($seriesName);
        $this->medalsDropMenu = new MedalsDropMenu('medals', ['1st', '2nd', 't4', 't8'], $medals);
        $this->colorCheckboxMenu = new ColorCheckboxMenu($colors);
    }
}
