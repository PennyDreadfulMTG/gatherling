<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

use function Gatherling\Helpers\getObjectVarsCamelCase;

class FormatSettings extends Component
{
    public string $formatName;
    /** @var array<int|string, mixed> */
    public array $format;
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

    public function __construct(public string $seriesName, Format $format)
    {
        $this->formatName = $format->name;
        $this->format = getObjectVarsCamelCase($format);
        $this->minMainStringField = new StringField('minmain', $format->min_main_cards_allowed, 5);
        $this->showMinMainWarning = $format->min_main_cards_allowed == 0;
        $this->maxMainStringField = new StringField('maxmain', $format->max_main_cards_allowed, 5);
        $this->showMaxMainWarning = $format->max_main_cards_allowed == 0;
        $this->minSideStringField = new StringField('minside', $format->min_side_cards_allowed, 5);
        $this->maxSideStringField = new StringField('maxside', $format->max_side_cards_allowed, 5);
        $this->showRarityWarning = 0 == $format->allow_commons + $format->allow_uncommons + $format->allow_rares + $format->allow_mythics + $format->allow_timeshifted;
        $this->underdogTooltip = new Tooltip('Underdog', 'Restrict usage of Changelings to 4 cards (8 for tribes with only 3 members).');
        $this->pureTooltip = new Tooltip('Pure', "Don't allow for off-tribe creatures or Changelings. All creatures in the deck must share at least one creature type.");
        $this->eternalTooltip = new Tooltip('Eternal Format', 'Eternal Formats treat all cardsets as legal.');
        $this->modernTooltip = new Tooltip('Modern Format', 'This format is built upon Modern.');
        $this->standardTooltip = new Tooltip('Standard Format', 'This format is built upon Standard.');
    }
}
