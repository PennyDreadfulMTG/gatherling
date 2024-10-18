<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class PlayerMatches extends Component
{
    public AllMatchForm $allMatchForm;
    public MatchTable $matchTable;

    public function __construct(Player $player, string $selectedFormat, string $selectedSeries, string $selectedSeason, string $selectedOpponent)
    {
        $this->allMatchForm = new AllMatchForm($player, $selectedFormat, $selectedSeries, $selectedSeason, $selectedOpponent);
        $this->matchTable = new MatchTable($player, $selectedFormat, $selectedSeries, $selectedSeason, $selectedOpponent);
    }
}
