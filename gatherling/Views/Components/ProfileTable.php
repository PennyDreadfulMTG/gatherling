<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class ProfileTable extends Component
{
    public InfoTable $infoTable;
    public MedalTable $medalTable;
    public TrophyTable $trophyTable;
    public BestDecksTable $bestDecksTable;

    public function __construct(Player $player)
    {
        parent::__construct('partials/profileTable');
        $this->infoTable = new InfoTable($player);
        $this->medalTable = new MedalTable($player);
        $this->trophyTable = new TrophyTable($player);
        $this->bestDecksTable = new BestDecksTable($player);
    }
}
