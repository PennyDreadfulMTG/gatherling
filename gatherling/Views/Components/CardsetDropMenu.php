<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;
use Gatherling\Models\CardSet;

class CardsetDropMenu extends Component
{
    /** @var list<string> */
    public array $cardsets;
    public bool $hasMany;
    public bool $isDisabled;
    /** @var list<array{value: string, text: string}> */
    public array $options;

    public function __construct(public string $cardsetType, Format $format)
    {
        parent::__construct('partials/cardsetDropMenu');
        $cardsets = CardSet::getMissingSets($cardsetType, $format);
        $options = [];
        $defaultText = $cardsets ? "- {$cardsetType} Cardset Name -" : "- All {$cardsetType} sets have been added -";
        $options[] = ['value' => 'Unclassified', 'text' => $defaultText];
        foreach ($cardsets as $cardset) {
            $options[] = ['value' => $cardset, 'text' => $cardset];
        }
        $this->cardsets = $cardsets;
        $this->cardsetType = $cardsetType;
        $this->hasMany = count($cardsets) > 2;
        $this->isDisabled = count($cardsets) == 0;
        $this->options = $options;
    }
}
