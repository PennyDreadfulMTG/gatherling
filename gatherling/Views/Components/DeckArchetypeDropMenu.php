<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;

class DeckArchetypeDropMenu extends SelectInput
{
    public function __construct(string $def = '')
    {
        $archetypes = Deck::getArchetypes();
        $archetypes = array_combine($archetypes, $archetypes);
        $archetypes = ['Unclassified' => '- Archetype -'] + $archetypes;
        parent::__construct('Archetype', 'archetype', $archetypes, $def, 'deck-archetype');
    }
}
