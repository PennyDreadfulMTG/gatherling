<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class DeckLink extends Component
{
    public bool $new;
    public ?string $name;
    public ?bool $isValid;
    public ?string $deckLink;

    public function __construct(Deck $deck)
    {
        parent::__construct('partials/deckLink');
        $this->new = $deck->new;
        if ($this->new) {
            return;
        }
        $this->name = empty($deck->name) ? '** NO NAME **' : $deck->name;
        $this->isValid = $deck->isValid();
        $this->deckLink = 'deck.php?mode=view&id=' . rawurlencode((string) $deck->id);
    }
}
