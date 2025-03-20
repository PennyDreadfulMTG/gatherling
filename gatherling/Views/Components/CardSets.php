<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class CardSets extends Component
{
    public string $formatName;
    /** @var list<string> */
    public array $coreCardSets;
    public ?NotAllowed $noCoreCardSets;
    public CardsetDropMenu $coreCardSetsDropMenu;
    /** @var list<string> */
    public array $blockCardSets;
    public ?NotAllowed $noBlockCardSets;
    public CardsetDropMenu $blockCardSetsDropMenu;
    /** @var list<string> */
    public array $extraCardSets;
    public ?NotAllowed $noExtraCardSets;
    public CardsetDropMenu $extraCardSetsDropMenu;

    public function __construct(public string $seriesName, Format $format)
    {
        $this->formatName = $format->name;
        $this->coreCardSets = $format->getCoreCardsets();
        $this->noCoreCardSets = $this->coreCardSets ? new NotAllowed('No Selected Card Set To Delete') : null;
        $this->coreCardSetsDropMenu = new CardsetDropMenu('Core', $format);
        $this->blockCardSets = $format->getBlockCardsets();
        $this->noBlockCardSets = $this->blockCardSets ? new NotAllowed('No Selected Card Set To Delete') : null;
        $this->blockCardSetsDropMenu = new CardsetDropMenu('Block', $format);
        $this->extraCardSets = $format->getExtraCardsets();
        $this->noExtraCardSets = $this->extraCardSets ? new NotAllowed('No Selected Card Set To Delete') : null;
        $this->extraCardSetsDropMenu = new CardsetDropMenu('Extra', $format);
    }
}
