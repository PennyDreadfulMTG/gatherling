<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Entry;
use Gatherling\Models\Player;

class CreateDeckLink extends Component
{
    public bool $canCreateDeck;
    public string $createDeckLink;

    public function __construct(Entry $entry)
    {
        parent::__construct('partials/createDeckLink');

        $this->canCreateDeck = Player::loginName() ? $entry->canCreateDeck(Player::loginName()) : false;
        if ($this->canCreateDeck) {
            $this->createDeckLink = 'deck.php?player=' . rawurlencode($entry->player->name ?? '') . '&event=' . rawurlencode((string) $entry->event->id) . '&mode=create';
        }
    }
}
