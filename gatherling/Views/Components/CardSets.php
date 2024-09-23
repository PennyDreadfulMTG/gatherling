<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class CardSets extends Component
{
    public string $activeFormatName;
    public array $coreCardSets;
    public ?NotAllowed $noCoreCardSets;
    public CardsetDropMenu $coreCardSetsDropMenu;
    public array $blockCardSets;
    public ?NotAllowed $noBlockCardSets;
    public CardsetDropMenu $blockCardSetsDropMenu;
    public array $extraCardSets;
    public ?NotAllowed $noExtraCardSets;
    public CardsetDropMenu $extraCardSetsDropMenu;

    public function __construct(public string $seriesName, Format $activeFormat)
    {
        parent::__construct('partials/cardSets');
        $this->activeFormatName = $activeFormat->name;
        $this->coreCardSets = $activeFormat->getCoreCardsets();
        $this->noCoreCardSets = $this->coreCardSets ? new NotAllowed('No Selected Card Set To Delete') : null;
        $this->coreCardSetsDropMenu = new CardsetDropMenu('Core', $activeFormat);
        $this->blockCardSets = $activeFormat->getBlockCardsets();
        $this->noBlockCardSets = $this->blockCardSets ? new NotAllowed('No Selected Card Set To Delete') : null;
        $this->blockCardSetsDropMenu = new CardsetDropMenu('Block', $activeFormat);
        $this->extraCardSets = $activeFormat->getExtraCardsets();
        $this->noExtraCardSets = $this->extraCardSets ? new NotAllowed('No Selected Card Set To Delete') : null;
        $this->extraCardSetsDropMenu = new CardsetDropMenu('Extra', $activeFormat);
    }
}
