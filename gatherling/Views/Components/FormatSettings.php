<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class FormatSettings extends Component
{
    public string $activeFormatName;
    /** @var array<string, mixed> */
    public array $activeFormat;
    public StringField $minMainStringField;
    public bool $showMinMainWarning;
    public StringField $maxMainStringField;
    public bool $showMaxMainWarning;
    public StringField $minSideStringField;
    public StringField $maxSideStringField;
    public bool $showRarityWarning;
    public Tooltip $underdogTooltip;
    public Tooltip $pureTooltip;
    public Tooltip $eternalTooltip;
    public Tooltip $modernTooltip;
    public Tooltip $standardTooltip;

    public function __construct(public string $seriesName, Format $activeFormat)
    {
        parent::__construct('partials/formatSettings');
        $this->activeFormatName = $activeFormat->name;
        $this->activeFormat = getObjectVarsCamelCase($activeFormat);
        $this->minMainStringField = new StringField('minmain', $activeFormat->min_main_cards_allowed, 5);
        $this->showMinMainWarning = $activeFormat->min_main_cards_allowed == 0;
        $this->maxMainStringField = new StringField('maxmain', $activeFormat->max_main_cards_allowed, 5);
        $this->showMaxMainWarning = $activeFormat->max_main_cards_allowed == 0;
        $this->minSideStringField = new StringField('minside', $activeFormat->min_side_cards_allowed, 5);
        $this->maxSideStringField = new StringField('maxside', $activeFormat->max_side_cards_allowed, 5);
        $this->showRarityWarning = 0 == (int) $activeFormat->allow_commons + (int) $activeFormat->allow_uncommons + (int) $activeFormat->allow_rares + (int) $activeFormat->allow_mythics + (int) $activeFormat->allow_timeshifted;
        $this->underdogTooltip = new Tooltip('Underdog', 'Restrict usage of Changelings to 4 cards (8 for tribes with only 3 members).');
        $this->pureTooltip = new Tooltip('Pure', "Don't allow for off-tribe creatures or Changelings. All creatures in the deck must share at least one creature type.");
        $this->eternalTooltip = new Tooltip('Eternal Format', 'Eternal Formats treat all cardsets as legal.');
        $this->modernTooltip = new Tooltip('Modern Format', 'This format is built upon Modern.');
        $this->standardTooltip = new Tooltip('Standard Format', 'This format is built upon Standard.');
    }
}
