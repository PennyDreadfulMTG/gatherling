<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class DeckRegisterForm extends Component
{
    public RecentDecksDropMenu $recentDecks;
    public DeckArchetypeDropMenu $deckArchetypeDropMenu;

    public function __construct(public string $playerName, public string $eventName)
    {
        parent::__construct('partials/deckRegisterForm');

        $this->recentDecks = new RecentDecksDropMenu($playerName);
        $this->deckArchetypeDropMenu = new DeckArchetypeDropMenu();
    }
}
